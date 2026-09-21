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
    <script>
    // ─── Toast Notification ────────────────────────────────────────────────────
    window.showToast = function(message, type = 'success') {
        let toast = document.getElementById('cart-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'cart-toast';
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;transform:translateY(120px);opacity:0;transition:all 0.35s cubic-bezier(.4,0,.2,1);pointer-events:none;';
            document.body.appendChild(toast);
        }
        const bg = type === 'success' ? '#111827' : '#dc2626';
        const icon = type === 'success'
            ? '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
            : '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        toast.innerHTML = `<div style="background:${bg};color:#fff;border-radius:12px;padding:12px 18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 32px rgba(0,0,0,0.28);min-width:240px;max-width:360px">${icon}<span>${message}</span></div>`;
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(() => {
            toast.style.transform = 'translateY(120px)';
            toast.style.opacity = '0';
        }, 3000);
    };

    // ─── Update Cart Badge ─────────────────────────────────────────────────────
    window.updateCartBadge = function(count) {
        document.querySelectorAll('.cart-badge-count').forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        });
    };

    // ─── Rebuild Cart Popover HTML ─────────────────────────────────────────────
    window.updateCartPopover = function(cartItems, cartCount) {
        const box = document.getElementById('cart-popover-box');
        if (!box) return;

        if (!cartItems || cartItems.length === 0) {
            box.innerHTML = `
                <div class="p-8 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-gray-100 text-gray-800 rounded-full flex items-center justify-center text-2xl mb-2">🛒</div>
                    <h4 class="text-sm font-bold text-gray-900">Your Cart is Empty</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-xs">No items added to your cart yet.</p>
                    <a href="/" class="mt-4 bg-black hover:bg-gray-800 text-white text-xs font-semibold px-5 py-2 rounded-md shadow-xs transition">Shop Now</a>
                </div>`;
            return;
        }

        const itemsHtml = cartItems.map(item => `
            <div class="p-3 hover:bg-gray-50 flex items-center gap-3 transition">
                <img src="${item.image || 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=100&h=100&fit=crop&q=80'}"
                     alt="Item" class="w-12 h-12 object-cover rounded border border-gray-200 shrink-0">
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-medium text-gray-900 truncate">${item.name}</h4>
                    <p class="text-[11px] text-gray-500">Qty: ${item.quantity} · ${item.variation || 'Standard'}</p>
                    <span class="text-xs font-bold text-black">₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                </div>
            </div>`).join('');

        const label = cartCount === 1 ? 'item' : 'items';
        box.innerHTML = `
            <div class="p-3 bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
                Cart (${cartCount} ${label})
            </div>
            <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">${itemsHtml}</div>
            <div class="p-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-2">
                <a href="/cart" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-900 text-xs font-semibold px-3 py-2 rounded transition border border-gray-200">View Cart</a>
                <a href="/checkout" class="flex-1 text-center bg-black hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded transition flex items-center justify-center gap-1"><span>Checkout</span><span>&rarr;</span></a>
            </div>`;
    };

    // ─── Add to Cart ───────────────────────────────────────────────────────────
    window.addToCart = function(id, name, price, image, variation) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                       || '{{ csrf_token() }}';
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id, name, price, image, variation, quantity: 1 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('✓ Added: ' + (name.length > 28 ? name.substring(0, 28) + '…' : name), 'success');
                updateCartBadge(data.cartCount);
                updateCartPopover(data.cart, data.cartCount);
            } else {
                showToast(data.message || 'Could not add to cart.', 'error');
            }
        })
        .catch(() => {
            showToast('Something went wrong. Please try again.', 'error');
        });
    };
    </script>
</body>
</html>
