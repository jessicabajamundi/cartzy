@extends('layouts.logistics')

@section('title', 'Dashboard | Logistics Hub | cartzy')
@section('page_title', 'Logistics Hub Dashboard')
@section('page_description', 'A clear view of your hub operations, rider fleet, and deliveries that need attention.')
@section('page_subtitle', 'All figures reflect recorded hub activity in Philippine Standard Time.')

@section('content')
<div class="operations-dashboard">
    <section class="overview-banner">
        <div class="overview-copy">
            <span class="eyebrow"><span class="welcome-dot" aria-hidden="true"></span> YOUR HUB, AT A GLANCE</span>
            <h2>Welcome back, {{ auth()->user()->name }}.</h2>
            <p>{{ $hub->business_name ?? $hub->name }} &mdash; a little oversight for smoother pickups, sorting, and deliveries.<br>Everything you need to keep your Cartzy hub moving.</p>
            <a class="button" href="{{ route('logistics.monitoring') }}">Monitor deliveries @include('admin.partials.icon', ['name' => 'arrow'])</a>
        </div>
        <a class="review-spotlight" href="{{ route('logistics.hub') }}">
            <span class="spotlight-heading">@include('admin.partials.icon', ['name' => 'box']) Hub desk</span>
            <strong>{{ number_format($stats['parcels_in_hub']) }}</strong>
            <span>parcels currently in hub</span>
            <span class="review-caption">{{ $stats['pending_applications'] > 0 ? $stats['pending_applications'].' rider application(s) awaiting review.' : 'Receive, sort, and assign parcels in one place.' }}</span>
            <span class="spotlight-action">Open hub desk @include('admin.partials.icon', ['name' => 'arrow'])</span>
        </a>
    </section>

    {{-- Where this hub is and the area it covers --}}
    <section class="panel hub-coverage" aria-labelledby="hub-coverage-title">
        <style>
            .hub-coverage{padding:20px 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
            .hub-coverage h2{font-size:15px;margin:4px 0 6px}
            .hub-coverage p{font-size:13px;margin:0}
            .hub-coverage .muted{color:var(--muted);font-size:12px}
            .hub-coverage ul{list-style:none;margin:6px 0 0;padding:0;display:grid;gap:6px}
            .hub-coverage li{display:flex;justify-content:space-between;gap:10px;font-size:12px;border-bottom:1px solid var(--line);padding-bottom:5px}
            .hub-coverage .chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:6px}
            .hub-coverage .chip{font-size:11px;padding:3px 9px;border-radius:20px;background:var(--accent-light);color:var(--accent)}
        </style>
        <div>
            <span class="eyebrow">HUB LOCATION</span>
            <h2 id="hub-coverage-title">{{ $hub->logisticsProvider?->name ?? $hub->name }}</h2>
            @if($coverage['location'])
                <p>{{ $coverage['location'] }}</p>
                <p class="muted">Sellers near this address see your hub recommended first.</p>
            @else
                <p class="muted">No office address yet, so sellers can't see that you're near them. <a href="{{ route('logistics.account') }}">Add your address</a></p>
            @endif
        </div>
        <div>
            <span class="eyebrow">SERVICE AREA</span>
            @if($coverage['regions'])
                <div class="chips">@foreach($coverage['regions'] as $region)<span class="chip">{{ $region }}</span>@endforeach</div>
            @else
                <p class="muted">No service regions chosen.</p>
            @endif
        </div>
        <div>
            <span class="eyebrow">WHERE YOUR RIDERS LIVE</span>
            @if($coverage['rider_areas'])
                <ul>
                    @foreach(array_slice($coverage['rider_areas'], 0, 6) as $area)
                        <li><span>{{ $area['label'] }}</span><span class="muted">@if($area['first_mile']){{ $area['first_mile'] }} courier{{ $area['first_mile'] > 1 ? 's' : '' }}@endif{{ $area['first_mile'] && $area['last_mile'] ? ' · ' : '' }}@if($area['last_mile']){{ $area['last_mile'] }} rider{{ $area['last_mile'] > 1 ? 's' : '' }}@endif</span></li>
                    @endforeach
                </ul>
            @else
                <p class="muted">None of your riders has added a home address yet.</p>
            @endif
            @if($coverage['riders_without_area'] > 0)
                <p class="muted" style="margin-top:6px">{{ $coverage['riders_without_area'] }} {{ \Illuminate\Support\Str::plural('rider', $coverage['riders_without_area']) }} without a home address can't be matched by barangay.</p>
            @endif
        </div>
    </section>

    {{-- Operations Date Filter Bar --}}
    <section class="panel operations-filter-card" aria-label="Date range filter">
        <div class="operations-filter-left">
            <div class="operations-filter-indicator">
                <span class="metric-icon" aria-hidden="true">@include('admin.partials.icon', ['name' => 'clock'])</span>
                <div class="operations-filter-indicator-text">
                    <span>OPERATIONS DATE FILTER</span>
                    <strong>{{ $filterLabel }}</strong>
                </div>
            </div>

            <nav class="operations-preset-list" aria-label="Quick date presets">
                @foreach([
                    'today'     => 'Today',
                    'yesterday' => 'Yesterday',
                    'week'      => 'Last 7 Days',
                    'month'     => 'This Month',
                ] as $presetKey => $presetLabel)
                    <a href="{{ route('logistics.dashboard', ['preset' => $presetKey]) }}"
                       class="operations-preset-btn {{ $activePreset === $presetKey ? 'is-active' : '' }}" @if($activePreset === $presetKey) aria-current="true" @endif>
                        {{ $presetLabel }}
                    </a>
                @endforeach
            </nav>
        </div>

        <form method="GET" action="{{ route('logistics.dashboard') }}" class="operations-custom-form">
            <div class="operations-date-field">
                <label for="start_date">From</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate->toDateString() }}" class="operations-date-input" required>
            </div>
            <div class="operations-date-field">
                <label for="end_date">To</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate->toDateString() }}" class="operations-date-input" required>
            </div>
            <button type="submit" class="button operations-filter-submit-btn">
                <span>Filter</span>
            </button>
            @if(request()->has('start_date') || request()->has('end_date') || (request()->has('preset') && request('preset') !== 'today'))
                <a href="{{ route('logistics.dashboard') }}" class="button subtle operations-reset-link" title="Reset date filter to today">
                    Reset
                </a>
            @endif
        </form>
    </section>

    @php
        $periodSuffix = $isToday ? 'today' : 'in period';
        $metrics = [
            ['Active riders', 'Fleet', $stats['active_riders'], 'of '.$stats['total_riders'].' registered riders', 'users', 'logistics.riders'],
            ['Pickups ' . $periodSuffix, 'First mile', $stats['pickups_today'], $isToday ? 'Scheduled for today' : 'Scheduled in period', 'truck', 'logistics.pickups'],
            ['Parcels in hub', 'Inventory', $stats['parcels_in_hub'], 'Current hub inventory', 'box', 'logistics.parcels'],
            ['Delivered ' . $periodSuffix, 'Last mile', $stats['delivered_today'], $isToday ? 'Completed deliveries today' : 'Delivered in period', 'shield', 'logistics.monitoring'],
            ['Hub efficiency', 'Rate', $stats['hub_efficiency'].'%', 'Delivery completion rate', 'chart', 'logistics.reports'],
        ];
    @endphp
    <div class="section-heading"><div><span class="eyebrow">PERFORMANCE</span><h2>Hub overview</h2></div><span class="muted">{{ $filterLabel }}</span></div>
    <div class="stats-grid dashboard-stats reports-kpis" aria-label="Hub metrics for selected period">
        @foreach($metrics as [$label, $tag, $value, $detail, $icon, $destination])
            <a class="stat-card" href="{{ route($destination) }}">
                <div class="metric-heading">
                    <span class="metric-icon">@include('admin.partials.icon', ['name' => $icon])</span>
                    <span>{{ $tag }}</span>
                </div>
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
                <small>{{ $detail }}</small>
            </a>
        @endforeach
    </div>

    <div class="section-heading quick-heading"><div><span class="eyebrow">YOUR WORKSPACE</span><h2>Keep things moving</h2></div><span class="muted">Everyday essentials</span></div>
    <nav class="quick-actions" aria-label="Quick actions">
        <a href="{{ route('logistics.pickups') }}">
            @include('admin.partials.icon', ['name' => 'truck'])
            <span>
                <strong>Confirm pickups @if($stats['pickups_today'] > 0)<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px;">{{ $stats['pickups_today'] }} {{ $periodSuffix }}</span>@endif</strong>
                <small>Review seller collection requests &amp; dispatch</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
        <a href="{{ route('logistics.hub') }}">
            @include('admin.partials.icon', ['name' => 'box'])
            <span>
                <strong>Hub desk &amp; sorting</strong>
                <small>Receive, sort by area, and assign parcels</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
        <a href="{{ route('logistics.riders') }}">
            @include('admin.partials.icon', ['name' => 'users'])
            <span>
                <strong>Rider management @if($stats['pending_applications'] > 0)<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px;">{{ $stats['pending_applications'] }} pending</span>@endif</strong>
                <small>Review applications &amp; courier assignments</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
        <a href="{{ route('logistics.monitoring') }}">
            @include('admin.partials.icon', ['name' => 'activity'])
            <span>
                <strong>Delivery monitoring @if($stats['failed_deliveries'] > 0)<span class="badge danger" style="margin-left: 6px; font-size: 10px; padding: 2px 6px;">{{ $stats['failed_deliveries'] }} failed</span>@endif</strong>
                <small>Track active deliveries &amp; follow up</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
        <a href="{{ route('logistics.attendance') }}">
            @include('admin.partials.icon', ['name' => 'clock'])
            <span>
                <strong>Time in / time out</strong>
                <small>Display live hub QR code for rider shifts</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
        <a href="{{ route('logistics.reports') }}">
            @include('admin.partials.icon', ['name' => 'chart'])
            <span>
                <strong>Performance reports</strong>
                <small>Parcel volume, rider rankings &amp; areas</small>
            </span>
            @include('admin.partials.icon', ['name' => 'arrow'])
        </a>
    </nav>

    <div class="population-strip" aria-label="Operational status">
        <div>
            <a href="{{ route('logistics.sorting') }}" style="display: flex; align-items: center; justify-content: center; gap: 10px; color: inherit; width: 100%;">
                @include('admin.partials.icon', ['name' => 'box'])
                <span class="population-number">{{ number_format($stats['sorted_today']) }}</span>
                <span>Sorted {{ $periodSuffix }}</span>
            </a>
        </div>
        <div>
            <a href="{{ route('logistics.monitoring') }}" style="display: flex; align-items: center; justify-content: center; gap: 10px; color: inherit; width: 100%;">
                @include('admin.partials.icon', ['name' => 'truck'])
                <span class="population-number">{{ number_format($stats['out_for_delivery']) }}</span>
                <span>Out for delivery</span>
            </a>
        </div>
        <div>
            <a href="{{ route('logistics.riders') }}" style="display: flex; align-items: center; justify-content: center; gap: 10px; color: inherit; width: 100%;">
                @include('admin.partials.icon', ['name' => 'users'])
                <span class="population-number">{{ number_format($stats['pending_applications']) }}</span>
                <span>Rider applications</span>
            </a>
        </div>
        <div>
            <a href="{{ route('logistics.monitoring', ['status' => 'failed']) }}" style="display: flex; align-items: center; justify-content: center; gap: 10px; color: inherit; width: 100%;">
                @include('admin.partials.icon', ['name' => 'shield'])
                <span class="population-number">{{ number_format($stats['failed_deliveries']) }}</span>
                <span>Failed deliveries {{ $periodSuffix }}</span>
            </a>
        </div>
    </div>

    <div class="two-columns">
        <section class="panel" aria-labelledby="volume-title">
            <header class="panel-header">
                <div>
                    <h2 id="volume-title">Parcel movement</h2>
                    <p>Received and delivered ({{ $filterLabel }})</p>
                </div>
                <a class="text-link" href="{{ route('logistics.reports') }}">View report →</a>
            </header>
            <div class="padded">
                <div class="operations-chart-summary" style="padding: 0 0 16px; border-bottom: 1px solid var(--line); margin-bottom: 16px;">
                    <div><span class="chart-key"></span><span>Received</span><strong>{{ array_sum($chartData['received']) }}</strong></div>
                    <div><span class="chart-key chart-key-rose"></span><span>Delivered</span><strong>{{ array_sum($chartData['delivered']) }}</strong></div>
                    <span class="operations-period">{{ $filterLabel }}</span>
                </div>
                <div class="operations-chart" style="padding: 0;"><canvas id="volumeChart" aria-label="Received and delivered parcels: {{ $filterLabel }}" role="img"></canvas></div>
                <details class="operations-chart-data" style="margin-top: 14px;">
                    <summary class="text-link">View daily figures</summary>
                    <div class="table-scroll" style="margin-top: 10px;">
                        <table>
                            <thead><tr><th scope="col">Day</th><th scope="col">Received</th><th scope="col">Delivered</th></tr></thead>
                            <tbody>
                                @foreach($chartData['labels'] as $index => $day)
                                    <tr><th scope="row">{{ $day }}</th><td>{{ $chartData['received'][$index] ?? 0 }}</td><td>{{ $chartData['delivered'][$index] ?? 0 }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </section>

        <section class="panel" aria-labelledby="activity-title">
            <header class="panel-header">
                <div>
                    <h2 id="activity-title">Recent activity</h2>
                    <p>Latest updates from your hub</p>
                </div>
                <a class="text-link" href="{{ route('logistics.notifications') }}">All notifications ({{ count($notifications) }}) →</a>
            </header>
            <div class="activity-list">
                @forelse($notifications as $notification)
                    <a href="{{ $notification['link'] }}" class="activity-item" style="text-decoration: none; color: inherit;">
                        <span class="activity-dot" style="{{ $notification['unread'] ? 'background: var(--rose-gold); box-shadow: 0 0 0 4px var(--rose-soft);' : '' }}"></span>
                        <div style="flex: 1; min-width: 0;">
                            <strong>{{ $notification['title'] }}</strong>
                            <p>{{ $notification['message'] }}</p>
                            <small>{{ $notification['time'] }}@if($notification['unread']) &middot; <span style="color: var(--rose-ink); font-weight: 700;">Unread</span>@endif</small>
                        </div>
                    </a>
                @empty
                    <div class="empty">
                        <strong>No updates yet</strong>
                        <p>New hub activity will appear here as parcels and riders move.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="panel" aria-labelledby="pickups-title">
        <header class="panel-header">
            <div>
                <h2 id="pickups-title">Recent pickup requests</h2>
                <p>Seller collections and their current status</p>
            </div>
            <a class="text-link" href="{{ route('logistics.pickups') }}">View all →</a>
        </header>
        <div class="table-scroll">
            <table>
                <thead><tr><th scope="col">Seller</th><th scope="col">Parcels</th><th scope="col">Scheduled pickup</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @forelse($recentPickups as $pickup)
                        <tr>
                            <td><strong><a href="{{ route('logistics.pickups') }}">{{ $pickup['seller_name'] }}</a></strong></td>
                            <td>{{ $pickup['parcel_count'] }}</td>
                            <td><strong>{{ $pickup['pickup_date'] }}</strong><small>{{ $pickup['pickup_time'] }}</small></td>
                            <td><span class="badge {{ $pickup['status'] === 'confirmed' ? 'good' : 'warning' }}">{{ ucfirst($pickup['status']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty"><strong>No pickup requests yet</strong><p>Seller pickup requests will appear here when scheduled.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
if (window.Chart) {
    new Chart(document.getElementById('volumeChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                { label: 'Received', data: @json($chartData['received']), backgroundColor: '#6F6382', borderRadius: 6, maxBarThickness: 26 },
                { label: 'Delivered', data: @json($chartData['delivered']), backgroundColor: '#C08B7F', borderRadius: 6, maxBarThickness: 26 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 350 },
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#3E354C', padding: 12, cornerRadius: 6 } },
            scales: {
                y: { beginAtZero: true, border: { display: false }, grid: { color: '#EEEAE8' }, ticks: { precision: 0, padding: 10, color: '#77717C' } },
                x: { border: { display: false }, grid: { display: false }, ticks: { color: '#77717C', padding: 8 } }
            }
        }
    });
}
</script>
@endpush
