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
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }

        /* Collapsed sidebar */
        #logistics-sidebar.is-collapsed { width: 5rem !important; }
        #logistics-sidebar.is-collapsed .sidebar-full-only { display: none !important; }
        #logistics-sidebar.is-collapsed .sidebar-mini-only { display: flex !important; }
        #logistics-sidebar.is-collapsed .nav-item { justify-content: center !important; padding: 0.85rem 0.5rem !important; }
        #logistics-sidebar.is-collapsed .brand-header { justify-content: center !important; padding: 1.25rem 0.5rem !important; }
        #logistics-sidebar.is-collapsed .user-card-full { display: none !important; }
        #logistics-sidebar.is-collapsed .user-card-mini { display: flex !important; }
        #logistics-sidebar.is-collapsed .sidebar-section-divider { height:1px; background:#f1f5f9; margin:0.6rem auto !important; width:1.75rem !important; padding:0 !important; }
    </style>
    @stack('styles')
</head>
<body class="h-screen bg-slate-100 text-slate-900 antialiased flex flex-col overflow-hidden selection:bg-[#0F4C75] selection:text-white">

    <!-- Mobile Top Bar -->
    <div class="lg:hidden bg-white text-slate-800 px-4 py-3 flex items-center justify-between sticky top-0 z-50 border-b border-slate-200 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('logistics-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-[#1A6FA8] text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <span class="font-bold text-sm tracking-wide">LOGISTICS HUB</span>
            </div>
        </div>
        <a href="{{ route('logistics.dashboard') }}" class="text-xs bg-slate-100 px-3 py-1.5 rounded-lg text-slate-600 border border-slate-200">Dashboard</a>
    </div>

    <div class="flex flex-1 h-full min-h-0 overflow-hidden">

        <!-- Sidebar -->
        <aside id="logistics-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white text-slate-700 transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out flex flex-col border-r border-slate-200 shadow-2xl lg:static lg:h-full lg:shadow-none shrink-0 overflow-hidden">

            <!-- Brand -->
            <div class="brand-header p-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="sidebar-full-only flex items-center justify-between w-full">
                    <a href="{{ route('logistics.dashboard') }}" class="flex flex-col gap-2 group">
                        <div class="bg-white px-3 py-2 rounded-2xl shadow-md inline-flex items-center justify-center group-hover:scale-105 transition w-fit">
                            <img src="{{ asset('images/logo.png') }}" alt="cartzy logo" class="h-8 w-auto object-contain">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black uppercase tracking-wider text-[#1A6FA8] bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200 inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                LOGISTICS HUB
                            </span>
                        </div>
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" class="p-2.5 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition border border-slate-200 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="sidebar-mini-only hidden flex-col items-center gap-2.5 w-full py-1">
                    <a href="{{ route('logistics.dashboard') }}" class="bg-white p-1.5 rounded-xl shadow-md flex items-center justify-center hover:scale-105 transition">
                        <img src="{{ asset('images/favicon.png') }}" alt="cartzy" class="w-7 h-7 object-contain">
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" class="p-2 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto sidebar-scroll min-h-0 px-3 py-3 space-y-1 text-[13.5px]">

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Overview</span>
                </div>

                <!-- Dashboard -->
                <a href="{{ route('logistics.dashboard') }}" title="Dashboard" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.dashboard') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.dashboard') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                            <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                            <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                            <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                        </svg>
                    </div>
                    <span class="sidebar-full-only truncate">Dashboard</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Dashboard</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Rider Management</span>
                </div>

                <!-- Riders -->
                <a href="{{ route('logistics.riders') }}" title="Rider Management" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.riders*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.riders*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <circle cx="5" cy="18" r="3"/>
                                <circle cx="19" cy="18" r="3"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5l-3-3h4l2 3h4"/>
                                <circle cx="9" cy="5" r="2"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Rider Management</span>
                    </div>
                    <span class="sidebar-full-only bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">3</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Rider Management</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Parcel Operations</span>
                </div>

                <!-- Pickup Requests -->
                <a href="{{ route('logistics.pickups') }}" title="Pickup Requests" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.pickups*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.pickups*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Pickup Requests</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">New</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Pickup Requests</span>
                </a>

                <!-- Incoming Parcels -->
                <a href="{{ route('logistics.parcels') }}" title="Incoming Parcels" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.parcels*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.parcels*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Incoming Parcels</span>
                    </div>
                    <span class="sidebar-full-only bg-sky-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-lg">187</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Incoming Parcels</span>
                </a>

                <!-- Sorting -->
                <a href="{{ route('logistics.sorting') }}" title="Sort Parcels" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.sorting*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.sorting*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Sorting of Parcels</span>
                    </div>
                    <span class="sidebar-full-only bg-violet-600 text-white text-[10px] font-semibold px-2 py-0.5 rounded-lg">Sort</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Sorting of Parcels</span>
                </a>

                <!-- Delivery Assignment -->
                <a href="{{ route('logistics.assignments') }}" title="Delivery Assignment" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.assignments*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.assignments*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Delivery Assignment</span>
                    </div>
                    <span class="sidebar-full-only bg-emerald-600 text-white text-[10px] font-semibold px-2 py-0.5 rounded-lg">Assign</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Delivery Assignment</span>
                </a>

                <!-- Delivery Monitoring -->
                <a href="{{ route('logistics.monitoring') }}" title="Delivery Monitoring" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.monitoring*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.monitoring*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Delivery Monitoring</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Delivery Monitoring</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Analytics & Comms</span>
                </div>

                <!-- Reports -->
                <a href="{{ route('logistics.reports') }}" title="Reports" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.reports*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.reports*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Reports</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Reports</span>
                </a>

                <!-- Chat -->
                <a href="{{ route('logistics.chat') }}" title="Chat" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.chat*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.chat*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Chat / Messaging</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">3</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Chat / Messaging</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Account</span>
                </div>

                <!-- Account -->
                <a href="{{ route('logistics.account') }}" title="Account" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.account*') ? 'bg-[#1A6FA8] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.account*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="sidebar-full-only truncate">Account Management</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Account Management</span>
                </a>

            </div>

            <!-- User Card -->
            <div class="user-card-full border-t border-slate-200 p-3 shrink-0 bg-slate-50">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#1A6FA8] flex items-center justify-center text-white shrink-0 shadow-xs">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate" title="{{ Auth::user()->business_name ?? Auth::user()->name }}">{{ Auth::user()->business_name ?? Auth::user()->name }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1 font-medium mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">{{ Auth::user()->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="bg-emerald-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md">LIVE</span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 active:scale-95 rounded-lg transition flex items-center justify-center group" aria-label="Logout">
                                <svg class="w-4 h-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="user-card-mini hidden border-t border-slate-200 p-2.5 flex-col items-center gap-2 justify-center shrink-0">
                <div class="w-8 h-8 rounded-xl bg-[#1A6FA8] flex items-center justify-center text-white shadow-xs" title="{{ Auth::user()->business_name ?? Auth::user()->name }}">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 active:scale-95 rounded-lg transition flex items-center justify-center group relative" aria-label="Logout">
                        <svg class="w-4 h-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 group-hover:block bg-slate-900 text-white text-xs font-semibold px-2 py-1 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                            Logout
                        </span>
                    </button>
                </form>
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
                        <span class="w-7 h-7 rounded-lg bg-[#1A6FA8] text-white flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
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
