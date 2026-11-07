{{--
    Login modal. Used two ways:
    - standalone (the /login pages): rendered open over a blurred backdrop; closing goes to the shop.
    - in the storefront layout for guests: hidden until a login link is clicked.
    People pick who they are signing in as (Buyer, Seller, Logistics, Rider, Admin) at the top.
    Riders sign in on the mobile app, so that choice points to the app instead of showing the form.
--}}
@php
    $standalone = $standalone ?? false;
    $current = $portal['key'];
    $roles = [
        'buyer' => ['label' => 'Buyer', 'icon' => '<path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 016 0"/>'],
        'seller' => ['label' => 'Seller', 'icon' => '<path d="M4 10l1.5-5h13L20 10"/><path d="M4 10a2.7 2.7 0 005.3 0 2.7 2.7 0 005.4 0 2.7 2.7 0 005.3 0"/><path d="M5 11v9h14v-9"/><path d="M10 20v-5h4v5"/>'],
        'logistics' => ['label' => 'Logistics', 'icon' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>'],
        'courier' => ['label' => 'Rider', 'icon' => '<circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l4-8h5l3 8"/><path d="M8 9h4"/>', 'app' => true],
        'admin' => ['label' => 'Admin', 'icon' => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>'],
    ];
    $portalData = collect($portals)->map(fn ($p, $key) => [
        'title' => $p['title'],
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
    $hintKey = session('portal_hint') ? collect($portals)->search(fn ($p) => $p['label'] === session('portal_hint.label')) : null;
@endphp

<div class="lm-overlay {{ $standalone ? 'is-open is-standalone' : '' }}" id="login-modal"
     data-login-url="{{ route('login') }}" data-portal="{{ $current }}"
     data-portals='@json($portalData)'
     @if($errors->any()) data-has-errors="true" @endif
     @unless($standalone) hidden @endunless>
    <div class="lm-backdrop" data-lm-close></div>

    <div class="lm-dialog" role="dialog" aria-modal="true" aria-labelledby="lm-title" aria-describedby="lm-subtitle">
        <div class="lm-panel">
            @if($standalone)
                <a href="{{ route('home') }}" class="lm-close" aria-label="Close and continue to the shop">
            @else
                <button type="button" class="lm-close" data-lm-close aria-label="Close login">
            @endif
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            @if($standalone) </a> @else </button> @endif

            <div class="lm-head">
                <img class="lm-logo" src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy">
                <h2 class="lm-title" id="lm-title">{{ $portal['title'] }}</h2>
                <p class="lm-subtitle" id="lm-subtitle">{{ $portal['subtitle'] }}</p>
            </div>

            <div class="lm-roles">
                <p class="lm-roles-label" id="lm-roles-label">Sign in as</p>
                <div class="lm-role-grid" role="radiogroup" aria-labelledby="lm-roles-label">
                    @foreach($roles as $key => $role)
                        @continue(! isset($portals[$key]))
                        <button type="button" class="lm-role" role="radio" data-lm-role="{{ $key }}"
                                aria-checked="{{ $key === $current ? 'true' : 'false' }}" tabindex="{{ $key === $current ? '0' : '-1' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $role['icon'] !!}</svg>
                            <span>{{ $role['label'] }}</span>
                            @if(! empty($role['app']))<small>In the app</small>@endif
                        </button>
                    @endforeach
                </div>
            </div>

            @if($standalone && session('success'))
                <div class="lm-alert lm-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($standalone && session('info'))
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
                                   placeholder="you@example.com" @if($standalone) autofocus @endif
                                   class="@error('email') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="lm-field">
                        <div class="lm-label-row">
                            <label for="lm-password">Password</label>
                            <a href="{{ $portalData[$current]['forgot'] ?? route('password.forgot') }}" class="lm-link" data-lm-forgot>Forgot password?</a>
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

                    <button type="submit" class="lm-submit">
                        <span data-lm-button>{{ $portal['button'] }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <div class="lm-divider"><span>or</span></div>

                <a href="{{ $portalData[$current]['google'] ?? route('auth.google') }}" class="lm-google" data-lm-google>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <p class="lm-foot" data-lm-register-row @unless($portal['register'] ?? null) hidden @endunless>
                    New to cartzy? <a href="{{ $portal['register'] ?? route('register') }}" class="lm-link" data-lm-register>Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>
