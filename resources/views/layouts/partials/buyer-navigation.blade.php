@php
    $navItems = [
        ['orders',        'Orders',            'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['messages',      'Messages',          'M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z'],
        ['cart',          'Cart',              'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['wishlist',      'Wishlist',          'M4.3 6.3a4.5 4.5 0 016.4 0L12 7.6l1.3-1.3a4.5 4.5 0 116.4 6.4L12 20.4l-7.7-7.7a4.5 4.5 0 010-6.4z'],
        ['notifications', 'Notifications',     'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0'],
    ];

    $buyerActiveTab = request()->routeIs('cart.*') ? 'cart' : (request()->routeIs('buyer.messages*') ? 'messages' : (request()->routeIs('buyer.dashboard') ? ($tab ?? request('tab', 'orders')) : ''));
    if (in_array($buyerActiveTab, ['overview', 'history', 'track'], true)) $buyerActiveTab = 'orders';
    $buyerNavCounts = app(\App\Services\BuyerNavigationCounts::class)->forUser(Auth::user());
    $countLabels = ['orders' => 'orders in progress', 'messages' => 'unread messages', 'cart' => 'items in cart', 'wishlist' => 'saved products', 'notifications' => 'unread notifications'];
@endphp
<nav class="buyer-top-nav" aria-label="Dashboard navigation" @if(Auth::check() && Auth::user()->isBuyer()) data-counts-url="{{ route('buyer.navigation-counts') }}" @endif>
    @foreach($navItems as [$key, $label, $icon])
        @if($key === 'orders')
            <div class="buyer-orders-dropdown-wrap" id="header-orders-dropdown-wrap">
                <details class="buyer-orders-dropdown" id="header-orders-dropdown">
                    <summary class="buyer-top-link" data-buyer-nav="orders" data-count-label="{{ $countLabels['orders'] }}" data-nav-label="Orders" title="Orders & Reviews: {{ $buyerNavCounts['orders'] }} {{ $countLabels['orders'] }}" aria-label="Orders & Reviews: {{ $buyerNavCounts['orders'] }} {{ $countLabels['orders'] }}" @if(in_array($buyerActiveTab, ['orders', 'reviews'], true)) aria-current="page" @endif>
                        <span class="buyer-top-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            <span data-buyer-count="orders" data-count="{{ $buyerNavCounts['orders'] }}" class="buyer-top-badge" aria-hidden="true">{{ $buyerNavCounts['orders'] > 99 ? '99+' : $buyerNavCounts['orders'] }}</span>
                        </span>
                    </summary>
                    <div class="buyer-orders-panel" id="header-orders-panel" role="menu">
                        <a href="{{ route('buyer.dashboard', ['tab' => 'orders']) }}" class="buyer-orders-menu-item @if($buyerActiveTab === 'orders') is-active @endif" data-buyer-nav-item="orders" role="menuitem">
                            <span class="buyer-orders-menu-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <span class="buyer-orders-menu-content">
                                <span class="buyer-orders-menu-title">My Orders</span>
                                <span class="buyer-orders-menu-sub">Track active orders & history</span>
                            </span>
                            @if($buyerNavCounts['orders'] > 0)
                                <span class="buyer-orders-menu-badge" aria-hidden="true">{{ $buyerNavCounts['orders'] }}</span>
                            @endif
                            <svg class="buyer-orders-menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </a>
                        <div class="buyer-orders-menu-separator" role="separator" aria-hidden="true"></div>
                        <a href="{{ route('buyer.dashboard', ['tab' => 'reviews']) }}" class="buyer-orders-menu-item @if($buyerActiveTab === 'reviews') is-active @endif" data-buyer-nav-item="reviews" role="menuitem">
                            <span class="buyer-orders-menu-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </span>
                            <span class="buyer-orders-menu-content">
                                <span class="buyer-orders-menu-title">Reviews & Ratings</span>
                                <span class="buyer-orders-menu-sub">Share feedback on products</span>
                            </span>
                            <svg class="buyer-orders-menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </details>
            </div>
        @else
            <a href="{{ $key === 'cart' ? route('cart.index') : route('buyer.dashboard', ['tab' => $key]) }}" class="buyer-top-link" data-buyer-nav="{{ $key }}" data-count-label="{{ $countLabels[$key] }}" data-nav-label="{{ $label }}" title="{{ $label }}: {{ $buyerNavCounts[$key] }} {{ $countLabels[$key] }}" aria-label="{{ $label }}: {{ $buyerNavCounts[$key] }} {{ $countLabels[$key] }}" @if($buyerActiveTab === $key) aria-current="page" @endif>
                <span class="buyer-top-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    <span @if($key === 'notifications') id="headerNotifBadge" @endif data-buyer-count="{{ $key }}" data-count="{{ $buyerNavCounts[$key] }}" class="buyer-top-badge {{ $key === 'cart' ? 'cart-badge-count' : '' }}" aria-hidden="true">{{ $buyerNavCounts[$key] > 99 ? '99+' : $buyerNavCounts[$key] }}</span>
                </span>
            </a>
        @endif
    @endforeach
</nav>
<style>
.buyer-top-nav { display:flex; align-items:center; gap:0; }
.buyer-top-link { display:flex; position:relative; align-items:center; justify-content:center; width:36px; min-width:36px; min-height:40px; padding:7px; border-radius:12px; color:#756b80; text-decoration:none; transition:background .15s,color .15s; }
.buyer-top-link:hover,.buyer-top-link:active,.buyer-top-link[aria-current="page"] { background:transparent; box-shadow:none; color:#564B68; }
.buyer-top-link { -webkit-tap-highlight-color:transparent; }
.buyer-top-link:focus-visible { outline:2px solid #6F6382; outline-offset:2px; }
.buyer-top-icon { position:relative; display:flex; }
.buyer-top-badge { position:absolute; top:-7px; right:-10px; min-width:16px; height:16px; padding:0 3px; border-radius:20px; background:#8C5A50; color:white; font-size:9px; line-height:16px; text-align:center; box-shadow:0 0 0 2px white; }
.buyer-top-badge[data-buyer-count] { display:block !important; }
.buyer-top-badge[data-buyer-count][data-count="0"] { display:none !important; }

/* Orders Dropdown Menu */
.buyer-orders-dropdown-wrap { position:relative; display:inline-flex; }
.buyer-orders-dropdown { position:relative; }
.buyer-orders-dropdown > summary { list-style:none; cursor:pointer; user-select:none; }
.buyer-orders-dropdown > summary::-webkit-details-marker { display:none; }
.buyer-orders-panel {
    position:absolute; top:calc(100% + 8px); left:0; z-index:60;
    width:260px; max-width:calc(100vw - 24px); padding:6px;
    border-radius:16px; border:1px solid #E8E5ED; background:#fff;
    box-shadow:0 16px 40px -10px rgba(40,33,51,0.18), 0 4px 12px rgba(40,33,51,0.04);
    font-family:'Lato','Segoe UI',sans-serif; text-align:left;
    animation:buyerOrdersFadeIn .16s ease-out;
}
@keyframes buyerOrdersFadeIn {
    from { opacity:0; transform:translateY(-4px) scale(0.98); }
    to { opacity:1; transform:translateY(0) scale(1); }
}
.buyer-orders-menu-item {
    display:flex; align-items:center; gap:11px; width:100%; padding:9px 11px;
    border-radius:10px; text-decoration:none; color:#4E465A;
    transition:background .15s, color .15s; box-sizing:border-box;
}
.buyer-orders-menu-item:hover { background:#F5F3F8; color:#564B68; }
.buyer-orders-menu-item.is-active { background:#F1EFF5; color:#564B68; font-weight:600; }
.buyer-orders-menu-icon {
    display:flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px; background:#F5F3F8;
    color:#6F6382; flex-shrink:0; transition:background .15s, color .15s;
}
.buyer-orders-menu-item:hover .buyer-orders-menu-icon,
.buyer-orders-menu-item.is-active .buyer-orders-menu-icon { background:#E8E3EE; color:#564B68; }
.buyer-orders-menu-content { display:flex; flex-direction:column; min-width:0; flex:1; }
.buyer-orders-menu-title { font-size:13.5px; font-weight:700; line-height:1.25; color:#282133; }
.buyer-orders-menu-sub { font-size:11px; font-weight:400; color:#81768C; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.buyer-orders-menu-badge { padding:1px 7px; border-radius:12px; background:#8C5A50; color:#fff; font-size:10px; font-weight:700; line-height:16px; flex-shrink:0; }
.buyer-orders-menu-arrow { width:13px; height:13px; color:#B2AABA; flex-shrink:0; margin-left:auto; transition:transform .15s; }
.buyer-orders-menu-item:hover .buyer-orders-menu-arrow { transform:translateX(2px); color:#6F6382; }
.buyer-orders-menu-separator { height:1px; background:#F0EDF3; margin:4px 6px; }

.buyer-header .header-search { min-width:0; margin:0; }
.buyer-header .header-icons { gap:6px; }
@media(max-width:1100px) {
.buyer-header .header-main { flex-wrap:wrap; gap:10px; }
.buyer-header .header-actions,.buyer-header .header-icons { width:100%; }
.buyer-header .header-actions { flex-wrap:wrap; }
.buyer-top-nav { justify-content:flex-start; flex-wrap:wrap; min-width:0; padding:4px 2px; }
.buyer-top-link { padding:7px; flex-shrink:0; }
}
@media(max-width:480px) {
.buyer-top-nav { gap:0; }
.buyer-top-link { min-width:36px; min-height:44px; padding:7px; }
.buyer-header .header-icons { gap:6px; }
.buyer-orders-panel { left:auto; right:0; }
}
</style>
<script>
(() => {
    const wrap = document.getElementById('header-orders-dropdown-wrap');
    if (!wrap) return;
    const dropdown = wrap.querySelector('#header-orders-dropdown');
    if (!dropdown) return;
    let closeTimer;
    const cancelClose = () => clearTimeout(closeTimer);

    wrap.addEventListener('pointerenter', (e) => {
        if (e.pointerType !== 'mouse') return;
        cancelClose();
        dropdown.open = true;
    });

    wrap.addEventListener('pointerleave', (e) => {
        if (e.pointerType !== 'mouse') return;
        closeTimer = setTimeout(() => {
            if (!dropdown.contains(document.activeElement)) dropdown.open = false;
        }, 180);
    });

    document.addEventListener('pointerdown', (e) => {
        if (!wrap.contains(e.target)) dropdown.open = false;
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && dropdown.open) {
            dropdown.open = false;
            dropdown.querySelector('summary')?.focus();
        }
    });

    dropdown.addEventListener('focusout', (e) => {
        if (e.relatedTarget && !dropdown.contains(e.relatedTarget)) dropdown.open = false;
    });
})();
</script>
