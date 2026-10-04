@extends('layouts.logistics')

@section('title', 'Incoming Parcels | Logistics Hub | cartzy')
@section('page_title', 'Incoming Parcels')
@section('page_subtitle', 'Manage parcels arriving at the sorting center')

@section('content')

{{-- Filter --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['all'=>'All','received'=>'Received','at_hub'=>'At Hub'] as $key=>$label)
    <a href="{{ route('logistics.parcels', ['status'=>$key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$key ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Tracking Code</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Seller</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Recipient</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Items</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">COD</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($parcels as $parcel)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-4 py-3">
                        <span class="font-black text-[#1A6FA8] text-xs">{{ $parcel['tracking_code'] }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-800">{{ $parcel['seller_name'] }}</div>
                        <div class="text-xs text-slate-400">{{ $parcel['weight_kg'] }} kg</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-800">{{ $parcel['recipient_name'] }}</div>
                        <div class="text-xs text-slate-400 max-w-[180px] truncate">{{ $parcel['recipient_address'] }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-slate-700 max-w-[180px] truncate">{{ $parcel['items'] }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($parcel['payment_method']==='COD')
                            <span class="font-black text-rose-600">₱{{ number_format($parcel['cod_amount'],2) }}</span><br>
                            <span class="text-xs text-rose-400 font-semibold">COD</span>
                        @else
                            <span class="text-xs text-emerald-600 font-bold">Prepaid</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php $st = $parcel['status']; @endphp
                        <span class="text-xs font-black px-2 py-0.5 rounded-full
                            {{ $st==='received' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst($st) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($parcel['status'] !== 'received')
                        <form method="POST" action="{{ route('logistics.parcels.receive', $parcel['id']) }}">
                            @csrf
                            <button type="submit" class="text-xs bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-bold px-3 py-1.5 rounded-lg transition">
                                Mark Received
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-emerald-600 font-semibold">✓ In Hub</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <p class="font-bold">No parcels found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
