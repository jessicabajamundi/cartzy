<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Seller centre') · cartzy</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}?v={{ filemtime(public_path('css/seller.css')) }}">
</head>
<body>
@php($sellerMode = auth()->user()->isSeller())
<a class="skip-link" href="#main">Skip to content</a>
<div class="portal">
    <aside class="sidebar" id="seller-sidebar">
        <a class="brand" href="{{ route($sellerMode ? 'seller.dashboard' : 'buyer.dashboard') }}"><img src="{{ asset('images/logo-transparent.png') }}" alt="cartzy"><span>{{ $sellerMode ? 'SELLER CENTRE' : 'MESSAGES' }}</span></a>
        <div class="shop-card"><div class="avatar">{{ mb_substr($shop?->name ?? auth()->user()->name, 0, 1) }}</div><div><strong>{{ $shop?->name ?? auth()->user()->name }}</strong><small>{{ $sellerMode ? ($shop ? ucfirst($shop->status).' store' : 'Set up your store') : 'Buyer account' }}</small></div></div>
        <nav aria-label="{{ $sellerMode ? 'Seller' : 'Buyer' }} navigation">
            <span class="nav-label">WORKSPACE</span>
            @if($sellerMode)
                @foreach(['dashboard' => ['Overview','grid'], 'orders' => ['Orders','bag'], 'inventory' => ['Inventory','box'], 'courier' => ['Shipping','truck'], 'deliveries' => ['Deliveries','check'], 'chat' => ['Messages','chat'], 'feedback' => ['Reviews','star'], 'reports' => ['Sales reports','chart'], 'account' => ['Store settings','settings']] as $key => [$label,$icon])
                    <a href="{{ route('seller.'.$key) }}" class="nav-link {{ request()->routeIs('seller.'.$key.'*') ? 'active' : '' }}" @if(request()->routeIs('seller.'.$key.'*')) aria-current="page" @endif>@include('seller.partials.icon', ['name' => $icon])<span>{{ $label }}</span></a>
                @endforeach
            @else
                <a class="nav-link" href="{{ route('buyer.dashboard') }}">@include('seller.partials.icon', ['name' => 'grid'])My account</a>
                <a class="nav-link active" href="{{ route('buyer.messages') }}">@include('seller.partials.icon', ['name' => 'chat'])Messages</a>
            @endif
        </nav>
        <div class="sidebar-footer"><small>Signed in as</small><strong>{{ auth()->user()->name }}</strong><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Sign out &rarr;</button></form></div>
    </aside>
    <div class="workspace">
        <header class="topbar"><div class="inline"><button class="icon-button mobile-menu" id="menu-toggle" aria-controls="seller-sidebar" aria-expanded="false" aria-label="Toggle navigation">☰</button><span class="muted">{{ $sellerMode ? 'Seller workspace' : 'Buyer workspace' }}</span><span class="breadcrumb">/</span><strong>@yield('page_title', 'Overview')</strong></div><a class="button small secondary" href="{{ route('home') }}">Marketplace ↗</a></header>
        <main id="main">
            @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="notice error" role="alert"><strong>Please check your entries.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if($sellerMode && !$shop && !request()->routeIs('seller.account*'))<div class="notice">Finish setting up your store to start managing products. <a href="{{ route('seller.account') }}">Set up store &rarr;</a></div>@endif
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
</body>
</html>
