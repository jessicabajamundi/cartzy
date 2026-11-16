<?php

namespace App\Services;

use App\Models\SellerOrder;
use Illuminate\Support\Facades\Cache;

class OrderAcceptanceMessage
{
    /** Called inside the seller acceptance transaction, with the order row locked. */
    public function send(SellerOrder $order): void
    {
        abort_unless($order->status === 'accepted', 422, 'Accept the order before sending its confirmation.');
        $parent = $order->order;
        $shop = $order->seller;
        if (!$parent?->buyer_id || !$shop?->user_id) {
            return;
        }

        $date = $parent->created_at->copy()->timezone('Asia/Manila')->format('M d, Y · h:i A');
        $body = "Hi, Dear Customer!\n\n"
            ."Thank you for choosing {$shop->name}! We truly appreciate your order and your trust in our products.\n\n"
            ."Your order has been successfully received and accepted. We will now carefully prepare your items for shipment.\n\n"
            ."Order number: {$parent->reference}\nOrder date: {$date} (Philippine time)\n\n"
            ."Thank you for shopping with us!\n\nBest regards,\n{$shop->name}";

        $message = $order->messages()->firstOrCreate(['event_key' => 'order_accepted'], [
            'sender_id' => $shop->user_id,
            'body' => $body,
            'delivered_at' => Cache::has('user_online:'.$parent->buyer_id) ? now() : null,
        ]);

        if ($message->wasRecentlyCreated) {
            NotificationService::notify(
                $parent->buyer_id,
                'order_accepted',
                'Order accepted by '.$shop->name,
                "{$shop->name} accepted order {$parent->reference}. Order date: {$date} (Philippine time). Tap to read your shop's thank-you message.",
                route('buyer.messages', ['contact' => $order->id])
            );
        }
    }
}
