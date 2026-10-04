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
            <div class="flex gap-1.5 items-start"><span>🏬</span> {{ $parcel['seller_name'] }}</div>
            <div class="flex gap-1.5 items-start"><span>📍</span> <span class="line-clamp-2">{{ $parcel['recipient_address'] }}</span></div>
            <div class="flex gap-1.5"><span>📦</span> {{ $parcel['items'] }}</div>
            <div class="flex gap-1.5"><span>⚖️</span> {{ $parcel['weight_kg'] }} kg
                @if($parcel['payment_method']==='COD')
                &mdash; <span class="text-rose-600 font-bold">COD ₱{{ number_format($parcel['cod_amount'],2) }}</span>
                @else
                &mdash; <span class="text-emerald-600 font-bold">Prepaid</span>
                @endif
            </div>
            @if($parcel['delivery_area'])
            <div class="flex gap-1.5 text-violet-700 font-bold"><span>🗺️</span> {{ $parcel['delivery_area'] }}</div>
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
        <div class="text-5xl mb-3">🗂️</div>
        <p class="font-bold">No parcels to sort.</p>
    </div>
    @endforelse
</div>

@endsection
