@extends('layouts.seller')
@section('page_title', 'Inventory')
@section('content')
@php
    $tab = request('tab', 'all');
    $canEdit = auth()->user()->status === 'active' && $shop?->isApproved();
    $statusOf = fn ($variant, $limit) => !$variant->is_active ? 'inactive' : ($variant->stock === 0 ? 'out' : ($variant->stock <= $limit ? 'low' : 'ok'));
    $statusLabel = ['ok' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock', 'inactive' => 'Hidden'];
    $peso = fn ($minor) => '₱'.number_format($minor / 100, 2);
    $keep = array_filter(['search' => request('search'), 'category' => request('category')]);
@endphp
<link rel="stylesheet" href="{{ asset('css/seller-inventory.css') }}?v={{ filemtime(public_path('css/seller-inventory.css')) }}">
<div class="inv-page">
    <header class="inv-header">
        <div>
            <div class="eyebrow">STOCK OVERVIEW</div>
            <h1>Inventory</h1>
            <p>Monitor stock levels across your shop. To restock, open the product with Edit.</p>
        </div>
        <a class="button secondary" href="{{ route('seller.products') }}">Manage products</a>
    </header>

    <nav class="inv-stats" aria-label="Stock summary">
        @foreach([
            'all' => ['All products', $stockStats['active'] ?? 0, 'Listed in your shop', 'box', ''],
            'in_stock' => ['In stock', $stockStats['in_stock'] ?? 0, 'Products', 'check', 'is-ok'],
            'low_stock' => ['Low stock', $stockStats['low_stock'] ?? 0, 'Restock soon', 'trending', 'is-low'],
            'out_of_stock' => ['Out of stock', $stockStats['out_of_stock'] ?? 0, 'Products', 'cancel', 'is-out'],
        ] as $key => [$label, $count, $hint, $icon, $tone])
            <a class="inv-stat {{ $tone }} {{ $count ? 'has-value' : '' }}" href="{{ route('seller.inventory', $key === 'all' ? $keep : ['tab' => $key] + $keep) }}" @if($tab === $key) aria-current="page" @endif>
                <span class="inv-stat-label">@include('seller.partials.icon', ['name' => $icon, 'size' => 16]) {{ $label }}</span>
                <strong>{{ number_format($count) }}</strong>
                <small>{{ $hint }}</small>
            </a>
        @endforeach
    </nav>

    <section class="inv-panel">
        <form class="inv-filters" method="GET" role="search">
            <label class="inv-field is-search">Search
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Product name or SKU">
            </label>
            <label class="inv-field">Category
                <select name="category">
                    <option value="">All categories</option>
                    @foreach($inventoryCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="inv-field">Stock level
                <select name="tab">
                    @foreach(['all' => 'All stock levels', 'in_stock' => 'In stock', 'low_stock' => 'Low stock', 'out_of_stock' => 'Out of stock', 'archived' => 'Archived products'] as $key => $label)
                        <option value="{{ $key }}" @selected($tab === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="inv-filter-actions">
                <button class="button" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 4h18l-7 8.5V19l-4 2v-8.5z"/></svg>
                    Apply
                </button>
                @if($keep || $tab !== 'all')
                    <a class="inv-clear" href="{{ route('seller.inventory') }}">Clear</a>
                @endif
            </div>
        </form>

        @if($products->isNotEmpty())
            <table class="inv-table">
                <thead>
                    <tr><th>Product</th><th class="is-num">Price</th><th>Stock</th><th>Status</th><th><span class="inv-sr">Actions</span></th></tr>
                </thead>
                @foreach($products as $product)
                    @php
                        $live = $product->variants->where('is_active', true);
                        $statuses = $product->variants->map(fn ($variant) => $statusOf($variant, $product->stock_alert_threshold));
                        $units = $live->sum('stock');
                        $worst = $units === 0 ? 'out' : ($statuses->contains('low') ? 'low' : 'ok');
                        $prices = $product->variants->pluck('price_minor')->filter(fn ($p) => $p !== null);
                        $image = $product->coverImage?->path;
                        $skus = $product->variants->pluck('sku');
                        $editorId = 'stock-editor-'.$product->id;
                    @endphp
                    <tbody class="inv-group">
                        <tr class="inv-tr">
                            <td data-label="Product">
                                <div class="inv-product">
                                    @if($image)
                                        <img class="inv-thumb" src="{{ str_starts_with($image, 'http') || str_starts_with($image, '/') ? $image : Storage::disk('public')->url($image) }}" alt="">
                                    @else
                                        <span class="inv-thumb is-empty">@include('seller.partials.icon', ['name' => 'box', 'size' => 20])</span>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <small>{{ $product->category?->name ?? 'Uncategorized' }}@if($skus->isNotEmpty()) · SKU {{ $skus->first() }}@if($skus->count() > 1) +{{ $skus->count() - 1 }}@endif @endif</small>
                                    </div>
                                </div>
                            </td>
                            <td class="is-num inv-price" data-label="Price">
                                @if($prices->isEmpty())
                                    —
                                @elseif($prices->min() === $prices->max())
                                    {{ $peso($prices->min()) }}
                                @else
                                    {{ $peso($prices->min()) }}<small>to {{ $peso($prices->max()) }}</small>
                                @endif
                            </td>
                            <td data-label="Stock">
                                <strong class="inv-units">{{ number_format($units) }}</strong>
                                <small>{{ $product->variants->count() > 1 ? $product->variants->count().' variations · ' : '' }}Alert at {{ $product->stock_alert_threshold }}</small>
                            </td>
                            <td data-label="Status">
                                <span class="inv-pill is-{{ $worst }}">{{ $statusLabel[$worst] }}</span>
                                @if($product->is_active)
                                    <small class="inv-visibility is-visible">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Visible to buyers
                                    </small>
                                @else
                                    <small class="inv-visibility">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l18 18 M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.1 M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6 M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                        Hidden (archived)
                                    </small>
                                @endif
                            </td>
                            <td class="inv-actions">
                                <button type="button" class="inv-edit-btn" data-editor-toggle aria-expanded="false" aria-controls="{{ $editorId }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25zM20.7 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <tr class="inv-editor-row" id="{{ $editorId }}" hidden>
                            <td colspan="5">
                                <div class="inv-editor">
                                    <div class="inv-editor-head">
                                        <div>
                                            <strong>Update stock</strong>
                                            <span>Type how many units you have <b>right now</b>, or use − and +.</span>
                                        </div>
                                        <a href="{{ route('seller.products', ['search' => $product->name]) }}">Edit name, price &amp; photos →</a>
                                    </div>
                                    @if($product->variants->isEmpty())
                                        <p class="inv-novariants">No variations yet. <a href="{{ route('seller.products', ['search' => $product->name]) }}">Add one in Products</a></p>
                                    @else
                                        <form method="POST" action="{{ route('seller.inventory.stock', $product) }}" data-stock-form>
                                            @csrf
                                            @foreach($product->variants as $index => $variant)
                                                @php($status = $statusOf($variant, $product->stock_alert_threshold))
                                                <div class="inv-row" data-stock-row data-active="{{ $variant->is_active ? 1 : 0 }}" data-limit="{{ $product->stock_alert_threshold }}">
                                                    <div class="inv-variant">
                                                        <strong>{{ $variant->name }}</strong>
                                                        <small>SKU {{ $variant->sku }} · {{ $peso($variant->price_minor) }}</small>
                                                    </div>
                                                    <span class="inv-pill is-{{ $status }}" data-stock-status>{{ $statusLabel[$status] }}</span>
                                                    <div class="inv-qty">
                                                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                                        <input type="hidden" name="variants[{{ $index }}][previous_stock]" value="{{ $variant->stock }}">
                                                        <div class="inv-stepper">
                                                            <button type="button" data-step="-1" aria-label="Remove one unit of {{ $variant->name }}" @disabled(!$canEdit)>−</button>
                                                            <input type="number" inputmode="numeric" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" data-original="{{ $variant->stock }}" min="0" max="1000000" step="1" required aria-label="Units of {{ $variant->name }}" @disabled(!$canEdit)>
                                                            <button type="button" data-step="1" aria-label="Add one unit of {{ $variant->name }}" @disabled(!$canEdit)>+</button>
                                                        </div>
                                                        <span class="inv-diff" data-stock-diff aria-live="polite"></span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($canEdit)
                                                <div class="inv-savebar" data-stock-savebar hidden>
                                                    <span data-stock-summary></span>
                                                    <div>
                                                        <button type="button" class="button secondary small" data-stock-cancel>Cancel</button>
                                                        <button type="submit" class="button small">Save stock quantities</button>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="inv-locked">Stock editing unlocks once your store is approved.</p>
                                            @endif
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        @else
            <div class="inv-empty">
                <span class="inv-empty-icon">@include('seller.partials.icon', ['name' => request('search') ? 'search' : 'box', 'size' => 24])</span>
                @if(request('search') || request('category'))
                    <h3>No matching products</h3>
                    <p>Try a different search or category, or <a href="{{ route('seller.inventory') }}">clear the filters</a>.</p>
                @elseif($tab === 'in_stock' || $tab === 'low_stock' || $tab === 'out_of_stock')
                    <h3>Nothing here</h3>
                    <p>No products are {{ ['in_stock' => 'fully in stock', 'low_stock' => 'running low', 'out_of_stock' => 'out of stock'][$tab] }} right now.</p>
                @elseif($tab === 'archived')
                    <h3>No archived products</h3>
                    <p>Products you archive will show up here.</p>
                @else
                    <h3>No products yet</h3>
                    <p>Add your first product to start tracking stock.</p>
                    <a class="button" href="{{ route('seller.products', ['create' => 1]) }}">Add product</a>
                @endif
            </div>
        @endif
    </section>

    @include('seller.partials.pagination', ['paginator' => $products])
</div>
<script src="{{ asset('js/seller-stock.js') }}?v={{ filemtime(public_path('js/seller-stock.js')) }}" defer></script>
@endsection
