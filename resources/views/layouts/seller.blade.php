<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Seller Centre | cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Chart.js for interactive analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f8fafc;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Cormorant Garamond', Georgia, serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Collapsed Mini Sidebar (Icon Only) */
        #seller-sidebar.is-collapsed {
            width: 5rem !important; /* 80px */
        }
        #seller-sidebar.is-collapsed .sidebar-full-only {
            display: none !important;
        }
        #seller-sidebar.is-collapsed .sidebar-mini-only {
            display: flex !important;
        }
        #seller-sidebar.is-collapsed .nav-item {
            justify-content: center !important;
            padding: 0.85rem 0.5rem !important;
        }
        #seller-sidebar.is-collapsed .brand-header {
            justify-content: center !important;
            padding: 1.25rem 0.5rem !important;
        }
        #seller-sidebar.is-collapsed .user-card-full {
            display: none !important;
        }
        #seller-sidebar.is-collapsed .user-card-mini {
            display: flex !important;
        }
        #seller-sidebar.is-collapsed .sidebar-section-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 0.6rem auto !important;
            width: 1.75rem !important;
            padding: 0 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="h-screen bg-slate-50 text-slate-900 antialiased flex flex-col overflow-hidden selection:bg-[#6F6382] selection:text-white">

    <!-- Top Mobile Header -->
    <div class="lg:hidden bg-[#2D2438] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50 border-b border-slate-800 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('seller-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-300 hover:text-white rounded-lg hover:bg-[#3D324C] focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-white/15 text-white flex items-center justify-center border border-white/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="font-bold text-xs tracking-wider uppercase text-slate-100">Seller Centre</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('seller.dashboard') }}" class="text-xs bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-white border border-white/15 font-semibold transition">Dashboard</a>
        </div>
    </div>

    <div class="flex flex-1 h-full min-h-0 overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside id="seller-sidebar" class="fixed inset-y-0 left-0 z-40 w-80 bg-white text-slate-700 transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out flex flex-col border-r border-slate-200 shadow-xl lg:static lg:h-full lg:shadow-none shrink-0 overflow-hidden">
            
            <!-- Brand / Logo Area (Pinned Top) -->
            <div class="brand-header p-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white z-20 transition-all">
                <!-- Full Sidebar Brand Logo -->
                <div class="sidebar-full-only flex items-center justify-between w-full">
                    <a href="{{ route('seller.dashboard') }}" class="flex flex-col gap-2 group">
                        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-100 shadow-xs inline-flex items-center justify-center group-hover:border-slate-200 transition-all w-fit">
                            <img src="{{ asset('images/logo.png') }}" alt="cartzy logo" class="h-8 w-auto object-contain">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#564B68] bg-[#F1EFF5] px-2.5 py-0.5 rounded-md border border-[#E1DDE7] inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Seller Centre
                            </span>
                            <span class="text-[10px] font-bold text-amber-800 bg-amber-50/90 px-2 py-0.5 rounded-md border border-amber-200/80 inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-amber-500 fill-amber-400" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                4.9 Rating
                            </span>
                        </div>
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" title="Collapse sidebar" class="p-2 text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 active:scale-95 rounded-xl transition flex items-center justify-center border border-slate-200/80 group relative shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="absolute left-full ml-3 top-1/2 -translate-y-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                            Collapse sidebar
                        </span>
                    </button>
                </div>

                <!-- Mini Collapsed Sidebar Logo & Toggle -->
                <div class="sidebar-mini-only hidden flex-col items-center gap-2.5 w-full py-1">
                    <a href="{{ route('seller.dashboard') }}" class="bg-white p-1.5 rounded-xl shadow-xs border border-slate-100 flex items-center justify-center hover:scale-105 transition" title="cartzy Seller Centre">
                        <img src="{{ asset('images/favicon.png') }}" alt="cartzy" class="w-7 h-7 object-contain">
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" title="Expand sidebar" class="p-2 text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 active:scale-95 rounded-xl transition flex items-center justify-center border border-slate-200/80 group relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="absolute left-full ml-3 top-1/2 -translate-y-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                            Expand sidebar
                        </span>
                    </button>
                </div>
            </div>

            <!-- Navigation Links (Smoothly Scrollable) -->
            <div class="flex-1 overflow-y-auto sidebar-scroll min-h-0 px-3 py-3 space-y-1 text-[13.5px]">
                
                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Overview</span>
                </div>

                <!-- 1. Dashboard -->
                <a href="{{ route('seller.dashboard') }}" title="Dashboard Overview" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.dashboard') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.dashboard') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                            <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                            <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                            <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                        </svg>
                    </div>
                    <span class="tracking-normal sidebar-full-only truncate">Dashboard Overview</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Dashboard Overview
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Order Management</span>
                </div>

                <!-- 2. Orders & Notifications -->
                <a href="{{ route('seller.orders') }}" title="Order Notifications & Details" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.orders*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.orders*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Orders & Notifications</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-50 text-rose-600 border border-rose-200/80 text-[10px] font-bold px-2 py-0.5 rounded-full">New</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-800 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Orders & Notifications
                    </span>
                </a>

                <!-- 3. Courier Handover & Tracking -->
                <a href="{{ route('seller.courier') }}" title="Hand over to Courier" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.courier*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.courier*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="1" y="3" width="15" height="13" rx="1.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8h4l3 3v5h-7V8z"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Courier Handover</span>
                    </div>
                    <span class="sidebar-full-only bg-indigo-50 text-indigo-700 border border-indigo-200/80 text-[10px] font-semibold px-2 py-0.5 rounded-md">Pickup</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Courier Handover & Tracking
                    </span>
                </a>

                <!-- 4. Delivery Confirmations -->
                <a href="{{ route('seller.deliveries') }}" title="Delivery Confirmations" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.deliveries*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.deliveries*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Confirm Delivery</span>
                    </div>
                    <span class="sidebar-full-only bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-semibold px-2 py-0.5 rounded-md">Escrow</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Confirm Delivery (Escrow)
                    </span>
                </a>

                <!-- 5. Handle Customer Feedback -->
                <a href="{{ route('seller.feedback') }}" title="Customer Feedback & Reviews" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.feedback*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.feedback*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Customer Feedback</span>
                    </div>
                    <span class="sidebar-full-only inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/80">
                        <svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        4.9
                    </span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Customer Feedback
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Catalog & Promotions</span>
                </div>

                <!-- 6. Manage Inventory (Products & Vouchers) -->
                <a href="{{ route('seller.inventory') }}" title="Manage Inventory & Vouchers" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.inventory*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.inventory*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Manage Inventory</span>
                    </div>
                    <span class="sidebar-full-only bg-slate-100 text-slate-600 text-[10px] font-medium px-2 py-0.5 rounded-md">Stock</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Manage Inventory
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Finance & Analytics</span>
                </div>

                <!-- 7. Generate Report -->
                <a href="{{ route('seller.reports') }}" title="Generate Financial & Profit Report" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.reports*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.reports*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Generate Report</span>
                    </div>
                    <span class="sidebar-full-only text-[10px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Export</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Generate Report (Profit & Dates)
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Communication</span>
                </div>

                <!-- 8. Chat / Messaging -->
                <a href="{{ route('seller.chat') }}" title="Chat & Messaging" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.chat*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.chat*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <span class="tracking-normal sidebar-full-only truncate">Chat / Messaging</span>
                    </div>
                    <span class="sidebar-full-only w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Chat / Messaging
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Settings & Security</span>
                </div>

                <!-- 9. Account Management -->
                <a href="{{ route('seller.account') }}" title="Account Management" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('seller.account*') ? 'bg-[#564B68] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('seller.account*') ? 'bg-white/15 text-white' : 'text-slate-500 group-hover:text-slate-800 group-hover:bg-slate-200/60' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="tracking-normal sidebar-full-only truncate">Account Management</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Account Management
                    </span>
                </a>
            </div>

            <!-- User Footer & Quick Logout Card (Pinned Bottom) -->
            <div class="p-3 border-t border-slate-100 bg-white/95 backdrop-blur-md shrink-0 sticky bottom-0 z-20">
                <!-- Full Card -->
                <div class="user-card-full flex items-center justify-between p-2 px-2.5 rounded-xl bg-slate-50 border border-slate-200/70 shadow-xs hover:border-slate-300 transition">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#6F6382] to-[#564B68] text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="truncate">
                            <div class="font-bold text-slate-800 text-xs truncate leading-tight">{{ Auth::user()->name ?? 'Maria Santos (TechZone)' }}</div>
                            <div class="text-[10px] text-slate-500 flex items-center gap-1.5 font-medium mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Verified Seller
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0 m-0">
                        @csrf
                        <button type="submit" title="Quick Logout" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 active:scale-95 rounded-lg transition flex items-center justify-center group" aria-label="Logout">
                            <svg class="w-4 h-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Mini Collapsed Card -->
                <div class="user-card-mini hidden flex flex-col items-center gap-2 py-1">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#6F6382] to-[#564B68] text-white flex items-center justify-center shadow-xs" title="{{ Auth::user()->name ?? 'Seller' }}">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0 m-0">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 active:scale-95 rounded-lg transition flex items-center justify-center group relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="absolute left-full ml-3 top-1/2 -translate-y-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-semibold px-2 py-1 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                                Logout
                            </span>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- Top Navbar for Seller -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 py-3 flex items-center justify-between shadow-2xs">
                
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        @yield('page_title', 'Seller Centre')
                    </h2>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    
                    <!-- Store Online Pill -->
                    <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Shop Live & Open
                    </div>

                    <!-- Marketplace Link -->
                    <a href="{{ route('home') }}" target="_blank" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition border border-slate-200">
                        <span>🛍️ View Storefront</span>
                    </a>

                    <!-- Notification Pill -->
                    <a href="{{ route('seller.orders') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl relative transition" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    </a>

                    <!-- Chat Pill -->
                    <a href="{{ route('seller.chat') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl relative transition" title="Messages">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    </a>

                </div>

            </header>

            <!-- Flash Notifications -->
            <div class="px-4 sm:px-6 pt-4">
                @if(session('success'))
                    <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span>✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-black">&times;</button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="p-4 mb-4 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-sm font-bold flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span>ℹ️</span>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-indigo-600 hover:text-indigo-900 font-black">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold shadow-xs">
                        <p class="font-extrabold mb-1">Please correct the following:</p>
                        <ul class="list-disc pl-5 font-normal text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- Scripts -->
    <script>
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('seller-sidebar');
            sidebar.classList.toggle('is-collapsed');
            localStorage.setItem('cartzy_seller_sidebar_collapsed', sidebar.classList.contains('is-collapsed'));
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('cartzy_seller_sidebar_collapsed') === 'true') {
                const sidebar = document.getElementById('seller-sidebar');
                if (sidebar) sidebar.classList.add('is-collapsed');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
