@extends('layouts.app')

@section('title', 'Courier Rider Hub | cartzy')

@section('content')
<div class="w-full max-w-[1920px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 py-8 space-y-6">

    <!-- Rider Profile Banner -->
    <div class="bg-black rounded-2xl p-6 text-white shadow-md flex flex-wrap items-center justify-between gap-4 border border-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white text-black rounded-2xl flex items-center justify-center text-3xl font-extrabold shadow-sm">
                🛵
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="bg-white/20 text-white text-[10px] font-black uppercase px-2 py-0.5 rounded tracking-widest">EXPRESS RIDER</span>
                    <h1 class="text-2xl font-black">{{ $courier->name }}</h1>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-300 mt-1">
                    <span>📍 Assigned Hub: <strong>Manila Central Hub 04</strong></span>
                    <span>•</span>
                    <span>⭐ <strong>{{ $stats['rider_rating'] }}</strong> Rating</span>
                    <span>•</span>
                    <span>📞 {{ $courier->phone ?? '0917-123-4567' }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 bg-gray-800 text-white text-xs font-bold px-3 py-1.5 rounded-full border border-gray-700 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                ON DUTY (Active)
            </span>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-xs font-semibold text-gray-500 uppercase">Ready for Pickup</div>
            <div class="text-2xl font-black text-gray-900 mt-2">{{ $stats['assigned_pickups'] }} Parcels</div>
            <div class="text-[11px] text-gray-400 mt-0.5">From sellers' warehouses</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-xs font-semibold text-gray-500 uppercase">Out For Delivery</div>
            <div class="text-2xl font-black text-gray-900 mt-2">{{ $stats['out_for_delivery'] }} Parcels</div>
            <div class="text-[11px] text-gray-400 mt-0.5">To buyers in your route</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-xs font-semibold text-gray-500 uppercase">Delivered Today</div>
            <div class="text-2xl font-black text-gray-900 mt-2">{{ $stats['delivered_today'] }} Completed</div>
            <div class="text-[11px] text-gray-600 font-semibold mt-0.5">100% Success rate</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-xs font-semibold text-gray-500 uppercase">COD Remittance</div>
            <div class="text-2xl font-black text-gray-900 mt-2">₱{{ number_format($stats['cod_collected_today'], 2) }}</div>
            <div class="text-[11px] text-gray-600 font-semibold mt-0.5">Ready for end-of-day remit</div>
        </div>

    </div>

    <!-- Active Delivery Task Queue -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider flex items-center gap-2">
                <span>📦</span> Today's Delivery Route
            </h3>
            <span class="text-xs text-gray-500">18 Parcels Remaining</span>
        </div>

        <div class="divide-y divide-gray-100">
            
            <!-- Parcel Task 1 -->
            <div class="p-4 hover:bg-gray-50 flex flex-wrap items-center justify-between gap-4 transition">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-900 font-bold text-sm flex items-center justify-center border border-gray-200">
                        #1
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-gray-900">EXP-PH-98214730</span>
                            <span class="bg-gray-100 text-gray-800 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-200">COD: ₱1,299.00</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">Recipient: <strong>Maria Santos</strong> (0918-987-6543)</p>
                        <p class="text-[11px] text-gray-400">📍 Unit 402, Acacia Tower, Taft Ave, Malate, Manila</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="bg-black hover:bg-gray-800 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-2xs transition">
                        ✓ Mark Delivered
                    </button>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold px-3 py-2 rounded-lg transition border border-gray-200">
                        Call Buyer
                    </button>
                </div>
            </div>

            <!-- Parcel Task 2 -->
            <div class="p-4 hover:bg-gray-50 flex flex-wrap items-center justify-between gap-4 transition">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-900 font-bold text-sm flex items-center justify-center border border-gray-200">
                        #2
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-gray-900">EXP-PH-98214742</span>
                            <span class="bg-gray-100 text-gray-800 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-200">Prepaid</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">Recipient: <strong>Juan Dela Cruz</strong> (0917-555-1234)</p>
                        <p class="text-[11px] text-gray-400">📍 128 Mabini St, Ermita, Manila</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="bg-black hover:bg-gray-800 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-2xs transition">
                        ✓ Mark Delivered
                    </button>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold px-3 py-2 rounded-lg transition border border-gray-200">
                        Call Buyer
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
