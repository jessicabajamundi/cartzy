@extends('layouts.app')

@section('title', 'My Account | cartzy')
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
    $inputCls = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-[#C08B7F] focus:ring-4 focus:ring-[#C08B7F]/15 transition';
    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#564B68] to-[#6F6382] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow-md hover:brightness-110 transition cursor-pointer';
    $btnGhost = 'inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer';
@endphp

@section('content')
@include('buyer.partials.commerce-style')
@include('buyer.partials.wishlist-style')
<div class="bg-[#F6F4F8] min-h-[calc(100vh-72px)]">

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

        <div class="min-w-0">

            {{-- ===================== MAIN ===================== --}}
            <main class="min-w-0 space-y-6">
                <section data-panel="messages" class="dash-panel hidden space-y-5">
                    @if($inbox)
                        @include('buyer.partials.messages', $inbox)
                    @endif
                </section>

                {{-- ---------- MY ORDERS ---------- --}}
                <section data-panel="orders" class="dash-panel commerce-page hidden space-y-5">
                    <header class="commerce-heading">
                        <div><p class="commerce-eyebrow">YOUR SHOPPING JOURNEY</p><h2>My Orders</h2><p>Every purchase, every update. Keep track of it all here.</p></div>
                        <span class="commerce-count">{{ count($orders) }} {{ count($orders) === 1 ? 'order' : 'orders' }}</span>
                    </header>
                    @if(count($orders))
                        <div class="orders-filter-bar" id="orderFilters" role="group" aria-label="Filter orders by status">
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
                        <div id="orderEmpty" class="hidden orders-filter-empty" role="status"><strong>No orders in this status yet.</strong><p>Choose another status to see your other purchases.</p></div>
                    @else
                        @include('buyer.partials.commerce-empty', [
                            'emptyPath' => 'm12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5',
                            'eyebrow' => 'YOUR FIRST FIND AWAITS',
                            'note' => 'Order details and delivery updates will be available right here.',
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
                <section data-panel="wishlist" class="dash-panel commerce-page wishlist-page hidden" aria-labelledby="wishlist-title">
                    <header class="commerce-heading wishlist-heading">
                        <div><p class="commerce-eyebrow">SAVED FOR LATER</p><h2 id="wishlist-title">Your wishlist</h2><p>A little collection of things you love.</p></div>
                        <div class="wishlist-heading-actions">
                            <span class="commerce-count">{{ count($wishlist) }} {{ count($wishlist) === 1 ? 'saved item' : 'saved items' }}</span>
                            <a class="wishlist-browse" href="{{ route('home') }}#daily-discover-section">Continue shopping <span aria-hidden="true">&rarr;</span></a>
                        </div>
                    </header>
                    @if(count($wishlist))
                        <div class="wishlist-grid">
                            @foreach($wishlist as $w)
                                <article class="wishlist-card">
                                    <div class="wishlist-media">
                                        <img src="{{ $w['image'] }}" alt="{{ $w['name'] }}" loading="lazy" decoding="async">
                                        <button type="button" onclick="removeFromWishlist({{ json_encode($w['ref']) }})" title="Remove from wishlist" aria-label="Remove {{ $w['name'] }} from wishlist" class="wishlist-remove">
                                            <svg aria-hidden="true" fill="currentColor" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                                        </button>
                                    </div>
                                    <div class="wishlist-body">
                                        <h3 class="wishlist-name">{{ $w['name'] }}</h3>
                                        <p class="wishlist-variant">{{ $w['variation'] ?? 'Standard' }}</p>
                                        <p class="wishlist-price">{{ $peso($w['price']) }}</p>
                                        <button type="button"
                                                onclick="moveToCart('{{ $w['ref'] }}', {{ ctype_digit((string) $w['ref']) ? (int) $w['ref'] : crc32($w['name']) % 100000 }}, {{ json_encode($w['name']) }}, {{ $w['price'] }}, {{ json_encode($w['image']) }}, {{ json_encode($w['variation']) }})"
                                                class="wishlist-cart"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l3 12h10l3-9H6M9 20h.01M18 20h.01"/></svg>Move to cart</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        @include('buyer.partials.commerce-empty', [
                            'emptyPath' => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z',
                            'eyebrow' => 'SAVED FOR LATER',
                            'note' => 'Tap the heart on a product to keep it here for later.',
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
                <section data-panel="history" class="dash-panel commerce-page hidden space-y-5">
                    <header class="commerce-heading"><div><p class="commerce-eyebrow">YOUR PURCHASES</p><h2>Order History</h2><p>A clear record of your completed and cancelled orders.</p></div><span class="commerce-count">Purchase archive</span></header>
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
                        @include('buyer.partials.commerce-empty', [
                            'emptyPath' => 'M6 3h12v18l-3-2-3 2-3-2-3 2V3ZM9 7h6M9 11h6M9 15h3',
                            'eyebrow' => 'YOUR PURCHASES',
                            'note' => 'Completed and cancelled purchases will appear here automatically.',
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
                <section data-panel="track" class="dash-panel commerce-page hidden space-y-5">
                    <header class="commerce-heading"><div><p class="commerce-eyebrow">DELIVERY UPDATES</p><h2>Track Order</h2><p>Follow your parcel, from order confirmation to your doorstep.</p></div><span class="commerce-count">Parcel tracking</span></header>

                    {{-- Search bar to track any order by reference or tracking code --}}
                    <form action="{{ route('buyer.dashboard') }}" method="GET" class="commerce-track-search">
                        <label for="parcel-reference">Order reference or tracking number</label><div class="flex flex-col sm:flex-row gap-3">
                        <input type="hidden" name="tab" value="track">
                        <input id="parcel-reference" type="text" name="track" value="{{ request('track') }}" placeholder="Enter order reference (e.g. ORD-...) or courier tracking number" class="{{ $inputCls }}">
                        <button type="submit" class="commerce-button whitespace-nowrap">Track parcel &rarr;</button></div>
                    </form>

                    @if($tracking && !($tracking['not_found'] ?? false))
                        <div class="commerce-tracking-card">
                            <div class="commerce-tracking-summary">
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Order</p><p class="font-extrabold">{{ $tracking['order'] }}</p></div>
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Tracking no.</p><p class="font-extrabold">{{ $tracking['tracking_no'] }}</p></div>
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Estimated arrival</p><p class="font-extrabold text-[#E8C9C0]">{{ $tracking['eta'] }}</p></div>
                            </div>
                            <p class="mt-4 text-sm text-gray-500">Courier: <span class="font-bold text-gray-800">{{ $tracking['courier'] }}</span></p>
                            <div class="tracking-status-panel">
                                <div><span class="tracking-caption">Latest confirmed stage</span><strong>{{ $tracking['current_status'] }}</strong></div>
                                <div><span class="tracking-caption">Last recorded update · Philippine time</span><strong>{{ $tracking['last_update'] }}</strong></div>
                            </div>
                            <dl class="tracking-details">
                                <div><dt>Assigned rider</dt><dd>{{ $tracking['rider'] }}</dd></div>
                                <div><dt>Delivery area</dt><dd>{{ $tracking['area'] }}</dd></div>
                                <div><dt>Delivery attempts</dt><dd>{{ $tracking['attempts'] ?: 'No attempt recorded' }}</dd></div>
                            </dl>
                            @if($tracking['attention'])
                                <p class="tracking-attention" role="status">{{ $tracking['attention'] }}</p>
                            @endif
                            <p class="tracking-explainer">Updates appear when the seller or courier records an action. This is not live GPS tracking. Refresh this page to check for new updates.</p>
                            <a class="tracking-refresh" href="{{ request()->fullUrl() }}">Refresh tracking ↻</a>
                            <h3 class="commerce-timeline-title">Delivery timeline</h3><ol class="mt-6 space-y-0">
                                @foreach($tracking['steps'] as $step)
                                    <li class="relative flex gap-4 pb-7 last:pb-0">
                                        @if(!$loop->last)<span class="absolute left-[15px] top-8 h-[calc(100%-1.5rem)] w-0.5 {{ $step['done'] && !($step['current'] ?? false) ? 'bg-[#C08B7F]' : 'bg-gray-200' }}"></span>@endif
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ ($step['current'] ?? false) ? 'bg-[#C08B7F] text-white ring-4 ring-[#C08B7F]/25' : ($step['done'] ? 'bg-[#564B68] text-white' : 'bg-gray-200 text-gray-400') }}">{{ $step['done'] ? '✓' : '•' }}</span>
                                        <div>
                                            <p class="text-sm font-bold {{ $step['done'] ? 'text-gray-900' : 'text-gray-500' }}">{{ $step['label'] }} @if($step['current'] ?? false)<span class="tracking-current">Latest confirmed</span>@endif</p>
                                            <p class="text-xs text-gray-500">{{ $step['detail'] }}</p>
                                            <p class="mt-0.5 text-[11px] font-semibold text-[#8C5A50]">{{ $step['time'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                            <details class="tracking-history">
                                <summary>Detailed activity · {{ count($tracking['history']) }} recorded updates</summary>
                                <p class="tracking-explainer">Newest first. Repeated scans and delivery attempts are shown separately. All times are Philippine time.</p>
                                @forelse($tracking['history'] as $event)
                                    <article>
                                        <strong>{{ $event['label'] }}</strong>
                                        <p>{{ $event['time'] }}@if($event['attempt'] > 1) · Attempt {{ $event['attempt'] }}@endif</p>
                                        @if($event['note'])<p>{{ $event['note'] }}</p>@endif
                                    </article>
                                @empty
                                    <p class="tracking-explainer">No courier scans recorded yet.</p>
                                @endforelse
                            </details>
                        </div>
                    @elseif($tracking && ($tracking['not_found'] ?? false))
                        <div class="rounded-3xl border border-rose-200 bg-white p-8 text-center shadow-sm">
                            <span class="text-3xl">🔍</span>
                            <h3 class="mt-2 text-base font-bold text-gray-900">Parcel not found</h3>
                            <p class="mt-1 text-sm text-gray-500">No matching order or tracking number found for "<strong>{{ $tracking['query'] }}</strong>".</p>
                        </div>
                    @else
                        @include('buyer.partials.commerce-empty', [
                            'emptyPath' => 'm12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5',
                            'eyebrow' => 'DELIVERY UPDATES',
                            'note' => 'Find your order reference in My Orders.',
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
                <section data-panel="notifications" class="dash-panel commerce-page hidden space-y-5">
                    @include('buyer.partials.notifications')
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
        if (key === 'cart') { window.location.href = @json(route('cart.index')); return; }
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
        if (!valid.includes(key)) key = 'orders';
        document.querySelectorAll('[data-buyer-nav]').forEach(link => {
            if (link.dataset.buyerNav === key) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
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
        document.querySelectorAll('.order-filter').forEach(b => {
            b.classList.toggle('is-active', b.dataset.filter === f);
            b.setAttribute('aria-pressed', String(b.dataset.filter === f));
        });
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
        show(location.hash.replace('#', '') || 'orders', false);
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
