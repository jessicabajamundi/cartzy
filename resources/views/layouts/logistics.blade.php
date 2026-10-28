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
        body { font-family: 'Lato', sans-serif; background-color: #F6F4F8; }
        h1, h2, h3, h4, .font-heading { font-family: 'Cormorant Garamond', Georgia, serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #F6F4F8; }
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
        #logistics-sidebar.is-collapsed .sidebar-section-divider { height:1px; background:#F6F4F8; margin:0.6rem auto !important; width:1.75rem !important; padding:0 !important; }
    </style>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/chat-popup.css') }}?v={{ filemtime(public_path('css/chat-popup.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/logistics.css') }}?v={{ filemtime(public_path('css/logistics.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/logistics-workspace.css') }}?v={{ filemtime(public_path('css/logistics-workspace.css')) }}">
    @if(request()->routeIs('logistics.dashboard'))
        <link rel="stylesheet" href="{{ asset('css/logistics-dashboard.css') }}?v={{ filemtime(public_path('css/logistics-dashboard.css')) }}">
    @endif
</head>
<body class="logistics-portal h-screen bg-slate-100 text-slate-900 antialiased flex flex-col overflow-hidden selection:bg-[#564B68] selection:text-white">

    <!-- Mobile Top Bar -->
    <div class="logistics-mobile-topbar lg:hidden bg-white text-slate-800 px-4 py-3 flex items-center justify-between sticky top-0 z-50 border-b border-slate-200 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('logistics-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-[#6F6382] text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <span class="font-bold text-sm tracking-wide">LOGISTICS HUB</span>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            @php
                $mobileLogisticsUnread = \App\Models\AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();
            @endphp
            <a href="{{ route('logistics.notifications') }}" class="relative p-2 text-slate-500 hover:text-[#564B68] rounded-lg hover:bg-slate-100" title="Notifications" aria-label="Notifications">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                @if($mobileLogisticsUnread)
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500"></span>
                @endif
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0 inline">
                @csrf
                <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer" title="Log out" aria-label="Log out">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
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
                            <span class="text-[11px] font-black uppercase tracking-wider text-[#6F6382] bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200 inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                LOGISTICS HUB
                            </span>
                        </div>
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" aria-label="Toggle sidebar" class="p-2.5 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition border border-slate-200 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="sidebar-mini-only hidden flex-col items-center gap-2.5 w-full py-1">
                    <a href="{{ route('logistics.dashboard') }}" class="bg-white p-1.5 rounded-xl shadow-md flex items-center justify-center hover:scale-105 transition">
                        <img src="{{ asset('images/favicon.png') }}" alt="cartzy" class="w-7 h-7 object-contain">
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" aria-label="Toggle sidebar" class="p-2 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto sidebar-scroll min-h-0 px-3 py-3 text-[13.5px]">

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Overview</span>
                </div>

                <!-- Dashboard -->
                <a href="{{ route('logistics.dashboard') }}" title="Dashboard" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.dashboard') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
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
                <a href="{{ route('logistics.riders') }}" title="Rider Management" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.riders*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
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
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Rider Management</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Parcel Operations</span>
                </div>

                <!-- Pickup Requests -->
                <a href="{{ route('logistics.pickups') }}" title="Pickup Requests" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.pickups*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.pickups*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Pickup Requests</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Pickup Requests</span>
                </a>

                <!-- Hub Desk: receive, sort and assign parcels in one place -->
                <a href="{{ route('logistics.hub') }}" title="Hub Desk" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.hub*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.hub*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Hub Desk</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Hub Desk</span>
                </a>

                <!-- Delivery Monitoring -->
                <a href="{{ route('logistics.monitoring') }}" title="Delivery Monitoring" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.monitoring*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
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
                <a href="{{ route('logistics.reports') }}" title="Reports" class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.reports*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
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

                @php
                    $logisticsUnreadCount = \App\Models\AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();
                @endphp
                <a href="{{ route('logistics.notifications') }}" title="Notifications{{ $logisticsUnreadCount ? ' (' . $logisticsUnreadCount . ' unread)' : '' }}" aria-label="Notifications, {{ $logisticsUnreadCount }} unread" @if(request()->routeIs('logistics.notifications*')) aria-current="page" @endif class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.notifications*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 relative flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                            @if($logisticsUnreadCount)<span class="sidebar-mini-only hidden absolute top-0 right-0 w-2 h-2 rounded-full bg-rose-500"></span>@endif
                        </div>
                        <span class="sidebar-full-only">Notifications</span>
                    </div>
                    @if($logisticsUnreadCount)<span class="sidebar-full-only rounded-full px-2 py-0.5 text-xs font-bold bg-purple-100 text-purple-800">{{ $logisticsUnreadCount > 99 ? '99+' : $logisticsUnreadCount }}</span>@endif
                </a>

                <!-- Chat -->
                <a href="{{ route('logistics.chat') }}" 
                   title="Chat / Messaging" 
                   id="logistics-sidebar-chat-launch"
                   data-chat-launch="logistics-chat"
                   class="nav-item flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.chat*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.chat*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <span class="sidebar-full-only truncate">Chat / Messaging</span>
                    </div>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Chat / Messaging</span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Account</span>
                </div>

                <!-- Account -->
                <a href="{{ route('logistics.account') }}" title="Account" class="nav-item flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition group relative {{ request()->routeIs('logistics.account*') ? 'bg-[#6F6382] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ request()->routeIs('logistics.account*') ? 'bg-white/20 text-white' : 'text-slate-400 group-hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="sidebar-full-only truncate">Account Management</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">Account Management</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" title="Log out" aria-label="Log out" class="nav-item w-full flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition text-slate-600 hover:bg-slate-100 hover:text-slate-900 cursor-pointer">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        <span class="sidebar-full-only">Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Bar -->
            <header class="logistics-topbar hidden lg:flex items-center justify-between px-6 py-3.5 bg-white border-b border-slate-200 shrink-0 shadow-sm">
                <div>
                    <h1 class="text-base font-bold text-slate-800 tracking-tight">@yield('page_title', 'Logistics Hub')</h1>
                    <p class="text-xs text-slate-500 mt-0.5">@yield('page_subtitle', 'cartzy Sorting & Fulfillment Center')</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('logistics.account') }}" class="flex items-center gap-2 bg-slate-100 hover:bg-blue-50 border border-slate-200 px-3 py-1.5 rounded-xl transition group">
                        <span class="w-7 h-7 rounded-lg bg-[#6F6382] text-white flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-[#564B68] truncate max-w-[140px]">{{ Auth::user()->name ?? 'Hub Manager' }}</span>
                    </a>
                    <div class="logistics-header-notifications">
                        <a href="{{ route('logistics.notifications') }}" title="Notifications" aria-label="Notifications, {{ $logisticsUnreadCount }} unread" @if(request()->routeIs('logistics.notifications*')) aria-current="page" @endif>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                            @if($logisticsUnreadCount)
                                <span class="logistics-header-notification-count" aria-hidden="true">{{ $logisticsUnreadCount > 99 ? '99+' : $logisticsUnreadCount }}</span>
                            @endif
                        </a>
                    </div>
                    @include('logistics.partials.header-logout')
                </div>
            </header>

            <div class="logistics-content flex-1 overflow-y-auto p-5 lg:p-7">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold px-4 py-3 rounded-xl" role="status">
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
        if (window.Chart) {
            Chart.defaults.font.family = "'Lato', sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#91809C';
            Chart.defaults.borderColor = '#EEE7F2';
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.boxWidth = 8;
            Chart.defaults.plugins.legend.labels.padding = 20;
        }
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('logistics-sidebar');
            sidebar.classList.toggle('is-collapsed');
        }
    </script>
    {{-- Floating Message Bubble (Hidden by default, appears if clicked in sidebar or minimized) --}}
    @php
        $currentHubProvider = Auth::user()?->logisticsProvider;
        $unreadMessagesCount = $currentHubProvider
            ? \App\Models\LogisticsMessage::where('logistics_provider_id', $currentHubProvider->id)
                ->where('sender_type', '!=', 'hub')
                ->whereNull('read_at')
                ->count()
            : 0;
    @endphp
    <div class="logistics-chat-bubble-form" id="logistics-chat-bubble-wrap" hidden>
        <button type="button" 
           class="chat-bubble" 
           id="logistics-chat-bubble-btn"
           title="Click to chat here, double-click to open Messages" 
           aria-controls="logistics-chat-popup" 
           aria-expanded="false" 
           aria-label="Open Chat / Messaging">
            <span class="chat-bubble-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="chat-bubble-badge" id="logistics-chat-bubble-badge" aria-label="{{ $unreadMessagesCount }} unread messages" {{ $unreadMessagesCount > 0 ? '' : 'hidden' }}>
                    {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                </span>
            </span>
        </button>
    </div>

    {{-- Floating Chat Popup Widget (Matching Screenshot UI) --}}
    <section class="pd-chat cw" id="logistics-chat-popup" role="dialog" aria-labelledby="logistics-chat-title" 
             data-conversations-url="{{ route('logistics.popup.conversations') }}" 
             data-inbox-url="{{ route('logistics.chat') }}" 
             data-title="Cartzy Logistics Chat" 
             data-subtitle="Pickups, deliveries, and rider updates" 
             data-empty-text="Say hello! Ask the seller about sizes, stock, or shipping." hidden>
        <header class="pd-chat-head">
            <button class="pd-chat-icon" type="button" id="logistics-chat-back" aria-label="Back to conversations" title="Back" hidden>&larr;</button>
            <div>
                <strong id="logistics-chat-title">Cartzy Logistics Chat</strong>
                <small id="logistics-chat-subtitle">Pickups, deliveries, and rider updates</small>
            </div>
            <a class="pd-chat-icon" id="logistics-chat-expand" href="{{ route('logistics.chat') }}" aria-label="Open full chat page" title="Open full chat">&#x2197;</a>
            <button class="pd-chat-icon pd-chat-minimize" type="button" id="logistics-chat-minimize" aria-label="Minimize to bubble" title="Minimize">&minus;</button>
            <button class="pd-chat-icon" type="button" id="logistics-chat-close" aria-label="Close chat" title="Close">&times;</button>
        </header>
        <div class="cw-list" id="logistics-chat-list" aria-live="polite"></div>
        <div class="pd-chat-messages" id="logistics-chat-messages" aria-live="polite" hidden></div>
        <form class="pd-chat-compose" id="logistics-chat-compose" hidden>
            <label class="pd-chat-sr" for="logistics-chat-input">Message</label>
            <textarea id="logistics-chat-input" name="message" rows="1" maxlength="5000" placeholder="Ask about this product…" required></textarea>
            <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
        </form>
        <p class="pd-chat-error" id="logistics-chat-error" role="alert" hidden></p>
    </section>

    <script src="{{ asset('js/logistics-chat-popup.js') }}?v={{ filemtime(public_path('js/logistics-chat-popup.js')) }}" defer></script>
    @stack('scripts')
    @include('partials.prevent-back-cache')
@include('partials.logout-confirm')
</body>
</html>
