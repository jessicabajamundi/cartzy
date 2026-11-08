<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $forEveryone ? 'Get the app' : 'Rider App' }} | cartzy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --lavender: #6F6382;
            --lavender-dark: #564B68;
            --rose: #B76E79;
            --rose-dark: #8C5A50;
            --ink: #282133;
            --muted: #6B6478;
            --line: #E7E2EC;
            --soft: #F6F2F7;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--ink);
            background: linear-gradient(160deg, #FAF8FC 0%, #F7EFF1 100%);
            -webkit-font-smoothing: antialiased;
        }
        .ra-card {
            width: 100%;
            max-width: 470px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 34px 26px 26px;
            text-align: center;
            box-shadow: 0 18px 50px rgba(86, 75, 104, 0.10);
        }
        .ra-icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 14px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 8px 22px rgba(86, 75, 104, 0.12);
            display: grid;
            place-items: center;
            overflow: hidden;
        }
        .ra-icon img { width: 100%; height: 100%; object-fit: cover; }
        .ra-eyebrow {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.18em;
            color: var(--rose-dark);
        }
        h1 {
            margin: 0 0 10px;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(30px, 7vw, 38px);
            font-weight: 700;
            line-height: 1.08;
        }
        h1 em { font-style: italic; color: var(--lavender); }
        .ra-lead {
            margin: 0 auto 22px;
            max-width: 380px;
            font-size: 14.5px;
            line-height: 1.6;
            color: var(--muted);
        }
        .ra-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px 20px;
            border-radius: 14px;
            background: linear-gradient(120deg, var(--lavender) 0%, var(--rose) 100%);
            color: #fff;
            font-size: 15.5px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(183, 110, 121, 0.30);
            transition: transform .15s, box-shadow .15s;
        }
        .ra-download:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(183, 110, 121, 0.36); }
        .ra-download:focus-visible { outline: 3px solid var(--lavender-dark); outline-offset: 3px; }
        .ra-download svg { width: 19px; height: 19px; flex-shrink: 0; }
        .ra-meta { margin: 10px 0 22px; font-size: 12.5px; color: var(--muted); }
        .ra-steps {
            margin: 0 0 18px;
            padding: 16px 18px 16px 38px;
            border-radius: 16px;
            background: var(--soft);
            text-align: left;
            font-size: 13.5px;
            line-height: 1.55;
            color: #3C3447;
        }
        .ra-steps li + li { margin-top: 9px; }
        .ra-steps code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12.5px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 5px;
            padding: 1px 5px;
        }
        .ra-note { margin: 0; text-align: left; font-size: 12px; line-height: 1.6; color: var(--muted); }
        .ra-signed-in {
            margin: 18px 0 0;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            font-size: 12.5px;
            color: var(--muted);
        }
        .ra-signed-in button {
            border: 0;
            background: none;
            padding: 0;
            font: inherit;
            font-weight: 700;
            color: var(--rose-dark);
            text-decoration: underline;
            cursor: pointer;
        }
        .ra-footer {
            margin: 20px 0 0;
            max-width: 470px;
            text-align: center;
            font-size: 13px;
            line-height: 1.7;
            color: var(--muted);
        }
        .ra-footer a { color: var(--ink); font-weight: 700; }
        .ra-footer nav { display: flex; flex-wrap: wrap; justify-content: center; gap: 4px 18px; margin-top: 4px; }
    </style>
</head>
<body>
    <main class="ra-card">
        <div class="ra-icon"><img src="{{ asset('images/cartzy-app-icon.png') }}" alt="cartzy app icon" width="84" height="84"></div>
        @if($forEveryone)
            <p class="ra-eyebrow">CARTZY FOR ANDROID</p>
            <h1>All of <em>cartzy</em>, in one app</h1>
            <p class="ra-lead">Shopping on your phone is easier in the cartzy app. Find deals, check out in a few taps, track every order and chat with shops, all in one place. Riders use the same app for their deliveries.</p>
        @else
            <p class="ra-eyebrow">CARTZY RIDERS</p>
            <h1>Deliver with the <em>cartzy</em> app</h1>
            <p class="ra-lead">Riders sign in on our Android app, not the website. Use it to see your dashboard, accept deliveries, scan parcels, take delivery photos and collect cash on delivery.</p>
        @endif

        <a class="ra-download" href="{{ route('rider.app.download') }}" rel="nofollow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
            Download the app (APK)
        </a>
        <p class="ra-meta">Free · Works on most Android phones @if($release['version'])· Version {{ $release['version'] }}@endif</p>

        <ol class="ra-steps">
            <li><strong>Download</strong> the app with the button above.</li>
            <li><strong>Open</strong> the downloaded <code>{{ $release['apk_name'] ?? 'cartzy-mobile.apk' }}</code> file. If Android asks, allow your browser to install unknown apps.</li>
            @if($forEveryone)
                <li><strong>Sign in</strong> or create an account. <strong>Riders:</strong> on the Log In screen, tap <strong>Courier</strong> at the bottom.</li>
            @else
                <li><strong>Sign in:</strong> on the Log In screen, tap <strong>Courier</strong> at the bottom, then use the email and password you registered with.</li>
            @endif
        </ol>

        <p class="ra-note">The app is not on the Play Store yet, so Google Play Protect may warn you the first time. Choose <strong>Install anyway</strong>. To update, download it again and install over the old version; you stay signed in.</p>

        @if($rider)
            <form class="ra-signed-in" method="POST" action="{{ route('logout') }}">
                @csrf
                You're signed in on the website as {{ $rider->name }}. <button type="submit">Log out</button>
            </form>
        @endif
    </main>

    <footer class="ra-footer">
        New rider? <a href="{{ route('register.rider') }}">Apply to deliver with cartzy</a>. Other accounts sign in on the website:
        <nav aria-label="Other sign-in pages">
            <a href="{{ route('login.seller') }}">Seller Centre</a>
            <a href="{{ route('login.logistics') }}">Logistics partners</a>
            <a href="{{ route('login.admin') }}">Admin</a>
        </nav>
    </footer>
</body>
</html>
