<?php

namespace App\Services;

use App\Models\{AppNotification, SellerMessage, SellerOrder, User, Wishlist};

class BuyerNavigationCounts
{
    public function forUser(?User $user): array
    {
        $counts = array_fill_keys(['orders', 'messages', 'cart', 'wishlist', 'history', 'track', 'notifications'], 0);
        if (!$user || !$user->isBuyer()) {
            return $counts;
        }

        $orders = SellerOrder::whereHas('order', fn ($query) => $query->where('buyer_id', $user->id));
        $statuses = (clone $orders)->pluck('status');
        $counts['orders'] = $statuses->count();
        $counts['history'] = $statuses->filter(fn ($status) => in_array($status, ['delivered', 'completed', 'cancelled'], true))->count();
        $counts['track'] = $counts['orders'] - $counts['history'];
        $counts['cart'] = array_sum(array_column(app(\App\Services\BuyerCart::class)->all(), 'quantity'));
        $counts['wishlist'] = Wishlist::where('user_id', $user->id)->count();
        $counts['messages'] = SellerMessage::whereIn('seller_order_id', (clone $orders)->select('id'))
            ->where('sender_id', '!=', $user->id)->whereNull('read_at')->count();
        $counts['notifications'] = AppNotification::where('user_id', $user->id)->where('is_read', false)->count();

        return $counts;
    }
}
