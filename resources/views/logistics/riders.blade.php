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
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#1A6FA8] to-[#0A2E4A] text-white flex items-center justify-center text-2xl shrink-0">🛵</div>
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
                <span class="text-base">📞</span> {{ $rider['phone'] }}
            </div>
            <div class="flex items-center gap-2 text-slate-600">
                <span class="text-base">🏍️</span> {{ $rider['vehicle_type'] }} &mdash; {{ $rider['plate_no'] }}
            </div>
            @if($rider['area'])
            <div class="flex items-center gap-2 text-slate-600">
                <span class="text-base">📍</span> {{ $rider['area'] }}
            </div>
            @endif
            @if($rider['rating'])
            <div class="flex items-center gap-2 text-slate-600">
                <span class="text-base">⭐</span> {{ $rider['rating'] }} rating &mdash; {{ $rider['deliveries_today'] }} deliveries today
            </div>
            @endif
            @if($rider['rejection_reason'])
            <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-lg p-2">
                ❌ {{ $rider['rejection_reason'] }}
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
                        class="w-full text-xs font-black py-2 px-3 rounded-xl transition
                        {{ $rider['is_active'] ? 'bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ $rider['is_active'] ? '⏸ Deactivate' : '▶ Activate' }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black py-2 px-3 rounded-xl transition">
                        ↩ Re-review
                    </button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <div class="text-5xl mb-3">🛵</div>
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
