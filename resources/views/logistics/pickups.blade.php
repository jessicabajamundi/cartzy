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
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#1A6FA8] border border-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>

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
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> {{ $pickup['parcel_count'] }} parcels ({{ $pickup['weight_kg'] }} kg)</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> {{ $pickup['pickup_date'] }} at {{ $pickup['pickup_time'] }}</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ $pickup['address'] }}</span>
                </div>
                @if($pickup['notes'])
                <div class="mt-2 text-xs bg-amber-50 border border-amber-100 text-amber-800 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>{{ $pickup['notes'] }}</span>
                </div>
                @endif
                @if($pickup['confirmed_at'])
                <div class="mt-1.5 text-xs text-emerald-600 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Confirmed: {{ $pickup['confirmed_at'] }}</span>
                </div>
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
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        </div>
        <p class="font-bold text-lg">No pickup requests found.</p>
    </div>
    @endforelse
</div>

@endsection
