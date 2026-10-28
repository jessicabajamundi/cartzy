<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistics Hub | cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    {{-- Same shell, fonts, tokens and components as the admin portal; logistics-admin.css restyles each logistics page to match. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/chat-popup.css') }}?v={{ filemtime(public_path('css/chat-popup.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/logistics.css') }}?v={{ filemtime(public_path('css/logistics.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/logistics-workspace.css') }}?v={{ filemtime(public_path('css/logistics-workspace.css')) }}">
    @if(request()->routeIs('logistics.dashboard'))
        <link rel="stylesheet" href="{{ asset('css/logistics-dashboard.css') }}?v={{ filemtime(public_path('css/logistics-dashboard.css')) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/logistics-admin.css') }}?v={{ filemtime(public_path('css/logistics-admin.css')) }}">
</head>
<body class="logistics-admin {{ request()->routeIs('logistics.chat*') ? 'is-chat-page' : '' }}">
@php
    $logisticsUnreadCount = \App\Models\AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();
    $navGroups = [
        'Overview' => [
            'dashboard' => ['Dashboard', 'grid'],
        ],
        'Riders' => [
            'riders' => ['Rider management', 'users'],
        ],
        'Parcel operations' => [
            'pickups' => ['Pickup requests', 'truck'],
            'hub' => ['Hub desk', 'box'],
            'monitoring' => ['Delivery monitoring', 'activity'],
        ],
        'Analytics & comms' => [
            'reports' => ['Reports', 'chart'],
            'notifications' => ['Notifications', 'bell'],
            'chat' => ['Chat / messaging', 'message'],
        ],
        'Account' => [
            'account' => ['Account management', 'user'],
        ],
    ];
@endphp
<a class="skip-link" href="#main-content">Skip to content</a>
<button class="sidebar-overlay" data-sidebar-close aria-label="Close navigation" hidden></button>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Logistics navigation">
    <a class="brand" href="{{ route('logistics.dashboard') }}"><img src="{{ asset('images/logo-transparent.png') }}" alt="Cartzy"><span>LOGISTICS HUB</span></a>
    <nav>
        @foreach($navGroups as $group => $links)
            <p class="nav-label">{{ $group }}</p>
            @foreach($links as $route => [$label, $icon])
                {{-- The Chat link also opens the floating chat popup (logistics-chat-popup.js). --}}
                <a class="nav-link {{ request()->routeIs('logistics.'.$route.'*') ? 'active' : '' }}" href="{{ route('logistics.'.$route) }}" @if(request()->routeIs('logistics.'.$route.'*')) aria-current="page" @endif @if($route === 'chat') id="logistics-sidebar-chat-launch" data-chat-launch="logistics-chat" @endif>
                    @include('admin.partials.icon', ['name' => $icon]) <span>{{ $label }}</span>
                    @if($route === 'notifications' && $logisticsUnreadCount)
                        <span class="badge warning nav-count" aria-label="{{ $logisticsUnreadCount }} unread">{{ $logisticsUnreadCount > 99 ? '99+' : $logisticsUnreadCount }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
        <hr class="sidebar-logout-divider">
        <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="nav-link sidebar-logout-button">
                @include('admin.partials.icon', ['name' => 'logout']) <span>Log out</span>
            </button>
        </form>
    </nav>
</aside>
<div class="admin-shell">
    <header class="topbar">
        <div class="topbar-title"><button type="button" class="icon-button mobile-toggle" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open navigation">@include('admin.partials.icon', ['name' => 'menu'])</button><span>Logistics hub <span class="crumb">/</span> <strong>@yield('page_title', 'Dashboard')</strong></span></div>
        <div class="topbar-actions">
            <a class="button subtle" href="{{ route('home') }}">View storefront @include('admin.partials.icon', ['name' => 'arrow'])</a>
            <a class="topbar-account" href="{{ route('logistics.account') }}" aria-label="Account management" title="{{ Auth::user()->name ?? 'Hub manager' }}"><span class="avatar">{{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'H', 0, 1)) }}</span></a>
            <a class="icon-button topbar-bell" href="{{ route('logistics.notifications') }}" aria-label="Notifications, {{ $logisticsUnreadCount }} unread" title="Notifications">
                @include('admin.partials.icon', ['name' => 'bell'])
                @if($logisticsUnreadCount)<span class="topbar-bell-count" aria-hidden="true">{{ $logisticsUnreadCount > 99 ? '99+' : $logisticsUnreadCount }}</span>@endif
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="icon-button" aria-label="Log out" title="Log out">@include('admin.partials.icon', ['name' => 'logout'])</button>
            </form>
        </div>
    </header>
    <main id="main-content" class="logistics-content">
        @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="notice danger" role="alert">{{ session('error') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="notice danger" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
        <footer class="page-footer">Cartzy logistics hub <span>@yield('page_subtitle', 'Sorting & fulfillment center')</span></footer>
    </main>
</div>

<script>
    // Charts follow the admin palette and type.
    if (window.Chart) {
        Chart.defaults.font.family = '"Segoe UI", Arial, sans-serif';
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#6F6382';
        Chart.defaults.borderColor = '#E1DDE7';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;
        Chart.defaults.plugins.legend.labels.padding = 20;
    }
</script>
    {{-- Floating Message Bubble (Hidden by default, appears if clicked in sidebar or minimized) --}}
    @php
        $currentHubProvider = Auth::user()?->logisticsProvider;
        $unreadMessagesCount = $currentHubProvider
            ? \App\Models\LogisticsMessage::where('logistics_provider_id', $currentHubProvider->id)
                ->where('sender_type', '!=', 'hub')
                ->whereNull('read_at')
                ->count()
            : 0;
    @endphp
    <div class="logistics-chat-bubble-form" id="logistics-chat-bubble-wrap" hidden>
        <button type="button"
           class="chat-bubble"
           id="logistics-chat-bubble-btn"
           title="Click to chat here, double-click to open Messages"
           aria-controls="logistics-chat-popup"
           aria-expanded="false"
           aria-label="Open Chat / Messaging">
            <span class="chat-bubble-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="chat-bubble-badge" id="logistics-chat-bubble-badge" aria-label="{{ $unreadMessagesCount }} unread messages" {{ $unreadMessagesCount > 0 ? '' : 'hidden' }}>
                    {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                </span>
            </span>
        </button>
    </div>

    {{-- Floating Chat Popup Widget (Matching Screenshot UI) --}}
    <section class="pd-chat cw" id="logistics-chat-popup" role="dialog" aria-labelledby="logistics-chat-title"
             data-conversations-url="{{ route('logistics.popup.conversations') }}"
             data-inbox-url="{{ route('logistics.chat') }}"
             data-title="Cartzy Logistics Chat"
             data-subtitle="Pickups, deliveries, and rider updates"
             data-empty-text="Say hello! Ask the seller about sizes, stock, or shipping." hidden>
        <header class="pd-chat-head">
            <button class="pd-chat-icon" type="button" id="logistics-chat-back" aria-label="Back to conversations" title="Back" hidden>&larr;</button>
            <div>
                <strong id="logistics-chat-title">Cartzy Logistics Chat</strong>
                <small id="logistics-chat-subtitle">Pickups, deliveries, and rider updates</small>
            </div>
            <a class="pd-chat-icon" id="logistics-chat-expand" href="{{ route('logistics.chat') }}" aria-label="Open full chat page" title="Open full chat">&#x2197;</a>
            <button class="pd-chat-icon pd-chat-minimize" type="button" id="logistics-chat-minimize" aria-label="Minimize to bubble" title="Minimize">&minus;</button>
            <button class="pd-chat-icon" type="button" id="logistics-chat-close" aria-label="Close chat" title="Close">&times;</button>
        </header>
        <div class="cw-list" id="logistics-chat-list" aria-live="polite"></div>
        <div class="pd-chat-messages" id="logistics-chat-messages" aria-live="polite" hidden></div>
        <form class="pd-chat-compose" id="logistics-chat-compose" hidden>
            <label class="pd-chat-sr" for="logistics-chat-input">Message</label>
            <textarea id="logistics-chat-input" name="message" rows="1" maxlength="5000" placeholder="Ask about this product…" required></textarea>
            <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
        </form>
        <p class="pd-chat-error" id="logistics-chat-error" role="alert" hidden></p>
    </section>

    <script src="{{ asset('js/logistics-chat-popup.js') }}?v={{ filemtime(public_path('js/logistics-chat-popup.js')) }}" defer></script>
<script src="{{ asset('js/admin/admin.js') }}?v={{ filemtime(public_path('js/admin/admin.js')) }}" defer></script>
    @stack('scripts')
    @include('partials.prevent-back-cache')
@include('partials.logout-confirm')
</body>
</html>
