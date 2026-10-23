<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = in_array($request->query('filter'), ['unread', 'archived'], true) ? $request->query('filter') : 'all';
        $unreadOnly = $filter === 'unread';
        $inbox = AppNotification::where('user_id', $request->user()->id)->whereNull('archived_at');
        $totalCount = (clone $inbox)->count();
        $unreadCount = (clone $inbox)->where('is_read', false)->count();
        $archivedCount = AppNotification::where('user_id', $request->user()->id)->whereNotNull('archived_at')->count();
        $notifications = ($filter === 'archived'
                ? AppNotification::where('user_id', $request->user()->id)->whereNotNull('archived_at')
                : $inbox->when($unreadOnly, fn ($query) => $query->where('is_read', false)))
            ->latest()->orderByDesc('id')->paginate(15)->withQueryString();

        return view('logistics.notifications', compact('notifications', 'filter', 'unreadOnly', 'totalCount', 'unreadCount', 'archivedCount'));
    }

    public function read(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)
            ->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('archived_at')->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function archive(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)
            ->update(['is_read' => true, 'archived_at' => now()]);

        return back()->with('success', 'Notification archived.');
    }

    public function restore(Request $request, int $id)
    {
        AppNotification::where('user_id', $request->user()->id)->findOrFail($id)
            ->update(['archived_at' => null]);

        return back()->with('success', 'Notification moved back to your inbox.');
    }
}
