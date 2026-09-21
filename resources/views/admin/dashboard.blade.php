@extends('layouts.admin')

@section('title', 'Admin Platform Overview | cartzy')
@section('page_title', 'Platform Operations Dashboard')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Welcome & Quick Announcement Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase px-2.5 py-1 rounded-full border border-emerald-500/30 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Platform Engine Live
                    </span>
                    <span class="text-xs text-slate-400 font-medium">10% Platform Commission Enabled</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Welcome back, Admin!</h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Review and verify incoming seller & rider registrations, monitor product compliance, manage 10% commission revenue, and resolve customer dispute escalations.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.registrations') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                    <span>📝 Review KYC ({{ $stats['pending_registrations'] }})</span>
                </a>
                <a href="{{ route('admin.disputes') }}" class="bg-white/10 hover:bg-white/20 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition border border-white/15 flex items-center gap-2">
                    <span>⚖️ Disputes ({{ $stats['active_disputes'] }})</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Platform KPI Stats Cards (4 Columns) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total GMV -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Total GMV Sales</span>
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl text-sm">📈</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-3">₱{{ number_format($stats['total_gmv'], 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold mt-2">
                <span>↑ +18.4%</span>
                <span class="text-slate-400 font-normal text-[11px]">vs last month</span>
            </div>
        </div>

        <!-- 10% Platform Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>10% Platform Cut</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm">💰</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-3">₱{{ number_format($stats['platform_revenue'], 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
                <span>Auto-deducted per completed order</span>
            </div>
        </div>

        <!-- Pending Account Registrations -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Pending KYC Reviews</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-xl text-sm">📝</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 mt-3">{{ $stats['pending_registrations'] }} Applicants</div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
                <a href="{{ route('admin.registrations') }}" class="text-indigo-600 hover:underline font-bold text-[11px]">Verify documents &rarr;</a>
            </div>
        </div>

        <!-- Active Disputes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Active Disputes</span>
                <span class="p-2 bg-rose-50 text-rose-600 rounded-xl text-sm">⚖️</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 mt-3">{{ $stats['active_disputes'] }} Open</div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
                <a href="{{ route('admin.disputes') }}" class="text-rose-600 hover:underline font-bold text-[11px]">Coordinate 3-way &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Secondary Stats: User Breakdown -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                🛍️
            </div>
            <div>
                <div class="text-xs text-slate-500 font-semibold">Registered Buyers</div>
                <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_buyers']) }}</div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                🏬
            </div>
            <div>
                <div class="text-xs text-slate-500 font-semibold">Active Merchants & Sellers</div>
                <div class="text-xl font-black text-slate-900">{{ $stats['total_sellers'] }} Stores</div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                🛵
            </div>
            <div>
                <div class="text-xs text-slate-500 font-semibold">Express Couriers & Riders</div>
                <div class="text-xl font-black text-slate-900">{{ $stats['total_couriers'] }} Riders</div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Recent Registrations & Active Notifications -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Recent Applications for Review -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                        <span>📝</span> Recent Account Registrations (KYC)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Review submitted IDs, business permits, and rider licenses.</p>
                </div>
                <a href="{{ route('admin.registrations') }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($recentRegistrations as $reg)
                <div class="p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 hover:bg-slate-50 transition">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 {{ $reg['role'] === 'seller' ? 'bg-purple-100 text-purple-700' : ($reg['role'] === 'courier' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $reg['role'] === 'seller' ? '🏬' : ($reg['role'] === 'courier' ? '🛵' : '👤') }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $reg['name'] }}</span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $reg['role'] === 'seller' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($reg['role'] === 'courier' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                                    {{ $reg['role'] }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $reg['email'] }} &bull; {{ $reg['phone'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                                <span>📄 Submitted: {{ count($reg['documents']) }} documents</span>
                                <span>&bull;</span>
                                <span>{{ $reg['applied_at'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($reg['status'] === 'pending')
                            <a href="{{ route('admin.registrations') }}" class="bg-slate-900 hover:bg-black text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                Review & Verify
                            </a>
                        @elseif($reg['status'] === 'approved')
                            <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-200 flex items-center gap-1">
                                ✓ Approved
                            </span>
                        @else
                            <span class="bg-rose-50 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full border border-rose-200">
                                ✕ Disapproved
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right 1 Col: Platform Live Notifications & Escalations -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                        <span>🔔</span> Platform Event Feed
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time alerts & action notices.</p>
                </div>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto">
                @foreach($notifications as $notif)
                <a href="{{ $notif['link'] }}" class="block p-3.5 rounded-xl border border-slate-100 hover:border-slate-300 hover:bg-slate-50 transition {{ $notif['unread'] ? 'bg-indigo-50/40 border-indigo-100' : '' }}">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-900">{{ $notif['title'] }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $notif['time'] }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notif['message'] }}</p>
                </a>
                @endforeach
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('admin.chat') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                    Open Admin Live Chat Console &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Bottom Row: 10% Commission Quick Ledger & Dispute Quick View -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 10% Commission Quick Ledger -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                        <span>💰</span> Recent 10% Commission Deductions
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Automatically retained from completed merchant sales.</p>
                </div>
                <a href="{{ route('admin.commission') }}" class="text-xs font-bold text-indigo-600 hover:underline">Full Ledger &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-slate-400 uppercase text-[10px] font-bold border-b border-slate-100">
                        <tr>
                            <th class="pb-2">Order</th>
                            <th class="pb-2">Seller</th>
                            <th class="pb-2 text-right">Order Gross</th>
                            <th class="pb-2 text-right">10% Platform</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTransactions as $tx)
                        <tr>
                            <td class="py-2.5 font-bold text-slate-900">{{ $tx['order_id'] }}</td>
                            <td class="py-2.5 text-slate-600">{{ $tx['seller'] }}</td>
                            <td class="py-2.5 text-right font-medium text-slate-800">₱{{ number_format($tx['order_amount'], 2) }}</td>
                            <td class="py-2.5 text-right font-bold text-emerald-600">+₱{{ number_format($tx['commission_amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Disputes Snapshot -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                        <span>⚖️</span> Active Dispute Escalations
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">3-way mediation between Buyer, Seller, and Courier.</p>
                </div>
                <a href="{{ route('admin.disputes') }}" class="text-xs font-bold text-rose-600 hover:underline">Manage &rarr;</a>
            </div>

            <div class="space-y-3">
                @foreach($recentDisputes as $disp)
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900">#DISP-{{ $disp['id'] }} ({{ $disp['order_id'] }})</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $disp['status'] === 'open' ? 'bg-rose-100 text-rose-700' : ($disp['status'] === 'under_investigation' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ str_replace('_', ' ', strtoupper($disp['status'])) }}
                            </span>
                        </div>
                        <p class="text-slate-600 text-[11px] mt-1">{{ $disp['issue_category'] }}: {{ $disp['item_name'] }}</p>
                        <div class="text-[10px] text-slate-400 mt-0.5">Disputed Amount: <strong>₱{{ number_format($disp['amount'], 2) }}</strong></div>
                    </div>
                    <a href="{{ route('admin.disputes') }}" class="shrink-0 bg-slate-900 hover:bg-black text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition">
                        Investigate
                    </a>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
