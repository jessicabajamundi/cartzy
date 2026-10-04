@extends('layouts.logistics')

@section('title', 'Dashboard | Logistics Hub | cartzy')
@section('page_title', 'Logistics Hub Dashboard')
@section('page_subtitle', 'Real-time overview of your sorting center operations')

@section('content')

{{-- Welcome Banner --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0A2E4A] via-[#0F4C75] to-[#1A6FA8] text-white p-6 mb-6 shadow-xl">
    <div class="relative z-10">
        <p class="text-blue-200 text-sm font-semibold uppercase tracking-wider mb-1">Welcome back</p>
        <h2 class="text-2xl font-black mb-1">{{ $hub->business_name ?? $hub->name }}</h2>
        <p class="text-blue-200 text-sm">{{ now()->format('l, F d, Y') }} &mdash; Hub operations are running smoothly</p>
    </div>
    <div class="absolute right-4 top-4 opacity-10 text-9xl select-none">🏭</div>
    <div class="absolute -bottom-6 -right-6 w-40 h-40 rounded-full bg-white/5"></div>
</div>

{{-- KPI Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @php
    $kpis = [
        ['label'=>'Active Riders',       'value'=>$stats['active_riders'],       'total'=>$stats['total_riders'], 'icon'=>'🛵', 'color'=>'bg-emerald-50 border-emerald-200 text-emerald-700'],
        ['label'=>'Pickups Today',        'value'=>$stats['pickups_today'],       'total'=>null,                    'icon'=>'📥', 'color'=>'bg-blue-50 border-blue-200 text-blue-700'],
        ['label'=>'Parcels in Hub',       'value'=>$stats['parcels_in_hub'],      'total'=>null,                    'icon'=>'📦', 'color'=>'bg-amber-50 border-amber-200 text-amber-700'],
        ['label'=>'Delivered Today',      'value'=>$stats['delivered_today'],     'total'=>null,                    'icon'=>'✅', 'color'=>'bg-teal-50 border-teal-200 text-teal-700'],
        ['label'=>'Hub Efficiency',       'value'=>$stats['hub_efficiency'].'%',  'total'=>null,                    'icon'=>'⚡', 'color'=>'bg-violet-50 border-violet-200 text-violet-700'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="bg-white rounded-2xl border {{ $kpi['color'] }} p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">{{ $kpi['icon'] }}</span>
            @if($kpi['total'])
                <span class="text-xs font-bold text-slate-400">/ {{ $kpi['total'] }}</span>
            @endif
        </div>
        <div class="text-2xl font-black text-slate-800">{{ $kpi['value'] }}</div>
        <div class="text-xs font-semibold text-slate-500 mt-1">{{ $kpi['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Second row stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-3xl mb-1">🗂️</div>
        <div class="text-2xl font-black text-slate-800">{{ $stats['sorted_today'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Sorted Today</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-3xl mb-1">🚀</div>
        <div class="text-2xl font-black text-slate-800">{{ $stats['out_for_delivery'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Out for Delivery</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-3xl mb-1">⏳</div>
        <div class="text-2xl font-black text-amber-600">{{ $stats['pending_applications'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Rider Applications</div>
    </div>
    <div class="bg-white rounded-2xl border border-red-100 p-4 shadow-sm text-center">
        <div class="text-3xl mb-1">⚠️</div>
        <div class="text-2xl font-black text-rose-600">{{ $stats['failed_deliveries'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Failed Deliveries</div>
    </div>
</div>

{{-- Main Grid: Chart + Notifications --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-black text-slate-800">Weekly Parcel Volume</h3>
                <p class="text-xs text-slate-500">Received vs Delivered — last 7 days</p>
            </div>
            <span class="text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full">Live</span>
        </div>
        <canvas id="volumeChart" height="200"></canvas>
    </div>

    {{-- Notifications --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-black text-slate-800 mb-4">Notifications</h3>
        <div class="space-y-3">
            @foreach($notifications as $n)
            <a href="{{ $n['link'] }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 transition group border {{ $n['unread'] ? 'border-blue-100 bg-blue-50/40' : 'border-transparent' }}">
                <span class="text-xl shrink-0">
                    @if($n['type']==='pickup') 📥
                    @elseif($n['type']==='rider') 🛵
                    @elseif($n['type']==='delivery') ⚠️
                    @else 🗂️
                    @endif
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-slate-800 group-hover:text-[#1A6FA8] transition truncate">{{ $n['title'] }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $n['message'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 font-semibold">{{ $n['time'] }}</div>
                </div>
                @if($n['unread'])
                    <span class="w-2 h-2 bg-blue-500 rounded-full shrink-0 mt-1.5"></span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Quick Actions + Recent Pickups --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- Quick Actions --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-black text-slate-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('logistics.pickups') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl transition group">
                <span class="text-3xl">📥</span>
                <span class="text-xs font-bold text-blue-700 text-center">Confirm Pickups</span>
            </a>
            <a href="{{ route('logistics.sorting') }}" class="flex flex-col items-center gap-2 p-4 bg-violet-50 hover:bg-violet-100 border border-violet-200 rounded-xl transition">
                <span class="text-3xl">🗂️</span>
                <span class="text-xs font-bold text-violet-700 text-center">Sort Parcels</span>
            </a>
            <a href="{{ route('logistics.assignments') }}" class="flex flex-col items-center gap-2 p-4 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition">
                <span class="text-3xl">📋</span>
                <span class="text-xs font-bold text-emerald-700 text-center">Assign Riders</span>
            </a>
            <a href="{{ route('logistics.monitoring') }}" class="flex flex-col items-center gap-2 p-4 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition">
                <span class="text-3xl">📡</span>
                <span class="text-xs font-bold text-amber-700 text-center">Monitor Deliveries</span>
            </a>
        </div>
    </div>

    {{-- Recent Pickups --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black text-slate-800">Recent Pickup Requests</h3>
            <a href="{{ route('logistics.pickups') }}" class="text-xs text-[#1A6FA8] font-bold hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @foreach($recentPickups as $pickup)
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg shrink-0">📥</div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-slate-800 truncate">{{ $pickup['seller_name'] }}</div>
                    <div class="text-xs text-slate-500">{{ $pickup['parcel_count'] }} parcels · {{ $pickup['pickup_date'] }} {{ $pickup['pickup_time'] }}</div>
                </div>
                <span class="text-xs font-black px-2 py-0.5 rounded-full shrink-0 {{ $pickup['status'] === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ ucfirst($pickup['status']) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('volumeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: 'Received',
                data: @json($chartData['received']),
                backgroundColor: 'rgba(26,111,168,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Delivered',
                data: @json($chartData['delivered']),
                backgroundColor: 'rgba(16,185,129,0.7)',
                borderRadius: 6,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
    }
});
</script>
@endpush
