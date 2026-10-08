@extends('layouts.seller')
@section('page_title', 'Inventory')
@section('content')
<div class="page-heading">
    <div>
        <div class="eyebrow">YOUR CATALOG</div>
        <h1>Product inventory</h1>
        <p class="subtitle">Keep product details, target audience, badges, prices, and available stock up to date.</p>
    </div>
    <div>
        <button type="button" class="button" onclick="const el = document.getElementById('add-product'); el.open = !el.open; if(el.open) el.scrollIntoView({behavior: 'smooth'});">
            + Add product
        </button>
    </div>
</div>

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

<details id="add-product" class="panel" @if(request('create') || ($errors->any() && !old('variants'))) open @endif>
    <summary style="display: none;"></summary>
    <div class="panel-body">
        @if(!$shop)
            <p>Save your <a href="{{ route('seller.account') }}">store profile</a> first.</p>
        @elseif($categories->isEmpty())
            <p class="muted">An administrator needs to add an active product category before you can create products.</p>
        @else
            <form action="{{ route('seller.inventory.add') }}" method="POST" enctype="multipart/form-data" class="form-grid">
                @csrf
                <label>Product name
                    <input name="name" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Wireless Noise-Cancelling Headphones">
                </label>

                <label>Category
                    <select name="category_id" required>
                        <option value="">Choose a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Gender / Target audience <small class="muted">· Sino ang pwedeng gumamit</small>
                    <select name="gender">
                        <option value="unisex" @selected(old('gender', 'unisex') === 'unisex')>Unisex (Panglahat)</option>
                        <option value="men" @selected(old('gender') === 'men')>Men (Pang-lalaki)</option>
                        <option value="women" @selected(old('gender') === 'women')>Women (Pang-babae)</option>
                        <option value="kids" @selected(old('gender') === 'kids')>Kids (Pang-bata)</option>
                    </select>
                </label>

                <label>Product badge <small class="muted">· Optional tag</small>
                    <select name="badge">
                        <option value="" @selected(old('badge') == '')>No badge</option>
                        <option value="Hot Deal" @selected(old('badge') === 'Hot Deal')>Hot Deal</option>
                        <option value="Best Seller" @selected(old('badge') === 'Best Seller')>Best Seller</option>
                        <option value="New Arrival" @selected(old('badge') === 'New Arrival')>New Arrival</option>
                        <option value="Trending" @selected(old('badge') === 'Trending')>Trending</option>
                        <option value="Authentic 100%" @selected(old('badge') === 'Authentic 100%')>Authentic 100%</option>
                        <option value="Limited Edition" @selected(old('badge') === 'Limited Edition')>Limited Edition</option>
                    </select>
                </label>

                <label>SKU
                    <input name="sku" value="{{ old('sku') }}" maxlength="100" placeholder="e.g. PROD-001" required>
                </label>

                <label>Price (PHP)
                    <input type="number" name="price" min="0.01" max="99999999.99" step="0.01" value="{{ old('price') }}" placeholder="0.00" required>
                </label>

                <label>Available stock
                    <input type="number" name="stock" min="0" max="1000000" value="{{ old('stock', 0) }}" required>
                </label>

                <label>Product photo <small class="muted">· Optional · JPG, PNG, WebP · max 4 MB</small>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                </label>

                <label class="full">Description
                    <textarea name="description" maxlength="5000" placeholder="Describe key features, materials, and product details...">{{ old('description') }}</textarea>
                </label>

                <div class="full" style="display: flex; gap: 10px; align-items: center;">
                    <button class="button" type="submit">Create product</button>
                    <button type="button" class="button secondary" onclick="document.getElementById('add-product').open = false;">Cancel</button>
                </div>
            </form>
        @endif
    </div>
</details>

<form class="filters" method="GET">
    <label>Search products
        <input name="search" value="{{ request('search') }}" placeholder="Product name or SKU">
    </label>
    <label>Show
        <select name="tab">
            <option value="all" @selected(request('tab', 'all') === 'all')>All products ({{ $stockStats['total'] ?? 0 }})</option>
            <option value="active" @selected(request('tab') === 'active')>Active ({{ $stockStats['active'] ?? 0 }})</option>
            <option value="low_stock" @selected(request('tab') === 'low_stock')>Low stock ({{ $stockStats['low_stock'] ?? 0 }})</option>
            <option value="out_of_stock" @selected(request('tab') === 'out_of_stock')>Out of stock ({{ $stockStats['out_of_stock'] ?? 0 }})</option>
            <option value="archived" @selected(request('tab') === 'archived')>Archived ({{ $stockStats['archived'] ?? 0 }})</option>
        </select>
    </label>
    <button class="button" type="submit">Apply filters</button>
    <a class="button secondary" href="{{ route('seller.inventory') }}">Reset</a>
</form>

<section class="panel">
    @forelse($products as $product)
        @php($totalUnits = $product->variants->sum('stock'))
        @php($isOutOfStock = $totalUnits <= 0)
        @php($isLowStock = !$isOutOfStock && $product->variants->contains(fn($v) => $v->stock <= 10))

        <div class="panel-header">
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
                    <small>
                        {{ $product->category?->name }} · Target: <strong>{{ ucfirst($product->gender ?? 'unisex') }}</strong> · {{ $product->variants->count() }} variant(s)
                    </small>
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

                <span class="badge {{ $product->is_active ? 'active':'archived' }}">
                    {{ $product->is_active ? 'Active':'Archived' }}
                </span>
            </div>
        </div>

        <div class="editor">
            <details @if(old('editing_product') == $product->id) open @endif>
                <summary>Edit details & stock</summary>
                <form action="{{ route('seller.inventory.update', $product) }}" method="POST" class="stack">
                    @csrf
                    <input type="hidden" name="editing_product" value="{{ $product->id }}">
                    <label>Product name
                        <input name="name" value="{{ old('editing_product') == $product->id ? old('name', $product->name) : $product->name }}" required maxlength="255">
                    </label>

                    <div class="form-grid">
                        <label>Gender / Target audience
                            @php($curGender = old('editing_product') == $product->id ? old('gender', $product->gender) : ($product->gender ?? 'unisex'))
                            <select name="gender">
                                <option value="unisex" @selected($curGender === 'unisex')>Unisex (All / Panglahat)</option>
                                <option value="men" @selected($curGender === 'men')>Men (Pang-lalaki)</option>
                                <option value="women" @selected($curGender === 'women')>Women (Pang-babae)</option>
                                <option value="kids" @selected($curGender === 'kids')>Kids (Pang-bata)</option>
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
                                <div>
                                    <strong>{{ $variant->name }}<small class="muted"> · {{ $variant->sku }}</small></strong>
                                    @if($variant->stock <= 0)
                                        <span class="badge" style="background: var(--red-bg); color: var(--red); border-color: var(--red-border); font-size: 10px; margin-left: 6px;">
                                            0 units · Out of stock
                                        </span>
                                    @elseif($variant->stock <= 10)
                                        <span class="badge" style="background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); font-size: 10px; margin-left: 6px;">
                                            {{ $variant->stock }} left · Low stock
                                        </span>
                                    @endif
                                </div>
                                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                <label>Price (PHP)
                                    <input type="number" name="variants[{{ $index }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ old('editing_product') == $product->id ? old('variants.'.$index.'.price', $variant->price) : $variant->price }}" required>
                                </label>
                                <label>Stock
                                    <input type="number" name="variants[{{ $index }}][stock]" min="0" max="1000000" value="{{ old('editing_product') == $product->id ? old('variants.'.$index.'.stock', $variant->stock) : $variant->stock }}" required>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <button type="submit" class="button">Save changes</button>
                    </div>
                </form>
            </details>

            <form action="{{ route('seller.inventory.archive', $product) }}" method="POST">
                @csrf
                <button type="submit" class="button secondary small">
                    {{ $product->is_active ? 'Archive product' : 'Restore product' }}
                </button>
            </form>
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
@endsection
