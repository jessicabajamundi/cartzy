@extends('layouts.courier')

@section('title', 'Delivery History | Cartzy Courier')
@section('header_title', 'Delivery History')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Search / Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl font-bold text-[#564B68]">Completed Deliveries</h1>
            <p class="text-xs text-gray-500">Official log of your successfully completed drops and recipient signatures</p>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Search Form -->
            <form method="GET" action="{{ route('courier.history') }}" class="relative">
                <input type="hidden" name="date" value="{{ $dateFilter }}">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search tracking / recipient..." 
                    class="pl-9 pr-4 py-2 text-xs bg-white border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:ring-1 focus:ring-[#564B68] w-64 shadow-2xs"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>

            <!-- Date Range Selector -->
            <div class="flex items-center gap-1 p-1 bg-white rounded-xl border border-[#ECE7EF] shadow-2xs text-xs font-semibold">
                <a href="{{ route('courier.history', ['date' => 'all', 'search' => $search]) }}" 
                   class="px-2.5 py-1.5 rounded-lg transition {{ $dateFilter === 'all' ? 'text-white' : 'text-gray-600 hover:text-gray-900' }}"
                   style="{{ $dateFilter === 'all' ? 'background: var(--gradient-master);' : '' }}">
                    All
                </a>
                <a href="{{ route('courier.history', ['date' => 'today', 'search' => $search]) }}" 
                   class="px-2.5 py-1.5 rounded-lg transition {{ $dateFilter === 'today' ? 'text-white' : 'text-gray-600 hover:text-gray-900' }}"
                   style="{{ $dateFilter === 'today' ? 'background: var(--gradient-master);' : '' }}">
                    Today
                </a>
                <a href="{{ route('courier.history', ['date' => 'week', 'search' => $search]) }}" 
                   class="px-2.5 py-1.5 rounded-lg transition {{ $dateFilter === 'week' ? 'text-white' : 'text-gray-600 hover:text-gray-900' }}"
                   style="{{ $dateFilter === 'week' ? 'background: var(--gradient-master);' : '' }}">
                    7 Days
                </a>
                <a href="{{ route('courier.history', ['date' => 'month', 'search' => $search]) }}" 
                   class="px-2.5 py-1.5 rounded-lg transition {{ $dateFilter === 'month' ? 'text-white' : 'text-gray-600 hover:text-gray-900' }}"
                   style="{{ $dateFilter === 'month' ? 'background: var(--gradient-master);' : '' }}">
                    This Month
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Performance Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rider-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#564B68] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total Delivered</p>
                <p class="font-heading text-lg font-bold text-gray-900">{{ number_format($stats['total_delivered']) }} drops</p>
            </div>
        </div>

        <div class="rider-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total COD Handled</p>
                <p class="font-heading text-lg font-bold text-gray-900">₱{{ number_format($stats['total_cod_revenue'], 2) }}</p>
            </div>
        </div>

        <div class="rider-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-[#8C5A50] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Rider Drop Payout</p>
                <p class="font-heading text-lg font-bold text-gray-900">₱{{ number_format($stats['total_delivered'] * 65, 2) }}</p>
            </div>
        </div>

        <div class="rider-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Service Rating</p>
                <p class="font-heading text-lg font-bold text-gray-900">{{ number_format($rider->rating ?? 4.95, 2) }} ★</p>
            </div>
        </div>
    </div>

    <!-- History List Table / Cards -->
    <div class="rider-card overflow-hidden">
        <div class="p-4 border-b border-[#ECE7EF] flex items-center justify-between">
            <h3 class="font-bold text-sm text-[#564B68] flex items-center gap-2">
                <svg class="w-4 h-4 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Delivery Records ({{ $deliveries->total() }})
            </h3>
            <span class="text-[11px] text-gray-500 font-semibold">Ordered by completion time</span>
        </div>

        @if($deliveries->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#F8F6FA] text-gray-600 border-b border-[#ECE7EF]">
                            <th class="py-3 px-4 font-bold">Tracking / Waybill</th>
                            <th class="py-3 px-4 font-bold">Recipient & Destination</th>
                            <th class="py-3 px-4 font-bold">Store Partner</th>
                            <th class="py-3 px-4 font-bold">Date & Time Completed</th>
                            <th class="py-3 px-4 font-bold">Payment Method</th>
                            <th class="py-3 px-4 font-bold text-right">Rider Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ECE7EF]">
                        @foreach($deliveries as $shipment)
                            @php
                                $order = $shipment->sellerOrder?->order;
                                $address = $order?->shipping_address ?? [];
                                $recipient = $address['recipient'] ?? $order?->buyer?->name ?? 'Consignee';
                                $codAmount = $shipment->cod_amount_minor 
                                    ? ($shipment->cod_amount_minor / 100) 
                                    : (($shipment->sellerOrder->subtotal_minor + $shipment->sellerOrder->shipping_fee_minor) / 100);
                                $latestEvent = $shipment->deliveryEvents->where('status', 'delivered')->last();
                            @endphp
                            <tr class="hover:bg-[#FAF9FC] transition">
                                <!-- Tracking & Ref -->
                                <td class="py-3.5 px-4 font-mono font-semibold">
                                    <div class="flex items-center gap-1.5 text-[#564B68]">
                                        <svg class="w-3.5 h-3.5 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                        </svg>
                                        {{ $shipment->tracking_code }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-normal">
                                        Ref: {{ $order?->reference ?? 'ORD-000' }}
                                    </div>
                                </td>

                                <!-- Recipient & Destination -->
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-gray-900">{{ $recipient }}</p>
                                    <p class="text-[11px] text-gray-500 truncate max-w-xs">
                                        {{ $address['address_line1'] ?? $address['address'] ?? 'Customer Delivery Address' }}
                                    </p>
                                    @if($latestEvent && $latestEvent->note)
                                        <p class="text-[10px] text-emerald-700 italic mt-0.5">
                                            ✓ {{ $latestEvent->note }}
                                        </p>
                                    @endif
                                </td>

                                <!-- Store Partner -->
                                <td class="py-3.5 px-4 text-gray-700">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 text-[#564B68] font-bold text-[11px]">
                                        {{ $shipment->sellerOrder?->seller?->name ?? 'Merchant Store' }}
                                    </span>
                                </td>

                                <!-- Completed At -->
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">
                                        {{ $shipment->updated_at ? $shipment->updated_at->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $shipment->updated_at ? $shipment->updated_at->format('h:i A') : '' }}
                                    </div>
                                </td>

                                <!-- Payment Method -->
                                <td class="py-3.5 px-4">
                                    @if($order?->payment_method === 'cod')
                                        <div class="inline-flex flex-col">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                COD ₱{{ number_format($codAmount, 2) }}
                                            </span>
                                            <span class="text-[9px] text-emerald-600 font-bold mt-0.5">Collected & Remitted</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200">
                                            PREPAID
                                        </span>
                                    @endif
                                </td>

                                <!-- Rider Payout -->
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-heading font-bold text-sm text-[#564B68]">+₱65.00</span>
                                    <p class="text-[10px] text-emerald-600 font-bold">Credited</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-[#ECE7EF]">
                {{ $deliveries->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center space-y-3">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-50 text-[#564B68] flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h4 class="font-heading text-lg font-bold text-gray-900">No delivery history records found</h4>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    {{ $search ? "No results matching '{$search}'" : "You haven't completed any deliveries yet for this timeframe. Once you mark items as delivered, they will be archived here." }}
                </p>
                @if($search || $dateFilter !== 'all')
                    <a href="{{ route('courier.history') }}" class="inline-block mt-2 text-xs font-bold text-[#564B68] hover:underline">
                        Clear all filters
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
