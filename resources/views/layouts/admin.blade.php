<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Platform Dashboard | cartzy')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

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
        #admin-sidebar.is-collapsed {
            width: 5rem !important; /* 80px */
        }
        #admin-sidebar.is-collapsed .sidebar-full-only {
            display: none !important;
        }
        #admin-sidebar.is-collapsed .sidebar-mini-only {
            display: flex !important;
        }
        #admin-sidebar.is-collapsed .nav-item {
            justify-content: center !important;
            padding: 0.85rem 0.5rem !important;
        }
        #admin-sidebar.is-collapsed .brand-header {
            justify-content: center !important;
            padding: 1.25rem 0.5rem !important;
        }
        #admin-sidebar.is-collapsed .user-card-full {
            display: none !important;
        }
        #admin-sidebar.is-collapsed .user-card-mini {
            display: flex !important;
        }
        #admin-sidebar.is-collapsed .sidebar-section-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 0.6rem auto !important;
            width: 1.75rem !important;
            padding: 0 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="h-screen bg-slate-50 text-slate-900 antialiased flex flex-col overflow-hidden selection:bg-[#A8A0B2] selection:text-white">

    <!-- Top Mobile Header -->
    <div class="lg:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50 border-b border-slate-800 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('admin-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-[#6F6382] text-white flex items-center justify-center font-black text-sm">🛡️</span>
                <span class="font-bold text-sm tracking-wide">SUPER ADMIN</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="text-xs bg-slate-800 px-3 py-1.5 rounded-lg text-slate-200 border border-slate-700">Dashboard</a>
        </div>
    </div>

    <div class="flex flex-1 h-full min-h-0 overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 w-80 bg-white text-slate-700 transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out flex flex-col border-r border-slate-200 shadow-2xl lg:static lg:h-full lg:shadow-none shrink-0 overflow-hidden">
            
            <!-- Brand / Logo Area (Pinned Top) -->
            <div class="brand-header p-4 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white z-20 transition-all">
                <!-- Full Sidebar Brand Logo -->
                <div class="sidebar-full-only flex items-center justify-between w-full">
                    <a href="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 group">
                        <div class="bg-white px-3.5 py-2 rounded-2xl shadow-md inline-flex items-center justify-center group-hover:scale-[1.02] transition-transform w-fit">
                            <img src="{{ asset('images/logo.png') }}" alt="cartzy logo" class="h-9 w-auto object-contain">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black uppercase tracking-wider text-[#564B68] bg-[#F1EFF5] px-2.5 py-0.5 rounded-lg border border-[#E1DDE7] inline-flex items-center gap-1.5 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                ADMIN CONTROL
                            </span>
                        </div>
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" title="Collapse sidebar" class="p-2.5 text-slate-500 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 active:scale-95 rounded-xl transition flex items-center justify-center border border-slate-200 group relative shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="absolute left-full ml-3 top-1/2 -translate-y-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                            Collapse sidebar
                        </span>
                    </button>
                </div>

                <!-- Mini Collapsed Sidebar Logo & Toggle -->
                <div class="sidebar-mini-only hidden flex-col items-center gap-2.5 w-full py-1">
                    <a href="{{ route('admin.dashboard') }}" class="bg-white p-1.5 rounded-xl shadow-md flex items-center justify-center hover:scale-105 transition" title="cartzy E-Commerce">
                        <img src="{{ asset('images/favicon.png') }}" alt="cartzy" class="w-7 h-7 object-contain">
                    </a>
                    <button type="button" onclick="toggleSidebarCollapse()" title="Expand sidebar" class="p-2 text-slate-500 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 active:scale-95 rounded-xl transition flex items-center justify-center border border-slate-200 group relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="absolute left-full ml-3 top-1/2 -translate-y-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                            Expand sidebar
                        </span>
                    </button>
                </div>
            </div>

            <!-- Navigation Links (Smoothly Scrollable) -->
            <div class="flex-1 overflow-y-auto sidebar-scroll min-h-0 px-3 py-4 space-y-1.5 text-[15px]">
                
                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-2 pb-1 text-xs font-black uppercase tracking-wider text-slate-400 block">CORE OVERVIEW</span>
                </div>

                <!-- 1. Dashboard -->
                <a href="{{ route('admin.dashboard') }}" title="Dashboard & Overview" class="nav-item flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.dashboard') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span class="text-xl shrink-0">📊</span>
                    <span class="tracking-wide sidebar-full-only truncate">Dashboard & Overview</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Dashboard & Overview
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-xs font-black uppercase tracking-wider text-slate-400 block">ACCOUNT & USER CONTROL</span>
                </div>

                <!-- 2. Manage Registrations -->
                <a href="{{ route('admin.registrations') }}" title="Account Registrations (KYC)" class="nav-item flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.registrations*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3.5 truncate">
                        <span class="text-xl shrink-0">📝</span>
                        <span class="tracking-wide sidebar-full-only truncate">Account Registrations</span>
                    </div>
                    <span class="sidebar-full-only bg-amber-500 text-slate-950 text-xs font-black px-2.5 py-1 rounded-full shadow-xs">KYC</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-800 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Account Registrations (KYC)
                    </span>
                </a>

                <!-- 3. Manage User Accounts -->
                <a href="{{ route('admin.users') }}" title="User Accounts" class="nav-item flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.users*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span class="text-xl shrink-0">👥</span>
                    <span class="tracking-wide sidebar-full-only truncate">User Accounts</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        User Accounts
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-xs font-black uppercase tracking-wider text-slate-400 block">COMPLIANCE & GOVERNANCE</span>
                </div>

                <!-- 4. Monitor Seller Compliance -->
                <a href="{{ route('admin.compliance') }}" title="Seller Compliance" class="nav-item flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.compliance*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3.5 truncate">
                        <span class="text-xl shrink-0">🛡️</span>
                        <span class="tracking-wide sidebar-full-only truncate">Seller Compliance</span>
                    </div>
                    <span class="sidebar-full-only bg-rose-50 text-rose-600 text-xs font-bold px-2.5 py-1 rounded-lg border border-rose-200">Audit</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Seller Compliance (Audit)
                    </span>
                </a>

                <!-- 5. Manage Complaints & Disputes -->
                <a href="{{ route('admin.disputes') }}" title="Complaints & Disputes" class="nav-item flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.disputes*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3.5 truncate">
                        <span class="text-xl shrink-0">⚖️</span>
                        <span class="tracking-wide sidebar-full-only truncate">Complaints & Disputes</span>
                    </div>
                    <span class="sidebar-full-only bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-amber-200">3-Way</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Complaints & Disputes
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-xs font-black uppercase tracking-wider text-slate-400 block">FINANCE & ANALYTICS</span>
                </div>

                <!-- 6. Manage Commission (10%) -->
                <a href="{{ route('admin.commission') }}" title="Platform Commission" class="nav-item flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.commission*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3.5 truncate">
                        <span class="text-xl shrink-0">💰</span>
                        <span class="tracking-wide sidebar-full-only truncate">Platform Commission</span>
                    </div>
                    <span class="sidebar-full-only bg-emerald-50 text-emerald-700 text-xs font-black px-2.5 py-1 rounded-lg border border-emerald-200">10%</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Platform Commission (10%)
                    </span>
                </a>

                <!-- 7. Generate Reports -->
                <a href="{{ route('admin.reports') }}" title="Generate Reports" class="nav-item flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.reports*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span class="text-xl shrink-0">📑</span>
                    <span class="tracking-wide sidebar-full-only truncate">Generate Reports</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Generate Reports
                    </span>
                </a>

                <div class="sidebar-section-divider">
                    <span class="sidebar-full-only px-3 pt-4 pb-1 text-xs font-black uppercase tracking-wider text-slate-400 block">COMMUNICATION & SYSTEM</span>
                </div>

                <!-- 8. Chat / Messaging -->
                <a href="{{ route('admin.chat') }}" title="Chat & Messaging" class="nav-item flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.chat*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3.5 truncate">
                        <span class="text-xl shrink-0">💬</span>
                        <span class="tracking-wide sidebar-full-only truncate">Chat & Messaging</span>
                    </div>
                    <span class="sidebar-full-only w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Chat & Messaging
                    </span>
                </a>

                <!-- 9. Manage Platform Settings -->
                <a href="{{ route('admin.settings') }}" title="Platform Settings" class="nav-item flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.settings*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span class="text-xl shrink-0">⚙️</span>
                    <span class="tracking-wide sidebar-full-only truncate">Platform Settings</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Platform Settings
                    </span>
                </a>

                <!-- 10. Account Management -->
                <a href="{{ route('admin.account') }}" title="Account Profile" class="nav-item flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-bold transition group relative {{ request()->routeIs('admin.account*') ? 'bg-[#6F6382] text-white shadow-lg shadow-[#6F6382]/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span class="text-xl shrink-0">👤</span>
                    <span class="tracking-wide sidebar-full-only truncate">Account Profile</span>
                    <span class="sidebar-mini-only hidden absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50 pointer-events-none border border-slate-700">
                        Account Profile
                    </span>
                </a>
            </div>

            <!-- User Footer & Floating Quick Logout Card (Pinned Bottom) -->
            <div class="p-3 border-t border-slate-200 bg-white/95 backdrop-blur-md shrink-0 sticky bottom-0 z-20">
                <!-- Full Card -->
                <div class="user-card-full flex items-center justify-between p-2.5 px-3 rounded-2xl bg-slate-50 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center gap-3 overflow-hidden">
                        @if(Auth::user() && Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md">
                                {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                            </div>
                        @endif
                        <div class="truncate">
                            <div class="font-bold text-slate-800 text-sm truncate leading-tight">{{ Auth::user()->name ?? 'Jessica Bajamundi' }}</div>
                            <div class="text-xs text-slate-500 flex items-center gap-1.5 font-medium mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50"></span>
                                Super Admin
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0 m-0">
                        @csrf
                        <button type="submit" title="Quick Logout" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 active:scale-95 rounded-xl transition flex items-center justify-center group" aria-label="Logout">
                            <svg class="w-5 h-5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Mini Collapsed Card -->
                <div class="user-card-mini hidden flex flex-col items-center gap-2.5 py-1">
                    @if(Auth::user() && Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 shadow-md" title="{{ Auth::user()->name ?? 'Admin' }}">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white flex items-center justify-center font-black text-sm shadow-md" title="{{ Auth::user()->name ?? 'Admin' }}">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0 m-0">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 active:scale-95 rounded-xl transition flex items-center justify-center group relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
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
            
            <!-- Top Navbar for Admin -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 py-3 flex items-center justify-between shadow-2xs">
                
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        @yield('page_title', 'Platform Management')
                    </h2>
                </div>

                <!-- Right Actions: Notifications, Storefront Link, Quick Status -->
                <div class="flex items-center gap-3">
                    
                    <a href="/" target="_blank" class="hidden sm:flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3 py-2 rounded-lg transition border border-slate-200">
                        <span>🏬 View Storefront</span>
                        <span class="text-slate-400">&rarr;</span>
                    </a>

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button onclick="document.getElementById('notif-panel').classList.toggle('hidden')" class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition border border-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 border-2 border-white rounded-full"></span>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden text-xs">
                            <div class="p-3.5 bg-slate-900 text-white flex items-center justify-between">
                                <div class="font-bold flex items-center gap-2">
                                    <span>🔔 Notifications</span>
                                    <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full">4 New</span>
                                </div>
                                <span class="text-[11px] text-slate-400">Live Platform Feed</span>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                                <a href="{{ route('admin.registrations') }}" class="p-3 hover:bg-slate-50 flex items-start gap-3 transition">
                                    <span class="text-base p-1.5 bg-amber-50 text-amber-600 rounded-lg">📝</span>
                                    <div>
                                        <div class="font-bold text-slate-900">New Seller KYC Submission</div>
                                        <p class="text-slate-500 text-[11px] mt-0.5">TechZone Gadgets submitted DTI & BIR 2303 for verification.</p>
                                        <span class="text-[10px] text-slate-400 font-medium">10 mins ago</span>
                                    </div>
                                </a>
                                <a href="{{ route('admin.disputes') }}" class="p-3 hover:bg-slate-50 flex items-start gap-3 transition">
                                    <span class="text-base p-1.5 bg-rose-50 text-rose-600 rounded-lg">⚖️</span>
                                    <div>
                                        <div class="font-bold text-slate-900">Dispute Escalated</div>
                                        <p class="text-slate-500 text-[11px] mt-0.5">Buyer #BY-9021 filed dispute: Item Damaged during Transit.</p>
                                        <span class="text-[10px] text-slate-400 font-medium">35 mins ago</span>
                                    </div>
                                </a>
                                <a href="{{ route('admin.compliance') }}" class="p-3 hover:bg-slate-50 flex items-start gap-3 transition">
                                    <span class="text-base p-1.5 bg-[#F1EFF5] text-[#6F6382] rounded-lg border border-[#E1DDE7]">🛡️</span>
                                    <div>
                                        <div class="font-bold text-slate-900">Seller Compliance Alert</div>
                                        <p class="text-slate-500 text-[11px] mt-0.5">ShoeHaven listed "Replica Sneakers" under Footwear.</p>
                                        <span class="text-[10px] text-slate-400 font-medium">2 hours ago</span>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2.5 bg-slate-50 text-center border-t border-slate-100">
                                <a href="{{ route('admin.dashboard') }}" class="text-[#6F6382] font-bold hover:underline text-[11px]">View All Dashboard Alerts &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Super Admin Badge -->
                    <div class="hidden sm:flex items-center gap-2.5 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-xs shadow-xs">
                            AD
                        </div>
                        <div class="text-left leading-tight">
                            <div class="text-xs font-bold text-slate-900">{{ Auth::user()->name ?? 'admin' }}</div>
                            <div class="text-[10px] text-slate-500">Super Administrator</div>
                        </div>
                    </div>

                </div>

            </header>

            <!-- Flash Session Alerts (Auto-dismisses in 10 seconds) -->
            <div class="px-4 sm:px-8 pt-4">
                @if(session('success'))
                    <div id="flash-alert-success" class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center justify-between shadow-2xs transition-all duration-700">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">✅</span>
                            <span class="font-bold text-sm">{{ session('success') }}</span>
                        </div>
                        <button onclick="dismissAlert('flash-alert-success')" class="text-emerald-600 hover:text-emerald-900 font-bold text-sm">✕</button>
                    </div>
                @endif

                @if(session('info'))
                    <div id="flash-alert-info" class="mb-4 p-4 bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs rounded-xl flex items-center justify-between shadow-2xs transition-all duration-700">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">ℹ️</span>
                            <span class="font-bold text-sm">{{ session('info') }}</span>
                        </div>
                        <button onclick="dismissAlert('flash-alert-info')" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">✕</button>
                    </div>
                @endif
            </div>

            <!-- Page Content Injection -->
            <main class="flex-1 px-4 sm:px-8 py-6">
                @yield('content')
            </main>

            <!-- Admin Footer -->
            <footer class="bg-white border-t border-slate-200 px-4 sm:px-8 py-4 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-2">
                <div>
                    &copy; 2026 <strong>cartzy E-Commerce Platform</strong> &bull; Super Admin Center
                </div>
                <div class="flex items-center gap-4 text-[11px] font-medium text-slate-600">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> 10% Commission Engine Active</span>
                    <span>&bull;</span>
                    <span>XAMPP-Free Demo Mode Ready</span>
                </div>
            </footer>

        </div>

    </div>

    <script src="{{ asset('js/admin/admin.js') }}"></script>

    @stack('scripts')
</body>
</html>
