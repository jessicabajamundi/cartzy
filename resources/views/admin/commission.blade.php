@extends('layouts.admin')

@section('title', 'Platform Commission (10%) Management | cartzy')
@section('page_title', 'Manage Platform Commission (10%)')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Summary -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800 flex flex-wrap items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase px-2.5 py-1 rounded-full border border-emerald-500/30">
                    Standard Rate: Fixed 10%
                </span>
                <span class="text-xs text-slate-400">Automated Escrow Splitting</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mt-2">10% Platform Commission Hub</h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mt-1 leading-relaxed">
                Every completed checkout automatically retains 10% platform revenue for operational costs, transaction processing, and platform maintenance while 90% is allocated for merchant payout.
            </p>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 text-center min-w-48">
            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-300 block">Total Platform 10% Cut</span>
            <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1 block">₱{{ number_format($totalCommission, 2) }}</span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">From ₱{{ number_format($totalSales, 2) }} Gross Sales</span>
        </div>
    </div>

    <!-- Financial Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Gross Merchandise Value (GMV)</div>
            <div class="text-2xl font-black text-slate-900 mt-2">₱{{ number_format($totalSales, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">100% total order checkout volume</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Platform Take (10%)</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">₱{{ number_format($totalCommission, 2) }}</div>
            <div class="text-[11px] text-emerald-600 font-bold mt-1">Net profit retained by cartzy</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Merchant Net Payout (90%)</div>
            <div class="text-2xl font-black text-indigo-600 mt-2">₱{{ number_format($sellerPayouts, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Disbursed to seller bank accounts</div>
        </div>

    </div>

    <!-- Interactive Commission Calculator -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
                <span>🧮</span> Interactive 10% Platform Commission Calculator
            </h3>
            <span class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-bold">Simulate Any Order Gross</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Enter Order Amount (₱):</label>
                <input type="number" id="calcOrderAmount" value="5000" oninput="calculateCommission()" class="w-full text-sm font-bold p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
            </div>

            <div class="p-3 bg-white rounded-xl border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Platform Commission (10%)</span>
                <span id="calcPlatformCut" class="text-lg font-black text-emerald-600 mt-0.5 block">₱500.00</span>
            </div>

            <div class="p-3 bg-white rounded-xl border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Seller Net Earnings (90%)</span>
                <span id="calcSellerNet" class="text-lg font-black text-indigo-600 mt-0.5 block">₱4,500.00</span>
            </div>
        </div>
    </div>

    <!-- Commission Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden space-y-4 p-6">
        <div>
            <h3 class="font-black text-base text-slate-900">Recent Platform Commission Ledger</h3>
            <p class="text-xs text-slate-500 mt-0.5">Real-time breakdown of gross checkout amounts and auto-deducted 10% fees per transaction.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Merchant Seller</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4 text-right">Gross Total</th>
                        <th class="py-3 px-4 text-center">Rate</th>
                        <th class="py-3 px-4 text-right">Platform 10% Cut</th>
                        <th class="py-3 px-4 text-right">Seller Net (90%)</th>
                        <th class="py-3 px-4 text-center">Escrow Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transactions as $tx)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $tx['order_id'] }}</td>
                        <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $tx['date'] }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $tx['seller'] }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $tx['buyer'] }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">₱{{ number_format($tx['order_amount'], 2) }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-500">10%</td>
                        <td class="py-3 px-4 text-right font-black text-emerald-600">+₱{{ number_format($tx['commission_amount'], 2) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-700">₱{{ number_format($tx['seller_net'], 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $tx['escrow_status'] === 'Released to Seller' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $tx['escrow_status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function calculateCommission() {
        const amount = parseFloat(document.getElementById('calcOrderAmount').value) || 0;
        const commission = amount * 0.10;
        const sellerNet = amount - commission;

        document.getElementById('calcPlatformCut').innerText = '₱' + commission.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcSellerNet').innerText = '₱' + sellerNet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>
@endpush
@endsection
