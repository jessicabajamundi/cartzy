@extends('layouts.seller')
@section('page_title', 'Order History')
@section('content')

<div class="page-heading">
    <div>
        <div class="eyebrow">ORDER ARCHIVE &amp; TIMELINE</div>
        <h1>Order history</h1>
        <p class="subtitle">Complete record of all orders placed in your shop with product photos, buyer details, and fulfillment statuses.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a class="button secondary" href="{{ route('seller.orders') }}">
            @include('seller.partials.icon', ['name' => 'bag', 'size' => 16])
            <span>Active orders &rarr;</span>
        </a>
        <a class="button secondary" href="{{ route('seller.deliveries') }}">
            @include('seller.partials.icon', ['name' => 'truck', 'size' => 16])
            <span>Deliveries &rarr;</span>
        </a>
    </div>
</div>

{{-- Summary Metric Cards --}}
<div class="metrics order-history-metrics">
    <div class="metric-card">
        <div class="metric-top">
            <span>Total orders in history</span>
            @include('seller.partials.icon', ['name' => 'bag', 'size' => 18])
        </div>
        <div class="metric-value">{{ number_format($summary['total_orders']) }}</div>
        <small>All-time shop orders</small>
    </div>
    <div class="metric-card">
        <div class="metric-top">
            <span>Delivered &amp; completed</span>
            @include('seller.partials.icon', ['name' => 'check', 'size' => 18])
        </div>
        <div class="metric-value" style="color: #15803d;">{{ number_format($summary['delivered_orders']) }}</div>
        <small>Successfully delivered parcels</small>
    </div>
    <div class="metric-card">
        <div class="metric-top">
            <span>Cancelled orders</span>
            @include('seller.partials.icon', ['name' => 'cancel', 'size' => 18])
        </div>
        <div class="metric-value" style="color: #b91c1c;">{{ number_format($summary['cancelled_orders']) }}</div>
        <small>Customer / seller cancellations</small>
    </div>
    <div class="metric-card">
        <div class="metric-top">
            <span>Refunded orders</span>
            @include('seller.partials.icon', ['name' => 'wallet', 'size' => 18])
        </div>
        <div class="metric-value" style="color: #7c3aed;">{{ number_format($summary['refunded_orders']) }}</div>
        <small>Processed returns &amp; refunds</small>
    </div>
    <div class="metric-card">
        <div class="metric-top">
            <span>Delivered gross sales</span>
            @include('seller.partials.icon', ['name' => 'chart', 'size' => 18])
        </div>
        <div class="metric-value">₱{{ number_format($summary['gross_sales'] / 100, 2) }}</div>
        <small>Delivered order value</small>
    </div>
</div>

{{-- Filter and Search Controls --}}
<form class="filters order-history-filters" method="GET" action="{{ route('seller.order-history') }}">
    <label class="search-field">
        Search order history
        <input name="search" value="{{ request('search') }}" placeholder="Order #, buyer, product name, or tracking code...">
    </label>

    <label>
        Status
        <select name="status" onchange="this.form.submit()">
            <option value="all" @selected(request('status', 'all') === 'all')>All statuses ({{ $statusCounts['all'] }})</option>
            <option value="delivered" @selected(request('status') === 'delivered')>Delivered &amp; Completed ({{ $statusCounts['delivered'] }})</option>
            <option value="shipped" @selected(request('status') === 'shipped')>In transit / Shipped ({{ $statusCounts['shipped'] }})</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled ({{ $statusCounts['cancelled'] }})</option>
            <option value="refunded" @selected(request('status') === 'refunded')>Refunded ({{ $statusCounts['refunded'] }})</option>
            <option value="ready_to_ship" @selected(request('status') === 'ready_to_ship')>Ready to ship</option>
            <option value="to_prepare" @selected(request('status') === 'to_prepare')>To prepare / packed</option>
        </select>
    </label>

    <label>
        Date range
        <select name="range" onchange="this.form.submit()">
            <option value="all" @selected(request('range', 'all') === 'all')>All time</option>
            <option value="today" @selected(request('range') === 'today')>Today only</option>
            <option value="yesterday" @selected(request('range') === 'yesterday')>Yesterday</option>
            <option value="7" @selected(request('range') === '7')>Last 7 days</option>
            <option value="30" @selected(request('range') === '30')>Last 30 days</option>
            <option value="this_month" @selected(request('range') === 'this_month')>This month</option>
        </select>
    </label>

    <label>
        Sort by
        <select name="sort" onchange="this.form.submit()">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest first</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
        </select>
    </label>

    <button class="button" type="submit">Filter</button>
    <a class="button secondary" href="{{ route('seller.order-history') }}">Reset</a>
</form>

{{-- Order History List with Product Pictures --}}
<section class="panel order-history-panel">
    <div class="panel-header order-history-header">
        <div>
            <h2>Orders Timeline</h2>
            <small>Showing {{ $orders->total() }} recorded {{ Str::plural('order', $orders->total()) }}</small>
        </div>
        <div class="order-history-pills">
            <a href="{{ route('seller.order-history', ['status' => 'all']) }}" class="history-chip {{ request('status', 'all') === 'all' ? 'active' : '' }}">All ({{ $statusCounts['all'] }})</a>
            <a href="{{ route('seller.order-history', ['status' => 'delivered']) }}" class="history-chip {{ request('status') === 'delivered' ? 'active' : '' }}">Delivered ({{ $statusCounts['delivered'] }})</a>
            <a href="{{ route('seller.order-history', ['status' => 'cancelled']) }}" class="history-chip {{ request('status') === 'cancelled' ? 'active' : '' }}">Cancelled ({{ $statusCounts['cancelled'] }})</a>
            <a href="{{ route('seller.order-history', ['status' => 'refunded']) }}" class="history-chip {{ request('status') === 'refunded' ? 'active' : '' }}">Refunded ({{ $statusCounts['refunded'] }})</a>
        </div>
    </div>

    <div class="order-history-list">
        @forelse($orders as $order)
            @php
                $payment = $order->order;
                $shipment = $order->shipment;
                $isRefunded = in_array($payment?->payment_status, ['refunded', 'partially_refunded'], true);
                $isCancelled = $order->status === 'cancelled';
                $isDelivered = in_array($order->status, ['delivered', 'completed'], true);
                $statusLabel = $order->status === 'shipped' && $shipment ? $shipment->status : $order->status;
                $items = $order->items;
                $totalUnits = $items->sum('quantity');
                $address = $payment?->shipping_address ?? [];
                $tracking = app(\App\Services\OrderTracking::class)->build($order);
                $manilaTime = fn ($date) => $date?->copy()->timezone('Asia/Manila')->format('M d, Y · h:i A');
            @endphp
            <article class="order-history-card {{ $isCancelled ? 'is-cancelled' : '' }} {{ $isDelivered ? 'is-delivered' : '' }} {{ $isRefunded ? 'is-refunded' : '' }}">
                {{-- Card Header: Order Reference, Date, Buyer, Status --}}
                <div class="oh-card-header">
                    <div class="oh-meta">
                        <div class="oh-ref-row">
                            <strong class="oh-ref">{{ $payment?->reference ?? 'ORD-'.$order->id }}</strong>
                            <time class="oh-date">{{ $order->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</time>
                            @if($shipment?->tracking_code)
                                <span class="oh-tracking-pill" title="Shipment tracking code">
                                    @include('seller.partials.icon', ['name' => 'truck', 'size' => 12])
                                    {{ $shipment->tracking_code }}
                                </span>
                            @endif
                        </div>
                        <div class="oh-buyer-row">
                            <span>Buyer: <strong>{{ $payment?->buyer?->name ?? 'Guest Buyer' }}</strong></span>
                            @if($payment?->buyer?->phone)
                                <span class="muted">· {{ $payment->buyer->phone }}</span>
                            @endif
                            <a class="oh-chat-link" href="{{ route('seller.chat', ['contact' => $order->id]) }}" title="Message buyer regarding this order">
                                @include('seller.partials.icon', ['name' => 'chat', 'size' => 14])
                                <span>Message</span>
                            </a>
                        </div>
                    </div>

                    <div class="oh-status-wrap">
                        @if($isDelivered)
                            <span class="badge delivered">
                                @include('seller.partials.icon', ['name' => 'check', 'size' => 13])
                                Delivered
                            </span>
                        @elseif($isCancelled)
                            <span class="badge cancelled">
                                @include('seller.partials.icon', ['name' => 'cancel', 'size' => 13])
                                Cancelled
                            </span>
                        @else
                            <span class="badge {{ $statusLabel }}">{{ ucfirst(str_replace('_', ' ', $statusLabel)) }}</span>
                        @endif

                        @if($isRefunded)
                            <span class="badge refunded" title="Refund processed">
                                @include('seller.partials.icon', ['name' => 'wallet', 'size' => 13])
                                {{ $payment->payment_status === 'refunded' ? 'Refunded' : 'Partial Refund' }}
                            </span>
                        @endif

                        <span class="oh-payment-badge">
                            {{ strtoupper($payment?->payment_method ?? 'COD') }} · {{ ucfirst(str_replace('_', ' ', $payment?->payment_status ?? 'pending')) }}
                        </span>
                    </div>
                </div>

                {{-- Product Items with Pictures --}}
                <div class="oh-items-section">
                    <div class="oh-items-header">
                        <span>Items in this order ({{ $items->count() }} {{ Str::plural('product', $items->count()) }} · {{ $totalUnits }} {{ Str::plural('unit', $totalUnits) }})</span>
                    </div>
                    <div class="oh-items-grid">
                        @foreach($items as $item)
                            @php
                                $thumb = $item->thumbnail_url;
                            @endphp
                            <div class="oh-item-row">
                                <div class="oh-item-media">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $item->product_name }}" class="oh-item-img" loading="lazy">
                                    @else
                                        <div class="oh-item-img-placeholder">
                                            @include('seller.partials.icon', ['name' => 'bag', 'size' => 24])
                                        </div>
                                    @endif
                                </div>
                                <div class="oh-item-info">
                                    <h4 class="oh-item-name">{{ $item->product_name }}</h4>
                                    @if($item->variant_name)
                                        <div class="oh-item-variant">
                                            <span>Variation:</span> <strong>{{ $item->variant_name }}</strong>
                                        </div>
                                    @endif
                                    <div class="oh-item-math">
                                        <span class="oh-qty">Qty: <strong>{{ $item->quantity }}</strong></span>
                                        <span class="muted">×</span>
                                        <span class="oh-price">₱{{ number_format($item->unit_price_minor / 100, 2) }}</span>
                                    </div>
                                </div>
                                <div class="oh-item-subtotal">
                                    <small>Item subtotal</small>
                                    <strong>₱{{ number_format(($item->unit_price_minor * $item->quantity) / 100, 2) }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Cancellation Note if applicable --}}
                @if($isCancelled && $order->cancellation_reason)
                    <div class="oh-notice oh-notice-cancelled">
                        <strong>Reason for Cancellation:</strong>
                        <p>{{ $order->cancellation_reason }}</p>
                    </div>
                @endif

                {{-- Card Footer: Delivery info, Total, Actions --}}
                <div class="oh-card-footer">
                    <div class="oh-delivery-info">
                        @if($shipment?->logisticsProvider)
                            <div class="oh-delivery-line">
                                @include('seller.partials.icon', ['name' => 'truck', 'size' => 15])
                                <span>Courier: <strong>{{ $shipment->logisticsProvider->name }}</strong></span>
                                @if($order->delivered_at)
                                    <span class="muted">· Delivered on {{ $manilaTime($order->delivered_at) }}</span>
                                @endif
                            </div>
                        @endif
                        @if(!empty($address))
                            <div class="oh-address-line" title="Delivery address">
                                <small class="muted">Ship to:</small>
                                <small>{{ $address['recipient'] ?? $payment?->buyer?->name }} — {{ $address['address'] ?? implode(', ', array_filter(Arr::only($address, ['line1', 'barangay', 'city', 'province']))) }}</small>
                            </div>
                        @endif
                    </div>

                    <div class="oh-total-action">
                        <div class="oh-total-block">
                            <span class="oh-total-label">Total Amount</span>
                            <span class="oh-total-amount">₱{{ number_format($order->buyerDueMinor() / 100, 2) }}</span>
                        </div>

                        <div class="oh-action-btns">
                            @if($shipment && !in_array($order->status, ['cancelled'], true))
                                <a class="button small secondary" href="{{ route('seller.orders.waybill', ['id' => $order->id, 'print' => 1]) }}" target="_blank">
                                    @include('seller.partials.icon', ['name' => 'printer', 'size' => 14])
                                    <span>Waybill</span>
                                </a>
                            @endif
                            <a class="button small" href="{{ route('seller.orders', ['search' => $payment?->reference]) }}" title="View in Orders Management">
                                <span>Manage Order &rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="order-history-empty">
                <div class="empty-icon">
                    @include('seller.partials.icon', ['name' => 'clock', 'size' => 42])
                </div>
                <h3>No order history found</h3>
                <p>No orders were found matching your current filter criteria. Try resetting your search or filters.</p>
                <a href="{{ route('seller.order-history') }}" class="button secondary" style="margin-top: 14px;">Reset Filters</a>
            </div>
        @endforelse
    </div>

    @if($orders)
        <div style="margin-top: 24px;">
            @include('seller.partials.pagination', ['paginator' => $orders])
        </div>
    @endif
</section>

@endsection
