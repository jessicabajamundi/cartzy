<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all active notifications for the authenticated buyer.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ensure real notifications are populated from existing orders if needed
        NotificationService::getUserNotifications($user->id);

        $notifications = AppNotification::where('user_id', $user->id)
            ->whereNull('archived_at')
            ->latest()
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        $formatted = $notifications->map(function (AppNotification $n) {
            $tag = $this->determineTag($n->type, $n->title);

            // Extract order reference if present (e.g. ORD-2026-56986, ORD-MTR-101)
            $orderRef = null;
            if (preg_match('/ORD-[A-Z0-9-]+/', $n->title . ' ' . $n->message, $matches)) {
                $orderRef = $matches[0];
            }

            return [
                'id'              => $n->id,
                'type'            => $n->type,
                'title'           => $n->title,
                'message'         => $n->message,
                'body'            => $n->message,
                'tag'             => $tag,
                'category'        => $tag,
                'read'            => (bool) $n->is_read,
                'is_read'         => (bool) $n->is_read,
                'link'            => $n->link,
                'order_reference' => $orderRef,
                'time'            => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                'created_at'      => $n->created_at ? $n->created_at->toIso8601String() : null,
            ];
        });

        $counts = [
            'all'    => $formatted->count(),
            'orders' => $formatted->where('tag', 'orders')->count(),
            'promos' => $formatted->where('tag', 'promos')->count(),
            'system' => $formatted->where('tag', 'system')->count(),
        ];

        return response()->json([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'counts'        => $counts,
            'notifications' => $formatted->values(),
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $unread = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('archived_at')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unread,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        NotificationService::markAsRead($id, $userId);

        $unread = AppNotification::where('user_id', $userId)
            ->whereNull('archived_at')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unread,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        NotificationService::markAllAsRead($userId);

        return response()->json([
            'success'      => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Archive/dismiss a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        AppNotification::where('user_id', $userId)
            ->where('id', $id)
            ->update([
                'is_read'     => true,
                'archived_at' => now(),
            ]);

        $unread = AppNotification::where('user_id', $userId)
            ->whereNull('archived_at')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unread,
        ]);
    }

    /**
     * Categorize notification into one of: 'orders', 'promos', 'system'.
     */
    private function determineTag(?string $type, ?string $title): string
    {
        $t = strtolower($type ?? '');
        $titleLower = strtolower($title ?? '');

        // Promos / Vouchers / Discounts
        if (
            in_array($t, ['voucher', 'promo', 'promotion', 'discount', 'sale', 'reward'], true) ||
            str_contains($titleLower, 'voucher') ||
            str_contains($titleLower, 'discount') ||
            str_contains($titleLower, 'promo') ||
            str_contains($titleLower, 'flash sale')
        ) {
            return 'promos';
        }

        // System / Admin / Account
        if (
            in_array($t, ['system', 'admin_message', 'registration_approved', 'registration_rejected', 'product_update', 'announcement', 'security'], true) ||
            str_contains($t, 'admin') ||
            str_contains($titleLower, 'administration') ||
            str_contains($titleLower, 'account')
        ) {
            return 'system';
        }

        // Default to orders (order, delivery, order_delivered, payment, cancellation_*, dispute, pickup_*)
        return 'orders';
    }
}
