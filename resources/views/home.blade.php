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
        <div class="daily-discover-grid">
            
            <!-- Product Card 1 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories sports-outdoors"
                 data-name="smart fitness tracker watch with blood oxygen heart rate monitor ip68">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '1', 'name' => 'Smart Fitness Tracker Watch IP68', 'price' => '1299', 'image' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80', 'variation' => 'Standard']])
                    <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80" alt="Smart Fitness Tracker Watch with Blood Oxygen &amp; Heart Rate Monitor IP68" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">Smart Fitness Tracker Watch with Blood Oxygen &amp; Heart Rate Monitor IP68</h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">1,299</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div><span>3.4k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(1,'Smart Fitness Tracker Watch IP68',1299,'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80','Standard')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="jewelry-accessories"
                 data-name="minimalist matte chronograph watch waterproof luxury unisex">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '2', 'name' => 'Minimalist Chronograph Watch', 'price' => '459', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80', 'variation' => 'Black']])
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80" alt="Minimalist Matte Chronograph Watch Waterproof Luxury Unisex" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">Minimalist Matte Chronograph Watch Waterproof Luxury Unisex</h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">459</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div><span>1.8k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(2,'Minimalist Chronograph Watch',459,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories"
                 data-name="anc pro wireless noise cancelling earphones deep bass bluetooth 5.3">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '3', 'name' => 'ANC Pro Wireless Earphones BT5.3', 'price' => '890', 'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80', 'variation' => 'Black']])
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80" alt="ANC Pro Wireless Noise Cancelling Earphones Deep Bass Bluetooth 5.3" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-52%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            ANC Pro Wireless Noise Cancelling Earphones Deep Bass Bluetooth 5.3
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">890</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">5.0</span>
                            </div>
                            <span>8.9k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(3,'ANC Pro Wireless Earphones BT5.3',890,'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="shoes sports-outdoors"
                 data-name="retro colorblock sneaker lightweight running walking breathable shoes">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '4', 'name' => 'Retro Colorblock Sneaker Breathable', 'price' => '650', 'image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500&h=500&fit=crop&q=80', 'variation' => 'Standard']])
                    <img src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500&h=500&fit=crop&q=80" alt="Retro Colorblock Sneaker Lightweight Running Walking Breathable Shoes" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Retro Colorblock Sneaker Lightweight Running Walking Breathable Shoes
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">650</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>920 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(4,'Retro Colorblock Sneaker Breathable',650,'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500&h=500&fit=crop&q=80','Standard')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 5 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="ceramic aesthetic coffee mug saucer set luxury minimalist cup">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '5', 'name' => 'Ceramic Coffee Mug Saucer Set', 'price' => '280', 'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80', 'variation' => 'White']])
                    <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80" alt="Ceramic Aesthetic Coffee Mug & Saucer Set Luxury Minimalist Cup" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Ceramic Aesthetic Coffee Mug & Saucer Set Luxury Minimalist Cup
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">280</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>540 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(5,'Ceramic Coffee Mug Saucer Set',280,'https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 6 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics"
                 data-name="instant print camera retro vintage pocket edition photo film">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '6', 'name' => 'Instant Print Camera Retro Vintage', 'price' => '3499', 'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=500&fit=crop&q=80', 'variation' => 'White']])
                    <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=500&fit=crop&q=80" alt="Instant Print Camera Retro Vintage Pocket Edition Photo Film" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Instant Print Camera Retro Vintage Pocket Edition Photo Film
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">3,499</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>2.1k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(6,'Instant Print Camera Retro Vintage',3499,'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>


            <!-- Product Card 7 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beauty-health"
                 data-name="korean skincare set vitamin c serum moisturizer whitening glow kit">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '7', 'name' => 'Korean Skincare Vitamin C Glow Kit', 'price' => '599', 'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop&q=80', 'variation' => 'Standard Set']])
                    <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop&q=80" alt="Korean Skincare Set Vitamin C Serum Moisturizer Whitening Glow Kit" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Korean Skincare Set Vitamin C Serum Moisturizer Whitening Glow Kit
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">599</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>6.2k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(7,'Korean Skincare Vitamin C Glow Kit',599,'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop&q=80','Standard Set')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 8 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics office-school-supplies"
                 data-name="gaming laptop 16-inch rtx 4060 144hz fhd display ultra performance">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '8', 'name' => 'Gaming Laptop 16in RTX4060 144Hz', 'price' => '52999', 'image' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&h=500&fit=crop&q=80', 'variation' => 'Space Grey']])
                    <img src="https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&h=500&fit=crop&q=80" alt="Gaming Laptop 16-inch RTX 4060 144Hz FHD Display Ultra Performance" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-18%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Gaming Laptop 16-inch RTX 4060 144Hz FHD Display Ultra Performance
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">52,999</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>780 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(8,'Gaming Laptop 16in RTX4060 144Hz',52999,'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&h=500&fit=crop&q=80','Space Grey')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 9 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living appliances"
                 data-name="non-stick cookware set 6-piece granite frying pan kitchen cooking">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '9', 'name' => 'Non-Stick Cookware Granite 6pc Set', 'price' => '1850', 'image' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80', 'variation' => 'Black Granite']])
                    <img src="https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80" alt="Non-Stick Cookware Set 6-Piece Granite Frying Pan Kitchen Cooking" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-45%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Non-Stick Cookware Set 6-Piece Granite Frying Pan Kitchen Cooking
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,850</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>2.3k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(9,'Non-Stick Cookware Granite 6pc Set',1850,'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80','Black Granite')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 10 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="bags-luggage women-clothing"
                 data-name="leather crossbody sling bag women premium anti-scratch stylish">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '10', 'name' => 'Leather Crossbody Sling Bag Women', 'price' => '749', 'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80', 'variation' => 'Brown']])
                    <img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80" alt="Leather Crossbody Sling Bag Women Premium Anti-Scratch Stylish" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-33%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Leather Crossbody Sling Bag Women Premium Anti-Scratch Stylish
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">749</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.6</span>
                            </div>
                            <span>1.5k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(10,'Leather Crossbody Sling Bag Women',749,'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80','Brown')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 11 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="men-clothing"
                 data-name="men's oversized graphic streetwear t-shirt cotton unisex drop shoulder">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '11', 'name' => 'Oversized Graphic Streetwear T-Shirt', 'price' => '349', 'image' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&h=500&fit=crop&q=80', 'variation' => 'Black / XL']])
                    <img src="https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&h=500&fit=crop&q=80" alt="Men's Oversized Graphic Streetwear T-Shirt Cotton Unisex Drop Shoulder" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-28%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Men's Oversized Graphic Streetwear T-Shirt Cotton Unisex Drop Shoulder
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">349</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>4.7k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(11,'Oversized Graphic Streetwear T-Shirt',349,'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&h=500&fit=crop&q=80','Black / XL')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 12 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="modern 3-seater sofa scandinavian living room furniture velvet upholstery">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '12', 'name' => 'Modern 3-Seater Scandinavian Sofa', 'price' => '12500', 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80', 'variation' => 'Grey Velvet']])
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80" alt="Modern 3-Seater Sofa Scandinavian Living Room Furniture Velvet Upholstery" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-15%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Modern 3-Seater Sofa Scandinavian Living Room Furniture Velvet Upholstery
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">12,500</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>310 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(12,'Modern 3-Seater Scandinavian Sofa',12500,'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80','Grey Velvet')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 13 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories sports-outdoors"
                 data-name="portable waterproof bluetooth speaker 360 surround bass 24h playtime outdoor">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '13', 'name' => 'Portable BT Speaker 360 Bass 24H', 'price' => '1499', 'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop&q=80', 'variation' => 'Black']])
                    <img src="https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop&q=80" alt="Portable Waterproof Bluetooth Speaker 360 Surround Bass 24H Playtime Outdoor" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-38%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Portable Waterproof Bluetooth Speaker 360 Surround Bass 24H Playtime Outdoor
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,499</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>4.1k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(13,'Portable BT Speaker 360 Bass 24H',1499,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 14 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="jewelry-accessories beachwear"
                 data-name="polarized uv400 sunglasses men women classic retro driving shades">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '14', 'name' => 'Polarized UV400 Sunglasses Classic', 'price' => '299', 'image' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&h=500&fit=crop&q=80', 'variation' => 'Black Frame']])
                    <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&h=500&fit=crop&q=80" alt="Polarized UV400 Sunglasses Men Women Classic Retro Driving Shades" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-50%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Polarized UV400 Sunglasses Men Women Classic Retro Driving Shades
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">299</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>8.5k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(14,'Polarized UV400 Sunglasses Classic',299,'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&h=500&fit=crop&q=80','Black Frame')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 15 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living sports-outdoors"
                 data-name="vacuum insulated stainless steel tumbler 32oz flask double wall leakproof">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '15', 'name' => 'Vacuum Insulated Steel Tumbler 32oz', 'price' => '480', 'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80', 'variation' => 'Midnight Blue']])
                    <img src="https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80" alt="Vacuum Insulated Stainless Steel Tumbler 32oz Flask Double Wall Leakproof" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Vacuum Insulated Stainless Steel Tumbler 32oz Flask Double Wall Leakproof
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">480</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>12.4k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(15,'Vacuum Insulated Steel Tumbler 32oz',480,'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80','Midnight Blue')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 16 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics office-school-supplies"
                 data-name="rgb mechanical gaming keyboard hot-swappable blue switch wireless ergonomic">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '16', 'name' => 'RGB Mechanical Gaming Keyboard Wireless', 'price' => '1650', 'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&h=500&fit=crop&q=80', 'variation' => 'Black / Blue Switch']])
                    <img src="https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&h=500&fit=crop&q=80" alt="RGB Mechanical Gaming Keyboard Hot-Swappable Blue Switch Wireless Ergonomic" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-22%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            RGB Mechanical Gaming Keyboard Hot-Swappable Blue Switch Wireless Ergonomic
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,650</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>3.2k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(16,'RGB Mechanical Gaming Keyboard Wireless',1650,'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&h=500&fit=crop&q=80','Black / Blue Switch')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 17 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beauty-health"
                 data-name="luxury eau de parfum 100ml long lasting fresh floral woody fragrance unisex">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '17', 'name' => 'Luxury Eau De Parfum 100ml Unisex', 'price' => '1120', 'image' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80', 'variation' => '100ml']])
                    <img src="https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80" alt="Luxury Eau De Parfum 100ml Long Lasting Fresh Floral Woody Fragrance Unisex" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Luxury Eau De Parfum 100ml Long Lasting Fresh Floral Woody Fragrance Unisex
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,120</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>1.9k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(17,'Luxury Eau De Parfum 100ml Unisex',1120,'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80','100ml')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 18 -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="minimalist nordic table lamp warm led ambient light bedside nightstand">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '18', 'name' => 'Minimalist Nordic LED Table Lamp', 'price' => '899', 'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80', 'variation' => 'White']])
                    <img src="https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80" alt="Minimalist Nordic Table Lamp Warm LED Ambient Light Bedside Nightstand" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Minimalist Nordic Table Lamp Warm LED Ambient Light Bedside Nightstand
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">899</span>
                        </div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>890 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(18,'Minimalist Nordic LED Table Lamp',899,'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 19 (Women Clothing) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="women-clothing"
                 data-name="women's floral summer bohemian midi dress puff sleeve elegant chic">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '19', 'name' => 'Women Floral Summer Bohemian Midi Dress', 'price' => '489', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80', 'variation' => 'Floral / Medium']])
                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80" alt="Women's Floral Summer Bohemian Midi Dress Puff Sleeve Elegant Chic" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Women's Floral Summer Bohemian Midi Dress Puff Sleeve Elegant Chic
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">489</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>3.8k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(19,'Women Floral Summer Bohemian Midi Dress',489,'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80','Floral / Medium')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 20 (Beachwear) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beachwear"
                 data-name="quick-dry beach swim shorts cover-up tropical vacation swimwear">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '20', 'name' => 'Quick-Dry Beach Tropical Swim Set', 'price' => '399', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&h=500&fit=crop&q=80', 'variation' => 'Ocean Blue / L']])
                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&h=500&fit=crop&q=80" alt="Quick-Dry Beach Swim Shorts &amp; Sun Cover-Up Tropical Vacation Set" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Quick-Dry Beach Swim Shorts &amp; Sun Cover-Up Tropical Vacation Set
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">399</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.4k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(20,'Quick-Dry Beach Tropical Swim Set',399,'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&h=500&fit=crop&q=80','Ocean Blue / L')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 21 (Kids) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="kids"
                 data-name="kids dinosaur 2-piece cotton adventure t-shirt shorts set children">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '21', 'name' => 'Kids Dinosaur 2-Piece Cotton Playset', 'price' => '349', 'image' => 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=500&h=500&fit=crop&q=80', 'variation' => 'Age 4-6 / Green']])
                    <img src="https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=500&h=500&fit=crop&q=80" alt="Kids Dinosaur 2-Piece Breathable Cotton Playset T-Shirt &amp; Shorts" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Kids Dinosaur 2-Piece Breathable Cotton Playset T-Shirt &amp; Shorts
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">349</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>2.2k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(21,'Kids Dinosaur 2-Piece Cotton Playset',349,'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=500&h=500&fit=crop&q=80','Age 4-6 / Green')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 22 (Curve) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="curve women-clothing"
                 data-name="curve plus-size flattering wrap dress stretch waistline elegant party">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '22', 'name' => 'Curve Plus-Size Flattering Wrap Dress', 'price' => '650', 'image' => 'https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80', 'variation' => '2XL / Plum']])
                    <img src="https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80" alt="Curve Plus-Size Flattering Silhouette Wrap Dress Stretch Waistline" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Curve Plus-Size Flattering Silhouette Wrap Dress Stretch Waistline
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">650</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.5k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(22,'Curve Plus-Size Flattering Wrap Dress',650,'https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80','2XL / Plum')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 23 (Underwear & Sleepwear) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="underwear-sleepwear"
                 data-name="mulberry silk sleepwear pajama loungewear set luxury smooth">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '23', 'name' => 'Mulberry Silk Sleepwear Pajama Set', 'price' => '790', 'image' => 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=500&h=500&fit=crop&q=80', 'variation' => 'Champagne / M']])
                    <img src="https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=500&h=500&fit=crop&q=80" alt="Mulberry Silk Sleepwear 2-Piece Pajama Set Ultra-Soft Loungewear" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Mulberry Silk Sleepwear 2-Piece Pajama Set Ultra-Soft Loungewear
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">790</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>2.4k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(23,'Mulberry Silk Sleepwear Pajama Set',790,'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=500&h=500&fit=crop&q=80','Champagne / M')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 24 (Baby & Maternity) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="baby-maternity"
                 data-name="organic bamboo soft baby swaddle blankets newborn wrap nursery">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '24', 'name' => 'Organic Bamboo Baby Swaddles 3pk', 'price' => '420', 'image' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&q=80', 'variation' => 'Pastel Trio']])
                    <img src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&q=80" alt="Soft Organic Bamboo Baby Swaddle Blankets 3-Pack Breathable Nursery" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Soft Organic Bamboo Baby Swaddle Blankets 3-Pack Breathable Nursery
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">420</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">5.0</span></div>
                            <span>1.7k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(24,'Organic Bamboo Baby Swaddles 3pk',420,'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&q=80','Pastel Trio')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 25 (Home Textiles) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-textiles home-living"
                 data-name="100% egyptian cotton luxury 4-piece bedding sheet set queen duvet">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '25', 'name' => 'Egyptian Cotton 4pc Bedding Set', 'price' => '1250', 'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=500&fit=crop&q=80', 'variation' => 'Queen / Slate Grey']])
                    <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=500&fit=crop&q=80" alt="100% Egyptian Cotton Luxury 4-Piece Bedding Sheet Set Queen Soft" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-42%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            100% Egyptian Cotton Luxury 4-Piece Bedding Sheet Set Queen Soft
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">1,250</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>1.3k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(25,'Egyptian Cotton 4pc Bedding Set',1250,'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=500&fit=crop&q=80','Queen / Slate Grey')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 26 (Tools & Home Improvement) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="tools-home-improvement"
                 data-name="48-in-1 cordless precision electric screwdriver kit repair tool">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '26', 'name' => '48-in-1 Cordless Precision Screwdriver Kit', 'price' => '850', 'image' => 'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=500&h=500&fit=crop&q=80', 'variation' => '48-pc Set']])
                    <img src="https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=500&h=500&fit=crop&q=80" alt="48-in-1 Cordless Precision Electric Screwdriver Kit Rechargeable DIY" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            48-in-1 Cordless Precision Electric Screwdriver Kit Rechargeable DIY
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">850</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>890 sold</span>
                        </div>
                        <button type="button" onclick="addToCart(26,'48-in-1 Cordless Precision Screwdriver Kit',850,'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=500&h=500&fit=crop&q=80','48-pc Set')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 27 (Toys & Games) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="toys-games kids"
                 data-name="stem magnetic educational building blocks set 3d geometric toy">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '27', 'name' => 'STEM Magnetic Building Blocks Set', 'price' => '599', 'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=500&h=500&fit=crop&q=80', 'variation' => '64-Piece']])
                    <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=500&h=500&fit=crop&q=80" alt="STEM Magnetic Educational Building Blocks Set 3D Geometric Tiles" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            STEM Magnetic Educational Building Blocks Set 3D Geometric Tiles
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">599</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>1.8k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(27,'STEM Magnetic Building Blocks Set',599,'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=500&h=500&fit=crop&q=80','64-Piece')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 28 (Pet Supplies) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="pet-supplies"
                 data-name="self-cleaning pet grooming slicker brush for dogs cats shedding">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '28', 'name' => 'Self-Cleaning Pet Slicker Brush', 'price' => '249', 'image' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&h=500&fit=crop&q=80', 'variation' => 'Pastel Purple']])
                    <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&h=500&fit=crop&q=80" alt="Self-Cleaning Pet Grooming Slicker Brush for Dogs &amp; Cats Deshedding" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Self-Cleaning Pet Grooming Slicker Brush for Dogs &amp; Cats Deshedding
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">249</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>3.1k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(28,'Self-Cleaning Pet Slicker Brush',249,'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&h=500&fit=crop&q=80','Pastel Purple')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 29 (Appliances) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="appliances home-living"
                 data-name="touchscreen digital air fryer 4.5l oil-less rapid heat kitchen appliance">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '29', 'name' => 'Touchscreen Digital Air Fryer 4.5L', 'price' => '2199', 'image' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80', 'variation' => 'Matte Black']])
                    <img src="https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80" alt="Touchscreen Digital Air Fryer 4.5L Oil-Free Rapid Heat 8 Presets" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-38%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Touchscreen Digital Air Fryer 4.5L Oil-Free Rapid Heat 8 Presets
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">2,199</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.6k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(29,'Touchscreen Digital Air Fryer 4.5L',2199,'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80','Matte Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 30 (Office & School Supplies) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="office-school-supplies"
                 data-name="aesthetic pastel gel pens set 12-pack quick-dry 0.5mm stationery">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '30', 'name' => 'Aesthetic Pastel Gel Pens 12pk', 'price' => '180', 'image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&h=500&fit=crop&q=80', 'variation' => 'Pastel Rainbow']])
                    <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&h=500&fit=crop&q=80" alt="Aesthetic Pastel Gel Pen Set 12-Pack Smooth Quick-Dry 0.5mm Fine Tip" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Aesthetic Pastel Gel Pen Set 12-Pack Smooth Quick-Dry 0.5mm Fine Tip
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">180</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>5.2k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(30,'Aesthetic Pastel Gel Pens 12pk',180,'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&h=500&fit=crop&q=80','Pastel Rainbow')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 31 (Automotive) -->
            <div class="discovery-card product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="automotive"
                 data-name="high-power cordless handheld car vacuum cleaner 9000pa wireless auto">
                <div class="discovery-media relative aspect-square overflow-hidden bg-gray-50">
                    @include('partials.discovery-wishlist', ['product' => ['ref' => '31', 'name' => 'Cordless Handheld Car Vacuum 9000Pa', 'price' => '750', 'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop&q=80', 'variation' => 'Black / Wireless']])
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop&q=80" alt="High-Power Cordless Handheld Car Vacuum Cleaner 9000Pa Wireless Auto" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="discovery-seller bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="discovery-discount absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-45%</span>
                </div>
                <div class="discovery-body p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            High-Power Cordless Handheld Car Vacuum Cleaner 9000Pa Wireless Auto
                        </h3>
                    </div>
                    <div class="discovery-pricing mt-3 pt-2.5 border-t border-gray-100">
                        <div class="discovery-price flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">750</span></div>
                        <div class="discovery-rating flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.9k sold</span>
                        </div>
                        <button type="button" onclick="addToCart(31,'Cordless Handheld Car Vacuum 9000Pa',750,'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop&q=80','Black / Wireless')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>
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
