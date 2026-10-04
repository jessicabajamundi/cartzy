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
                        <div class="text-xs text-slate-400">📞 {{ $parcel['recipient_phone'] }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold text-slate-700">{{ $parcel['delivery_area'] ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($parcel['assigned_rider_name'])
                            <span class="text-sm font-bold text-slate-700">🛵 {{ $parcel['assigned_rider_name'] }}</span>
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
                        <div class="text-4xl mb-3">📡</div>
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
