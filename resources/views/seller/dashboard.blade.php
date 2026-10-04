@extends('layouts.seller')

@section('title', 'Dashboard Overview | Seller Centre')
@section('page_title', 'Seller Dashboard & Overview')

@section('content')
<div class="space-y-6">

    <!-- Store Profile Header -->
    <div class="bg-gradient-to-r from-[#282133] via-[#3E344E] to-[#564B68] rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-slate-700/50">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 bg-white text-[#282133] rounded-3xl flex items-center justify-center text-4xl font-extrabold shadow-lg shrink-0">
                🏬
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight font-heading">{{ $seller->name ?? 'Maria Santos' }} Store</h1>
                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                        ✓ Verified Merchant
                    </span>
                    <span class="bg-amber-400/20 text-amber-300 border border-amber-300/30 text-xs font-bold px-2.5 py-1 rounded-full">
                        ⭐ Mall Preferred
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 mt-2">
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm">⭐ {{ $stats['rating'] }}</strong> / 5.0 Rating</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm">📦 {{ $stats['total_products'] }}</strong> Active Products</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm">⚡ {{ $stats['response_rate'] }}%</strong> Chat Response</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><strong class="text-white text-sm">🚚 99%</strong> Fast Shipping</span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('seller.inventory') }}" class="bg-white text-slate-900 hover:bg-slate-100 text-xs font-black px-4 py-3 rounded-xl shadow-md transition flex items-center gap-2">
                <span>➕ Add New Product</span>
            </a>
            <a href="{{ route('seller.reports') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-3 rounded-xl border border-white/20 transition flex items-center gap-2">
                <span>📑 Financial Reports</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (4 Columns) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Gross Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-[#6F6382] transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gross Sales</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl text-lg">💰</span>
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
                <span class="p-2 bg-purple-50 text-purple-600 rounded-xl text-lg">📈</span>
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
                <span class="p-2 bg-blue-50 text-blue-600 rounded-xl text-lg">💳</span>
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
                <span class="p-2 bg-amber-50 text-amber-600 rounded-xl text-lg">🏷️</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ $stats['total_products'] }} Products</div>
                <div class="text-xs text-amber-600 font-bold mt-1 flex items-center gap-1">
                    <span>⚠️ {{ $stats['low_stock'] }} items low in stock</span>
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
                    <span>💬 Open Customer Web Chat</span>
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
                                <a href="{{ route('seller.orders.waybill', $order['id']) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold px-3 py-1.5 rounded-lg transition inline-block">
                                    🖨️ AWB
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
