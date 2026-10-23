@extends('layouts.courier')

@section('title', 'Items for Delivery | cartzy')
@section('header_title', 'Items for Delivery')

@section('content')
<div class="space-y-6">

    <!-- Header & Search Controls -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl font-bold text-[#564B68]">Active Delivery Queue</h1>
            <p class="text-xs text-gray-500">Parcels out for doorstep delivery across your assigned sector</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-600 bg-white px-3 py-1.5 rounded-xl border border-[#ECE7EF]">
                Total in Route: <strong class="text-[#564B68]">{{ $totalActive }}</strong>
            </span>
            <span class="text-xs font-bold text-amber-900 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200">
                COD to Collect: <strong>₱{{ number_format($totalCodToCollect, 2) }}</strong>
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="rider-card p-4 flex flex-wrap items-center justify-between gap-3 bg-white">
        <!-- Search Input -->
        <form action="{{ route('courier.deliveries') }}" method="GET" class="flex-1 min-w-[240px] flex gap-2">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search tracking #, customer name, or order ref..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#564B68] focus:outline-hidden" />
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="btn-gradient-master text-xs px-4 py-2 cursor-pointer">Search</button>
            @if($search)
                <a href="{{ route('courier.deliveries') }}" class="px-3 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Clear</a>
            @endif
        </form>

        <!-- Payment Filter Tabs -->
        <div class="flex items-center gap-1.5 text-xs font-bold bg-[#FAF8F6] p-1 rounded-xl border border-[#ECE7EF]">
            <a href="{{ route('courier.deliveries', ['payment' => 'all', 'search' => $search]) }}" class="px-3 py-1 rounded-lg transition {{ $paymentFilter === 'all' ? 'bg-[#564B68] text-white' : 'text-gray-600 hover:text-gray-900' }}">All</a>
            <a href="{{ route('courier.deliveries', ['payment' => 'cod', 'search' => $search]) }}" class="px-3 py-1 rounded-lg transition {{ $paymentFilter === 'cod' ? 'bg-[#564B68] text-white' : 'text-gray-600 hover:text-gray-900' }}">COD</a>
            <a href="{{ route('courier.deliveries', ['payment' => 'prepaid', 'search' => $search]) }}" class="px-3 py-1 rounded-lg transition {{ $paymentFilter === 'prepaid' ? 'bg-[#564B68] text-white' : 'text-gray-600 hover:text-gray-900' }}">Prepaid</a>
        </div>
    </div>

    <!-- Deliveries List -->
    <div class="space-y-3">
        @forelse($deliveries as $index => $shipment)
            @php
                $order = $shipment->sellerOrder->order;
                $address = $order->shipping_address ?? [];
                $codAmount = ($shipment->cod_amount_minor ?: ($shipment->sellerOrder->subtotal_minor + $shipment->sellerOrder->shipping_fee_minor)) / 100;
                $recipientName = $address['recipient'] ?? $order->buyer?->name ?? 'Recipient';
                $recipientPhone = $address['phone'] ?? $order->buyer?->phone ?? 'N/A';
                $recipientAddress = $address['address'] ?? implode(', ', array_filter(\Illuminate\Support\Arr::only($address, ['line1', 'barangay', 'city', 'province', 'postal_code'])));
            @endphp
            <div class="rider-card p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5 flex-1 min-w-[280px]">
                    <div class="w-10 h-10 rounded-xl text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs" style="background: var(--gradient-master);">
                        #{{ $index + 1 }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-bold text-xs text-gray-900 tracking-wide">{{ $shipment->tracking_code }}</span>
                            @if($order->payment_method === 'cod')
                                <span class="bg-amber-100 text-amber-900 text-[10px] font-black px-2 py-0.5 rounded border border-amber-200">
                                    COLLECT COD: ₱{{ number_format($codAmount, 2) }}
                                </span>
                            @else
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded border border-emerald-200">
                                    ONLINE PAID (₱0.00)
                                </span>
                            @endif
                            <span class="text-[11px] text-gray-400">Order: {{ $order->reference }}</span>
                        </div>

                        <h4 class="text-sm font-bold text-gray-900 mt-1">{{ $recipientName }}</h4>
                        <p class="text-xs text-gray-600 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $recipientAddress }}</span>
                        </p>
                        <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-2 flex-wrap">
                            <span>Store: <strong>{{ $shipment->sellerOrder->seller?->name ?? 'Store Partner' }}</strong></span>
                            <span>•</span>
                            <span>Items: {{ $shipment->sellerOrder->items->map(fn($i) => $i->quantity . 'x ' . $i->product_name)->implode(', ') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($recipientPhone && $recipientPhone !== 'N/A')
                        <a href="tel:{{ $recipientPhone }}" class="p-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl border border-gray-200 transition" title="Call Customer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </a>
                    @endif

                    <button type="button" onclick="triggerParcelDelivery({{ $shipment->id }}, '{{ $shipment->tracking_code }}', '{{ addslashes($recipientName) }}', '{{ $order->payment_method === 'cod' ? '₱' . number_format($codAmount, 2) : 'PREPAID' }}')" class="btn-gradient-master text-xs px-4 py-2.5 cursor-pointer flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm Delivery</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="rider-card p-12 text-center text-gray-500">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h4 class="font-heading text-xl font-bold text-gray-900">No active parcels to deliver</h4>
                <p class="text-xs text-gray-400 mt-1">All assigned packages are complete. Check the pickup section for new collections.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($deliveries->hasPages())
        <div class="pt-4">
            {{ $deliveries->links() }}
        </div>
    @endif

</div>
@endsection
