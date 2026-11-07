<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $portal['key'] === 'buyer' ? 'Log In' : $portal['title'].' Log In' }} | cartzy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v={{ filemtime(public_path('images/favicon.png')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth-modal.css') }}?v={{ filemtime(public_path('css/auth-modal.css')) }}">
</head>
<body class="lm-page">
    <div class="lm-page-bg" aria-hidden="true"></div>

    <main>
        @include('auth.partials.login-modal', ['standalone' => true])
    </main>

    <p class="lm-page-footer">&copy; {{ date('Y') }} cartzy. All rights reserved.</p>

    <script src="{{ asset('js/auth/login-modal.js') }}?v={{ filemtime(public_path('js/auth/login-modal.js')) }}"></script>
    <script>
        if (window.location.hash === '#forgot-password') {
            window.location.replace('{{ route("password.forgot") }}');
        }
        window.addEventListener('hashchange', function () {
            if (window.location.hash === '#forgot-password') {
                window.location.replace('{{ route("password.forgot") }}');
            }
        });
    </script>
    @include('partials.prevent-back-cache')
</body>
</html>
