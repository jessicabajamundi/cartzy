<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\ProductVariant;
use App\Models\User;

class SellerNotifications
{
    public static function feed(User $user): array
    {
        $notifications = AppNotification::where('user_id', $user->id);
        $stock = ProductVariant::where('is_active', true)->where('stock', '<=', 10)
            ->whereHas('product', fn ($query) => $query->where('seller_id', $user->seller?->id ?? 0)->where('is_active', true));

        return [
            'notificationCount' => (clone $notifications)->where('is_read', false)->count() + (clone $stock)->count(),
            'sellerNotifications' => $notifications->orderBy('is_read')->latest('id')->limit(30)->get(),
            'sellerStockNotifications' => $stock->with('product')->orderBy('stock')->orderBy('id')->limit(20)->get(),
        ];
    }
}
