<?php

namespace App\Services;

use App\Models\{AppNotification, ProductInquiryMessage, SellerMessage, SellerOrder, User, Wishlist};

class BuyerNavigationCounts
{
    public function forUser(?User $user): array
    {
        $counts = array_fill_keys(['orders', 'messages', 'cart', 'wishlist', 'track', 'notifications'], 0);
        if (!$user || !$user->isBuyer()) {
            return $counts;
        }

        $orders = SellerOrder::whereHas('order', fn ($query) => $query->where('buyer_id', $user->id));
        $statuses = (clone $orders)->pluck('status');
        // Only orders still in progress (to pay, to ship, to receive); finished or cancelled ones are not counted.
        $counts['orders'] = $statuses->reject(fn ($status) => in_array($status, ['delivered', 'completed', 'cancelled'], true))->count();
        $counts['track'] = $counts['orders'];
        $counts['cart'] = count(app(\App\Services\BuyerCart::class)->all());
        $counts['wishlist'] = Wishlist::where('user_id', $user->id)->count();
        $counts['messages'] = SellerMessage::whereIn('seller_order_id', (clone $orders)->select('id'))
            ->where('sender_id', '!=', $user->id)->whereNull('read_at')->count()
            + ProductInquiryMessage::whereHas('inquiry', fn ($query) => $query->where('buyer_id', $user->id))
                ->where('sender_id', '!=', $user->id)->whereNull('read_at')->count();
        $counts['notifications'] = AppNotification::where('user_id', $user->id)->where('is_read', false)->count();

        return $counts;
    }
}
