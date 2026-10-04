@extends('layouts.logistics')

@section('title', 'Sorting | Logistics Hub | cartzy')
@section('page_title', 'Sorting of Parcels')
@section('page_subtitle', 'Sort incoming parcels by delivery area')

@section('content')

{{-- Filter --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['all'=>'All','unsorted'=>'Unsorted','sorted'=>'Sorted','assigned'=>'Assigned'] as $key=>$label)
    <a href="{{ route('logistics.sorting', ['status'=>$key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$key ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Parcel Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($parcels as $parcel)
    <div class="bg-white rounded-2xl border {{ $parcel['sort_status']==='unsorted' ? 'border-amber-200' : 'border-slate-200' }} shadow-sm hover:shadow-md transition overflow-hidden">
        {{-- Header --}}
        <div class="px-4 pt-4 pb-3 border-b border-slate-100 flex items-center justify-between gap-2">
            <div>
                <div class="text-xs font-black text-[#1A6FA8]">{{ $parcel['tracking_code'] }}</div>
                <div class="font-bold text-slate-800 text-sm mt-0.5 truncate">{{ $parcel['recipient_name'] }}</div>
            </div>
            @php
                $ss = $parcel['sort_status'];
                $ssColors = ['unsorted'=>'bg-amber-100 text-amber-700','sorted'=>'bg-blue-100 text-blue-700','assigned'=>'bg-emerald-100 text-emerald-700'];
            @endphp
            <span class="text-xs font-black px-2 py-0.5 rounded-full {{ $ssColors[$ss] ?? 'bg-slate-100 text-slate-700' }} shrink-0">
                {{ ucfirst($ss) }}
            </span>
        </div>

        <div class="p-4 space-y-1.5 text-xs text-slate-600">
            <div class="flex gap-2 items-start"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> {{ $parcel['seller_name'] }}</div>
            <div class="flex gap-2 items-start"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> <span class="line-clamp-2">{{ $parcel['recipient_address'] }}</span></div>
            <div class="flex gap-2 items-center"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> {{ $parcel['items'] }}</div>
            <div class="flex gap-2 items-center"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg> {{ $parcel['weight_kg'] }} kg
                @if($parcel['payment_method']==='COD')
                &mdash; <span class="text-rose-600 font-bold">COD ₱{{ number_format($parcel['cod_amount'],2) }}</span>
                @else
                &mdash; <span class="text-emerald-600 font-bold">Prepaid</span>
                @endif
            </div>
            @if($parcel['delivery_area'])
            <div class="flex gap-2 items-center text-violet-700 font-bold"><svg class="w-3.5 h-3.5 text-violet-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg> {{ $parcel['delivery_area'] }}</div>
            @endif
        </div>

        {{-- Sort Action --}}
        @if($parcel['sort_status'] === 'unsorted')
        <div class="px-4 pb-4">
            <form method="POST" action="{{ route('logistics.sorting.sort', $parcel['id']) }}" class="flex gap-2">
                @csrf
                <select name="area" required class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="">Select Area</option>
                    <option>Laguna - Santa Cruz</option>
                    <option>Laguna - San Pablo</option>
                    <option>Laguna - Calamba</option>
                    <option>Laguna - Los Baños</option>
                    <option>Laguna - Biñan / Santa Rosa</option>
                    <option>Batangas - Lipa</option>
                    <option>Batangas - Mataasnakahoy</option>
                    <option>Quezon - Lucena</option>
                    <option>Quezon - Tayabas</option>
                    <option>Metro Manila - South</option>
                </select>
                <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-black text-xs px-3 py-2 rounded-xl transition">
                    Sort
                </button>
            </form>
        </div>
        @elseif($parcel['sort_status'] === 'sorted')
        <div class="px-4 pb-4">
            <div class="flex items-center gap-1.5 text-blue-700 text-xs font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Sorted → {{ $parcel['delivery_area'] }}
            </div>
            @if($parcel['sorted_at'])<div class="text-[10px] text-slate-400 mt-0.5">{{ $parcel['sorted_at'] }}</div>@endif
        </div>
        @else
        <div class="px-4 pb-4">
            <div class="flex items-center gap-1.5 text-emerald-700 text-xs font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Assigned to {{ $parcel['assigned_rider_name'] ?? 'Rider' }}
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        </div>
        <p class="font-bold">No parcels to sort.</p>
    </div>
    @endforelse
</div>

@endsection
