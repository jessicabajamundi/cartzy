@extends('layouts.seller')

@section('title', 'Confirm Delivery & Escrow Releases | Seller Centre')
@section('page_title', 'Confirmed Deliveries & Escrow Release')

@section('content')
<div class="space-y-6">

    <!-- Header & Notification Alert -->
    <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-emerald-700/50">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-2xl font-extrabold font-heading">Confirmed Deliveries & Escrow Release</h1>
            </div>
            <p class="text-xs text-emerald-200 mt-1 max-w-xl leading-relaxed">
                When a customer receives and accepts their package, the platform instantly notifies you and automatically releases order escrow funds (net of 10% platform fee) into your Seller Wallet.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/10 px-4 py-2.5 rounded-2xl border border-white/20 text-center">
                <div class="text-2xl font-black text-emerald-300">{{ count($deliveredOrders) }}</div>
                <div class="text-[10px] text-emerald-200 font-bold uppercase">Completed Deliveries</div>
            </div>
            <div class="bg-white/10 px-4 py-2.5 rounded-2xl border border-white/20 text-center">
                <div class="text-2xl font-black text-white">₱{{ number_format($totalNetEarnings, 2) }}</div>
                <div class="text-[10px] text-emerald-200 font-bold uppercase">Total Net Released</div>
            </div>
        </div>
    </div>

    <!-- Notification Highlight Banner -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3 text-emerald-900 text-xs">
        <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <div>
            <span class="font-extrabold">Automatic Delivery Notification:</span>
            <span>Once the courier rider completes package handover and the customer confirms receipt via app or OTP, funds are released in real-time with zero hold periods.</span>
        </div>
    </div>

    <!-- Confirmed Delivered Orders Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Delivered Orders History</h2>
                <p class="text-xs text-slate-500">Record of successfully received packages and escrow settlements</p>
            </div>
            <a href="{{ route('seller.reports') }}" class="text-xs font-bold text-[#6F6382] hover:underline">
                View Financial Statement &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Order ID & Delivered Date</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Product Items</th>
                        <th class="p-4">Gross Total</th>
                        <th class="p-4">10% Platform Fee</th>
                        <th class="p-4">Net Payout to Wallet</th>
                        <th class="p-4">Escrow Status</th>
                        <th class="p-4 text-right">Waybill</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($deliveredOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-900">#{{ $order['id'] }}</div>
                                <div class="text-[11px] text-emerald-700 font-bold flex items-center gap-1 mt-0.5">
                                    <span>✓ {{ $order['delivered_at'] ?? 'Delivered on time' }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $order['buyer_name'] }}</div>
                                <div class="text-[10px] text-slate-400">Confirmed by Customer</div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 max-w-xs truncate">
                                    <img src="{{ $order['items'][0]['image'] }}" alt="" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <span class="truncate font-semibold">{{ $order['items'][0]['name'] }}</span>
                                </div>
                            </td>
                            <td class="p-4 font-bold text-slate-800">
                                ₱{{ number_format($order['total_amount'], 2) }}
                            </td>
                            <td class="p-4 font-medium text-slate-400">
                                -₱{{ number_format($order['total_amount'] * 0.10, 2) }}
                            </td>
                            <td class="p-4">
                                <div class="font-black text-emerald-700 text-sm">
                                    +₱{{ number_format($order['total_amount'] * 0.90, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400">Credited to Wallet</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ✓ Escrow Released
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('seller.orders.waybill', $order['id']) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>AWB</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                No completed deliveries found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
