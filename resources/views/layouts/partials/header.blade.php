<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E1DDE7]/80 shadow-[0_2px_15px_-3px_rgba(40,33,51,0.05)] w-full transition-all">
    <!-- Main Navigation & Search Bar -->
    <div class="w-full px-4 sm:px-6 lg:px-10 py-2 sm:py-2.5">
        <div class="flex items-center justify-between gap-4 lg:gap-8">
            
            <!-- Brand Logo -->
            <a href="/" class="flex items-center shrink-0 group py-0.5" title="cartzy">
                <img src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy" class="h-8.5 sm:h-9.5 w-auto object-contain group-hover:scale-105 transition-transform duration-200">
            </a>

            <!-- Search Area -->
            <div class="flex-1 mx-3 sm:mx-6 flex justify-center max-w-[650px]">
                <form id="header-search-form" action="{{ route('home') }}" method="GET" class="group relative flex items-center w-full bg-[#FAF9FB] hover:bg-[#F5F2F7] focus-within:!bg-white border border-[#E1DDE7] focus-within:border-[#C08B7F] rounded-full p-1 pl-4.5 shadow-[0_1px_3px_rgba(40,33,51,0.03)] focus-within:shadow-[0_4px_20px_rgba(111,99,130,0.12)] focus-within:ring-3 focus-within:ring-[#C08B7F]/20 transition-all duration-200">
                    <span class="text-gray-400 group-focus-within:text-[#6F6382] mr-2 shrink-0 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="header-search-input"
                        name="q" 
                        value="{{ request('q') }}"
                        placeholder="Search products, brands, categories..." 
                        autocomplete="off"
                        class="w-full py-1.5 text-xs sm:text-sm text-[#282133] placeholder-gray-400 focus:outline-none bg-transparent font-medium"
                    >
                    <button 
                        type="button" 
                        id="header-search-clear"
                        class="hidden text-gray-400 hover:text-[#282133] mr-1.5 text-base leading-none font-bold px-1.5 py-0.5 rounded-full hover:bg-gray-200/60 transition"
                        title="Clear search"
                    >&times;</button>
                    <button 
                        type="submit" 
                        id="header-search-btn"
                        class="bg-gradient-to-r from-[#564B68] via-[#6F6382] to-[#C08B7F] hover:from-[#433A52] hover:to-[#B07B6F] text-white w-9 h-8 sm:w-10 sm:h-8.5 flex items-center justify-center rounded-full shadow-sm hover:shadow-md transition-all duration-200 shrink-0 transform hover:scale-105 active:scale-95 cursor-pointer"
                        aria-label="Search"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>
            </div>


            <!-- Action Links: Login, Sign up, Notifications, Cart -->
            <div class="flex items-center gap-1 shrink-0">
                
                @guest
                    <div class="flex items-center gap-2 mr-1">
                        <!-- Login Link -->
                        <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-[#3E354C] hover:text-[#564B68] hover:bg-[#F4F1F7] transition px-3 py-1.5 rounded-full text-xs font-bold">
                            <svg class="w-4 h-4 shrink-0 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9A3.75 3.75 0 1 1 8.25 9a3.75 3.75 0 0 1 7.5 0ZM3 20.25a9 9 0 0 1 18 0"/>
                            </svg>
                            <span class="text-xs font-bold">Login</span>
                        </a>

                        <!-- Sign up Link -->
                        <a href="{{ route('register') }}" class="flex items-center gap-1.5 text-white bg-gradient-to-r from-[#564B68] to-[#C08B7F] hover:from-[#433A52] hover:to-[#B07B6F] shadow-sm hover:shadow-md transition-all duration-200 px-3.5 py-1.5 rounded-full text-xs font-bold tracking-wide transform hover:-translate-y-0.5 active:translate-y-0">
                            <svg class="shrink-0 w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span>Sign up</span>
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
                                    <a href="{{ route('buyer.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-[#564B68] font-bold">My Dashboard</a>
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

                {{-- Notifications Dropdown --}}
                <div class="relative" id="headerNotifDropdownContainer">
                    <button type="button" 
                            id="headerNotifBtn"
                            onclick="toggleHeaderNotifDropdown(event)"
                            class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-[#F3EFF7] hover:text-[#564B68] transition duration-150 cursor-pointer" 
                            aria-label="Notifications" 
                            title="Notifications">
                        <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span id="headerNotifBadge" class="{{ (isset($headerUnreadCount) && $headerUnreadCount > 0) ? '' : 'hidden' }} absolute top-0.5 right-0.5 bg-gradient-to-tr from-[#C08B7F] to-[#D4A69A] text-white text-[8px] font-black min-w-[15px] h-[15px] rounded-full flex items-center justify-center px-0.5 ring-2 ring-white shadow-xs">
                            {{ $headerUnreadCount ?? 0 }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown Popover -->
                    <div id="headerNotifDropdown" class="hidden absolute right-0 top-full pt-2 w-80 sm:w-96 z-50">
                        <div class="bg-white text-gray-800 rounded-2xl shadow-2xl border border-[#E1DDE7] overflow-hidden">
                            {{-- Header --}}
                            <div class="px-4 py-3 bg-[#FAF9FB] border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#282133] text-sm">Notifications</span>
                                    <span id="headerNotifPill" class="{{ (isset($headerUnreadCount) && $headerUnreadCount > 0) ? '' : 'hidden' }} text-[10px] font-bold bg-[#FFF5F3] text-[#8C5A50] px-2 py-0.5 rounded-full border border-[#C08B7F]/30">
                                        <span id="headerNotifPillCount">{{ $headerUnreadCount ?? 0 }}</span> unread
                                    </span>
                                </div>
                                @auth
                                    <button type="button" onclick="markAllNotificationsAsRead(event)" class="text-[11px] font-semibold text-[#8C5A50] hover:underline cursor-pointer">
                                        Mark all as read
                                    </button>
                                @endauth
                            </div>

                            {{-- Search bar: "search notifications....." --}}
                            <div class="p-2.5 border-b border-gray-100 bg-[#FAF9FB]/50">
                                <div class="relative">
                                    <input type="text" 
                                           id="headerNotifSearch" 
                                           placeholder="search notifications....." 
                                           oninput="filterHeaderNotifications(this.value)"
                                           class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-none focus:border-[#C08B7F] focus:ring-2 focus:ring-[#C08B7F]/20 transition placeholder:text-gray-400">
                                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Notification Items List --}}
                            <div id="headerNotifList" class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                                @auth
                                    @if(isset($headerNotifications) && count($headerNotifications) > 0)
                                        @foreach($headerNotifications as $notif)
                                            <a href="{{ $notif->link ?: route('buyer.dashboard', ['tab' => 'notifications']) }}" 
                                               onclick="markNotificationAsRead({{ $notif->id }}, this, event)"
                                               data-notif-id="{{ $notif->id }}"
                                               data-title="{{ strtolower($notif->title) }}"
                                               data-message="{{ strtolower($notif->message) }}"
                                               class="header-notif-item p-3.5 flex items-start gap-3 transition hover:bg-[#FAF9FB] cursor-pointer {{ !$notif->is_read ? 'bg-[#FFF5F3]/50' : '' }}">
                                                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm {{ match($notif->type) { 'delivery' => 'bg-emerald-100 text-emerald-700', 'payment' => 'bg-amber-100 text-amber-700', 'order' => 'bg-purple-100 text-purple-700', 'review' => 'bg-pink-100 text-pink-700', default => 'bg-gray-100 text-gray-700' } }}">
                                                    {{ match($notif->type) { 'delivery' => '🚚', 'payment' => '💳', 'order' => '📦', 'review' => '⭐', default => '🔔' } }}
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <h4 class="text-xs font-bold text-gray-900 truncate">{{ $notif->title }}</h4>
                                                        <span class="text-[10px] text-gray-400 shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-gray-600 line-clamp-2 mt-0.5">{{ $notif->message }}</p>
                                                </div>
                                                @if(!$notif->is_read)
                                                    <span class="w-2 h-2 rounded-full bg-[#C08B7F] shrink-0 mt-1.5 header-notif-dot"></span>
                                                @endif
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="p-8 text-center" id="headerNotifEmptyState">
                                            <div class="w-12 h-12 bg-[#F6F4F8] text-gray-400 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                                                🔔
                                            </div>
                                            <h4 class="text-xs font-bold text-gray-800">No notifications yet</h4>
                                            <p class="text-[11px] text-gray-500 mt-0.5">You'll receive notifications here when you receive order updates or activity on your account.</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="p-8 text-center">
                                        <div class="w-12 h-12 bg-[#F6F4F8] text-gray-400 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                                            🔔
                                        </div>
                                        <h4 class="text-xs font-bold text-gray-800">Sign in to view notifications</h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Stay updated on your orders and special offers.</p>
                                        <a href="{{ route('login') }}" class="mt-3 inline-block bg-[#564B68] text-white text-xs font-bold px-4 py-1.5 rounded-lg hover:bg-[#3E354C] transition">Sign in</a>
                                    </div>
                                @endauth
                                <div id="headerNotifNoMatch" class="hidden p-6 text-center text-xs text-gray-400 font-medium">
                                    No notifications matching your search.
                                </div>
                            </div>

                            {{-- Footer --}}
                            @auth
                                <div class="p-2.5 bg-[#FAF9FB] border-t border-gray-100 text-center">
                                    <a href="{{ route('buyer.dashboard', ['tab' => 'notifications']) }}" class="text-xs font-bold text-[#564B68] hover:text-[#3E354C] hover:underline transition">
                                        View all in Dashboard &rarr;
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>

                {{-- Mail --}}
                <button type="button" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-[#F3EFF7] hover:text-[#564B68] transition duration-150 cursor-pointer" aria-label="Messages" title="Messages">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                    </svg>
                </button>

                {{-- Customer Service --}}
                <a href="#" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-[#F3EFF7] hover:text-[#564B68] transition duration-150 cursor-pointer" aria-label="Customer Service" title="Customer Service">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                    </svg>
                </a>

                {{-- Wishlist --}}
                <a href="{{ route('buyer.dashboard', ['tab' => 'wishlist']) }}" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-[#F3EFF7] hover:text-[#564B68] transition duration-150 cursor-pointer" aria-label="Wishlist" title="Wishlist">
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                    </svg>
                </a>

                {{-- Cart --}}
                <div class="relative group">
                    <a href="{{ route('cart.index') }}" class="flex items-center justify-center w-9 h-9 rounded-full text-gray-600 hover:bg-[#F3EFF7] hover:text-[#564B68] transition duration-150 relative cursor-pointer" aria-label="Cart" title="Cart">
                        <div class="relative">
                            <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                            </svg>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="absolute -top-1.5 -right-2 bg-gradient-to-tr from-[#564B68] to-[#6F6382] text-white font-black text-[9px] min-w-[15px] h-[15px] rounded-full flex items-center justify-center cart-badge-count ring-2 ring-white shadow-xs">
                                    {{ $cartCount }}
                                </span>
                            @else
                                <span class="absolute -top-1.5 -right-2 bg-gradient-to-tr from-[#564B68] to-[#6F6382] text-white font-black text-[9px] min-w-[15px] h-[15px] rounded-full items-center justify-center cart-badge-count hidden ring-2 ring-white shadow-xs">
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

    <!-- Category Navigation Bar (Directly below Logo, Search, and Action Icons) -->
    <div class="w-full bg-[#FAF9FB] border-t border-[#EAE6F0] px-4 sm:px-6 lg:px-10 py-1.5 text-xs font-semibold relative">
        <div class="flex items-center justify-between gap-2">
            
            <!-- Left: Categories Dropdown Button & Mega Menu -->
            <div class="relative" id="category-mega-menu-wrapper">
                <button 
                    type="button" 
                    id="all-categories-btn"
                    onclick="toggleCategoryMenu()"
                    class="inline-flex items-center gap-2 text-[#282133] hover:text-[#564B68] px-3.5 py-1.5 rounded-full font-bold transition-all duration-150 bg-white hover:bg-[#F3EFF7] border border-[#E1DDE7] shadow-[0_1px_2px_rgba(40,33,51,0.04)] cursor-pointer select-none text-xs shrink-0 group"
                    aria-expanded="false"
                >
                    <svg class="w-3.5 h-3.5 text-[#6F6382] group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <span class="tracking-wide">Categories</span>
                    <svg id="cat-chevron" class="w-3 h-3 text-gray-400 group-hover:text-gray-700 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Category Mega Menu Popover with Side Navigation (SHEIN/Lazada Style) -->
                <div 
                    id="category-mega-dropdown" 
                    class="hidden absolute left-0 top-full mt-1.5 w-[94vw] sm:w-[720px] lg:w-[940px] bg-white rounded-2xl shadow-2xl border border-[#E1DDE7] z-50 p-4 sm:p-5 overflow-hidden transition-all duration-200 transform origin-top-left"
                >
                    <div class="flex flex-col md:flex-row gap-5">
                        <!-- Left Column: Featured Sections (Just for You, New In, Sale) with Chevron > and Divider -->
                        <div class="w-full md:w-52 shrink-0 md:border-r md:border-[#E1DDE7] md:pr-4 flex flex-col gap-1.5">
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 px-3 pb-1">Featured</div>
                            <a 
                                href="#daily-discover-section" 
                                onclick="filterFeaturedSection('just-for-you')" 
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-gray-100 hover:bg-[#EBEBEB] text-gray-900 font-semibold text-xs sm:text-sm transition group cursor-pointer"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#564B68]"></span>
                                    <span>Just for You</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-800 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <a 
                                href="#shoes" 
                                onclick="filterFeaturedSection('new-in')" 
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl hover:bg-gray-100 text-gray-800 font-semibold text-xs sm:text-sm transition group cursor-pointer"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>New In</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-800 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <a 
                                href="#flash-sale" 
                                onclick="filterFeaturedSection('sale')" 
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl hover:bg-red-50 text-red-600 font-semibold text-xs sm:text-sm transition group cursor-pointer"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span>Sale</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-red-400 group-hover:text-red-700 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            
                            <div class="mt-4 pt-3 border-t border-gray-100 hidden md:block">
                                <p class="text-[11px] text-gray-500 px-3 leading-relaxed">
                                    Quickly jump to curated recommendations, latest arrivals, or discounted items.
                                </p>
                            </div>
                        </div>

                        <!-- Right Column: Search + 22 Categories Grid -->
                        <div class="flex-1 min-w-0 flex flex-col">
                            <!-- Mega Menu Header -->
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#E1DDE7] gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-[#F1EFF5] text-[#6F6382] flex items-center justify-center font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-extrabold text-[#282133] leading-none">All Categories</h3>
                                        <p class="text-[11px] text-gray-500 mt-0.5">22 Categories synced from Cartzy database</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 22 Categories Responsive Grid -->
                            <div id="mega-categories-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-[340px] overflow-y-auto pr-1">
                                @php
                                    $catList = isset($allCategories) && $allCategories->count() ? $allCategories : (new \App\Http\Controllers\HomeController())->getCategoriesWithMeta();
                                    $metaMap = (new \App\Http\Controllers\HomeController())->getCategoryMeta();
                                @endphp
                                @foreach($catList as $cat)
                                    @php
                                        $catIcon = $cat->meta['icon'] ?? ($metaMap[$cat->slug]['icon'] ?? null);
                                    @endphp
                                    <a 
                                        href="{{ route('home', ['category' => $cat->slug]) }}" 
                                        onclick="if(window.filterByCategory) { filterByCategory('{{ $cat->slug }}', '{{ addslashes($cat->name) }}'); closeCategoryMenu(); return false; }"
                                        data-cat-name="{{ strtolower($cat->name) }}"
                                        class="mega-cat-item group flex items-center gap-2.5 p-2 rounded-xl border border-transparent hover:border-[#E1DDE7] hover:bg-[#F1EFF5] transition-all"
                                    >
                                        <span class="w-7 h-7 rounded-lg bg-[#FAF9FB] group-hover:bg-[#6F6382] group-hover:text-white text-[#6F6382] border border-[#E1DDE7] group-hover:border-transparent flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $catIcon ?? 'M12 6v12m6-6H6' }}"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-800 group-hover:text-[#282133] truncate leading-tight">{{ $cat->name }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Mega Menu Footer -->
                            <div class="mt-3 pt-2 border-t border-[#E1DDE7] flex items-center justify-between text-[11px] text-gray-500">
                                <span>{{ count($catList) }} active categories</span>
                                <a href="{{ route('home') }}#browse-categories" onclick="closeCategoryMenu()" class="text-[#6F6382] font-bold hover:underline">
                                    Browse visual grid &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Level Featured 3 Items (Just for You, New In, Sale) from Screenshot -->
            <div class="flex items-center gap-1 shrink-0">
                <a 
                    href="#daily-discover-section"
                    onclick="filterFeaturedSection('just-for-you')"
                    class="px-2.5 sm:px-3 py-1.5 rounded-full font-bold text-gray-800 hover:text-[#564B68] hover:bg-[#F3EFF7] transition text-xs shrink-0 flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-[#6F6382]"></span>
                    <span>Just for You</span>
                </a>
                <a 
                    href="#shoes"
                    onclick="filterFeaturedSection('new-in')"
                    class="px-2.5 sm:px-3 py-1.5 rounded-full font-bold text-gray-600 hover:text-[#564B68] hover:bg-[#F3EFF7] transition text-xs shrink-0 flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>New In</span>
                </a>
                <a 
                    href="#flash-sale"
                    onclick="filterFeaturedSection('sale')"
                    class="px-2.5 sm:px-3 py-1.5 rounded-full font-extrabold text-[#BE123C] bg-rose-50/90 hover:bg-rose-100 border border-rose-100 transition text-xs shrink-0 flex items-center gap-1.5 cursor-pointer shadow-2xs"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] animate-pulse"></span>
                    <span>Sale</span>
                </a>
            </div>

            <div class="h-4 w-px bg-[#E1DDE7] mx-1.5 shrink-0 hidden md:block"></div>

            <!-- Center: Horizontally Scrollable Categories -->
            <div class="relative flex-1 min-w-0 flex items-center">
                <!-- Left Scroll Arrow -->
                <button 
                    type="button" 
                    onclick="scrollHeaderCategories('left')" 
                    id="header-cat-scroll-left"
                    class="hidden sm:flex items-center justify-center w-6.5 h-6.5 rounded-full bg-white/95 hover:bg-white text-gray-600 hover:text-black border border-[#E1DDE7] shadow-xs shrink-0 mr-1.5 z-10 transition-all duration-150 transform hover:scale-105 active:scale-95 cursor-pointer"
                    aria-label="Scroll left"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Scrollable Track -->
                <div 
                    id="header-categories-scroll" 
                    class="flex-1 overflow-x-auto no-scrollbar scroll-smooth flex items-center gap-1 py-0.5"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                >
                    @php
                        $catList = isset($allCategories) && $allCategories->count() ? $allCategories : (new \App\Http\Controllers\HomeController())->getCategoriesWithMeta();
                    @endphp
                    @foreach($catList as $cat)
                        <a 
                            href="{{ route('home', ['category' => $cat->slug]) }}"
                            onclick="if(window.filterByCategory) { filterByCategory('{{ $cat->slug }}', '{{ addslashes($cat->name) }}'); return false; }"
                            id="header-nav-cat-{{ $cat->slug }}"
                            class="header-nav-pill whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-gray-600 hover:text-[#282133] hover:bg-white hover:shadow-2xs border border-transparent hover:border-[#E1DDE7] transition font-medium text-xs flex items-center gap-1"
                        >
                            <span>{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Right Scroll Arrow -->
                <button 
                    type="button" 
                    onclick="scrollHeaderCategories('right')" 
                    id="header-cat-scroll-right"
                    class="hidden sm:flex items-center justify-center w-6.5 h-6.5 rounded-full bg-white/95 hover:bg-white text-gray-600 hover:text-black border border-[#E1DDE7] shadow-xs shrink-0 ml-1.5 z-10 transition-all duration-150 transform hover:scale-105 active:scale-95 cursor-pointer"
                    aria-label="Scroll right"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

        </div>
    </div>
</header>

<script>
function toggleHeaderNotifDropdown(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('headerNotifDropdown');
    if (!dropdown) return;
    const isHidden = dropdown.classList.contains('hidden');
    dropdown.classList.toggle('hidden', !isHidden);
    if (isHidden) {
        const searchInput = document.getElementById('headerNotifSearch');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 60);
        }
    }
}

function filterHeaderNotifications(term) {
    const q = (term || '').toLowerCase().trim();
    const items = document.querySelectorAll('.header-notif-item');
    let matches = 0;
    items.forEach(el => {
        const title = el.getAttribute('data-title') || '';
        const msg = el.getAttribute('data-message') || '';
        const match = title.includes(q) || msg.includes(q);
        el.classList.toggle('hidden', !match);
        if (match) matches++;
    });
    const noMatchEl = document.getElementById('headerNotifNoMatch');
    if (noMatchEl) {
        noMatchEl.classList.toggle('hidden', matches > 0 || items.length === 0);
    }
}

function markNotificationAsRead(id, el, e) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (el) {
            el.classList.remove('bg-[#FFF5F3]/50');
            const dot = el.querySelector('.header-notif-dot');
            if (dot) dot.remove();
        }
        updateHeaderNotifCount(data.unread ?? 0);
    })
    .catch(() => {});
}

function markAllNotificationsAsRead(e) {
    if (e) e.stopPropagation();
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        document.querySelectorAll('.header-notif-item').forEach(el => {
            el.classList.remove('bg-[#FFF5F3]/50');
            const dot = el.querySelector('.header-notif-dot');
            if (dot) dot.remove();
        });
        updateHeaderNotifCount(0);
    })
    .catch(() => {});
}

function updateHeaderNotifCount(unread) {
    const badge = document.getElementById('headerNotifBadge');
    const pill = document.getElementById('headerNotifPill');
    const pillCount = document.getElementById('headerNotifPillCount');
    if (badge) {
        badge.textContent = unread;
        badge.classList.toggle('hidden', unread <= 0);
    }
    if (pill) {
        pill.classList.toggle('hidden', unread <= 0);
        if (pillCount) pillCount.textContent = unread;
    }
}

document.addEventListener('click', function (e) {
    const container = document.getElementById('headerNotifDropdownContainer');
    const dropdown = document.getElementById('headerNotifDropdown');
    if (container && dropdown && !container.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>

