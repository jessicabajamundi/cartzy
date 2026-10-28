<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Overview') · Cartzy Admin</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<button class="sidebar-overlay" data-sidebar-close aria-label="Close navigation" hidden></button>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
    <a class="brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/logo-transparent.png') }}" alt="Cartzy"><span>ADMIN WORKSPACE</span></a>
    <nav>
        @php
            $groups = [
                'Workspace' => [
                    'dashboard' => ['Overview', 'grid'],
                    'products' => ['Seller products', 'box'],
                    'registrations' => ['Registrations', 'file'],
                    'id-checks' => ['ID checks', 'shield'],
                    'logistics' => ['Logistics partners', 'truck'],
                    'users' => ['User accounts', 'users'],
                ],
                'Operations' => [
                    'compliance' => ['Product compliance', 'shield'],
                    'archive' => ['Archive', 'archive'],
                    'disputes' => ['Disputes', 'shield'],
                    'commission' => ['Commission', 'wallet'],
                    'vouchers' => ['Vouchers', 'ticket'],
                    'shipping' => ['Shipping rates', 'truck'],
                    'reports' => ['Reports', 'chart'],
                    'activity-logs' => ['Activity logs', 'activity'],
                ],
                'Administration' => [
                    'chat' => ['Messages', 'message'],
                    'settings' => ['Platform settings', 'settings'],
                    'account' => ['My account', 'user'],
                ],
            ];
        @endphp
        @foreach ($groups as $group => $links)
            <p class="nav-label">{{ $group }}</p>
            @foreach ($links as $route => [$label, $icon])
                <a class="nav-link {{ request()->routeIs('admin.'.$route.'*') ? 'active' : '' }}" href="{{ route('admin.'.$route) }}" @if(request()->routeIs('admin.'.$route.'*')) aria-current="page" @endif>
                    @include('admin.partials.icon', ['name' => $icon]) <span>{{ $label }}</span>
                    @if($route === 'products' && !empty($adminPendingProductsCount))
                        <span class="badge warning" style="margin-left:auto;font-size:10px;padding:2px 7px;border-radius:10px;" title="{{ $adminPendingProductsCount }} pending review">{{ $adminPendingProductsCount }}</span>
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
        <div class="topbar-title"><button type="button" class="icon-button mobile-toggle" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open navigation">@include('admin.partials.icon', ['name' => 'menu'])</button><span>Administration <span class="crumb">/</span> <strong>@yield('page_title', 'Overview')</strong></span></div>
        <div class="topbar-actions">
            <a class="button subtle" href="{{ route('home') }}">View storefront @include('admin.partials.icon', ['name' => 'arrow'])</a>
            <a class="topbar-account" href="{{ route('admin.account') }}" aria-label="My account"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span></a>
            <a class="icon-button" href="{{ route('admin.chat') }}" aria-label="Notifications" title="Notifications">@include('admin.partials.icon', ['name' => 'bell'])</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="icon-button" aria-label="Log out" title="Log out">@include('admin.partials.icon', ['name' => 'logout'])</button>
            </form>
        </div>
    </header>
    <main id="main-content">
        <div class="page-heading"><div><p class="eyebrow">CARTZY / ADMIN</p><h1>@yield('page_title', 'Overview')</h1><p class="muted">@yield('page_description', 'Manage your marketplace with confidence.')</p></div><span class="date-label">{{ now()->timezone('Asia/Manila')->format('D, M j, Y') }}</span></div>
        @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
        @if(session('info'))<div class="notice" role="status">{{ session('info') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="notice danger" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
        <footer class="page-footer">Cartzy administration <span>All figures reflect recorded marketplace activity.</span></footer>
    </main>
</div>
<script src="{{ asset('js/admin/admin.js') }}?v={{ filemtime(public_path('js/admin/admin.js')) }}" defer></script>
@stack('scripts')
@include('partials.prevent-back-cache')
@include('partials.logout-confirm')
</body>
</html>
