<?php

namespace App\Services;

use App\Models\Address;
use App\Models\DeliveryEvent;
use App\Models\LogisticsProvider;
use App\Models\Rider;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Coordinates Hub-to-Hub (Linehaul) parcel transit between an Origin Hub (near the seller)
 * and a Destination Hub (near the buyer).
 *
 * Flow:
 *  1. Origin Hub receives parcel from First-Mile Rider / Seller dropoff.
 *  2. Origin Hub dispatches parcel via Linehaul Courier to Destination Hub (`in_transit`).
 *  3. Destination Hub (or Linehaul Courier) records arrival and leaves the parcel unassigned,
 *     transferring operational ownership (`current_hub_id`, `logistics_provider_id`) so
 *     Destination Hub can sort and assign a local Last-Mile Rider.
 */
class HubToHubService
{
    public function resolveDestinationHub(Address|User|null $buyerAddress, int $fallbackHubId): int
    {
        if (!$buyerAddress) {
            return $fallbackHubId;
        }

        $providers = LogisticsProvider::where('status', 'approved')->with('user')->get();
        if ($providers->isEmpty()) {
            return $fallbackHubId;
        }

        $ranked = app(HubRecommender::class)->rank($providers, $buyerAddress);
        $top = $ranked->first();

        if ($top && ($top['score'] ?? 0) > 0) {
            return (int) $top['provider']->id;
        }

        return $fallbackHubId;
    }

    public function dispatchToDestinationHub(
        Shipment $shipment,
        ?int $courierId = null,
        ?int $destinationHubId = null,
        ?int $actorId = null,
        ?string $batchCode = null
    ): Shipment {
        return DB::transaction(function () use ($shipment, $courierId, $destinationHubId, $actorId, $batchCode) {
            $parcel = Shipment::whereKey($shipment->id)
                ->with(['sellerOrder.order', 'sellerOrder.seller', 'originHub', 'destinationHub'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($destinationHubId !== null && $destinationHubId !== $parcel->destination_hub_id) {
                if ($parcel->package_profile['bulky'] ?? false) throw ValidationException::withMessages(['destination_hub_id' => 'Use the bulky route priced at checkout.']);
                $dest = LogisticsProvider::where('status', 'approved')->findOrFail($destinationHubId);
                $parcel->destination_hub_id = $dest->id;
            }

            if (!$parcel->origin_hub_id) {
                $parcel->origin_hub_id = $parcel->logistics_provider_id;
            }
            if (!$parcel->current_hub_id) {
                $parcel->current_hub_id = $parcel->logistics_provider_id;
            }

            if (!$parcel->received_at) {
                throw ValidationException::withMessages([
                    'shipment' => 'Receive the parcel at the origin hub before dispatching it for hub-to-hub transit.',
                ]);
            }

            if (!$parcel->requiresHubToHub()) {
                throw ValidationException::withMessages([
                    'destination_hub_id' => 'This parcel does not require hub-to-hub transit because origin and destination hubs are the same.',
                ]);
            }

            if (($parcel->package_profile['bulky'] ?? false) && (!$parcel->originHub?->bulky_enabled || !$parcel->destinationHub?->bulky_enabled)) {
                throw ValidationException::withMessages(['shipment' => 'Both hubs must support bulky handling and loading crew.']);
            }

            if ($parcel->isInHubToHubTransit() || $parcel->hasArrivedAtDestinationHub()) {
                throw ValidationException::withMessages([
                    'shipment' => 'This parcel is already in hub-to-hub transit or has arrived at its destination hub.',
                ]);
            }

            $courier = null;
            if ($courierId !== null) {
                $courier = Rider::whereKey($courierId)->where('logistics_provider_id', $parcel->logistics_provider_id)
                    ->availableForLinehaul()->with('user')->lockForUpdate()->first();
                if (!$courier) {
                    throw ValidationException::withMessages([
                        'courier_id' => 'Choose an active, approved Hub-to-Hub or First-Mile courier.',
                    ]);
                }
                if (!app(ParcelDelivery::class)->canLoad($parcel, $courier)) {
                    throw ValidationException::withMessages(['courier_id' => 'This vehicle cannot carry the parcel weight or dimensions.']);
                }
            }
            if ($parcel->package_profile && !$courier) {
                throw ValidationException::withMessages(['courier_id' => 'Select a compatible courier vehicle before dispatch.']);
            }

            $parcel->update([
                'origin_hub_id' => $parcel->origin_hub_id,
                'destination_hub_id' => $parcel->destination_hub_id,
                'current_hub_id' => $parcel->current_hub_id,
                'linehaul_courier_id' => $courier?->id,
                'linehaul_batch_code' => $batchCode ?: $parcel->linehaul_batch_code,
                'rider_id' => null,
                'assigned_at' => null,
                'status' => 'in_transit',
                // Transport is recorded in status/timestamps; keep the actual sorting state.
                'dispatched_to_hub_at' => now(),
            ]);

            $originName = $parcel->originHub?->name ?? 'Origin Hub';
            $destHub = LogisticsProvider::find($parcel->destination_hub_id);
            $destName = $destHub?->name ?? 'Destination Hub';
            $courierLabel = $courier?->user?->name ? " via courier {$courier->user->name}" : '';

            DeliveryEvent::create([
                'shipment_id' => $parcel->id,
                'status' => 'in_transit',
                'attempt' => DeliveryEvent::nextAttempt($parcel->id, 'in_transit'),
                'user_id' => $actorId ?? $parcel->originHub?->user_id,
                'note' => "Dispatched from {$originName} to {$destName}{$courierLabel} (Hub-to-Hub Linehaul).",
                'occurred_at' => now(),
            ]);

            if ($parcel->sellerOrder && !in_array($parcel->sellerOrder->status, ['shipped', 'delivered', 'completed'], true)) {
                $parcel->sellerOrder->update(['status' => 'shipped']);
            }

            $this->notifyDispatch($parcel, $originName, $destName, $destHub, $courier);

            return $parcel->fresh(['originHub', 'destinationHub', 'currentHub', 'linehaulCourier.user']);
        }, 3);
    }

    /**
     * Group waiting Hub-to-Hub parcels for a destination hub into a single BATCH-XXXXXX manifest.
     *
     * @return array{batch_code: string, count: int, shipments: array<int, Shipment>}
     */
    public function dispatchBatchToDestinationHub(
        int $originHubId,
        int $destinationHubId,
        ?int $courierId = null,
        ?int $actorId = null
    ): array {
        $batchCode = 'BATCH-' . strtoupper(Str::random(6));
        $waiting = Shipment::where('logistics_provider_id', $originHubId)
            ->where('destination_hub_id', $destinationHubId)
            ->whereNotNull('received_at')
            ->whereNull('dispatched_to_hub_at')
            ->whereNull('arrived_at_destination_hub_at')
            ->get();

        $dispatched = [];
        foreach ($waiting as $shipment) {
            $dispatched[] = $this->dispatchToDestinationHub($shipment, $courierId, null, $actorId, $batchCode);
        }

        return [
            'batch_code' => $batchCode,
            'count' => count($dispatched),
            'shipments' => $dispatched,
        ];
    }

    /**
     * Mark every in-transit parcel sharing a BATCH-XXXXXX code as arrived at its destination hub.
     *
     * @return array{batch_code: string, count: int}
     */
    public function receiveBatchByCode(string $batchCode, ?int $actorId = null): array
    {
        $code = strtoupper(trim($batchCode));
        $parcels = Shipment::where('linehaul_batch_code', $code)
            ->whereNotNull('dispatched_to_hub_at')
            ->whereNull('arrived_at_destination_hub_at')
            ->get();

        $count = 0;
        foreach ($parcels as $parcel) {
            $this->receiveAtDestinationHub($parcel, $actorId);
            $count++;
        }

        return ['batch_code' => $code, 'count' => $count];
    }

    public function receiveAtDestinationHub(Shipment $shipment, ?int $actorId = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $actorId) {
            $parcel = Shipment::whereKey($shipment->id)
                ->with(['sellerOrder.order', 'sellerOrder.seller', 'originHub', 'destinationHub'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$parcel->requiresHubToHub()) {
                throw ValidationException::withMessages([
                    'shipment' => 'This parcel is not a hub-to-hub transfer.',
                ]);
            }

            if ($parcel->arrived_at_destination_hub_at !== null) {
                return $parcel;
            }

            $destHub = LogisticsProvider::findOrFail($parcel->destination_hub_id);
            $originName = $parcel->originHub?->name ?? 'Origin Hub';
            $destName = $destHub->name;

            $parcel->update([
                'current_hub_id' => $destHub->id,
                'logistics_provider_id' => $destHub->id,
                'arrived_at_destination_hub_at' => now(),
                'received_at' => now(),
                'sorted_at' => null,
                'sort_status' => 'unsorted',
                // received_at marks it as physically at the hub, awaiting a delivery driver.
                'status' => 'unassigned',
                'rider_id' => null,
                'assigned_at' => null,
            ]);

            if ($parcel->sellerOrder) {
                $parcel->sellerOrder->update(['logistics_provider_id' => $destHub->id]);
            }

            $userId = $actorId ?? $destHub->user_id;

            DeliveryEvent::create([
                'shipment_id' => $parcel->id,
                'status' => 'arrived_at_destination_hub',
                'attempt' => DeliveryEvent::nextAttempt($parcel->id, 'arrived_at_destination_hub'),
                'user_id' => $userId,
                'note' => "Arrived at destination hub ({$destName}) from {$originName}.",
                'occurred_at' => now(),
            ]);

            DeliveryEvent::create([
                'shipment_id' => $parcel->id,
                'status' => 'received_at_hub',
                'attempt' => DeliveryEvent::nextAttempt($parcel->id, 'received_at_hub'),
                'user_id' => $userId,
                'note' => "Received at {$destName} for local area sorting and Last Mile delivery.",
                'occurred_at' => now(),
            ]);

            $this->notifyArrival($parcel, $originName, $destName);

            return $parcel->fresh(['originHub', 'destinationHub', 'currentHub', 'linehaulCourier.user']);
        }, 3);
    }

    private function notifyDispatch(
        Shipment $parcel,
        string $originName,
        string $destName,
        ?LogisticsProvider $destHub,
        ?Rider $courier
    ): void {
        $reference = $parcel->sellerOrder?->order?->reference ?? $parcel->tracking_code;
        $code = $parcel->tracking_code;

        if ($buyerId = $parcel->sellerOrder?->order?->buyer_id) {
            NotificationService::notify(
                $buyerId,
                'delivery',
                'Parcel in Hub-to-Hub Transit',
                "Order {$reference} ({$code}) departed {$originName} and is in transit to {$destName}.",
                route('buyer.dashboard', ['tab' => 'orders'])
            );
        }

        if ($sellerUserId = $parcel->sellerOrder?->seller?->user_id) {
            NotificationService::notify(
                $sellerUserId,
                'order_update',
                'Parcel Dispatched to Destination Hub',
                "Parcel {$code} for order {$reference} departed {$originName} toward {$destName}.",
                route('seller.notifications')
            );
        }

        if ($destHub?->user_id) {
            NotificationService::notify(
                $destHub->user_id,
                'parcel_scan',
                'Incoming Hub-to-Hub Parcel',
                "Parcel {$code} was dispatched from {$originName} to your hub ({$destName}).",
                route('logistics.hub', ['tab' => 'receive'])
            );
        }

        if ($courier?->user_id) {
            NotificationService::notify(
                $courier->user_id,
                'parcel_scan',
                'Hub-to-Hub Transfer Assigned',
                "Carry parcel {$code} from {$originName} to {$destName}.",
                '/courier'
            );
        }
    }

    private function notifyArrival(Shipment $parcel, string $originName, string $destName): void
    {
        $reference = $parcel->sellerOrder?->order?->reference ?? $parcel->tracking_code;
        $code = $parcel->tracking_code;

        if ($buyerId = $parcel->sellerOrder?->order?->buyer_id) {
            NotificationService::notify(
                $buyerId,
                'delivery',
                'Arrived at Destination Hub',
                "Order {$reference} ({$code}) arrived at {$destName} and is being sorted for delivery.",
                route('buyer.dashboard', ['tab' => 'orders'])
            );
        }

        if ($sellerUserId = $parcel->sellerOrder?->seller?->user_id) {
            NotificationService::notify(
                $sellerUserId,
                'order_update',
                'Arrived at Destination Hub',
                "Parcel {$code} arrived at {$destName} from {$originName}.",
                route('seller.notifications')
            );
        }

        if ($originUserId = $parcel->originHub?->user_id) {
            NotificationService::notify(
                $originUserId,
                'parcel_scan',
                'Hub-to-Hub Transfer Received',
                "Parcel {$code} safely arrived at {$destName}.",
                route('logistics.monitoring')
            );
        }
    }
}
