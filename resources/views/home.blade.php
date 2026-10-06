@extends('layouts.app')

@section('title', 'cartzy')

@section('content')
<div class="w-full px-3 sm:px-6 lg:px-8 py-6 space-y-8">


    <!-- Original editorial collection carousel -->
    @include('partials.hero-carousel')

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
            <a href="#all-categories" onclick="if(window.toggleCategoryMenu){ toggleCategoryMenu(); return false; }" class="text-xs sm:text-sm font-bold text-gray-700 hover:text-black hover:underline flex items-center gap-1 group">
                <span>See All Categories</span>
                <span class="group-hover:translate-x-0.5 transition-transform font-mono">&rarr;</span>
            </a>
        </div>

        <!-- 8-Column Grid: Popular Categories -->
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 sm:gap-4 w-full">

            <!-- Category 1: Mobiles & Gadgets -->
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('cell-phones-accessories', 'Cell Phones & Accessories')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('men-clothing', 'Men Clothing')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('women-clothing', 'Women Clothing')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('shoes', 'Shoes')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('beauty-health', 'Beauty & Health')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('home-living', 'Home & Living')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('electronics', 'Electronics')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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
            <a href="javascript:void(0)" onclick="if(window.filterByCategory) filterByCategory('electronics', 'Electronics')" class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-gray-50 transition-all">
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

    <!-- 3. Today's Picks — Editorial Curation Strip -->
    <section id="flash-sale" class="w-full scroll-mt-24">
        <!-- Two-Row Layout: Featured Hero + 4 Compact Picks -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            <!-- LEFT: Featured Product (Large Editorial Card spanning 2 cols) -->
            <div class="lg:col-span-2 relative rounded-2xl overflow-hidden bg-[#F7F5F2] border border-[#E8E4DF] group cursor-pointer hover:shadow-lg transition-shadow">
                <div class="relative aspect-[4/5] overflow-hidden">
                    <img 
                        src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=1000&fit=crop&q=85" 
                        alt="Premium Minimalist Watch" 
                        class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-700 ease-out"
                    >
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                    
                    <!-- Badge -->
                    <div class="absolute top-4 left-4">
                        <span class="inline-flex items-center gap-1.5 bg-white/95 backdrop-blur-sm text-[11px] font-bold text-gray-900 px-3 py-1.5 rounded-lg shadow-sm tracking-wide">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            EDITOR'S PICK
                        </span>
                    </div>
                    
                    <!-- Content Overlay -->
                    <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-6 text-white">
                        <p class="text-[11px] font-medium uppercase tracking-widest text-white/70 mb-1.5">Featured · Accessories</p>
                        <h3 class="text-lg sm:text-xl font-bold leading-snug mb-2">Classic Analog Timepiece — Sapphire Edition</h3>
                        <div class="flex items-baseline gap-2.5 mb-3">
                            <span class="text-xl font-black">₱2,490</span>
                            <span class="text-sm text-white/50 line-through">₱4,990</span>
                            <span class="text-[11px] font-bold bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded-md">Save 50%</span>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] text-white/60">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                4.8 (342 reviews)
                            </span>
                            <span>·</span>
                            <span>Free shipping</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: 4 Compact Product Cards in 2×2 Grid (spanning 3 cols) -->
            <div class="lg:col-span-3 grid grid-cols-2 gap-4">

                <!-- Pick 1 -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden group cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all">
                    <div class="relative aspect-square overflow-hidden bg-[#FAFAFA]">
                        <img 
                            src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&h=600&fit=crop&q=80" 
                            alt="Wireless Over-Ear Headphones" 
                            class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
                        >
                        <span class="absolute top-3 left-3 bg-[#282133] text-white text-[10px] font-bold px-2 py-1 rounded-md tracking-wide">STAFF PICK</span>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Audio · Electronics</p>
                        <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 mb-2">Studio-Grade Wireless Headphones</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base font-black text-gray-900">₱1,790</span>
                            <span class="text-xs text-gray-400 line-through">₱3,500</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-[10px] text-gray-400">
                            <svg class="w-3 h-3 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="font-semibold text-gray-500">4.9</span>
                            <span>· 128 sold</span>
                        </div>
                    </div>
                </div>

                <!-- Pick 2 -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden group cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all">
                    <div class="relative aspect-square overflow-hidden bg-[#FAFAFA]">
                        <img 
                            src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600&h=600&fit=crop&q=80" 
                            alt="Nike Running Shoes" 
                            class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
                        >
                        <span class="absolute top-3 left-3 bg-[#282133] text-white text-[10px] font-bold px-2 py-1 rounded-md tracking-wide">TRENDING</span>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Footwear · Running</p>
                        <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 mb-2">Ultralight Performance Runners</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base font-black text-gray-900">₱3,290</span>
                            <span class="text-xs text-gray-400 line-through">₱5,990</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-[10px] text-gray-400">
                            <svg class="w-3 h-3 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="font-semibold text-gray-500">4.7</span>
                            <span>· 89 sold</span>
                        </div>
                    </div>
                </div>

                <!-- Pick 3 -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden group cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all">
                    <div class="relative aspect-square overflow-hidden bg-[#FAFAFA]">
                        <img 
                            src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=600&h=600&fit=crop&q=80" 
                            alt="Premium Skincare Set" 
                            class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
                        >
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Beauty · Skincare</p>
                        <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 mb-2">Vitamin C Brightening Essentials Kit</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base font-black text-gray-900">₱899</span>
                            <span class="text-xs text-gray-400 line-through">₱1,650</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-[10px] text-gray-400">
                            <svg class="w-3 h-3 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="font-semibold text-gray-500">4.6</span>
                            <span>· 256 sold</span>
                        </div>
                    </div>
                </div>

                <!-- Pick 4 -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden group cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all">
                    <div class="relative aspect-square overflow-hidden bg-[#FAFAFA]">
                        <img 
                            src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600&h=600&fit=crop&q=80" 
                            alt="Wireless Earbuds" 
                            class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
                        >
                        <span class="absolute top-3 left-3 bg-[#282133] text-white text-[10px] font-bold px-2 py-1 rounded-md tracking-wide">BEST VALUE</span>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Audio · Wireless</p>
                        <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 mb-2">Active Noise-Cancelling Earbuds Pro</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base font-black text-gray-900">₱1,290</span>
                            <span class="text-xs text-gray-400 line-through">₱2,800</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-[10px] text-gray-400">
                            <svg class="w-3 h-3 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="font-semibold text-gray-500">4.8</span>
                            <span>· 415 sold</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section Header Strip (below the grid, minimal) -->
        <div class="flex items-center justify-between mt-4 px-1">
            <div class="flex items-center gap-3">
                <h2 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Today's Picks</h2>
                <span class="text-[10px] font-semibold text-gray-400 border border-gray-200 px-2 py-0.5 rounded-md">Curated daily</span>
            </div>
            <a href="#" class="text-xs font-bold text-gray-500 hover:text-gray-900 transition flex items-center gap-1">
                View all recommendations
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>



    
    <!-- Active Category Announcement Bar (Dynamically shown when category is filtered) -->
    <div id="category-results-banner" class="hidden bg-[#282133] text-white rounded-2xl p-4 sm:p-5 shadow-sm border border-[#564B68] flex flex-wrap items-center justify-between gap-3 w-full transition-all">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-[#A8A0B2] shrink-0 border border-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                </svg>
            </span>
            <div>
                <p class="text-[11px] text-[#C9C3D3] font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span>Department Filter</span>
                    <span class="w-1 h-1 rounded-full bg-[#A8A0B2]"></span>
                    <span>Database Synchronized</span>
                </p>
                <h3 class="text-sm sm:text-base font-extrabold text-white">
                    Showing items in <span id="category-banner-name" class="text-[#A8A0B2] font-black underline underline-offset-4"></span> (<span id="category-banner-count" class="font-mono text-emerald-400 font-bold">0</span> products)
                </h3>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="clearCategoryFilter()" class="bg-[#FAF9FB] hover:bg-white text-[#282133] text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <span>✕</span>
                <span>Clear Category Filter</span>
            </button>
        </div>
    </div>

    <!-- Quick Category Filter Bar for Catalog -->
    <div class="bg-white p-3 rounded-2xl border border-[#E1DDE7] shadow-2xs flex items-center justify-between gap-3 overflow-x-auto no-scrollbar w-full">
        <div class="flex items-center gap-2 shrink-0 text-xs font-bold text-[#564B68]">
            <svg class="w-4 h-4 text-[#6F6382]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.539.092 1.002.434 1.258.924.321.616.486 1.309.486 2.016 0 2.257-.96 4.31-2.525 5.75L15 15.75V21l-6-3v-2.25l-4.293-4.293A7.957 7.957 0 0 1 2.173 5.75c0-.707.165-1.4.486-2.016.256-.49.719-.832 1.258-.924A48.27 48.27 0 0 1 12 3Z"/></svg>
            <span class="hidden sm:inline">Department:</span>
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button type="button" onclick="clearCategoryFilter()" id="catalog-pill-all" class="catalog-cat-pill px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer whitespace-nowrap bg-[#6F6382] text-white">
                All Items
            </button>
            @foreach($categories as $cat)
                <button 
                    type="button" 
                    onclick="filterByCategory('{{ $cat->slug }}', '{{ addslashes($cat->name) }}')" 
                    id="catalog-pill-{{ $cat->slug }}" 
                    class="catalog-cat-pill px-3 py-1.5 rounded-xl text-xs font-medium transition hover:bg-[#F1EFF5] border border-transparent hover:border-[#E1DDE7] cursor-pointer whitespace-nowrap bg-white text-gray-700"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </div>

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
                <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories sports-outdoors"
                 data-name="smart fitness tracker watch with blood oxygen heart rate monitor ip68">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="jewelry-accessories"
                 data-name="minimalist matte chronograph watch waterproof luxury unisex">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories"
                 data-name="anc pro wireless noise cancelling earphones deep bass bluetooth 5.3">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="shoes sports-outdoors"
                 data-name="retro colorblock sneaker lightweight running walking breathable shoes">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="ceramic aesthetic coffee mug saucer set luxury minimalist cup">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics"
                 data-name="instant print camera retro vintage pocket edition photo film">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beauty-health"
                 data-name="korean skincare set vitamin c serum moisturizer whitening glow kit">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics office-school-supplies"
                 data-name="gaming laptop 16-inch rtx 4060 144hz fhd display ultra performance">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living appliances"
                 data-name="non-stick cookware set 6-piece granite frying pan kitchen cooking">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="bags-luggage women-clothing"
                 data-name="leather crossbody sling bag women premium anti-scratch stylish">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="men-clothing"
                 data-name="men's oversized graphic streetwear t-shirt cotton unisex drop shoulder">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="modern 3-seater sofa scandinavian living room furniture velvet upholstery">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics cell-phones-accessories sports-outdoors"
                 data-name="portable waterproof bluetooth speaker 360 surround bass 24h playtime outdoor">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="jewelry-accessories beachwear"
                 data-name="polarized uv400 sunglasses men women classic retro driving shades">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living sports-outdoors"
                 data-name="vacuum insulated stainless steel tumbler 32oz flask double wall leakproof">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="electronics office-school-supplies"
                 data-name="rgb mechanical gaming keyboard hot-swappable blue switch wireless ergonomic">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beauty-health"
                 data-name="luxury eau de parfum 100ml long lasting fresh floral woody fragrance unisex">
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
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-living"
                 data-name="minimalist nordic table lamp warm led ambient light bedside nightstand">
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
                        <button onclick="addToCart(18,'Minimalist Nordic LED Table Lamp',899,'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&h=500&fit=crop&q=80','White')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 19 (Women Clothing) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="women-clothing"
                 data-name="women's floral summer bohemian midi dress puff sleeve elegant chic">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Women's Floral Summer Bohemian Midi Dress Puff Sleeve Elegant Chic
                        </h3>
                        <div class="flex items-center gap-1 mt-1.5"><span class="text-[10px] text-[#564B68] bg-[#F1EFF5] border border-[#E1DDE7] font-medium px-1.5 py-0.5 rounded">Free Shipping</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">489</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>3.8k sold</span>
                        </div>
                        <button onclick="addToCart(19,'Women Floral Summer Bohemian Midi Dress',489,'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80','Floral / Medium')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 20 (Beachwear) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="beachwear"
                 data-name="quick-dry beach swim shorts cover-up tropical vacation swimwear">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-40%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Quick-Dry Beach Swim Shorts &amp; Sun Cover-Up Tropical Vacation Set
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">399</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.4k sold</span>
                        </div>
                        <button onclick="addToCart(20,'Quick-Dry Beach Tropical Swim Set',399,'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&h=500&fit=crop&q=80','Ocean Blue / L')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 21 (Kids) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="kids"
                 data-name="kids dinosaur 2-piece cotton adventure t-shirt shorts set children">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Kids Dinosaur 2-Piece Breathable Cotton Playset T-Shirt &amp; Shorts
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">349</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>2.2k sold</span>
                        </div>
                        <button onclick="addToCart(21,'Kids Dinosaur 2-Piece Cotton Playset',349,'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=500&h=500&fit=crop&q=80','Age 4-6 / Green')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 22 (Curve) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="curve women-clothing"
                 data-name="curve plus-size flattering wrap dress stretch waistline elegant party">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Curve Plus-Size Flattering Silhouette Wrap Dress Stretch Waistline
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">650</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.5k sold</span>
                        </div>
                        <button onclick="addToCart(22,'Curve Plus-Size Flattering Wrap Dress',650,'https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80','2XL / Plum')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 23 (Underwear & Sleepwear) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="underwear-sleepwear"
                 data-name="mulberry silk sleepwear pajama loungewear set luxury smooth">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-35%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Mulberry Silk Sleepwear 2-Piece Pajama Set Ultra-Soft Loungewear
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">790</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>2.4k sold</span>
                        </div>
                        <button onclick="addToCart(23,'Mulberry Silk Sleepwear Pajama Set',790,'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=500&h=500&fit=crop&q=80','Champagne / M')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 24 (Baby & Maternity) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="baby-maternity"
                 data-name="organic bamboo soft baby swaddle blankets newborn wrap nursery">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Soft Organic Bamboo Baby Swaddle Blankets 3-Pack Breathable Nursery
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">420</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">5.0</span></div>
                            <span>1.7k sold</span>
                        </div>
                        <button onclick="addToCart(24,'Organic Bamboo Baby Swaddles 3pk',420,'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&q=80','Pastel Trio')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 25 (Home Textiles) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="home-textiles home-living"
                 data-name="100% egyptian cotton luxury 4-piece bedding sheet set queen duvet">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-42%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            100% Egyptian Cotton Luxury 4-Piece Bedding Sheet Set Queen Soft
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">1,250</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>1.3k sold</span>
                        </div>
                        <button onclick="addToCart(25,'Egyptian Cotton 4pc Bedding Set',1250,'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=500&fit=crop&q=80','Queen / Slate Grey')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 26 (Tools & Home Improvement) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="tools-home-improvement"
                 data-name="48-in-1 cordless precision electric screwdriver kit repair tool">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-25%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            48-in-1 Cordless Precision Electric Screwdriver Kit Rechargeable DIY
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">850</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>890 sold</span>
                        </div>
                        <button onclick="addToCart(26,'48-in-1 Cordless Precision Screwdriver Kit',850,'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=500&h=500&fit=crop&q=80','48-pc Set')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 27 (Toys & Games) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="toys-games kids"
                 data-name="stem magnetic educational building blocks set 3d geometric toy">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Preferred</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-30%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            STEM Magnetic Educational Building Blocks Set 3D Geometric Tiles
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">599</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>1.8k sold</span>
                        </div>
                        <button onclick="addToCart(27,'STEM Magnetic Building Blocks Set',599,'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=500&h=500&fit=crop&q=80','64-Piece')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 28 (Pet Supplies) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="pet-supplies"
                 data-name="self-cleaning pet grooming slicker brush for dogs cats shedding">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Self-Cleaning Pet Grooming Slicker Brush for Dogs &amp; Cats Deshedding
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">249</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>3.1k sold</span>
                        </div>
                        <button onclick="addToCart(28,'Self-Cleaning Pet Slicker Brush',249,'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&h=500&fit=crop&q=80','Pastel Purple')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 29 (Appliances) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="appliances home-living"
                 data-name="touchscreen digital air fryer 4.5l oil-less rapid heat kitchen appliance">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-38%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Touchscreen Digital Air Fryer 4.5L Oil-Free Rapid Heat 8 Presets
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">2,199</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.6k sold</span>
                        </div>
                        <button onclick="addToCart(29,'Touchscreen Digital Air Fryer 4.5L',2199,'https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80','Matte Black')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 30 (Office & School Supplies) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="office-school-supplies"
                 data-name="aesthetic pastel gel pens set 12-pack quick-dry 0.5mm stationery">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-20%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            Aesthetic Pastel Gel Pen Set 12-Pack Smooth Quick-Dry 0.5mm Fine Tip
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">180</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.9</span></div>
                            <span>5.2k sold</span>
                        </div>
                        <button onclick="addToCart(30,'Aesthetic Pastel Gel Pens 12pk',180,'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&h=500&fit=crop&q=80','Pastel Rainbow')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 31 (Automotive) -->
            <div class="product-card bg-white rounded-xl shadow-2xs hover:shadow-xl border border-[#E1DDE7] overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1"
                 data-category="automotive"
                 data-name="high-power cordless handheld car vacuum cleaner 9000pa wireless auto">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop&q=80" alt="Product" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 left-2"><span class="bg-[#282133] text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-2xs">Official</span></div>
                    <span class="absolute top-2 right-2 bg-[#6F6382] text-white font-bold text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded shadow-2xs">-45%</span>
                </div>
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-relaxed group-hover:text-black transition">
                            High-Power Cordless Handheld Car Vacuum Cleaner 9000Pa Wireless Auto
                        </h3>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-gray-100">
                        <div class="flex items-baseline gap-1"><span class="text-xs font-bold text-[#6F6382]">₱</span><span class="text-base sm:text-lg font-black text-gray-900">750</span></div>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                            <div class="flex items-center gap-0.5 text-gray-800 font-semibold"><span class="text-amber-400">★</span> <span class="text-gray-600 font-medium">4.8</span></div>
                            <span>1.9k sold</span>
                        </div>
                        <button onclick="addToCart(31,'Cordless Handheld Car Vacuum 9000Pa',750,'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop&q=80','Black / Wireless')" class="mt-2 w-full bg-[#6F6382] hover:bg-[#564B68] active:bg-[#3E354C] text-white text-[11px] font-bold py-1.5 rounded-lg transition-all duration-150 shadow-xs hover:shadow flex items-center justify-center gap-1.5 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</button>
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
<script src="{{ asset('js/home.js') }}"></script>
<script src="{{ asset('js/hero-carousel.js') }}"></script>
@endpush
