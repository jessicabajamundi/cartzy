@extends('layouts.seller')

@section('title', 'Financial & Profit Reports | Seller Centre')
@section('page_title', 'Financial Reports & Profit Analytics')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Summary -->
    <div class="bg-gradient-to-r from-slate-900 via-[#2D2438] to-[#564B68] rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-slate-700/50">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h1 class="text-2xl font-extrabold font-heading">Financial & Profit Statements</h1>
            </div>
            <p class="text-xs text-slate-300 mt-2 max-w-xl leading-relaxed">
                Filter by custom date ranges to audit gross sales, platform commission fees (10%), net payout earnings, and SKU performance.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2.5 rounded-xl border border-white/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Statement</span>
            </button>
            <button onclick="exportToCSV()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export to CSV</span>
            </button>
        </div>
    </div>

    <!-- Date Picker Filter Card (Required: "date picker as to from and to date") -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Date Range Filter</span>
                <span class="text-[10px] text-slate-400 font-normal">Select From Date and To Date to generate financial metrics</span>
            </h2>
        </div>

        <form action="{{ route('seller.reports') }}" method="GET" class="flex flex-wrap items-end gap-4 text-xs">
            <!-- From Date Picker -->
            <div class="flex-1 min-w-[200px]">
                <label for="from_date" class="block font-bold text-slate-700 mb-1">From Date</label>
                <input 
                    type="date" 
                    id="from_date" 
                    name="from_date" 
                    value="{{ $reportSummary['from_date'] }}" 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-[#6F6382]"
                >
            </div>

            <!-- To Date Picker -->
            <div class="flex-1 min-w-[200px]">
                <label for="to_date" class="block font-bold text-slate-700 mb-1">To Date</label>
                <input 
                    type="date" 
                    id="to_date" 
                    name="to_date" 
                    value="{{ $reportSummary['to_date'] }}" 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-[#6F6382]"
                >
            </div>

            <!-- Filter Submit Button -->
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-6 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                    <span>Generate Report</span>
                </button>
                <a href="{{ route('seller.reports') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- Quick Preset Buttons -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 text-xs text-slate-500 overflow-x-auto">
            <span class="font-bold shrink-0">Quick Ranges:</span>
            <a href="{{ route('seller.reports', ['from_date' => now()->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Today</a>
            <a href="{{ route('seller.reports', ['from_date' => now()->subDays(7)->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Last 7 Days</a>
            <a href="{{ route('seller.reports', ['from_date' => now()->startOfMonth()->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">This Month</a>
            <a href="{{ route('seller.reports', ['from_date' => now()->subDays(30)->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Last 30 Days</a>
        </div>
    </div>

    <!-- Financial Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Gross Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gross Sales</span>
            <div class="mt-2">
                <div class="text-2xl font-black text-slate-900">₱{{ number_format($reportSummary['gross_sales'], 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Across {{ $reportSummary['total_orders'] }} completed orders</div>
            </div>
        </div>

        <!-- Platform Commission (10%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Platform Commission (10%)</span>
            <div class="mt-2">
                <div class="text-2xl font-black text-rose-600">-₱{{ number_format($reportSummary['platform_commission'], 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Platform service & hosting fee</div>
            </div>
        </div>

        <!-- Net Seller Profit -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between bg-emerald-50/20 border-emerald-200">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Net Profit (Take Home)</span>
            <div class="mt-2">
                <div class="text-2xl font-black text-emerald-700">₱{{ number_format($reportSummary['net_profit'], 2) }}</div>
                <div class="text-[11px] text-emerald-800 font-bold mt-0.5">Credited to Seller Wallet</div>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Average Order Value (AOV)</span>
            <div class="mt-2">
                <div class="text-2xl font-black text-slate-900">₱{{ number_format($reportSummary['avg_order_value'], 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Avg spend per customer checkout</div>
            </div>
        </div>

    </div>

    <!-- Top Performing Products in Period -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Top Selling Products & Profit Contribution</span>
            </h2>
            <p class="text-xs text-slate-500">Breakdown of best-performing inventory items during the selected date window</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Product Name</th>
                        <th class="p-4">Category</th>
                        <th class="p-4 text-center">Units Sold</th>
                        <th class="p-4">Gross Revenue</th>
                        <th class="p-4">10% Platform Fee</th>
                        <th class="p-4 text-right">Net Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($topProducts as $prod)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-bold text-slate-900">{{ $prod['name'] }}</td>
                            <td class="p-4">{{ $prod['category'] }}</td>
                            <td class="p-4 text-center font-black">{{ $prod['units'] }}</td>
                            <td class="p-4 font-bold">₱{{ number_format($prod['revenue'], 2) }}</td>
                            <td class="p-4 text-rose-600">-₱{{ number_format($prod['commission'], 2) }}</td>
                            <td class="p-4 text-right font-black text-emerald-700">₱{{ number_format($prod['net'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Itemized Orders Ledger -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Itemized Transaction Ledger</h2>
                <p class="text-xs text-slate-500">
                    Orders dated between <strong>{{ $reportSummary['from_date'] }}</strong> to <strong>{{ $reportSummary['to_date'] }}</strong>
                </p>
            </div>
            <span class="text-xs text-slate-500 font-bold">{{ count($filteredTransactions) }} Transactions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Date & Ref</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Item Details</th>
                        <th class="p-4">Order Gross</th>
                        <th class="p-4">Platform Fee (10%)</th>
                        <th class="p-4">Net Payout</th>
                        <th class="p-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($filteredTransactions as $txn)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-900">{{ $txn['id'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $txn['date'] }}</div>
                            </td>
                            <td class="p-4 font-bold text-slate-800">{{ $txn['customer'] }}</td>
                            <td class="p-4 max-w-xs truncate">{{ $txn['item'] }}</td>
                            <td class="p-4 font-bold text-slate-900">₱{{ number_format($txn['order_amount'], 2) }}</td>
                            <td class="p-4 text-rose-600 font-medium">-₱{{ number_format($txn['platform_fee'], 2) }}</td>
                            <td class="p-4 font-black text-emerald-700">₱{{ number_format($txn['seller_net'], 2) }}</td>
                            <td class="p-4 text-right">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                    {{ strtoupper($txn['status']) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function exportToCSV() {
        const rows = [
            ["Transaction ID", "Date", "Customer", "Item", "Order Gross", "Platform Fee", "Net Payout", "Status"],
            @foreach($filteredTransactions as $txn)
                ["{{ $txn['id'] }}", "{{ $txn['date'] }}", "{{ $txn['customer'] }}", "{{ $txn['item'] }}", "{{ $txn['order_amount'] }}", "{{ $txn['platform_fee'] }}", "{{ $txn['seller_net'] }}", "{{ $txn['status'] }}"],
            @endforeach
        ];
        let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "cartzy_seller_financial_report_{{ $reportSummary['from_date'] }}_to_{{ $reportSummary['to_date'] }}.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection
