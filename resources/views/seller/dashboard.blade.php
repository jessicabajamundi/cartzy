@extends('layouts.seller')
@section('page_title', 'Overview')
@section('content')
<div class="page-heading overview-heading">
    <div><h1>Store overview</h1><p class="subtitle">Monitor your sales, orders, and inventory in one place.</p></div>
    <div class="overview-actions">
        <form method="GET" action="{{ route('seller.dashboard') }}" class="period-filter">
            <label for="sales-period">Sales period</label>
            <div class="inline"><select id="sales-period" name="period">@foreach($periodLabels as $value => $label)<option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>@endforeach</select><button type="submit" class="button secondary small">Apply</button></div>
        </form>
        <a class="button secondary" href="{{ route('seller.reports', $reportDates) }}">View reports ↗</a>
        <a class="button" href="{{ route('seller.inventory', ['create' => 1]) }}#add-product">＋ Add product</a>
    </div>
</div>
<div class="overview-context"><span>Sales: {{ $periodLabel }} <span aria-hidden="true">·</span> Orders and inventory: current status</span><span>Updated at {{ $updatedAt }} PHT</span></div>
@if($stats['orders'] > 0)
<a class="preparation-notice" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}">
    <span class="inline">@include('seller.partials.icon', ['name' => 'bag'])<span><strong>{{ number_format($stats['orders']) }} {{ Str::plural('order', $stats['orders']) }} awaiting preparation</strong><small>Review and prepare your pending, accepted, and packed orders.</small></span></span>
    <span class="notice-action">Review orders →</span>
</a>
@endif
<div class="metrics">
    <div class="metric featured"><div class="metric-top">Gross sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['sales']/100,2) }}</div><small>{{ $periodLabel }} · paid & delivered orders</small></div>
    <div class="metric"><div class="metric-top">Net sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['net']/100,2) }}</div><small>After recorded platform commission</small></div>
    <a class="metric" href="{{ route('seller.orders',['status'=>'to_prepare']) }}"><div class="metric-top">Orders to prepare @include('seller.partials.icon',['name'=>'bag'])</div><div class="metric-value">{{ number_format($stats['orders']) }}</div><small>Pending, accepted & packed orders</small></a>
    <a class="metric" href="{{ route('seller.inventory',['tab'=>'active']) }}"><div class="metric-top">Active products @include('seller.partials.icon',['name'=>'box'])</div><div class="metric-value">{{ number_format($stats['products']) }}</div><small>Currently active in your inventory</small></a>
</div>
<div class="two-column">
    <section class="panel"><div class="panel-header"><div><h2>Sales performance</h2><small>Paid sales by delivery date</small></div><span class="badge">{{ $periodLabel }}</span></div><div class="panel-body"><div class="sales-chart-scroll" tabindex="0" role="region" aria-label="Daily sales chart"><div style="min-width:{{ $chart->count() > 7 ? $chart->count() * 58 : 0 }}px"><div class="chart" role="img" aria-label="Sales — {{ $periodLabel }}: {{ $chart->map(fn($d) => $d['date'].': PHP '.number_format($d['sales']/100,2))->join('; ') }}">@foreach($chart as $day)<div class="chart-column"><span class="chart-value">₱{{ number_format($day['sales']/100,0) }}</span><div class="chart-bar" style="height:{{ $chart->max('sales') ? round($day['sales']/$chart->max('sales')*80) : 0 }}%"></div></div>@endforeach</div><div class="chart-labels">@foreach($chart as $day)<span>{{ $day['label'] }}</span>@endforeach</div></div></div><p class="chart-note">{{ $chart->sum('sales') ? 'Only paid, delivered or completed orders are included.' : 'No paid, delivered sales in this period yet.' }}</p></div></section>
    <section class="panel"><div class="panel-header"><h2>Needs your attention</h2>@include('seller.partials.icon',['name'=>'check'])</div><div class="panel-body"><a class="task-row" href="{{ route('seller.orders') }}"><div><strong>Prepare your orders</strong><small>Pack and get ready for pickup</small></div><span class="task-number">{{ $stats['orders'] }}</span></a><a class="task-row" href="{{ route('seller.inventory',['tab'=>'low_stock']) }}"><div><strong>Review low stock</strong><small>Products with 10 units or fewer per variant</small></div><span class="task-number">{{ $stats['low_stock'] }}</span></a><a class="task-row" href="{{ route('seller.feedback') }}"><div><strong>Customer rating</strong><small>{{ $stats['reviews'] }} verified purchase reviews</small></div><span class="task-number">{{ $stats['rating'] ? number_format($stats['rating'],1) : '—' }}</span></a></div></section>
</div>
<section class="panel"><div class="panel-header"><div><h2>Recent orders</h2><small>The latest activity in your store</small></div><a class="button text" href="{{ route('seller.orders') }}">View all orders →</a></div>@include('seller.partials.orders-table',['orders'=>$recentOrders])</section>
@endsection
