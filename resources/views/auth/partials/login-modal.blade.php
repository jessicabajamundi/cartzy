{{--
    Login modal, in two steps:
      1. "choose" — people pick who they sign in as (Buyer, Seller, Logistics, Rider, Admin).
      2. "login"  — the sign-in form for that role (Riders get a pointer to the mobile app instead).
    Used two ways:
    - standalone (the /login pages): rendered open over a blurred backdrop; closing goes to the shop.
    - in the storefront layout for guests: hidden until a login link is clicked.
--}}
@php
    $standalone = $standalone ?? false;
    $current = $portal['key'];
    // The generic /login page (and the storefront modal) start on the role picker;
    // a role's own login page, or a failed attempt, goes straight to the form.
    $startStep = $startStep ?? (($current === 'buyer' && ! $errors->any()) ? 'choose' : 'login');

    $roles = [
        'buyer' => [
            'label' => 'Buyer', 'description' => 'Shop, track orders and save favourites',
            'icon' => '<path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 016 0"/>',
        ],
        'seller' => [
            'label' => 'Seller', 'description' => 'Manage your store, products and orders',
            'icon' => '<path d="M4 10l1.5-5h13L20 10"/><path d="M4 10a2.7 2.7 0 005.3 0 2.7 2.7 0 005.4 0 2.7 2.7 0 005.3 0"/><path d="M5 11v9h14v-9"/><path d="M10 20v-5h4v5"/>',
        ],
        'logistics' => [
            'label' => 'Logistics', 'description' => 'Run pickups and riders for your hub',
            'icon' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>',
        ],
        'courier' => [
            'label' => 'Rider', 'description' => 'Deliver parcels using the cartzy Rider app', 'app' => true,
            'icon' => '<circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l4-8h5l3 8"/><path d="M8 9h4"/>',
        ],
        'admin' => [
            'label' => 'Admin', 'description' => 'Oversee the platform. Authorized staff only',
            'icon' => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        ],
    ];

    $portalData = collect($portals)->map(fn ($p, $key) => [
        'label' => $roles[$key]['label'] ?? $p['label'],
        'icon' => $roles[$key]['icon'] ?? '',
        'title' => $p['title'],
        'heading' => match ($key) {
            'buyer' => 'Welcome back',
            'logistics' => 'Welcome, Partner',
            default => 'Welcome, '.($roles[$key]['label'] ?? $p['label']),
        },
        'subtitle' => $p['subtitle'],
        'button' => $p['button'],
        'register' => $p['register'] ?? null,
        'url' => route($p['route']),
        'forgot' => route('password.forgot', ['portal' => $key]),
        'google' => route('auth.google', ['portal' => $key]),
    ]);
    if ($portalData->has('courier')) {
        $portalData['courier'] = ['subtitle' => 'Deliveries and routes are in the cartzy Rider app'] + $portalData['courier'];
    }
    $active = $portalData[$current] ?? $portalData['buyer'];
    $hintKey = session('portal_hint') ? collect($portals)->search(fn ($p) => $p['label'] === session('portal_hint.label')) : null;

    $closeIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
@endphp

<div class="lm-overlay {{ $standalone ? 'is-open is-standalone' : '' }}" id="login-modal"
     data-login-url="{{ route('login') }}" data-portal="{{ $current }}" data-step="{{ $startStep }}"
     data-portals='@json($portalData)'
     @if($errors->any()) data-has-errors="true" @endif
     @unless($standalone) hidden @endunless>
    <div class="lm-backdrop" data-lm-close></div>

    @include('auth.partials.cozy-peek')

    {{-- ── Step 1: choose who you are signing in as ── --}}
    <section class="lm-dialog lm-step" data-lm-step="choose" role="dialog" aria-modal="true" aria-labelledby="lm-choose-title"
             @unless($startStep === 'choose') hidden @endunless>
        <div class="lm-panel">
            @if($standalone)
                <a href="{{ route('home') }}" class="lm-close" aria-label="Close and continue to the shop">{!! $closeIcon !!}</a>
            @else
                <button type="button" class="lm-close" data-lm-close aria-label="Close login">{!! $closeIcon !!}</button>
            @endif

            <div class="lm-head">
                <img class="lm-logo" src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy">
                <h2 class="lm-title" id="lm-choose-title">Sign in to cartzy</h2>
                <p class="lm-subtitle">Choose how you use cartzy to continue</p>
            </div>

            @if($standalone && session('success'))
                <div class="lm-alert lm-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($standalone && session('info'))
                <div class="lm-alert lm-alert-info" role="status">{{ session('info') }}</div>
            @endif

            <p class="lm-roles-label">Continue as...</p>
            <ul class="lm-role-list">
                @foreach($roles as $key => $role)
                    @continue(! isset($portals[$key]))
                    <li>
                        <button type="button" class="lm-role" data-lm-role="{{ $key }}">
                            <span class="lm-role-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $role['icon'] !!}</svg>
                            </span>
                            <span class="lm-role-text">
                                <span class="lm-role-name">{{ $role['label'] }} @if(! empty($role['app']))<small>In the app</small>@endif</span>
                                <span class="lm-role-desc">{{ $role['description'] }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>

            @if(! empty($portals['buyer']['register']))
                <p class="lm-foot">New to cartzy? <a href="{{ $portals['buyer']['register'] }}" class="lm-link" data-lm-skip>Create an account</a></p>
            @endif
        </div>
    </section>

    {{-- ── Step 2: sign in to that account ── --}}
    <section class="lm-dialog lm-step" data-lm-step="login" role="dialog" aria-modal="true" aria-labelledby="lm-title" aria-describedby="lm-subtitle"
             @unless($startStep === 'login') hidden @endunless>
        <div class="lm-panel">
            @if($standalone)
                <a href="{{ route('home') }}" class="lm-close" aria-label="Close and continue to the shop">{!! $closeIcon !!}</a>
            @else
                <button type="button" class="lm-close" data-lm-close aria-label="Close login">{!! $closeIcon !!}</button>
            @endif

            <div class="lm-head">
                {{-- The role badge doubles as the way back to the role picker --}}
                <button type="button" class="lm-role-badge" data-lm-back title="Sign in as someone else">
                    <span class="lm-role-badge-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-lm-badge-icon>{!! $active['icon'] !!}</svg>
                    </span>
                    <span data-lm-badge-label>{{ $active['label'] }}</span>
                    <span class="lm-role-badge-switch">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4L3 8l4 4"/><path d="M3 8h13"/><path d="M17 20l4-4-4-4"/><path d="M21 16H8"/></svg>
                        Switch
                    </span>
                </button>
                <h2 class="lm-title" id="lm-title">{{ $active['heading'] }}</h2>
                <p class="lm-subtitle" id="lm-subtitle">{{ $active['subtitle'] }}</p>
            </div>

            @if($standalone && $startStep === 'login' && session('success'))
                <div class="lm-alert lm-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($standalone && $startStep === 'login' && session('info'))
                <div class="lm-alert lm-alert-info" role="status">{{ session('info') }}</div>
            @endif
            @if($errors->any())
                <div class="lm-alert lm-alert-error" role="alert">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    @if(session('portal_hint'))
                        <a href="{{ session('portal_hint.url') }}" class="lm-hint" @if($hintKey) data-lm-role="{{ $hintKey }}" @endif>Sign in as {{ session('portal_hint.label') }} instead &rarr;</a>
                    @endif
                </div>
            @endif

            {{-- Riders: the app is where they sign in --}}
            <div class="lm-app-note" data-lm-section="app" @unless($current === 'courier') hidden @endunless>
                <span class="lm-app-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></svg>
                </span>
                <p class="lm-app-title">Riders sign in on the cartzy app</p>
                <p class="lm-app-text">Your deliveries, routes and earnings live in the Rider app. Install it on your Android phone and log in there.</p>
                <a href="{{ route('login.courier') }}" class="lm-submit lm-app-button" data-lm-skip>
                    <span>Get the Rider app</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12M6 10l6 6 6-6M5 20h14"/></svg>
                </a>
                @if(! empty($portals['courier']['register']))
                    <p class="lm-foot">Want to deliver with us? <a href="{{ $portals['courier']['register'] }}" class="lm-link" data-lm-skip>Apply as a rider</a></p>
                @endif
            </div>

            <div data-lm-section="form" @if($current === 'courier') hidden @endif>
                <form action="{{ route('login.submit') }}" method="POST" class="lm-form" id="lm-form" novalidate>
                    @csrf
                    <input type="hidden" name="portal" value="{{ $current }}" data-lm-portal-input>
                    @unless($standalone)
                        <input type="hidden" name="redirect_to" value="{{ '/'.ltrim(request()->path(), '/').(request()->getQueryString() ? '?'.request()->getQueryString() : '') }}">
                    @endunless

                    <div class="lm-field">
                        <label for="lm-email">Email address</label>
                        <div class="lm-input-wrap">
                            <svg class="lm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                            <input type="email" name="email" id="lm-email" value="{{ old('email') }}" required autocomplete="email"
                                   placeholder="you@example.com" @if($standalone && $startStep === 'login') autofocus @endif
                                   class="@error('email') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="lm-field">
                        <div class="lm-label-row">
                            <label for="lm-password">Password</label>
                            <a href="{{ $active['forgot'] }}" class="lm-link" data-lm-forgot>Forgot password?</a>
                        </div>
                        <div class="lm-input-wrap">
                            <svg class="lm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                            <input type="password" name="password" id="lm-password" required autocomplete="current-password"
                                   placeholder="Enter your password" class="@error('password') is-invalid @enderror">
                            <button type="button" class="lm-eye" data-lm-toggle-password aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="lm-remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Keep me signed in</span>
                    </label>

                    <button type="submit" class="lm-submit lm-submit-main">
                        <span class="lm-submit-shine" aria-hidden="true"></span>
                        <span class="lm-submit-spinner" aria-hidden="true"></span>
                        <span class="lm-submit-label" data-lm-button>{{ $active['button'] }}</span>
                        <svg class="lm-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <div class="lm-divider"><span>or</span></div>

                <a href="{{ $active['google'] }}" class="lm-google" data-lm-google>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <p class="lm-foot" data-lm-register-row @unless($active['register']) hidden @endunless>
                    New to cartzy? <a href="{{ $active['register'] ?? route('register') }}" class="lm-link" data-lm-register data-lm-skip>Create an account</a>
                </p>
            </div>
        </div>
    </section>
</div>

@include('auth.partials.forgot-password-modal')

@if(session('account_removed'))
    <dialog id="account-removed-modal" class="account-removed-dialog" open aria-labelledby="account-removed-title" aria-describedby="account-removed-reason">
        <div class="account-removed-card">
            <button type="button" class="account-removed-close" data-dismiss-account-removed aria-label="Close dialog">&times;</button>
            <div class="account-removed-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h2 id="account-removed-title">Account Removed</h2>
            <p class="account-removed-sub">
                @if(session('removed_user_name'))
                    Hello <strong>{{ session('removed_user_name') }}</strong>, this account has been removed by platform administration.
                @else
                    This account has been removed by platform administration.
                @endif
            </p>

            <div class="account-removed-reason-card">
                <span class="account-removed-reason-label">Reason for removal:</span>
                <p id="account-removed-reason" class="account-removed-reason-text">
                    {{ session('removal_reason') ?? 'Account policy violation or terms non-compliance.' }}
                </p>
            </div>

            <p class="account-removed-help">
                Access to this account has been disabled. If you have questions or believe this was done in error, please reach out to our platform support team.
            </p>

            <div class="account-removed-actions">
                <button type="button" class="account-removed-btn" data-dismiss-account-removed autofocus>Understood</button>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rmModal = document.getElementById('account-removed-modal');
            if (rmModal) {
                if (typeof rmModal.showModal === 'function' && !rmModal.open) {
                    rmModal.showModal();
                }
                document.querySelectorAll('[data-dismiss-account-removed]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        if (typeof rmModal.close === 'function') rmModal.close();
                        rmModal.remove();
                    });
                });
                rmModal.addEventListener('click', function (e) {
                    const rect = rmModal.getBoundingClientRect();
                    if (
                        e.clientX < rect.left ||
                        e.clientX > rect.right ||
                        e.clientY < rect.top ||
                        e.clientY > rect.bottom
                    ) {
                        if (typeof rmModal.close === 'function') rmModal.close();
                        rmModal.remove();
                    }
                });
            }
        });
    </script>
@endif
