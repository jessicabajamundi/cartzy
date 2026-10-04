<header class="sticky top-0 z-50 bg-white border-b border-[#E1DDE7] shadow-sm w-full">
    <!-- Main Navigation & Search Bar -->
    <div class="w-full px-4 sm:px-6 lg:px-10 py-2.5">
        <div class="flex items-center justify-between gap-4 lg:gap-8">
            
            <!-- Brand Logo -->
            <a href="/" class="flex items-center shrink-0 group" title="cartzy">
                <img src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy" class="h-8.5 sm:h-9.5 w-auto object-contain group-hover:scale-105 transition-transform duration-200">
            </a>

            <!-- Search Area -->
            <div class="flex-1 mx-2 sm:mx-4 flex justify-center">
                <form id="header-search-form" action="{{ route('home') }}" method="GET" class="relative flex items-center w-full max-w-[650px] border border-[#E1DDE7] rounded-lg overflow-hidden bg-white shadow-sm focus-within:ring-2 focus-within:ring-[#A8A0B2] focus-within:border-[#6F6382] transition">
                    <input 
                        type="text" 
                        id="header-search-input"
                        name="q" 
                        value="{{ request('q') }}"
                        placeholder="Search products, brands, categories..." 
                        autocomplete="off"
                        class="w-full px-4 py-2.5 text-sm text-[#191421] placeholder-gray-400 focus:outline-none bg-transparent"
                    >
                    <button 
                        type="button" 
                        id="header-search-clear"
                        class="hidden text-gray-400 hover:text-[#282133] mr-1.5 text-base leading-none font-bold px-1 py-0.5 rounded transition"
                        title="Clear search"
                    >&times;</button>
                    <button 
                        type="submit" 
                        id="header-search-btn"
                        class="bg-[#6F6382] hover:bg-[#564B68] text-white w-11 h-9 flex items-center justify-center transition-colors shrink-0 m-0.5 rounded-md"
                        aria-label="Search"
                    >
                        <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>


            <!-- Action Links: Login, Sign up, Notifications, Cart -->
            <div class="flex items-center gap-1 shrink-0">
                
                @guest
                    <div class="flex items-center gap-3 mr-1">
                        <!-- Login Link -->
                        <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-gray-700 hover:text-[#564B68] transition group px-2.5 py-1.5 rounded-md hover:bg-[#F6F3F7]">
                            <svg class="w-4.5 h-4.5 shrink-0" style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9A3.75 3.75 0 1 1 8.25 9a3.75 3.75 0 0 1 7.5 0ZM3 20.25a9 9 0 0 1 18 0"/>
                            </svg>
                            <span class="text-xs font-semibold">Login</span>
                        </a>

                        <!-- Sign up Link -->
                        <a href="{{ route('register') }}" class="flex items-center gap-1.5 text-white bg-[#6F6382] hover:bg-[#564B68] transition px-3.5 py-1.5 rounded-md text-xs font-semibold">
                            <svg class="shrink-0" style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Sign up
                        </a>
                    </div>
                @endguest

                {{-- Icons Group --}}
                <div class="flex items-center gap-0.5">

                @auth
                    {{-- Profile Avatar --}}
                    <div class="relative group">
                        <button class="flex items-center justify-center w-8.5 h-8.5 rounded-full bg-[#6F6382] text-white hover:bg-[#564B68] transition font-bold text-xs" aria-label="Profile" title="Profile">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </button>
                        
                        <div class="hidden group-hover:block absolute right-0 top-full pt-2 w-56 z-50">
                            <div class="bg-white text-gray-800 rounded-lg shadow-xl border border-[#E1DDE7] py-1 text-xs divide-y divide-gray-100">
                                <div class="px-4 py-2.5 bg-[#FAF9FB]">
                                    <p class="font-bold text-[#282133] truncate text-sm">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-[#6F6382] capitalize mt-0.5">{{ Auth::user()->role }}</p>
                                </div>
                                <div class="py-1">
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">
                                            <svg class="w-3.5 h-3.5 text-indigo-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            <span>Admin Dashboard</span>
                                        </a>
                                    @elseif(Auth::user()->isSeller())
                                        <a href="{{ route('seller.dashboard') }}" class="flex items-center px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">
                                            <svg class="w-3.5 h-3.5 text-[#6F6382] mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span>Seller Centre</span>
                                        </a>
                                    @elseif(Auth::user()->isCourier())
                                        <a href="{{ route('courier.dashboard') }}" class="flex items-center px-4 py-2 hover:bg-[#FAF9FB] font-bold text-[#564B68]">
                                            <svg class="w-3.5 h-3.5 text-[#1A6FA8] mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
                                            <span>Rider Hub</span>
                                        </a>
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
                <button type="button" class="relative flex items-center justify-center w-8.5 h-8.5 rounded-full text-gray-500 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Notifications" title="Notifications">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                    <span class="absolute top-0.5 right-0.5 bg-[#6F6382] text-white text-[8px] font-bold min-w-[14px] min-h-[14px] rounded-full flex items-center justify-center px-0.5">3</span>
                </button>

                {{-- Mail --}}
                <button type="button" class="relative flex items-center justify-center w-8.5 h-8.5 rounded-full text-gray-500 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Messages" title="Messages">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                    </svg>
                </button>

                {{-- Customer Service --}}
                <a href="#" class="relative flex items-center justify-center w-8.5 h-8.5 rounded-full text-gray-500 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Customer Service" title="Customer Service">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                    </svg>
                </a>

                {{-- Wishlist --}}
                <a href="#" class="relative flex items-center justify-center w-8.5 h-8.5 rounded-full text-gray-500 hover:bg-[#F6F3F7] hover:text-[#564B68] transition" aria-label="Wishlist" title="Wishlist">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                    </svg>
                </a>

                {{-- Cart --}}
                <div class="relative group">
                    <a href="{{ route('cart.index') }}" class="flex items-center justify-center w-8.5 h-8.5 rounded-full text-gray-600 hover:bg-[#F6F3F7] hover:text-[#564B68] transition relative" aria-label="Cart" title="Cart">
                        <div class="relative">
                            <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                            </svg>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="absolute -top-1.5 -right-2 bg-[#6F6382] text-white font-bold text-[9px] w-3.5 h-3.5 rounded-full flex items-center justify-center cart-badge-count shadow-sm">
                                    {{ $cartCount }}
                                </span>
                            @else
                                <span class="absolute -top-1.5 -right-2 bg-[#6F6382] text-white font-bold text-[9px] w-3.5 h-3.5 rounded-full items-center justify-center cart-badge-count hidden shadow-sm">
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
                                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>ID Verification Required to Checkout</span>
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
                                        <div class="w-14 h-14 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mb-2">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-900">Your Cart is Empty</h4>
                                        <p class="text-xs text-gray-500 mt-1 max-w-xs">No items added to your cart yet.</p>
                                        <a href="/" class="mt-4 bg-black hover:bg-gray-800 text-white text-xs font-semibold px-5 py-2 rounded-md shadow-sm transition">
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
