@extends('layouts.seller')
@section('page_title', 'Overview')
@section('content')
<section class="seller-welcome" aria-labelledby="welcome-title">
    <div class="seller-welcome-copy">
        <span class="dashboard-eyebrow"><span class="welcome-dot" aria-hidden="true"></span> YOUR STORE, AT A GLANCE</span>
        <h2 id="welcome-title">Welcome back, {{ auth()->user()->name }}.</h2>
        <p>A little care. A growing business.<br>Everything you need to keep {{ $shop?->name ?? 'your store' }} moving.</p>
        <a class="button" href="{{ route('seller.reports', $reportDates) }}">Explore reports @include('seller.partials.icon', ['name' => 'arrow-right'])</a>
    </div>
    <a class="seller-spotlight" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}">
        <span class="spotlight-heading">@include('seller.partials.icon', ['name' => 'bag']) Order desk</span>
        <strong>{{ number_format($stats['orders']) }}</strong>
        <span>{{ Str::plural('order', $stats['orders']) }} awaiting preparation</span>
        <small>{{ $stats['orders'] > 0 ? 'Review and prepare your pending, accepted, and packed orders.' : 'All caught up. Your preparation queue is clear.' }}</small>
        <span class="spotlight-action">Review orders @include('seller.partials.icon', ['name' => 'arrow-right'])</span>
    </a>
</section>
<div class="dashboard-section-heading">
    <div><span class="dashboard-eyebrow">PERFORMANCE</span><h2>Your store in numbers</h2><small class="muted">Updated {{ $updatedAt }} PHT</small></div>
    <div class="overview-actions">
        <a class="button secondary" href="{{ route('seller.reports', $reportDates) }}">View reports ↗</a>
        @if(auth()->user()->status !== 'active' || !$shop || !$shop->isApproved())
            @if($shop)
                <button type="button" class="button secondary" disabled title="Your shop page goes live once an administrator approves your store.">View my shop</button>
            @endif
            <button type="button" class="button" disabled style="opacity:0.65; cursor:not-allowed; background:#9ca3af; display:inline-flex; align-items:center; gap:6px;" title="Administrator approval is required before you can sell or add products.">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Add product (Locked)</span>
            </button>
        @else
            <a class="button secondary" href="{{ route('store.show', $shop->slug) }}" target="_blank" rel="noopener" title="See your shop the way buyers do">View my shop ↗</a>
            <a class="button" href="{{ route('seller.products', ['create' => 1]) }}#add-product">＋ Add product</a>
        @endif
    </div>
</div>
@php($lowOnly = max(0, $stats['low_stock'] - $stats['out_of_stock']))
<div class="metrics overview-metrics">
    <a class="metric" href="{{ route('seller.orders') }}"><div class="metric-top">Open orders @include('seller.partials.icon',['name'=>'bag'])</div><div class="metric-value">{{ number_format($stats['orders'] + $stats['in_delivery']) }}</div><small>{{ number_format($stats['orders']) }} to prepare · {{ number_format($stats['in_delivery']) }} in delivery</small></a>
    <a class="metric" href="{{ route('seller.reports') }}"><div class="metric-top">Delivered sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['delivered_sales']/100,2) }}</div><small>₱{{ number_format($stats['delivered_sales_month']/100,2) }} this month</small></a>
    <a class="metric" href="{{ route('seller.inventory', ($lowOnly + $stats['out_of_stock']) > 0 ? ['tab' => 'low_stock'] : []) }}"><div class="metric-top">Products @include('seller.partials.icon',['name'=>'box'])</div><div class="metric-value">{{ number_format($stats['products']) }}</div><small>{{ number_format($lowOnly) }} low · {{ number_format($stats['out_of_stock']) }} out of stock</small></a>
    <a class="metric" href="{{ route('seller.feedback') }}"><div class="metric-top">Rating @include('seller.partials.icon',['name'=>'star'])</div><div class="metric-value">{{ $stats['reviews'] ? number_format($stats['rating'], 1) : '—' }}</div><small>{{ number_format($stats['reviews']) }} {{ Str::plural('review', $stats['reviews']) }}</small></a>
    <div class="metric"><div class="metric-top">Followers @include('seller.partials.icon',['name'=>'users'])</div><div class="metric-value">{{ number_format($stats['followers']) }}</div><small>Buyers following your shop page</small></div>
</div>
<div class="dashboard-section-heading"><div><span class="dashboard-eyebrow">EVERYDAY ESSENTIALS</span><h2>Keep things moving</h2></div></div>
<nav class="seller-quick-actions" aria-label="Quick actions">
    <a href="{{ route('seller.products') }}">@include('seller.partials.icon', ['name' => 'box'])<span><strong>Your products</strong><small>Manage listings and product details</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
    <a href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}">@include('seller.partials.icon', ['name' => 'tag'])<span><strong>Stock &amp; inventory</strong><small>{{ number_format($stockAlertCount ?? 0) }} variations need restocking</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
    <a href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}">@include('seller.partials.icon', ['name' => 'truck'])<span><strong>Arrange shipping</strong><small>{{ number_format($stats['ready_for_pickup']) }} orders ready for pickup</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
</nav>
<div class="two-column">
    <section class="panel"><div class="panel-header"><div><h2>Sales performance</h2><small>Paid sales by delivery date</small></div><span class="badge">{{ $periodLabel }}</span></div><div class="panel-body"><div class="sales-chart-scroll" tabindex="0" role="region" aria-label="Daily sales chart"><div style="min-width:{{ $chart->count() > 7 ? $chart->count() * 58 : 0 }}px"><div class="chart" role="img" aria-label="Sales — {{ $periodLabel }}: {{ $chart->map(fn($d) => $d['date'].': ₱'.number_format($d['sales']/100,2))->join('; ') }}">@foreach($chart as $day)<div class="chart-column"><span class="chart-value">₱{{ number_format($day['sales']/100,0) }}</span><div class="chart-bar" style="height:{{ $chart->max('sales') ? round($day['sales']/$chart->max('sales')*80) : 0 }}%"></div></div>@endforeach</div><div class="chart-labels">@foreach($chart as $day)<span>{{ $day['label'] }}</span>@endforeach</div></div></div><p class="chart-note">{{ $chart->sum('sales') ? 'Only paid, delivered or completed orders are included.' : 'No paid, delivered sales in this period yet.' }}</p></div></section>
    <section class="panel" id="needs-attention"><div class="panel-header"><h2>Needs your attention</h2>@include('seller.partials.icon',['name'=>'check'])</div><div class="panel-body">
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'cancellation_requested']) }}"><div><strong>Cancellation requests</strong><small>Review requests awaiting your decision</small></div><span class="task-number">{{ number_format($stats['cancellation_requests']) }}</span></a>
        <a class="task-row" href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}"><div><strong>Ready for pickup</strong><small>Packed orders awaiting courier collection</small></div><span class="task-number">{{ number_format($stats['ready_for_pickup']) }}</span></a>
        <a class="task-row" href="{{ route('seller.chat', ['unread' => 1]) }}"><div><strong>Unread buyer messages</strong><small>Open conversations with unread messages</small></div><span class="task-number">{{ number_format($stats['unread_messages']) }}</span></a>
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}"><div><strong>Prepare your orders</strong><small>Pack and get ready for pickup</small></div><span class="task-number">{{ $stats['orders'] }}</span></a>@if(($stockAlertCount ?? 0) > 0)<button type="button" class="task-row restock-task" data-restock-open aria-haspopup="dialog" aria-controls="restock-dialog"><div><strong>Restock products</strong><small>Low-stock and sold-out variations need attention</small></div><span class="task-number">{{ number_format($stockAlertCount) }}</span></button>@else<a class="task-row" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}"><div><strong>Restock products</strong><small>No variations need restocking</small></div><span class="task-number">0</span></a>@endif<a class="task-row" href="{{ route('seller.feedback') }}"><div><strong>Customer rating</strong><small>{{ $stats['reviews'] }} verified purchase reviews</small></div><span class="task-number">{{ $stats['rating'] ? number_format($stats['rating'],1) : '—' }}</span></a></div></section>
</div>
<section class="panel"><div class="panel-header"><div><h2>Recent orders</h2><small>The latest activity in your store</small></div><a class="button text" href="{{ route('seller.orders') }}">View all orders →</a></div>@include('seller.partials.orders-table',['orders'=>$recentOrders])</section>
@endsection
