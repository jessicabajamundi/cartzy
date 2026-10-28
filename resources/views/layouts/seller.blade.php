<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Seller centre') · cartzy</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}?v={{ filemtime(public_path('css/seller.css')) }}">
    @if(request()->routeIs('seller.dashboard'))
        <link rel="stylesheet" href="{{ asset('css/seller-dashboard.css') }}?v={{ filemtime(public_path('css/seller-dashboard.css')) }}">
    @endif
    @if(auth()->user()->isSeller())
        <link rel="stylesheet" href="{{ asset('css/chat-popup.css') }}?v={{ filemtime(public_path('css/chat-popup.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/seller-chat-popups.css') }}?v={{ filemtime(public_path('css/seller-chat-popups.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/seller-numerals.css') }}?v={{ filemtime(public_path('css/seller-numerals.css')) }}">
    @endif
</head>
<body class="{{ request()->routeIs('seller.dashboard') ? 'seller-overview' : '' }}">
@php($sellerMode = auth()->user()->isSeller())
<a class="skip-link" href="#main">Skip to content</a>
<div class="portal">
    <aside class="sidebar {{ $sellerMode ? 'seller-admin-sidebar' : '' }}" id="seller-sidebar">
        <a class="brand" href="{{ route($sellerMode ? 'seller.dashboard' : 'buyer.dashboard') }}"><img src="{{ asset('images/logo-transparent.png') }}" alt="cartzy"><span>{{ $sellerMode ? 'SELLER WORKSPACE' : 'MESSAGES' }}</span></a>
        @unless($sellerMode)
        <div class="shop-card"><div class="avatar">{{ mb_substr($shop?->name ?? auth()->user()->name, 0, 1) }}</div><div><strong>{{ $shop?->name ?? auth()->user()->name }}</strong><small>Buyer account</small></div></div>
        @endunless
        <nav aria-label="{{ $sellerMode ? 'Seller' : 'Buyer' }} navigation">
            @unless($sellerMode)<span class="nav-label">WORKSPACE</span>@endunless
            @if($sellerMode)
                @foreach(['dashboard' => ['Overview','grid'], 'products' => ['Products','tag'], 'inventory' => ['Inventory','box'], 'archive' => ['Archive','archive'], 'orders' => ['Orders','bag'], 'courier' => ['Shipping','truck'], 'deliveries' => ['Deliveries','check'], 'vouchers' => ['Vouchers','ticket'], 'feedback' => ['Reviews','star'], 'reports' => ['Sales reports','chart'], 'chat' => ['Messages','chat-round'], 'logistics' => ['Logistics chat','truck'], 'account' => ['My account','user']] as $key => [$label,$icon])
                    @php($navActive = request()->routeIs('seller.'.$key.'*'))
                    <a href="{{ route('seller.'.$key) }}" class="nav-link {{ $navActive ? 'active' : '' }}" @if($navActive) aria-current="page" @endif @if(in_array($key, ['chat', 'logistics'], true)) data-chat-launch="{{ $key === 'chat' ? 'sm' : 'lc' }}" @endif>@include('seller.partials.icon', ['name' => $icon])<span>{{ $label }}</span></a>
                    @if($key === 'dashboard')
                        @include('seller.partials.workspace-notifications')
                    @endif
                @endforeach
            @else
                <a class="nav-link" href="{{ route('buyer.dashboard') }}">@include('seller.partials.icon', ['name' => 'grid'])My account</a>
                <a class="nav-link active" href="{{ route('buyer.messages') }}">@include('seller.partials.icon', ['name' => 'chat'])Messages</a>
            @endif
        @unless($sellerMode)</nav>@endunless
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="width: 100%; margin: 0;">
                @csrf
                <button type="submit" class="nav-link sidebar-logout-btn" aria-label="Log out" title="Log out">
                    @include('seller.partials.icon', ['name' => 'logout', 'size' => 20])
                    <span>Log out</span>
                </button>
            </form>
        </div>
        @if($sellerMode)</nav>@endif
    </aside>
    <div class="workspace">
        <header class="topbar">
            <div class="inline">
                <button class="icon-button mobile-menu" id="menu-toggle" aria-controls="seller-sidebar" aria-expanded="false" aria-label="Toggle navigation">☰</button>
                <span class="muted">{{ $sellerMode ? 'Seller workspace' : 'Buyer workspace' }}</span>
                <span class="breadcrumb">/</span>
                <strong>@yield('page_title', 'Overview')</strong>
            </div>
            @unless($sellerMode) @include('layouts.partials.account-navigation') @endunless
            <div class="topbar-actions" style="display: flex; align-items: center; gap: 10px;">
                <a class="button small secondary" href="{{ route('home') }}">Marketplace ↗</a>
                @if($sellerMode)
                    @php($sellerNotifData = \App\Services\SellerNotifications::feed(auth()->user()))
                    <a class="icon-button seller-header-notif-btn" href="{{ route('seller.notifications') }}" aria-label="Notifications" title="Notifications" style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--line); background: var(--surface); color: var(--muted); text-decoration: none; transition: var(--transition);">
                        @include('seller.partials.icon', ['name' => 'bell'])
                        <span class="badge" data-notification-count style="position: absolute; top: -5px; right: -5px; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 9px; background: var(--rose-primary); color: #fff; font-size: 10px; font-weight: 700; line-height: 18px; text-align: center; border: 2px solid #fff;" @if(($sellerNotifData['notificationCount'] ?? 0) <= 0) hidden @endif>{{ ($sellerNotifData['notificationCount'] ?? 0) > 0 ? $sellerNotifData['notificationCount'] : '' }}</span>
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="margin: 0; display: inline-flex;">
                    @csrf
                    <button class="icon-button" type="submit" aria-label="Log out" title="Log out" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--line); background: var(--rose-soft); color: var(--rose-ink); cursor: pointer; transition: var(--transition);">
                        @include('seller.partials.icon', ['name' => 'logout', 'size' => 18])
                    </button>
                </form>
            </div>
        </header>
        <main id="main">
            @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="notice error" role="alert"><strong>Please check your entries.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if($sellerMode && (auth()->user()->status !== 'active' || ($shop && !$shop->isApproved())))
                <div class="notice" style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-left: 5px solid #F59E0B; color: #92400E; display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 22px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 20px;">🔒</span>
                        <div>
                            <strong>Account Pending Administrator Approval:</strong> Your seller account is currently under review. You can view your dashboard and store settings, but adding products and selling are locked until your business permits and documents are approved.
                        </div>
                    </div>
                    <a href="{{ route('seller.account') }}" class="button small" style="white-space: nowrap; background: #92400E; color: #fff;">Upload / Check documents &rarr;</a>
                </div>
            @elseif($sellerMode && !$shop && !request()->routeIs('seller.account*'))
                <div class="notice">Finish setting up your store to start managing products. <a href="{{ route('seller.account') }}">Set up store &rarr;</a></div>
            @endif
            @if($sellerMode && ($stockAlertCount ?? 0) > 0 && !request()->routeIs('seller.inventory'))
                @php($stockLowCount = $stockAlertCount - ($stockOutCount ?? 0))
                <dialog class="restock-dialog" id="restock-dialog" aria-labelledby="restock-title" aria-describedby="restock-summary" data-restock-autoopen="{{ ($restockPrompt ?? false) ? 'true' : 'false' }}">
                    <div class="restock-panel">
                    <header class="restock-head">
                        <span class="restock-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg></span>
                        <div class="restock-titles">
                            <h2 id="restock-title">Time to restock</h2>
                            <p id="restock-summary">{{ $stockAlertCount }} {{ $stockAlertCount === 1 ? 'item has' : 'items have' }} reached the low-stock limit. Restock now so buyers can keep ordering.</p>
                        </div>
                        <button type="button" class="restock-close" data-restock-close aria-label="Close restock reminder"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                    </header>
                    <div class="restock-stats">
                        @if(($stockOutCount ?? 0) > 0)<span class="restock-stat is-out"><strong>{{ $stockOutCount }}</strong> out of stock</span>@endif
                        @if($stockLowCount > 0)<span class="restock-stat is-low"><strong>{{ $stockLowCount }}</strong> running low</span>@endif
                    </div>
                    <ul class="restock-list">
                        @foreach($stockAlerts as $stockAlert)
                            <li>
                                <a class="restock-item" href="{{ route('seller.inventory', ['search' => $stockAlert->sku]) }}">
                                    <span class="restock-thumb">@if($stockAlert->product->coverImage || $stockAlert->image_url)<img src="{{ $stockAlert->resolved_image_url }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($stockAlert->product->name, 0, 1)) }}@endif</span>
                                    <span class="restock-item-text">
                                        <strong>{{ $stockAlert->product->name }}</strong>
                                        <small>{{ $stockAlert->name }}@if($stockAlert->sku) &middot; SKU {{ $stockAlert->sku }}@endif</small>
                                    </span>
                                    <span class="restock-pill {{ $stockAlert->stock > 0 ? 'is-low' : 'is-out' }}">{{ $stockAlert->stock > 0 ? $stockAlert->stock.' left' : 'Out of stock' }}</span>
                                    <svg class="restock-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <footer class="restock-actions">
                        <small>@if($stockAlertCount > $stockAlerts->count())Showing {{ $stockAlerts->count() }} of {{ $stockAlertCount }} items @else&nbsp;@endif</small>
                        <div><button type="button" class="button secondary" data-restock-close>Remind me later</button><a class="button" href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}">Manage stock &rarr;</a></div>
                    </footer>
                    </div>
                </dialog>
            @endif
            @yield('content')
        </main>
        <footer class="page-footer">cartzy {{ $sellerMode ? 'Seller Centre' : 'Messages' }} <span>Built for your everyday business.</span></footer>
    </div>
</div>
<script>
document.getElementById('menu-toggle').addEventListener('click', function () {
    const expanded = document.getElementById('seller-sidebar').classList.toggle('open');
    this.setAttribute('aria-expanded', String(expanded));
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') { document.getElementById('seller-sidebar').classList.remove('open'); document.getElementById('menu-toggle').setAttribute('aria-expanded', 'false'); }
});
</script>
@stack('scripts')
@if($sellerMode)<script src="{{ asset('js/seller-restock.js') }}?v={{ filemtime(public_path('js/seller-restock.js')) }}" defer></script>@endif
@if($sellerMode)<script src="{{ asset('js/seller-notifications.js') }}?v={{ filemtime(public_path('js/seller-notifications.js')) }}" defer></script>@endif
@if($sellerMode)
    {{-- Bubbles stay hidden until opened from the Messages / Logistics chat sidebar links. --}}
    <div class="seller-chat-bubbles" role="region" aria-label="Chat bubbles">
        @include('seller.partials.chat-popup', ['id' => 'sm', 'label' => 'Messages', 'title' => 'Messages', 'subtitle' => 'Buyer questions and order chats', 'conversationsUrl' => route('seller.popup.conversations'), 'inboxUrl' => route('seller.chat'), 'emptyText' => 'No conversations yet. Buyer questions and order chats will appear here.'])
        @include('seller.partials.chat-popup', ['id' => 'lc', 'label' => 'Logistics', 'title' => 'Logistics chat', 'subtitle' => 'Message a logistics hub', 'conversationsUrl' => route('seller.logistics.conversations'), 'inboxUrl' => route('seller.logistics'), 'emptyText' => 'No logistics hubs are available yet. Approved hubs will appear here.'])
    </div>
    <script src="{{ asset('js/seller-chat-popups.js') }}?v={{ filemtime(public_path('js/seller-chat-popups.js')) }}" defer></script>
    <script src="{{ asset('js/seller-numerals.js') }}?v={{ filemtime(public_path('js/seller-numerals.js')) }}" defer></script>
@endif
@include('partials.prevent-back-cache')
@include('partials.logout-confirm')
</body>
</html>
