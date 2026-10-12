@extends('layouts.seller')
@section('page_title', 'Inventory')
@section('content')
<div class="page-heading"><div><div class="eyebrow">STOCK MANAGEMENT</div><h1>Inventory</h1><p class="subtitle">Monitor stock by variation, identify shortages, and update available quantities.</p></div><a class="button secondary" href="{{ route('seller.products') }}">Manage products</a></div>
<!-- Real-time Database Stock Metrics & Restock Indicators -->
<div class="metrics">
    <a class="metric {{ request('tab', 'all') === 'all' && !request('search') ? 'featured-light' : '' }}" href="{{ route('seller.inventory', ['tab' => 'all']) }}">
        <div class="metric-top">
            Total products
            @include('seller.partials.icon', ['name' => 'box'])
        </div>
        <div class="metric-value">{{ number_format($stockStats['total'] ?? 0) }}</div>
        <small>{{ number_format($stockStats['total_units'] ?? 0) }} total units in store</small>
    </a>

    <a class="metric {{ request('tab') === 'active' ? 'featured-light' : '' }}" href="{{ route('seller.inventory', ['tab' => 'active']) }}">
        <div class="metric-top">
            Active products
            @include('seller.partials.icon', ['name' => 'check'])
        </div>
        <div class="metric-value">{{ number_format($stockStats['active'] ?? 0) }}</div>
        <small>Live & available to buyers</small>
    </a>

    <a class="metric {{ request('tab') === 'low_stock' ? 'warning-card' : '' }}" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}">
        <div class="metric-top">
            Low stock (Restock soon)
            @include('seller.partials.icon', ['name' => 'trending'])
        </div>
        <div class="metric-value" style="color: {{ ($stockStats['low_stock'] ?? 0) > 0 ? 'var(--amber)' : 'inherit' }};">
            {{ number_format($stockStats['low_stock'] ?? 0) }}
        </div>
        <small>{{ ($stockStats['low_stock'] ?? 0) > 0 ? '10 units or fewer remaining' : 'Stock levels healthy' }}</small>
    </a>

    <a class="metric {{ request('tab') === 'out_of_stock' ? 'danger-card' : '' }}" href="{{ route('seller.inventory', ['tab' => 'out_of_stock']) }}">
        <div class="metric-top">
            Out of stock
            @include('seller.partials.icon', ['name' => 'bag'])
        </div>
        <div class="metric-value" style="color: {{ ($stockStats['out_of_stock'] ?? 0) > 0 ? 'var(--red)' : 'inherit' }};">
            {{ number_format($stockStats['out_of_stock'] ?? 0) }}
        </div>
        <small>{{ ($stockStats['out_of_stock'] ?? 0) > 0 ? 'Requires immediate restock' : 'Zero depleted items' }}</small>
    </a>
</div>

<!-- Restock Recommendation Alert Banner -->
@if(($stockStats['out_of_stock'] ?? 0) > 0 || ($stockStats['low_stock'] ?? 0) > 0)
    <div class="notice" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: linear-gradient(135deg, var(--rose-soft) 0%, var(--surface) 100%); border-color: var(--rose-border); margin-bottom: 24px;">
        <div class="inline">
            <span style="font-size: 18px;">⚠️</span>
            <div>
                <strong>Restock Recommendation:</strong>
                <span class="muted">
                    Mayroon kang 
                    @if(($stockStats['out_of_stock'] ?? 0) > 0)
                        <strong style="color: var(--red);">{{ $stockStats['out_of_stock'] }} out of stock</strong>
                    @endif
                    @if(($stockStats['out_of_stock'] ?? 0) > 0 && ($stockStats['low_stock'] ?? 0) > 0) at @endif
                    @if(($stockStats['low_stock'] ?? 0) > 0)
                        <strong style="color: var(--amber);">{{ $stockStats['low_stock'] }} paubos na (low stock)</strong>
                    @endif
                    na produkto. I-update ang stock para hindi mawalan ng benta.
                </span>
            </div>
        </div>
        <div class="inline">
            @if(($stockStats['out_of_stock'] ?? 0) > 0)
                <a class="button small" style="background: var(--red); border-color: var(--red);" href="{{ route('seller.inventory', ['tab' => 'out_of_stock']) }}">
                    Tingnan ang Out of Stock ({{ $stockStats['out_of_stock'] }}) →
                </a>
            @endif
            @if(($stockStats['low_stock'] ?? 0) > 0)
                <a class="button secondary small" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}">
                    Tingnan ang Low Stock ({{ $stockStats['low_stock'] }}) →
                </a>
            @endif
        </div>
    </div>
@endif


<form class="filters" method="GET">
    <label>Search inventory<input name="search" value="{{ request('search') }}" placeholder="Product name or SKU"></label>
    <label>Stock filter<select name="tab">@foreach(['all' => 'All products', 'active' => 'Active products', 'low_stock' => 'Low stock', 'out_of_stock' => 'Out of stock', 'archived' => 'Archived products'] as $key => $label)<option value="{{ $key }}" @selected(request('tab', 'all') === $key)>{{ $label }}</option>@endforeach</select></label>
    <button class="button" type="submit">Apply filters</button><a class="button secondary" href="{{ route('seller.inventory') }}">Reset</a>
</form>
@forelse($products as $product)
<section class="panel">
    <header class="panel-header"><div class="product-row">
        @if($product->coverImage)<img class="product-image" src="{{ str_starts_with($product->coverImage->path, 'http') || str_starts_with($product->coverImage->path, '/') ? $product->coverImage->path : asset('storage/'.$product->coverImage->path) }}" alt="">@endif
        <div><h2>{{ $product->name }}</h2><p class="muted">{{ $product->variants->count() }} variations &middot; {{ $product->is_active ? 'Active listing' : 'Archived listing' }}</p></div></div>
        <a class="button secondary small" href="{{ route('seller.products', ['search' => $product->name]) }}">Edit product</a>
    </header>
    <form method="POST" action="{{ route('seller.inventory.stock', $product) }}">
        @csrf
        <div class="table-wrap"><table><thead><tr><th>Variation / SKU</th><th>Stock status</th><th>Current units</th><th>Set available quantity</th></tr></thead><tbody>
        @forelse($product->variants as $index => $variant)
            <tr><td><strong>{{ $variant->name }}</strong><small>{{ $variant->sku }}</small></td>
                <td><span class="badge">{{ !$variant->is_active ? 'Inactive variation' : ($variant->stock === 0 ? 'Out of stock' : ($variant->stock <= 10 ? 'Low stock' : 'In stock')) }}</span></td>
                <td class="amount">{{ number_format($variant->stock) }}</td>
                <td><input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}"><input type="hidden" name="variants[{{ $index }}][previous_stock]" value="{{ $variant->stock }}"><label>Available units for {{ $variant->name }}<input type="number" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" min="0" max="1000000" step="1" required @disabled(auth()->user()->status !== 'active' || !$shop?->isApproved())></label></td>
            </tr>
        @empty<tr><td colspan="4">No variations have been added to this product.</td></tr>@endforelse
        </tbody></table></div>
        @if($product->variants->isNotEmpty())<div class="panel-body inline"><button class="button" type="submit" @disabled(auth()->user()->status !== 'active' || !$shop?->isApproved())>Save stock quantities</button><span class="muted">Enter the total available units, not the quantity being added.</span></div>@endif
    </form>
</section>
@empty
<section class="panel"><div class="empty"><h3>No products to show</h3><p>Adjust your filters or add a product to start tracking inventory.</p><a class="button" href="{{ route('seller.products', ['create' => 1]) }}">Add product</a></div></section>
@endforelse
@include('seller.partials.pagination', ['paginator' => $products])
@endsection