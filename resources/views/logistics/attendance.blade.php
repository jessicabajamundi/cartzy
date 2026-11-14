@extends('layouts.logistics')
@section('title', 'Attendance (Time In / Time Out) | Logistics Hub | cartzy')
@section('page_eyebrow', 'COURIER & RIDER ATTENDANCE')
@section('page_title', 'Attendance (Time in / time out)')
@section('page_description', 'Couriers and riders open the Cartzy mobile app, tap Attendance (Time in / out), and scan this code. The first scan times them in, the next one times them out.')
@section('page_subtitle', 'Couriers and riders scan the hub QR code in the mobile app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-attendance.css') }}?v={{ filemtime(public_path('css/logistics-attendance.css')) }}">
@endpush
@php
    $fmtTime = fn ($time) => $time?->copy()->timezone($tz)->format('g:i A');
    $fmtWorked = fn (int $minutes) => intdiv($minutes, 60).'h '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).'m';
@endphp
@section('content')
<section class="att-workspace" aria-label="Courier and rider time in and time out">
    <div class="stats-grid dashboard-stats three">
        <div class="stat-card">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => 'users'])</span>
                <span>Live</span>
            </div>
            <span>Personnel on shift</span>
            <strong>{{ $onShift->count() }}</strong>
            <small>{{ $onShiftCouriers ?? 0 }} couriers · {{ $onShiftRiders ?? 0 }} riders clocked in</small>
        </div>
        <div class="stat-card">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => 'clock'])</span>
                <span>{{ $isToday ? 'Today' : $date->format('M j') }}</span>
            </div>
            <span>Recorded shifts</span>
            <strong>{{ $shifts->count() }}</strong>
            <small>{{ $todayCouriers ?? 0 }} couriers · {{ $todayRiders ?? 0 }} riders on {{ $date->format('M j, Y') }}</small>
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
                    <span class="eyebrow">SCAN FOR ATTENDANCE (TIME IN / OUT)</span>
                    <h2>{{ $provider->name }}</h2>
                    <p>Point the Courier or Rider mobile app scanner at this code.</p>
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
                        <p>Active couriers and riders currently clocked in</p>
                    </div>
                    <span class="badge good att-pill" id="att-on-shift-count">{{ $onShift->count() }}</span>
                </header>
                <ul class="att-on-shift">
                    @forelse($onShift as $shift)
                        <li>
                            <span class="avatar att-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($shift->rider->user->name ?? 'P', 0, 1)) }}</span>
                            <span class="att-on-shift-name">
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <strong>{{ $shift->rider->user->name ?? 'Personnel' }}</strong>
                                    @if(($shift->rider->courier_type ?? '') === 'first_mile')
                                        <span class="badge" style="background: #EEF2FF; color: #4338CA; font-size: 10px; font-weight: 700; padding: 2px 6px;">🚚 First Mile Courier</span>
                                    @else
                                        <span class="badge" style="background: #FEF3C7; color: #92400E; font-size: 10px; font-weight: 700; padding: 2px 6px;">🏍️ Last Mile Rider</span>
                                    @endif
                                </div>
                                <small>{{ $shift->rider->area ?: 'General area' }} · {{ $shift->rider->vehicle_type ? ucfirst($shift->rider->vehicle_type) : '' }} {{ $shift->rider->plate_no ? '('.$shift->rider->plate_no.')' : '' }}</small>
                            </span>
                            <span class="att-on-shift-time">In {{ $fmtTime($shift->time_in_at) }}<small>{{ $fmtWorked($shift->workedMinutes()) }}</small></span>
                        </li>
                    @empty
                        <li class="empty att-empty"><strong>No couriers or riders are timed in right now.</strong><p>Personnel will appear here as soon as they scan the hub QR code in the app.</p></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="panel att-panel att-log">
        <header class="panel-header att-panel-head" style="flex-wrap: wrap; gap: 12px;">
            <div>
                <h2>Attendance log</h2>
                <p>{{ $isToday ? 'Today, ' : '' }}{{ $date->format('l, M j, Y') }} · {{ $shifts->count() }} {{ Str::plural('shift', $shifts->count()) }}</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <form method="GET" action="{{ route('logistics.attendance') }}" class="att-date">
                    <input type="hidden" name="type" value="{{ $typeFilter ?? 'all' }}">
                    <label for="att-date">Day</label>
                    <input type="date" id="att-date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ now($tz)->format('Y-m-d') }}" onchange="this.form.submit()">
                    <noscript><button type="submit" class="button subtle att-button">Show</button></noscript>
                    @unless($isToday)<a class="button subtle att-link" href="{{ route('logistics.attendance', ['type' => $typeFilter ?? 'all']) }}">Today</a>@endunless
                </form>
            </div>
        </header>

        {{-- Type filter pills --}}
        <div style="padding: 10px 20px 0; display: flex; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid var(--line, #e2e8f0);">
            <a href="{{ route('logistics.attendance', ['date' => $date->format('Y-m-d'), 'type' => 'all']) }}"
               class="button {{ ($typeFilter ?? 'all') === 'all' ? '' : 'subtle' }}"
               style="min-height: 32px; padding: 4px 14px; font-size: 12px; border-radius: 20px;">
               All Personnel ({{ ($todayCouriers ?? 0) + ($todayRiders ?? 0) }})
            </a>
            <a href="{{ route('logistics.attendance', ['date' => $date->format('Y-m-d'), 'type' => 'first_mile']) }}"
               class="button {{ ($typeFilter ?? 'all') === 'first_mile' ? '' : 'subtle' }}"
               style="min-height: 32px; padding: 4px 14px; font-size: 12px; border-radius: 20px;">
               🚚 First Mile Couriers ({{ $todayCouriers ?? 0 }})
            </a>
            <a href="{{ route('logistics.attendance', ['date' => $date->format('Y-m-d'), 'type' => 'last_mile']) }}"
               class="button {{ ($typeFilter ?? 'all') === 'last_mile' ? '' : 'subtle' }}"
               style="min-height: 32px; padding: 4px 14px; font-size: 12px; border-radius: 20px;">
               🏍️ Last Mile Riders ({{ $todayRiders ?? 0 }})
            </a>
        </div>

        <div class="table-scroll att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr>
                        <th scope="col">Courier / Rider</th>
                        <th scope="col">Type & Vehicle</th>
                        <th scope="col">Time in</th>
                        <th scope="col">Time out</th>
                        <th scope="col">Hours worked</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        @php $stale = !$shift->time_out_at && $shift->time_in_at->lt(now()->subHours(\App\Services\RiderAttendanceService::MAX_SHIFT_HOURS)); @endphp
                        <tr>
                            <td>
                                <strong>{{ $shift->rider->user->name ?? 'Personnel' }}</strong>
                                <small>{{ $shift->rider->user->phone ?? ($shift->rider->area ?: 'General area') }}</small>
                            </td>
                            <td>
                                @if(($shift->rider->courier_type ?? '') === 'first_mile')
                                    <span class="badge" style="background: #EEF2FF; color: #4338CA; font-size: 11px; font-weight: 700;">🚚 First Mile</span>
                                @else
                                    <span class="badge" style="background: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 700;">🏍️ Last Mile</span>
                                @endif
                                <small>{{ ucfirst($shift->rider->vehicle_type ?? '') }} {{ $shift->rider->plate_no ? '· '.$shift->rider->plate_no : '' }}</small>
                            </td>
                            <td>{{ $fmtTime($shift->time_in_at) }}</td>
                            <td>
                                @if($shift->time_out_at){{ $fmtTime($shift->time_out_at) }}
                                @elseif($stale)<span class="badge warning att-tag warn">No time out</span>
                                @else<span class="badge good att-tag live">On shift</span>@endif
                            </td>
                            <td>{{ $stale ? '—' : $fmtWorked($shift->workedMinutes()) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty att-empty">No courier or rider timed in on this day.</td></tr>
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
