<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    /** The signed-in user's own account activity, newest first. */
    public function index(Request $request)
    {
        $data = $request->validate(['group' => ['nullable', Rule::in(ActivityLog::GROUPS)]]);

        $logs = ActivityLog::where('user_id', $request->user()->id)->inGroup($data['group'] ?? null)
            ->latest('created_at')->latest('id')->paginate(ActivityLog::PER_PAGE);

        return response()->json([
            'activity' => $logs->getCollection()->map->toFeedArray()->values(),
            'has_more' => $logs->hasMorePages(),
            'next_page' => $logs->hasMorePages() ? $logs->currentPage() + 1 : null,
        ]);
    }
}
