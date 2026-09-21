@extends('layouts.app')

@section('title', 'cartzy')

@section('content')
<div class="w-full px-3 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- 1. Hero Promo Banner Section (Edge-to-Edge Grid) -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full">
        <!-- Main Hero Banner (Left 2 cols) -->
        <div class="lg:col-span-2 relative rounded-2xl overflow-hidden shadow-lg bg-[#282133] h-72 sm:h-88 lg:h-[380px] flex items-center justify-between p-6 sm:p-10 text-white group border border-[#3E354C]">
            <div class="space-y-4 max-w-2xl z-10">
                <span class="inline-block bg-[#A8A0B2] text-[#191421] text-xs font-extrabold uppercase px-3.5 py-1.5 rounded-full tracking-wider shadow-sm">
                    ✦ NEW SEASON COLLECTION
                </span>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight text-white">
                    Premium Quality Essentials &amp; Exclusive Deals
                </h1>
                <p class="text-xs sm:text-base text-[#C9C3D3] font-normal leading-relaxed">
                    Enjoy up to 60% off select brands, complimentary shipping vouchers, and verified authentic products.
                </p>
                <div class="pt-2">
                    <a href="#shop-now" class="inline-flex items-center gap-2.5 bg-[#A8A0B2] text-[#191421] hover:bg-[#91879E] hover:text-white px-6 py-3 rounded-xl font-bold text-sm shadow-sm transition-all transform active:scale-95">
                        Shop the Collection
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Background Gradient Texture -->
            <div class="absolute inset-0 bg-gradient-to-tr from-[#191421] via-[#282133] to-[#3E354C] opacity-95"></div>
            <!-- Decorative Vector Icon -->
            <div class="absolute right-4 bottom-4 opacity-10 pointer-events-none transform translate-x-4 translate-y-4">
                <svg class="w-80 h-80 fill-white" viewBox="0 0 24 24">
                    <path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h6v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/>
                </svg>
            </div>
            <!-- Lavender glow orb -->
            <div class="absolute top-0 right-0 w-72 h-72 rounded-full bg-[#A8A0B2]/10 blur-3xl pointer-events-none"></div>
        </div>

        <!-- 2 Side Banners (Right 1 col) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4 h-72 sm:h-88 lg:h-[380px]">
            <!-- Mini Banner 1: Flexible Payments -->
            <div class="rounded-2xl overflow-hidden bg-[#F1EFF5] border border-[#E1DDE7] p-6 sm:p-8 text-[#282133] flex flex-col justify-between shadow-sm relative hover:border-[#A8A0B2] hover:shadow-md transition-all">
                <div>
                    <span class="bg-white text-[#6F6382] text-[10px] sm:text-[11px] font-bold px-3 py-1 rounded-md uppercase tracking-wider border border-[#E1DDE7]">Flexible Payments</span>
                    <h3 class="text-lg sm:text-xl font-extrabold mt-2 sm:mt-3 text-[#282133]">0% Interest Installments</h3>
                    <p class="text-xs sm:text-sm text-[#6F6382] mt-1 sm:mt-1.5 leading-relaxed">Split payments into 3, 6, or 12 convenient monthly terms</p>
                </div>
                <a href="#" class="text-xs sm:text-sm font-bold text-[#6F6382] hover:text-[#564B68] hover:underline flex items-center gap-1.5">Learn More &rarr;</a>
            </div>

            <!-- Mini Banner 2: Express Delivery -->
            <div class="rounded-2xl overflow-hidden bg-[#564B68] text-white p-6 sm:p-8 flex flex-col justify-between shadow-sm relative hover:bg-[#3E354C] transition-all border border-[#6F6382]">
                <div>
                    <span class="bg-[#A8A0B2]/30 text-[#FAF9FB] text-[10px] sm:text-[11px] font-bold px-3 py-1 rounded-md uppercase tracking-wider border border-[#A8A0B2]/40">Express Delivery</span>
                    <h3 class="text-lg sm:text-xl font-extrabold mt-2 sm:mt-3 text-white">Next Day Nationwide</h3>
                    <p class="text-xs sm:text-sm text-[#C9C3D3] mt-1 sm:mt-1.5 leading-relaxed">Guaranteed prompt dispatch on verified partner items</p>
                </div>
                <a href="#" class="text-xs sm:text-sm font-bold text-[#E1DDE7] hover:text-white hover:underline flex items-center gap-1.5">Explore Express &rarr;</a>
            </div>
        </div>
    </section>

    <!-- 2. Popular Categories Section (Human-crafted realistic marketplace photography) -->
    <section class="bg-white rounded-2xl p-5 sm:p-7 shadow-2xs border border-gray-200 w-full">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-5 sm:mb-6 pb-3 border-b border-gray-100">
            <div>
                <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-gray-400">Curated Collections</p>
                <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">
                    POPULAR CATEGORIES
                </h2>
            </div>
            <a href="#all-categories" class="text-xs sm:text-sm font-bold text-gray-700 hover:text-black hover:underline flex items-center gap-1 group">
                <span>See All Categories</span>
                <span class="group-hover:translate-x-0.5 transition-transform font-mono">&rarr;</span>
            </a>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
            
            <!-- Category 1: Mobiles & Gadgets -->
            <a href="#mobiles" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&h=300&fit=crop&q=80"
                        alt="Mobiles & Gadgets"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Mobiles &amp; Gadgets</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">3.4k+ items</span>
            </a>

            <!-- Category 2: Men's Fashion -->
            <a href="#mens-fashion" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=300&h=300&fit=crop&q=80"
                        alt="Men's Fashion"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Men's Fashion</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">2.8k+ items</span>
            </a>

            <!-- Category 3: Women's Fashion -->
            <a href="#womens-fashion" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=300&h=300&fit=crop&q=80"
                        alt="Women's Fashion"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Women's Fashion</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">4.2k+ items</span>
            </a>

            <!-- Category 4: Shoes & Sneakers -->
            <a href="#shoes" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1552346154-21d32810aba3?w=300&h=300&fit=crop&q=80"
                        alt="Shoes & Sneakers"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Shoes &amp; Sneakers</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">1.9k+ items</span>
            </a>

            <!-- Category 5: Beauty & Skincare -->
            <a href="#beauty" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1556228720-195a672e8a03?w=300&h=300&fit=crop&q=80"
                        alt="Beauty & Skincare"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Beauty &amp; Skincare</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">2.5k+ items</span>
            </a>

            <!-- Category 6: Home & Living -->
            <a href="#home-living" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=300&h=300&fit=crop&q=80"
                        alt="Home & Living"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Home &amp; Living</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">1.6k+ items</span>
            </a>

            <!-- Category 7: Laptops & Computers -->
            <a href="#laptops" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=300&h=300&fit=crop&q=80"
                        alt="Laptops & Computers"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Laptops &amp; Tech</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">980+ items</span>
            </a>

            <!-- Category 8: Audio & Headphones -->
            <a href="#audio" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
                <div class="w-full max-w-[110px] aspect-square rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/90 group-hover:border-black/40 group-hover:shadow-md transition-all relative">
                    <img
                        src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&h=300&fit=crop&q=80"
                        alt="Audio & Headphones"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-black mt-2.5 leading-tight truncate w-full">Audio &amp; Sound</span>
                <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium mt-0.5">1.2k+ items</span>
            </a>

        </div>
    </section>

    <!-- 3. Flash Deals Section (Edge-to-Edge 6-Column Grid) -->
    <section class="bg-white rounded-2xl shadow-2xs border border-gray-200 overflow-hidden w-full">
        <!-- Header -->
        <div class="p-5 sm:p-6 bg-white border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    ⚡ FLASH DEALS
                </span>
                <!-- Countdown Timer -->
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-bold" id="flash-sale-timer">
                    <span class="bg-[#6F6382] text-white px-2.5 py-1 rounded-md font-mono shadow-xs">02</span>
                    <span class="text-[#6F6382] font-bold">:</span>
                    <span class="bg-[#6F6382] text-white px-2.5 py-1 rounded-md font-mono shadow-xs">45</span>
                    <span class="text-[#6F6382] font-bold">:</span>
                    <span class="bg-[#564B68] text-white px-2.5 py-1 rounded-md font-mono animate-pulse shadow-xs">18</span>
                </div>
            </div>
            <a href="#flash-deals-all" class="text-xs sm:text-sm font-bold text-gray-900 hover:underline flex items-center gap-1">
                View All Deals &gt;
            </a>
        </div>

        <!-- Flash Items Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 divide-x divide-y md:divide-y-0 divide-gray-100 p-2 sm:p-3 w-full">
            
            <!-- Flash Item 1 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400&h=400&fit=crop&q=80" alt="Smartwatch" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -65%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱399</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱1,199</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 82%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">18 SOLD</span>
                    </div>
                </div>
            </div>

            <!-- Flash Item 2 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&q=80" alt="Headphones" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -50%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱749</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱1,499</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 60%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">12 SOLD</span>
                    </div>
                </div>
            </div>

            <!-- Flash Item 3 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=400&h=400&fit=crop&q=80" alt="Retro Camera" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -40%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱1,899</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱3,199</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 95%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">ALMOST SOLD</span>
                    </div>
                </div>
            </div>

            <!-- Flash Item 4 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop&q=80" alt="Sunglasses" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -70%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱189</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱650</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 45%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">9 SOLD</span>
                    </div>
                </div>
            </div>

            <!-- Flash Item 5 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop&q=80" alt="Skincare" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -55%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱99</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱220</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 30%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">6 SOLD</span>
                    </div>
                </div>
            </div>

            <!-- Flash Item 6 -->
            <div class="p-3 sm:p-4 group cursor-pointer hover:shadow-md transition bg-white flex flex-col justify-between rounded-xl">
                <div class="relative overflow-hidden rounded-xl bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=80" alt="Sneakers" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-2xs">
                        -45%
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm sm:text-base font-extrabold text-gray-900">₱1,250</div>
                    <div class="text-[11px] sm:text-xs text-gray-400 line-through">₱2,299</div>
                    <div class="mt-2 relative w-full bg-gray-100 rounded-full h-3.5 sm:h-4 overflow-hidden">
                        <div class="bg-[#6F6382] h-full rounded-full" style="width: 75%"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider">25 SOLD</span>
                    </div>
                </div>
            </div>

        </div>
    </section>



    
    <!-- Search Results Announcement Bar (Dynamically shown when searching) -->
    <div id="search-results-banner" class="hidden bg-gray-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-800 flex flex-wrap items-center justify-between gap-3 w-full">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-lg shrink-0">🔍</span>
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
        <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-2xl mb-3">👟</div>
        <h3 class="text-base sm:text-lg font-bold text-gray-900">No matching products found</h3>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md">Try searching with a different term like "Nike", "Adidas", "Jordan", "Ultraboost", "Watch", or "Earphones".</p>
        <button type="button" onclick="clearSearch()" class="mt-4 bg-black hover:bg-gray-800 text-white text-xs font-bold px-6 py-2.5 rounded-xl transition shadow-xs">
            View All Available Products
        </button>
    </div>

    <!-- 4. Shoes & Sneakers Section (Nike & Adidas Curated Showcase) -->
    <section id="shoes" class="space-y-4 w-full scroll-mt-24">
        <!-- Section Header -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                    👟
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider bg-gray-100 text-gray-800 px-2 py-0.5 rounded">
                            Official Brand Partners
                        </span>
                        <span class="text-xs text-green-600 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> 100% Authentic
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-0.5">
                        SHOES &amp; SNEAKERS
                    </h2>
                    <p class="text-xs text-gray-500 font-normal">Featuring official Nike &amp; Adidas footwear with verified authentic guarantee</p>
                </div>
            </div>

            <!-- Brand Filter Tabs (All / Nike / Adidas) -->
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl text-xs font-bold text-gray-700">
                <button type="button" onclick="filterShoeBrand('all')" id="tab-brand-all" class="brand-tab-btn active bg-white text-black px-3.5 py-1.5 rounded-lg shadow-2xs transition">
                    All (4)
                </button>
                <button type="button" onclick="filterShoeBrand('nike')" id="tab-brand-nike" class="brand-tab-btn hover:bg-white/70 text-gray-600 hover:text-black px-3.5 py-1.5 rounded-lg transition flex items-center gap-1">
                    <span>Nike</span>
                    <span class="text-[10px] bg-gray-200 text-gray-800 px-1.5 py-0.2 rounded-full font-bold">2</span>
                </button>
                <button type="button" onclick="filterShoeBrand('adidas')" id="tab-brand-adidas" class="brand-tab-btn hover:bg-white/70 text-gray-600 hover:text-black px-3.5 py-1.5 rounded-lg transition flex items-center gap-1">
                    <span>Adidas</span>
                    <span class="text-[10px] bg-gray-200 text-gray-800 px-1.5 py-0.2 rounded-full font-bold">2</span>
                </button>
            </div>
        </div>

        <!-- 4-Column Shoes Grid (2 Nike + 2 Adidas) -->
        <div id="shoes-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3 w-full">
            
            <!-- Shoe 1: Nike Air Max 270 React -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-brand="nike"
                 data-name="nike air max 270 react sports running shoes gym red white sneaker footwear"
                 data-category="shoes sneakers">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=500&fit=crop&q=80" 
                         alt="Nike Air Max 270 React" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded shadow-2xs tracking-wide uppercase">
                            Nike Official
                        </span>
                        <span class="bg-[#6F6382] text-white text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-2xs">
                            Hot Deal
                        </span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">
                        -35%
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Nike Philippines</div>
                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Nike Air Max 270 React Sports Running Shoes - Gym Red / White
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                            <span class="text-[10px] text-[#6F6382] bg-[#F1EFF5] border border-[#E1DDE7] px-1.5 py-0.5 rounded font-medium">Authentic</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">5,899</span>
                            <span class="text-[11px] text-gray-400 line-through">₱8,999</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                ★ <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>2.4k sold</span>
                        </div>
                        <button onclick="addToCart(101,'Nike Air Max 270 React Gym Red',5899,'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=500&fit=crop&q=80','Size 9 / Gym Red')" 
                                class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shoe 2: Nike Air Jordan 1 High Retro -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-brand="nike"
                 data-name="nike air jordan 1 high retro heritage classic basketball shoes high top sneakers"
                 data-category="shoes sneakers">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?w=500&h=500&fit=crop&q=80" 
                         alt="Nike Air Jordan 1 High Retro" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded shadow-2xs tracking-wide uppercase">
                            Nike Official
                        </span>
                        <span class="bg-[#564B68] text-white text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-2xs">
                            Collector's
                        </span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">
                        -25%
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Jordan Brand</div>
                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Nike Air Jordan 1 High Retro Heritage Classic Basketball Shoes
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                            <span class="text-[10px] text-[#6F6382] bg-[#F1EFF5] border border-[#E1DDE7] px-1.5 py-0.5 rounded font-medium">Authentic</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">7,495</span>
                            <span class="text-[11px] text-gray-400 line-through">₱9,995</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                ★ <span class="text-gray-600 font-medium">5.0</span>
                            </div>
                            <span>3.8k sold</span>
                        </div>
                        <button onclick="addToCart(102,'Nike Air Jordan 1 High Retro',7495,'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=500&h=500&fit=crop&q=80','Size 9.5 / High Top')" 
                                class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shoe 3: Adidas Ultraboost 22 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-brand="adidas"
                 data-name="adidas ultraboost 22 primeknit high performance running shoes boost sneakers"
                 data-category="shoes sneakers">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=500&h=500&fit=crop&q=80" 
                         alt="Adidas Ultraboost 22" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded shadow-2xs tracking-wide uppercase">
                            Adidas Official
                        </span>
                        <span class="bg-[#564B68] text-white text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-2xs">
                            Top Rated
                        </span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">
                        -30%
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Adidas Performance</div>
                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Adidas Ultraboost 22 Primeknit High Performance Running Shoes
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                            <span class="text-[10px] text-[#6F6382] bg-[#F1EFF5] border border-[#E1DDE7] px-1.5 py-0.5 rounded font-medium">Authentic</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">6,499</span>
                            <span class="text-[11px] text-gray-400 line-through">₱9,299</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                ★ <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>1.9k sold</span>
                        </div>
                        <button onclick="addToCart(103,'Adidas Ultraboost 22 Primeknit',6499,'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=500&h=500&fit=crop&q=80','Size 9 / Core Black')" 
                                class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shoe 4: Adidas Originals Superstar Classic -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-brand="adidas"
                 data-name="adidas originals superstar classic 3-stripes leather unisex sneakers shoes white black"
                 data-category="shoes sneakers">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=500&h=500&fit=crop&q=80" 
                         alt="Adidas Originals Superstar" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded shadow-2xs tracking-wide uppercase">
                            Adidas Official
                        </span>
                        <span class="bg-[#564B68] text-white text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-2xs">
                            Iconic Classic
                        </span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">
                        -20%
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Adidas Originals</div>
                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Adidas Originals Superstar Classic 3-Stripes Leather Unisex Shoes
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                            <span class="text-[10px] text-[#6F6382] bg-[#F1EFF5] border border-[#E1DDE7] px-1.5 py-0.5 rounded font-medium">Authentic</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">4,500</span>
                            <span class="text-[11px] text-gray-400 line-through">₱5,600</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                ★ <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>4.5k sold</span>
                        </div>
                        <button onclick="addToCart(104,'Adidas Originals Superstar Classic',4500,'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=500&h=500&fit=crop&q=80','Size 8.5 / White-Black')" 
                                class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>


    <!-- 5. Daily Discover / Recommended For You (Edge-to-Edge 6-Column Feed) -->
    <section id="daily-discover-section" class="space-y-4 w-full">
        <!-- Sticky Section Header -->
        <div class="sticky top-18 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-[#E1DDE7] shadow-2xs flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 bg-[#6F6382] rounded-full"></span>
                <h2 class="text-base sm:text-lg font-extrabold text-gray-900 uppercase tracking-wide">
                    Daily Discover
                </h2>
            </div>
            <span class="text-xs sm:text-sm text-gray-500 font-medium">Curated deals tailored for you</span>
        </div>

        <!-- Multi-Column Responsive Product Cards Grid (Fills full width) -->
        <div class="daily-discover-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7 xl:grid-cols-9 gap-2 sm:gap-3 w-full">
            
            <!-- Product Card 1 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">Smart Fitness Tracker Watch with Blood Oxygen &amp; Heart Rate Monitor IP68</h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">1,299</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div><span>3.4k sold</span>
                        </div>
                        <button onclick="addToCart(1,'Smart Fitness Tracker Watch IP68',1299,'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80','Standard')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2 flex flex-col gap-1"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">Minimalist Matte Chronograph Watch Waterproof Luxury Unisex</h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">459</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div><span>1.8k sold</span>
                        </div>
                        <button onclick="addToCart(2,'Minimalist Chronograph Watch',459,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-52%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            ANC Pro Wireless Noise Cancelling Earphones Deep Bass Bluetooth 5.3
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">890</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">5.0</span>
                            </div>
                            <span>8.9k sold</span>
                        </div>
                        <button onclick="addToCart(3,'ANC Pro Wireless Earphones BT5.3',890,'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Retro Colorblock Sneaker Lightweight Running Walking Breathable Shoes
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">650</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>920 sold</span>
                        </div>
                        <button onclick="addToCart(4,'Retro Colorblock Sneaker Breathable',650,'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500&h=500&fit=crop&q=80','Standard')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 5 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Ceramic Aesthetic Coffee Mug & Saucer Set Luxury Minimalist Cup
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">280</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>540 sold</span>
                        </div>
                        <button onclick="addToCart(5,'Ceramic Coffee Mug Saucer Set',280,'https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 6 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Instant Print Camera Retro Vintage Pocket Edition Photo Film
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">3,499</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>2.1k sold</span>
                        </div>
                        <button onclick="addToCart(6,'Instant Print Camera Retro Vintage',3499,'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>


            <!-- Product Card 7 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Korean Skincare Set Vitamin C Serum Moisturizer Whitening Glow Kit
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">599</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>6.2k sold</span>
                        </div>
                        <button onclick="addToCart(7,'Korean Skincare Vitamin C Glow Kit',599,'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop&q=80','Standard Set')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 8 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-18%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Gaming Laptop 16-inch RTX 4060 144Hz FHD Display Ultra Performance
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">52,999</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>780 sold</span>
                        </div>
                        <button onclick="addToCart(8,'Gaming Laptop 16in RTX4060 144Hz',52999,'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&h=500&fit=crop&q=80','Space Grey')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 9 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-45%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Non-Stick Cookware Set 6-Piece Granite Frying Pan Kitchen Cooking
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,850</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>2.3k sold</span>
                        </div>
                        <button onclick="addToCart(9,'Non-Stick Cookware Granite 6pc Set',1850,'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80','Black Granite')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 10 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-33%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Leather Crossbody Sling Bag Women Premium Anti-Scratch Stylish
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">749</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.6</span>
                            </div>
                            <span>1.5k sold</span>
                        </div>
                        <button onclick="addToCart(10,'Leather Crossbody Sling Bag Women',749,'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80','Brown')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 11 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-28%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Men's Oversized Graphic Streetwear T-Shirt Cotton Unisex Drop Shoulder
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">349</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>4.7k sold</span>
                        </div>
                        <button onclick="addToCart(11,'Oversized Graphic Streetwear T-Shirt',349,'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&h=500&fit=crop&q=80','Black / XL')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 12 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-15%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Modern 3-Seater Sofa Scandinavian Living Room Furniture Velvet Upholstery
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">12,500</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>310 sold</span>
                        </div>
                        <button onclick="addToCart(12,'Modern 3-Seater Scandinavian Sofa',12500,'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80','Grey Velvet')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 13 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-38%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Portable Waterproof Bluetooth Speaker 360 Surround Bass 24H Playtime Outdoor
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,499</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>4.1k sold</span>
                        </div>
                        <button onclick="addToCart(13,'Portable BT Speaker 360 Bass 24H',1499,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=500&fit=crop&q=80','Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 14 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-50%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Polarized UV400 Sunglasses Men Women Classic Retro Driving Shades
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">299</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>8.5k sold</span>
                        </div>
                        <button onclick="addToCart(14,'Polarized UV400 Sunglasses Classic',299,'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&h=500&fit=crop&q=80','Black Frame')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 15 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Vacuum Insulated Stainless Steel Tumbler 32oz Flask Double Wall Leakproof
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">480</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>12.4k sold</span>
                        </div>
                        <button onclick="addToCart(15,'Vacuum Insulated Steel Tumbler 32oz',480,'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80','Midnight Blue')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 16 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-22%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            RGB Mechanical Gaming Keyboard Hot-Swappable Blue Switch Wireless Ergonomic
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,650</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span>
                            </div>
                            <span>3.2k sold</span>
                        </div>
                        <button onclick="addToCart(16,'RGB Mechanical Gaming Keyboard Wireless',1650,'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&h=500&fit=crop&q=80','Black / Blue Switch')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 17 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span>
                    </div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Luxury Eau De Parfum 100ml Long Lasting Fresh Floral Woody Fragrance Unisex
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">1,120</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span>
                            </div>
                            <span>1.9k sold</span>
                        </div>
                        <button onclick="addToCart(17,'Luxury Eau De Parfum 100ml Unisex',1120,'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80','100ml')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 18 -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Minimalist Nordic Table Lamp Warm LED Ambient Light Bedside Nightstand
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-[#6F6382]">₱</span>
                            <span class="text-base sm:text-lg font-black text-gray-900">899</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold">
                                <span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.7</span>
                            </div>
                            <span>890 sold</span>
                        </div>
                        <button onclick="addToCart(18,'Minimalist Nordic LED Table Lamp',899,'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- See More Button -->
        <div class="pt-6 text-center">
            <button class="bg-white hover:bg-[#6F6382] hover:text-white text-gray-900 border border-[#E1DDE7] hover:border-[#6F6382] px-12 py-3 rounded-xl text-sm font-bold shadow-2xs transition-all duration-200 inline-flex items-center gap-2 cursor-pointer">
                See More Products
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('header-search-input');
    const searchForm = document.getElementById('header-search-form');
    const searchClear = document.getElementById('header-search-clear');
    const resultsBanner = document.getElementById('search-results-banner');
    const noResults = document.getElementById('no-search-results');
    const queryDisplay = document.getElementById('search-query-display');
    const countDisplay = document.getElementById('search-count-display');
    const shoesSection = document.getElementById('shoes');
    const dailySection = document.getElementById('daily-discover-section');

    function performSearch(query, shouldScroll = false) {
        query = (query || '').trim().toLowerCase();
        
        // Show or hide clear cross button
        if (searchClear) {
            if (query.length > 0) {
                searchClear.classList.remove('hidden');
            } else {
                searchClear.classList.add('hidden');
            }
        }

        const allCards = document.querySelectorAll('.product-card');
        let matchedCount = 0;
        let matchedInShoes = 0;
        let matchedInDaily = 0;

        if (!query) {
            // Restore everything
            allCards.forEach(card => card.classList.remove('hidden'));
            if (resultsBanner) resultsBanner.classList.add('hidden');
            if (noResults) {
                noResults.classList.add('hidden');
                noResults.classList.remove('flex');
            }
            if (shoesSection) shoesSection.style.display = '';
            if (dailySection) dailySection.style.display = '';
            return;
        }

        allCards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const brand = (card.getAttribute('data-brand') || '').toLowerCase();
            const category = (card.getAttribute('data-category') || '').toLowerCase();
            const cardText = (card.innerText || '').toLowerCase();

            const isMatch = name.includes(query) || 
                            brand.includes(query) || 
                            category.includes(query) || 
                            cardText.includes(query);

            if (isMatch) {
                card.classList.remove('hidden');
                matchedCount++;
                if (shoesSection && shoesSection.contains(card)) {
                    matchedInShoes++;
                } else if (dailySection && dailySection.contains(card)) {
                    matchedInDaily++;
                }
            } else {
                card.classList.add('hidden');
            }
        });

        // Update banner
        if (resultsBanner) {
            resultsBanner.classList.remove('hidden');
            if (queryDisplay) queryDisplay.textContent = query;
            if (countDisplay) countDisplay.textContent = matchedCount;
        }

        // Toggle sections based on matches
        if (shoesSection) {
            shoesSection.style.display = matchedInShoes > 0 ? '' : 'none';
        }
        if (dailySection) {
            dailySection.style.display = matchedInDaily > 0 ? '' : 'none';
        }

        // Show/hide no results box
        if (noResults) {
            if (matchedCount === 0) {
                noResults.classList.remove('hidden');
                noResults.classList.add('flex');
            } else {
                noResults.classList.add('hidden');
                noResults.classList.remove('flex');
            }
        }

        if (shouldScroll) {
            const target = resultsBanner || shoesSection;
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    window.clearSearch = function() {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        const url = new URL(window.location);
        url.searchParams.delete('q');
        window.history.replaceState({}, '', url.pathname);
        performSearch('', false);
    };

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            window.clearSearch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            performSearch(this.value, false);
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (window.location.pathname === '/' || window.location.pathname === '') {
                e.preventDefault();
                const q = searchInput ? searchInput.value : '';
                performSearch(q, true);
                const url = new URL(window.location);
                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }
                window.history.replaceState({}, '', url.toString());
            }
        });
    }

    // Brand filter tab buttons inside Shoes & Sneakers section
    window.filterShoeBrand = function(brand) {
        document.querySelectorAll('.brand-tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-black', 'shadow-2xs');
            btn.classList.add('text-gray-600');
        });
        const activeBtn = document.getElementById('tab-brand-' + brand);
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'text-black', 'shadow-2xs');
            activeBtn.classList.remove('text-gray-600');
        }

        const shoeCards = document.querySelectorAll('#shoes-grid .product-card');
        shoeCards.forEach(card => {
            const cardBrand = card.getAttribute('data-brand');
            if (brand === 'all' || cardBrand === brand) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    };

    // Check if URL has q param on initial load
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q');
    if (initialQuery) {
        if (searchInput) searchInput.value = initialQuery;
        performSearch(initialQuery, true);
    }
});
</script>
@endpush
