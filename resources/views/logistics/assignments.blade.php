@extends('layouts.logistics')

@section('title', 'Delivery Assignment | Logistics Hub | cartzy')
@section('page_title', 'Delivery Assignment')
@section('page_subtitle', 'Assign sorted parcels to riders per area')

@section('content')

{{-- Area Filter --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('logistics.assignments', ['area'=>'all']) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter==='all' ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        All Areas
    </a>
    @foreach($areas as $area)
    <a href="{{ route('logistics.assignments', ['area'=>$area]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$area ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $area }}
    </a>
    @endforeach
</div>

{{-- Parcels --}}
<div class="space-y-4">
    @forelse($parcels as $parcel)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center gap-4 p-5">
            {{-- Icon + Code --}}
            <div class="shrink-0">
                <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>

            {{-- Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-xs font-black text-[#1A6FA8]">{{ $parcel['tracking_code'] }}</span>
                    <span class="text-xs font-black bg-violet-100 text-violet-700 border border-violet-200 px-2 py-0.5 rounded-full">{{ $parcel['delivery_area'] }}</span>
                    @if($parcel['sort_status']==='assigned')
                        <span class="text-xs font-black bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">Assigned</span>
                    @endif
                </div>
                <div class="font-bold text-slate-800">{{ $parcel['recipient_name'] }}</div>
                <div class="text-xs text-slate-500 mt-0.5 truncate">{{ $parcel['recipient_address'] }}</div>
                <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>{{ $parcel['items'] }}</span></span>
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg><span>{{ $parcel['weight_kg'] }}kg</span></span>
                    @if($parcel['payment_method']==='COD')
                        <span class="text-rose-600 font-bold">COD ₱{{ number_format($parcel['cod_amount'],2) }}</span>
                    @else
                        <span class="text-emerald-600 font-bold">Prepaid</span>
                    @endif
                </div>
                @if($parcel['assigned_rider_name'])
                    <div class="mt-1.5 text-xs text-emerald-600 font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
                        <span>Assigned to: {{ $parcel['assigned_rider_name'] }}</span>
                        @if($parcel['assigned_at'])<span class="text-slate-400 font-normal">&mdash; {{ $parcel['assigned_at'] }}</span>@endif
                    </div>
                @endif
            </div>

            {{-- Assign Form --}}
            <div class="shrink-0">
                @if($parcel['sort_status'] !== 'assigned')
                <form method="POST" action="{{ route('logistics.assignments.assign', $parcel['id']) }}" class="flex flex-col gap-2 min-w-[200px]">
                    @csrf
                    <select name="rider_id" required class="border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">Assign Rider</option>
                        @foreach($riders as $rider)
                        <option value="{{ $rider['id'] }}" data-name="{{ $rider['name'] }}">{{ $rider['name'] }} ({{ $rider['area'] }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black text-sm px-4 py-2.5 rounded-xl transition inline-flex items-center justify-center gap-2" onclick="setRiderName(this)">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Assign to Rider</span>
                    </button>
                    <input type="hidden" name="rider_name" id="riderName_{{ $parcel['id'] }}" value="">
                </form>
                @else
                <div class="text-center">
                    <div class="text-emerald-600 font-black text-sm flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Assigned
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-slate-400">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <p class="font-bold text-lg">No parcels to assign in this area.</p>
        <a href="{{ route('logistics.sorting') }}" class="mt-3 inline-block text-sm text-[#1A6FA8] hover:underline font-bold">→ Sort parcels first</a>
    </div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
function setRiderName(btn) {
    const form = btn.closest('form');
    const sel = form.querySelector('select[name="rider_id"]');
    const opt = sel.options[sel.selectedIndex];
    const hiddenName = form.querySelector('input[name="rider_name"]');
    if (hiddenName && opt) hiddenName.value = opt.dataset.name || opt.text;
}
</script>
@endpush
