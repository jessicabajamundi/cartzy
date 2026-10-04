@extends('layouts.logistics')

@section('title', 'Pickup Requests | Logistics Hub | cartzy')
@section('page_title', 'Parcel Pickup Requests')
@section('page_subtitle', 'Confirm and verify seller parcel pickup schedules')

@section('content')

{{-- Filter Tabs --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed'] as $key=>$label)
    <a href="{{ route('logistics.pickups', ['status'=>$key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$key ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Pickup Cards --}}
<div class="space-y-4">
    @forelse($pickups as $pickup)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center gap-4 p-5">
            {{-- Icon --}}
            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-3xl shrink-0">📥</div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="font-black text-slate-800 text-base">{{ $pickup['seller_name'] }}</span>
                    <span class="text-xs font-black px-2.5 py-0.5 rounded-full
                        {{ $pickup['status']==='confirmed' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($pickup['status']) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                    <span class="flex items-center gap-1.5"><span>📦</span> {{ $pickup['parcel_count'] }} parcels ({{ $pickup['weight_kg'] }} kg)</span>
                    <span class="flex items-center gap-1.5"><span>📅</span> {{ $pickup['pickup_date'] }} at {{ $pickup['pickup_time'] }}</span>
                    <span class="flex items-center gap-1.5"><span>📍</span> {{ $pickup['address'] }}</span>
                </div>
                @if($pickup['notes'])
                <div class="mt-2 text-xs bg-amber-50 border border-amber-100 text-amber-800 px-3 py-1.5 rounded-lg inline-block">
                    💬 {{ $pickup['notes'] }}
                </div>
                @endif
                @if($pickup['confirmed_at'])
                <div class="mt-1.5 text-xs text-emerald-600 font-semibold">✓ Confirmed: {{ $pickup['confirmed_at'] }}</div>
                @endif
                <div class="mt-1 text-xs text-slate-400">Seller email: {{ $pickup['seller_email'] }}</div>
            </div>

            {{-- Action --}}
            @if($pickup['status'] === 'pending')
            <form method="POST" action="{{ route('logistics.pickups.confirm', $pickup['id']) }}" class="shrink-0">
                @csrf
                <button type="submit" class="bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black text-sm px-5 py-2.5 rounded-xl transition shadow-sm hover:shadow">
                    ✓ Confirm Pickup
                </button>
            </form>
            @else
            <div class="shrink-0 flex items-center gap-2 text-emerald-600 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Confirmed
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-slate-400">
        <div class="text-6xl mb-4">📥</div>
        <p class="font-bold text-lg">No pickup requests found.</p>
    </div>
    @endforelse
</div>

@endsection
