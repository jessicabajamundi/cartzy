<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rider Hub | cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts: Cormorant Garamond & Lato -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Chart.js for Profit Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            /* Palette 1: Deep Plum & Lavender */
            --brand-dark: #564B68;
            --brand-lavender: #6F6382;
            --brand-muted: #91879E;
            --brand-soft: #F8F6FA;

            /* Palette 2: Warm Terracotta & Rose */
            --rose-dark: #8C5A50;
            --rose-primary: #C08B7F;
            --rose-muted: #D4A69A;
            --rose-soft: #FAF8F6;

            /* Merged Gradients & Accents */
            --gradient-master: linear-gradient(135deg, #564B68 0%, #6F6382 48%, #8C5A50 100%);
            --gradient-rose: linear-gradient(135deg, #8C5A50 0%, #C08B7F 100%);
            --gradient-soft: linear-gradient(120deg, #FAF8F6 0%, #F8F6FA 100%);
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #F8F7FA;
            color: #1F2937;
            -webkit-tap-highlight-color: transparent;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Cormorant Garamond', Georgia, serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1CAD6; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #91879E; }

        /* Card & Button Elevations */
        .rider-card {
            background: #FFFFFF;
            border: 1px solid #ECE7EF;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(86, 75, 104, 0.04), 0 1px 3px rgba(140, 90, 80, 0.03);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .rider-card:hover {
            box-shadow: 0 6px 18px rgba(86, 75, 104, 0.08), 0 2px 6px rgba(140, 90, 80, 0.04);
        }

        .btn-gradient-master {
            background: var(--gradient-master);
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(86, 75, 104, 0.22);
            transition: all 0.2s ease;
        }
        .btn-gradient-master:hover {
            opacity: 0.96;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(140, 90, 80, 0.28);
        }
        .btn-gradient-master:active {
            transform: translateY(0);
        }

        .badge-online {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .badge-offline {
            background-color: #F3F4F6;
            color: #4B5563;
            border: 1px solid #E5E7EB;
        }

        /* Mobile Bottom Nav Bar */
        @media (max-width: 1024px) {
            .main-content-wrapper {
                padding-bottom: 84px !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="h-screen flex flex-col overflow-hidden selection:bg-[#564B68] selection:text-white">

    <!-- Mobile Top Navigation Header -->
    <header class="lg:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shadow-2xs z-30 shrink-0">
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="toggleMobileSidebar()" class="p-2 text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('courier.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="cartzy" class="h-6 w-auto object-contain">
                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md text-white tracking-wider" style="background: var(--gradient-master);">RIDER</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <!-- Scan Barcode Modal Button -->
            <button type="button" onclick="openScanModal()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white transition shadow-sm" style="background: var(--gradient-master);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Scan</span>
            </button>

            <!-- Online/Offline quick status -->
            <form action="{{ route('courier.account.toggle-status') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-2.5 py-1 rounded-xl text-[11px] font-bold inline-flex items-center gap-1.5 transition {{ ($rider->is_active ?? true) ? 'badge-online' : 'badge-offline' }}" title="Click to toggle Online/Offline">
                    <span class="w-2 h-2 rounded-full {{ ($rider->is_active ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                    <span>{{ ($rider->is_active ?? true) ? 'ONLINE' : 'OFFLINE' }}</span>
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 h-full min-h-0 overflow-hidden">

        <!-- Desktop & Tablet Sidebar -->
        <aside id="riderSidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-[#ECE7EF] flex flex-col shadow-xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shrink-0 lg:static">
            <!-- Brand & Rider Status -->
            <div class="p-4 border-b border-[#ECE7EF] flex items-center justify-between">
                <a href="{{ route('courier.dashboard') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo.png') }}" alt="cartzy" class="h-7 w-auto object-contain">
                    <span class="text-[10px] font-black uppercase tracking-wider text-white px-2 py-0.5 rounded-md" style="background: var(--gradient-master);">COURIER</span>
                </a>
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-1.5 text-gray-400 hover:text-gray-700 rounded-lg">✕</button>
            </div>

            <!-- Rider Summary Card in Sidebar -->
            <div class="p-3.5 m-3 rounded-2xl border border-[#ECE7EF]" style="background: var(--gradient-soft);">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl text-white font-bold flex items-center justify-center shrink-0 shadow-sm" style="background: var(--gradient-master);">
                        {{ strtoupper(substr(Auth::user()->name ?? 'Rider', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-bold text-gray-900 truncate">{{ Auth::user()->name ?? 'Express Rider' }}</h4>
                            <span class="text-[9px] font-bold text-amber-800 bg-amber-100 px-1 py-0.2 rounded shrink-0">★ {{ number_format($rider->rating ?? 4.95, 1) }}</span>
                        </div>
                        <p class="text-[11px] text-[#6F6382] font-semibold truncate">{{ $rider->vehicle_type ?? 'Motorcycle' }} · {{ $rider->plate_no ?? 'NQ-4812' }}</p>
                    </div>
                </div>

                <div class="mt-2.5 pt-2.5 border-t border-gray-200/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Working Status:</span>
                    <form action="{{ route('courier.account.toggle-status') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[10px] font-black px-2 py-0.5 rounded-lg inline-flex items-center gap-1 transition {{ ($rider->is_active ?? true) ? 'badge-online' : 'badge-offline' }}" title="Click to toggle status">
                            <span class="w-1.5 h-1.5 rounded-full {{ ($rider->is_active ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            <span>{{ ($rider->is_active ?? true) ? 'Online' : 'Offline' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar Navigation Menu -->
            <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 text-xs font-semibold">
                <!-- 1. Dashboard -->
                <a href="{{ route('courier.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.dashboard') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.dashboard') ? 'background: var(--gradient-master);' : '' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- 2. Items for Pickup -->
                <a href="{{ route('courier.pickups') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.pickups*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.pickups*') ? 'background: var(--gradient-master);' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>Items for Pickup</span>
                    </div>
                    @php $pCount = \App\Models\PickupRequest::where('status', 'pending')->count(); @endphp
                    @if($pCount > 0)
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-full {{ request()->routeIs('courier.pickups*') ? 'bg-white text-[#564B68]' : 'bg-[#C08B7F] text-white' }}">{{ $pCount }}</span>
                    @endif
                </a>

                <!-- 3. Items for Delivery -->
                <a href="{{ route('courier.deliveries') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.deliveries*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.deliveries*') ? 'background: var(--gradient-master);' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <rect x="1" y="3" width="15" height="13" rx="2"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        <span>Items for Delivery</span>
                    </div>
                    @php $dCount = \App\Models\Shipment::where('rider_id', $rider->id ?? 0)->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count(); @endphp
                    @if($dCount > 0)
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-full {{ request()->routeIs('courier.deliveries*') ? 'bg-white text-[#564B68]' : 'bg-[#6F6382] text-white' }}">{{ $dCount }}</span>
                    @endif
                </a>

                <!-- 4. Profit & Earnings -->
                <a href="{{ route('courier.earnings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.earnings*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.earnings*') ? 'background: var(--gradient-master);' : '' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Profit &amp; Earnings</span>
                </a>

                <!-- 5. Delivery History -->
                <a href="{{ route('courier.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.history*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.history*') ? 'background: var(--gradient-master);' : '' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Delivery History</span>
                </a>

                <!-- 6. Chat / Messaging -->
                <a href="{{ route('courier.chat') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.chat*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.chat*') ? 'background: var(--gradient-master);' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat / Messaging</span>
                    </div>
                </a>

                <div class="pt-3 border-t border-[#ECE7EF]">
                    <!-- 7. Account Management -->
                    <a href="{{ route('courier.account') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('courier.account*') ? 'text-white shadow-sm font-bold' : 'text-gray-600 hover:bg-[#F8F6FA] hover:text-[#564B68]' }}" style="{{ request()->routeIs('courier.account*') ? 'background: var(--gradient-master);' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Account Management</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer & Logout -->
            <div class="p-3 border-t border-[#ECE7EF]">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 hover:bg-rose-50 hover:text-rose-700 border border-gray-200 hover:border-rose-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            <!-- Desktop Top Header Bar -->
            <header class="hidden lg:flex bg-white border-b border-[#ECE7EF] px-8 py-3.5 items-center justify-between shrink-0 shadow-2xs">
                <div class="flex items-center gap-3">
                    <h2 class="font-heading text-2xl font-bold text-[#564B68]">@yield('header_title', 'Courier Rider Hub')</h2>
                    <span class="text-xs text-gray-400">|</span>
                    <span class="text-xs font-semibold text-gray-500">Sector: <strong class="text-gray-800">{{ $rider->area ?? 'Metro South Sector' }}</strong></span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Quick Barcode Scan Trigger -->
                    <button type="button" onclick="openScanModal()" class="btn-gradient-master px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Scan Waybill Barcode</span>
                    </button>

                    <!-- Rider Online Toggle -->
                    <form action="{{ route('courier.account.toggle-status') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition {{ ($rider->is_active ?? true) ? 'badge-online' : 'badge-offline' }}" title="Click to toggle status">
                            <span class="w-2 h-2 rounded-full {{ ($rider->is_active ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            <span>{{ ($rider->is_active ?? true) ? 'Online (Accepting Jobs)' : 'Offline (Paused)' }}</span>
                        </button>
                    </form>

                    <!-- Quick Logout -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Log Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Scrollable Page Container -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 main-content-wrapper space-y-6">
                <!-- Flash Notification Banner -->
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded-2xl flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</div>
                            <span class="text-xs font-bold">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-bold">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 rounded-2xl flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">!</div>
                            <span class="text-xs font-bold">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-bold">✕</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Docked) -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-[#ECE7EF] px-2 py-1.5 z-40 flex items-center justify-around shadow-2xl">
        <a href="{{ route('courier.dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-bold transition {{ request()->routeIs('courier.dashboard') ? 'text-[#564B68]' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            <span>Home</span>
        </a>

        <a href="{{ route('courier.pickups') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-bold transition relative {{ request()->routeIs('courier.pickups*') ? 'text-[#564B68]' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Pickups</span>
            @if(isset($pCount) && $pCount > 0)
                <span class="absolute top-1 right-2 w-2 h-2 rounded-full bg-[#C08B7F]"></span>
            @endif
        </a>

        <a href="{{ route('courier.deliveries') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-bold transition relative {{ request()->routeIs('courier.deliveries*') ? 'text-[#564B68]' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>Deliveries</span>
            @if(isset($dCount) && $dCount > 0)
                <span class="absolute top-1 right-2 w-2 h-2 rounded-full bg-[#6F6382]"></span>
            @endif
        </a>

        <a href="{{ route('courier.earnings') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-bold transition {{ request()->routeIs('courier.earnings*') ? 'text-[#564B68]' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Earnings</span>
        </a>

        <a href="{{ route('courier.account') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-bold transition {{ request()->routeIs('courier.account*') ? 'text-[#564B68]' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Profile</span>
        </a>
    </nav>

    <!-- UNIVERSAL BARCODE & QR SCANNING MODAL -->
    <div id="scanModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-gray-100 transform transition-all">
            <!-- Header -->
            <div class="text-white p-5 flex items-center justify-between" style="background: var(--gradient-master);">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Optical Waybill Scanner</h3>
                        <p class="text-[11px] text-white/80">Scan package barcode or enter tracking number</p>
                    </div>
                </div>
                <button type="button" onclick="closeScanModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white text-base">✕</button>
            </div>

            <div class="p-5 space-y-4">
                <!-- Viewfinder Graphic -->
                <div class="relative bg-gray-900 rounded-2xl overflow-hidden aspect-video flex flex-col items-center justify-center text-white border-2 border-dashed border-gray-700">
                    <div class="w-48 h-24 border-2 border-[#D4A69A] rounded-lg relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-[#C08B7F]/10 animate-pulse"></div>
                        <div class="w-full h-0.5 bg-[#D4A69A] absolute shadow-[0_0_8px_#D4A69A] animate-[bounce_2s_infinite]"></div>
                        <span class="text-[9px] text-[#F5CFC7] uppercase tracking-widest font-black z-10">AIM CAMERA AT BARCODE</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-3 text-center px-4">
                        Place the <strong>Code 128 Barcode</strong> or <strong>Tracking Code</strong> inside the box.
                    </p>
                </div>

                <!-- Manual Input Form -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Enter Tracking Number</label>
                    <form id="barcodeSearchForm" onsubmit="handleBarcodeSubmit(event)" class="flex gap-2">
                        <input type="text" id="barcodeInput" placeholder="e.g. CTZY-RD-884901 or ORD ref" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-mono focus:ring-2 focus:ring-[#564B68] focus:outline-hidden" required autofocus />
                        <button type="submit" id="searchBarcodeBtn" class="btn-gradient-master text-xs px-4 py-2.5 cursor-pointer">
                            Lookup
                        </button>
                    </form>
                </div>

                <!-- Result Card -->
                <div id="scanResultSection" class="hidden pt-3 border-t border-gray-200 space-y-3">
                    <div class="bg-[#FAF8F6] border border-[#EFE8DE] rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-white px-2 py-0.5 rounded" style="background: var(--brand-lavender);">PARCEL VERIFIED</span>
                            <span id="resultPaymentBadge" class="text-xs font-black px-2 py-0.5 rounded"></span>
                        </div>

                        <h4 id="resultRecipient" class="font-bold text-sm text-gray-900"></h4>
                        <p id="resultAddress" class="text-xs text-gray-600 mt-0.5"></p>
                        <p class="text-xs text-gray-500 mt-1">Tracking: <strong id="resultTracking" class="font-mono text-gray-800"></strong></p>
                        <p class="text-xs text-gray-500">Items: <span id="resultItems"></span></p>

                        <form id="confirmDeliveryForm" method="POST" class="mt-4 pt-3 border-t border-gray-200 space-y-2.5">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700">Received By (Name / Relation):</label>
                                <input type="text" name="recipient_name" id="confirmRecipientInput" class="w-full bg-white px-3 py-2 rounded-lg border border-gray-300 text-xs mt-0.5" placeholder="e.g. Customer, Spouse, Concierge" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700">Rider Delivery Note (Optional):</label>
                                <input type="text" name="note" class="w-full bg-white px-3 py-2 rounded-lg border border-gray-300 text-xs mt-0.5" placeholder="e.g. Package received in good condition">
                            </div>

                            <button type="submit" class="w-full btn-gradient-master text-xs py-3 cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>CONFIRM DELIVERY (NOTIFY SELLER &amp; BUYER)</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div id="scanErrorSection" class="hidden bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-xl text-xs font-semibold"></div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('riderSidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
        }

        function openScanModal() {
            document.getElementById('scanModal').classList.remove('hidden');
            const input = document.getElementById('barcodeInput');
            if (input) input.focus();
        }

        function closeScanModal() {
            document.getElementById('scanModal').classList.add('hidden');
            document.getElementById('scanResultSection').classList.add('hidden');
            document.getElementById('scanErrorSection').classList.add('hidden');
            document.getElementById('barcodeInput').value = '';
        }

        async function handleBarcodeSubmit(e) {
            e.preventDefault();
            const code = document.getElementById('barcodeInput').value.trim();
            if (!code) return;

            const btn = document.getElementById('searchBarcodeBtn');
            btn.disabled = true;
            btn.innerText = 'Scanning...';

            const errBox = document.getElementById('scanErrorSection');
            const resBox = document.getElementById('scanResultSection');
            errBox.classList.add('hidden');
            resBox.classList.add('hidden');

            try {
                const response = await fetch("{{ route('courier.scan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ tracking_code: code })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const ship = data.shipment;
                    document.getElementById('resultRecipient').innerText = ship.recipient_name + ' (' + ship.recipient_phone + ')';
                    document.getElementById('resultAddress').innerText = ship.recipient_address;
                    document.getElementById('resultTracking').innerText = ship.tracking_code;
                    document.getElementById('resultItems').innerText = ship.items_summary || (ship.items_count + ' items');
                    document.getElementById('confirmRecipientInput').value = ship.recipient_name;

                    const badge = document.getElementById('resultPaymentBadge');
                    if (ship.is_cod) {
                        badge.className = 'text-xs font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-200';
                        badge.innerText = 'COLLECT COD: ₱' + ship.cod_amount;
                    } else {
                        badge.className = 'text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-200';
                        badge.innerText = 'PAID ONLINE (₱0.00)';
                    }

                    const form = document.getElementById('confirmDeliveryForm');
                    form.action = `/courier/deliver/${ship.id}`;

                    resBox.classList.remove('hidden');
                } else {
                    errBox.innerText = data.message || 'Parcel not found. Please check tracking code.';
                    errBox.classList.remove('hidden');
                }
            } catch (err) {
                errBox.innerText = 'Could not scan barcode. Please check network connection.';
                errBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Lookup';
            }
        }

        function triggerParcelDelivery(id, tracking, recipient, payment) {
            openScanModal();
            const input = document.getElementById('barcodeInput');
            if (input) {
                input.value = tracking;
                document.getElementById('barcodeSearchForm').dispatchEvent(new Event('submit'));
            }
        }
    </script>
    @stack('scripts')
@include('partials.logout-confirm')
</body>
</html>
