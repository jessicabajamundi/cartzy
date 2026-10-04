@extends('layouts.admin')

@section('title', 'Generate Reports | cartzy')
@section('page_title', 'Platform Reports & Analytics')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header & Action Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">Financial & Performance Reports</h1>
            <p class="text-xs text-slate-500 mt-1">
                Generate, analyze, and export Sales Summary Reports and Platform 10% Commission Revenue breakdowns.
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>
            <button onclick="exportCSV()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Filters & Report Type Selector -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-4">
        
        <!-- Report Type Toggle -->
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mr-2">Report Type:</span>
            <a href="{{ route('admin.reports', ['type' => 'sales_summary', 'range' => $dateRange]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 {{ $reportType === 'sales_summary' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Sales Summary Report</span>
            </a>
            <a href="{{ route('admin.reports', ['type' => 'commission_report', 'range' => $dateRange]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 {{ $reportType === 'commission_report' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>10% Commission Report</span>
            </a>
        </div>

        <!-- Date Range Filter -->
        <div class="flex items-center gap-2 text-xs">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Date Range:</span>
            <a href="{{ route('admin.reports', ['type' => $reportType, 'range' => 'this_week']) }}" class="px-2.5 py-1 rounded font-semibold {{ $dateRange === 'this_week' ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-500' }}">This Week</a>
            <a href="{{ route('admin.reports', ['type' => $reportType, 'range' => 'this_month']) }}" class="px-2.5 py-1 rounded font-semibold {{ $dateRange === 'this_month' ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-500' }}">This Month</a>
            <a href="{{ route('admin.reports', ['type' => $reportType, 'range' => 'ytd']) }}" class="px-2.5 py-1 rounded font-semibold {{ $dateRange === 'ytd' ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-500' }}">Year-to-Date</a>
        </div>

    </div>

    <!-- Report Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <span class="text-slate-400 text-[10px] uppercase font-black tracking-wider block">Gross Sales (GMV)</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">₱{{ number_format($salesData['gross_sales'], 2) }}</span>
            <span class="text-[11px] text-emerald-600 font-bold mt-1 block">Across {{ $salesData['total_orders'] }} Total Orders</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <span class="text-slate-400 text-[10px] uppercase font-black tracking-wider block">10% Platform Revenue</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">₱{{ number_format($salesData['platform_10_percent'], 2) }}</span>
            <span class="text-[11px] text-slate-500 mt-1 block">Fixed Take Rate</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <span class="text-slate-400 text-[10px] uppercase font-black tracking-wider block">Fulfilled Orders</span>
            <span class="text-2xl font-black text-indigo-600 mt-1 block">{{ $salesData['completed_orders'] }} Completed</span>
            <span class="text-[11px] text-slate-500 mt-1 block">94.3% Success Rate</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <span class="text-slate-400 text-[10px] uppercase font-black tracking-wider block">Disputed / Refunded</span>
            <span class="text-2xl font-black text-rose-600 mt-1 block">{{ $salesData['refunded_orders'] }} Orders</span>
            <span class="text-[11px] text-rose-600 font-bold mt-1 block">₱34,500 Refund Escrow</span>
        </div>
    </div>

    <!-- Category Sales Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-black text-base text-slate-900">Category Revenue & Commission Distribution</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sales contribution and platform commission share per trade category.</p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">5 Active Categories</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left" id="reportTable">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4 text-right">Gross GMV</th>
                        <th class="py-3 px-4 text-center">Market Share</th>
                        <th class="py-3 px-4 text-right">10% Platform Revenue</th>
                        <th class="py-3 px-4 text-right">Merchant Payout (90%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($salesData['top_categories'] as $cat)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            {{ $cat['category'] }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">₱{{ number_format($cat['sales'], 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-24 bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $cat['percentage'] }}%"></div>
                                </div>
                                <span class="font-bold text-slate-700 text-[11px]">{{ $cat['percentage'] }}%</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right font-black text-emerald-600">+₱{{ number_format($cat['commission'], 2) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-700">₱{{ number_format($cat['sales'] * 0.9, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="reportsExportData" 
         data-report-type="{{ $reportType }}" 
         data-categories='@json($salesData['top_categories'] ?? [])' 
         class="hidden"></div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/reports.js') }}"></script>
@endpush
@endsection
