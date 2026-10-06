@extends('layouts.auth')

@section('title', 'Log In | cartzy')
@section('auth_visual', 'products')

@section('auth_form')

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Login to your account and continue shopping</p>

    @if(session('success'))
        <div class="alert alert-success">
            <svg style="width: 18px; height: 18px; flex-shrink: 0; color: #16A34A;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            <svg style="width: 18px; height: 18px; flex-shrink: 0; color: #6F6382;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf

        <label class="field-label" for="email">Email address</label>
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M2 7l10 7 10-7"/>
                </svg>
            </span>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder="Enter your email address"
                class="auth-input @error('email') error @enderror"
            >
        </div>

        <label class="field-label" for="login_password">Password</label>
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <path d="M8 11V7a4 4 0 018 0v4"/>
                </svg>
            </span>
            <input
                type="password"
                name="password"
                id="login_password"
                required
                placeholder="Enter your password"
                class="auth-input @error('password') error @enderror"
            >
            <button type="button" class="eye-btn" onclick="togglePassword('login_password', 'eye_icon_login')" tabindex="-1">
                <svg id="eye_icon_login" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        <div class="meta-row">
            <label class="remember-label">
                <input type="checkbox" name="remember">
                <span>Remember me</span>
            </label>
            <a href="{{ route('password.forgot') }}" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary">Sign In to Account</button>
    </form>

    <div class="divider">or continue with</div>

    <a href="{{ route('auth.google') }}" class="btn-google">
        <svg viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
        <span>Continue with Google</span>
    </a>

    <div class="auth-foot">
        Don't have an account?
        <a href="{{ route('register') }}">Sign up</a>
    </div>

    <!-- Quick Portal Demo Logins for Easy Evaluation -->
    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed #E2E8F0; text-align: center;">
        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; margin-bottom: 0.6rem;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            <span>Quick Portal Demo Logins (1-Click Access)</span>
        </span>
        <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('demo.login', 'seller') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 11px; font-weight: 700; background: #F5F3FF; color: #6D28D9; border: 1px solid #DDD6FE; padding: 0.4rem 0.75rem; border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;">
                <span style="display:inline-flex;align-items:center;gap:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Seller Centre</span>
            </a>
            <a href="{{ route('demo.login', 'admin') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 11px; font-weight: 700; background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; padding: 0.4rem 0.75rem; border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;">
                <span style="display:inline-flex;align-items:center;gap:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Super Admin</span>
            </a>
            <a href="{{ route('demo.login', 'courier') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 11px; font-weight: 700; background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; padding: 0.4rem 0.75rem; border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;">
                <span style="display:inline-flex;align-items:center;gap:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/><path d="M12 18v-5l-3-3h4l2 3h4"/><circle cx="9" cy="5" r="2"/></svg> Courier Hub</span>
            </a>
            <a href="{{ route('demo.login', 'logistics') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 11px; font-weight: 700; background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; padding: 0.4rem 0.75rem; border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;">
                <span style="display:inline-flex;align-items:center;gap:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> Logistics Hub</span>
            </a>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/auth/login.js') }}"></script>
<script>
    if (window.location.hash === '#forgot-password') {
        window.location.replace('{{ route("password.forgot") }}');
    }
    window.addEventListener('hashchange', function() {
        if (window.location.hash === '#forgot-password') {
            window.location.replace('{{ route("password.forgot") }}');
        }
    });
</script>
@endpush

@endsection
