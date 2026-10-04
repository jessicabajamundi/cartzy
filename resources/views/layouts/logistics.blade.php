<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Logistics Hub | cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Lato', sans-serif; background-color: #f1f5f9; }
        h1, h2, h3, h4, .font-heading { font-family: 'Cormorant Garamond', Georgia, serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

        /* Collapsed sidebar */
        #logistics-sidebar.is-collapsed { width: 5rem !important; }
        #logistics-sidebar.is-collapsed .sidebar-full-only { display: none !important; }
        #logistics-sidebar.is-collapsed .sidebar-mini-only { display: flex !important; }
        #logistics-sidebar.is-collapsed .nav-item { justify-content: center !important; padding: 0.85rem 0.5rem !important; }
        #logistics-sidebar.is-collapsed .brand-header { justify-content: center !important; padding: 1.25rem 0.5rem !important; }
        #logistics-sidebar.is-collapsed .user-card-full { display: none !important; }
        #logistics-sidebar.is-collapsed .user-card-mini { display: flex !important; }
        #logistics-sidebar.is-collapsed .sidebar-section-divider { height:1px; background:#e2e8f0; margin:0.6rem auto !important; width:1.75rem !important; padding:0 !important; }
    </style>
    @stack('styles')
</head>
<body class="h-screen bg-slate-100 text-slate-900 antialiased flex flex-col overflow-hidden selection:bg-[#0F4C75] selection:text-white">

    <!-- Mobile Top Bar -->
    <div class="lg:hidden bg-[#0A2E4A] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50 border-b border-blue-900 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('logistics-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-300 hover:text-white rounded-lg hover:bg-[#1A3E5A]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-[#1A6FA8] text-white flex items-center justify-center font-black text-sm">🏭</span>
                <span class="font-bold text-sm tracking-wide">LOGISTICS HUB</span>
            </div>
        </div>
        <a href="{{ route('logistics.dashboard') }}" class="text-xs bg-[#1A3E5A] px-3 py-1.5 rounded-lg text-slate-200 border border-blue-700">Dashboard</a>
    </div>

    <div class="flex flex-1 h-full min-h-0 overflow-hidden">

        <!-- Sidebar -->
        <aside id="logistics-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-[#0A2E4A] text-slate-200 transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out flex flex-col border-r border-blue-900 shadow-2xl lg:static lg:h-full lg:shadow-none shrink-0 overflow-hidden">

            <!-- Brand -->
            <div class="brand-header p-4 border-b border-blue-900 flex items-center justify-between shrink-0">
                <div class="sidebar-full-only flex items-center justify-between w-full">
                    <a href="{{ route('logistics.dashboard') }}" class="flex flex-col gap-2 group">
                        <div class="bg-white px-3 py-2 rounded-2xl shadow-md inline-flex items-center justify-center group-hover:scale-105 transition w-fit">
                            <img src="{{ asset('images/logo.png') }}" alt="cartzy logo" class="h-8 w-auto object-contain">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black uppercase tracking-wider text-blue-300 bg-blue-900/60 px-2.5 py-0.5 rounded-lg border border-blue-700 inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                LOGISTICS HUB
                            </span>
                        </div>
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" class="p-2.5 text-slate-400 hover:text-white bg-blue-900/40 hover:bg-blue-800 rounded-xl transition border border-blue-800 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="sidebar-mini-only hidden flex-col items-center gap-2.5 w-full py-1">
                    <a href="{{ route('logistics.dashboard') }}" class="bg-white p-1.5 rounded-xl shadow-md flex items-center justify-center hover:scale-105 transition">
                        <img src="{{ asset('images/favicon.png') }}" alt="cartzy" class="w-7 h-7 object-contain">
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" class="p-2 text-slate-400 hover:text-white bg-blue-900/40 hover:bg-blue-800 rounded-xl transition border border-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto sidebar-scroll min-h-0 px-3 py-4 space-y-1 text-[14px]">

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-2 pb-1 text-[10px] font-black uppercase tracking-wider text-blue-400 block">OVERVIEW</span>
                </div>

                <!-- Dashboard -->
                <a href="{{ route('logistics.dashboard') }}" title="Dashboard" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.dashboard') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <span class="text-lg shrink-0">📊</span>
                    <span class="sidebar-full-only truncate">Dashboard</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Dashboard</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-wider text-blue-400 block">RIDER MANAGEMENT</span>
                </div>

                <!-- Riders -->
                <a href="{{ route('logistics.riders') }}" title="Rider Management" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.riders*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">🛵</span>
                        <span class="sidebar-full-only truncate">Rider Management</span>
                    </div>
                    <span class="sidebar-full-only bg-amber-500 text-white text-xs font-black px-2 py-0.5 rounded-full">3</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Rider Management</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-wider text-blue-400 block">PARCEL OPERATIONS</span>
                </div>

                <!-- Pickup Requests -->
                <a href="{{ route('logistics.pickups') }}" title="Pickup Requests" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.pickups*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">📥</span>
                        <span class="sidebar-full-only truncate">Pickup Requests</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-500 text-white text-xs font-black px-2 py-0.5 rounded-full">New</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Pickup Requests</span>
                </a>

                <!-- Incoming Parcels -->
                <a href="{{ route('logistics.parcels') }}" title="Incoming Parcels" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.parcels*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">📦</span>
                        <span class="sidebar-full-only truncate">Incoming Parcels</span>
                    </div>
                    <span class="sidebar-full-only bg-sky-600 text-white text-xs font-bold px-2 py-0.5 rounded-lg">187</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Incoming Parcels</span>
                </a>

                <!-- Sorting -->
                <a href="{{ route('logistics.sorting') }}" title="Sort Parcels" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.sorting*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">🗂️</span>
                        <span class="sidebar-full-only truncate">Sorting of Parcels</span>
                    </div>
                    <span class="sidebar-full-only bg-violet-600 text-white text-xs font-bold px-2 py-0.5 rounded-lg">Sort</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Sorting of Parcels</span>
                </a>

                <!-- Delivery Assignment -->
                <a href="{{ route('logistics.assignments') }}" title="Delivery Assignment" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.assignments*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">📋</span>
                        <span class="sidebar-full-only truncate">Delivery Assignment</span>
                    </div>
                    <span class="sidebar-full-only bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-lg">Assign</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Delivery Assignment</span>
                </a>

                <!-- Delivery Monitoring -->
                <a href="{{ route('logistics.monitoring') }}" title="Delivery Monitoring" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.monitoring*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">📡</span>
                        <span class="sidebar-full-only truncate">Delivery Monitoring</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Delivery Monitoring</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-wider text-blue-400 block">ANALYTICS & COMMS</span>
                </div>

                <!-- Reports -->
                <a href="{{ route('logistics.reports') }}" title="Reports" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.reports*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">📑</span>
                        <span class="sidebar-full-only truncate">Reports</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Reports</span>
                </a>

                <!-- Chat -->
                <a href="{{ route('logistics.chat') }}" title="Chat" class="nav-item flex items-center justify-between px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.chat*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <div class="flex items-center gap-3 truncate">
                        <span class="text-lg shrink-0">💬</span>
                        <span class="sidebar-full-only truncate">Chat / Messaging</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-500 text-white text-xs font-black px-2 py-0.5 rounded-full">3</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Chat / Messaging</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-wider text-blue-400 block">ACCOUNT</span>
                </div>

                <!-- Account -->
                <a href="{{ route('logistics.account') }}" title="Account" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl font-bold transition group relative {{ request()->routeIs('logistics.account*') ? 'bg-[#1A6FA8] text-white shadow-lg' : 'text-slate-300 hover:bg-blue-900/60 hover:text-white' }}">
                    <span class="text-lg shrink-0">⚙️</span>
                    <span class="sidebar-full-only truncate">Account Management</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Account Management</span>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="nav-item w-full flex items-center gap-3 px-4 py-2.5 rounded-xl font-bold transition text-slate-400 hover:bg-rose-900/40 hover:text-rose-300 group relative">
                        <span class="text-lg shrink-0">🚪</span>
                        <span class="sidebar-full-only truncate">Logout</span>
                        <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Logout</span>
                    </button>
                </form>
            </div>

            <!-- User Card -->
            <div class="user-card-full border-t border-blue-900 p-4 shrink-0 bg-blue-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1A6FA8] flex items-center justify-center text-xl font-black text-white shrink-0">
                        🏭
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-white truncate">{{ Auth::user()->business_name ?? Auth::user()->name }}</div>
                        <div class="text-xs text-blue-300 truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <span class="bg-emerald-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shrink-0">LIVE</span>
                </div>
            </div>
            <div class="user-card-mini hidden border-t border-blue-900 p-3 justify-center shrink-0">
                <div class="w-9 h-9 rounded-xl bg-[#1A6FA8] flex items-center justify-center text-lg font-black text-white">🏭</div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Bar -->
            <header class="hidden lg:flex items-center justify-between px-6 py-3.5 bg-white border-b border-slate-200 shrink-0 shadow-sm">
                <div>
                    <h1 class="text-base font-bold text-slate-800 tracking-tight">@yield('page_title', 'Logistics Hub')</h1>
                    <p class="text-xs text-slate-500 mt-0.5">@yield('page_subtitle', 'cartzy Sorting & Fulfillment Center')</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(session('success'))
                        <span class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg font-medium">✓ {{ session('success') }}</span>
                    @endif
                    <a href="{{ route('logistics.pickups') }}" class="relative p-2 text-slate-500 hover:text-[#1A6FA8] bg-slate-100 hover:bg-blue-50 rounded-xl transition border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">3</span>
                    </a>
                    <a href="{{ route('logistics.account') }}" class="flex items-center gap-2 bg-slate-100 hover:bg-blue-50 border border-slate-200 px-3 py-1.5 rounded-xl transition group">
                        <span class="w-7 h-7 rounded-lg bg-[#1A6FA8] text-white flex items-center justify-center font-black text-sm">🏭</span>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-[#0F4C75] truncate max-w-[140px]">{{ Auth::user()->name ?? 'Hub Manager' }}</span>
                    </a>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-5 lg:p-7">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold px-4 py-3 rounded-xl lg:hidden">
                        <span class="text-base">✓</span> {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                        @foreach($errors->all() as $err) <p>• {{ $err }}</p> @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('logistics-sidebar');
            sidebar.classList.toggle('is-collapsed');
        }
    </script>
    @stack('scripts')
</body>
</html>
