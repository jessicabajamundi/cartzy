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
                        <div><p class="commerce-eyebrow">YOUR SHOPPING JOURNEY</p><h2>Orders</h2><p>Follow your active orders, track deliveries and look back at past purchases, all in one place.</p></div>
                    </header>
                    @if($showTracking && $tracking)
                        <div id="order-tracking" class="order-tracking-panel">
                            <div class="order-tracking-heading"><div><p class="commerce-eyebrow">DELIVERY UPDATES</p><h3>Tracking {{ $tracking['order'] ?? $tracking['query'] ?? '' }}</h3></div><a href="{{ route('buyer.dashboard', ['tab' => 'orders']) }}" class="order-tracking-close">✕ Close tracking</a></div>
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

                                {{-- Realtime OpenStreetMap Live Tracking Container --}}
                                @if(!empty($tracking['map']))
                                    <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#FAF9FB] px-4 py-3 sm:px-5">
                                            <div class="flex items-center gap-2">
                                                <span class="relative flex h-3 w-3">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                                                </span>
                                                <span class="text-xs font-black tracking-wider uppercase text-emerald-800">OpenStreetMap GPS Live</span>
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">REALTIME</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs font-medium text-gray-600">
                                                <div>Speed: <strong class="text-gray-900" id="osm-live-speed">{{ $tracking['map']['speed_kmh'] }} km/h</strong></div>
                                                <div>Remaining: <strong class="text-gray-900" id="osm-live-dist">{{ $tracking['map']['distance_remaining_km'] }} km</strong></div>
                                                <div>ETA: <strong class="text-[#8C5A50]" id="osm-live-eta">~{{ $tracking['map']['eta_minutes'] }} mins</strong></div>
                                                <button type="button" id="osm-recenter-btn" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-bold text-gray-700 shadow-2xs hover:bg-gray-50 transition cursor-pointer">
                                                    <span>🎯 Recenter Rider</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="realtime-osm-map" style="height: 380px; width: 100%; z-index: 1;"></div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-4 py-3 sm:px-5 text-xs text-gray-500">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span>🏍️ Rider: <strong class="text-gray-900">{{ $tracking['map']['rider']['name'] }}</strong> ({{ $tracking['map']['rider']['vehicle'] }})</span>
                                                <span>📞 <a href="tel:{{ $tracking['map']['rider']['phone'] }}" class="font-bold text-[#564B68] hover:underline">{{ $tracking['map']['rider']['phone'] }}</a></span>
                                            </div>
                                            <div class="text-[11px] text-gray-400">
                                                Free live map via &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap</a> contributors
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <p class="tracking-explainer mt-4">Realtime OpenStreetMap GPS tracking active. Courier location updates continuously along the delivery route.</p>
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
                        @endif
                        </div>
                    @endif
                    @if(count($orders))
                        <div class="orders-filter-bar" id="orderFilters" role="group" aria-label="Filter orders by status">
                            <button type="button" data-filter="all" class="order-filter rounded-xl px-4 py-2 text-sm font-bold cursor-pointer">All <span class="ml-1 text-xs opacity-70">{{ count($orders) }}</span></button>
                            @foreach(['In progress' => ['to_pay', 'to_ship', 'to_receive'], 'Past' => ['completed', 'cancelled']] as $groupLabel => $groupKeys)
                                <span class="order-filter-group" aria-hidden="true">{{ $groupLabel }}</span>
                                @foreach($groupKeys as $key)
                                    <button type="button" data-filter="{{ $key }}" class="order-filter rounded-xl px-4 py-2 text-sm font-bold cursor-pointer">{{ $statusMap[$key]['label'] }} <span class="ml-1 text-xs opacity-70">{{ $statusCounts[$key] ?? 0 }}</span></button>
                                @endforeach
                            @endforeach
                        </div>
                        <label class="orders-search">
                            <span class="sr-only">Search orders</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                            <input type="search" id="orderSearch" placeholder="Search by order ID, store or product" autocomplete="off">
                        </label>
                        <div class="space-y-4" id="orderList">
                            @foreach($orders as $o)
                                @include('buyer.partials.order-card', ['o' => $o, 'statusMap' => $statusMap, 'peso' => $peso, 'btnPrimary' => $btnPrimary, 'btnGhost' => $btnGhost])
                            @endforeach
                        </div>
                        <div id="orderEmpty" class="hidden orders-filter-empty" role="status"><strong>No matching orders.</strong><p>Choose another status or clear your search to see your other purchases.</p></div>
                        <nav id="orderPagination" class="orders-pagination hidden" aria-label="Orders pagination">
                            <p id="orderPageSummary" class="order-pagination-summary" role="status" aria-live="polite"></p>
                            <div class="order-pagination-controls">
                                <button type="button" id="orderPrevious" class="order-pagination-arrow" aria-label="Previous page" title="Previous page">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 15px; height: 15px;"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                <span id="orderPageNumber" class="order-pagination-current-page"></span>
                                <button type="button" id="orderNext" class="order-pagination-arrow" aria-label="Next page" title="Next page">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 15px; height: 15px;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                            <span class="order-pagination-spacer" aria-hidden="true"></span>
                        </nav>
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
                                        <button type="button" onclick="removeFromWishlist({{ json_encode($w['ref']) }}, this)" title="Remove from wishlist" aria-label="Remove {{ $w['name'] }} from wishlist" class="wishlist-remove">
                                            <svg aria-hidden="true" fill="currentColor" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                                        </button>
                                    </div>
                                    <div class="wishlist-body">
                                        <h3 class="wishlist-name">{{ $w['name'] }}</h3>
                                        <p class="wishlist-variant">{{ $w['variation'] ?? 'Standard' }}</p>
                                        <p class="wishlist-price">{{ $peso($w['price']) }}</p>
                                        <div class="wishlist-actions">
                                        <button type="button"
                                                @disabled(!$w['in_stock'])
                                                @if($w['in_stock']) onclick="moveToCart('{{ $w['ref'] }}', {{ json_encode($w['ref']) }}, {{ json_encode($w['name']) }}, {{ $w['price'] }}, {{ json_encode($w['image']) }}, {{ json_encode($w['variation']) }})" @endif
                                                class="wishlist-cart btn-blend"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l3 12h10l3-9H6M9 20h.01M18 20h.01"/></svg>{{ $w['in_stock'] ? 'Move to cart' : 'Out of Stock' }}</button>
                                        <button type="button" onclick="removeFromWishlist({{ json_encode($w['ref']) }}, this)" aria-label="Remove {{ $w['name'] }} from wishlist" title="Remove from wishlist" class="wishlist-delete"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/></svg></button>
                                        </div>
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

                {{-- ---------- NOTIFICATIONS ---------- --}}
                <section data-panel="notifications" class="dash-panel commerce-page hidden space-y-5">
                    @include('buyer.partials.notifications')
                </section>

                {{-- ---------- VOUCHERS ---------- --}}
                <section data-panel="vouchers" class="dash-panel commerce-page hidden space-y-5" aria-labelledby="vouchers-title">
                    @include('buyer.partials.vouchers', ['vouchers' => $vouchers])
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
    .orders-pagination:not(.hidden) { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 18px 0; color: #81738e; font-size: 13px; }
    .order-pagination-summary { flex: 1 1 0; min-width: 0; text-align: left; margin: 0; }
    .order-pagination-controls { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 12px; }
    .order-pagination-spacer { flex: 1 1 0; min-width: 0; }
    .order-pagination-arrow { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; padding: 0; border: 1px solid #e1dde7; border-radius: 10px; background: #fff; color: #564b68; font-weight: 700; cursor: pointer; transition: all .15s ease; }
    .order-pagination-arrow:hover:not(:disabled) { background: #f1edf5; border-color: #9b899f; }
    .order-pagination-arrow:disabled { opacity: .35; cursor: not-allowed; }
    .order-pagination-arrow:focus-visible { outline: 2px solid #6f6382; outline-offset: 3px; }
    .order-pagination-current-page { font-weight: 600; color: #564b68; white-space: nowrap; padding: 0 4px; }
    @media (max-width: 600px) {
        .orders-pagination:not(.hidden) { flex-direction: column; align-items: center; gap: 10px; }
        .order-pagination-summary { text-align: center; }
        .order-pagination-spacer { display: none; }
    }
    #orderFilters { scroll-margin-top: 120px; }
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
            if (wasAddresses) window.showAccountSection?.('addresses');
        }
        if (key === 'history') {
            // Order History now lives inside Orders as the "Past" filters.
            key = 'orders';
            filterOrders('completed');
        }
        if (key === 'track') key = 'orders'; // Tracking is shown at the top of the Orders tab.
        if (!valid.includes(key)) key = 'orders';
        document.querySelectorAll('[data-buyer-nav]').forEach(link => {
            if (link.dataset.buyerNav === key || (link.dataset.buyerNav === 'orders' && key === 'reviews')) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        document.querySelectorAll('[data-buyer-nav-item]').forEach(item => {
            item.classList.toggle('is-active', item.dataset.buyerNavItem === key);
        });
        panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== key));
        navs.forEach(n => n.classList.toggle('is-active', n.dataset.nav === key));
        navs.forEach(n => n.classList.toggle('group', true));
        const url = new URL(location.href);
        url.pathname = new URL(@json(route('buyer.dashboard'))).pathname;
        url.searchParams.set('tab', key);
        if (key === 'orders' && orderFilter !== 'all') url.searchParams.set('status', orderFilter); else url.searchParams.delete('status');
        url.hash = key;
        history.replaceState(null, '', url);
        const trackingPanel = key === 'orders' && document.getElementById('order-tracking');
        if (trackingPanel) trackingPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        else window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    let orderFilter = 'all';
    let orderSearch = '';
    let orderPage = 1;
    const ordersPerPage = 10;
    function renderOrders() {
        const cards = Array.from(document.querySelectorAll('#orderList [data-status]'));
        const matching = cards.filter(card => (orderFilter === 'all' || card.dataset.status === orderFilter)
            && (!orderSearch || card.textContent.toLowerCase().includes(orderSearch)));
        const pages = Math.max(1, Math.ceil(matching.length / ordersPerPage));
        orderPage = Math.min(Math.max(1, orderPage), pages);
        const start = (orderPage - 1) * ordersPerPage;
        cards.forEach(card => card.classList.add('hidden'));
        matching.slice(start, start + ordersPerPage).forEach(card => card.classList.remove('hidden'));
        const emptyEl = document.getElementById('orderEmpty');
        if (emptyEl) emptyEl.classList.toggle('hidden', matching.length > 0);
        const pagination = document.getElementById('orderPagination');
        if (pagination) {
            pagination.classList.toggle('hidden', matching.length <= ordersPerPage);
            document.getElementById('orderPageSummary').textContent = matching.length ? `Showing ${start + 1}–${Math.min(start + ordersPerPage, matching.length)} of ${matching.length} orders` : '';
            document.getElementById('orderPageNumber').textContent = `Page ${orderPage} of ${pages}`;
            document.getElementById('orderPrevious').disabled = orderPage === 1;
            document.getElementById('orderNext').disabled = orderPage === pages;
        }
    }
    function filterOrders(f) {
        if (!document.querySelector(`.order-filter[data-filter="${f}"]`)) f = 'all';
        orderFilter = f;
        orderPage = 1;
        renderOrders();
        document.querySelectorAll('.order-filter').forEach(b => {
            b.classList.toggle('is-active', b.dataset.filter === f);
            b.setAttribute('aria-pressed', String(b.dataset.filter === f));
        });
        const url = new URL(location.href);
        if (url.searchParams.get('tab') === 'orders' || !url.searchParams.has('tab')) {
            if (f === 'all') url.searchParams.delete('status'); else url.searchParams.set('status', f);
            history.replaceState(null, '', url);
        }
    }
    document.getElementById('orderSearch')?.addEventListener('input', function () {
        orderSearch = this.value.trim().toLowerCase();
        orderPage = 1;
        renderOrders();
    });
    [['orderPrevious', -1], ['orderNext', 1]].forEach(([id, step]) => {
        document.getElementById(id)?.addEventListener('click', () => {
            orderPage += step;
            renderOrders();
            document.getElementById('orderFilters')?.scrollIntoView({ block: 'start' });
        });
    });

    navs.forEach(n => n.addEventListener('click', () => show(n.dataset.nav, true)));
    document.querySelectorAll('[data-buyer-nav-item]').forEach(b => b.addEventListener('click', (e) => {
        e.preventDefault();
        show(b.dataset.buyerNavItem, true);
        const dropdown = document.getElementById('header-orders-dropdown');
        if (dropdown) dropdown.open = false;
    }));
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

    filterOrders(new URL(location.href).searchParams.get('status') || 'all');
    const start = location.hash.replace('#', '') || @json($tab);
    show(start, false);

    window.addEventListener('hashchange', () => {
        show(location.hash.replace('#', '') || 'orders', false);
    });
})();

window.removeFromWishlist = async function (ref, trigger) {
    if (typeof window.appConfirm === 'function') {
        const ok = await window.appConfirm('Remove this product from your wishlist?', {
            title: 'Remove from Wishlist',
            kind: 'delete',
            btnText: 'Yes, remove'
        });
        if (!ok) return;
    } else if (!confirm('Remove this product from your wishlist?')) {
        return;
    }
    const card = trigger?.closest('.wishlist-card');
    const buttons = card ? card.querySelectorAll('.wishlist-remove, .wishlist-delete') : [];
    buttons.forEach(b => { b.disabled = true; });
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
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(data => {
        if (!data.success) throw new Error();
        const left = Number.isInteger(data.count) ? data.count : null;
        if (!card || left === 0) {
            window.location.href = '{{ route('buyer.dashboard', ['tab' => 'wishlist']) }}';
            return;
        }
        card.classList.add('is-removing');
        setTimeout(() => card.remove(), 260);
        if (left !== null) {
            const label = document.querySelector('.wishlist-heading .commerce-count');
            if (label) label.textContent = left + (left === 1 ? ' saved item' : ' saved items');
            window.updateBuyerNavCount?.('wishlist', left);
        }
        window.showToast?.('Removed from your wishlist.', 'success');
    })
    .catch(() => {
        buttons.forEach(b => { b.disabled = false; });
        window.showToast ? window.showToast('Could not remove item. Please try again.', 'error') : alert('Could not remove item. Please try again.');
    });
};

window.moveToCart = async function (ref, id, name, price, image, variation) {
    if (typeof window.addToCart !== 'function') return;
    const added = await window.addToCart(id, name, price, image, variation);
    if (!added) return;
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
        }, 900); // let the fly-to-cart animation finish first
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

@if($showTracking && !empty($tracking['map']))
(function() {
    function initOsmMap() {
        if (!window.L) {
            setTimeout(initOsmMap, 150);
            return;
        }
        const mapData = @json($tracking['map']);
        const mapContainer = document.getElementById('realtime-osm-map');
        if (!mapContainer || !mapData) return;

        const route = mapData.route || [];
        let riderPos = [mapData.rider.lat, mapData.rider.lng];
        let progress = (mapData.progress_percent || 75) / 100;
        const isLive = mapData.is_live;

        const map = L.map('realtime-osm-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView(riderPos, 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
        }).addTo(map);

        const createMarkerIcon = (emoji, bg) => L.divIcon({
            html: `<div style="background:${bg}; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; border:2.5px solid #fff; box-shadow:0 3px 8px rgba(0,0,0,0.25);">${emoji}</div>`,
            className: 'osm-custom-icon',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
            popupAnchor: [0, -17]
        });

        const riderIcon = L.divIcon({
            html: '<div style="position:relative; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">' +
                    '<span style="position:absolute; width:40px; height:40px; border-radius:50%; background:rgba(16,185,129,0.35); animation:ping 1.5s cubic-bezier(0,0,0.2,1) infinite;"></span>' +
                    '<div style="position:relative; background:#10B981; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; border:2.5px solid #fff; box-shadow:0 4px 12px rgba(16,185,129,0.4);">🏍️</div>' +
                  '</div>',
            className: 'osm-rider-icon',
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });

        if (mapData.origin) {
            L.marker([mapData.origin.lat, mapData.origin.lng], { icon: createMarkerIcon('🏬', '#564B68') })
                .addTo(map)
                .bindPopup(`<strong>Origin Store: ${mapData.origin.name}</strong><br>${mapData.origin.address}`);
        }

        if (mapData.hub) {
            L.marker([mapData.hub.lat, mapData.hub.lng], { icon: createMarkerIcon('🏢', '#2C3E50') })
                .addTo(map)
                .bindPopup(`<strong>Logistics Hub: ${mapData.hub.name}</strong><br>${mapData.hub.address}`);
        }

        if (mapData.destination) {
            L.marker([mapData.destination.lat, mapData.destination.lng], { icon: createMarkerIcon('📍', '#C08B7F') })
                .addTo(map)
                .bindPopup(`<strong>Destination: ${mapData.destination.name}</strong><br>${mapData.destination.address}`);
        }

        const riderMarker = L.marker(riderPos, { icon: riderIcon, zIndexOffset: 1000 }).addTo(map)
            .bindPopup(`<strong>${mapData.rider.name}</strong><br>${mapData.rider.vehicle}<br>Speed: ${mapData.speed_kmh} km/h`);

        if (route && route.length > 1) {
            const polyline = L.polyline(route, {
                color: '#564B68',
                weight: 5,
                opacity: 0.8,
                dashArray: '8, 8',
                lineJoin: 'round'
            }).addTo(map);

            map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
        }

        const recenterBtn = document.getElementById('osm-recenter-btn');
        if (recenterBtn) {
            recenterBtn.addEventListener('click', () => {
                map.flyTo(riderPos, 15, { animate: true, duration: 1.2 });
                riderMarker.openPopup();
            });
        }

        if (isLive && route.length > 1) {
            const speedEl = document.getElementById('osm-live-speed');
            const distEl = document.getElementById('osm-live-dist');
            const etaEl = document.getElementById('osm-live-eta');

            setInterval(() => {
                if (progress < 0.98) {
                    progress += 0.003;
                    const totalSegments = route.length - 1;
                    const scaled = progress * totalSegments;
                    const seg = Math.min(totalSegments - 1, Math.floor(scaled));
                    const factor = scaled - seg;
                    const p1 = route[seg];
                    const p2 = route[seg + 1];

                    riderPos = [
                        p1[0] + (p2[0] - p1[0]) * factor,
                        p1[1] + (p2[1] - p1[1]) * factor
                    ];
                    riderMarker.setLatLng(riderPos);

                    const remDist = Math.max(0.1, ((1 - progress) * 12.8)).toFixed(1);
                    const liveSpeed = 28 + Math.floor(Math.random() * 8);
                    const liveEta = Math.max(2, Math.round((remDist / liveSpeed) * 60));

                    if (speedEl) speedEl.textContent = `${liveSpeed} km/h`;
                    if (distEl) distEl.textContent = `${remDist} km`;
                    if (etaEl) etaEl.textContent = `~${liveEta} mins`;
                }
            }, 2500);
        }
    }

    if (!document.getElementById('leaflet-css')) {
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }
    if (!document.getElementById('leaflet-js')) {
        const script = document.createElement('script');
        script.id = 'leaflet-js';
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = initOsmMap;
        document.body.appendChild(script);
    } else {
        initOsmMap();
    }
})();
@endif

// Global GCash QR Payment Modal Helper
window.openGcashPayModal = function(orderRef, amount) {
    document.getElementById('gcash-modal-ref').textContent = orderRef;
    document.getElementById('gcash-modal-amount').textContent = '₱' + amount;
    const qrImg = document.getElementById('gcash-modal-qr');
    if (qrImg) {
        qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?data=00020101021226580014ph.com.gcash011891755522780210CARTZYPH520459995303608540' + amount.replace(/,/g, '') + '5802PH5915CartzyCommerce6006Manila62080504' + orderRef + '6304&size=200x200&margin=4';
    }
    window.currentGcashOrderRef = orderRef;
    document.getElementById('gcash-pay-modal').classList.remove('hidden');
};

window.closeGcashPayModal = function() {
    document.getElementById('gcash-pay-modal').classList.add('hidden');
};

window.submitGcashPayment = function() {
    if (!window.currentGcashOrderRef) return;
    const form = document.getElementById('pay-form-' + window.currentGcashOrderRef);
    if (form) {
        form.submit();
    } else {
        window.location.reload();
    }
};
</script>

<!-- GCash QR Payment Modal -->
<div id="gcash-pay-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-gray-100 text-center">
        <button type="button" onclick="closeGcashPayModal()" class="absolute right-4 top-4 text-gray-400 hover:text-gray-700 text-xl font-bold cursor-pointer">✕</button>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007DFE]/10 text-[#007DFE] text-xs font-black tracking-wider uppercase mb-3">
            <span>GCash QR Payment</span>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-1">Scan &amp; Pay via GCash</h3>
        <p class="text-xs text-gray-500 mb-4">Order Ref: <strong id="gcash-modal-ref" class="text-gray-800 font-mono"></strong></p>
        
        <div class="inline-block p-3 rounded-2xl border-2 border-[#007DFE] bg-white shadow-md mb-4">
            <img id="gcash-modal-qr" src="" alt="GCash QR Code" class="w-48 h-48 mx-auto block" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'192\' height=\'192\' viewBox=\'0 0 100 100\'><rect width=\'100\' height=\'100\' fill=\'%23f0f7ff\'/><text x=\'50\' y=\'55\' font-size=\'12\' font-weight=\'bold\' fill=\'%23007DFE\' text-anchor=\'middle\'>GCASH QR</text></svg>';">
        </div>
        
        <div class="rounded-xl bg-[#F0F7FF] p-3 text-left mb-5">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500">Merchant:</span>
                <strong class="text-gray-800">Cartzy Official Store</strong>
            </div>
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500">GCash Mobile:</span>
                <strong class="font-mono text-[#007DFE]">0917-555-2278</strong>
            </div>
            <div class="flex justify-between text-sm font-extrabold pt-2 border-t border-blue-100">
                <span class="text-gray-700">Amount Due:</span>
                <strong id="gcash-modal-amount" class="text-[#007DFE] text-base"></strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="closeGcashPayModal()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">Cancel</button>
            <button type="button" onclick="submitGcashPayment()" class="flex-1 py-3 px-4 rounded-xl bg-[#007DFE] hover:bg-blue-600 text-white text-xs font-extrabold shadow-md hover:shadow-lg transition cursor-pointer">I Have Paid via GCash</button>
        </div>
    </div>
</div>
@endsection
