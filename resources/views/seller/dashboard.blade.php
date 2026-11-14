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
        <a class="button secondary" href="{{ route('seller.reports', $reportDates) }}">@include('seller.partials.icon', ['name' => 'chart', 'size' => 16, 'class' => 'icon icon-lead'])<span>View reports</span></a>
        @if(auth()->user()->status !== 'active' || !$shop || !$shop->isApproved())
            @if($shop)
                <button type="button" class="button secondary" disabled title="Your shop page goes live once an administrator approves your store.">@include('seller.partials.icon', ['name' => 'store', 'size' => 16, 'class' => 'icon icon-lead'])<span>View my shop</span></button>
            @endif
            <button type="button" class="button" disabled style="opacity:0.65; cursor:not-allowed; background:#9ca3af;" title="Administrator approval is required before you can sell or add products.">
                @include('seller.partials.icon', ['name' => 'lock', 'size' => 15, 'stroke' => 2, 'class' => 'icon icon-lead'])
                <span>Add product (Locked)</span>
            </button>
        @else
            <a class="button secondary" href="{{ route('store.show', $shop->slug) }}" target="_blank" rel="noopener" title="See your shop the way buyers do (opens in a new tab)">@include('seller.partials.icon', ['name' => 'store', 'size' => 16, 'class' => 'icon icon-lead'])<span>View my shop</span>@include('seller.partials.icon', ['name' => 'external', 'size' => 14, 'class' => 'icon icon-trail'])</a>
            <a class="button" href="{{ route('seller.products', ['create' => 1]) }}#add-product">@include('seller.partials.icon', ['name' => 'plus', 'size' => 16, 'stroke' => 2.2, 'class' => 'icon icon-lead'])<span>Add product</span></a>
        @endif
    </div>
</div>
@php $lowOnly = max(0, $stats['low_stock'] - $stats['out_of_stock']); @endphp
<div class="metrics overview-metrics">
    <a class="metric" href="{{ route('seller.orders') }}"><div class="metric-top">Open orders @include('seller.partials.icon',['name'=>'bag'])</div><div class="metric-value">{{ number_format($stats['orders'] + $stats['in_delivery']) }}</div><small>{{ number_format($stats['orders']) }} to prepare · {{ number_format($stats['in_delivery']) }} in delivery</small></a>
    <a class="metric" href="{{ route('seller.reports') }}"><div class="metric-top">Delivered sales @include('seller.partials.icon',['name'=>'chart'])</div><div class="metric-value">₱{{ number_format($stats['delivered_sales']/100,2) }}</div><small>₱{{ number_format($stats['delivered_sales_month']/100,2) }} this month</small></a>
    <a class="metric" href="{{ route('seller.inventory', ($lowOnly + $stats['out_of_stock']) > 0 ? ['tab' => 'low_stock'] : []) }}"><div class="metric-top">Products @include('seller.partials.icon',['name'=>'box'])</div><div class="metric-value">{{ number_format($stats['products']) }}</div><small>{{ number_format($lowOnly) }} low · {{ number_format($stats['out_of_stock']) }} out of stock</small></a>
    <a class="metric" href="{{ route('seller.feedback') }}"><div class="metric-top">Rating @include('seller.partials.icon',['name'=>'star'])</div><div class="metric-value">{{ $stats['reviews'] ? number_format($stats['rating'], 1) : '—' }}</div><small>{{ number_format($stats['reviews']) }} {{ Str::plural('review', $stats['reviews']) }}</small></a>
    <div class="metric"><div class="metric-top">Followers @include('seller.partials.icon',['name'=>'users'])</div><div class="metric-value">{{ number_format($stats['followers']) }}</div><small>Buyers following your shop page</small></div>
</div>
<div class="dashboard-section-heading">
    <div>
        <span class="dashboard-eyebrow">TODAY'S ORDERS · {{ $todayStats['date_formatted'] }}</span>
        <h2>Today's Order Details</h2>
        <small class="muted">Live details for today. Counters reset back to zero every day at 12:00 AM PHT.</small>
    </div>
    <div class="overview-actions">
        <span class="daily-reset-tag" title="Daily metrics reset at midnight">
            @include('seller.partials.icon', ['name' => 'clock', 'size' => 14])
            <span>Resets daily at 12:00 AM</span>
        </span>
    </div>
</div>
<div class="metrics today-metrics">
    <div class="metric">
        <div class="metric-top">Today's Orders @include('seller.partials.icon',['name'=>'bag'])</div>
        <div class="metric-value">{{ number_format($todayStats['orders']) }}</div>
        <small>{{ $todayStats['orders'] === 0 ? 'Starts at 0 today · No orders yet' : number_format($todayStats['orders']).' received today' }}</small>
    </div>
    <div class="metric">
        <div class="metric-top">Delivered @include('seller.partials.icon',['name'=>'check'])</div>
        <div class="metric-value">{{ number_format($todayStats['delivered']) }}</div>
        <small>{{ $todayStats['delivered'] === 0 ? '0 delivered today' : number_format($todayStats['delivered']).' completed today' }}</small>
    </div>
    <div class="metric">
        <div class="metric-top">Cancelled @include('seller.partials.icon',['name'=>'cancel'])</div>
        <div class="metric-value">{{ number_format($todayStats['cancelled']) }}</div>
        <small>{{ $todayStats['cancelled'] === 0 ? '0 cancelled today' : number_format($todayStats['cancelled']).' cancelled today' }}</small>
    </div>
    <div class="metric">
        <div class="metric-top">Refunded @include('seller.partials.icon',['name'=>'wallet'])</div>
        <div class="metric-value">{{ number_format($todayStats['refunded']) }}</div>
        <small>{{ $todayStats['refunded'] === 0 ? '0 refunds today' : number_format($todayStats['refunded']).' refunded today' }}</small>
    </div>
    <div class="metric">
        <div class="metric-top">Today's Sales @include('seller.partials.icon',['name'=>'chart'])</div>
        <div class="metric-value">₱{{ number_format($todayStats['sales'] / 100, 2) }}</div>
        <small>Gross sales today</small>
    </div>
</div>
<div class="dashboard-section-heading"><div><span class="dashboard-eyebrow">EVERYDAY ESSENTIALS</span><h2>Keep things moving</h2></div></div>
<nav class="seller-quick-actions" aria-label="Quick actions">
    <a href="{{ route('seller.products') }}">@include('seller.partials.icon', ['name' => 'box'])<span><strong>Your products</strong><small>Manage listings and product details</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
    <a href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}">@include('seller.partials.icon', ['name' => 'tag'])<span><strong>Stock &amp; inventory</strong><small>{{ number_format($stockAlertCount ?? 0) }} variations need restocking</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
    <a href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}">@include('seller.partials.icon', ['name' => 'truck'])<span><strong>Arrange shipping</strong><small>{{ number_format($stats['ready_for_pickup']) }} orders ready for pickup</small></span>@include('seller.partials.icon', ['name' => 'arrow-right'])</a>
</nav>
@include('seller.partials.store-rankings')
<div class="dashboard-attention">
    <section class="panel" id="needs-attention"><div class="panel-header"><h2>Needs your attention</h2>@include('seller.partials.icon',['name'=>'check'])</div><div class="panel-body">
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'cancellation_requested']) }}"><div><strong>Cancellation requests</strong><small>Review requests awaiting your decision</small></div><span class="task-number">{{ number_format($stats['cancellation_requests']) }}</span></a>
        <a class="task-row" href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}"><div><strong>Ready for pickup</strong><small>Packed orders awaiting courier collection</small></div><span class="task-number">{{ number_format($stats['ready_for_pickup']) }}</span></a>
        <a class="task-row" href="{{ route('seller.chat', ['unread' => 1]) }}"><div><strong>Unread buyer messages</strong><small>Open conversations with unread messages</small></div><span class="task-number">{{ number_format($stats['unread_messages']) }}</span></a>
        <a class="task-row" href="{{ route('seller.orders', ['status' => 'to_prepare']) }}"><div><strong>Prepare your orders</strong><small>Pack and get ready for pickup</small></div><span class="task-number">{{ $stats['orders'] }}</span></a>@if(($stockAlertCount ?? 0) > 0)<button type="button" class="task-row restock-task" data-restock-open aria-haspopup="dialog" aria-controls="restock-dialog"><div><strong>Restock products</strong><small>Low-stock and sold-out variations need attention</small></div><span class="task-number">{{ number_format($stockAlertCount) }}</span></button>@else<a class="task-row" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}"><div><strong>Restock products</strong><small>No variations need restocking</small></div><span class="task-number">0</span></a>@endif<a class="task-row" href="{{ route('seller.feedback') }}"><div><strong>Customer rating</strong><small>{{ $stats['reviews'] }} verified purchase reviews</small></div><span class="task-number">{{ $stats['rating'] ? number_format($stats['rating'],1) : '—' }}</span></a></div></section>
</div>
<section class="panel daily-history-panel" id="daily-order-history">
    <div class="panel-header daily-history-header">
        <div>
            <h2>Daily Order History</h2>
            <small>Everyday order records with live status: Delivered, Cancelled, and Refunded</small>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('seller.order-history') }}" class="button text" style="font-weight: 700;">Full order history &rarr;</a>
            <div class="daily-history-filters">
            <form method="GET" action="{{ route('seller.dashboard') }}#daily-order-history" class="daily-filter-form">
                <select name="history_range" onchange="this.form.submit()" aria-label="Filter by date range">
                    <option value="today" @selected($historyRange === 'today')>Today only</option>
                    <option value="yesterday" @selected($historyRange === 'yesterday')>Yesterday</option>
                    <option value="7" @selected($historyRange === '7')>Last 7 days</option>
                    <option value="30" @selected($historyRange === '30')>Last 30 days</option>
                    <option value="all" @selected($historyRange === 'all')>All history</option>
                </select>
                <select name="history_status" onchange="this.form.submit()" aria-label="Filter by order status">
                    <option value="all" @selected($historyStatus === 'all')>All statuses</option>
                    <option value="delivered" @selected($historyStatus === 'delivered')>Delivered only</option>
                    <option value="cancelled" @selected($historyStatus === 'cancelled')>Cancelled only</option>
                    <option value="refunded" @selected($historyStatus === 'refunded')>Refunded only</option>
                </select>
            </form>
        </div>
    </div>
    <div class="daily-history-body">
        @forelse($dailyHistory as $day)
            <article class="daily-group {{ $day['is_today'] ? 'is-today' : '' }}">
                <header class="daily-group-header">
                    <div class="daily-group-title">
                        <span class="daily-day-badge">{{ $day['label'] }}</span>
                        <time class="daily-date">{{ $day['full_date'] }}</time>
                        @if($day['is_today'])
                            <span class="daily-live-dot" title="Today's live activity (resets at midnight)"></span>
                        @endif
                    </div>
                    <div class="daily-group-pills">
                        <span class="daily-pill orders" title="Total orders placed on this day">
                            <strong>{{ $day['total_orders'] }}</strong> {{ Str::plural('order', $day['total_orders']) }}
                        </span>
                        <span class="daily-pill delivered" title="Delivered orders">
                            @include('seller.partials.icon', ['name' => 'check', 'size' => 12])
                            <strong>{{ $day['delivered_count'] }}</strong> delivered
                        </span>
                        <span class="daily-pill cancelled" title="Cancelled orders">
                            @include('seller.partials.icon', ['name' => 'cancel', 'size' => 12])
                            <strong>{{ $day['cancelled_count'] }}</strong> cancelled
                        </span>
                        <span class="daily-pill refunded" title="Refunded orders">
                            @include('seller.partials.icon', ['name' => 'wallet', 'size' => 12])
                            <strong>{{ $day['refunded_count'] }}</strong> refund
                        </span>
                        <span class="daily-pill sales" title="Total sales on this day">
                            ₱{{ number_format($day['sales_minor'] / 100, 2) }}
                        </span>
                    </div>
                </header>

                @if($day['orders']->isEmpty())
                    <div class="daily-empty-state">
                        @if($day['is_today'])
                            <p>No orders yet today. Order details reset back to zero at 12:00 AM PHT every day.</p>
                        @else
                            <p>No {{ $historyStatus !== 'all' ? $historyStatus : '' }} orders recorded on {{ $day['full_date'] }}.</p>
                        @endif
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="seller-orders-table daily-orders-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Buyer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status in History</th>
                                    <th>Next action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($day['orders'] as $order)
                                    @php
                                        $payment = $order->order;
                                        $shipment = $order->shipment;
                                        $isRefunded = in_array($payment?->payment_status, ['refunded', 'partially_refunded'], true);
                                        $isCancelled = $order->status === 'cancelled';
                                        $isDelivered = in_array($order->status, ['delivered', 'completed'], true);
                                        $firstItem = $order->items->first();
                                        $moreItems = $order->items->count() - 1;
                                        $statusLabel = $order->status === 'shipped' && $shipment ? $shipment->status : $order->status;
                                        $canPack = in_array($order->status, ['pending', 'accepted', 'packed'], true)
                                            && !$isRefunded
                                            && ($payment?->payment_method === 'cod' || $payment?->payment_status === 'paid');
                                        $needsPickup = $order->status === 'ready_to_ship' && (!$shipment || ($shipment->status === 'unassigned' && !$shipment->received_at));
                                    @endphp
                                    <tr class="{{ $isCancelled ? 'row-cancelled' : '' }} {{ $isDelivered ? 'row-delivered' : '' }} {{ $isRefunded ? 'row-refunded' : '' }}">
                                        <td class="so-order">
                                            <strong>{{ $payment?->reference ?? 'ORD-'.$order->id }}</strong>
                                            <small>{{ $order->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</small>
                                            @if($shipment?->tracking_code)
                                                <small class="so-tracking">{{ $shipment->tracking_code }}</small>
                                            @endif
                                        </td>
                                        <td class="so-buyer">
                                            <strong>{{ $payment?->buyer?->name ?? 'Buyer' }}</strong>
                                        </td>
                                        <td class="so-items">
                                            @if($firstItem)
                                                <div class="so-item">
                                                    <span class="so-thumb">
                                                        @if($firstItem->thumbnail_url)
                                                            <img src="{{ $firstItem->thumbnail_url }}" alt="" loading="lazy">
                                                        @else
                                                            @include('seller.partials.icon', ['name' => 'bag', 'size' => 20])
                                                        @endif
                                                    </span>
                                                    <span class="so-item-copy">
                                                        <strong>{{ $firstItem->product_name }}@if($firstItem->variant_name) — {{ $firstItem->variant_name }}@endif</strong>
                                                        <small>{{ $firstItem->quantity }} × ₱{{ number_format($firstItem->unit_price_minor / 100, 2) }}@if($moreItems > 0) · +{{ $moreItems }} more {{ Str::plural('item', $moreItems) }}@endif</small>
                                                    </span>
                                                    <span class="so-item-price">₱{{ number_format($firstItem->unit_price_minor * $firstItem->quantity / 100, 2) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="so-total">
                                            <strong>₱{{ number_format($order->buyerDueMinor() / 100, 2) }}</strong>
                                            <small>{{ strtoupper($payment?->payment_method ?? 'COD') }} · {{ ucfirst(str_replace('_', ' ', $payment?->payment_status ?? 'pending')) }}</small>
                                            @php $sShip = $order->shipment; @endphp
                                            @if(($payment?->payment_method ?? 'cod') === 'cod')
                                                @if($sShip?->isCodRemitted())
                                                    <small style="display:block; color:#16a34a; font-weight:700;">✓ COD Remitted by Hub</small>
                                                @elseif($sShip?->isCodHolding())
                                                    <small style="display:block; color:#ea580c; font-weight:700;">⏳ Escrow ({{ $sShip->codHoldingDaysRemaining() }}d holding)</small>
                                                @elseif($sShip?->isCodEligibleForRemittance())
                                                    <small style="display:block; color:#0284c7; font-weight:700;">✓ Awaiting Hub Remittance</small>
                                                @endif
                                            @else
                                                <small style="display:block; color:#007DFE; font-weight:700;">Direct Online Payment</small>
                                            @endif
                                        </td>
                                        <td class="so-status">
                                            @if($isDelivered)
                                                <span class="badge delivered">Delivered</span>
                                            @elseif($isCancelled)
                                                <span class="badge cancelled">Cancelled</span>
                                            @else
                                                <span class="badge {{ $statusLabel }}">{{ ucfirst(str_replace('_', ' ', $statusLabel)) }}</span>
                                            @endif

                                            @if($isRefunded)
                                                <span class="badge refunded" style="margin-top: 4px;" title="Refund processed for this order">
                                                    {{ $payment->payment_status === 'refunded' ? 'Refunded' : 'Partial Refund' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="so-next">
                                            <div class="so-actions">
                                                @if($canPack)
                                                    <form method="POST" action="{{ route('seller.orders.pack', $order) }}">@csrf<button class="button small" type="submit">Mark packed</button></form>
                                                @elseif($needsPickup)
                                                    <a class="button small" href="{{ route('seller.courier', ['status' => 'ready_to_ship', 'order' => $order->id]) }}">Pickup or drop-off</a>
                                                @elseif($order->cancellation_status === 'pending')
                                                    <a class="button small" href="{{ route('seller.orders', ['status' => 'cancellation_requested']) }}">Review cancel</a>
                                                @else
                                                    <span class="so-hint">{{ $isDelivered ? 'Delivered' : ($isCancelled ? 'Cancelled' : ($isRefunded ? 'Refunded' : 'Completed')) }}</span>
                                                @endif
                                                <a class="button small secondary" href="{{ route('seller.orders', ['search' => $payment?->reference]) }}" title="View order in orders page">Orders page →</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        @empty
            <div class="daily-empty-state">
                <p>No order history found matching the selected filters.</p>
            </div>
        @endforelse
    </div>
</section>
<section class="panel"><div class="panel-header"><div><h2>Recent orders</h2><small>The latest activity in your store</small></div><a class="button text" href="{{ route('seller.orders') }}">View all orders →</a></div>@include('seller.partials.orders-table',['orders'=>$recentOrders])</section>
@endsection
