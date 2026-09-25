<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v={{ filemtime(public_path('images/favicon.png')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lato', sans-serif !important;
            background-color: #ffffff !important;
            background-image: none !important;
            min-height: 100vh;
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 60px 16px 40px;
            color: #111827 !important;
            -webkit-font-smoothing: antialiased;
        }

        .auth-card {
            background: #ffffff !important;
            border-radius: 4px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04) !important;
            border: 1px solid #f3f4f6;
            padding: 48px 56px 40px !important;
            width: 100%;
            max-width: 720px;
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 36px;
            text-decoration: none;
        }
        .auth-logo-img {
            height: 64px;
            width: auto;
            max-width: 260px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        .auth-logo:hover .auth-logo-img {
            transform: scale(1.04);
        }

        .auth-top-bar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        .btn-back-shop {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.86rem;
            font-weight: 700;
            color: #6F6382;
            text-decoration: none;
            padding: 7px 14px;
            border-radius: 9999px;
            background: #FAF8FC;
            border: 1px solid #ECE7F2;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }
        .btn-back-shop:hover {
            color: #564B68;
            background: #F1EFF5;
            border-color: #D6CFE2;
            transform: translateX(-3px);
            box-shadow: 0 2px 8px rgba(111, 99, 130, 0.12);
        }
        .btn-back-shop svg {
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }
        .btn-back-shop:hover svg {
            transform: translateX(-2px);
        }

        .auth-title    { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.35rem; font-weight: 700; color: #111; margin-bottom: 8px; text-align: center; letter-spacing: -0.5px; }
        .auth-subtitle { font-size: 1rem; color: #6b7280; margin-bottom: 36px; text-align: center; }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 36px;
            gap: 12px;
        }
        .step {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .step-label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #6F6382;
            white-space: nowrap;
            transition: color 0.2s;
        }
        .step-label.inactive {
            color: #9ca3af;
            font-weight: 600;
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, #A8A0B2, #6F6382);
            min-width: 32px;
            transition: background 0.2s;
        }
        .step-line.inactive {
            background: #d1d5db;
        }

        .section-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 4px;
        }
        .section-subtitle {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .field-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-icon  {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: #9ca3af; display: flex; align-items: center; pointer-events: none;
        }
        .input-icon svg { width: 18px; height: 18px; }

        .auth-input {
            width: 100%;
            padding: 14px 46px 14px 48px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            color: #111 !important;
            background: #ffffff !important;
            outline: none !important;
            transition: border-color 0.18s, box-shadow 0.18s;
            font-family: 'Lato', sans-serif !important;
            box-shadow: none !important;
        }
        .auth-input::placeholder { color: #9ca3af !important; font-size: 0.95rem; font-weight: 500; }
        .auth-input:focus {
            border-color: #A8A0B2 !important;
            box-shadow: 0 0 0 3px rgba(168, 160, 178, 0.28) !important;
        }
        .auth-input.error { border-color: #ef4444 !important; }

        /* Hide native browser password reveal and clear buttons (Microsoft Edge, IE) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            pointer-events: none !important;
        }

        .eye-btn {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af; padding: 4px; display: flex; align-items: center;
            transition: color 0.15s;
        }
        .eye-btn:hover { color: #4b5563; }
        .eye-btn svg { width: 18px; height: 18px; }

        .meta-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem; color: #6b7280; cursor: pointer; user-select: none; font-weight: 500;
        }
        .remember-label input[type="checkbox"] {
            width: 16px; height: 16px; border-radius: 4px; accent-color: #A8A0B2; cursor: pointer;
        }
        .forgot-link {
            font-size: 0.85rem; font-weight: 700; color: #6F6382;
            text-decoration: none; transition: opacity 0.15s, color 0.15s;
        }
        .forgot-link:hover { color: #564B68; }

        .btn-primary {
            display: block; width: 100%; padding: 15px 32px !important;
            background: linear-gradient(135deg, #91879E 0%, #6F6382 50%, #564B68 100%) !important;
            color: #ffffff !important;
            font-size: 1rem !important; font-weight: 700 !important;
            border: none !important; border-radius: 9999px !important; cursor: pointer;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(111, 99, 130, 0.35) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Lato', sans-serif !important;
            text-align: center;
        }
        .btn-primary:hover  {
            background: linear-gradient(135deg, #9E94AB 0%, #7B6E90 50%, #625575 100%) !important;
            box-shadow: 0 6px 20px rgba(111, 99, 130, 0.45) !important;
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0) scale(0.99);
            box-shadow: 0 2px 8px rgba(111, 99, 130, 0.25) !important;
        }

        .divider {
            display: flex; align-items: center; gap: 16px;
            margin: 28px 0;
            font-size: 0.8rem; font-weight: 500; color: #9ca3af;
            letter-spacing: 0.04em;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e5e7eb;
        }

        .social-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-social {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 12px 16px !important;
            border: 1px solid #e5e7eb !important; border-radius: 9999px !important; background: #fff !important;
            font-size: 0.85rem !important; font-weight: 600 !important; color: #374151 !important; cursor: pointer;
            transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
            font-family: 'Lato', sans-serif !important;
        }
        .btn-social:hover {
            border-color: #d1d5db !important; background: #f9fafb !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
        }
        .btn-social svg { width: 20px; height: 20px; flex-shrink: 0; }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 13px 24px !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 9999px !important;
            background: #ffffff !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            color: #374151 !important;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Lato', sans-serif !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            user-select: none;
        }
        .btn-google:hover {
            border-color: #d1d5db !important;
            background: #f9fafb !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            transform: translateY(-1px);
            color: #111827 !important;
        }
        .btn-google:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
        }
        .btn-google svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .auth-foot {
            text-align: center; margin-top: 36px;
            font-size: 0.95rem; color: #6b7280; font-weight: 500;
        }
        .auth-foot a {
            font-weight: 700; color: #6F6382;
            text-decoration: none; margin-left: 4px;
        }
        .auth-foot a:hover { color: #564B68; text-decoration: underline; }

        .alert {
            border-radius: 10px; padding: 13px 18px;
            font-size: 0.88rem; margin-bottom: 22px; line-height: 1.5;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; border-left: 4px solid #16A34A; color: #166534; font-weight: 600; }
        .alert-info    { background: #FAF8FC; border: 1px solid #E1DDE7; border-left: 4px solid #6F6382; color: #564B68; font-weight: 600; box-shadow: 0 2px 8px rgba(111, 99, 130, 0.06); }
        .alert-error   { background: #FFF1F2; border: 1px solid #FECDD3; border-left: 4px solid #BE123C; color: #BE123C; font-weight: 600; display: block; }

        .page-footer {
            margin-top: 32px;
            text-align: center; font-size: 0.75rem; color: #9ca3af;
        }
        .page-footer a { color: #9ca3af; margin: 0 8px; text-decoration: none; }
        .page-footer a:hover { color: #4b5563; }
        .page-footer-links { margin-bottom: 8px; }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-grid-2 .input-group {
            margin-bottom: 0;
        }

        .select-input {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 18px;
            padding-right: 48px !important;
            cursor: pointer;
        }
        .select-input:focus {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236F6382' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }

        .flex {
            display: flex;
        }
        .gap-3 {
            gap: 12px;
        }

        .btn-secondary {
            background: #ffffff !important;
            color: #111111 !important;
            border: 1px solid #e5e7eb !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-secondary:hover {
            background: #f9fafb !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
        }

        .mb-2 { margin-bottom: 8px; }
        .mb-5 { margin-bottom: 20px; }

        @media (max-width: 640px) {
            body { padding: 32px 12px 24px; }
            .auth-card { padding: 32px 24px 28px !important; }
            .auth-title { font-size: 1.75rem; }
            .auth-subtitle { font-size: 0.9rem; }
            .form-grid-2 { grid-template-columns: 1fr; }
            .auth-logo-img { height: 48px; max-width: 200px; }
        }
    </style>
</head>
<body>

    <div class="auth-card">

        <div class="auth-top-bar">
            <a href="{{ route('home') }}" class="btn-back-shop" title="Back to the shop">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to the shop</span>
            </a>
        </div>

        <a href="/" class="auth-logo" title="cartzy">
            <img src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy" class="auth-logo-img">
        </a>

        @yield('auth_form')

    </div>

    <footer class="page-footer">
        <div class="page-footer-links">
            <a href="#">About</a><a href="#">Terms</a><a href="#">Privacy</a><a href="#">Help</a>
        </div>
        <div>© 2026 cartzy. All rights reserved.</div>
    </footer>

    @stack('scripts')
</body>
</html>
