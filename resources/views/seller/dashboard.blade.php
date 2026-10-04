@extends('layouts.seller')

@section('title', 'Dashboard Overview | Seller Centre')
@section('page_title', 'Seller Dashboard & Overview')

@section('content')
<div class="space-y-6">

    <!-- Store Profile Header -->
    <div class="bg-gradient-to-r from-[#282133] via-[#3E344E] to-[#564B68] rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-slate-700/50">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 bg-white text-[#282133] rounded-3xl flex items-center justify-center shadow-lg shrink-0">
                <svg class="w-10 h-10 text-[#282133]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight font-heading">{{ $seller->name ?? 'Maria Santos' }} Store</h1>
                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Verified Merchant</span>
                    </span>
                    <span class="bg-amber-400/20 text-amber-300 border border-amber-300/30 text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-amber-300 text-amber-300" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>Mall Preferred</span>
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 mt-2">
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $stats['rating'] }}</strong> / 5.0 Rating</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> {{ $stats['total_products'] }}</strong> Active Products</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> {{ $stats['response_rate'] }}%</strong> Chat Response</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg> 99%</strong> Fast Shipping</span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('seller.inventory') }}" class="bg-white text-slate-900 hover:bg-slate-100 text-xs font-black px-4 py-3 rounded-xl shadow-md transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Product</span>
            </a>
            <a href="{{ route('seller.reports') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-3 rounded-xl border border-white/20 transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Financial Reports</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (4 Columns) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Gross Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-[#6F6382] transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gross Sales</span>
                <span class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">₱{{ number_format($stats['gross_sales'], 2) }}</div>
                <div class="text-xs text-emerald-600 font-bold mt-1 flex items-center gap-1">
                    <span>↑ +18.4%</span>
                    <span class="text-slate-400 font-normal">vs last month</span>
                </div>
            </div>
        </div>

        <!-- Net Profit (After 10% platform fee) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-[#6F6382] transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Net Profit (90%)</span>
                <span class="w-9 h-9 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-[#564B68]">₱{{ number_format($stats['net_profit'], 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">10% Platform fee deducted</div>
            </div>
        </div>

        <!-- Seller Wallet Balance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-[#6F6382] transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Available Wallet</span>
                <span class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 10h20M6 14h2"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">₱{{ number_format($stats['wallet_balance'], 2) }}</div>
                <div class="text-xs text-blue-600 font-bold mt-1 flex items-center justify-between">
                    <span>Ready for withdrawal</span>
                    <a href="{{ route('seller.account') }}" class="underline hover:text-blue-800">Payout &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Active Inventory & Stock Alerts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-[#6F6382] transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Inventory Status</span>
                <span class="w-9 h-9 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ $stats['total_products'] }} Products</div>
                <div class="text-xs text-amber-600 font-bold mt-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $stats['low_stock'] }} items low in stock</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Order Management Action To-Do Pipeline -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Order Fulfillment Pipeline</h2>
                <p class="text-xs text-slate-500">Orders requiring your packaging, courier handover, or delivery confirmation</p>
            </div>
            <a href="{{ route('seller.orders') }}" class="text-xs font-bold text-[#6F6382] hover:text-[#564B68] hover:underline">
                View All Orders &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            
            <!-- 1. To Pack -->
            <a href="{{ route('seller.orders', ['status' => 'new']) }}" class="p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-[#6F6382] transition group text-center">
                <div class="text-3xl font-black text-rose-600 group-hover:scale-110 transition-transform">
                    {{ $stats['to_pack'] }}
                </div>
                <div class="text-xs font-bold text-slate-800 mt-1">To Pack (New Orders)</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Needs packaging & AWB label</div>
            </a>

            <!-- 2. Ready for Courier Pickup -->
            <a href="{{ route('seller.courier') }}" class="p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-[#6F6382] transition group text-center">
                <div class="text-3xl font-black text-amber-600 group-hover:scale-110 transition-transform">
                    {{ $stats['ready_pickup'] }}
                </div>
                <div class="text-xs font-bold text-slate-800 mt-1">Ready for Courier</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Schedule pickup / rider arrival</div>
            </a>

            <!-- 3. In Transit -->
            <a href="{{ route('seller.courier') }}" class="p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-[#6F6382] transition group text-center">
                <div class="text-3xl font-black text-indigo-600 group-hover:scale-110 transition-transform">
                    {{ $stats['in_transit'] }}
                </div>
                <div class="text-xs font-bold text-slate-800 mt-1">In Transit</div>
                <div class="text-[11px] text-slate-500 mt-0.5">With courier riders on the road</div>
            </a>

            <!-- 4. Confirmed Deliveries -->
            <a href="{{ route('seller.deliveries') }}" class="p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-[#6F6382] transition group text-center">
                <div class="text-3xl font-black text-emerald-600 group-hover:scale-110 transition-transform">
                    {{ $stats['completed'] }}
                </div>
                <div class="text-xs font-bold text-slate-800 mt-1">Delivered (Escrow Released)</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Customer accepted orders</div>
            </a>

        </div>
    </div>

    <!-- Interactive Charts & Live Notifications (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sales & Performance Chart (2 Columns Wide) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Sales & Performance Tracking</h3>
                    <p class="text-xs text-slate-500">Daily gross revenue and order volume for the past 7 days</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 font-bold text-[#6F6382]">
                        <span class="w-3 h-3 rounded-full bg-[#6F6382]"></span> Sales (₱)
                    </span>
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Orders Count
                    </span>
                </div>
            </div>

            <!-- Canvas for Chart.js -->
            <div class="h-64 w-full">
                <canvas id="sellerSalesChart"></canvas>
            </div>
        </div>

        <!-- Store Notifications & Activity Feed (1 Column Wide) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-extrabold text-slate-900">Live Order Alerts</h3>
                    <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-bold">Real-time</span>
                </div>

                <div class="space-y-3">
                    @foreach($notifications as $notif)
                        <a href="{{ $notif['link'] }}" class="block p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 hover:border-slate-300 transition {{ $notif['unread'] ? 'bg-purple-50/40 border-purple-100' : '' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-900">{{ $notif['title'] }}</span>
                                <span class="text-[10px] text-slate-400">{{ $notif['time'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $notif['message'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <a href="{{ route('seller.chat') }}" class="w-full bg-[#FAF9FB] hover:bg-[#F1EFF5] text-[#564B68] text-xs font-bold py-2.5 rounded-xl border border-[#E1DDE7] transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-[#564B68]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Open Customer Web Chat</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Recent Customer Orders</h3>
                <p class="text-xs text-slate-500">Latest incoming orders requiring review and dispatch</p>
            </div>
            <a href="{{ route('seller.orders') }}" class="text-xs font-bold text-[#6F6382] hover:text-[#564B68] hover:underline">
                View All Orders &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Order ID & Date</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Items Ordered</th>
                        <th class="p-4">Total Amount</th>
                        <th class="p-4">Payment</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-900">{{ $order['id'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order['date'] }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $order['buyer_name'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order['buyer_phone'] }}</div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 max-w-xs truncate">
                                    <img src="{{ $order['items'][0]['image'] }}" alt="" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <span class="truncate">{{ $order['items'][0]['name'] }}</span>
                                    @if(count($order['items']) > 1)
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold">+{{ count($order['items']) - 1 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-black text-slate-900">₱{{ number_format($order['total_amount'], 2) }}</div>
                                <div class="text-[10px] text-slate-400">Net: ₱{{ number_format($order['total_amount'] * 0.90, 2) }}</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $order['payment_method'] }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($order['status'] === 'new')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                        ● New Order
                                    </span>
                                @elseif($order['status'] === 'to_pack')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
                                        ● To Pack
                                    </span>
                                @elseif($order['status'] === 'ready_pickup')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                        ● Ready for Pickup
                                    </span>
                                @elseif($order['status'] === 'in_transit')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">
                                        ● In Transit
                                    </span>
                                @elseif($order['status'] === 'delivered')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Delivered
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right space-x-1">
                                @if(in_array($order['status'], ['new', 'to_pack']))
                                    <form action="{{ route('seller.orders.pack', $order['id']) }}" method="POST" class="inline-block m-0">
                                        @csrf
                                        <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-xs transition">
                                            Pack Item
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('seller.orders.waybill', $order['id']) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>AWB</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('sellerSalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'Sales Revenue (₱)',
                        data: {!! json_encode($chartData['sales']) !!},
                        borderColor: '#6F6382',
                        backgroundColor: 'rgba(111, 99, 130, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders Count',
                        data: {!! json_encode($chartData['orders']) !!},
                        borderColor: '#10B981',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.4,
                        borderWidth: 2,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(val) { return '₱' + (val / 1000) + 'k'; }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
