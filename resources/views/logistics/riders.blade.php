@extends('layouts.logistics')

@section('title', 'Rider Management | Logistics Hub | cartzy')
@section('page_title', 'Rider Management')
@section('page_subtitle', 'Approve, manage and monitor courier/rider applications')

@section('content')

{{-- Filter Tabs --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['all'=>'All Riders','pending'=>'Pending','approved'=>'Approved','active'=>'Active','inactive'=>'Inactive','rejected'=>'Rejected'] as $key=>$label)
    <a href="{{ route('logistics.riders', ['filter'=>$key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-bold border transition
              {{ $filter===$key ? 'bg-[#1A6FA8] text-white border-[#1A6FA8] shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-[#1A6FA8]' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Rider Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($riders as $rider)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center gap-3 p-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#1A6FA8] to-[#0A2E4A] text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-black text-slate-800 truncate">{{ $rider['name'] }}</div>
                <div class="text-xs text-slate-500 truncate">{{ $rider['email'] }}</div>
            </div>
            <div>
                @if($rider['status']==='pending')
                    <span class="text-xs font-black bg-amber-100 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">Pending</span>
                @elseif($rider['status']==='approved' && $rider['is_active'])
                    <span class="text-xs font-black bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">Active</span>
                @elseif($rider['status']==='approved')
                    <span class="text-xs font-black bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded-full">Inactive</span>
                @else
                    <span class="text-xs font-black bg-rose-100 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-full">Rejected</span>
                @endif
            </div>
        </div>

        {{-- Details --}}
        <div class="p-4 space-y-2 text-sm">
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $rider['phone'] }}
            </div>
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg> {{ $rider['vehicle_type'] }} &mdash; {{ $rider['plate_no'] }}
            </div>
            @if($rider['area'])
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ $rider['area'] }}
            </div>
            @endif
            @if($rider['rating'])
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="w-4 h-4 text-amber-400 fill-amber-400 shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $rider['rating'] }} rating &mdash; {{ $rider['deliveries_today'] }} deliveries today
            </div>
            @endif
            @if($rider['rejection_reason'])
            <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-lg p-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ $rider['rejection_reason'] }}</span>
            </div>
            @endif
            <div class="text-xs text-slate-400">Applied: {{ $rider['applied_at'] }}</div>
        </div>

        {{-- Actions --}}
        <div class="px-4 pb-4 flex flex-wrap gap-2">
            @if($rider['status'] === 'pending')
                <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black py-2 px-3 rounded-xl transition">
                        ✓ Approve
                    </button>
                </form>
                <button onclick="showRejectModal({{ $rider['id'] }}, '{{ addslashes($rider['name']) }}')"
                        class="flex-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black py-2 px-3 rounded-xl transition">
                    ✕ Reject
                </button>
            @elseif($rider['status'] === 'approved')
                <form method="POST" action="{{ route('logistics.riders.toggle', $rider['id']) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full text-xs font-black py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5
                        {{ $rider['is_active'] ? 'bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        @if($rider['is_active'])
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Deactivate</span>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Activate</span>
                        @endif
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Re-review</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
        </div>
        <p class="font-bold">No riders found for this filter.</p>
    </div>
    @endforelse
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-xl font-black text-slate-800 mb-1">Reject Rider Application</h3>
        <p id="rejectModalName" class="text-sm text-slate-500 mb-4"></p>
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <textarea name="reason" rows="3" placeholder="State reason for rejection (e.g. Incomplete documents, expired NBI clearance...)"
                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showRejectModal(id, name) {
    document.getElementById('rejectForm').action = `/logistics/riders/${id}/status`;
    document.getElementById('rejectModalName').textContent = `Rider: ${name}`;
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endpush
