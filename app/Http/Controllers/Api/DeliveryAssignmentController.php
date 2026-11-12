<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Rider, Shipment};
use App\Services\{DeliveryAssignment, RiderAttendanceService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeliveryAssignmentController extends Controller
{
    public function __construct(private DeliveryAssignment $assignment) {}

    private function parcel(Request $request, int $id): Shipment
    {
        $query = Shipment::query();
        if (!$request->user()->isAdmin()) {
            $query->whereHas('logisticsProvider', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'approved'));
        }
        return $query->findOrFail($id);
    }

    public function candidates(Request $request, int $id)
    {
        $parcel = $this->parcel($request, $id);
        $rows = $this->assignment->candidates($parcel)->map(fn ($row) => [
            'id' => $row['rider']->id, 'name' => $row['rider']->user->name,
            'match' => $row['method'], 'active_load' => $row['rider']->active_load,
        ]);
        return response()->json(['shipment_id' => $parcel->id, 'assigned_rider_id' => $parcel->rider_id,
            'recommended_rider_id' => $rows->first()['id'] ?? null, 'riders' => $rows]);
    }

    public function autoAssign(Request $request, int $id)
    {
        $result = $this->assignment->assign($this->parcel($request, $id), actorId: $request->user()->id);
        return response()->json(['assigned' => $result !== null, 'shipment' => $result,
            'message' => $result ? 'Assignment saved (existing assignments are preserved).' : 'No assignment made: parcel is not ready or no online rider is available.']);
    }

    public function override(Request $request, int $id)
    {
        $data = $request->validate(['rider_id' => ['required', 'integer']]);
        return response()->json(['shipment' => $this->assignment->assign($this->parcel($request, $id), $data['rider_id'], $request->user()->id)]);
    }

    public function designation(Request $request, int $id)
    {
        $data = $request->validate(['barangay' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:150'], 'province' => ['required', 'string', 'max:150']]);
        $query = Rider::query();
        if (!$request->user()->isAdmin()) {
            $query->whereHas('logisticsProvider', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'approved'));
        }
        $rider = $query->findOrFail($id);
        $rider->update(['designated_place' => $data]);
        return response()->json(['rider_id' => $rider->id, 'designated_place' => $rider->designated_place]);
    }

    public function availability(Request $request)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['online', 'offline'])]]);
        $rider = DB::transaction(function () use ($request, $data) {
            $rider = Rider::where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($rider->status === 'approved' && $rider->is_active, 403, 'Your rider profile must be approved and active.');
            abort_unless($rider->logisticsProvider?->status === 'approved', 403, 'An approved logistics hub is required.');
            $rider->update(['online_until' => $data['status'] === 'online' ? now()->addHours(RiderAttendanceService::MAX_SHIFT_HOURS) : null]);
            return $rider;
        });
        if ($data['status'] === 'online') $this->assignment->assignWaiting($rider->logistics_provider_id);
        return response()->json(['status' => $data['status'], 'online_until' => $rider->online_until]);
    }
}
