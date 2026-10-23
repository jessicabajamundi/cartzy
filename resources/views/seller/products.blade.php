@extends('layouts.seller')
@section('page_title', !session('success') && (request('create') || ($errors->any() && !old('editing_product'))) ? 'Add product' : 'Products')
@section('content')
@if(!session('success') && (request('create') || ($errors->any() && !old('editing_product'))))
    @include('seller.partials.product-editor')
@else
<div class="page-heading">
    <div>
        <div class="eyebrow">YOUR CATALOG</div>
        <h1>Products</h1>
        <p class="subtitle">Manage your listings, photos, variations, pricing, and product visibility.</p>
    </div>
    <div>
        @if(auth()->user()->status !== 'active' || !$shop || !$shop->isApproved())
            <button type="button" class="button" disabled style="opacity:0.65; cursor:not-allowed; background:#9ca3af; display:inline-flex; align-items:center; gap:6px;" title="Administrator approval is required before you can sell or add products.">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Add product (Pending approval)</span>
            </button>
        @else
            <a class="button" id="add-product-trigger" href="{{ route('seller.products', ['create' => 1]) }}">
                + Add product
            </a>
        @endif
    </div>
</div>



<div class="notice">Manage product content and prices here. For restocking and quantity adjustments, open <a href="{{ route('seller.inventory') }}">Inventory &rarr;</a></div>
<form class="filters" method="GET">
    <label>Search products
        <input name="search" value="{{ request('search') }}" placeholder="Product name or SKU">
    </label>
    <label>Show
        <select name="tab">
            <option value="active" @selected(request('tab', 'active') === 'active' || request('tab') === 'all')>Active products ({{ $stockStats['active'] ?? 0 }})</option>
            <option value="low_stock" @selected(request('tab') === 'low_stock')>Low stock ({{ $stockStats['low_stock'] ?? 0 }})</option>
            <option value="out_of_stock" @selected(request('tab') === 'out_of_stock')>Out of stock ({{ $stockStats['out_of_stock'] ?? 0 }})</option>
            <option value="archived" @selected(request('tab') === 'archived')>Archived ({{ $stockStats['archived'] ?? 0 }})</option>
        </select>
    </label>
    <button class="button" type="submit">Apply filters</button>
    <a class="button secondary" href="{{ route('seller.products') }}">Reset</a>
</form>

@if(request('tab') === 'archived')
    <div class="notice" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
        <span>Showing archived products. You can also review, restore, or delete them in your <strong>Archive</strong> section.</span>
        <a class="button secondary small" href="{{ route('seller.archive') }}">Open Archive section &rarr;</a>
    </div>
@endif

<section class="panel">
    @forelse($products as $product)
        @php($totalUnits = $product->variants->sum('stock'))
        @php($isOutOfStock = $totalUnits <= 0)
        @php($isLowStock = !$isOutOfStock && $product->variants->contains(fn($v) => $v->stock <= $product->stock_alert_threshold))

        <div class="panel-header catalog-product-header">
            <div class="product-row">
                @if($product->coverImage)
                    <img class="product-image" src="{{ str_starts_with($product->coverImage->path, 'http') || str_starts_with($product->coverImage->path, '/') ? $product->coverImage->path : asset('storage/'.$product->coverImage->path) }}" alt="">
                @else
                    <span class="product-image">@include('seller.partials.icon',['name'=>'box'])</span>
                @endif
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3>{{ $product->name }}</h3>

                        @if($product->badge)
                            @php($bIcon = match($product->badge) {
                                'Hot Deal' => 'flame',
                                'Best Seller' => 'star',
                                'New Arrival' => 'sparkles',
                                'Trending' => 'trending',
                                'Authentic 100%' => 'shield',
                                'Limited Edition' => 'diamond',
                                default => 'tag',
                            })
                            <span class="badge badge-product-highlight">
                                @include('seller.partials.icon', ['name' => $bIcon, 'size' => 13])
                                <span>{{ $product->badge }}</span>
                            </span>
                        @endif

                        @if(!empty($product->gender) && $product->gender !== 'unisex')
                            <span class="badge badge-gender">
                                @include('seller.partials.icon', ['name' => 'user', 'size' => 12])
                                <span>{{ ucfirst($product->gender) }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="catalog-product-meta">
                        <span>{{ $product->category?->name }} &middot; Target: <strong>{{ ucfirst($product->gender ?? 'unisex') }}</strong></span>
                        @if($product->variants->count() > 1)
                            @include('seller.partials.catalog-variants')
                        @else
                            <span>&middot; {{ $product->variants->count() }} variant</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Health Status & Active Badges -->
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0; flex-wrap: wrap;">
                @if($isOutOfStock)
                    <span class="badge" style="background: var(--red-bg); color: var(--red); border-color: var(--red-border); font-weight: 700;">
                        0 stock · Restock now
                    </span>
                @elseif($isLowStock)
                    <span class="badge" style="background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); font-weight: 700;">
                        Low stock · {{ $totalUnits }} left
                    </span>
                @else
                    <span class="badge" style="background: var(--green-bg); color: var(--green); border-color: var(--green-border);">
                        {{ $totalUnits }} in stock
                    </span>
                @endif

                @if($product->discount_type)
                    <span class="badge">{{ $product->sale_status }}: {{ $product->discount_type === 'percentage' ? number_format($product->discount_value, 2).'%' : 'PHP '.number_format($product->discount_value, 2) }} off per item</span>
                @endif
                <span class="badge {{ $product->is_active ? 'active':'archived' }}">
                    {{ $product->is_active ? 'Active':'Archived' }}
                </span>
            </div>
        </div>

        <div class="editor product-list-editor">
            <div class="product-list-actions">
                <a class="button secondary small" href="{{ route('seller.products.edit', $product->id) }}">Edit product details</a>
                <a class="button secondary small" href="{{ route('seller.products.sale', $product) }}">{{ $product->discount_type ? 'Manage sale' : 'Set up sale' }}</a>
                <form action="{{ route('seller.products.archive', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="button secondary small">{{ $product->is_active ? 'Archive product' : 'Restore product' }}</button>
                </form>
            </div>
            <div class="product-edit-panel" id="product-edit-{{ $product->id }}" @if(old('editing_product') != $product->id) hidden @endif>
                <form action="{{ route('seller.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="stack">
                    @csrf
                    <input type="hidden" name="editing_product" value="{{ $product->id }}">
                    <label>Product name
                        <input name="name" value="{{ old('editing_product') == $product->id ? old('name', $product->name) : $product->name }}" required maxlength="255">
                    </label>

                    <div class="form-grid">
                        <label>Gender / Target audience
                            @php($curGender = old('editing_product') == $product->id ? old('gender', $product->gender) : ($product->gender ?? 'unisex'))
                            <select name="gender">
                                <option value="" @selected(empty($curGender))>Not specified</option>
                                <optgroup label="Adult">
                                    <option value="men" @selected($curGender === 'men')>Men</option>
                                    <option value="women" @selected($curGender === 'women')>Women</option>
                                    <option value="unisex" @selected($curGender === 'unisex')>Unisex</option>
                                </optgroup>
                                <optgroup label="Kids">
                                    <option value="boys" @selected($curGender === 'boys')>Boys</option>
                                    <option value="girls" @selected($curGender === 'girls')>Girls</option>
                                    <option value="kids" @selected($curGender === 'kids' || $curGender === 'all_kids')>All Kids</option>
                                </optgroup>
                            </select>
                        </label>

                        <label>Product badge
                            @php($curBadge = old('editing_product') == $product->id ? old('badge', $product->badge) : $product->badge)
                            <select name="badge">
                                <option value="" @selected(empty($curBadge))>No badge</option>
                                <option value="Hot Deal" @selected($curBadge === 'Hot Deal')>Hot Deal</option>
                                <option value="Best Seller" @selected($curBadge === 'Best Seller')>Best Seller</option>
                                <option value="New Arrival" @selected($curBadge === 'New Arrival')>New Arrival</option>
                                <option value="Trending" @selected($curBadge === 'Trending')>Trending</option>
                                <option value="Authentic 100%" @selected($curBadge === 'Authentic 100%')>Authentic 100%</option>
                                <option value="Limited Edition" @selected($curBadge === 'Limited Edition')>Limited Edition</option>
                            </select>
                        </label>
                    </div>

                    <label>Description
                        <textarea name="description" maxlength="5000">{{ old('editing_product') == $product->id ? old('description', $product->description) : $product->description }}</textarea>
                    </label>

                    <div class="variants">
                        @foreach($product->variants as $index => $variant)
                            <div class="variant-row">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if($variant->image_url)
                                        <img src="{{ $variant->image_url }}" alt="" style="width: 38px; height: 38px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); flex-shrink: 0;">
                                    @elseif(!empty($variant->options['color']))
                                        <span style="width: 24px; height: 24px; border-radius: 50%; background: {{ $variant->options['color'] }}; display: inline-block; border: 1px solid rgba(0,0,0,0.15); flex-shrink: 0;"></span>
                                    @endif
                                    <div>
                                        <strong>{{ $variant->name }}<small class="muted"> · {{ $variant->sku }}</small></strong>
                                        @if($variant->stock <= 0)
                                            <span class="badge" style="background: var(--red-bg); color: var(--red); border-color: var(--red-border); font-size: 10px; margin-left: 6px;">
                                                0 units · Out of stock
                                            </span>
                                        @elseif($variant->stock <= $product->stock_alert_threshold)
                                            <span class="badge" style="background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); font-size: 10px; margin-left: 6px;">
                                                {{ $variant->stock }} left · Low stock
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                <label>Shade / Variant name
                                    <input type="text" name="variants[{{ $index }}][name]" value="{{ old('editing_product') == $product->id ? old('variants.'.$index.'.name', $variant->name) : $variant->name }}" maxlength="255">
                                </label>
                                <label>Regular price (PHP)
                                    <input type="number" name="variants[{{ $index }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ old('editing_product') == $product->id ? old('variants.'.$index.'.price', $variant->price) : $variant->price }}" required>
                                </label>
                                @if($product->discount_type)
                                    <small>Current price: <strong>PHP {{ number_format($variant->sale_price_minor / 100, 2) }}</strong> per item</small>
                                @endif
                                <a class="button secondary small" href="{{ route('seller.inventory', ['search' => $variant->sku]) }}">Manage stock</a>
                                <label>Update photo <small class="muted">· Optional</small>
                                    <input type="file" name="variants[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp">
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <button type="submit" class="button">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="empty">
            <div class="empty-icon">@include('seller.partials.icon',['name'=>'box'])</div>
            <h3>No products to show</h3>
            <p>Add your first product above, or adjust your search and filters.</p>
        </div>
    @endforelse

    @include('seller.partials.pagination',['paginator'=>$products])
</section>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.product-edit-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        const expanded = button.getAttribute('aria-expanded') !== 'true';
        button.setAttribute('aria-expanded', String(expanded));
        panel.hidden = !expanded;
    });
});
</script>
<script src="{{ asset('js/seller-inventory.js') }}?v={{ filemtime(public_path('js/seller-inventory.js')) }}"></script>
<script src="{{ asset('js/seller-color-wheel.js') }}?v={{ filemtime(public_path('js/seller-color-wheel.js')) }}"></script>
@endpush
