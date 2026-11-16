<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryEvent;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\Shipment;
use App\Services\HubToHubService;
use App\Services\LogisticsEarningsService;
use App\Services\RiderAttendanceService;
use Illuminate\Http\Request;

/**
 * The First Mile courier's dashboard in the app, all from the database:
 * who they are, their shift, the pickups waiting for them, the parcels in
 * their vehicle, and what they handed to the hub. Nothing is made up; empty
 * lists and null values mean there is nothing yet.
 */
class CourierDashboardController extends Controller
{
    public function __construct(private RiderAttendanceService $attendance) {}

    public function show(Request $request)
    {
        $rider = $request->user()->rider()->with(['user', 'logisticsProvider'])->first();
        if (!$rider) {
            return response()->json(['message' => 'No courier profile is linked to this account.'], 404);
        }

        $shift = $this->attendance->openShift($rider);
        $linehaul = $this->linehaulTransfers($rider);
        $today = now('Asia/Manila')->startOfDay()->utc();
        $completedLinehaulToday = Shipment::where('linehaul_courier_id', $rider->id)
            ->where('arrived_at_destination_hub_at', '>=', $today)
            ->count();
        $earnings = app(LogisticsEarningsService::class)->courierSummary($rider, $today);

        return response()->json([
            'courier' => [
                'name' => $rider->user->name,
                'phone' => $rider->user->phone,
                'email' => $rider->user->email,
                'courier_type' => $rider->courier_type,
                'courier_type_label' => Rider::COURIER_TYPES[$rider->courier_type][0] ?? null,
                'vehicle_type' => $rider->vehicle_type,
                'vehicle_label' => Rider::vehicleTypesFor($rider->courier_type)[$rider->vehicle_type] ?? ($rider->vehicle_type ? ucfirst($rider->vehicle_type) : null),
                'plate_no' => $rider->plate_no ?: null,
                'area' => $rider->area ?: null,
                'home_address' => AuthController::homeAddress($rider->user),
                'hub' => $rider->logisticsProvider?->name,
                'status' => $rider->status,
                'is_active' => (bool) $rider->is_active,
                // No rating: couriers can't be rated yet, and the column only holds a 5.00 default.
                'member_since' => $rider->created_at?->timezone('Asia/Manila')->format('F Y'),
            ],
            'shift' => [
                'on_shift' => (bool) $shift,
                'since' => $shift?->time_in_at?->toIso8601String(),
            ],
            'today' => [
                'pickups_to_do' => $linehaul->where('is_in_transit', false)->count(),
                'parcels_to_collect' => $linehaul->where('is_in_transit', false)->count(),
                'in_vehicle' => $linehaul->where('is_in_transit', true)->count(),
                'collected_today' => $linehaul->where('is_in_transit', true)->count(),
                'handed_to_hub_today' => $completedLinehaulToday,
                'linehaul_in_transit' => $linehaul->where('is_in_transit', true)->count(),
                'linehaul_earnings_today' => $earnings['earnings_today'],
                'linehaul_earnings_total' => $earnings['earnings_total'],
            ],
            'pickups' => [],
            'in_vehicle' => [],
            'handed_to_hub' => [],
            'linehaul_transfers' => $linehaul->values(),
        ]);
    }

    /**
     * Pickup requests the hub assigned to this courier that still have parcels
     * waiting at the seller.
     */
    private function pickupsToDo(Rider $rider)
    {
        return PickupRequest::with(['seller.user', 'seller.pickupAddress'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['pending', 'approved', 'confirmed'])
            ->orderBy('pickup_date')->orderBy('id')
            ->get()
            ->map(function (PickupRequest $pickup) use ($rider) {
                $waiting = Shipment::where('logistics_provider_id', $rider->logistics_provider_id)
                    ->whereNull('received_at')
                    ->whereIn('status', ['unassigned', 'assigned'])
                    ->when(
                        $pickup->seller_order_id,
                        fn ($q) => $q->where('seller_order_id', $pickup->seller_order_id),
                        fn ($q) => $q->whereHas('sellerOrder', fn ($so) => $so->where('seller_id', $pickup->seller_id))
                    )
                    ->count();

                return [
                    'id' => $pickup->id,
                    'seller_name' => $pickup->seller?->name,
                    'seller_phone' => $pickup->seller?->pickupAddress?->phone ?? $pickup->seller?->user?->phone,
                    'address' => $pickup->address,
                    'pickup_date' => $pickup->pickup_date?->toDateString(),
                    'pickup_time' => $pickup->pickup_time,
                    'parcel_count' => (int) $pickup->parcel_count,
                    'parcels_waiting' => $waiting,
                    'weight_kg' => $pickup->weight_kg,
                    'notes' => $pickup->notes,
                    'status' => $pickup->status,
                ];
            })
            // Fully collected requests are done, even if the hub hasn't closed them.
            ->filter(fn (array $pickup) => $pickup['parcels_waiting'] > 0);
    }

    /** Parcels this courier picked up that the hub hasn't received yet. */
    private function inVehicle(Rider $rider)
    {
        return Shipment::with(['sellerOrder.seller', 'sellerOrder.order'])
            ->where('pickup_rider_id', $rider->id)
            ->whereNull('received_at')
            ->where('status', 'picked_up')
            ->latest('updated_at')
            ->get()
            ->map(fn (Shipment $s) => $this->parcel($s, $this->pickedUpAt($s)));
    }

    /** The latest parcels this courier brought to the hub. */
    private function handedToHub(Rider $rider)
    {
        return Shipment::with(['sellerOrder.seller', 'sellerOrder.order'])
            ->where('pickup_rider_id', $rider->id)
            ->whereNotNull('received_at')
            ->latest('received_at')
            ->take(30)
            ->get()
            ->map(fn (Shipment $s) => $this->parcel($s, $s->received_at?->toIso8601String()));
    }

    private function pickedUpAt(Shipment $shipment): ?string
    {
        return DeliveryEvent::where('shipment_id', $shipment->id)->where('status', 'picked_up')
            ->latest('occurred_at')->first()?->occurred_at?->toIso8601String();
    }

    public function dispatchLinehaul(Request $request, int $id)
    {
        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved' || !$rider->canCarryLinehaul()) {
            return response()->json(['message' => 'Only approved Hub-to-Hub or First Mile couriers can dispatch linehaul parcels.'], 403);
        }

        $shipment = Shipment::where('origin_hub_id', $rider->logistics_provider_id)
            ->where(function ($q) use ($rider) {
                $q->whereNull('linehaul_courier_id')->orWhere('linehaul_courier_id', $rider->id);
            })
            ->findOrFail($id);

        $updated = app(HubToHubService::class)->dispatchToDestinationHub($shipment, $rider->id, null, $request->user()->id);

        return response()->json([
            'message' => "Parcel {$updated->tracking_code} is now in Hub-to-Hub transit to {$updated->destinationHub?->name}.",
            'transfer' => $this->linehaulJson($updated),
        ]);
    }

    public function arriveLinehaul(Request $request, int $id)
    {
        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved' || !$rider->canCarryLinehaul()) {
            return response()->json(['message' => 'Only approved Hub-to-Hub or First Mile couriers can complete linehaul transfers.'], 403);
        }

        $shipment = Shipment::where(function ($q) use ($rider) {
                $q->where('linehaul_courier_id', $rider->id)
                  ->orWhere('origin_hub_id', $rider->logistics_provider_id);
            })
            ->whereNotNull('dispatched_to_hub_at')
            ->whereNull('arrived_at_destination_hub_at')
            ->findOrFail($id);

        $updated = app(HubToHubService::class)->receiveAtDestinationHub($shipment, $request->user()->id);

        return response()->json([
            'message' => "Parcel {$updated->tracking_code} handed over at {$updated->destinationHub?->name}.",
            'transfer' => $this->linehaulJson($updated),
        ]);
    }

    public function arriveLinehaulBatch(Request $request, string $code)
    {
        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved' || !$rider->canCarryLinehaul()) {
            return response()->json(['message' => 'Only approved Hub-to-Hub couriers can complete batch transfers.'], 403);
        }

        $result = app(HubToHubService::class)->receiveBatchByCode($code, $request->user()->id);

        return response()->json([
            'message' => "Batch {$result['batch_code']} ({$result['count']} parcel(s)) handed over at destination hub.",
            'batch_code' => $result['batch_code'],
            'count' => $result['count'],
        ]);
    }

    /** Hub-to-Hub transfers assigned to this courier or waiting at their origin hub. */
    private function linehaulTransfers(Rider $rider)
    {
        if (!$rider->canCarryLinehaul()) {
            return collect();
        }

        return Shipment::with(['originHub', 'destinationHub', 'sellerOrder.seller', 'sellerOrder.order'])
            ->whereNotNull('origin_hub_id')
            ->whereNotNull('destination_hub_id')
            ->whereColumn('origin_hub_id', '!=', 'destination_hub_id')
            ->whereNull('arrived_at_destination_hub_at')
            ->whereNotNull('received_at')
            ->where(function ($q) use ($rider) {
                $q->where('linehaul_courier_id', $rider->id)
                  ->orWhere(function ($oq) use ($rider) {
                      $oq->where('origin_hub_id', $rider->logistics_provider_id)
                         ->whereNull('linehaul_courier_id');
                  });
            })
            ->latest('updated_at')
            ->get()
            ->map(fn (Shipment $s) => $this->linehaulJson($s));
    }

    private function linehaulJson(Shipment $s): array
    {
        return [
            'id' => $s->id,
            'tracking_code' => $s->tracking_code,
            'order_reference' => $s->sellerOrder?->order?->reference,
            'batch_code' => $s->linehaul_batch_code,
            'origin_hub' => $s->originHub?->name ?? 'Origin Hub',
            'destination_hub' => $s->destinationHub?->name ?? 'Destination Hub',
            'payout_amount' => round(LogisticsEarningsService::linehaulPayoutMinor($s) / 100, 2),
            'is_in_transit' => $s->isInHubToHubTransit(),
            'dispatched_at' => $s->dispatched_to_hub_at?->toIso8601String(),
            'arrived_at' => $s->arrived_at_destination_hub_at?->toIso8601String(),
        ];
    }

    private function parcel(Shipment $shipment, ?string $at): array
    {
        return [
            'tracking_code' => $shipment->tracking_code,
            'order_reference' => $shipment->sellerOrder?->order?->reference,
            'seller_name' => $shipment->sellerOrder?->seller?->name,
            'items_count' => (int) $shipment->sellerOrder?->items()->sum('quantity'),
            'at' => $at,
        ];
    }
}
