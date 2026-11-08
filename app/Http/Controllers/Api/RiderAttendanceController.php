<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\RiderAttendance;
use App\Services\RiderAttendanceException;
use App\Services\RiderAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rider app: time in / time out by scanning the hub's QR code (see RiderAttendanceService).
 */
class RiderAttendanceController extends Controller
{
    public function __construct(private RiderAttendanceService $attendance)
    {
    }

    /** GET /api/rider/attendance: whether the rider is on shift, plus their recent shifts. */
    public function show(Request $request): JsonResponse
    {
        $rider = $this->rider($request);
        if (!$rider) {
            return response()->json(['message' => 'No rider profile is linked to this account.'], 404);
        }

        $open = $this->attendance->openShift($rider);
        $recent = RiderAttendance::where('rider_id', $rider->id)->latest('time_in_at')->take(10)->get();

        return response()->json([
            'on_shift' => (bool) $open,
            'current' => $open ? $this->shift($open) : null,
            'recent' => $recent->map(fn (RiderAttendance $shift) => $this->shift($shift))->values(),
        ]);
    }

    /** POST /api/rider/attendance/scan {code}: times the rider in, or out if they are on shift. */
    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:200']]);
        $rider = $this->rider($request);
        if (!$rider) {
            return response()->json(['message' => 'No rider profile is linked to this account.'], 404);
        }

        try {
            ['action' => $action, 'attendance' => $shift] = $this->attendance->scan($rider, $data['code']);
        } catch (RiderAttendanceException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        $shift->loadMissing('logisticsProvider');
        $hub = $shift->logisticsProvider->name ?? 'your hub';
        $time = ($action === 'time_in' ? $shift->time_in_at : $shift->time_out_at)->copy()->timezone('Asia/Manila')->format('g:i A');

        return response()->json([
            'action' => $action,
            'message' => $action === 'time_in'
                ? "Timed in at {$hub} at {$time}. Have a safe shift!"
                : "Timed out at {$time}. You worked ".$this->duration($shift->workedMinutes()).'.',
            'on_shift' => $action === 'time_in',
            'shift' => $this->shift($shift),
        ], $action === 'time_in' ? 201 : 200);
    }

    private function rider(Request $request): ?Rider
    {
        return Rider::where('user_id', $request->user()->id)->first();
    }

    private function shift(RiderAttendance $shift): array
    {
        return [
            'id' => $shift->id,
            'time_in_at' => $shift->time_in_at->toIso8601String(),
            'time_out_at' => $shift->time_out_at?->toIso8601String(),
            'worked_minutes' => $shift->workedMinutes(),
        ];
    }

    private function duration(int $minutes): string
    {
        return intdiv($minutes, 60).'h '.($minutes % 60).'m';
    }
}
