<?php

namespace App\Services;

use App\Models\LogisticsProvider;
use App\Models\Rider;
use App\Models\RiderAttendance;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;

/**
 * Rider time in / time out by scanning the hub's QR code.
 *
 * The hub screen shows a new code every 25 seconds. A code is "CARTZY-ATT.<hub id>.<window>.<signature>",
 * where the window is the 25-second slot it belongs to and the signature is an HMAC with the app key,
 * so codes can't be made up and a screenshot stops working once its slot has passed.
 */
class RiderAttendanceService
{
    public const WINDOW_SECONDS = 25;

    /** A code from the slot that just ended still counts for this long, for scans made right at the changeover. */
    public const GRACE_SECONDS = 5;

    /** An open shift older than this was never timed out; the next scan starts a new shift. */
    public const MAX_SHIFT_HOURS = 16;

    private const PREFIX = 'CARTZY-ATT';

    public function currentWindow(?int $timestamp = null): int
    {
        return intdiv($timestamp ?? now()->getTimestamp(), self::WINDOW_SECONDS);
    }

    /** Seconds until the code on screen changes. */
    public function secondsLeft(?int $timestamp = null): int
    {
        $timestamp ??= now()->getTimestamp();

        return self::WINDOW_SECONDS - ($timestamp % self::WINDOW_SECONDS);
    }

    public function code(LogisticsProvider $hub, ?int $window = null): string
    {
        $window ??= $this->currentWindow();

        return implode('.', [self::PREFIX, $hub->id, $window, $this->signature($hub->id, $window)]);
    }

    public function qrSvg(string $code, int $size = 280): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd()));

        return $writer->writeString($code);
    }

    /**
     * Times the rider in, or out if they are on shift, from a scanned code.
     *
     * @return array{action: string, attendance: RiderAttendance}
     *
     * @throws RiderAttendanceException when the code is invalid or expired, or the rider can't use this hub
     */
    public function scan(Rider $rider, string $code): array
    {
        [$hubId, $window] = $this->verify($code);

        if ((int) $rider->logistics_provider_id !== $hubId) {
            throw new RiderAttendanceException('This QR code belongs to another hub. Scan the code at your own hub.', 403);
        }
        if ($rider->status !== 'approved') {
            throw new RiderAttendanceException('Your rider account must be approved by your hub before you can time in.', 403);
        }

        return DB::transaction(function () use ($rider, $hubId, $window) {
            // Lock the rider so two scans arriving together can't both time in.
            Rider::whereKey($rider->id)->lockForUpdate()->first();
            $latest = RiderAttendance::where('rider_id', $rider->id)->latest('time_in_at')->latest('id')->first();

            if ($latest && in_array($window, [$latest->time_in_window, $latest->time_out_window], true)) {
                throw new RiderAttendanceException('You already scanned this code. Wait for the next code to time '.($latest->time_out_at ? 'in' : 'out').' again.', 409);
            }

            $open = $latest && !$latest->time_out_at && $latest->time_in_at->gt(now()->subHours(self::MAX_SHIFT_HOURS)) ? $latest : null;
            if ($open) {
                $open->update(['time_out_at' => now(), 'time_out_window' => $window]);

                return ['action' => 'time_out', 'attendance' => $open->fresh()];
            }

            $attendance = RiderAttendance::create([
                'rider_id' => $rider->id,
                'logistics_provider_id' => $hubId,
                'time_in_at' => now(),
                'time_in_window' => $window,
            ]);

            return ['action' => 'time_in', 'attendance' => $attendance];
        });
    }

    /** The rider's shift that is still open, if any. */
    public function openShift(Rider $rider): ?RiderAttendance
    {
        return RiderAttendance::where('rider_id', $rider->id)
            ->whereNull('time_out_at')
            ->where('time_in_at', '>', now()->subHours(self::MAX_SHIFT_HOURS))
            ->latest('time_in_at')
            ->first();
    }

    /** @return array{0: int, 1: int} hub id and window of a genuine, current code */
    private function verify(string $code): array
    {
        $parts = explode('.', trim($code));
        if (count($parts) !== 4 || $parts[0] !== self::PREFIX || !ctype_digit($parts[1]) || !ctype_digit($parts[2])) {
            throw new RiderAttendanceException('This is not a cartzy time in / time out code. Scan the QR code on the hub screen.', 422);
        }

        [, $hubId, $window, $signature] = $parts;
        $hubId = (int) $hubId;
        $window = (int) $window;
        if (!hash_equals($this->signature($hubId, $window), $signature)) {
            throw new RiderAttendanceException('This QR code is not valid. Scan the code on the hub screen.', 422);
        }

        $now = now()->getTimestamp();
        $current = $this->currentWindow($now);
        $justEnded = $window === $current - 1 && ($now % self::WINDOW_SECONDS) < self::GRACE_SECONDS;
        if ($window !== $current && !$justEnded) {
            throw new RiderAttendanceException('This QR code has expired. Codes change every '.self::WINDOW_SECONDS.' seconds, so scan the code on the hub screen, not a photo of it.', 422);
        }

        return [$hubId, $window];
    }

    private function signature(int $hubId, int $window): string
    {
        return substr(hash_hmac('sha256', "rider-attendance|{$hubId}|{$window}", (string) config('app.key')), 0, 24);
    }
}
