@extends('layouts.app')

@section('title', 'cartzy')

@section('content')
    <!-- Full-width Hero Collection Carousel (edge-to-edge, no gaps) -->
    @include('partials.hero-carousel')

    <div class="storefront-cards w-full px-3 sm:px-6 lg:px-8 py-6 space-y-8">

    @include('partials.shop-categories')

    @include('partials.picks-carousel')

    <!-- Search Results Announcement Bar (Dynamically shown when searching) -->
    <div id="search-results-banner" class="hidden bg-gray-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-800 flex flex-wrap items-center justify-between gap-3 w-full">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <div>
                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Marketplace Search</p>
                <h3 class="text-sm sm:text-base font-bold text-white">
                    Found <span id="search-count-display" class="text-white font-extrabold underline">0</span> matching items for "<span id="search-query-display" class="font-bold text-gray-100"></span>"
                </h3>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="clearSearch()" class="bg-white hover:bg-gray-100 text-black text-xs font-bold px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                <span>✕</span>
                <span>Clear Filter</span>
            </button>
        </div>
    </div>

    <!-- No Search Results Fallback Card -->
    <div id="no-search-results" class="hidden bg-white rounded-2xl p-8 sm:p-12 text-center border border-gray-200 shadow-2xs w-full flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-gray-900">No matching products found</h3>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md">Try another search or category, or clear all filters to browse every product.</p>
        <a href="{{ route('home') }}#daily-discover-section" class="mt-4 inline-flex bg-black hover:bg-gray-800 text-white text-xs font-bold px-6 py-2.5 rounded-xl transition shadow-xs">
            View All Available Products
        </a>
    </div>


    <!-- Daily discovery catalog -->
    <section id="daily-discover-section" aria-labelledby="daily-discover-title" class="daily-discover"
             data-wishlist-url="{{ route('wishlist.toggle', [], false) }}" data-login-url="{{ route('login', [], false) }}"
             data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
        <p id="wishlist-status" class="{{ session('wishlist_status') ? 'mb-3 text-sm' : 'sr-only' }}" role="status" aria-live="polite">{{ session('wishlist_status') }}</p>
        <header class="discovery-heading">
            <div>
                <p class="discovery-eyebrow">Picked for you</p>
                <h2 id="daily-discover-title">Daily Discover</h2>
                <p class="discovery-intro">Everyday favorites, all in one place.</p>
            </div>
            <a class="discovery-browse" href="{{ route('home') }}#browse-categories">Browse categories <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg></a>
        </header>
        <!-- Multi-Column Responsive Product Cards Grid (Fills full width) -->
        <div id="daily-discover-grid" class="daily-discover-grid">
            @forelse($discoveryProducts ?? [] as $product)
                @php
                    $minVariant = $product->variants->first();
                    $price = $minVariant ? $minVariant->price : 0;
                    $origPrice = $minVariant && $product->sale_is_active && $minVariant->original_price > $price ? $minVariant->original_price : null;
                    $discountPct = $origPrice ? round((($origPrice - $price) / $origPrice) * 100) : null;
                    $coverImg = $product->coverImage ? (str_starts_with($product->coverImage->path, 'http') || str_starts_with($product->coverImage->path, '/') ? $product->coverImage->path : asset('storage/' . $product->coverImage->path)) : ($product->variants->first()?->image_path ? (str_starts_with($product->variants->first()->image_path, 'http') ? $product->variants->first()->image_path : asset('storage/' . $product->variants->first()->image_path)) : asset('images/logo-transparent.png'));
                    $rating = $product->ratingLabel();
                    $hasFreeShipping = \App\Models\Product::qualifiesForFreeShipping($minVariant?->price_minor, $product->seller_id);
                    $sold = $product->sold_quantity ? ($product->sold_quantity >= 1000 ? round($product->sold_quantity / 1000, 1) . 'k sold' : $product->sold_quantity . ' sold') : '0 sold';
                    $sellerName = $product->seller?->name ?? 'Official';
                    $badge = $product->badge ?: ($product->seller?->isApproved() ? 'Official' : null);
                    $categorySlug = $product->category?->slug ?? '';
                    $totalStock = (int) $product->variants->sum('stock');
                    $isOutOfStock = $totalStock <= 0;
                    $isLowStock = !$isOutOfStock && $totalStock <= $product->stock_alert_threshold;
                @endphp
                <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 cursor-pointer"
                     data-href="{{ route('products.show', $product->id) }}"
                     data-category="{{ $categorySlug }}"
                     data-name="{{ strtolower($product->name) }}"
                     data-brand="{{ strtolower($sellerName) }}">
                    <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                        <a href="{{ route('products.show', $product->id) }}" class="block w-full h-full">
                            <img src="{{ $coverImg }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $isOutOfStock ? 'opacity-75' : '' }}">
                        </a>
                        @if($badge)
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                <span class="discovery-seller">@if($badge === 'Official')<svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="currentColor"/><path d="M4.8 8.2l2.1 2.1 4.3-4.5" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif{{ $badge }}</span>
                            </div>
                        @endif
                        @if($isOutOfStock)
                            <div class="absolute bottom-2 left-2 flex items-center gap-1">
                                <span class="bg-[#282133]/90 text-white font-bold text-[9px] sm:text-[10px] px-2 py-0.5 rounded shadow-2xs">Out of Stock</span>
                            </div>
                        @endif
                        @if($discountPct && !$isOutOfStock)
                            <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-{{ $discountPct }}%</span>
                        @endif
                    </div>
                    <div class="discovery-body flex-1 flex flex-col">
                        <div class="discovery-info">
                            @if($product->seller)<a href="{{ route('store.show', $product->seller->slug) }}" class="discovery-store-link">{{ $product->seller->name }}</a>@endif
                            <a href="{{ route('products.show', $product->id) }}" class="block">
                                <h3 class="line-clamp-2">{{ $product->name }}</h3>
                            </a>
                            @if($hasFreeShipping)<span class="discovery-tag">Free Shipping</span>@endif
                        </div>
                        <div class="discovery-pricing">
                            <div class="discovery-price">
                                <span class="discovery-price-now"><span class="discovery-currency">₱</span>{{ number_format($price, 2) }}</span>
                                @if($origPrice)<del class="discovery-price-was">₱{{ number_format($origPrice, 2) }}</del>@endif
                            </div>
                            <div class="discovery-rating">
                                <span class="discovery-score"><span aria-hidden="true">★</span>{{ $rating }}</span>
                                <span class="discovery-dot" aria-hidden="true"></span>
                                <span>{{ $sold }}</span>
                            </div>
                            <p class="discovery-stock {{ $isOutOfStock ? 'is-out' : ($isLowStock ? 'is-low' : '') }}">
                                <span class="discovery-stock-dot" aria-hidden="true"></span>
                                @if($isOutOfStock) Out of stock @elseif($isLowStock) Only {{ number_format($totalStock) }} left @else {{ number_format($totalStock) }} in stock @endif
                            </p>
                            <div class="discovery-actions">
                                @include('partials.discovery-wishlist', ['product' => ['ref' => (string)$product->id, 'name' => $product->name, 'price' => (string)$price, 'image' => $coverImg, 'variation' => $minVariant?->name ?? 'Standard']])
                            @if($isOutOfStock)
                                <button type="button" disabled class="mt-2 w-full bg-gray-100 text-gray-400 border border-gray-200 text-[11px] font-bold py-1.5 rounded-lg cursor-not-allowed flex items-center justify-center gap-1.5 shadow-none select-none" aria-disabled="true">Out of Stock</button>
                            @else
                                <button type="button" onclick="addToCart({{ $product->id }},'{{ addslashes($product->name) }}',{{ $price }},'{{ addslashes($coverImg) }}','{{ addslashes($minVariant?->name ?? 'Standard') }}')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-gray-500 bg-white rounded-2xl border border-gray-100 p-8 shadow-xs">
                    <p class="text-sm font-medium text-gray-700">No products available in Daily Discover yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Check back soon for new items!</p>
                </div>
            @endforelse
        </div>

        <footer class="discovery-footer">
            <span>More to explore? Find a category you love.</span>
            <a href="{{ route('home') }}#browse-categories">Explore categories <span aria-hidden="true">&rarr;</span></a>
        </footer>
    </section>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}"></script>
<script src="{{ asset('js/discovery-wishlist.js') }}?v={{ filemtime(public_path('js/discovery-wishlist.js')) }}"></script>
<script src="{{ asset('js/hero-carousel.js') }}"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/daily-discover.css') }}?v={{ filemtime(public_path('css/daily-discover.css')) }}">
<link rel="stylesheet" href="{{ asset('css/storefront-cards.css') }}?v={{ filemtime(public_path('css/storefront-cards.css')) }}">
@endpush
