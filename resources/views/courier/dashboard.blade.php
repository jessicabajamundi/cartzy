@extends('layouts.app')

@section('title', 'Courier Rider Hub | cartzy')

@section('content')
<div class="w-full max-w-[1920px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 py-8 space-y-6">

    <!-- Rider Profile Banner -->
    <div class="bg-black rounded-2xl p-6 text-white shadow-md flex flex-wrap items-center justify-between gap-4 border border-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white text-black rounded-2xl flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="2.5" stroke-width="1.8"/><circle cx="17.5" cy="16.5" r="2.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 16.5h5m-2.5-7l-3 7m2.5-7l4-2h3m-7 2l2-3h3"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="bg-white/20 text-white text-[10px] font-black uppercase px-2 py-0.5 rounded tracking-widest">EXPRESS RIDER</span>
                    <h1 class="text-2xl font-black">{{ $courier->name }}</h1>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-300 mt-1">
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Assigned Hub: <strong>Manila Central Hub 04</strong></span></span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><span><strong>{{ $stats['rider_rating'] }}</strong> Rating</span></span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><span>{{ $courier->phone ?? '0917-123-4567' }}</span></span>
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
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Today's Delivery Route</span>
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
                        <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5"><svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Unit 402, Acacia Tower, Taft Ave, Malate, Manila</span></p>
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
                        <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5"><svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>128 Mabini St, Ermita, Manila</span></p>
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
