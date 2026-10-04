@extends('layouts.logistics')

@section('title', 'Reports | Logistics Hub | cartzy')
@section('page_title', 'Generation of Reports')
@section('page_subtitle', 'Hub performance metrics, rider rankings, and area breakdowns')

@section('content')

{{-- Date Filter --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('logistics.reports') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-1.5">From Date</label>
            <input type="date" name="from" value="{{ $dateFrom }}"
                   class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>
        <div>
            <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-1.5">To Date</label>
            <input type="date" name="to" value="{{ $dateTo }}"
                   class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>
        <button type="submit" class="bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black text-sm px-6 py-2.5 rounded-xl transition shadow">
            Generate Report
        </button>
        <a href="#" onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm px-5 py-2.5 rounded-xl transition border border-slate-200">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print</span>
        </a>
    </form>
</div>

{{-- KPI Summary --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @php
    $kpis = [
        ['label'=>'Total Parcels Handled','value'=>number_format($reportData['total_parcels_handled']),'type'=>'parcels','color'=>'border-blue-200 text-blue-700 bg-blue-50'],
        ['label'=>'Delivered On Time',    'value'=>number_format($reportData['delivered_on_time']),    'type'=>'delivered','color'=>'border-emerald-200 text-emerald-700 bg-emerald-50'],
        ['label'=>'Failed Deliveries',    'value'=>number_format($reportData['failed_deliveries']),    'type'=>'failed','color'=>'border-rose-200 text-rose-700 bg-rose-50'],
        ['label'=>'Success Rate',         'value'=>$reportData['success_rate'].'%',                    'type'=>'rate','color'=>'border-violet-200 text-violet-700 bg-violet-50'],
        ['label'=>'Avg. Delivery Days',   'value'=>$reportData['avg_delivery_days'].' days',           'type'=>'days','color'=>'border-amber-200 text-amber-700 bg-amber-50'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-white rounded-2xl border {{ $kpi['color'] }} p-4 shadow-sm text-center">
        <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center {{ $kpi['color'] }}">
            @if($kpi['type']==='parcels')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            @elseif($kpi['type']==='delivered')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($kpi['type']==='failed')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            @elseif($kpi['type']==='rate')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            @endif
        </div>
        <div class="text-2xl font-black text-slate-800">{{ $kpi['value'] }}</div>
        <div class="text-xs font-semibold text-slate-500 mt-1">{{ $kpi['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Chart + Area Breakdown --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Volume Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-black text-slate-800 mb-1">Weekly Parcel Volume</h3>
        <p class="text-xs text-slate-500 mb-5">Received vs Delivered — by week</p>
        <canvas id="reportChart" height="200"></canvas>
    </div>

    {{-- Area Breakdown --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-black text-slate-800 mb-4">Top Delivery Areas</h3>
        <div class="space-y-3">
            @foreach($reportData['area_breakdown'] as $area)
            @php $pct = round($area['parcels'] / max($reportData['total_parcels_handled'],1) * 100); @endphp
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-semibold text-slate-700 truncate">{{ $area['area'] }}</span>
                    <span class="font-black text-slate-800 ml-2">{{ $area['parcels'] }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-[#1A6FA8] h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Top Riders --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-lg font-black text-slate-800">Top Performing Riders</h3>
        <span class="text-xs text-slate-400">{{ $dateFrom }} – {{ $dateTo }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Rank</th>
                    <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Rider</th>
                    <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Deliveries</th>
                    <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Success Rate</th>
                    <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Performance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($reportData['top_riders'] as $i => $rider)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-6 py-3">
                        @if($i===0) <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-black inline-flex items-center justify-center border border-amber-200">1</span>
                        @elseif($i===1) <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-black inline-flex items-center justify-center border border-slate-300">2</span>
                        @elseif($i===2) <span class="w-6 h-6 rounded-full bg-amber-700/10 text-amber-800 text-xs font-black inline-flex items-center justify-center border border-amber-300">3</span>
                        @else <span class="font-black text-slate-400">#{{ $i+1 }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 font-bold text-slate-800">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
                            {{ $rider['name'] }}
                        </span>
                    </td>
                    <td class="px-6 py-3 font-black text-[#1A6FA8]">{{ $rider['deliveries'] }}</td>
                    <td class="px-6 py-3">
                        <span class="font-black {{ $rider['success_rate'] >= 95 ? 'text-emerald-600' : ($rider['success_rate'] >= 90 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $rider['success_rate'] }}%
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="w-full bg-slate-100 rounded-full h-2 max-w-[120px]">
                            <div class="h-2 rounded-full {{ $rider['success_rate'] >= 95 ? 'bg-emerald-500' : ($rider['success_rate'] >= 90 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                 style="width:{{ $rider['success_rate'] }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
const rCtx = document.getElementById('reportChart').getContext('2d');
new Chart(rCtx, {
    type: 'line',
    data: {
        labels: @json($reportData['chart']['labels']),
        datasets: [
            {
                label: 'Received',
                data: @json($reportData['chart']['received']),
                borderColor: '#1A6FA8',
                backgroundColor: 'rgba(26,111,168,0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1A6FA8',
                pointRadius: 5,
            },
            {
                label: 'Delivered',
                data: @json($reportData['chart']['delivered']),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 5,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
