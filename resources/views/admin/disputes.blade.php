@extends('layouts.admin')

@section('title', 'Manage Complaints & Disputes | cartzy')
@section('page_title', 'Manage Complaints and Disputes')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Banner -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">3-Way Dispute & Complaint Mediation Hub</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Review buyer return claims, evaluate merchant packing proofs, verify courier waybill & delivery geo-tagging, and render binding resolution rulings.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-slate-900 text-white font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                <span>⚖️</span> Escrow Protection Engine Active
            </span>
        </div>
    </div>

    <!-- Dispute List -->
    <div class="space-y-6">
        @forelse($disputes as $d)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-6">
            
            <!-- Top Dispute Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-black text-base">
                        ⚖️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-black text-slate-900">Dispute #DISP-{{ $d['id'] }}</h2>
                            <span class="text-xs font-bold text-slate-500">Order: {{ $d['order_id'] }}</span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $d['status'] === 'open' ? 'bg-rose-100 text-rose-800' : ($d['status'] === 'under_investigation' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ str_replace('_', ' ', strtoupper($d['status'])) }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">Opened: {{ $d['opened_at'] }}</div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Disputed Escrow Amount</span>
                    <span class="text-lg font-black text-rose-600">₱{{ number_format($d['amount'], 2) }}</span>
                </div>
            </div>

            <!-- 3-Way Stakeholder Matrix (Buyer, Seller, Courier) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- Buyer Info -->
                <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 space-y-1">
                    <div class="text-[10px] uppercase font-black text-blue-700 tracking-wider flex items-center gap-1.5">
                        <span>👤 Buyer (Complainant)</span>
                    </div>
                    <div class="font-bold text-slate-900 text-xs">{{ $d['buyer_name'] }}</div>
                    <div class="text-[11px] text-slate-600">Claims: <strong class="text-rose-600">{{ $d['issue_category'] }}</strong></div>
                </div>

                <!-- Seller Info -->
                <div class="p-4 bg-purple-50/50 rounded-xl border border-purple-100 space-y-1">
                    <div class="text-[10px] uppercase font-black text-purple-700 tracking-wider flex items-center gap-1.5">
                        <span>🏬 Merchant Seller</span>
                    </div>
                    <div class="font-bold text-slate-900 text-xs">{{ $d['seller_name'] }}</div>
                    <div class="text-[11px] text-slate-600">Item: <strong>{{ $d['item_name'] }}</strong></div>
                </div>

                <!-- Courier Info -->
                <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100 space-y-1">
                    <div class="text-[10px] uppercase font-black text-amber-700 tracking-wider flex items-center gap-1.5">
                        <span>🛵 Delivery Courier</span>
                    </div>
                    <div class="font-bold text-slate-900 text-xs">{{ $d['courier_name'] }}</div>
                    <div class="text-[11px] text-slate-600">Status: In-transit / Handover Audit</div>
                </div>

            </div>

            <!-- Complaint Statement & Supporting Evidence -->
            <div class="space-y-3">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1">Buyer Complaint Description:</div>
                    <p class="text-xs text-slate-800 leading-relaxed font-medium">"{{ $d['description'] }}"</p>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Uploaded Photographic & Digital Evidence:</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($d['evidence'] as $ev)
                        <div class="bg-white border border-slate-200 text-slate-700 text-xs px-3 py-1.5 rounded-lg flex items-center gap-2 font-medium shadow-2xs">
                            <span class="text-indigo-600">📷</span>
                            <span>{{ $ev }}</span>
                            <span class="text-emerald-600 text-[10px] font-bold">Verified</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($d['admin_notes'])
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs rounded-xl">
                <strong class="text-emerald-800 block mb-0.5">Admin Resolution Verdict:</strong>
                {{ $d['admin_notes'] }}
            </div>
            @endif

            <!-- Admin Ruling & 3-Way Coordinator Resolution Actions -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Admin Action triggers official SMS & Email dispatch to all 3 parties.
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    
                    <!-- Issue Refund to Buyer -->
                    <form action="{{ route('admin.disputes.resolve', $d['id']) }}" method="POST">
                        @csrf
                        <input type="hidden" name="resolution" value="refund_buyer">
                        <input type="hidden" name="notes" value="Full refund issued to Buyer wallet due to verified package damage during courier transit.">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <span>💸 Full Refund to Buyer</span>
                        </button>
                    </form>

                    <!-- Release to Seller -->
                    <form action="{{ route('admin.disputes.resolve', $d['id']) }}" method="POST">
                        @csrf
                        <input type="hidden" name="resolution" value="release_to_seller">
                        <input type="hidden" name="notes" value="Merchant evidence verified item was packed properly and matched specifications. Escrow released to seller.">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <span>🏬 Release Funds to Seller</span>
                        </button>
                    </form>

                    <!-- Coordinate via Chat -->
                    <a href="{{ route('admin.chat') }}" class="bg-slate-900 hover:bg-black text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5">
                        <span>💬 Message Parties</span>
                    </a>

                </div>
            </div>

        </div>
        @empty
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-500 text-sm">
            No active disputes or complaints found.
        </div>
        @endforelse
    </div>

</div>
@endsection
