@extends('layouts.logistics')

@section('title', 'Reports | Logistics Hub | cartzy')
@section('page_title', 'Reports')
@section('page_subtitle', 'Hub performance metrics, rider rankings, and area breakdowns')

@section('content')
@php
    $kpis = [
        ['Total parcels handled', number_format($reportData['total_parcels_handled']), 'Received in this period', 'box'],
        ['Delivered on time', number_format($reportData['delivered_on_time']), 'Completed deliveries', 'truck'],
        ['Failed deliveries', number_format($reportData['failed_deliveries']), 'Need a follow-up attempt', 'shield'],
        ['Success rate', $reportData['success_rate'].'%', 'Delivered ÷ handled', 'chart'],
        ['Avg. delivery time', $reportData['avg_delivery_days'].' days', 'From receipt to delivery', 'activity'],
    ];
    $rateClass = fn ($rate) => $rate >= 95 ? 'good' : ($rate >= 90 ? 'warning' : 'danger');
@endphp
<div class="page-heading">
    <div><p class="eyebrow">LOGISTICS / REPORTS</p><h1>Reports</h1><p class="muted">Hub performance, rider rankings and delivery areas for the selected dates.</p></div>
    <span class="date-label">{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('M j, Y') }} – {{ \Illuminate\Support\Carbon::parse($dateTo)->format('M j, Y') }}</span>
</div>

<section class="panel">
    <form method="GET" action="{{ route('logistics.reports') }}" class="filters">
        <label>From date<input type="date" name="from" value="{{ $dateFrom }}"></label>
        <label>To date<input type="date" name="to" value="{{ $dateTo }}"></label>
        <button type="submit" class="button">Generate report</button>
        <button type="button" class="button subtle" onclick="window.print()">Print</button>
    </form>
</section>

<div class="stats-grid reports-kpis">
    @foreach($kpis as [$label, $value, $hint, $icon])
        <div class="stat-card">
            <div class="metric-heading"><span>{{ $label }}</span>@include('admin.partials.icon', ['name' => $icon])</div>
            <strong>{{ $value }}</strong>
            <small>{{ $hint }}</small>
        </div>
    @endforeach
</div>

<div class="two-columns">
    <section class="panel">
        <header class="panel-header"><div><h2>Weekly parcel volume</h2><p>Received vs delivered, by week</p></div></header>
        <div class="padded"><canvas id="reportChart" height="200" aria-label="Weekly parcels received and delivered" role="img"></canvas></div>
    </section>
    <section class="panel">
        <header class="panel-header"><div><h2>Top delivery areas</h2><p>Share of parcels handled</p></div></header>
        <div class="padded reports-areas">
            @forelse($reportData['area_breakdown'] as $area)
                <div class="reports-area">
                    <div class="reports-area-row"><span>{{ $area['area'] }}</span><strong>{{ $area['parcels'] }}</strong></div>
                    <div class="reports-bar" aria-hidden="true"><span style="width:{{ round($area['parcels'] / max($reportData['total_parcels_handled'], 1) * 100) }}%"></span></div>
                </div>
            @empty
                <div class="empty"><strong>No areas yet</strong><p>Areas appear once parcels are sorted.</p></div>
            @endforelse
        </div>
    </section>
</div>

<section class="panel">
    <header class="panel-header"><div><h2>Top performing riders</h2><p>{{ $dateFrom }} – {{ $dateTo }}</p></div></header>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Rank</th><th>Rider</th><th>Deliveries</th><th>Success rate</th><th>Performance</th></tr></thead>
            <tbody>
                @forelse($reportData['top_riders'] as $i => $rider)
                    <tr>
                        <td><span class="badge {{ $i < 3 ? 'good' : '' }}">#{{ $i + 1 }}</span></td>
                        <td><strong>{{ $rider['name'] }}</strong></td>
                        <td class="numeric">{{ $rider['deliveries'] }}</td>
                        <td><span class="badge {{ $rateClass($rider['success_rate']) }}">{{ $rider['success_rate'] }}%</span></td>
                        <td><div class="reports-bar is-small" aria-hidden="true"><span style="width:{{ $rider['success_rate'] }}%"></span></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><strong>No deliveries yet</strong><p>Rider rankings appear once deliveries are recorded in this period.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('reportChart'), {
    type: 'line',
    data: {
        labels: @json($reportData['chart']['labels']),
        datasets: [
            { label: 'Received', data: @json($reportData['chart']['received']), borderColor: '#6F6382', backgroundColor: 'rgba(111,99,130,0.08)', borderWidth: 2, tension: 0.35, fill: true, pointRadius: 3, pointBackgroundColor: '#6F6382' },
            { label: 'Delivered', data: @json($reportData['chart']['delivered']), borderColor: '#8C5A50', backgroundColor: 'rgba(140,90,80,0.06)', borderWidth: 2, tension: 0.35, fill: true, pointRadius: 3, pointBackgroundColor: '#8C5A50' },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top', align: 'end' } }, scales: { y: { beginAtZero: true, grid: { color: '#F1EFF5' } }, x: { grid: { display: false } } } }
});
</script>
@endpush
