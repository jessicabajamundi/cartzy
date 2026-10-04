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
        <a href="#" onclick="window.print()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm px-5 py-2.5 rounded-xl transition border border-slate-200">
            🖨️ Print
        </a>
    </form>
</div>

{{-- KPI Summary --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @php
    $kpis = [
        ['label'=>'Total Parcels Handled','value'=>number_format($reportData['total_parcels_handled']),'icon'=>'📦','color'=>'border-blue-200 text-blue-700'],
        ['label'=>'Delivered On Time',    'value'=>number_format($reportData['delivered_on_time']),    'icon'=>'✅','color'=>'border-emerald-200 text-emerald-700'],
        ['label'=>'Failed Deliveries',    'value'=>number_format($reportData['failed_deliveries']),    'icon'=>'⚠️','color'=>'border-rose-200 text-rose-700'],
        ['label'=>'Success Rate',         'value'=>$reportData['success_rate'].'%',                    'icon'=>'⚡','color'=>'border-violet-200 text-violet-700'],
        ['label'=>'Avg. Delivery Days',   'value'=>$reportData['avg_delivery_days'].' days',           'icon'=>'📅','color'=>'border-amber-200 text-amber-700'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-white rounded-2xl border {{ $kpi['color'] }} p-4 shadow-sm text-center">
        <div class="text-2xl mb-2">{{ $kpi['icon'] }}</div>
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
                        @if($i===0) <span class="text-xl">🥇</span>
                        @elseif($i===1) <span class="text-xl">🥈</span>
                        @elseif($i===2) <span class="text-xl">🥉</span>
                        @else <span class="font-black text-slate-400">#{{ $i+1 }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 font-bold text-slate-800">🛵 {{ $rider['name'] }}</td>
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
