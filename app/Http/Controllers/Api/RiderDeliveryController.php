<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\Shipment;
use App\Services\RiderDeliveries;
use App\Services\RiderParcelScanException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The Last Mile rider's delivery queue in the app. Starting a delivery is the
 * waybill scan (POST /rider/parcels/accept), which saves "out for delivery".
 */
class RiderDeliveryController extends Controller
{
    public function __construct(private RiderDeliveries $deliveries) {}

    public function index(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        if ($rider instanceof JsonResponse) {
            return $rider;
        }

        $active = $this->deliveries->activeFor($rider);

        return response()->json([
            'deliveries' => $active->map(fn (Shipment $s) => self::deliveryJson($s))->values(),
            'cod_to_collect' => round($active->sum(fn (Shipment $s) => self::codMinor($s)) / 100, 2),
            'delivered_today' => Shipment::where('rider_id', $rider->id)->where('status', 'delivered')
                ->whereDate('updated_at', now()->toDateString())->count(),
        ]);
    }

    public function deliver(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:500'],
            'received_by' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->run($request, fn (Rider $rider) => $this->deliveries->deliver($rider, $id, $data['code'], $data['received_by'] ?? null, $data['note'] ?? null));
    }

    public function fail(Request $request, int $id): JsonResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:200']])['reason'];

        return $this->run($request, fn (Rider $rider) => $this->deliveries->fail($rider, $id, $reason));
    }

    private function run(Request $request, callable $action): JsonResponse
    {
        $rider = $this->rider($request);
        if ($rider instanceof JsonResponse) {
            return $rider;
        }

        try {
            $result = $action($rider);
        } catch (RiderParcelScanException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status());
        }

        return response()->json(['message' => $result['message'], 'delivery' => self::deliveryJson($result['shipment'])]);
    }

    private function rider(Request $request): Rider|JsonResponse
    {
        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved') {
            return response()->json(['message' => 'Your rider account is not approved yet. Ask your logistics hub.'], 403);
        }

        return $rider;
    }

    private static function codMinor(Shipment $shipment): int
    {
        return $shipment->sellerOrder->order?->payment_method === 'cod'
            ? (int) ($shipment->cod_amount_minor ?: $shipment->sellerOrder->buyerDueMinor())
            : 0;
    }

    public static function deliveryJson(Shipment $shipment): array
    {
        $sellerOrder = $shipment->sellerOrder;
        $order = $sellerOrder->order;
        $address = $order?->shipping_address ?? [];
        $items = $sellerOrder->items;

        return [
            'id' => $shipment->id,
            'tracking_code' => $shipment->tracking_code,
            'order_reference' => $order?->reference,
            'status' => $shipment->status,
            'recipient_name' => $address['recipient'] ?? $order?->buyer?->name,
            'recipient_phone' => $address['phone'] ?? $order?->buyer?->phone,
            'recipient_address' => $address['address'] ?? null,
            'seller_name' => $sellerOrder->seller?->name,
            'items_count' => (int) $items->sum('quantity'),
            'items_summary' => $items->map(fn ($i) => $i->quantity.'x '.$i->product_name)->implode(', '),
            'is_cod' => $order?->payment_method === 'cod',
            'cod_amount' => self::codMinor($shipment) / 100,
            'attempts' => (int) $shipment->attempts,
            'assigned_at' => $shipment->assigned_at?->toIso8601String(),
        ];
    }
}
