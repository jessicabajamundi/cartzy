<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a new notification for a specific user.
     */
    public static function notify(int $userId, string $type, string $title, string $message, ?string $link = null): AppNotification
    {
        return AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Retrieve all real notifications for a user.
     * If the user already has real orders in the system but no notification records yet,
     * generate them once from their real orders so their real history is represented.
     */
    public static function getUserNotifications(int $userId): Collection
    {
        $existing = AppNotification::where('user_id', $userId)->latest()->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        // Sync from real seller orders if user has placed orders before this feature was introduced
        $sellerOrders = SellerOrder::query()
            ->whereHas('order', fn ($q) => $q->where('buyer_id', $userId))
            ->with(['order', 'shipment'])
            ->latest('updated_at')
            ->get();

        if ($sellerOrders->isNotEmpty()) {
            foreach ($sellerOrders as $so) {
                $ref = $so->order->reference;
                $status = $so->status;

                [$title, $message, $type] = match (true) {
                    $status === 'delivered' || $status === 'completed' => [
                        'Order Delivered',
                        "Your order {$ref} has been safely delivered.",
                        'delivery',
                    ],
                    $status === 'shipped' || in_array(optional($so->shipment)->status, ['in_transit', 'out_for_delivery'], true) => [
                        'Parcel On The Way',
                        "Order {$ref} is in transit with your courier.",
                        'delivery',
                    ],
                    $so->order->payment_status === 'pending' && $so->order->payment_method === 'wallet' => [
                        'Payment Pending',
                        "Please complete payment for order {$ref}.",
                        'payment',
                    ],
                    default => [
                        'Order Confirmed',
                        "Your order {$ref} has been placed and is being prepared.",
                        'order',
                    ],
                };

                AppNotification::create([
                    'user_id'    => $userId,
                    'type'       => $type,
                    'title'      => $title,
                    'message'    => $message,
                    'link'       => route('buyer.dashboard', ['tab' => 'orders']),
                    'is_read'    => false,
                    'created_at' => $so->created_at,
                    'updated_at' => $so->updated_at,
                ]);
            }

            return AppNotification::where('user_id', $userId)->latest()->get();
        }

        // If the user has 0 orders and 0 activities, return empty (no fake default notifications)
        return collect();
    }

    /**
     * Mark a single notification as read.
     */
    public static function markAsRead(int $notificationId, int $userId): bool
    {
        return AppNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['is_read' => true]) > 0;
    }

    /**
     * Mark all notifications for a user as read.
     */
    public static function markAllAsRead(int $userId): int
    {
        return AppNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
