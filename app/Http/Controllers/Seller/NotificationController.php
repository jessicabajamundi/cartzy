<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\SellerNotifications;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['page' => 'nullable|integer|min:1', 'filter' => 'nullable|in:all,unread,stock,orders,products,archived']);
        $data = SellerNotifications::feed($request->user(), (int) $request->input('page', 1), $request->input('filter', 'all'));

        if (!$request->expectsJson()) {
            return view('seller.notifications', $data + ['shop' => $request->user()->seller]);
        }

        return response()->json([
            'count' => $data['notificationCount'],
            'unread' => $data['notificationUnreadCount'],
            'filterCounts' => $data['notificationFilterCounts'],
            'html' => view('seller.partials.notification-items', $data)->render(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function read(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function archive(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)->update(['is_read' => true, 'archived_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function restore(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)->update(['archived_at' => null]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('archived_at')->where('is_read', false)->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
