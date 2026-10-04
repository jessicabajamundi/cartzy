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
    <div class="absolute right-6 top-4 opacity-[0.07] select-none">
        <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </div>
    <div class="absolute -bottom-6 -right-6 w-40 h-40 rounded-full bg-white/5"></div>
</div>

{{-- KPI Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @php
    $kpis = [
        ['label'=>'Active Riders',       'value'=>$stats['active_riders'],       'total'=>$stats['total_riders'], 'icon'=>'rider', 'color'=>'bg-emerald-50 border-emerald-200 text-emerald-700', 'iconColor'=>'text-emerald-600'],
        ['label'=>'Pickups Today',        'value'=>$stats['pickups_today'],       'total'=>null,                    'icon'=>'pickup', 'color'=>'bg-blue-50 border-blue-200 text-blue-700', 'iconColor'=>'text-blue-600'],
        ['label'=>'Parcels in Hub',       'value'=>$stats['parcels_in_hub'],      'total'=>null,                    'icon'=>'parcel', 'color'=>'bg-amber-50 border-amber-200 text-amber-700', 'iconColor'=>'text-amber-600'],
        ['label'=>'Delivered Today',      'value'=>$stats['delivered_today'],     'total'=>null,                    'icon'=>'delivered', 'color'=>'bg-teal-50 border-teal-200 text-teal-700', 'iconColor'=>'text-teal-600'],
        ['label'=>'Hub Efficiency',       'value'=>$stats['hub_efficiency'].'%',  'total'=>null,                    'icon'=>'efficiency', 'color'=>'bg-violet-50 border-violet-200 text-violet-700', 'iconColor'=>'text-violet-600'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="bg-white rounded-2xl border {{ $kpi['color'] }} p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $kpi['iconColor'] }} bg-white shadow-xs border border-current/10">
                @if($kpi['icon'] === 'rider')
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5l-3-3h4l2 3h4"/><circle cx="9" cy="5" r="2"/></svg>
                @elseif($kpi['icon'] === 'pickup')
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                @elseif($kpi['icon'] === 'parcel')
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                @elseif($kpi['icon'] === 'delivered')
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                @elseif($kpi['icon'] === 'efficiency')
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                @endif
            </div>
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
        <div class="flex justify-center mb-2">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-800">{{ $stats['sorted_today'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Sorted Today</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="flex justify-center mb-2">
            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-800">{{ $stats['out_for_delivery'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Out for Delivery</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="flex justify-center mb-2">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-black text-amber-600">{{ $stats['pending_applications'] }}</div>
        <div class="text-xs text-slate-500 font-semibold mt-1">Rider Applications</div>
    </div>
    <div class="bg-white rounded-2xl border border-red-100 p-4 shadow-sm text-center">
        <div class="flex justify-center mb-2">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
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
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                    @if($n['type']==='pickup')
                        <span class="w-full h-full rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg></span>
                    @elseif($n['type']==='rider')
                        <span class="w-full h-full rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5l-3-3h4l2 3h4"/><circle cx="9" cy="5" r="2"/></svg></span>
                    @elseif($n['type']==='delivery')
                        <span class="w-full h-full rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                    @else
                        <span class="w-full h-full rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></span>
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
                <span class="w-10 h-10 rounded-xl bg-blue-200/60 text-blue-700 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg></span>
                <span class="text-xs font-bold text-blue-700 text-center">Confirm Pickups</span>
            </a>
            <a href="{{ route('logistics.sorting') }}" class="flex flex-col items-center gap-2 p-4 bg-violet-50 hover:bg-violet-100 border border-violet-200 rounded-xl transition">
                <span class="w-10 h-10 rounded-xl bg-violet-200/60 text-violet-700 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></span>
                <span class="text-xs font-bold text-violet-700 text-center">Sort Parcels</span>
            </a>
            <a href="{{ route('logistics.assignments') }}" class="flex flex-col items-center gap-2 p-4 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition">
                <span class="w-10 h-10 rounded-xl bg-emerald-200/60 text-emerald-700 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></span>
                <span class="text-xs font-bold text-emerald-700 text-center">Assign Riders</span>
            </a>
            <a href="{{ route('logistics.monitoring') }}" class="flex flex-col items-center gap-2 p-4 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition">
                <span class="w-10 h-10 rounded-xl bg-amber-200/60 text-amber-700 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg></span>
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
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg></div>
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
