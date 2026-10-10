@extends('layouts.app')

@section('title', 'My Dashboard | cartzy')
@section('hide_footer', 'true')

@php
    $statusMap = [
        'to_pay'     => ['label' => 'To Pay',     'cls' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'to_ship'    => ['label' => 'To Ship',    'cls' => 'bg-sky-50 text-sky-700 ring-sky-200'],
        'to_receive' => ['label' => 'To Receive', 'cls' => 'bg-violet-50 text-violet-700 ring-violet-200'],
        'completed'  => ['label' => 'Completed',  'cls' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'cancelled'  => ['label' => 'Cancelled',  'cls' => 'bg-rose-50 text-rose-700 ring-rose-200'],
    ];
    $peso = fn ($n) => '₱' . number_format($n, 2);
    $navItems = [
        ['overview',      'Overview',          'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ['orders',        'My Orders',         'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['messages',      'Messages',          'M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z'],
        ['cart',          'Cart',              'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['wishlist',      'Wishlist',          'M4.3 6.3a4.5 4.5 0 016.4 0L12 7.6l1.3-1.3a4.5 4.5 0 116.4 6.4L12 20.4l-7.7-7.7a4.5 4.5 0 010-6.4z'],
        ['history',       'Order History',     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['track',         'Track Order',       'M17.7 16.7L13.4 20.9a2 2 0 01-2.8 0l-4.2-4.2a8 8 0 1111.3 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        ['notifications', 'Notifications',     'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0'],
        ['reviews',       'Reviews & Ratings', 'M11.5 3.3a.6.6 0 011 0l2.3 4.7 5.2.8c.5.1.8.7.4 1.1l-3.8 3.7.9 5.2c.1.5-.4.9-.9.6L12 17l-4.6 2.4c-.5.3-1-.1-.9-.6l.9-5.2-3.8-3.7c-.4-.4-.1-1 .4-1.1l5.2-.8 2.3-4.7z'],
        ['settings',      'Account Settings',  'M12 15a3 3 0 100-6 3 3 0 000 6zm7.4-3a7.4 7.4 0 00-.1-1.2l2-1.6-2-3.4-2.4 1a7.5 7.5 0 00-2-1.2L14.5 3h-4l-.4 2.6a7.5 7.5 0 00-2 1.2l-2.4-1-2 3.4 2 1.6a7.4 7.4 0 000 2.4l-2 1.6 2 3.4 2.4-1a7.5 7.5 0 002 1.2l.4 2.6h4l.4-2.6a7.5 7.5 0 002-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2z'],
    ];
    $badges = ['orders' => count($orders), 'cart' => $cartCount, 'wishlist' => count($wishlist), 'notifications' => $unread];
    $inputCls = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-[#C08B7F] focus:ring-4 focus:ring-[#C08B7F]/15 transition';
    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#564B68] to-[#6F6382] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow-md hover:brightness-110 transition cursor-pointer';
    $btnGhost = 'inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer';
@endphp

@section('content')
<div class="bg-[#F6F4F8] min-h-[calc(100vh-72px)]">

    {{-- Hero welcome band --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-[#564B68] via-[#855B6E] to-[#C08B7F] text-white shadow-sm">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute left-1/3 -bottom-24 h-56 w-56 rounded-full bg-[#C08B7F]/40 blur-3xl"></div>
        <div class="absolute -left-12 -top-12 h-52 w-52 rounded-full bg-black/10 blur-2xl"></div>
        <div class="relative mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-4 px-4 py-8 sm:px-6 lg:px-10">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#F9ECE9]">MY CARTZY</p>
                <h1 class="mt-1 font-heading text-3xl font-bold sm:text-4xl text-white">Welcome back, {{ \Illuminate\Support\Str::of($user->name)->before(' ') }}</h1>
                <p class="mt-1 text-sm text-white/90">Track your orders, manage your account, and pick up where you left off.</p>
            </div>
            <div class="flex gap-2.5">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-[#564B68] shadow-sm hover:bg-[#FAF7F6] hover:shadow-md transition">Continue shopping</a>
                <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/40 bg-white/10 backdrop-blur-xs px-5 py-2.5 text-sm font-bold text-white hover:bg-white/20 transition">View cart ({{ $cartCount }})</a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-10">

        @if(session('success'))
            <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-bold text-emerald-800">
                <span>✓ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="cursor-pointer text-emerald-600">✕</button>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3.5 text-sm text-rose-800">
                <p class="font-bold">Please fix the following:</p>
                <ul class="mt-1 list-disc list-inside text-xs">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

            {{-- ===================== SIDEBAR ===================== --}}
            <aside class="lg:col-span-3 lg:sticky lg:top-[120px]">
                <div class="overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-[0_8px_30px_-12px_rgba(40,33,51,0.15)]">
                    <div class="flex items-center gap-3.5 p-5">
                        <div class="relative group shrink-0">
                            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#564B68] to-[#C08B7F] text-lg font-black text-white ring-4 ring-[#F1EFF5]">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <button type="button" onclick="document.getElementById('dashAvatarInput').click()" title="Change photo" class="absolute inset-0 rounded-full bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            <form id="dashAvatarForm" action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="avatar" id="dashAvatarInput" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                            </form>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-extrabold text-gray-900">{{ $user->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                            @if(method_exists($user, 'isIdVerified') && $user->isIdVerified())
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200">✓ Verified buyer</span>
                            @else
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-amber-200">ID not verified</span>
                            @endif
                        </div>
                    </div>
                    <nav class="border-t border-gray-100 p-2.5" id="dashNav" aria-label="Dashboard navigation">
                        @foreach($navItems as [$key, $label, $icon])
                            <button type="button" data-nav="{{ $key }}"
                                    class="dash-nav group flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-sm font-semibold text-gray-600 transition hover:bg-[#F6F4F8] hover:text-[#3E354C] cursor-pointer">
                                <svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                <span class="flex-1">{{ $label }}</span>
                                @if(!empty($badges[$key]))
                                    <span class="rounded-full bg-[#F1EFF5] px-2 py-0.5 text-[10px] font-bold text-[#564B68] group-[.is-active]:bg-white/20 group-[.is-active]:text-white">{{ $badges[$key] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </nav>
                    <div class="border-t border-gray-100 p-3">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- ===================== MAIN ===================== --}}
            <main class="space-y-6 lg:col-span-9">
                <section data-panel="messages" class="dash-panel hidden space-y-5">
                    @if($inbox)
                        @include('buyer.partials.messages', $inbox)
                    @endif
                </section>

                {{-- ---------- OVERVIEW ---------- --}}
                <section data-panel="overview" class="dash-panel space-y-6">
                    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                        @foreach([
                            ['Total orders', $stats['total_orders'], 'from-[#564B68] to-[#6F6382]', 'orders'],
                            ['Total spent', $peso($stats['total_spent']), 'from-[#C08B7F] to-[#D4A69A]', 'history'],
                            ['Items in cart', $stats['cart_items'], 'from-[#3E354C] to-[#564B68]', 'cart'],
                            ['Wishlist', $stats['wishlist'], 'from-[#8C5A50] to-[#C08B7F]', 'wishlist'],
                        ] as [$label, $value, $grad, $go])
                            <button type="button" data-go="{{ $go }}" class="group rounded-2xl border border-[#E1DDE7] bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg cursor-pointer">
                                <div class="mb-4 h-1.5 w-10 rounded-full bg-gradient-to-r {{ $grad }}"></div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ $value }}</p>
                            </button>
                        @endforeach
                    </div>

                    {{-- Order pipeline --}}
                    <div class="rounded-3xl border border-[#E1DDE7] bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="font-heading text-xl font-bold text-gray-900">Order status</h2>
                            <button type="button" data-go="orders" class="text-sm font-bold text-[#8C5A50] hover:underline cursor-pointer">View all →</button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                            @foreach($statusMap as $key => $s)
                                <button type="button" data-go="orders" data-filter="{{ $key }}" class="rounded-2xl bg-[#F6F4F8] p-4 text-center transition hover:bg-[#F1EFF5] cursor-pointer">
                                    <p class="text-2xl font-extrabold text-[#3E354C]">{{ $statusCounts[$key] ?? 0 }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-gray-500">{{ $s['label'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-5">
                        <div class="rounded-3xl border border-[#E1DDE7] bg-white p-6 shadow-sm xl:col-span-3">
                            <h2 class="mb-4 font-heading text-xl font-bold text-gray-900">Recent orders</h2>
                            <div class="divide-y divide-gray-100">
                                @forelse(array_slice($orders, 0, 3) as $o)
                                    <div class="flex items-center gap-4 py-3.5">
                                        <img src="{{ $o['items'][0]['image'] ?? '' }}" alt="" class="h-14 w-14 rounded-xl border border-gray-100 object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $o['items'][0]['name'] ?? 'Order item' }}</p>
                                            <p class="text-xs text-gray-500">{{ $o['id'] }} · {{ $o['date'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-extrabold text-gray-900">{{ $peso($o['total']) }}</p>
                                            <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold ring-1 {{ $statusMap[$o['status']]['cls'] }}">{{ $statusMap[$o['status']]['label'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-8 text-center text-xs font-semibold text-gray-400">No orders placed yet.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-3xl border border-[#E1DDE7] bg-white p-6 shadow-sm xl:col-span-2">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="font-heading text-xl font-bold text-gray-900">Latest updates</h2>
                                <button type="button" data-go="notifications" class="text-sm font-bold text-[#8C5A50] hover:underline cursor-pointer">All →</button>
                            </div>
                            <div class="space-y-3">
                                @forelse(array_slice($notifications, 0, 3) as $n)
                                    <div class="flex gap-3 rounded-2xl p-3 {{ $n['unread'] ? 'bg-[#FFF5F3]' : 'bg-[#F6F4F8]' }}">
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $n['unread'] ? 'bg-[#C08B7F]' : 'bg-gray-300' }}"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900">{{ $n['title'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $n['message'] }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-8 text-center text-xs font-semibold text-gray-400">No updates right now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ---------- MY ORDERS ---------- --}}
                <section data-panel="orders" class="dash-panel hidden space-y-5">
                    <header>
                        <h2 class="font-heading text-2xl font-bold text-gray-900">My Orders</h2>
                        <p class="text-sm text-gray-500">Everything you've ordered, grouped by status.</p>
                    </header>
                    @if(count($orders))
                        <div class="flex flex-wrap gap-2 rounded-2xl border border-[#E1DDE7] bg-white p-2" id="orderFilters">
                            <button type="button" data-filter="all" class="order-filter rounded-xl px-4 py-2 text-sm font-bold cursor-pointer">All <span class="ml-1 text-xs opacity-70">{{ count($orders) }}</span></button>
                            @foreach($statusMap as $key => $s)
                                <button type="button" data-filter="{{ $key }}" class="order-filter rounded-xl px-4 py-2 text-sm font-bold cursor-pointer">{{ $s['label'] }} <span class="ml-1 text-xs opacity-70">{{ $statusCounts[$key] ?? 0 }}</span></button>
                            @endforeach
                        </div>
                        <div class="space-y-4" id="orderList">
                            @foreach($orders as $o)
                                @include('buyer.partials.order-card', ['o' => $o, 'statusMap' => $statusMap, 'peso' => $peso, 'btnPrimary' => $btnPrimary, 'btnGhost' => $btnGhost])
                            @endforeach
                        </div>
                        <p id="orderEmpty" class="hidden rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm font-semibold text-gray-500">No orders in this status yet.</p>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '📦',
                            'title' => 'No orders placed yet',
                            'text' => 'When you buy something, your order will appear here with live tracking and status updates.',
                            'cta' => 'Start shopping',
                            'href' => route('home'),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- CART ---------- --}}
                <section data-panel="cart" class="dash-panel hidden space-y-5">
                    <header class="flex items-end justify-between">
                        <div>
                            <h2 class="font-heading text-2xl font-bold text-gray-900">Cart</h2>
                            <p class="text-sm text-gray-500">Products you're planning to buy.</p>
                        </div>
                        <a href="{{ route('cart.index') }}" class="{{ $btnGhost }}">Open full cart</a>
                    </header>
                    @if(count($cart))
                        <div class="overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm">
                            <div class="divide-y divide-gray-100">
                                @foreach($cart as $item)
                                    <div class="flex items-center gap-4 p-4 sm:p-5">
                                        <img src="{{ $item['image'] }}" alt="" class="h-16 w-16 rounded-xl border border-gray-100 object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $item['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $item['variation'] ?? 'Standard' }} · Qty {{ $item['quantity'] }}</p>
                                        </div>
                                        <p class="text-sm font-extrabold text-gray-900">{{ $peso($item['price'] * $item['quantity']) }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3 bg-[#F6F4F8] p-5">
                                <p class="text-sm font-semibold text-gray-600">Subtotal ({{ $cartCount }} items) <span class="ml-2 text-lg font-extrabold text-gray-900">{{ $peso($cartSubtotal) }}</span></p>
                                <a href="{{ route('checkout') }}" class="{{ $btnPrimary }}">Proceed to checkout →</a>
                            </div>
                        </div>
                    @else
                        @include('buyer.partials.empty', ['icon' => '🛒', 'title' => 'Your cart is empty', 'text' => 'Browse the marketplace and add items you love.', 'cta' => 'Start shopping', 'href' => route('home'), 'btnPrimary' => $btnPrimary])
                    @endif
                </section>

                {{-- ---------- WISHLIST ---------- --}}
                <section data-panel="wishlist" class="dash-panel hidden space-y-5">
                    <header>
                        <h2 class="font-heading text-2xl font-bold text-gray-900">Wishlist</h2>
                        <p class="text-sm text-gray-500">Saved products you may want to buy later.</p>
                    </header>
                    @if(count($wishlist))
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($wishlist as $w)
                                <article class="group relative overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                    <button type="button" onclick="removeFromWishlist('{{ $w['ref'] }}')" title="Remove from wishlist" class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-rose-500 shadow-sm backdrop-blur hover:bg-rose-50 cursor-pointer transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                                        <img src="{{ $w['image'] }}" alt="{{ $w['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    </div>
                                    <div class="p-4">
                                        <p class="line-clamp-2 text-sm font-bold text-gray-900">{{ $w['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $w['variation'] ?? 'Standard' }}</p>
                                        <p class="mt-2 text-lg font-extrabold text-[#3E354C]">{{ $peso($w['price']) }}</p>
                                        <button type="button"
                                                onclick="moveToCart('{{ $w['ref'] }}', {{ crc32($w['name']) % 100000 }}, {{ json_encode($w['name']) }}, {{ $w['price'] }}, {{ json_encode($w['image']) }}, {{ json_encode($w['variation']) }})"
                                                class="mt-3 w-full {{ $btnPrimary }}">Move to cart</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '🤍',
                            'title' => 'Your wishlist is empty',
                            'text' => 'Save products you love so you can easily purchase them later.',
                            'cta' => 'Discover products',
                            'href' => route('home'),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- ORDER HISTORY ---------- --}}
                <section data-panel="history" class="dash-panel hidden space-y-5">
                    <header>
                        <h2 class="font-heading text-2xl font-bold text-gray-900">Order History</h2>
                        <p class="text-sm text-gray-500">Your previous purchases and receipts.</p>
                    </header>
                    @php
                        $historyOrders = array_filter($orders, fn ($o) => in_array($o['status'], ['completed', 'cancelled']));
                    @endphp
                    @if(count($historyOrders))
                        <div class="overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-[#F6F4F8] text-xs font-bold uppercase tracking-wider text-gray-500">
                                        <tr><th class="px-5 py-3.5">Order</th><th class="px-5 py-3.5">Date</th><th class="px-5 py-3.5">Store</th><th class="px-5 py-3.5">Payment</th><th class="px-5 py-3.5 text-right">Total</th><th class="px-5 py-3.5">Status</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($historyOrders as $o)
                                            <tr class="hover:bg-[#FAF9FB]">
                                                <td class="px-5 py-4 font-bold text-gray-900">{{ $o['id'] }}</td>
                                                <td class="px-5 py-4 text-gray-600">{{ $o['date'] }}</td>
                                                <td class="px-5 py-4 text-gray-600">{{ $o['store'] }}</td>
                                                <td class="px-5 py-4 text-gray-600">{{ $o['payment'] }}</td>
                                                <td class="px-5 py-4 text-right font-extrabold text-gray-900">{{ $peso($o['total']) }}</td>
                                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusMap[$o['status']]['cls'] }}">{{ $statusMap[$o['status']]['label'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '📜',
                            'title' => 'No past purchases yet',
                            'text' => 'Completed and cancelled orders will be archived here for your records.',
                            'cta' => 'Start shopping',
                            'href' => route('home'),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- TRACK ORDER ---------- --}}
                <section data-panel="track" class="dash-panel hidden space-y-5">
                    <header>
                        <h2 class="font-heading text-2xl font-bold text-gray-900">Track Order</h2>
                        <p class="text-sm text-gray-500">Live delivery status and parcel timeline.</p>
                    </header>

                    {{-- Search bar to track any order by reference or tracking code --}}
                    <form action="{{ route('buyer.dashboard') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                        <input type="hidden" name="tab" value="track">
                        <input type="text" name="track" value="{{ request('track') }}" placeholder="Enter order reference (e.g. ORD-...) or courier tracking number" class="{{ $inputCls }}">
                        <button type="submit" class="{{ $btnPrimary }} whitespace-nowrap">Track parcel</button>
                    </form>

                    @if($tracking && !($tracking['not_found'] ?? false))
                        <div class="rounded-3xl border border-[#E1DDE7] bg-white p-6 shadow-sm">
                            <div class="grid gap-4 rounded-2xl bg-gradient-to-r from-[#3E354C] to-[#564B68] p-5 text-white sm:grid-cols-3">
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Order</p><p class="font-extrabold">{{ $tracking['order'] }}</p></div>
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Tracking no.</p><p class="font-extrabold">{{ $tracking['tracking_no'] }}</p></div>
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Estimated arrival</p><p class="font-extrabold text-[#E8C9C0]">{{ $tracking['eta'] }}</p></div>
                            </div>
                            <p class="mt-4 text-sm text-gray-500">Courier: <span class="font-bold text-gray-800">{{ $tracking['courier'] }}</span></p>
                            <ol class="mt-6 space-y-0">
                                @foreach($tracking['steps'] as $step)
                                    <li class="relative flex gap-4 pb-7 last:pb-0">
                                        @if(!$loop->last)<span class="absolute left-[15px] top-8 h-[calc(100%-1.5rem)] w-0.5 {{ $step['done'] && !($step['current'] ?? false) ? 'bg-[#C08B7F]' : 'bg-gray-200' }}"></span>@endif
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ ($step['current'] ?? false) ? 'bg-[#C08B7F] text-white ring-4 ring-[#C08B7F]/25' : ($step['done'] ? 'bg-[#564B68] text-white' : 'bg-gray-200 text-gray-400') }}">{{ $step['done'] ? '✓' : '•' }}</span>
                                        <div>
                                            <p class="text-sm font-bold {{ $step['done'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $step['label'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $step['detail'] }}</p>
                                            <p class="mt-0.5 text-[11px] font-semibold text-[#8C5A50]">{{ $step['time'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @elseif($tracking && ($tracking['not_found'] ?? false))
                        <div class="rounded-3xl border border-rose-200 bg-white p-8 text-center shadow-sm">
                            <span class="text-3xl">🔍</span>
                            <h3 class="mt-2 text-base font-bold text-gray-900">Parcel not found</h3>
                            <p class="mt-1 text-sm text-gray-500">No matching order or tracking number found for "<strong>{{ $tracking['query'] }}</strong>".</p>
                        </div>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '📦',
                            'title' => 'No active parcels to track',
                            'text' => 'When you place an order, live shipping status and courier timeline will appear here.',
                            'cta' => 'Browse products',
                            'href' => route('home'),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- NOTIFICATIONS ---------- --}}
                <section data-panel="notifications" class="dash-panel hidden space-y-5">
                    <header class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-heading text-2xl font-bold text-gray-900">Notifications</h2>
                            <p class="text-sm text-gray-500">Updates about your orders and account.</p>
                        </div>
                        @if(count($notifications))
                            <button type="button" onclick="markAllNotificationsAsRead()" class="{{ $btnGhost }} text-xs">
                                Mark all as read
                            </button>
                        @endif
                    </header>
                    @if(count($notifications))
                        <div class="relative">
                            <input type="text" 
                                   id="dashNotifSearch" 
                                   placeholder="search notifications....." 
                                   oninput="filterDashNotifications(this.value)"
                                   class="{{ $inputCls }} pl-10">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <div id="dashNotifList" class="divide-y divide-gray-100 overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm">
                            @foreach($notifications as $n)
                                <div data-title="{{ strtolower($n['title']) }}"
                                     data-message="{{ strtolower($n['message']) }}"
                                     data-notif-id="{{ $n['id'] ?? '' }}"
                                     onclick="markNotificationAsRead({{ $n['id'] ?? 'null' }}, this)"
                                     class="dash-notif-row flex gap-4 p-5 transition hover:bg-[#FAF9FB] cursor-pointer {{ $n['unread'] ? 'bg-[#FFF5F3]/70' : '' }}">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EFF5] text-lg">{{ ['delivery' => '🚚', 'payment' => '💳', 'order' => '📦', 'review' => '⭐'][$n['type']] ?? '🔔' }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-sm font-bold text-gray-900">{{ $n['title'] }}</p>
                                            <span class="shrink-0 text-[11px] font-semibold text-gray-400">{{ $n['time'] }}</span>
                                        </div>
                                        <p class="mt-0.5 text-sm text-gray-500">{{ $n['message'] }}</p>
                                    </div>
                                    @if($n['unread'])<span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-[#C08B7F] dash-notif-dot"></span>@endif
                                </div>
                            @endforeach
                            <div id="dashNotifNoMatch" class="hidden p-8 text-center text-sm text-gray-400 font-semibold">
                                No notifications matching your search.
                            </div>
                        </div>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '🔔',
                            'title' => 'No notifications yet',
                            'text' => 'Updates regarding your orders, tracking and reviews will show up here.',
                            'cta' => 'Go to overview',
                            'href' => route('buyer.dashboard'),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- REVIEWS ---------- --}}
                <section data-panel="reviews" class="dash-panel hidden space-y-5">
                    <header>
                        <h2 class="font-heading text-2xl font-bold text-gray-900">Reviews & Ratings</h2>
                        <p class="text-sm text-gray-500">Share feedback on products you've received.</p>
                    </header>
                    @if(count($reviews))
                        <div class="space-y-4">
                            @foreach($reviews as $r)
                                <div class="flex flex-col gap-4 rounded-3xl border border-[#E1DDE7] bg-white p-5 shadow-sm sm:flex-row">
                                    <img src="{{ $r['image'] }}" alt="" class="h-20 w-20 rounded-2xl border border-gray-100 object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900">{{ $r['product'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $r['store'] }} · {{ $r['order'] }}</p>
                                        @if(!empty($r['seller_reply']))
                                            <div class="mt-3 rounded-xl bg-[#F6F4F8] p-3 text-sm text-gray-700"><strong>Seller response</strong><p class="mt-1 whitespace-pre-wrap">{{ $r['seller_reply'] }}</p></div>
                                        @endif
                                        @if($r['rating'])
                                            <p class="mt-2 text-lg tracking-wide text-amber-400">{{ str_repeat('★', $r['rating']) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $r['rating']) }}</span></p>
                                            <p class="text-sm text-gray-600">“{{ $r['comment'] }}”</p>
                                            <span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-emerald-600">✓ Reviewed</span>
                                        @else
                                            <form action="{{ route('reviews.store') }}" method="POST" class="mt-3 space-y-2.5">
                                                @csrf
                                                <input type="hidden" name="order_item_id" value="{{ $r['item_id'] }}">
                                                <input type="hidden" name="rating" value="5" class="rating-input">
                                                <div class="flex items-center gap-1 text-2xl text-amber-400 star-group">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <button type="button" data-star="{{ $i }}" class="star is-on cursor-pointer transition hover:scale-125">★</button>
                                                    @endfor
                                                </div>
                                                <textarea name="comment" rows="2" placeholder="Write your review and share your experience with this item…" class="{{ $inputCls }}"></textarea>
                                                <button type="submit" class="{{ $btnPrimary }}">Submit review</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @include('buyer.partials.empty', [
                            'icon' => '⭐',
                            'title' => 'No products to review yet',
                            'text' => 'Once your orders are delivered, you can rate and share your feedback here.',
                            'cta' => 'View orders',
                            'href' => route('buyer.dashboard', ['tab' => 'orders']),
                            'btnPrimary' => $btnPrimary
                        ])
                    @endif
                </section>

                {{-- ---------- SETTINGS ---------- --}}
                <section data-panel="settings" class="dash-panel hidden space-y-5">
                    @include('buyer.partials.account-settings')
                </section>

            </main>
        </div>
    </div>
</div>

<style>
    .dash-nav.is-active { background: linear-gradient(90deg, #564B68, #C08B7F); color: #fff; box-shadow: 0 6px 16px -6px rgba(86,75,104,.4); }
    .dash-panel { animation: dashFade .28s ease both; }
    @keyframes dashFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .order-filter { color: #6b7280; transition: all .15s; }
    .order-filter:hover { background: #F6F4F8; }
    .order-filter.is-active { background: #564B68; color: #fff; }
    .star.is-on { color: #fbbf24; }
</style>

<script>
(function () {
    const panels = document.querySelectorAll('[data-panel]');
    const navs = document.querySelectorAll('[data-nav]');
    const valid = Array.from(panels).map(p => p.dataset.panel);

    function show(key, push) {
        if (key === 'messages' && !@json((bool) $inbox)) {
            window.location.href = @json(route('buyer.messages'));
            return;
        }
        if (key === 'profile' || key === 'addresses') {
            const wasAddresses = (key === 'addresses');
            key = 'settings';
            if (wasAddresses) {
                setTimeout(() => {
                    const el = document.getElementById('saved-addresses-card') || document.querySelector('.settings-add');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 120);
            }
        }
        if (!valid.includes(key)) key = 'overview';
        panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== key));
        navs.forEach(n => n.classList.toggle('is-active', n.dataset.nav === key));
        navs.forEach(n => n.classList.toggle('group', true));
        const url = new URL(location.href);
        url.pathname = new URL(@json(route('buyer.dashboard'))).pathname;
        url.searchParams.set('tab', key);
        url.hash = key;
        history.replaceState(null, '', url);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function filterOrders(f) {
        let shown = 0;
        document.querySelectorAll('#orderList [data-status]').forEach(c => {
            const ok = f === 'all' || c.dataset.status === f;
            c.classList.toggle('hidden', !ok);
            if (ok) shown++;
        });
        const emptyEl = document.getElementById('orderEmpty');
        if (emptyEl) emptyEl.classList.toggle('hidden', shown > 0);
        document.querySelectorAll('.order-filter').forEach(b => b.classList.toggle('is-active', b.dataset.filter === f));
    }

    navs.forEach(n => n.addEventListener('click', () => show(n.dataset.nav, true)));
    document.querySelectorAll('[data-go]').forEach(b => b.addEventListener('click', () => {
        if (b.dataset.filter) filterOrders(b.dataset.filter);
        show(b.dataset.go, true);
    }));
    document.querySelectorAll('.order-filter').forEach(b => b.addEventListener('click', () => filterOrders(b.dataset.filter)));

    document.querySelectorAll('.star-group').forEach(g => {
        const form = g.closest('form');
        const ratingInput = form?.querySelector('.rating-input');
        g.addEventListener('click', e => {
            const s = e.target.closest('[data-star]'); if (!s) return;
            const val = +s.dataset.star;
            if (ratingInput) ratingInput.value = val;
            g.querySelectorAll('.star').forEach(x => x.classList.toggle('is-on', +x.dataset.star <= val));
        });
    });

    filterOrders('all');
    const start = location.hash.replace('#', '') || @json($tab);
    show(start, false);

    window.addEventListener('hashchange', () => {
        show(location.hash.replace('#', '') || 'overview', false);
    });
})();

window.removeFromWishlist = function (ref) {
    if (!confirm('Remove this product from your wishlist?')) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('{{ route('wishlist.remove') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ref: ref })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route('buyer.dashboard', ['tab' => 'wishlist']) }}';
        }
    })
    .catch(() => alert('Could not remove item. Please try again.'));
};

window.moveToCart = function (ref, id, name, price, image, variation) {
    if (typeof window.addToCart === 'function') {
        window.addToCart(id, name, price, image, variation);
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('{{ route('wishlist.remove') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ref: ref })
    })
    .then(() => {
        setTimeout(() => {
            window.location.href = '{{ route('buyer.dashboard', ['tab' => 'cart']) }}';
        }, 500);
    });
};

window.filterDashNotifications = function (term) {
    const q = (term || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.dash-notif-row');
    let matches = 0;
    rows.forEach(r => {
        const title = r.getAttribute('data-title') || '';
        const msg = r.getAttribute('data-message') || '';
        const match = title.includes(q) || msg.includes(q);
        r.classList.toggle('hidden', !match);
        if (match) matches++;
    });
    const noMatch = document.getElementById('dashNotifNoMatch');
    if (noMatch) noMatch.classList.toggle('hidden', matches > 0 || rows.length === 0);
};

window.markNotificationAsRead = function (id, el) {
    if (!id) return;
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
            el.classList.remove('bg-[#FFF5F3]/70');
            const dot = el.querySelector('.dash-notif-dot');
            if (dot) dot.remove();
        }
        if (typeof updateHeaderNotifCount === 'function') {
            updateHeaderNotifCount(data.unread ?? 0);
        }
    })
    .catch(() => {});
};

window.markAllNotificationsAsRead = function () {
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
        document.querySelectorAll('.dash-notif-row').forEach(el => {
            el.classList.remove('bg-[#FFF5F3]/70');
            const dot = el.querySelector('.dash-notif-dot');
            if (dot) dot.remove();
        });
        if (typeof updateHeaderNotifCount === 'function') {
            updateHeaderNotifCount(0);
        }
    })
    .catch(() => {});
};
</script>
@endsection
