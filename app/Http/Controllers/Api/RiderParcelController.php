<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\Shipment;
use App\Services\RiderParcelScan;
use App\Services\RiderParcelScanException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The rider app scans a waybill barcode or QR code, shows the parcel, and the
 * rider accepts or declines it. Only accepting hands it over and notifies people.
 */
class RiderParcelController extends Controller
{
    public function __construct(private RiderParcelScan $scans) {}

    /** Scanned: show the parcel. Changes nothing. */
    public function scan(Request $request)
    {
        return $this->handle($request, function (Rider $rider, string $code) {
            $result = $this->scans->preview($rider, $code);

            return [
                'message' => $result['already']
                    ? "Parcel {$result['shipment']->tracking_code} is already with you."
                    : 'Check the parcel, then accept or decline it.',
                'already' => $result['already'],
                'stage' => $result['stage'],
            ] + $this->parcelJson($result['shipment']);
        });
    }

    public function accept(Request $request)
    {
        return $this->handle($request, function (Rider $rider, string $code) {
            $result = $this->scans->accept($rider, $code);

            return ['message' => $result['message'], 'already' => $result['already'], 'stage' => $result['stage']]
                + $this->parcelJson($result['shipment']);
        });
    }

    public function decline(Request $request)
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:200']])['reason'] ?? null;

        return $this->handle($request, function (Rider $rider, string $code) use ($reason) {
            $result = $this->scans->decline($rider, $code, $reason);

            return ['message' => $result['message'], 'released' => $result['released']] + $this->parcelJson($result['shipment']);
        });
    }

    /** Checks the rider and the code, runs [$action], and turns refusals into a message for the rider. */
    private function handle(Request $request, callable $action): JsonResponse
    {
        $code = $request->validate(['code' => ['required', 'string', 'max:500']])['code'];

        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved') {
            return response()->json(['message' => 'Your rider account is not approved yet. Ask your logistics hub.'], 403);
        }

        try {
            return response()->json($action($rider, $code));
        } catch (RiderParcelScanException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status());
        }
    }

    private function parcelJson(Shipment $shipment): array
    {
        $sellerOrder = $shipment->sellerOrder;
        $order = $sellerOrder->order;
        $address = $order?->shipping_address ?? [];
        $isCod = $order?->payment_method === 'cod';

        return ['parcel' => [
            'tracking_code' => $shipment->tracking_code,
            'order_reference' => $order?->reference,
            'status' => $shipment->status,
            'seller_name' => $sellerOrder->seller?->name,
            'recipient_name' => $address['recipient'] ?? null,
            'recipient_address' => $address['address'] ?? null,
            'items_count' => (int) $sellerOrder->items->sum('quantity'),
            'is_cod' => $isCod,
            'cod_amount' => $isCod ? ($shipment->cod_amount_minor ?: $sellerOrder->buyerDueMinor()) / 100 : 0,
        ]];
    }
}
