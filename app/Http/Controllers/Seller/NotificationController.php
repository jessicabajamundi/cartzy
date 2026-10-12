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
        $data = SellerNotifications::feed($request->user());

        if (!$request->expectsJson()) {
            return view('seller.notifications', $data + ['shop' => $request->user()->seller]);
        }

        return response()->json([
            'count' => $data['notificationCount'],
            'html' => view('seller.partials.notification-items', $data)->render(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function read(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }
}
