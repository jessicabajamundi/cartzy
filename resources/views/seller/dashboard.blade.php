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
        @if(auth()->user()->status !== 'active' || !$shop || !$shop->isApproved())
            <button type="button" class="button" disabled style="opacity:0.65; cursor:not-allowed; background:#9ca3af; display:inline-flex; align-items:center; gap:6px;" title="Administrator approval is required before you can sell or add products.">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Add product (Locked)</span>
            </button>
        @else
            <a class="button" href="{{ route('seller.products', ['create' => 1]) }}#add-product">＋ Add product</a>
        @endif
    </div>
</div>
<div class="overview-context"><span>Sales: {{ $periodLabel }} <span aria-hidden="true">·</span> Orders and inventory: current status</span><span>Updated {{ $updatedAt }} PHT</span></div>
@if($stats['orders'] > 0)
<a class="preparation-notice" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}">
    <span class="inline">@include('seller.partials.icon', ['name' => 'bag'])<span><strong>{{ number_format($stats['orders']) }} {{ Str::plural('order', $stats['orders']) }} awaiting preparation</strong><small>Review and prepare your pending, accepted, and packed orders.</small></span></span>
    <span class="notice-action">Review orders →</span>
</a>
@endif
<div class="metrics">
    <div class="metric featured"><div class="metric-top">Gross sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['sales']/100,2) }}</div><small>{{ $periodLabel }} · paid & delivered orders</small></div>
    <div class="metric"><div class="metric-top">Net sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['net']/100,2) }}</div><small>After {{ \App\Models\SellerOrder::COMMISSION_BPS / 100 }}% platform commission</small></div>
    <a class="metric" href="{{ route('seller.orders',['status'=>'to_prepare']) }}"><div class="metric-top">Orders to prepare @include('seller.partials.icon',['name'=>'bag'])</div><div class="metric-value">{{ number_format($stats['orders']) }}</div><small>Pending, accepted & packed orders</small></a>
    <a class="metric" href="{{ route('seller.products',['tab'=>'active']) }}"><div class="metric-top">Active products @include('seller.partials.icon',['name'=>'box'])</div><div class="metric-value">{{ number_format($stats['products']) }}</div><small>Currently active in your inventory</small></a>
</div>
<div class="two-column">
    <section class="panel"><div class="panel-header"><div><h2>Sales performance</h2><small>Paid sales by delivery date</small></div><span class="badge">{{ $periodLabel }}</span></div><div class="panel-body"><div class="sales-chart-scroll" tabindex="0" role="region" aria-label="Daily sales chart"><div style="min-width:{{ $chart->count() > 7 ? $chart->count() * 58 : 0 }}px"><div class="chart" role="img" aria-label="Sales — {{ $periodLabel }}: {{ $chart->map(fn($d) => $d['date'].': PHP '.number_format($d['sales']/100,2))->join('; ') }}">@foreach($chart as $day)<div class="chart-column"><span class="chart-value">₱{{ number_format($day['sales']/100,0) }}</span><div class="chart-bar" style="height:{{ $chart->max('sales') ? round($day['sales']/$chart->max('sales')*80) : 0 }}%"></div></div>@endforeach</div><div class="chart-labels">@foreach($chart as $day)<span>{{ $day['label'] }}</span>@endforeach</div></div></div><p class="chart-note">{{ $chart->sum('sales') ? 'Only paid, delivered or completed orders are included.' : 'No paid, delivered sales in this period yet.' }}</p></div></section>
    <section class="panel" id="needs-attention"><div class="panel-header"><h2>Needs your attention</h2>@include('seller.partials.icon',['name'=>'check'])</div><div class="panel-body">
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'cancellation_requested']) }}"><div><strong>Cancellation requests</strong><small>Review requests awaiting your decision</small></div><span class="task-number">{{ number_format($stats['cancellation_requests']) }}</span></a>
        <a class="task-row" href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}"><div><strong>Ready for pickup</strong><small>Packed orders awaiting courier collection</small></div><span class="task-number">{{ number_format($stats['ready_for_pickup']) }}</span></a>
        <a class="task-row" href="{{ route('seller.chat', ['unread' => 1]) }}"><div><strong>Unread buyer messages</strong><small>Open conversations with unread messages</small></div><span class="task-number">{{ number_format($stats['unread_messages']) }}</span></a>
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}"><div><strong>Prepare your orders</strong><small>Pack and get ready for pickup</small></div><span class="task-number">{{ $stats['orders'] }}</span></a>@if(($stockAlertCount ?? 0) > 0)<button type="button" class="task-row restock-task" data-restock-open aria-haspopup="dialog" aria-controls="restock-dialog"><div><strong>Restock products</strong><small>Low-stock and sold-out variations need attention</small></div><span class="task-number">{{ number_format($stockAlertCount) }}</span></button>@else<a class="task-row" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}"><div><strong>Restock products</strong><small>No variations need restocking</small></div><span class="task-number">0</span></a>@endif<a class="task-row" href="{{ route('seller.feedback') }}"><div><strong>Customer rating</strong><small>{{ $stats['reviews'] }} verified purchase reviews</small></div><span class="task-number">{{ $stats['rating'] ? number_format($stats['rating'],1) : '—' }}</span></a></div></section>
</div>
<section class="panel"><div class="panel-header"><div><h2>Recent orders</h2><small>The latest activity in your store</small></div><a class="button text" href="{{ route('seller.orders') }}">View all orders →</a></div>@include('seller.partials.orders-table',['orders'=>$recentOrders])</section>
@endsection
