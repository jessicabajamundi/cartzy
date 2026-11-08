<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RiderParcelScan;
use App\Services\RiderParcelScanException;
use Illuminate\Http\Request;

/** The rider app scans a waybill barcode or QR code to take a parcel. */
class RiderParcelController extends Controller
{
    public function scan(Request $request, RiderParcelScan $scans)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:500']]);

        $rider = $request->user()->rider;
        if (!$rider || $rider->status !== 'approved') {
            return response()->json(['message' => 'Your rider account is not approved yet. Ask your logistics hub.'], 403);
        }

        try {
            $result = $scans->scan($rider, $data['code']);
        } catch (RiderParcelScanException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status());
        }

        $shipment = $result['shipment'];
        $sellerOrder = $shipment->sellerOrder;
        $address = $sellerOrder->order?->shipping_address ?? [];

        return response()->json([
            'message' => $result['message'],
            'already' => $result['already'],
            'stage' => $result['stage'],
            'parcel' => [
                'tracking_code' => $shipment->tracking_code,
                'order_reference' => $sellerOrder->order?->reference,
                'status' => $shipment->status,
                'seller_name' => $sellerOrder->seller?->name,
                'recipient_name' => $address['recipient'] ?? null,
                'recipient_address' => $address['address'] ?? null,
                'items_count' => (int) $sellerOrder->items->sum('quantity'),
                'is_cod' => $sellerOrder->order?->payment_method === 'cod',
                'cod_amount' => $sellerOrder->order?->payment_method === 'cod'
                    ? ($shipment->cod_amount_minor ?: $sellerOrder->buyerDueMinor()) / 100
                    : 0,
            ],
        ]);
    }
}
