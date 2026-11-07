{{--
    Login modal. Used two ways:
    - standalone (the /login pages): rendered open over a blurred backdrop; closing goes to the shop.
    - in the storefront layout for guests: hidden until a login link is clicked.
--}}
@php
    $standalone = $standalone ?? false;
    $isBuyerPortal = $portal['key'] === 'buyer';
    $registerUrl = $portal['register'] ?? null;
@endphp

<div class="lm-overlay {{ $standalone ? 'is-open is-standalone' : '' }}" id="login-modal"
     data-login-url="{{ route('login') }}" data-close-url="{{ route('home') }}"
     @if($errors->any()) data-has-errors="true" @endif
     @unless($standalone) hidden @endunless>
    <div class="lm-backdrop" data-lm-close></div>

    <div class="lm-dialog" role="dialog" aria-modal="true" aria-labelledby="lm-title" aria-describedby="lm-subtitle">
        <aside class="lm-visual" aria-hidden="true">
            <img src="{{ asset('images/auth/cartzy-collection.jpg') }}" width="1122" height="1402" alt="" @unless($standalone) loading="lazy" @endunless>
            <div class="lm-visual-copy">
                <p class="lm-eyebrow">{{ $portal['eyebrow'] ?? 'THE CARTZY COLLECTION' }}</p>
                <p class="lm-visual-heading">{{ $portal['heading'][0] ?? 'Good finds.' }}<br><span>{{ $portal['heading'][1] ?? 'Great beginnings.' }}</span></p>
            </div>
            <p class="lm-visual-caption">Fashion &middot; Tech &middot; Beauty &middot; Home</p>
        </aside>

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
                @unless($isBuyerPortal)<p class="lm-badge">{{ $portal['label'] }} login</p>@endunless
                <h2 class="lm-title" id="lm-title">{{ $portal['title'] }}</h2>
                <p class="lm-subtitle" id="lm-subtitle">{{ $portal['subtitle'] }}</p>
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
                        <a href="{{ session('portal_hint.url') }}" class="lm-hint">Go to the {{ session('portal_hint.label') }} login &rarr;</a>
                    @endif
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="lm-form" id="lm-form" novalidate>
                @csrf
                <input type="hidden" name="portal" value="{{ $portal['key'] }}">
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
                        <a href="{{ route('password.forgot', ['portal' => $portal['key']]) }}" class="lm-link">Forgot password?</a>
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
                    <span>{{ $portal['button'] }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <div class="lm-divider"><span>or</span></div>

            <a href="{{ route('auth.google', ['portal' => $portal['key']]) }}" class="lm-google">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </a>

            @if($registerUrl)
                <p class="lm-foot">New to cartzy? <a href="{{ $registerUrl }}" class="lm-link">Create an account</a></p>
            @endif

            <nav class="lm-portals" aria-label="Log in to another portal">
                <span>{{ $isBuyerPortal ? 'Not shopping?' : 'Other logins' }}</span>
                @foreach($portals as $key => $other)
                    @continue($key === $portal['key'])
                    <a href="{{ route($other['route']) }}" data-lm-skip>{{ $other['label'] }}</a>
                @endforeach
            </nav>
        </div>
    </div>
</div>
