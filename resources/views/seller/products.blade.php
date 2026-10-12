@extends('layouts.seller')
@section('page_title', 'Products')
@section('content')
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
            <button type="button" class="button" id="add-product-trigger" aria-haspopup="dialog" aria-controls="add-product">
                + Add product
            </button>
        @endif
    </div>
</div>

<dialog id="add-product" class="product-modal" aria-labelledby="add-product-title" aria-describedby="add-product-description" data-auto-open="{{ !session('success') && (request('create') || ($errors->any() && !old('editing_product'))) ? 'true' : 'false' }}">
    <header class="product-modal-header">
        <div>
            <h2 id="add-product-title">Add product</h2>
            <p id="add-product-description" class="muted">Add product details, pricing, and stock to your catalog.</p>
        </div>
        <button type="button" class="icon-button" data-close-product aria-label="Close add product">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </header>
    <div class="product-modal-body">
        @if($errors->any() && !old('editing_product'))
            <div class="notice error" role="alert">
                <strong>Please check your entries.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if(!$shop)
            <p>Save your <a href="{{ route('seller.account') }}">store profile</a> first.</p>
        @elseif(auth()->user()->status !== 'active' || !$shop->isApproved())
            <div class="notice" style="background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; padding: 18px; border-radius: 10px;">
                <h3 style="color: #92400E; margin-bottom: 6px; font-size: 1rem;">Account Pending Administrator Approval</h3>
                <p style="margin: 0; line-height: 1.5; font-size: 0.88rem;">
                    Your seller account is currently under review by our administration team. You will be able to add and sell products once your account has been approved by the administrator.
                </p>
                <div style="margin-top: 14px;">
                    <a href="{{ route('seller.account') }}" class="button small">Check verification status &rarr;</a>
                </div>
            </div>
        @elseif($categories->isEmpty())
            <p class="muted">An administrator needs to add an active product category before you can create products.</p>
        @else
            <form id="add-product-form" action="{{ route('seller.products.add') }}" method="POST" enctype="multipart/form-data" class="form-grid">
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

                <label>Gender / Target audience <small class="muted">· Suitable for</small>
                    <select name="gender">
                        <option value="unisex" @selected(old('gender', 'unisex') === 'unisex')>Unisex (All)</option>
                        <option value="men" @selected(old('gender') === 'men')>Men</option>
                        <option value="women" @selected(old('gender') === 'women')>Women</option>
                        <option value="kids" @selected(old('gender') === 'kids')>Kids</option>
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

                <!-- Variations / Options Optional Toggle Card -->
                <div class="variants-toggle-card">
                    <div class="toggle-card-left">
                        <div class="toggle-badge-row">
                            <span class="badge-accent">Optional</span>
                            <strong>Product variations</strong>
                        </div>
                        <p class="muted" id="variants-help">Selling different sizes, colors, or shades? Set a price and stock for each option.</p>
                    </div>
                    <label class="switch-toggle" for="enable-variants-toggle">
                        <input type="checkbox" id="enable-variants-toggle" name="has_variants" value="1" aria-label="Enable product variations" aria-describedby="variants-help" aria-controls="multiple-variants-fields" @checked(old('has_variants'))>
                        <span class="switch-slider"></span>
                    </label>
                </div>

                <!-- Single Variant Fields (Default Mode) -->
                <div id="single-variant-fields" class="single-variant-group full" style="{{ old('has_variants') ? 'display:none;' : '' }}">
                    <div class="form-grid">
                        <label>SKU
                            <input name="sku" id="single-sku-input" value="{{ old('sku') }}" maxlength="100" placeholder="e.g. PROD-001" {{ old('has_variants') ? '' : 'required' }}>
                        </label>

                        <label>Price (PHP)
                            <input type="number" name="price" id="single-price-input" min="0.01" max="99999999.99" step="0.01" value="{{ old('price') }}" placeholder="0.00" {{ old('has_variants') ? '' : 'required' }}>
                        </label>

                        <label>Available stock
                            <input type="number" name="stock" id="single-stock-input" min="0" max="1000000" value="{{ old('stock', 0) }}" {{ old('has_variants') ? '' : 'required' }}>
                        </label>
                    </div>
                </div>

                <!-- Multiple Variations & Options Builder (Optional Mode) -->
                <div id="multiple-variants-fields" class="multiple-variants-group" style="{{ old('has_variants') ? '' : 'display:none;' }}">
                    <div class="variants-builder-header">
                        <div>
                            <h4>Your variations <span id="variant-count" class="variant-count"></span></h4>
                            <p class="muted">Start with a preset or add your own. You can edit every option below.</p>
                        </div>
                        <button type="button" class="button small" id="add-shade-row-btn">
                            + Add option
                        </button>
                    </div>

                    <!-- Quick Preset Helpers -->
                    <div class="preset-chips">
                        <span class="preset-label">Quick add</span>
                        <button type="button" class="preset-btn" data-preset="shoes">Shoe sizes <small>38–42</small></button>
                        <button type="button" class="preset-btn" data-preset="sizes">Clothing <small>S–2XL</small></button>
                        <button type="button" class="preset-btn" data-preset="colors">Colors <small>4 options</small></button>
                        <button type="button" class="preset-btn" data-preset="lipstick">Shades <small>4 options</small></button>
                    </div>

                    <div class="variant-bulk-bar">
                        <div><strong>Save time with bulk edits</strong><small>Filled values replace every option's value.</small></div>
                        <label>Price (PHP)<input id="bulk-variant-price" type="number" min="0.01" max="99999999.99" step="0.01" placeholder="Leave unchanged"></label>
                        <label>Stock per option<input id="bulk-variant-stock" type="number" min="0" max="1000000" step="1" placeholder="Leave unchanged"></label>
                        <button type="button" class="button secondary small" id="apply-variant-values">Apply to all</button>
                    </div>
                    <p id="variant-feedback" class="variant-feedback" role="status" aria-live="polite">Photos and color swatches are optional.</p>

                    <!-- List of Variations -->
                    <div id="shades-container" class="shades-list">
                        @php($oldVariants = old('variants', []))
                        @if(empty($oldVariants))
                            @php($oldVariants = [
                                ['name' => '', 'sku' => '', 'price' => '', 'stock' => '0', 'color' => '#1E293B'],
                            ])
                        @endif

                        @foreach($oldVariants as $idx => $v)
                            <div class="shade-card" data-shade-index="{{ $idx }}">
                                <div class="shade-card-header">
                                    <div class="shade-title">
                                        <span class="shade-num-badge">Option #{{ $idx + 1 }}</span>
                                        <input type="text" name="variants[{{ $idx }}][name]" value="{{ $v['name'] ?? '' }}" aria-label="Option name" placeholder="Option name, e.g. Medium / Black" class="shade-name-input" data-required-variant="true" {{ old('has_variants') ? 'required' : '' }} maxlength="255">
                                    </div>
                                    <button type="button" class="icon-button remove-shade-btn" title="Remove this option" aria-label="Remove option">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="shade-card-body">
                                    <div class="shade-fields-grid">
                                        <label>SKU
                                            <input type="text" name="variants[{{ $idx }}][sku]" value="{{ $v['sku'] ?? '' }}" placeholder="e.g. VAR-0{{ $idx + 1 }}" data-required-variant="true" {{ old('has_variants') ? 'required' : '' }} maxlength="100">
                                        </label>
                                        <label>Price (PHP)
                                            <input type="number" name="variants[{{ $idx }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ $v['price'] ?? '' }}" placeholder="0.00" data-required-variant="true" {{ old('has_variants') ? 'required' : '' }}>
                                        </label>
                                        <label>Stock
                                            <input type="number" name="variants[{{ $idx }}][stock]" min="0" max="1000000" value="{{ $v['stock'] ?? 0 }}" data-required-variant="true" {{ old('has_variants') ? 'required' : '' }}>
                                        </label>
                                    </div>
                                    <details class="variant-extras"><summary>Photo &amp; color <span>Optional</span></summary><div class="shade-visual-row">
                                        <label class="shade-photo-label">
                                            <span class="label-text">Variation photo thumbnail <small class="muted">· Optional</small></span>
                                            <div class="shade-photo-uploader">
                                                <input type="file" name="variants[{{ $idx }}][image]" accept="image/jpeg,image/png,image/webp" class="shade-file-input">
                                                <div class="shade-photo-preview empty">
                                                    <span class="preview-placeholder">No photo</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="shade-color-label">
                                            <span class="label-text">Color swatch <small class="muted">· Optional</small></span>
                                            <div class="swatch-picker-row">
                                                <input type="color" name="variants[{{ $idx }}][color]" value="{{ $v['color'] ?? '#1E293B' }}" class="color-swatch-input">
                                                <span class="swatch-hex-text">{{ strtoupper($v['color'] ?? '#1E293B') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Product Media & Gallery (Up to 10 photos) -->
                <div class="full product-media-card">
                    <div class="media-card-header">
                        <div class="media-header-info">
                            <span class="media-section-title">Product media &amp; gallery</span>
                            <p class="media-section-subtitle">Upload up to 10 high-resolution photos. The first image will be featured as your primary storefront cover photo.</p>
                        </div>
                        <div class="media-count-badge" id="photos-counter-badge" aria-live="polite">
                            <span class="media-count-dot"></span>
                            <span id="photos-counter-text">0 / 10 photos</span>
                        </div>
                    </div>

                    <div class="media-dropzone" id="photos-dropzone" role="button" tabindex="0" aria-label="Upload product gallery photos">
                        <input type="file" name="images[]" id="product-images-input" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
                        <input type="file" id="product-images-picker" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
                        <div class="dropzone-content">
                            <div class="dropzone-icon-circle">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                    <path d="M12 12v9"/>
                                    <path d="m16 16-4-4-4 4"/>
                                </svg>
                            </div>
                            <div class="dropzone-text-group">
                                <span class="dropzone-primary-action">
                                    <strong class="dropzone-link">Click to browse</strong> or drag and drop photos here
                                </span>
                                <span class="dropzone-hint-text">Supported formats: JPG, PNG, WebP · Max 10 MB each (up to 10 images)</span>
                                <span class="dropzone-subhint">Hold <strong>Ctrl</strong> (or <strong>Cmd</strong>) to select multiple, or add photos one by one</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview Grid of Selected Photos -->
                    <div id="product-photos-preview-grid" class="media-preview-grid" style="display: none;"></div>
                </div>

                <label class="full">Description
                    <textarea name="description" maxlength="5000" placeholder="Describe key features, materials, and product details...">{{ old('description') }}</textarea>
                </label>

            </form>
        @endif
    </div>
    <footer class="product-modal-footer">
        <button type="button" class="button secondary" data-close-product>Cancel</button>
        @if($shop && auth()->user()->status === 'active' && $shop->isApproved() && $categories->isNotEmpty())
            <button class="button" type="submit" form="add-product-form">Create product</button>
        @endif
    </footer>
</dialog>

<div class="notice">Manage product content and prices here. For restocking and quantity adjustments, open <a href="{{ route('seller.inventory') }}">Inventory &rarr;</a></div>
<form class="filters" method="GET">
    <label>Search products
        <input name="search" value="{{ request('search') }}" placeholder="Product name or SKU">
    </label>
    <label>Show
        <select name="tab">
            <option value="all" @selected(request('tab', 'all') === 'all')>All products ({{ $stockStats['total'] ?? 0 }})</option>
            <option value="active" @selected(request('tab') === 'active')>Active ({{ $stockStats['active'] ?? 0 }})</option>
            <option value="archived" @selected(request('tab') === 'archived')>Archived ({{ $stockStats['archived'] ?? 0 }})</option>
        </select>
    </label>
    <button class="button" type="submit">Apply filters</button>
    <a class="button secondary" href="{{ route('seller.products') }}">Reset</a>
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

                    @if($product->variants->count() > 1)
                        <!-- Lipstick Shades / Variations Gallery Track (Image 2 style) -->
                        <div class="product-shades-gallery">
                            <span class="gallery-icon" title="Multiple shades/variants available">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                            </span>
                            <div class="shades-thumbnails-track">
                                @foreach($product->variants as $vItem)
                                    <div class="shade-pill" title="{{ $vItem->name }} · ₱{{ number_format($vItem->price, 2) }} · {{ $vItem->stock }} available">
                                        @if($vItem->image_url)
                                            <img src="{{ $vItem->image_url }}" alt="{{ $vItem->name }}" class="shade-mini-thumb">
                                        @elseif(!empty($vItem->options['color']))
                                            <span class="shade-color-circle" style="background-color: {{ $vItem->options['color'] }};"></span>
                                        @else
                                            <span class="shade-dot"></span>
                                        @endif
                                        <span class="shade-pill-name">{{ $vItem->name }}</span>
                                        <span class="shade-pill-stock">({{ $vItem->stock }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
                <summary>Edit product details</summary>
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
                                <option value="unisex" @selected($curGender === 'unisex')>Unisex (All)</option>
                                <option value="men" @selected($curGender === 'men')>Men</option>
                                <option value="women" @selected($curGender === 'women')>Women</option>
                                <option value="kids" @selected($curGender === 'kids')>Kids</option>
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
                                        @elseif($variant->stock <= 10)
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
                                <label>Price (PHP)
                                    <input type="number" name="variants[{{ $index }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ old('editing_product') == $product->id ? old('variants.'.$index.'.price', $variant->price) : $variant->price }}" required>
                                </label>
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
            </details>

            <form action="{{ route('seller.products.archive', $product) }}" method="POST">
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

@push('scripts')
<script src="{{ asset('js/seller-inventory.js') }}?v={{ filemtime(public_path('js/seller-inventory.js')) }}"></script>
@endpush
