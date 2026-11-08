<?php

namespace App\Services;

use App\Models\DeliveryEvent;
use App\Models\Rider;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

/**
 * A rider scans a parcel's waybill (barcode or QR code). That hands the parcel
 * to the rider, and the seller, the buyer and the logistics hub are told.
 *
 *  - First Mile courier, parcel still at the seller  -> picked up
 *  - Last Mile courier, parcel received at the hub   -> out for delivery
 *
 * Scanning a parcel the rider already holds changes nothing and notifies no one.
 */
class RiderParcelScan
{
    /**
     * @return array{shipment: Shipment, stage: string, already: bool, message: string}
     *
     * @throws RiderParcelScanException when the parcel can't be handed to this rider
     */
    public function scan(Rider $rider, string $rawCode): array
    {
        $code = self::normalize($rawCode);
        if ($code === '') {
            throw new RiderParcelScanException("That code doesn't look like a cartzy waybill.");
        }

        return DB::transaction(function () use ($rider, $code) {
            $shipment = $this->find($code);
            if (!$shipment) {
                throw new RiderParcelScanException("No parcel found for {$code}. Check that you scanned the cartzy waybill.", 404);
            }

            $this->guard($rider, $shipment);

            $stage = $rider->isFirstMile() ? 'picked_up' : 'out_for_delivery';
            if ($this->alreadyWithRider($rider, $shipment)) {
                return ['shipment' => $shipment, 'stage' => $stage, 'already' => true,
                    'message' => "Parcel {$shipment->tracking_code} is already with you."];
            }

            $riderName = $rider->user?->name ?? 'your rider';
            if ($stage === 'picked_up') {
                $shipment->update(['pickup_rider_id' => $rider->id, 'status' => 'picked_up']);
                // Buyers see it under "To receive" once it has left the seller.
                if (!in_array($shipment->sellerOrder->status, ['shipped', 'delivered', 'completed'], true)) {
                    $shipment->sellerOrder->update(['status' => 'shipped']);
                }
                $note = "Scanned and picked up from {$shipment->sellerOrder->seller?->name} by rider {$riderName}.";
            } else {
                $shipment->update(['rider_id' => $rider->id, 'status' => 'out_for_delivery', 'assigned_at' => $shipment->assigned_at ?? now()]);
                $note = "Scanned at the hub by rider {$riderName}; out for delivery.";
            }

            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $stage,
                'attempt' => max(1, (int) $shipment->attempts),
                'user_id' => $rider->user_id,
                'note' => $note,
                'occurred_at' => now(),
            ]);

            $this->notifyEveryone($shipment, $stage, $riderName);

            return ['shipment' => $shipment, 'stage' => $stage, 'already' => false,
                'message' => $stage === 'picked_up'
                    ? "Parcel {$shipment->tracking_code} is now with you. The seller, buyer and hub were notified."
                    : "Parcel {$shipment->tracking_code} is out for delivery with you. The seller, buyer and hub were notified."];
        });
    }

    /**
     * Waybill barcodes hold the tracking code; a QR code may hold the code or a
     * link ending in it. Order references are accepted too.
     */
    public static function normalize(string $raw): string
    {
        $code = trim($raw);
        if (preg_match('~(CTZ-[A-Z0-9-]+|ORD-[0-9]{4}-[0-9]+)~i', $code, $match)) {
            $code = $match[1];
        }

        return strtoupper(mb_substr($code, 0, 100));
    }

    private function find(string $code): ?Shipment
    {
        $with = ['sellerOrder.order', 'sellerOrder.seller', 'sellerOrder.items', 'logisticsProvider'];
        $shipment = Shipment::with($with)->where('tracking_code', $code)->lockForUpdate()->first();
        if ($shipment) {
            return $shipment;
        }

        // An order reference only identifies a parcel when the order has one store.
        $byOrder = Shipment::with($with)->whereHas('sellerOrder.order', fn ($q) => $q->where('reference', $code))->lockForUpdate()->get();

        return $byOrder->count() === 1 ? $byOrder->first() : null;
    }

    private function guard(Rider $rider, Shipment $shipment): void
    {
        if ($shipment->logistics_provider_id && $rider->logistics_provider_id && $shipment->logistics_provider_id !== $rider->logistics_provider_id) {
            throw new RiderParcelScanException('This parcel belongs to another logistics partner.');
        }
        if ($shipment->status === 'delivered') {
            throw new RiderParcelScanException("Parcel {$shipment->tracking_code} was already delivered.");
        }
        if (in_array($shipment->sellerOrder?->status, ['cancelled', 'canceled'], true) || in_array($shipment->status, ['cancelled', 'returned'], true)) {
            throw new RiderParcelScanException("Parcel {$shipment->tracking_code} was cancelled. Don't take it.");
        }

        $atHub = $shipment->received_at !== null;
        if ($rider->isFirstMile()) {
            if ($atHub) {
                throw new RiderParcelScanException("Parcel {$shipment->tracking_code} is already at the hub. Last Mile couriers deliver it from here.");
            }
            if ($shipment->pickup_rider_id && $shipment->pickup_rider_id !== $rider->id) {
                throw new RiderParcelScanException("Parcel {$shipment->tracking_code} was picked up by another rider.");
            }
        } else {
            if (!$atHub) {
                throw new RiderParcelScanException("Parcel {$shipment->tracking_code} hasn't reached the hub yet. A First Mile courier picks it up from the seller first.");
            }
            if ($shipment->rider_id && $shipment->rider_id !== $rider->id) {
                throw new RiderParcelScanException("Parcel {$shipment->tracking_code} is assigned to another rider.");
            }
        }
    }

    private function alreadyWithRider(Rider $rider, Shipment $shipment): bool
    {
        return $rider->isFirstMile()
            ? $shipment->status === 'picked_up' && $shipment->pickup_rider_id === $rider->id
            : $shipment->status === 'out_for_delivery' && $shipment->rider_id === $rider->id;
    }

    private function notifyEveryone(Shipment $shipment, string $stage, string $riderName): void
    {
        $sellerOrder = $shipment->sellerOrder;
        $reference = $sellerOrder->order?->reference ?? $shipment->tracking_code;
        $code = $shipment->tracking_code;
        $store = $sellerOrder->seller?->name ?? 'the seller';

        [$buyer, $seller, $hub] = $stage === 'picked_up'
            ? [
                ['Parcel picked up', "Your order {$reference} was picked up from {$store} by rider {$riderName} and is on its way to the hub."],
                ['Parcel picked up', "Rider {$riderName} scanned and picked up parcel {$code} for order {$reference}."],
                ['Parcel picked up from seller', "Rider {$riderName} picked up parcel {$code} from {$store}. Expect it at the hub."],
            ]
            : [
                ['Out for delivery', "Your order {$reference} is out for delivery with rider {$riderName}."],
                ['Out for delivery', "Parcel {$code} for order {$reference} left the hub with rider {$riderName}."],
                ['Parcel out for delivery', "Rider {$riderName} scanned parcel {$code} at the hub and is delivering it."],
            ];

        if ($buyerId = $sellerOrder->order?->buyer_id) {
            NotificationService::notify($buyerId, 'delivery', $buyer[0], $buyer[1], route('buyer.dashboard', ['tab' => 'orders']));
        }
        if ($sellerUserId = $sellerOrder->seller?->user_id) {
            NotificationService::notify($sellerUserId, 'order_update', $seller[0], $seller[1], route('seller.notifications'));
        }
        if ($hubUserId = $shipment->logisticsProvider?->user_id) {
            NotificationService::notify($hubUserId, 'parcel_scan', $hub[0], $hub[1], route('logistics.notifications'));
        }
    }
}
