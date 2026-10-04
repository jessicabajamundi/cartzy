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
                <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center text-2xl">📦</div>
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
                    <span>📦 {{ $parcel['items'] }}</span>
                    <span>⚖️ {{ $parcel['weight_kg'] }}kg</span>
                    @if($parcel['payment_method']==='COD')
                        <span class="text-rose-600 font-bold">COD ₱{{ number_format($parcel['cod_amount'],2) }}</span>
                    @else
                        <span class="text-emerald-600 font-bold">Prepaid</span>
                    @endif
                </div>
                @if($parcel['assigned_rider_name'])
                    <div class="mt-1.5 text-xs text-emerald-600 font-bold flex items-center gap-1">
                        🛵 Assigned to: {{ $parcel['assigned_rider_name'] }}
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
                    <button type="submit" class="bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black text-sm px-4 py-2.5 rounded-xl transition" onclick="setRiderName(this)">
                        📋 Assign to Rider
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
        <div class="text-6xl mb-4">📋</div>
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
