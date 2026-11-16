<?php

namespace App\Services;

use App\Models\Rider;
use App\Models\Shipment;
use Illuminate\Support\Carbon;

/**
 * Computes transparent per-leg earnings from real Shipment records:
 *  - Rider Seller Pickup leg (Seller -> Origin Hub): 20% of shipping fee (min ₱15.00)
 *  - Courier Hub-to-Hub leg (Origin Hub -> Dest Hub): 45% of shipping fee (min ₱45.00)
 *  - Rider Last-Mile Delivery leg (Dest Hub -> Buyer): 50% of shipping fee (min ₱30.00)
 */
class LogisticsEarningsService
{
    public const MIN_PICKUP_MINOR = 1500;
    public const MIN_DELIVERY_MINOR = 3000;
    public const MIN_LINEHAUL_MINOR = 4500;

    public static function pickupPayoutMinor(Shipment $shipment): int
    {
        $fee = max(0, (int) $shipment->fee_minor);
        return max(self::MIN_PICKUP_MINOR, (int) round($fee * 0.20));
    }

    public static function deliveryPayoutMinor(Shipment $shipment): int
    {
        $fee = max(0, (int) $shipment->fee_minor);
        return max(self::MIN_DELIVERY_MINOR, (int) round($fee * 0.50));
    }

    public static function linehaulPayoutMinor(Shipment $shipment): int
    {
        $fee = max(0, (int) $shipment->fee_minor);
        return max(self::MIN_LINEHAUL_MINOR, (int) round($fee * 0.45));
    }

    public function riderSummary(Rider $rider): array
    {
        $pickupShipments = Shipment::where('pickup_rider_id', $rider->id)
            ->whereNotNull('received_at')
            ->get();

        $deliveryShipments = Shipment::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->get();

        $pickupMinor = $pickupShipments->sum(fn (Shipment $s) => self::pickupPayoutMinor($s));
        $deliveryMinor = $deliveryShipments->sum(fn (Shipment $s) => self::deliveryPayoutMinor($s));

        return [
            'pickup_count' => $pickupShipments->count(),
            'pickup_earnings' => round($pickupMinor / 100, 2),
            'delivery_count' => $deliveryShipments->count(),
            'delivery_earnings' => round($deliveryMinor / 100, 2),
            'total_earnings' => round(($pickupMinor + $deliveryMinor) / 100, 2),
        ];
    }

    public function courierSummary(Rider $courier, ?Carbon $since = null): array
    {
        $completed = Shipment::where('linehaul_courier_id', $courier->id)
            ->whereNotNull('arrived_at_destination_hub_at')
            ->get();

        $totalMinor = $completed->sum(fn (Shipment $s) => self::linehaulPayoutMinor($s));
        $todayMinor = $since
            ? $completed->filter(fn (Shipment $s) => $s->arrived_at_destination_hub_at && $s->arrived_at_destination_hub_at->gte($since))
                ->sum(fn (Shipment $s) => self::linehaulPayoutMinor($s))
            : 0;

        return [
            'completed_count' => $completed->count(),
            'earnings_today' => round($todayMinor / 100, 2),
            'earnings_total' => round($totalMinor / 100, 2),
        ];
    }
}
