@extends('layouts.courier')

@section('title', 'Rider Command Center | cartzy')
@section('header_title', 'Rider Command Center')

@section('content')
<div class="space-y-6">

    <!-- Hero Status Banner -->
    <div class="rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-white/10" style="background: var(--gradient-master);">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-heading text-3xl font-bold shrink-0 shadow-inner">
                    {{ strtoupper(substr($courier->name ?? 'Rider', 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-white/20 text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-widest backdrop-blur-xs">EXPRESS RIDER</span>
                        <span class="text-xs text-white/80 font-semibold">• {{ $rider->area ?? 'Metro South Sector' }}</span>
                    </div>
                    <h1 class="font-heading text-2xl sm:text-3xl font-bold text-white mt-1">{{ $courier->name }}</h1>
                    <div class="flex items-center gap-3 text-xs text-white/90 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 font-semibold">
                            <svg class="w-3.5 h-3.5 text-[#F5CFC7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            <span>{{ $rider->vehicle_type ?? 'Motorcycle' }} (<strong>{{ $rider->plate_no ?? 'NQ-4812' }}</strong>)</span>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 text-amber-300 font-bold">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>{{ number_format($stats['rider_rating'], 2) }} Rating</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openScanModal()" class="bg-white hover:bg-gray-50 text-[#564B68] font-bold text-xs px-5 py-3 rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer transform active:scale-95">
                    <svg class="w-4 h-4 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Scan Parcel</span>
                </button>
            </div>
        </div>

        <!-- Subtle geometric background overlay -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="absolute right-40 -top-20 w-48 h-48 rounded-full bg-[#C08B7F]/20 pointer-events-none blur-3xl"></div>
    </div>

    <!-- 4 Key Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Pickups Pending -->
        <a href="{{ route('courier.pickups') }}" class="rider-card p-5 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-gray-400 group-hover:text-[#564B68] transition">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Items For Pickup</span>
                    <div class="w-8 h-8 rounded-xl bg-[#FAF8F6] text-[#8C5A50] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                </div>
                <div class="font-heading text-3xl font-bold text-[#564B68] mt-2">{{ $stats['assigned_pickups'] }}</div>
            </div>
            <p class="text-[11px] text-[#C08B7F] font-semibold mt-2 flex items-center gap-1">
                <span>View seller requests</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </p>
        </a>

        <!-- 2. Out for Delivery -->
        <a href="{{ route('courier.deliveries') }}" class="rider-card p-5 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-gray-400 group-hover:text-[#564B68] transition">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Active Delivery Queue</span>
                    <div class="w-8 h-8 rounded-xl bg-[#F8F6FA] text-[#6F6382] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                </div>
                <div class="font-heading text-3xl font-bold text-[#6F6382] mt-2">{{ $stats['out_for_delivery'] }}</div>
            </div>
            <p class="text-[11px] text-[#564B68] font-semibold mt-2 flex items-center gap-1">
                <span>View route parcels</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </p>
        </a>

        <!-- 3. Delivered Today -->
        <div class="rider-card p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-gray-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Delivered Today</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <div class="font-heading text-3xl font-bold text-emerald-700 mt-2">{{ $stats['delivered_today'] }}</div>
            </div>
            <p class="text-[11px] text-emerald-600 font-semibold mt-2">Verified with recipient</p>
        </div>

        <!-- 4. Today's Earnings -->
        <a href="{{ route('courier.earnings') }}" class="rider-card p-5 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-gray-400 group-hover:text-[#564B68] transition">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Today's Profit</span>
                    <div class="w-8 h-8 rounded-xl bg-[#FAF8F6] text-[#8C5A50] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="font-heading text-3xl font-bold text-[#8C5A50] mt-2">₱{{ number_format($stats['earnings_today'], 2) }}</div>
            </div>
            <p class="text-[11px] text-gray-500 font-semibold mt-2">
                COD Remittance: <strong class="text-gray-900">₱{{ number_format($stats['cod_collected_today'], 2) }}</strong>
            </p>
        </a>
    </div>

    <!-- Active Delivery Task Queue -->
    <div class="rider-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#ECE7EF] flex flex-wrap items-center justify-between gap-3 bg-[#FAF8F6]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-white text-[#564B68] flex items-center justify-center shadow-2xs border border-[#ECE7EF]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-[#564B68]">Today's Delivery Route</h3>
                    <p class="text-[11px] text-gray-500">Parcels scheduled for direct doorstep delivery</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('courier.deliveries') }}" class="text-xs font-bold text-[#6F6382] hover:text-[#564B68] px-3 py-1.5 rounded-lg hover:bg-white transition">
                    View All ({{ $activeShipments->count() }}) &rarr;
                </a>
            </div>
        </div>

        <div class="divide-y divide-[#ECE7EF]">
            @forelse($activeShipments as $index => $shipment)
                @php
                    $order = $shipment->sellerOrder->order;
                    $address = $order->shipping_address ?? [];
                    $codAmount = ($shipment->cod_amount_minor ?: ($shipment->sellerOrder->subtotal_minor + $shipment->sellerOrder->shipping_fee_minor)) / 100;
                    $recipientName = $address['recipient'] ?? $order->buyer?->name ?? 'Recipient';
                    $recipientPhone = $address['phone'] ?? $order->buyer?->phone ?? 'N/A';
                    $recipientAddress = $address['address'] ?? implode(', ', array_filter(\Illuminate\Support\Arr::only($address, ['line1', 'barangay', 'city', 'province', 'postal_code'])));
                @endphp
                <div class="p-4 sm:p-5 hover:bg-[#FAF9FB] flex flex-wrap items-center justify-between gap-4 transition">
                    <div class="flex items-start sm:items-center gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-[#F8F6FA] text-[#564B68] font-bold text-xs flex items-center justify-center border border-[#ECE7EF] shrink-0 mt-0.5 sm:mt-0">
                            #{{ $index + 1 }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-bold text-xs text-gray-900 tracking-wide">{{ $shipment->tracking_code }}</span>
                                @if($order->payment_method === 'cod')
                                    <span class="bg-amber-100 text-amber-900 text-[10px] font-black px-2 py-0.5 rounded border border-amber-200">COD: ₱{{ number_format($codAmount, 2) }}</span>
                                @else
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded border border-emerald-200">PREPAID</span>
                                @endif
                                <span class="text-[11px] text-gray-400">Order Ref: {{ $order->reference }}</span>
                            </div>

                            <p class="text-xs font-bold text-gray-900 mt-1">Recipient: {{ $recipientName }}</p>
                            <p class="text-[11px] text-gray-600 flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $recipientAddress }}</span>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Merchant: <strong>{{ $shipment->sellerOrder->seller?->name ?? 'Store Partner' }}</strong> · {{ $shipment->sellerOrder->items->sum('quantity') }} items</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="triggerParcelDelivery({{ $shipment->id }}, '{{ $shipment->tracking_code }}', '{{ addslashes($recipientName) }}', '{{ $order->payment_method === 'cod' ? '₱' . number_format($codAmount, 2) : 'PREPAID' }}')" class="btn-gradient-master text-xs px-4 py-2 cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Mark Delivered</span>
                        </button>

                        @if($recipientPhone && $recipientPhone !== 'N/A')
                            <a href="tel:{{ $recipientPhone }}" class="p-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl border border-gray-200 transition" title="Call Customer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">
                    <div class="w-12 h-12 bg-[#F8F6FA] text-[#6F6382] rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 class="font-heading text-lg font-bold text-gray-900">Your delivery route is clear!</h4>
                    <p class="text-xs text-gray-500 mt-1">Check the pickup dashboard to collect new packages from merchants.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Merchant Pickups in Sector -->
    <div class="rider-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#ECE7EF] flex flex-wrap items-center justify-between gap-3 bg-[#FAF8F6]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-white text-[#8C5A50] flex items-center justify-center shadow-2xs border border-[#ECE7EF]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-[#564B68]">Seller Pickup Requests</h3>
                    <p class="text-[11px] text-gray-500">Merchants waiting for courier collection</p>
                </div>
            </div>

            <a href="{{ route('courier.pickups') }}" class="text-xs font-bold text-[#8C5A50] hover:text-[#564B68] px-3 py-1.5 rounded-lg hover:bg-white transition">
                View All Pickups &rarr;
            </a>
        </div>

        <div class="divide-y divide-[#ECE7EF]">
            @forelse($availablePickups as $pickup)
                <div class="p-4 sm:p-5 hover:bg-[#FAF9FB] flex flex-wrap items-center justify-between gap-4 transition">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-gray-900">{{ $pickup->seller?->name ?? 'Store Merchant' }}</h4>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-200">
                                {{ $pickup->parcel_count }} PARCELS ({{ $pickup->weight_kg }} kg)
                            </span>
                            <span class="text-[10px] text-gray-500 font-semibold">{{ $pickup->pickup_time }}</span>
                        </div>
                        <p class="text-[11px] text-gray-600 flex items-center gap-1 mt-1">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $pickup->address }}</span>
                        </p>
                        @if($pickup->notes)
                            <p class="text-[10px] text-[#8C5A50] font-medium mt-0.5">Note: {{ $pickup->notes }}</p>
                        @endif
                    </div>

                    <form action="{{ route('courier.pickups.accept', $pickup->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white transition flex items-center gap-1.5 shadow-sm" style="background: var(--gradient-rose);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Accept Pickup</span>
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <p class="text-xs">No pending merchant pickups at this moment.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
