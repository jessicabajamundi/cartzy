<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <meta name="description" content="@yield('meta_description', 'Discover premium deals, flash sales, fast shipping, and curated products from top brands and trusted sellers.')">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --brand-primary: #A8A0B2;
            --brand-deep: #6F6382;
            --brand-dark: #564B68;
            --brand-darkest: #282133;
            --brand-soft: #FAF9FB;
            --brand-light: #F1EFF5;
            --brand-border: #E1DDE7;
        }
        body {
            font-family: 'Lato', sans-serif;
            background-color: #FAF9FB;
            color: #191421;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Cormorant Garamond', Georgia, serif;
            letter-spacing: -0.01em;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: #F1EFF5;
        }
        ::-webkit-scrollbar-thumb {
            background: #A8A0B2;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #6F6382;
        }
        ::selection {
            background-color: #A8A0B2;
            color: #ffffff;
        }

        /* ── Compact Product Cards ──────────────────────────────────────── */

        /* Explicit Grid Rules for Edge-to-Edge Full Width */
        #shoes-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.5rem !important;
            width: 100% !important;
        }
        @media (min-width: 640px) {
            #shoes-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 768px) {
            #shoes-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; gap: 0.65rem !important; }
        }
        @media (min-width: 1024px) {
            #shoes-grid { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 1280px) {
            #shoes-grid { grid-template-columns: repeat(6, minmax(0, 1fr)) !important; }
        }

        .daily-discover-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.5rem !important;
            width: 100% !important;
        }
        @media (min-width: 640px) {
            .daily-discover-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 768px) {
            .daily-discover-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 1024px) {
            .daily-discover-grid { grid-template-columns: repeat(6, minmax(0, 1fr)) !important; gap: 0.6rem !important; }
        }
        @media (min-width: 1280px) {
            .daily-discover-grid { grid-template-columns: repeat(8, minmax(0, 1fr)) !important; gap: 0.6rem !important; }
        }

        /* Clamp image height for a tighter square */
        .product-card .relative.aspect-square {
            max-height: 125px;
        }

        /* Reduce inner content padding */
        .product-card > div:not(.relative) {
            padding: 0.35rem 0.5rem 0.45rem !important;
        }

        /* Smaller product title */
        .product-card h3 {
            font-size: 0.66rem !important;
            line-height: 1.25 !important;
        }

        /* Compact price */
        .product-card .text-base,
        .product-card .text-lg {
            font-size: 0.82rem !important;
        }

        /* Compact Add to Cart button */
        .product-card button[onclick] {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            font-size: 0.60rem !important;
            margin-top: 0.3rem !important;
            border-radius: 0.35rem !important;
        }
        .product-card button[onclick] svg {
            width: 0.65rem !important;
            height: 0.65rem !important;
        }

        /* Tighter border-top divider above price */
        .product-card .border-t {
            margin-top: 0.3rem !important;
            padding-top: 0.3rem !important;
        }

        /* Smaller tag badges (Free Shipping, Authentic) */
        .product-card .flex.items-center.gap-1 span {
            font-size: 0.57rem !important;
            padding: 0.07rem 0.28rem !important;
        }

        /* Smaller sold / rating text */
        .product-card .flex.items-center.justify-between {
            margin-top: 0.2rem !important;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col text-neutral-900 antialiased selection:bg-[#A8A0B2] selection:text-white">

    <!-- Top Header -->
    @include('layouts.partials.header')

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @unless(View::hasSection('hide_footer'))
        @include('layouts.partials.footer')
    @endunless

    @stack('scripts')

    <!-- Global Cart JS (available on all pages) -->
    <script src="{{ asset('js/cart.js') }}"></script>
</body>
</html>
