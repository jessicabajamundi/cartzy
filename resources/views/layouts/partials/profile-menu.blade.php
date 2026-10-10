@php
    $profileUser = Auth::user();
    $profileInitial = mb_strtoupper(mb_substr($profileUser->name ?? 'U', 0, 1));
    $profileLinks = [];
    if ($profileUser->isAdmin()) {
        $profileLinks[] = ['admin.dashboard', 'Admin Dashboard', 'grid'];
    } elseif ($profileUser->isSeller()) {
        $profileLinks[] = ['seller.dashboard', 'Seller Centre', 'store'];
    } elseif ($profileUser->isCourier()) {
        $profileLinks[] = ['courier.dashboard', 'Rider Hub', 'box'];
    }
    $profileLinks = array_merge($profileLinks, [
        ['buyer.dashboard', 'My cartzy', 'grid'],
    ]);
    $profileIcons = [
        'grid' => 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        'store' => 'M3 10h18L19 3H5L3 10Z M5 10v11h14V10 M9 21v-7h6v7',
        'box' => 'm12 3 9 5-9 5-9-5 9-5Z M3 8v9l9 5 9-5V8 M12 13v9 M7.5 5.5l9 5',
        'user' => 'M19 21v-2a7 7 0 0 0-14 0v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z',
        'bag' => 'M5 7h14l1 14H4L5 7Z M9 7V5a3 3 0 0 1 6 0v2',
    ];
@endphp

<details class="profile-menu" id="header-profile-menu">
    <summary class="profile-menu-trigger overflow-hidden" aria-label="Account options" aria-controls="header-profile-panel" title="Account options">
        @if($profileUser->avatar_url)
            <img src="{{ $profileUser->avatar_url }}" alt="{{ $profileUser->name }}" class="w-full h-full object-cover rounded-full">
        @else
            {{ $profileInitial }}
        @endif
    </summary>
    <div class="profile-menu-panel" id="header-profile-panel">
        <div class="profile-menu-header">
            @if($profileUser->avatar_url)
                <img src="{{ $profileUser->avatar_url }}" alt="{{ $profileUser->name }}" class="profile-menu-avatar object-cover overflow-hidden" aria-hidden="true">
            @else
                <span class="profile-menu-avatar" aria-hidden="true">{{ $profileInitial }}</span>
            @endif
            <div class="profile-menu-identity">
                <p class="profile-menu-name">{{ $profileUser->name }}</p>
                <span class="profile-menu-role">{{ ucfirst($profileUser->role) }} account</span>
            </div>
        </div>
        <nav class="profile-menu-links" aria-label="Your account">
            @foreach($profileLinks as [$profileRoute, $profileLabel, $profileIcon])
                <a class="profile-menu-link" href="{{ route($profileRoute) }}" @if(request()->routeIs($profileRoute)) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $profileIcons[$profileIcon] }}"/></svg>
                    <span>{{ $profileLabel }}</span>
                    <svg class="profile-menu-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </a>
            @endforeach
        </nav>
        <div class="profile-menu-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="profile-menu-link profile-menu-logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9"/></svg>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </div>
</details>

@once
<style>
    .profile-menu { position: relative; }
    .profile-menu-trigger {
        display: flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 50%; cursor: pointer;
        list-style: none; background: #6F6382; color: #fff; font-size: 12px; font-weight: 700;
        transition: background 150ms, box-shadow 150ms;
    }
    .profile-menu-trigger::-webkit-details-marker { display: none; }
    .profile-menu-trigger:hover, .profile-menu[open] > summary { background: #564B68; box-shadow: 0 0 0 3px #F1EFF5; }
    .profile-menu-panel {
        position: absolute; right: 0; top: calc(100% + 12px); z-index: 60;
        width: 280px; max-width: calc(100vw - 32px); max-height: calc(100dvh - 90px); overflow-y: auto;
        padding: 0; border: 1px solid #E8E5ED; border-radius: 16px;
        background: #fff; color: #282133; text-align: left;
        font-family: 'Lato', 'Segoe UI', sans-serif; font-size: 14px; line-height: 1.5;
        box-shadow: 0 16px 48px -12px rgba(40,33,51,.22), 0 4px 12px rgba(40,33,51,.04);
    }
    .profile-menu-header { display: flex; align-items: center; gap: 12px; padding: 20px; border-bottom: 1px solid #F0EDF3; }
    .profile-menu-avatar {
        display: flex; align-items: center; justify-content: center; flex: 0 0 42px; height: 42px;
        background: #F1EFF5; color: #564B68; border: 1px solid #E8E3EE; border-radius: 12px; font-size: 17px; font-weight: 700;
    }
    .profile-menu-identity { min-width: 0; }
    .profile-menu-name { margin: 0; font-size: 15px; line-height: 1.4; font-weight: 700; overflow-wrap: anywhere; }
    .profile-menu-role { display: block; margin-top: 4px; color: #81768C; font-size: 12px; font-weight: 400; }
    .profile-menu-links { padding: 8px; }
    .profile-menu-link {
        display: flex; align-items: center; gap: 12px; width: 100%; min-height: 44px; padding: 10px 12px;
        border: 0; border-radius: 8px; background: transparent; color: #4E465A; text-decoration: none;
        font: inherit; font-weight: 400; text-align: left; cursor: pointer; transition: background 150ms, color 150ms;
    }
    .profile-menu-link svg { width: 18px; height: 18px; flex-shrink: 0; color: #8A8095; }
    .profile-menu-link .profile-menu-chevron { width: 14px; height: 14px; margin-left: auto; color: #B2AABA; }
    .profile-menu-link:hover, .profile-menu-link[aria-current="page"] { background: #F5F3F8; color: #564B68; }
    .profile-menu-link[aria-current="page"] { font-weight: 700; }
    .profile-menu-link[aria-current="page"] svg { color: #6F6382; }
    .profile-menu-footer { padding: 8px; border-top: 1px solid #F0EDF3; }
    .profile-menu-logout, .profile-menu-logout svg { color: #A14848; }
    .profile-menu-logout:hover { background: #FFF3F3; color: #8C3535; }
    .profile-menu-trigger:focus-visible, .profile-menu-link:focus-visible { outline: 2px solid #91879E; outline-offset: 2px; }
    @media (max-width: 640px) {
        .profile-menu { position: static; }
        .profile-menu-panel { top: 100%; right: 12px; width: 280px; max-width: calc(100vw - 24px); margin-top: 8px; }
    }
    @media (prefers-reduced-motion: reduce) { .profile-menu-trigger, .profile-menu-link { transition: none; } }
</style>
<script>
    (() => {
        const menu = document.getElementById('header-profile-menu');
        document.addEventListener('click', event => {
            if (!menu.contains(event.target)) menu.open = false;
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && menu.open) {
                menu.open = false;
                menu.querySelector('summary').focus();
            }
        });
        menu.addEventListener('focusout', event => {
            if (event.relatedTarget && !menu.contains(event.relatedTarget)) menu.open = false;
        });
    })();
</script>
@endonce
