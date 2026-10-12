@php
    $accountMessageUrl = match (auth()->user()->role) {
        'seller' => route('seller.chat'),
        'logistics' => route('logistics.chat'),
        default => route('buyer.dashboard', ['tab' => 'messages']),
    };
    $accountShortcuts = [
        ['Notifications', route('buyer.dashboard', ['tab' => 'notifications']), 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', $headerUnreadCount ?? 0],
        ['Messages', $accountMessageUrl, 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75', 0],
        ['Customer Service', 'mailto:support@cartzy.ph', 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155', 0],
        ['Wishlist', route('buyer.dashboard', ['tab' => 'wishlist']), 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z', 0],
        ['Cart', route('cart.index'), 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z', $cartCount ?? 0],
    ];
@endphp
<nav class="account-shortcuts" aria-label="Account shortcuts">
    @foreach($accountShortcuts as [$label, $url, $path, $count])
        <a class="account-shortcut" href="{{ $url }}" aria-label="{{ $label }}" title="{{ $label }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
            @if($count > 0)
                <span class="account-shortcut-badge {{ $label === 'Cart' ? 'cart-badge-count' : '' }}">{{ $count }}</span>
            @endif
        </a>
    @endforeach
</nav>
@once
<style>
.account-shortcuts { display:flex; align-items:center; gap:4px; flex-shrink:0; }
.account-shortcuts .account-shortcut { position:relative; display:flex; align-items:center; justify-content:center; width:40px; height:44px; flex-shrink:0; border-radius:12px; color:#6b7280; text-decoration:none; transition:color .15s,background .15s; }
.account-shortcuts .account-shortcut:hover { background:#F3EFF7; color:#564B68; }
.account-shortcuts .account-shortcut:focus-visible { outline:2px solid #6F6382; outline-offset:2px; }
.account-shortcuts svg { width:20px; height:20px; flex-shrink:0; }
.account-shortcut-badge { position:absolute; top:0; right:0; min-width:16px; height:16px; padding:0 3px; border-radius:20px; background:#8C5A50; color:#fff; font-size:9px; line-height:16px; text-align:center; box-shadow:0 0 0 2px #fff; }
.portal-header-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.workspace > .topbar { flex-wrap:wrap; height:auto; min-height:72px; padding-top:10px; padding-bottom:10px; gap:12px; }
.logistics-mobile-topbar { flex-wrap:wrap; gap:8px; }
.logistics-mobile-topbar .account-shortcuts { width:100%; justify-content:flex-end; }
@media(max-width:1100px) {
    .account-header .header-main { flex-wrap:wrap; gap:10px; }
    .account-header .header-search { min-width:0; margin:0; }
    .account-header .header-actions { width:100%; }
}
</style>
@endonce
