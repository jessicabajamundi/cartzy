@extends('layouts.seller')
@section('page_title', 'Edit product')
@section('content')
<link rel="stylesheet" href="{{ asset('css/seller-add-product.css') }}?v={{ filemtime(public_path('css/seller-add-product.css')) }}">
@php
    $canEdit = auth()->user()->status === 'active' && $shop?->isApproved();
    $live = $product->variants->where('is_active', true)->values();
    $hidden = $product->variants->where('is_active', false)->values();
    $single = $live->count() === 1 ? $live->first() : null;
    $limit = $product->stock_alert_threshold;
    $units = $live->sum('stock');
    $stockStatus = $units === 0 ? 'out' : ($live->contains(fn ($v) => $v->stock <= $limit) ? 'low' : 'ok');
    $stockLabel = ['ok' => number_format($units).' available', 'low' => 'Low · '.number_format($units).' left', 'out' => 'Out of stock'][$stockStatus];
    $coverPath = $product->coverImage?->path;
    $coverUrl = $coverPath ? (str_starts_with($coverPath, 'http') || str_starts_with($coverPath, '/') ? $coverPath : Storage::disk('public')->url($coverPath)) : null;

    $galleryImages = $product->images->sortBy('position')->values();
    $photoSlots = [];
    for ($i = 0; $i < max(1, $galleryImages->count()); $i++) {
        $img = $galleryImages->get($i);
        $url = null;
        if ($img) {
            $url = str_starts_with($img->path, 'http') || str_starts_with($img->path, '/') ? $img->path : Storage::disk('public')->url($img->path);
        } elseif ($i === 0 && $coverUrl) {
            $url = $coverUrl;
        }
        $photoSlots[$i] = [
            'position' => $img?->position ?? $i,
            'bytes' => $img && \Illuminate\Support\Facades\Storage::disk('public')->exists($img->path) ? \Illuminate\Support\Facades\Storage::disk('public')->size($img->path) : 0,
            'id' => $img?->id,
            'url' => $url,
            'is_cover' => $i === 0,
            'label' => $i === 0 ? 'Cover Photo' : 'Photo '.($i + 1),
        ];
    }

    $peso = fn ($minor) => number_format($minor / 100, 2, '.', '');
    $saleText = match ($product->sale_status) {
        'Sale active' => 'Sale running',
        'Scheduled' => 'Starts '.$product->discount_starts_at?->timezone('Asia/Manila')->format('M j, g:i A'),
        'Sale ended' => 'Sale ended',
        default => 'No scheduled sale',
    };

    // Group active variants by colorway
    $colorGroups = [];
    foreach ($live as $variant) {
        $opts = (array) ($variant->options ?? []);
        $vColor = $opts['color_name'] ?? null;
        $vSize = $opts['size'] ?? null;
        if ($vColor === null && $vSize === null) {
            if (str_contains($variant->name, ' / ')) {
                [$vColor, $vSize] = explode(' / ', $variant->name, 2);
            } elseif (preg_match('/^(US\s*\d+(\.\d+)?\s*[CYK]?|EU\s*\d+|UK\s*\d+|\d+(\.\d+)?|XS|S|M|L|XL|2XL|3XL)$/i', trim($variant->name))) {
                $vSize = $variant->name;
                $vColor = '';
            } else {
                $vColor = $variant->name === 'Standard' ? '' : $variant->name;
                $vSize = '';
            }
        }
        $groupKey = trim((string) $vColor);
        if (!isset($colorGroups[$groupKey])) {
            $colorGroups[$groupKey] = [
                'color' => $groupKey,
                'price' => $peso($variant->price_minor),
                'sizes' => [],
            ];
        }
        $colorGroups[$groupKey]['sizes'][] = [
            'id' => $variant->id,
            'size' => trim((string) $vSize),
            'stock' => $variant->stock,
            'sku' => $variant->sku,
            'is_ordered' => in_array($variant->id, $orderedVariantIds),
            'status' => $variant->stock === 0 ? 'out' : ($variant->stock <= $limit ? 'low' : 'ok'),
            'hex' => $opts['color'] ?? null,
        ];
    }
    $colorGroups = array_values($colorGroups);
    if (empty($colorGroups)) {
        $colorGroups[] = [
            'color' => '',
            'price' => $single ? $peso($single->price_minor) : '0.00',
            'sizes' => [
                [
                    'id' => $single?->id,
                    'size' => '',
                    'stock' => $single?->stock ?? 0,
                    'sku' => $single?->sku ?? '',
                    'is_ordered' => false,
                    'status' => 'ok',
                    'hex' => null,
                ]
            ],
        ];
    }

    $adultSizes = ['US 6', 'US 6.5', 'US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'];
    $kidsSizes = ['US 10C', 'US 10.5C', 'US 11C', 'US 11.5C', 'US 12C', 'US 12.5C', 'US 13C', 'US 1Y', 'US 1.5Y', 'US 2Y', 'US 2.5Y', 'US 3Y', 'US 3.5Y', 'US 4Y', 'US 4.5Y', 'US 5Y', 'US 5.5Y', 'US 6Y', 'US 7Y'];

    $activeCat = $categories->firstWhere('id', (int) old('category_id', $product->category_id));
    $isKidsCat = ($activeCat && (str_contains($activeCat->slug, 'kids') || str_contains(strtolower($activeCat->name), 'kid')));
    $hasKidsSizes = collect($colorGroups)->flatMap(fn($g) => $g['sizes'])->contains(fn($s) => preg_match('/[CYK]/i', $s['size']));
    $initialSizeMode = ($isKidsCat || in_array(old('gender', $product->gender ?? ''), ['kids', 'boys', 'girls', 'all_kids']) || $hasKidsSizes) ? 'kids' : 'adult';
    $currentPresetSizes = $initialSizeMode === 'kids' ? $kidsSizes : $adultSizes;
@endphp
<link rel="stylesheet" href="{{ asset('css/seller-add-product.css') }}?v={{ filemtime(public_path('css/seller-add-product.css')) }}">
<div class="pe pe-edit">
    <header class="pe-header pe-header-row">
        <div>
            <a class="pe-back pe-back-caps" href="{{ route('seller.products') }}">&larr; Products</a>
            <h1>{{ $product->name }}</h1>
            <p>Changes appear in your shop as soon as you save.</p>
        </div>
        @if($product->is_active && $product->category?->slug)
            <a class="button secondary pe-view-shop" href="{{ route('category.show', $product->category->slug) }}" target="_blank" rel="noopener">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 4h6v6 M20 4l-9 9 M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                View in shop
            </a>
        @endif
    </header>

    @if($product->isPending())
        <div style="background: #FFFDF5; border-left: 4px solid #F59E0B; padding: 14px 18px; border-radius: 10px; margin-bottom: 22px; font-size: 13px; color: #92400E; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div>
                <strong>⏳ Pending Administrator Review:</strong>
                <p style="margin: 3px 0 0; font-size: 12px; color: #78350F;">This product is currently under review by our admin team. It will appear live on the storefront once approved.</p>
            </div>
            <span class="badge warning" style="background: #FEF3C7; color: #B45309; font-weight: 700; white-space: nowrap;">Pending Approval</span>
        </div>
    @elseif($product->isRejected())
        <div style="background: #FFF5F5; border-left: 4px solid #E53E3E; padding: 14px 18px; border-radius: 10px; margin-bottom: 22px; font-size: 13px; color: #9B2C2C; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px;">
                <strong>✕ Action Required — Listing Rejected:</strong>
                <span class="badge danger" style="background: #FED7D7; color: #9B2C2C; font-weight: 700;">Rejected</span>
            </div>
            <p style="margin: 0 0 8px; font-size: 13px; line-height: 1.5; color: #742A2A;">
                <strong>Admin Reason:</strong> {{ $product->rejection_reason }}
            </p>
            <p style="margin: 0; font-size: 11px; color: #9B2C2C; background: #FED7D740; padding: 6px 10px; border-radius: 6px;">
                💡 <em>Tip: Correct the issues described above and click <strong>Save product details</strong> below. Your product will automatically be resubmitted for admin approval.</em>
            </p>
        </div>
    @elseif($product->isApproved())
        <div style="background: #F0FDF4; border-left: 4px solid #16A34A; padding: 12px 18px; border-radius: 10px; margin-bottom: 22px; font-size: 13px; color: #166534; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div>
                <strong>✓ Approved & Verified Listing:</strong>
                <span style="display: block; font-size: 12px; color: #15803D; margin-top: 2px;">This product is verified by admin and actively visible to marketplace shoppers.</span>
            </div>
            <span class="badge good" style="background: #DCFCE7; color: #166534; font-weight: 700; white-space: nowrap;">Approved</span>
        </div>
    @endif

    @unless($canEdit)
        <div class="pe-blocked is-pending pe-locked"><strong>Editing is locked</strong><p>Your store needs administrator approval before you can change products.</p></div>
    @endunless

    <div class="pe-layout pe-edit-layout">
        {{-- Left: details, price, stock settings, photo, visibility --}}
        <form id="product-details-form" class="pe-form" action="{{ route('seller.products.details', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <fieldset class="pe-fieldset" @disabled(!$canEdit)>
            <section class="pe-card" aria-labelledby="pe-details-title">
                <header class="pe-card-head">
                    <h2 id="pe-details-title">Details</h2>
                    <p>Name, SKU, category, and the description shown on the product page.</p>
                </header>
                <div class="pe-card-body pe-grid">
                    <label class="pe-field pe-full">Product name
                        <input name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
                    </label>
                    @if($single)
                        <label class="pe-field">SKU
                            <input name="sku" value="{{ old('sku', $single->sku) }}" required maxlength="100">
                            <small class="pe-hint">Your own stock code. Must be unique.</small>
                        </label>
                    @else
                        <div class="pe-field">SKU
                            <p class="pe-static">Each option has its own SKU — see Colors &amp; sizes.</p>
                        </div>
                    @endif
                    @include('seller.partials.category-multi-select', [
                        'categories' => $categories,
                        'selectedCategoryIds' => old('category_ids', $product->categories->isNotEmpty() ? $product->categories->pluck('id')->all() : [$product->category_id]),
                        'defaultCategoryId' => $product->category_id,
                    ])
                    <label class="pe-field">For (optional)
                        @php
                            $curGender = old('gender', $product->gender ?? 'unisex');
                        @endphp
                        <select name="gender" id="pe-edit-gender-select" data-gender-select>
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
                    <label class="pe-field">Badge
                        <select name="badge">
                            <option value="" @selected(old('badge', $product->badge) == '')>No badge</option>
                            @foreach(array_unique(array_filter(['Hot Deal', 'Best Seller', 'New Arrival', 'Trending', 'Authentic 100%', 'Limited Edition', $product->badge])) as $badge)
                                <option value="{{ $badge }}" @selected(old('badge', $product->badge) === $badge)>{{ $badge }}</option>
                            @endforeach
                        </select>
                        <small class="pe-hint">"Sale" is added automatically during a scheduled sale.</small>
                    </label>
                    <label class="pe-field pe-full">Description
                        <textarea rows="5" name="description" maxlength="5000">{{ old('description', $product->description) }}</textarea>
                        <small class="pe-hint">Materials, sizing, care, and what's in the box. Up to 5,000 characters.</small>
                    </label>
                </div>
            </section>

            <section class="pe-card" aria-labelledby="pe-price-title">
                <header class="pe-card-head">
                    <h2 id="pe-price-title">Price</h2>
                    <p>Your everyday price. Discounts are scheduled under Sale.</p>
                </header>
                <div class="pe-card-body">
                    @if($single)
                        <div class="pe-grid">
                            <label class="pe-field">Regular price (₱)
                                <span class="pe-money"><span>₱</span><input type="number" name="price" min="0.01" max="99999999.99" step="0.01" value="{{ old('price', $peso($single->price_minor)) }}" required></span>
                            </label>
                        </div>
                    @else
                        <p class="pe-static pe-static-note">Each option has its own price. Set them under Colors &amp; sizes.</p>
                    @endif
                </div>
            </section>

            <section class="pe-card" aria-labelledby="pe-stock-title">
                <header class="pe-card-head">
                    <h2 id="pe-stock-title">Stock</h2>
                    <p>Restock each option under Colors &amp; sizes.</p>
                </header>
                <div class="pe-card-body pe-grid">
                    <label class="pe-field">Low stock alert
                        <input type="number" name="low_stock_threshold" min="10" max="100000" value="{{ old('low_stock_threshold', $limit) }}" required>
                        <small class="pe-hint">Shown as "Low stock" in Inventory at or below this number.</small>
                    </label>
                    <label class="pe-field">Weight (grams)
                        <input type="number" name="weight_grams" min="0" max="1000000" value="{{ old('weight_grams', $product->variants->first()?->weight_grams ?: '') }}">
                        <small class="pe-hint">Optional. Used for shipping.</small>
                    </label>
                </div>
            </section>

            <section class="pe-card pe-card-photos" aria-labelledby="pe-photo-title">
                <header class="pe-card-head pe-photos-header">
                    <div>
                        <h2 id="pe-photo-title">Product Photos</h2>
                        <p>Add as many photos as you like, up to 10 MB total including existing photos. The first photo is the cover.</p>
                    </div>
                    <div class="pe-photos-header-actions">
                        <label class="button secondary pe-bulk-upload-btn" for="pe-bulk-photos-input" title="Choose multiple photos at once">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Add multiple</span>
                        </label>
                        <input type="file" id="pe-bulk-photos-input" multiple accept="image/jpeg,image/png,image/webp" style="display:none;" data-bulk-photos-input data-max-files="{{ (int) ini_get('max_file_uploads') }}">
                    </div>
                </header>
                <div class="pe-card-body">
                    {{-- Container for hidden inputs of removed images --}}
                    <div id="pe-removed-images-container"></div>

                    <div class="pe-photos-grid">
                        @foreach($photoSlots as $slotIndex => $slot)
                            @php
                                $slotIndex = $slot['position'];
                            @endphp
                            <div class="pe-photo-slot {{ $slot['url'] ? 'has-image' : 'is-empty' }}" data-slot-card="{{ $slotIndex }}" data-image-id="{{ $slot['id'] ?? '' }}" data-image-bytes="{{ $slot['bytes'] }}">
                                <div class="pe-photo-slot-head">
                                    <span class="pe-photo-badge {{ $slot['is_cover'] ? 'is-cover' : '' }}">
                                        @if($slot['is_cover']) ★ Cover @else Photo {{ $slotIndex + 1 }} @endif
                                    </span>
                                    <button type="button" class="pe-photo-slot-remove" data-remove-slot="{{ $slotIndex }}" title="Remove this photo" aria-label="Remove photo {{ $slotIndex + 1 }}" @if(!$slot['url']) style="display:none;" @endif>
                                        &times;
                                    </button>
                                </div>

                                <div class="pe-photo-preview-box" data-preview-box="{{ $slotIndex }}" onclick="document.getElementById('pe_photo_input_{{ $slotIndex }}').click()">
                                    @if($slot['url'])
                                        <img src="{{ $slot['url'] }}" alt="{{ $slot['label'] }}" data-preview-img="{{ $slotIndex }}">
                                    @else
                                        <div class="pe-photo-placeholder" data-placeholder="{{ $slotIndex }}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                            </svg>
                                            <span>+ Add photo</span>
                                        </div>
                                    @endif
                                </div>

                                <label class="pe-photo-slot-action" for="pe_photo_input_{{ $slotIndex }}">
                                    <span data-action-text="{{ $slotIndex }}">{{ $slot['url'] ? 'Change' : 'Choose' }}</span>
                                    <input type="file" 
                                           id="pe_photo_input_{{ $slotIndex }}" 
                                           name="photos[{{ $slotIndex }}]" 
                                           accept="image/jpeg,image/png,image/webp" 
                                           class="pe-slot-file-input" 
                                           data-slot-input="{{ $slotIndex }}"
                                           @if($slot['is_cover']) data-cover-input @endif>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="pe-hint" style="margin-top: 14px; display: block;">JPG, PNG, or WebP. Maximum 10 MB total for all photos. Leave unchanged to keep a current photo.</small>
                    <p class="pe-hint" data-photo-total aria-live="polite"></p>
                </div>
            </section>

            <section class="pe-card" aria-labelledby="pe-visibility-title">
                <header class="pe-card-head"><h2 id="pe-visibility-title">Visibility</h2></header>
                <div class="pe-card-body">
                    <label class="pe-field">Shown in your shop
                        <select name="is_active">
                            <option value="1" @selected((string) old('is_active', (int) $product->is_active) === '1')>Active — visible to buyers</option>
                            <option value="0" @selected((string) old('is_active', (int) $product->is_active) === '0')>Hidden — only you can see it</option>
                        </select>
                    </label>
                </div>
            </section>

            <footer class="pe-actions pe-actions-sticky">
                <a class="button secondary" href="{{ route('seller.products') }}">Cancel</a>
                <button type="submit" class="button" data-save-details>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                    Save changes
                </button>
            </footer>
            </fieldset>
        </form>

        {{-- Right: at a glance, options, sale, delete --}}
        <aside class="pe-edit-aside">
            <section class="pe-card" aria-labelledby="pe-glance-title">
                <header class="pe-card-head"><h2 id="pe-glance-title">At a glance</h2></header>
                <dl class="pe-glance">
                    <div><dt>Stock</dt><dd><span class="pe-pill is-{{ $stockStatus }}">{{ $stockLabel }}</span></dd></div>
                    <div><dt>Visibility</dt><dd>{{ $product->is_active ? 'Visible to buyers' : 'Hidden' }}</dd></div>
                    <div><dt>Sale</dt><dd>{{ $saleText }}</dd></div>
                    <div><dt>Last updated</dt><dd>{{ $product->updated_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}</dd></div>
                </dl>
            </section>

            <section class="pe-card" aria-labelledby="pe-options-title">
                <header class="pe-card-head">
                    <h2 id="pe-options-title">Colors &amp; sizes</h2>
                    <p>{{ number_format($units) }} in stock across {{ $live->count() }} {{ $live->count() === 1 ? 'option' : 'options' }}. Select multiple sizes per colorway below.</p>
                </header>
                <form class="pe-card-body pe-options" action="{{ route('seller.products.options', $product->id) }}" method="POST" data-options-form>
                    @csrf
                    <fieldset class="pe-fieldset" @disabled(!$canEdit)>
                    <div class="pe-quick-size-bar" data-quick-size-bar data-active-mode="{{ $initialSizeMode }}">
                        <div class="pe-quick-size-head">
                            <span class="pe-quick-badge">⚡ Quick US Sizes</span>
                            <div class="pe-size-type-toggle">
                                <button type="button" class="pe-size-type-btn {{ $initialSizeMode === 'adult' ? 'is-active' : '' }}" data-set-size-mode="adult">👟 Adult US</button>
                                <button type="button" class="pe-size-type-btn {{ $initialSizeMode === 'kids' ? 'is-active' : '' }}" data-set-size-mode="kids">🧒 Kids US</button>
                            </div>
                            <span class="pe-quick-hint">Click a size to toggle it in your selected color</span>
                        </div>
                        <div class="pe-quick-size-chips" data-quick-global-chips>
                            @foreach($currentPresetSizes as $s)
                                <button type="button" class="pe-quick-size-btn" data-quick-global-size="{{ $s }}">+ {{ $s }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="pe-option-list" data-color-group-list>
                        @foreach($colorGroups as $g => $group)
                            @php
                                $groupColor = $group['color'];
                                $groupPrice = $group['price'];
                                $groupSizes = $group['sizes'];
                                $sizesList = array_values(array_filter(array_column($groupSizes, 'size'), fn($s) => $s !== ''));
                                $sizesText = implode(', ', $sizesList);
                                $groupTotalStock = array_sum(array_column($groupSizes, 'stock'));
                                $groupStockStatus = $groupTotalStock === 0 ? 'out' : ($groupTotalStock <= $limit ? 'low' : 'ok');
                                $displayInitial = mb_strtoupper(mb_substr($groupColor ?: 'O', 0, 1));
                                $hex = $groupSizes[0]['hex'] ?? null;
                            @endphp
                            <div class="pe-option pe-color-group" data-color-group data-group-index="{{ $g }}">
                                <div class="pe-option-head">
                                    <span class="pe-swatch-dot" data-swatch style="{{ $hex ? 'background: '.$hex : '' }}">{{ $hex ? '' : $displayInitial }}</span>
                                    <div class="pe-option-title-wrap">
                                        <span class="pe-option-variant-label" data-preview-color-label>{{ $groupColor ?: 'Color Option' }}</span>
                                        <span class="pe-pill is-{{ $groupStockStatus }}" data-stock-pill>{{ ['ok' => 'In stock', 'low' => 'Low', 'out' => 'Sold out'][$groupStockStatus] }}</span>
                                    </div>
                                    <label class="pe-option-remove" title="Remove {{ $groupColor ?: 'this color' }}">
                                        <input type="checkbox" name="color_groups[{{ $g }}][remove]" value="1" data-group-remove @checked(old("color_groups.$g.remove"))>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18 M8 6V4h8v2 M19 6l-1 14H6L5 6"/></svg>
                                        <span class="inv-sr">Remove</span>
                                    </label>
                                </div>

                                <div class="pe-option-body">
                                    <div class="pe-option-dual-inputs">
                                        <div class="pe-field-col">
                                            <label class="pe-input-label">Color / Shade</label>
                                            <input class="pe-option-input pe-color-input" name="color_groups[{{ $g }}][color]" value="{{ old("color_groups.$g.color", $groupColor) }}" placeholder="e.g. Black" maxlength="100" data-sync-color>
                                        </div>
                                        <div class="pe-field-col">
                                            <label class="pe-input-label">Price (₱)</label>
                                            <input type="number" class="pe-option-input" name="color_groups[{{ $g }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ old("color_groups.$g.price", $groupPrice) }}" required data-sync-price>
                                        </div>
                                    </div>

                                    <div class="pe-field-col">
                                        <label class="pe-input-label">Available sizes (US / Clothing)</label>
                                        <input class="pe-option-input pe-sizes-input" name="color_groups[{{ $g }}][sizes_text]" value="{{ old("color_groups.$g.sizes_text", $sizesText) }}" placeholder="e.g. US 7, US 7.5, US 8, US 8.5, US 9" data-sizes-input>
                                    </div>

                                    <div class="pe-size-chips-mini" data-group-chips-wrap>
                                        <div class="pe-mini-chips-head">
                                            <span class="pe-chips-mini-label">Quick sizes:</span>
                                            <div class="pe-mini-type-toggle">
                                                <button type="button" class="pe-mini-type-btn {{ $initialSizeMode === 'adult' ? 'is-active' : '' }}" data-group-size-toggle="adult">Adult</button>
                                                <button type="button" class="pe-mini-type-btn {{ $initialSizeMode === 'kids' ? 'is-active' : '' }}" data-group-size-toggle="kids">Kids</button>
                                            </div>
                                        </div>
                                        <div class="pe-mini-chips-list" data-mini-chips-list>
                                            @foreach($currentPresetSizes as $s)
                                                <button type="button" class="pe-size-chip-btn {{ in_array($s, $sizesList) ? 'is-selected' : '' }}" data-toggle-size="{{ $s }}">{{ $s }}</button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="pe-size-stocks-wrap" data-size-stocks-wrap>
                                        <div class="pe-size-stocks-head">
                                            <span class="pe-size-stocks-title">Stock per size</span>
                                            <div class="pe-batch-stock-tool">
                                                <span class="pe-batch-label">Set all:</span>
                                                <input type="number" class="pe-batch-input" min="0" max="1000000" placeholder="Qty" data-batch-stock-input>
                                                <button type="button" class="pe-batch-apply-btn" data-batch-apply-stock>Apply</button>
                                            </div>
                                        </div>

                                        <div class="pe-size-stocks-grid" data-size-stocks-grid>
                                            @if(count($sizesList) > 0)
                                                @foreach($groupSizes as $si => $s)
                                                    @if($s['size'] !== '')
                                                        <div class="pe-size-stock-row" data-size-row="{{ $s['size'] }}">
                                                            <span class="pe-size-stock-badge">{{ $s['size'] }}</span>
                                                            <input type="hidden" name="color_groups[{{ $g }}][sizes][{{ $si }}][size]" value="{{ $s['size'] }}" data-field="size">
                                                            <input type="hidden" name="color_groups[{{ $g }}][sizes][{{ $si }}][id]" value="{{ $s['id'] }}" data-field="id">
                                                            <input type="hidden" name="color_groups[{{ $g }}][sizes][{{ $si }}][previous_stock]" value="{{ $s['stock'] }}" data-field="prev">
                                                            <div class="pe-size-stock-input-wrap">
                                                                <input type="number" class="pe-size-stock-input" name="color_groups[{{ $g }}][sizes][{{ $si }}][stock]" value="{{ old("color_groups.$g.sizes.$si.stock", $s['stock']) }}" min="0" max="1000000" required data-field="stock">
                                                                <span class="pe-size-stock-sublabel">stock</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class="pe-single-stock-row" data-single-stock-row>
                                                    <input type="hidden" name="color_groups[{{ $g }}][sizes][0][size]" value="" data-field="size">
                                                    <input type="hidden" name="color_groups[{{ $g }}][sizes][0][id]" value="{{ $groupSizes[0]['id'] ?? '' }}" data-field="id">
                                                    <input type="hidden" name="color_groups[{{ $g }}][sizes][0][previous_stock]" value="{{ $groupSizes[0]['stock'] ?? 0 }}" data-field="prev">
                                                    <div class="pe-size-stock-input-wrap is-full">
                                                        <label class="pe-input-label">Stock quantity</label>
                                                        <input type="number" class="pe-option-input" name="color_groups[{{ $g }}][sizes][0][stock]" value="{{ old("color_groups.$g.sizes.0.stock", $groupSizes[0]['stock'] ?? 0) }}" min="0" max="1000000" required data-field="stock">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @php
                                        $skus = array_filter(array_column($groupSizes, 'sku'));
                                        $hasOrders = collect($groupSizes)->contains(fn($s) => !empty($s['is_ordered']));
                                    @endphp
                                    @if(!empty($skus))
                                        <small class="pe-option-sku">{{ implode(' · ', array_slice($skus, 0, 3)) }}{{ count($skus) > 3 ? ' + '.(count($skus)-3).' more' : '' }} @if($hasOrders) · has orders, will be hidden if removed @endif</small>
                                    @else
                                        <small class="pe-option-sku">SKUs will be generated automatically upon save.</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template data-color-group-template>
                        <div class="pe-option pe-color-group is-new" data-color-group data-group-index="__g__">
                            <div class="pe-option-head">
                                <span class="pe-swatch-dot" data-swatch>+</span>
                                <div class="pe-option-title-wrap">
                                    <span class="pe-option-variant-label" data-preview-color-label>New Color Option</span>
                                    <span class="pe-pill is-new-pill">NEW</span>
                                </div>
                                <button type="button" class="pe-option-cancel" data-group-cancel aria-label="Remove new color" title="Remove new color">&times;</button>
                            </div>
                            <div class="pe-option-body">
                                <div class="pe-option-dual-inputs">
                                    <div class="pe-field-col">
                                        <label class="pe-input-label">Color / Shade</label>
                                        <input class="pe-option-input pe-color-input" name="color_groups[__g__][color]" value="" placeholder="e.g. White / Metallic Gold" maxlength="100" data-sync-color>
                                    </div>
                                    <div class="pe-field-col">
                                        <label class="pe-input-label">Price (₱)</label>
                                        <input type="number" class="pe-option-input" name="color_groups[__g__][price]" min="0.01" max="99999999.99" step="0.01" value="__price__" required data-sync-price>
                                    </div>
                                </div>
                                <div class="pe-field-col">
                                    <label class="pe-input-label">Available sizes (US / Clothing)</label>
                                    <input class="pe-option-input pe-sizes-input" name="color_groups[__g__][sizes_text]" value="" placeholder="e.g. US 7, US 7.5, US 8, US 8.5, US 9" data-sizes-input>
                                </div>
                                <div class="pe-size-chips-mini" data-group-chips-wrap>
                                    <div class="pe-mini-chips-head">
                                        <span class="pe-chips-mini-label">Quick sizes:</span>
                                        <div class="pe-mini-type-toggle">
                                            <button type="button" class="pe-mini-type-btn is-active" data-group-size-toggle="adult">Adult</button>
                                            <button type="button" class="pe-mini-type-btn" data-group-size-toggle="kids">Kids</button>
                                        </div>
                                    </div>
                                    <div class="pe-mini-chips-list" data-mini-chips-list>
                                        @foreach($adultSizes as $s)
                                            <button type="button" class="pe-size-chip-btn" data-toggle-size="{{ $s }}">{{ $s }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="pe-size-stocks-wrap" data-size-stocks-wrap>
                                    <div class="pe-size-stocks-head">
                                        <span class="pe-size-stocks-title">Stock per size</span>
                                        <div class="pe-batch-stock-tool">
                                            <span class="pe-batch-label">Set all:</span>
                                            <input type="number" class="pe-batch-input" min="0" max="1000000" placeholder="Qty" data-batch-stock-input>
                                            <button type="button" class="pe-batch-apply-btn" data-batch-apply-stock>Apply</button>
                                        </div>
                                    </div>
                                    <div class="pe-size-stocks-grid" data-size-stocks-grid>
                                        <div class="pe-single-stock-row" data-single-stock-row>
                                            <input type="hidden" name="color_groups[__g__][sizes][0][size]" value="" data-field="size">
                                            <input type="hidden" name="color_groups[__g__][sizes][0][id]" value="" data-field="id">
                                            <div class="pe-size-stock-input-wrap is-full">
                                                <label class="pe-input-label">Stock quantity</label>
                                                <input type="number" class="pe-option-input" name="color_groups[__g__][sizes][0][stock]" value="0" min="0" max="1000000" required data-field="stock">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="pe-option-sku">SKUs will be generated automatically upon save.</small>
                            </div>
                        </div>
                    </template>

                    <button type="button" class="pe-add-color" data-color-group-add>
                        <span class="pe-add-color-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
                        <span class="pe-add-color-text"><strong>Add another color or style</strong><small>Add another colorway with its own sizes, price, and stock</small></span>
                        <svg class="pe-add-color-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                    </button>
                    <p class="pe-hint">Tick 🗑 to remove a colorway. Options with past orders are hidden instead of deleted.@if($hidden->isNotEmpty()) {{ $hidden->count() }} hidden {{ $hidden->count() === 1 ? 'option is' : 'options are' }} not shown here.@endif</p>
                    <div class="pe-options-save"><button type="submit" class="button">Save colors &amp; sizes</button></div>
                    </fieldset>
                </form>
            </section>

            <section class="pe-card" aria-labelledby="pe-sale-title">
                <header class="pe-card-head">
                    <h2 id="pe-sale-title">Sale</h2>
                    <p>Schedule a discount that starts and ends automatically.</p>
                </header>
                <div class="pe-card-body pe-sale-row">
                    <a href="{{ route('seller.products.sale', $product->id) }}">&#9656; {{ $product->discount_type ? 'Manage sale' : 'Set up sale' }}</a>
                    <span>{{ $saleText }}</span>
                </div>
            </section>

            <section class="pe-card" aria-labelledby="pe-delete-title">
                <header class="pe-card-head">
                    <h2 id="pe-delete-title">Delete product</h2>
                    <p>Removes it from your shop. Past orders keep their records.</p>
                </header>
                <form class="pe-card-body" action="{{ route('seller.products.delete', $product->id) }}" method="POST" data-confirm="Delete &quot;{{ $product->name }}&quot;? It will be removed from your shop.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pe-delete" @disabled(!$canEdit)>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18 M8 6V4h8v2 M19 6l-1 14H6L5 6"/></svg>
                        Delete product
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
<script src="{{ asset('js/seller-product-edit.js') }}?v={{ filemtime(public_path('js/seller-product-edit.js')) }}" defer></script>
@endsection
