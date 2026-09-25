<header class="sticky top-0 z-50 bg-white border-b border-[#E1DDE7] shadow-xs w-full">
    <!-- Main Edge-to-Edge Navigation & Full Width Search Bar -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-3.5">
        <div class="flex items-center justify-between gap-6 lg:gap-10">
            
            <!-- Brand Logo -->
            <a href="/" class="flex items-center shrink-0 group py-1" title="cartzy">
                <img src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy" class="h-10 sm:h-12 w-auto object-contain group-hover:scale-105 transition-transform duration-200">
            </a>

            <!-- Search Area -->
            <div class="flex-1 mx-2 sm:mx-6 flex justify-center">
                <form id="header-search-form" action="{{ route('home') }}" method="GET" class="relative flex items-center w-full max-w-[620px] border border-[#E1DDE7] rounded-xl overflow-hidden bg-white shadow-2xs focus-within:ring-2 focus-within:ring-[#A8A0B2] focus-within:border-[#6F6382] transition">
                    <input 
                        type="text" 
                        id="header-search-input"
                        name="q" 
                        value="{{ request('q') }}"
                        placeholder="Search for Nike, Adidas, sneakers, watches, electronics..." 
                        autocomplete="off"
                        class="w-full px-4 py-2.5 text-sm text-[#191421] placeholder-gray-400 focus:outline-none bg-transparent"
                    >
                    <button 
                        type="button" 
                        id="header-search-clear"
                        class="hidden text-gray-400 hover:text-[#282133] mr-2 text-lg leading-none font-bold px-1 py-0.5 rounded transition"
                        title="Clear search"
                    >&times;</button>
                    <button 
                        type="submit" 
                        id="header-search-btn"
                        class="bg-[#6F6382] hover:bg-[#564B68] text-white w-14 h-10 flex items-center justify-center transition-colors shrink-0 m-0.5 rounded-r-[10px]"
                        aria-label="Search"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>


            <!-- Action Links: Login, Sign up, Notifications, Cart -->
            <div class="flex items-center gap-5 shrink-0">
                
                @guest
                    <div class="flex items-center gap-4">
                        <!-- 1. Login Link -->
                        <a href="{{ route('login') }}" class="flex items-center gap-2 text-gray-900 hover:text-black transition group">
                            <svg class="w-6 h-6 text-gray-900 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Login</span>
                        </a>

                        <!-- 2. Sign up Link -->
                        <a href="{{ route('register') }}" class="flex items-center gap-2 text-gray-900 hover:text-black transition group">
                            <svg class="w-6 h-6 text-gray-900 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Sign up</span>
                        </a>
                    </div>
                @endguest

                {{-- 6 Icons Group (always visible when authenticated) --}}
                <div class="flex items-center">

                @auth
                    {{-- Profile --}}
                    <div class="relative group">
                        <button class="flex items-center justify-center w-9 h-9 rounded-full bg-[#6F6382] text-white hover:bg-[#564B68] transition shadow-xs font-bold text-sm" aria-label="Profile" title="Profile">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </button>
                        
                        <div class="hidden group-hover:block absolute right-0 top-full pt-2 w-56 z-50">
                            <div class="bg-white text-gray-800 rounded-lg shadow-xl border border-[#E1DDE7] py-1 text-xs divide-y divide-gray-100">
                                <div class="px-4 py-3 bg-[#FAF9FB]">
                                    <p class="font-bold text-[#282133] truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-[#6F6382] capitalize">Role: {{ Auth::user()->role }}</p>
                                </div>
                                <div class="py-1">
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">🛡️ Admin Dashboard</a>
                                    @elseif(Auth::user()->isSeller())
                                        <a href="{{ route('seller.dashboard') }}" class="block px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">🏬 Seller Centre</a>
                                    @elseif(Auth::user()->isCourier())
                                        <a href="{{ route('courier.dashboard') }}" class="block px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">🛵 Rider Hub</a>
                                    @endif
                                    <a href="{{ route('account.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700 font-medium">My Account</a>
                                    <a href="{{ route('account.purchases') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700 font-medium">My Purchase</a>
                                </div>
                                <div class="py-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700 font-medium">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Notification --}}
                <button type="button" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-700 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Notifications" title="Notifications">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
                    </svg>
                    <span class="absolute -top-0.5 -right-0.5 bg-[#6F6382] text-white text-[9px] font-bold min-w-[16px] min-h-[16px] rounded-full flex items-center justify-center shadow-xs px-0.5">3</span>
                </button>

                {{-- Mail --}}
                <button type="button" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-700 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Messages" title="Messages">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </button>

                {{-- Customer Service --}}
                <a href="#" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-700 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Customer Service" title="Customer Service">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M6.343 17.657a9 9 0 010-12.728M9.172 15.536a5 5 0 010-7.072M12 12h.01M3 10a9 9 0 0118 0v1a2 2 0 01-2 2h-1a2 2 0 01-2-2v-1a2 2 0 012-2h.93A7.001 7.001 0 005.07 8H6a2 2 0 012 2v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1z"></path>
                    </svg>
                </a>

                {{-- Wishlist --}}
                <a href="#" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-700 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Wishlist" title="Wishlist">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </a>

                {{-- Cart --}}
                <div class="relative group">
                    <a href="{{ route('cart.index') }}" class="flex items-center justify-center w-9 h-9 rounded-full text-gray-900 hover:bg-[#F6F3F7] hover:text-[#564B68] transition relative" aria-label="Cart" title="Cart">
                        <div class="relative">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="absolute -top-1.5 -right-2 bg-[#6F6382] text-white font-bold text-[10px] w-4 h-4 rounded-full flex items-center justify-center cart-badge-count shadow-xs">
                                    {{ $cartCount }}
                                </span>
                            @else
                                <span class="absolute -top-1.5 -right-2 bg-[#6F6382] text-white font-bold text-[10px] w-4 h-4 rounded-full items-center justify-center cart-badge-count hidden shadow-xs">
                                    0
                                </span>
                            @endif
                        </div>
                    </a>

                    <!-- Cart Preview Popover -->
                    <div class="hidden group-hover:block absolute right-0 top-full pt-2 w-80 sm:w-96 z-50">
                        <div id="cart-popover-box" class="bg-white text-gray-800 rounded-lg shadow-2xl border border-gray-200 overflow-hidden">
                            @auth
                                @if(isset($cartItems) && count($cartItems) > 0)
                                    @auth
                                        @if(!Auth::user()->isIdVerified())
                                            <div class="px-3 py-2 bg-amber-50 border-b border-amber-200 text-[11px] text-amber-800 font-bold flex items-center justify-between">
                                                <span class="flex items-center gap-1.5">
                                                    <span>⚠️</span> ID Verification Required to Checkout
                                                </span>
                                                <a href="{{ route('account.index', ['tab' => 'profile']) }}" class="underline hover:text-amber-950 font-black">Verify Now</a>
                                            </div>
                                        @endif
                                    @endauth
                                    <div class="p-3 bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Cart ({{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }})
                                    </div>
                                    <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">
                                        @foreach($cartItems as $item)
                                            <div class="p-3 hover:bg-gray-50 flex items-center gap-3 transition">
                                                <img src="{{ $item['image'] ?? 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=100&h=100&fit=crop&q=80' }}" alt="Item" class="w-12 h-12 object-cover rounded border border-gray-200 shrink-0">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                                                    <p class="text-[11px] text-gray-500">Qty: {{ $item['quantity'] }} · Variation: {{ $item['variation'] ?? 'Standard' }}</p>
                                                    <span class="text-xs font-bold text-black">₱{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="p-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-2">
                                        <a href="{{ route('cart.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-900 text-xs font-semibold px-3 py-2 rounded transition border border-gray-200">
                                            View Cart
                                        </a>
                                        <a href="{{ route('checkout') }}" class="flex-1 text-center bg-black hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded transition flex items-center justify-center gap-1">
                                            <span>Checkout</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="p-8 text-center flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 text-gray-800 rounded-full flex items-center justify-center text-2xl mb-2">
                                            🛒
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-900">Your Cart is Empty</h4>
                                        <p class="text-xs text-gray-500 mt-1 max-w-xs">No items added to your cart yet.</p>
                                        <a href="/" class="mt-4 bg-black hover:bg-gray-800 text-white text-xs font-semibold px-5 py-2 rounded-md shadow-xs transition">
                                            Shop Now
                                        </a>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
                </div>

            </div>

        </div>
    </div>
</header>
