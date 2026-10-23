@extends('layouts.courier')

@section('title', 'Items for Pickup | cartzy')
@section('header_title', 'Items for Pickup')

@section('content')
<div class="space-y-6">

    <!-- Top Stats and Filters -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl font-bold text-[#564B68]">Merchant Pickup Requests</h1>
            <p class="text-xs text-gray-500">Collect parcels from seller stores and warehouse locations</p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-white rounded-2xl border border-[#ECE7EF] shadow-2xs text-xs font-bold">
            <a href="{{ route('courier.pickups', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl transition {{ $statusFilter === 'all' ? 'text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}" style="{{ $statusFilter === 'all' ? 'background: var(--gradient-master);' : '' }}">
                All Pickups
            </a>
            <a href="{{ route('courier.pickups', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl transition {{ $statusFilter === 'pending' ? 'text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}" style="{{ $statusFilter === 'pending' ? 'background: var(--gradient-master);' : '' }}">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('courier.pickups', ['status' => 'confirmed']) }}" class="px-3 py-1.5 rounded-xl transition {{ $statusFilter === 'confirmed' ? 'text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}" style="{{ $statusFilter === 'confirmed' ? 'background: var(--gradient-master);' : '' }}">
                Confirmed ({{ $confirmedCount }})
            </a>
        </div>
    </div>

    <!-- Pickup Requests Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($pickups as $pickup)
            <div class="rider-card p-5 flex flex-col justify-between space-y-4">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-heading text-lg font-bold shrink-0 text-white shadow-xs" style="background: var(--gradient-rose);">
                                {{ strtoupper(substr($pickup->seller?->name ?? 'Store', 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-gray-900">{{ $pickup->seller?->name ?? 'Store Partner' }}</h3>
                                <p class="text-[11px] text-[#6F6382] font-semibold">{{ $pickup->seller?->user?->phone ?? '0917-888-0000' }}</p>
                            </div>
                        </div>

                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full border {{ $pickup->status === 'confirmed' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                            {{ strtoupper($pickup->status) }}
                        </span>
                    </div>

                    <!-- Pickup Details -->
                    <div class="mt-4 pt-3 border-t border-[#ECE7EF] space-y-2 text-xs">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-[#8C5A50] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-gray-700">{{ $pickup->address }}</span>
                        </div>

                        <div class="flex items-center justify-between text-gray-500 pt-1">
                            <span>Package Count: <strong class="text-gray-900">{{ $pickup->parcel_count }} parcels</strong></span>
                            <span>Weight: <strong class="text-gray-900">{{ $pickup->weight_kg }} kg</strong></span>
                        </div>

                        <div class="flex items-center justify-between text-gray-500">
                            <span>Scheduled Window:</span>
                            <span class="font-bold text-gray-900">{{ $pickup->pickup_time }} ({{ $pickup->pickup_date?->format('M d') ?? 'Today' }})</span>
                        </div>

                        @if($pickup->notes)
                            <div class="p-2.5 rounded-xl bg-[#FAF8F6] border border-[#EFE8DE] text-[11px] text-[#8C5A50]">
                                <strong>Merchant Note:</strong> {{ $pickup->notes }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-3 border-t border-[#ECE7EF] flex items-center justify-between gap-3">
                    @if($pickup->seller?->user?->phone)
                        <a href="tel:{{ $pickup->seller->user->phone }}" class="text-xs font-bold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-xl border border-gray-200 transition inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Call Merchant</span>
                        </a>
                    @else
                        <span></span>
                    @endif

                    @if($pickup->status !== 'confirmed')
                        <form action="{{ route('courier.pickups.accept', $pickup->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-gradient-master text-xs px-4 py-2 cursor-pointer flex items-center gap-1.5 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Confirm &amp; Pickup</span>
                            </button>
                        </form>
                    @else
                        <span class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Collected &amp; In Transit</span>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rider-card p-12 text-center text-gray-500">
                <div class="w-12 h-12 bg-[#F8F6FA] text-[#6F6382] rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h4 class="font-heading text-xl font-bold text-gray-900">No Pickup Requests</h4>
                <p class="text-xs text-gray-400 mt-1">There are no merchant pickup requests matching this filter right now.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($pickups->hasPages())
        <div class="pt-4">
            {{ $pickups->links() }}
        </div>
    @endif

</div>
@endsection
