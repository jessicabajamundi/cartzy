@extends('layouts.logistics')

@section('title', 'Dashboard | Logistics Hub | cartzy')
@section('page_title', 'Logistics Hub Dashboard')
@section('page_subtitle', 'Operations / Overview')

@section('content')
<div class="operations-dashboard">
    <section class="operations-heading" aria-labelledby="operations-title">
        <div>
            <p class="operations-kicker">Daily operations</p>
            <h2 id="operations-title">Your hub, at a glance.</h2>
            <p class="operations-location">{{ $hub->business_name ?? $hub->name }}</p>
        </div>
        <div class="operations-heading-actions">
            <time datetime="{{ now()->timezone('Asia/Manila')->toDateString() }}">{{ now()->timezone('Asia/Manila')->format('D, d M Y') }}</time>
            <a href="{{ route('logistics.monitoring') }}" class="operations-button">Monitor deliveries <span aria-hidden="true">&rarr;</span></a>
        </div>
    </section>

    @php
        $metrics = [
            ['Active riders', $stats['active_riders'], 'of '.$stats['total_riders'].' registered riders', 'users', 'logistics.riders'],
            ['Pickups today', $stats['pickups_today'], 'Scheduled for today', 'truck', 'logistics.pickups'],
            ['Parcels in hub', $stats['parcels_in_hub'], 'Current hub inventory', 'box', 'logistics.parcels'],
            ['Delivered today', $stats['delivered_today'], 'Completed deliveries', 'check', 'logistics.monitoring'],
            ['Hub efficiency', $stats['hub_efficiency'].'%', 'Delivery completion rate', 'chart', 'logistics.reports'],
        ];
    @endphp
    <section class="operations-metrics" aria-label="Today’s hub metrics">
        @foreach($metrics as [$label, $value, $detail, $icon, $destination])
            <a class="operations-metric" href="{{ route($destination) }}">
                <div class="operations-metric-label"><span>{{ $label }}</span>@include('seller.partials.icon', ['name' => $icon, 'size' => 18])</div>
                <strong>{{ $value }}</strong>
                <span class="operations-metric-detail">{{ $detail }}</span>
            </a>
        @endforeach
    </section>

    <section class="operations-queue" aria-label="Operational status">
        <a href="{{ route('logistics.sorting') }}"><span class="queue-dot"></span><strong>{{ $stats['sorted_today'] }}</strong> sorted today <span class="queue-arrow" aria-hidden="true">&rarr;</span></a>
        <a href="{{ route('logistics.monitoring') }}"><span class="queue-dot"></span><strong>{{ $stats['out_for_delivery'] }}</strong> out for delivery <span class="queue-arrow" aria-hidden="true">&rarr;</span></a>
        <a class="queue-attention" href="{{ route('logistics.riders') }}"><span class="queue-dot"></span><strong>{{ $stats['pending_applications'] }}</strong> rider applications <span class="queue-arrow" aria-hidden="true">&rarr;</span></a>
        <a class="queue-attention" href="{{ route('logistics.monitoring') }}"><span class="queue-dot"></span><strong>{{ $stats['failed_deliveries'] }}</strong> failed deliveries <span class="queue-arrow" aria-hidden="true">&rarr;</span></a>
    </section>

    <div class="operations-main-grid">
        <section class="operations-panel operations-volume" aria-labelledby="volume-title">
            <div class="operations-panel-heading">
                <div><h3 id="volume-title">Parcel movement</h3><p>Received and delivered over the last 7 days</p></div>
                <a class="operations-text-link" href="{{ route('logistics.reports') }}">View report <span aria-hidden="true">&nearr;</span></a>
            </div>
            <div class="operations-chart-summary">
                <div><span class="chart-key"></span><span>Received</span><strong>{{ array_sum($chartData['received']) }}</strong></div>
                <div><span class="chart-key chart-key-rose"></span><span>Delivered</span><strong>{{ array_sum($chartData['delivered']) }}</strong></div>
                <span class="operations-period">Last 7 days</span>
            </div>
            <div class="operations-chart"><canvas id="volumeChart" aria-label="Received and delivered parcels over the last seven days" role="img"></canvas></div>
            <details class="operations-chart-data"><summary>View daily figures</summary><div class="operations-table-scroll"><table><thead><tr><th scope="col">Day</th><th scope="col">Received</th><th scope="col">Delivered</th></tr></thead><tbody>
                @foreach($chartData['labels'] as $index => $day)
                    <tr><th scope="row">{{ $day }}</th><td>{{ $chartData['received'][$index] ?? 0 }}</td><td>{{ $chartData['delivered'][$index] ?? 0 }}</td></tr>
                @endforeach
            </tbody></table></div></details>
        </section>

        <section class="operations-panel operations-activity" aria-labelledby="activity-title">
            <div class="operations-panel-heading"><div><h3 id="activity-title">Activity feed</h3><p>Latest updates from your hub</p></div><span class="operations-feed-count">{{ count($notifications) }}</span></div>
            <div class="operations-activity-list">
                @forelse($notifications as $notification)
                    <a href="{{ $notification['link'] }}" class="operations-activity-item {{ $notification['unread'] ? 'is-unread' : '' }}">
                        <span class="operations-activity-icon {{ $notification['type'] === 'delivery' ? 'is-rose' : '' }}">@include('seller.partials.icon', ['name' => match($notification['type']) { 'pickup' => 'truck', 'rider' => 'user', 'delivery' => 'check', default => 'box' }, 'size' => 17])</span>
                        <div><h4>{{ $notification['title'] }}</h4><p>{{ $notification['message'] }}</p><span class="operations-activity-time">{{ $notification['time'] }}@if($notification['unread'])<span class="operations-unread">Unread</span>@endif</span></div>
                        <span class="operations-activity-arrow" aria-hidden="true">&nearr;</span>
                    </a>
                @empty
                    <p class="operations-empty">No updates yet. New hub activity will appear here.</p>
                @endforelse
            </div>
        </section>

        <section class="operations-panel operations-pickups" aria-labelledby="pickups-title">
            <div class="operations-panel-heading"><div><h3 id="pickups-title">Recent pickup requests</h3><p>Seller collections and their current status</p></div><a class="operations-text-link" href="{{ route('logistics.pickups') }}">View all <span aria-hidden="true">&rarr;</span></a></div>
            <div class="operations-table-scroll">
                <table class="operations-pickup-table">
                    <thead><tr><th scope="col">Seller</th><th scope="col">Parcels</th><th scope="col">Scheduled pickup</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @forelse($recentPickups as $pickup)
                            <tr><td><a href="{{ route('logistics.pickups') }}">{{ $pickup['seller_name'] }}</a></td><td>{{ $pickup['parcel_count'] }}</td><td>{{ $pickup['pickup_date'] }}<span class="operations-table-time">{{ $pickup['pickup_time'] }}</span></td><td><span class="operations-status {{ $pickup['status'] === 'confirmed' ? 'is-confirmed' : '' }}">{{ ucfirst($pickup['status']) }}</span></td></tr>
                        @empty
                            <tr><td colspan="4" class="operations-empty">No pickup requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="operations-panel operations-tools" aria-labelledby="tools-title">
            <div class="operations-panel-heading"><div><h3 id="tools-title">Hub workspace</h3><p>Keep the next step moving</p></div></div>
            @foreach([
                ['logistics.pickups', 'Confirm pickups', 'Review seller collection requests', 'truck'],
                ['logistics.sorting', 'Sort parcels', 'Organize parcels by delivery area', 'box'],
                ['logistics.assignments', 'Assign riders', 'Prepare the next delivery run', 'users'],
            ] as [$destination, $label, $description, $icon])
                <a class="operations-tool" href="{{ route($destination) }}">@include('seller.partials.icon', ['name' => $icon, 'size' => 19])<span><strong>{{ $label }}</strong><small>{{ $description }}</small></span><span aria-hidden="true">&rarr;</span></a>
            @endforeach
        </section>
    </div>
    <footer class="operations-footer"><span>cartzy <span aria-hidden="true">/</span> Logistics workspace</span><span>All times in Philippine Standard Time</span></footer>
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
                { label: 'Received', data: @json($chartData['received']), backgroundColor: '#6F6382', borderRadius: 3, maxBarThickness: 24 },
                { label: 'Delivered', data: @json($chartData['delivered']), backgroundColor: '#C08B7F', borderRadius: 3, maxBarThickness: 24 }
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
