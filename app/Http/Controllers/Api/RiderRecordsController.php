<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{LogisticsMessage, Rider, Shipment};
use App\Services\LogisticsEarningsService;
use Illuminate\Http\Request;

class RiderRecordsController extends Controller
{
    private function rider(Request $request): Rider
    {
        $user = $request->user();
        $rider = $user->rider;
        abort_unless($user->status === 'active' && !$user->is_suspended && $rider && $rider->status === 'approved', 403);
        return $rider;
    }

    public function completed(Request $request)
    {
        $rider = $this->rider($request);
        $page = Shipment::where('rider_id', $rider->id)->where('status', 'delivered')
            ->with(['sellerOrder.order.buyer', 'sellerOrder.seller', 'sellerOrder.items', 'deliveryEvents'])
            ->latest('id')->paginate(100);
        $summary = app(LogisticsEarningsService::class)->riderSummary($rider);
        $fee = round(LogisticsEarningsService::MIN_DELIVERY_MINOR / 100, 2);
        return response()->json(['records' => $page->getCollection()->map(function ($shipment) {
            $parcel = $shipment->sellerOrder;
            $order = $parcel->order;
            $address = $order->shipping_address ?? [];
            $completed = $parcel->delivered_at ?? $shipment->deliveryEvents->firstWhere('status', 'delivered')?->occurred_at;
            $payout = round(LogisticsEarningsService::deliveryPayoutMinor($shipment) / 100, 2);
            return ['id' => $shipment->id, 'tracking_code' => $shipment->tracking_code, 'order_reference' => $order->reference,
                'status' => 'completed', 'buyer_name' => $address['recipient'] ?? $order->buyer?->name ?? '',
                'buyer_phone' => $address['phone'] ?? '', 'delivery_address' => $address['address'] ?? '',
                'items_summary' => $parcel->items->map(fn ($i) => $i->quantity.'x '.$i->product_name)->implode(', '),
                'total_amount' => $shipment->isCod() ? $shipment->cod_amount_minor / 100 : $parcel->buyerDueMinor() / 100,
                'created_at' => $shipment->created_at->toIso8601String(), 'completed_at' => $completed?->toIso8601String() ?? '',
                'store_partner' => $parcel->seller?->name ?? '', 'payment_method' => $shipment->isCod() ? 'COD' : 'Prepaid',
                'rider_payout' => $payout, 'cod_collected' => (bool) $shipment->cod_collected];
        })->values(), 'meta' => ['last_page' => $page->lastPage(), 'current_page' => $page->currentPage()],
            'hub_name' => $rider->logisticsProvider?->name, 'fee_per_delivery' => $fee,
            'earnings_summary' => $summary,
            'earnings_note' => 'Live per-leg earnings from completed Seller Pickups (20% of shipping, min ₱15) and Buyer Deliveries (50% of shipping, min ₱30).']);
    }

    public function messages(Request $request)
    {
        $rider = $this->rider($request);
        $hub = $rider->logisticsProvider;
        abort_unless($hub && $hub->status === 'approved', 403, 'No approved logistics hub is assigned to your account.');
        $messages = LogisticsMessage::where('logistics_provider_id', $hub->id)->where('contact_user_id', $rider->user_id);
        (clone $messages)->where('sender_type', 'hub')->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['hub_name' => $hub->name, 'messages' => $messages->orderBy('id')->get()->map(fn ($m) => [
            'id' => $m->id, 'body' => $m->message, 'is_me' => $m->sender_type === 'contact', 'created_at' => $m->created_at->toIso8601String(),
        ])]);
    }

    public function send(Request $request)
    {
        $rider = $this->rider($request);
        $hub = $rider->logisticsProvider;
        abort_unless($hub && $hub->status === 'approved', 403, 'No approved logistics hub is assigned to your account.');
        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $body = trim($data['message']);
        abort_unless($body !== '', 422, 'Write a message before sending.');
        $message = LogisticsMessage::create(['logistics_provider_id' => $hub->id, 'contact_user_id' => $rider->user_id,
            'sender_type' => 'contact', 'message' => $body]);
        return response()->json(['id' => $message->id, 'message' => 'Message saved for your logistics hub.'], 201);
    }
}
