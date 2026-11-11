@extends('layouts.admin')
@section('page_title', 'Overview')
@section('page_description', 'A clear view of your marketplace and the work that needs attention.')
@section('content')
<section class="overview-banner">
    <div class="overview-copy"><span class="eyebrow"><span class="welcome-dot" aria-hidden="true"></span> YOUR MARKETPLACE, AT A GLANCE</span><h2>Welcome back, {{ auth()->user()->name }}.</h2><p>A little oversight. A better marketplace.<br>Everything you need to keep Cartzy moving.</p><a class="button" href="{{ route('admin.reports') }}">Explore reports @include('admin.partials.icon', ['name' => 'arrow'])</a></div>
    <a class="review-spotlight" href="{{ route('admin.registrations', ['status' => 'pending']) }}">
        <span class="spotlight-heading">@include('admin.partials.icon', ['name' => 'file']) Registration desk</span>
        <strong>{{ number_format($stats['pending']) }}</strong>
        <span>applications awaiting review</span><span class="review-caption">{{ $stats['pending'] > 0 ? 'Your next step toward a safer marketplace.' : 'All caught up. Your review queue is clear.' }}</span>
        <span class="spotlight-action">Review registrations @include('admin.partials.icon', ['name' => 'arrow'])</span>
    </a>
</section>
<div class="section-heading"><div><span class="eyebrow">PERFORMANCE</span><h2>Marketplace overview</h2></div><span class="muted">All-time totals</span></div>
<div class="stats-grid dashboard-stats">
    <article class="stat-card"><div class="metric-heading"><span class="metric-icon">@include('admin.partials.icon', ['name' => 'chart'])</span><span>Sales</span></div><span>Completed merchandise sales</span><strong>₱{{ number_format($stats['sales'], 2) }}</strong><small>Paid, delivered or completed orders</small></article>
    <article class="stat-card"><div class="metric-heading"><span class="metric-icon">@include('admin.partials.icon', ['name' => 'wallet'])</span><span>Earnings</span></div><span>Earned commission</span><strong>₱{{ number_format($stats['commission'], 2) }}</strong><small>Actual recorded order commission</small></article>
    <article class="stat-card"><div class="metric-heading"><span class="metric-icon">@include('admin.partials.icon', ['name' => 'box'])</span><span>Orders</span></div><span>Total orders</span><strong>{{ number_format($stats['orders']) }}</strong><small>Buyer orders across all stores</small></article>
    <a class="stat-card" href="{{ route('admin.registrations', ['status' => 'pending']) }}"><div class="metric-heading"><span class="metric-icon">@include('admin.partials.icon', ['name' => 'file'])</span><span>Review queue</span></div><span>Awaiting review</span><strong>{{ number_format($stats['pending']) }}</strong><small>Identity and account applications →</small></a>
</div>
<div class="section-heading quick-heading"><div><span class="eyebrow">YOUR WORKSPACE</span><h2>Keep things moving</h2></div><span class="muted">Everyday essentials</span></div>
<nav class="quick-actions" aria-label="Quick actions">
    <a href="{{ route('admin.products') }}">@include('admin.partials.icon', ['name' => 'box'])<span><strong>Seller products @if(!empty($stats['pending_products']))<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px;">{{ $stats['pending_products'] }} pending</span>@endif</strong><small>Review & approve seller listings</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.registrations') }}">@include('admin.partials.icon', ['name' => 'file'])<span><strong>Store registrations @if(!empty($adminPendingRegistrationsCount))<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px;">{{ $adminPendingRegistrationsCount }} pending</span>@endif</strong><small>Review merchant & partner applications</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.id-checks') }}">@include('admin.partials.icon', ['name' => 'shield'])<span><strong>ID checks @if(!empty($adminPendingIdChecksCount))<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px; background: #EFF6FF; color: #1E40AF;">{{ $adminPendingIdChecksCount }} pending</span>@endif</strong><small>Inspect & verify buyer government IDs</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.logistics') }}">@include('admin.partials.icon', ['name' => 'truck'])<span><strong>Logistics partners @if(!empty($adminPendingLogisticsCount))<span class="badge warning" style="margin-left: 6px; font-size: 10px; padding: 2px 6px; background: #FEF3C7; color: #B45309;">{{ $adminPendingLogisticsCount }} pending</span>@endif</strong><small>Delivery companies & rider fleets</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.compliance') }}">@include('admin.partials.icon', ['name' => 'shield'])<span><strong>Product compliance</strong><small>Category & license checks</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
</nav>
<div class="population-strip">
    @foreach(['buyers' => 'Buyers', 'sellers' => 'Seller accounts', 'couriers' => 'Courier accounts', 'products' => 'Products'] as $key => $label)
    <div>
        @if($key === 'products')
            <a href="{{ route('admin.products') }}" style="display: flex; align-items: center; justify-content: center; gap: 12px; color: inherit; width: 100%;">
                @include('admin.partials.icon', ['name' => 'box'])
                <span class="population-number">{{ number_format($stats[$key]) }}</span>
                <span>{{ $label }}</span>
            </a>
        @else
            @include('admin.partials.icon', ['name' => 'users'])
            <span class="population-number">{{ number_format($stats[$key]) }}</span>
            <span>{{ $label }}</span>
        @endif
    </div>
    @endforeach
</div>
<section class="panel">
    <header class="panel-header">
        <div>
            <h2>Registered stores</h2>
            <p>All registered merchant storefronts across your marketplace</p>
        </div>
        <span class="muted">{{ $stores->total() }} total {{ \Illuminate\Support\Str::plural('store', $stores->total()) }}</span>
    </header>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Store</th>
                    <th>Owner</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th style="text-align: right;">Storefront</th>
                </tr>
            </thead>
            <tbody>
            @forelse($stores as $store)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($store->logoUrl())
                                <img src="{{ $store->logoUrl() }}" alt="{{ $store->name }}" style="width: 32px; height: 32px; border-radius: 8px; object-fit: cover; border: 1px solid var(--line);">
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--accent-light, #FAF8FC); color: var(--accent, #6F6382); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; border: 1px solid var(--line);">
                                    {{ strtoupper(substr($store->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <strong>{{ $store->name }}</strong>
                                @if($store->slug)
                                    <small class="muted" style="display: block;">/store/{{ $store->slug }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $store->user?->name ?? 'Unknown owner' }}</strong>
                        <small class="muted" style="display: block;">{{ $store->user?->email ?? 'No email' }}</small>
                    </td>
                    <td>
                        <span>{{ number_format($store->products_count ?? 0) }} {{ \Illuminate\Support\Str::plural('item', $store->products_count ?? 0) }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $store->status === 'approved' ? 'good' : ($store->status === 'pending' ? 'warning' : '') }}">
                            {{ ucfirst($store->status ?? ($store->user?->status ?? 'pending')) }}
                        </span>
                    </td>
                    <td class="nowrap">
                        {{ $store->created_at ? $store->created_at->timezone('Asia/Manila')->format('M j, Y') : 'N/A' }}
                    </td>
                    <td style="text-align: right;">
                        @if($store->slug)
                            <a href="{{ route('store.show', $store->slug) }}" target="_blank" rel="noopener" class="button subtle" style="padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                <span>View Store</span>
                                @include('admin.partials.icon', ['name' => 'arrow'])
                            </a>
                        @else
                            <span class="muted" style="font-size: 11px;">No storefront</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <span class="icon">@include('admin.partials.icon', ['name' => 'box'])</span>
                            <strong>No registered stores found</strong>
                            <p>Stores will appear here as soon as sellers complete registration.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $stores])
</section>

<section class="panel">
    <header class="panel-header">
        <div>
            <h2>Recent activity</h2>
            <p>Recorded administrator actions and events</p>
        </div>
        <a class="text-link" href="{{ route('admin.activity-logs') }}">View all activity →</a>
    </header>
    @include('admin.partials.activity')
</section>
@endsection
