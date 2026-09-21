@extends('layouts.admin')

@section('title', 'Monitor Seller Compliance | cartzy')
@section('page_title', 'Monitor Seller Compliance')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Banner -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">Seller Category & Product Compliance Engine</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Automatically flags listing category mismatches, identifies prohibited or counterfeit goods, and enforces platform seller sanctions (warnings, product takedowns, and account suspensions).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-rose-50 text-rose-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-rose-200 flex items-center gap-1.5">
                <span>🛡️</span> Zero-Tolerance Policy Active
            </span>
        </div>
    </div>

    <!-- Category Compliance Rules Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div class="p-4 bg-purple-50/60 rounded-2xl border border-purple-200 space-y-1">
            <div class="font-bold text-purple-900 flex items-center gap-2">
                <span>1️⃣</span> Category Verification
            </div>
            <p class="text-purple-700 text-[11px] leading-relaxed">
                Merchants must only list items within their approved KYC trade category (e.g. Fashion, Electronics).
            </p>
        </div>
        <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200 space-y-1">
            <div class="font-bold text-amber-900 flex items-center gap-2">
                <span>2️⃣</span> Prohibited & Banned Goods
            </div>
            <p class="text-amber-700 text-[11px] leading-relaxed">
                Counterfeits, replicas, unapproved pharmaceuticals, weapons, and age-restricted goods without license are strictly forbidden.
            </p>
        </div>
        <div class="p-4 bg-rose-50/60 rounded-2xl border border-rose-200 space-y-1">
            <div class="font-bold text-rose-900 flex items-center gap-2">
                <span>3️⃣</span> Sanctions & Strikes
            </div>
            <p class="text-rose-700 text-[11px] leading-relaxed">
                1st Strike: Official warning. 2nd Strike: Product takedown. 3rd Strike: Instant merchant account suspension.
            </p>
        </div>
    </div>

    <!-- Compliance Audit Table / Cards -->
    <div class="space-y-4">
        @forelse($items as $item)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 transition hover:border-slate-300">
            
            <div class="flex flex-wrap items-start justify-between gap-6">
                
                <!-- Product Preview & Details -->
                <div class="flex items-start gap-4 max-w-3xl">
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-slate-200 shrink-0 bg-slate-100">
                    
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-black text-slate-900">{{ $item['product_name'] }}</h2>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ $item['status'] === 'flagged' ? 'bg-rose-100 text-rose-800 border border-rose-200 animate-pulse' : ($item['status'] === 'warning_issued' ? 'bg-amber-100 text-amber-800' : ($item['status'] === 'verified_compliant' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800')) }}">
                                {{ str_replace('_', ' ', strtoupper($item['status'])) }}
                            </span>
                        </div>

                        <!-- Seller Info -->
                        <div class="text-xs text-slate-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <div><strong class="text-slate-900">Seller:</strong> {{ $item['seller_name'] }} ({{ $item['seller_email'] }})</div>
                            <div><strong class="text-slate-900">Registered Category:</strong> <span class="text-indigo-600 font-bold">{{ $item['seller_category'] }}</span></div>
                            <div><strong class="text-slate-900">Listed Under:</strong> <span class="text-rose-600 font-bold">{{ $item['product_category'] }}</span></div>
                        </div>

                        <!-- Flag Reason -->
                        <div class="p-3 bg-rose-50/70 border border-rose-200 rounded-xl text-xs text-rose-900">
                            <div class="font-bold flex items-center gap-1.5 text-rose-700">
                                <span>⚠️ Violation Flag:</span>
                                <span>{{ $item['flag_type'] }}</span>
                            </div>
                            <p class="text-[11px] text-rose-800 mt-1 leading-relaxed">{{ $item['reason'] }}</p>
                        </div>

                        <div class="text-[11px] text-slate-400">
                            Current Seller Compliance Record: <strong>{{ $item['strikes'] ?? 0 }} Strike Warnings</strong>
                        </div>
                    </div>
                </div>

                <!-- Action Panel -->
                <div class="flex sm:flex-col items-center gap-2 shrink-0 w-full sm:w-56">
                    
                    <!-- Issue Warning -->
                    <form action="{{ route('admin.compliance.action', $item['id']) }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="action" value="warn">
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-3.5 py-2.5 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>⚠️ Issue Strike Warning</span>
                        </button>
                    </form>

                    <!-- Take Down Product -->
                    <form action="{{ route('admin.compliance.action', $item['id']) }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="action" value="suspend_product">
                        <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold text-xs px-3.5 py-2.5 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>🛑 Take Down Listing</span>
                        </button>
                    </form>

                    <!-- Suspend Seller -->
                    <form action="{{ route('admin.compliance.action', $item['id']) }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="action" value="suspend_seller">
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-3.5 py-2.5 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>⛔ Suspend Seller Account</span>
                        </button>
                    </form>

                    <!-- Dismiss / Mark Verified -->
                    <form action="{{ route('admin.compliance.action', $item['id']) }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="action" value="dismiss">
                        <button type="submit" class="w-full bg-white hover:bg-slate-50 text-slate-600 border border-slate-300 font-semibold text-xs px-3.5 py-2 rounded-xl transition">
                            ✓ Mark as Compliant
                        </button>
                    </form>

                </div>

            </div>

        </div>
        @empty
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-500 text-sm">
            All listed products currently comply with registered merchant categories and platform terms.
        </div>
        @endforelse
    </div>

</div>
@endsection
