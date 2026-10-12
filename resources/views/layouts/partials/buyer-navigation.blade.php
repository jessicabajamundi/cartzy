@php
    $navItems = [
        ['orders',        'My Orders',         'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['messages',      'Messages',          'M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z'],
        ['cart',          'Cart',              'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['wishlist',      'Wishlist',          'M4.3 6.3a4.5 4.5 0 016.4 0L12 7.6l1.3-1.3a4.5 4.5 0 116.4 6.4L12 20.4l-7.7-7.7a4.5 4.5 0 010-6.4z'],
        ['history',       'Order History',     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['track',         'Track Order',       'M17.7 16.7L13.4 20.9a2 2 0 01-2.8 0l-4.2-4.2a8 8 0 1111.3 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        ['notifications', 'Notifications',     'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0'],
    ];

    $buyerActiveTab = request()->routeIs('cart.*') ? 'cart' : (request()->routeIs('buyer.messages*') ? 'messages' : (request()->routeIs('buyer.dashboard') ? ($tab ?? request('tab', 'orders')) : ''));
    if ($buyerActiveTab === 'overview') $buyerActiveTab = 'orders';
    $buyerNavCounts = app(\App\Services\BuyerNavigationCounts::class)->forUser(Auth::user());
    $countLabels = ['orders' => 'total orders', 'messages' => 'unread messages', 'cart' => 'items in cart', 'wishlist' => 'saved products', 'history' => 'completed or cancelled orders', 'track' => 'ongoing orders', 'notifications' => 'unread notifications'];
@endphp
<nav class="buyer-top-nav" aria-label="Dashboard navigation" @if(Auth::check() && Auth::user()->isBuyer()) data-counts-url="{{ route('buyer.navigation-counts') }}" @endif>
    @foreach($navItems as [$key, $label, $icon])
        <a href="{{ $key === 'cart' ? route('cart.index') : route('buyer.dashboard', ['tab' => $key]) }}" class="buyer-top-link" data-buyer-nav="{{ $key }}" data-count-label="{{ $countLabels[$key] }}" data-nav-label="{{ $label }}" title="{{ $label }}: {{ $buyerNavCounts[$key] }} {{ $countLabels[$key] }}" aria-label="{{ $label }}: {{ $buyerNavCounts[$key] }} {{ $countLabels[$key] }}" @if($buyerActiveTab === $key) aria-current="page" @endif>
            <span class="buyer-top-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                <span @if($key === 'notifications') id="headerNotifBadge" @endif data-buyer-count="{{ $key }}" data-count="{{ $buyerNavCounts[$key] }}" class="buyer-top-badge {{ $key === 'cart' ? 'cart-badge-count' : '' }}" aria-hidden="true">{{ $buyerNavCounts[$key] > 99 ? '99+' : $buyerNavCounts[$key] }}</span>
            </span>
        </a>
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
.buyer-header .header-search { min-width:0; margin:0; }
.buyer-header .header-icons { gap:6px; }
@media(max-width:1100px) {
.buyer-header .header-main { flex-wrap:wrap; gap:10px; }
.buyer-header .header-actions,.buyer-header .header-icons { width:100%; }
.buyer-header .header-actions { flex-wrap:wrap; }
.buyer-top-nav { justify-content:flex-start; min-width:0; overflow-x:auto; padding:4px 2px; }
.buyer-top-link { padding:7px; flex-shrink:0; }
}
@media(max-width:480px) {
.buyer-top-nav { gap:0; }
.buyer-top-link { min-width:36px; min-height:44px; padding:7px; }
.buyer-header .header-icons { gap:6px; }
}
</style>
