<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerNotifications
{
    public static function feed(User $user, int $page = 1, string $filter = 'all'): array
    {
        $archived = AppNotification::where('user_id', $user->id)->whereNotNull('archived_at');
        $notifications = AppNotification::where('user_id', $user->id)->whereNull('archived_at');
        $stock = ProductVariant::where('is_active', true)->atStockAlertLevel()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $user->seller?->id ?? 0)->where('is_active', true));
        $orderTypes = ['order_update', 'order_cancellation', 'cancellation_approved', 'cancellation_rejected'];
        $unread = (clone $notifications)->where('is_read', false)->count();
        $stockCount = (clone $stock)->count();
        $counts = [
            'all' => $notifications->count() + $stockCount,
            'unread' => $unread, 'stock' => $stockCount,
            'orders' => (clone $notifications)->whereIn('type', $orderTypes)->count(),
            'products' => (clone $notifications)->where('type', 'product_update')->count(),
            'archived' => (clone $archived)->count(),
        ];
        if (!array_key_exists($filter, $counts)) $filter = 'all';
        if ($filter === 'archived') $notifications = $archived;
        if ($filter === 'unread') $notifications->where('is_read', false);
        if ($filter === 'orders') $notifications->whereIn('type', $orderTypes);
        if ($filter === 'products') $notifications->where('type', 'product_update');
        $total = $counts[$filter];
        $page = min(max(1, $page), max(1, (int) ceil($total / 20)));
        $offset = ($page - 1) * 20;
        $visibleStockCount = in_array($filter, ['all', 'stock']) ? $stockCount : 0;
        $stockItems = $offset < $visibleStockCount
            ? (clone $stock)->with('product')->orderBy('stock')->orderBy('id')->skip($offset)->take(20)->get()
            : collect();
        $slots = 20 - $stockItems->count();
        $updates = $filter !== 'stock' && $slots > 0
            ? $notifications->latest('id')->skip(max(0, $offset - $visibleStockCount))->take($slots)->get()
            : collect();
        $paginator = new LengthAwarePaginator($stockItems->concat($updates), $total, 20, $page, [
            'path' => route('seller.notifications'), 'query' => ['filter' => $filter],
        ]);
        return [
            'notificationCount' => $unread + $stockCount,
            'notificationUnreadCount' => $unread,
            'notificationFilter' => $filter,
            'notificationFilterCounts' => $counts,
            'notificationPaginator' => $paginator,
            'sellerNotifications' => $updates,
            'sellerStockNotifications' => $stockItems,
        ];
    }
}
