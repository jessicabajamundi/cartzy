@extends('layouts.logistics')
@section('title', 'Time In / Time Out | Logistics Hub | cartzy')
@section('page_eyebrow', 'RIDER ATTENDANCE')
@section('page_title', 'Time in / time out')
@section('page_description', 'Riders open the Cartzy Rider app, tap Time in / out, and scan this code. The first scan times them in, the next one times them out.')
@section('page_subtitle', 'Riders scan the hub QR code in the Rider app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-attendance.css') }}?v={{ filemtime(public_path('css/logistics-attendance.css')) }}">
@endpush
@php
    $fmtTime = fn ($time) => $time?->copy()->timezone($tz)->format('g:i A');
    $fmtWorked = fn (int $minutes) => intdiv($minutes, 60).'h '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).'m';
@endphp
@section('content')
<section class="att-workspace" aria-label="Rider time in and time out">
    <div class="stats-grid dashboard-stats three">
        <div class="stat-card">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => 'users'])</span>
                <span>Live</span>
            </div>
            <span>Riders on shift</span>
            <strong>{{ $onShift->count() }}</strong>
            <small>Currently timed in at {{ $provider->name }}</small>
        </div>
        <div class="stat-card">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => 'clock'])</span>
                <span>{{ $isToday ? 'Today' : $date->format('M j') }}</span>
            </div>
            <span>Recorded shifts</span>
            <strong>{{ $shifts->count() }}</strong>
            <small>{{ $date->format('l, M j, Y') }}</small>
        </div>
        <div class="stat-card">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => 'shield'])</span>
                <span>Security</span>
            </div>
            <span>Dynamic QR refresh</span>
            <strong>{{ $windowSeconds }}s</strong>
            <small>Rotates automatically to prevent screenshot reuse</small>
        </div>
    </div>

    <div class="two-columns att-grid">
        {{-- The QR code changes every 25 seconds, so a photo or screenshot of it stops working --}}
        <div class="panel att-qr-card" id="att-qr-card" data-qr-url="{{ route('logistics.attendance.qr') }}" data-seconds-left="{{ $secondsLeft }}" data-window="{{ $windowSeconds }}" data-version="{{ $version }}">
            <header class="panel-header att-qr-top">
                <div>
                    <span class="eyebrow">SCAN TO TIME IN / OUT</span>
                    <h2>{{ $provider->name }}</h2>
                    <p>Point the Rider mobile app scanner at this code.</p>
                </div>
                <button type="button" class="button subtle att-button" id="att-fullscreen" aria-pressed="false">⛶ Full screen</button>
            </header>
            <div class="padded" style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 14px;">
                <div class="att-qr" id="att-qr" role="img" aria-label="Time in and time out QR code for {{ $provider->name }}">{!! $qrSvg !!}</div>
                <div class="att-countdown" aria-hidden="true" style="width: 100%; max-width: 300px;">
                    <div class="att-countdown-bar"><span id="att-countdown-fill"></span></div>
                    <p>New code in <strong id="att-countdown-text">{{ $secondsLeft }}s</strong></p>
                </div>
                <p class="att-qr-note">This code changes every {{ $windowSeconds }} seconds. Photos and screenshots of it won't work.</p>
                <p class="att-status" id="att-status" role="status" aria-live="polite"></p>
            </div>
        </div>

        <div class="att-side">
            <div class="panel att-panel">
                <header class="panel-header att-panel-head">
                    <div>
                        <h2>On shift now</h2>
                        <p>Active riders currently clocked in</p>
                    </div>
                    <span class="badge good att-pill" id="att-on-shift-count">{{ $onShift->count() }}</span>
                </header>
                <ul class="att-on-shift">
                    @forelse($onShift as $shift)
                        <li>
                            <span class="avatar att-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($shift->rider->user->name ?? 'R', 0, 1)) }}</span>
                            <span class="att-on-shift-name"><strong>{{ $shift->rider->user->name ?? 'Rider' }}</strong><small>{{ $shift->rider->area ?: 'General area' }}</small></span>
                            <span class="att-on-shift-time">In {{ $fmtTime($shift->time_in_at) }}<small>{{ $fmtWorked($shift->workedMinutes()) }}</small></span>
                        </li>
                    @empty
                        <li class="empty att-empty"><strong>No riders are timed in right now.</strong><p>Riders will appear here as soon as they scan the hub QR code.</p></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="panel att-panel att-log">
        <header class="panel-header att-panel-head">
            <div>
                <h2>Attendance log</h2>
                <p>{{ $isToday ? 'Today, ' : '' }}{{ $date->format('l, M j, Y') }} · {{ $shifts->count() }} {{ Str::plural('shift', $shifts->count()) }}</p>
            </div>
            <form method="GET" action="{{ route('logistics.attendance') }}" class="att-date">
                <label for="att-date">Day</label>
                <input type="date" id="att-date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ now($tz)->format('Y-m-d') }}" onchange="this.form.submit()">
                <noscript><button type="submit" class="button subtle att-button">Show</button></noscript>
                @unless($isToday)<a class="button subtle att-link" href="{{ route('logistics.attendance') }}">Today</a>@endunless
            </form>
        </header>
        <div class="table-scroll att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr><th scope="col">Rider</th><th scope="col">Time in</th><th scope="col">Time out</th><th scope="col">Hours worked</th></tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        @php $stale = !$shift->time_out_at && $shift->time_in_at->lt(now()->subHours(\App\Services\RiderAttendanceService::MAX_SHIFT_HOURS)); @endphp
                        <tr>
                            <td><strong>{{ $shift->rider->user->name ?? 'Rider' }}</strong><small>{{ $shift->rider->plate_no ?: ($shift->rider->vehicle_type ?: '') }}</small></td>
                            <td>{{ $fmtTime($shift->time_in_at) }}</td>
                            <td>
                                @if($shift->time_out_at){{ $fmtTime($shift->time_out_at) }}
                                @elseif($stale)<span class="badge warning att-tag warn">No time out</span>
                                @else<span class="badge good att-tag live">On shift</span>@endif
                            </td>
                            <td>{{ $stale ? '—' : $fmtWorked($shift->workedMinutes()) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty att-empty">No rider timed in on this day.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
(function () {
    const card = document.getElementById('att-qr-card');
    if (!card) return;
    const qr = document.getElementById('att-qr');
    const fill = document.getElementById('att-countdown-fill');
    const text = document.getElementById('att-countdown-text');
    const status = document.getElementById('att-status');
    const windowSeconds = Number(card.dataset.window);
    let deadline = Date.now() + Number(card.dataset.secondsLeft) * 1000;
    let loading = false;
    let version = card.dataset.version;

    function tick() {
        const left = Math.max(0, (deadline - Date.now()) / 1000);
        fill.style.width = (left / windowSeconds * 100) + '%';
        text.textContent = Math.ceil(left) + 's';
        if (left <= 0) refresh();
    }

    // Ask the server for the current code; it also says how long that code lasts, so the clocks stay in step.
    async function refresh() {
        if (loading) return;
        loading = true;
        try {
            const response = await fetch(card.dataset.qrUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!response.ok) throw new Error(response.status);
            const data = await response.json();
            qr.innerHTML = data.svg;
            deadline = Date.now() + data.seconds_left * 1000;
            status.textContent = '';
            card.classList.remove('is-offline');
            if (data.version !== version && !document.fullscreenElement) {
                // A rider timed in or out: reload so the lists show it (not while the code is shown full screen).
                window.location.reload();
            }
        } catch (error) {
            // Never leave an old code on screen: hide it until the server answers again.
            qr.innerHTML = '';
            card.classList.add('is-offline');
            status.textContent = 'Can\'t reach the server. Trying again…';
            deadline = Date.now() + 3000;
        } finally {
            loading = false;
        }
    }

    setInterval(tick, 250);
    tick();
    // Coming back to a tab that slept: get a fresh code straight away.
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });

    const fullscreen = document.getElementById('att-fullscreen');
    if (!card.requestFullscreen) { fullscreen.hidden = true; return; }
    fullscreen.addEventListener('click', () => {
        if (document.fullscreenElement) document.exitFullscreen(); else card.requestFullscreen();
    });
    document.addEventListener('fullscreenchange', () => {
        const on = document.fullscreenElement === card;
        fullscreen.setAttribute('aria-pressed', on ? 'true' : 'false');
        fullscreen.textContent = on ? '✕ Exit full screen' : '⛶ Full screen';
    });
})();
</script>
@endpush
