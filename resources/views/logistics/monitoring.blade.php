@extends('layouts.logistics')

@section('title', 'Delivery Monitoring | Logistics Hub | cartzy')
@section('page_title', 'Delivery Monitoring')
@section('page_subtitle', 'Track real-time status of all active deliveries')

@section('content')

{{-- Status Filter --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['all'=>'All','in_transit'=>'In Transit','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','failed'=>'Failed','at_hub'=>'At Hub'] as $key=>$label)
    <a href="{{ route('logistics.monitoring', ['status'=>$key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$key ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Monitoring Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Tracking</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Recipient</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Area</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Rider</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">COD</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Delivery Status</th>
                    <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-500">Last Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($parcels as $parcel)
                @php
                    $ds = $parcel['delivery_status'];
                    $dsMap = [
                        'in_transit'       => ['label'=>'In Transit',         'class'=>'bg-blue-100 text-blue-700'],
                        'out_for_delivery' => ['label'=>'Out for Delivery',    'class'=>'bg-violet-100 text-violet-700'],
                        'delivered'        => ['label'=>'Delivered',           'class'=>'bg-emerald-100 text-emerald-700'],
                        'failed'           => ['label'=>'Failed',              'class'=>'bg-rose-100 text-rose-700'],
                        'at_hub'           => ['label'=>'At Hub',              'class'=>'bg-amber-100 text-amber-700'],
                    ];
                    $dsInfo = $dsMap[$ds] ?? ['label'=>ucfirst($ds),'class'=>'bg-slate-100 text-slate-700'];
                @endphp
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-4 py-3 font-black text-[#1A6FA8] text-xs">{{ $parcel['tracking_code'] }}</td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-800">{{ $parcel['recipient_name'] }}</div>
                        <div class="text-xs text-slate-400 max-w-[160px] truncate">{{ $parcel['recipient_address'] }}</div>
                        <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5"><svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><span>{{ $parcel['recipient_phone'] }}</span></div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold text-slate-700">{{ $parcel['delivery_area'] ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($parcel['assigned_rider_name'])
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-700">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
                                <span>{{ $parcel['assigned_rider_name'] }}</span>
                            </span>
                        @else
                            <span class="text-xs text-slate-400">Not assigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($parcel['payment_method']==='COD')
                            <span class="font-black text-rose-600 text-xs">₱{{ number_format($parcel['cod_amount'],2) }}</span>
                        @else
                            <span class="text-xs text-emerald-600 font-bold">Prepaid</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-full {{ $dsInfo['class'] }}">
                            {{ $dsInfo['label'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400">
                        {{ $parcel['assigned_at'] ?? $parcel['received_at'] ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <p class="font-bold">No deliveries to monitor for this filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div class="mt-5 flex flex-wrap gap-3">
    @foreach(['In Transit'=>'bg-blue-100 text-blue-700','Out for Delivery'=>'bg-violet-100 text-violet-700','Delivered'=>'bg-emerald-100 text-emerald-700','Failed'=>'bg-rose-100 text-rose-700','At Hub'=>'bg-amber-100 text-amber-700'] as $lbl=>$cls)
    <span class="text-xs font-bold px-3 py-1 rounded-full {{ $cls }}">{{ $lbl }}</span>
    @endforeach
</div>

@endsection
