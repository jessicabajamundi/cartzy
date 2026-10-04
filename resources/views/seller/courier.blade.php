@extends('layouts.seller')

@section('title', 'Hand Over to Courier & Tracking | Seller Centre')
@section('page_title', 'Courier Handover & Shipment Tracking')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Summary -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-indigo-700/50">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-2xl">🚚</span>
                <h1 class="text-2xl font-extrabold font-heading">Courier Dispatch & Shipment Tracking</h1>
            </div>
            <p class="text-xs text-indigo-200 mt-1 max-w-xl leading-relaxed">
                Schedule on-demand courier rider pickups at your warehouse address, generate hand-over manifests, and monitor real-time delivery milestones.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/10 px-4 py-2 rounded-2xl border border-white/20 text-center">
                <div class="text-2xl font-black text-amber-300">{{ count($pickupReady) }}</div>
                <div class="text-[10px] text-indigo-200 font-bold uppercase">Ready for Pickup</div>
            </div>
            <div class="bg-white/10 px-4 py-2 rounded-2xl border border-white/20 text-center">
                <div class="text-2xl font-black text-emerald-300">{{ count($inTransit) }}</div>
                <div class="text-[10px] text-indigo-200 font-bold uppercase">On the Road</div>
            </div>
        </div>
    </div>

    <!-- Integrated Logistics Partners -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($courierPartners as $partner)
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $partner['badge'] }}</span>
                    <h4 class="font-extrabold text-xs text-slate-900 mt-1">{{ $partner['name'] }}</h4>
                    <p class="text-[10px] text-slate-500">{{ $partner['type'] }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-amber-500">⭐ {{ $partner['rating'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Section 1: Orders Ready for Courier Pickup -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">📦 Packed Parcels Awaiting Courier Pickup ({{ count($pickupReady) }})</h2>
                <p class="text-xs text-slate-500">Schedule pickup so designated riders can collect your prepared packages.</p>
            </div>
            <span class="text-xs text-slate-500 font-medium">Pickup Address: Unit 402, High Street Plaza, BGC</span>
        </div>

        @if(count($pickupReady) > 0)
            <div class="space-y-4">
                @foreach($pickupReady as $order)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm text-slate-900">Order #{{ $order['id'] }}</span>
                                <span class="text-[11px] font-mono bg-white px-2 py-0.5 rounded border border-slate-200 font-bold text-slate-600">
                                    AWB: {{ $order['tracking_number'] }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600">
                                Consignee: <strong>{{ $order['buyer_name'] }}</strong> • {{ $order['shipping_address'] }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                Items: {{ $order['items'][0]['name'] }} (Qty: {{ $order['items'][0]['qty'] }})
                            </div>
                        </div>

                        <!-- Schedule Pickup Form -->
                        <form action="{{ route('seller.courier.schedule', $order['id']) }}" method="POST" class="flex flex-wrap items-center gap-2 m-0">
                            @csrf
                            <select name="courier_name" class="bg-white border border-slate-300 text-xs font-bold rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                                <option value="cartzy Express Fleet">cartzy Express Fleet</option>
                                <option value="J&T Express Philippines">J&T Express Philippines</option>
                                <option value="Flash Express PH">Flash Express PH</option>
                                <option value="2GO Logistics">2GO Logistics</option>
                            </select>

                            <select name="pickup_slot" class="bg-white border border-slate-300 text-xs font-bold rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                                <option value="Today (1:00 PM - 5:00 PM)">Today (1:00 PM - 5:00 PM)</option>
                                <option value="Tomorrow (9:00 AM - 1:00 PM)">Tomorrow (9:00 AM - 1:00 PM)</option>
                                <option value="Tomorrow (1:00 PM - 5:00 PM)">Tomorrow (1:00 PM - 5:00 PM)</option>
                            </select>

                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
                                <span>Schedule Pickup &rarr;</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 text-slate-500 text-xs">
                All packed orders have been assigned to couriers! <a href="{{ route('seller.orders', ['status' => 'new']) }}" class="text-[#6F6382] font-bold underline">Check for new orders to pack.</a>
            </div>
        @endif
    </div>

    <!-- Section 2: Active Shipments In Transit (With Real-Time Milestones) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">🚚 Active Shipments In Transit & Live Tracking ({{ count($inTransit) }})</h2>
            <p class="text-xs text-slate-500">Parcels handed over to courier riders currently moving through logistics hubs to customers.</p>
        </div>

        @if(count($inTransit) > 0)
            <div class="space-y-6">
                @foreach($inTransit as $order)
                    <div class="p-6 rounded-2xl border border-slate-200 bg-white shadow-xs space-y-5">
                        
                        <!-- Order & Rider Banner -->
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm">Order #{{ $order['id'] }}</h3>
                                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                        Tracking: {{ $order['tracking_number'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Courier: <strong class="text-slate-800">{{ $order['courier_name'] }}</strong> • Recipient: <strong>{{ $order['buyer_name'] }}</strong> ({{ $order['shipping_address'] }})
                                </div>
                            </div>

                            <!-- Rider Information Card -->
                            <div class="flex items-center gap-3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
                                <span class="text-xl">🛵</span>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $order['rider_name'] ?? 'Arnel Gomez (Express Rider)' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        Plate: <span class="font-mono font-bold">{{ $order['rider_plate'] ?? 'MC-8924-NCR' }}</span> • {{ $order['rider_phone'] ?? '+63 917 842 1928' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5-Step Visual Timeline Progress Bar -->
                        <div class="relative py-2">
                            <div class="grid grid-cols-5 text-center text-xs relative z-10">
                                
                                <!-- Step 1 -->
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white font-black text-xs flex items-center justify-center mx-auto shadow-sm">✓</div>
                                    <div class="font-extrabold text-slate-900 text-[11px]">Order Placed</div>
                                    <div class="text-[10px] text-slate-400">Paid</div>
                                </div>

                                <!-- Step 2 -->
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white font-black text-xs flex items-center justify-center mx-auto shadow-sm">✓</div>
                                    <div class="font-extrabold text-slate-900 text-[11px]">Packed by Seller</div>
                                    <div class="text-[10px] text-slate-400">AWB Created</div>
                                </div>

                                <!-- Step 3 -->
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white font-black text-xs flex items-center justify-center mx-auto shadow-sm">✓</div>
                                    <div class="font-extrabold text-slate-900 text-[11px]">Handed to Courier</div>
                                    <div class="text-[10px] text-slate-400">Rider Picked Up</div>
                                </div>

                                <!-- Step 4 -->
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center mx-auto shadow-sm animate-pulse">4</div>
                                    <div class="font-extrabold text-indigo-700 text-[11px]">In Transit</div>
                                    <div class="text-[10px] text-slate-400">NCR Hub Sorting</div>
                                </div>

                                <!-- Step 5 -->
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 font-bold text-xs flex items-center justify-center mx-auto">5</div>
                                    <div class="font-bold text-slate-400 text-[11px]">Delivery Confirmation</div>
                                    <div class="text-[10px] text-slate-400">Escrow Release</div>
                                </div>

                            </div>
                        </div>

                        <!-- Manual Simulate Customer Delivery Button -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Estimated Delivery: <strong>Today by 6:00 PM</strong></span>
                            <form action="{{ route('seller.deliveries.confirm', $order['id']) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
                                    <span>Simulate Customer Delivery & Escrow Release &rarr;</span>
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 text-slate-500 text-xs">
                No packages currently in transit.
            </div>
        @endif
    </div>

</div>
@endsection
