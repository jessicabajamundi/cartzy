@extends('layouts.seller')

@section('title', 'Order Management & Notifications | Seller Centre')
@section('page_title', 'Order Management & Fulfillment')

@section('content')
<div class="space-y-6">

    <!-- Header & Summary -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Customer Orders & Fulfillment</h1>
            <p class="text-xs text-slate-500 mt-1">Review new orders, pack items, print standard AWB waybills, and hand over to couriers.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                {{ $counts['new'] }} New Orders Waiting
            </span>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs font-bold">
        <a href="{{ route('seller.orders', ['status' => 'all']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            All Orders ({{ $counts['all'] }})
        </a>
        <a href="{{ route('seller.orders', ['status' => 'new']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'new' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            🔴 New Orders ({{ $counts['new'] }})
        </a>
        <a href="{{ route('seller.orders', ['status' => 'to_pack']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'to_pack' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            🟡 To Pack ({{ $counts['to_pack'] }})
        </a>
        <a href="{{ route('seller.orders', ['status' => 'ready_pickup']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'ready_pickup' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            🔵 Ready for Pickup ({{ $counts['ready_pickup'] }})
        </a>
        <a href="{{ route('seller.orders', ['status' => 'in_transit']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'in_transit' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            🟣 In Transit ({{ $counts['in_transit'] }})
        </a>
        <a href="{{ route('seller.orders', ['status' => 'delivered']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $statusFilter === 'delivered' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            🟢 Delivered ({{ $counts['delivered'] }})
        </a>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($filteredOrders as $order)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden hover:border-[#6F6382] transition">
                
                <!-- Order Header Bar -->
                <div class="bg-slate-50/90 px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-900 text-sm">#{{ $order['id'] }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-500 font-medium">{{ $order['date'] }}</span>
                        </div>
                        <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-slate-700 border border-slate-200">
                            {{ $order['courier_name'] }}
                        </span>
                        @if(!empty($order['tracking_number']))
                            <span class="text-[11px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                AWB: {{ $order['tracking_number'] }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if($order['status'] === 'new')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                New Order (Action Required)
                            </span>
                        @elseif($order['status'] === 'to_pack')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                                🟡 Preparing Items
                            </span>
                        @elseif($order['status'] === 'ready_pickup')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                                🔵 Ready for Courier Pickup
                            </span>
                        @elseif($order['status'] === 'in_transit')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-purple-50 text-purple-700 border border-purple-200">
                                🟣 In Transit With Courier
                            </span>
                        @elseif($order['status'] === 'delivered')
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                ✓ Confirmed Delivered
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Order Body Content -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Items List (6 Cols) -->
                    <div class="lg:col-span-6 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Order Items</div>
                        @foreach($order['items'] as $item)
                            <div class="flex items-center gap-3.5 p-2 rounded-2xl bg-slate-50/60 border border-slate-100">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-extrabold text-slate-900 truncate">{{ $item['name'] }}</h4>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>Variant: <strong>{{ $item['variant'] }}</strong></span>
                                        <span>•</span>
                                        <span>Qty: <strong>{{ $item['qty'] }}</strong></span>
                                    </div>
                                    <div class="text-xs font-bold text-slate-800 mt-1">₱{{ number_format($item['price'], 2) }} each</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Customer & Delivery Address (3 Cols) -->
                    <div class="lg:col-span-3 text-xs space-y-1.5 border-t lg:border-t-0 lg:border-l border-slate-100 lg:pl-6 pt-4 lg:pt-0">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Customer & Shipping</div>
                        <div class="font-extrabold text-slate-900 text-sm">{{ $order['buyer_name'] }}</div>
                        <div class="text-slate-500 font-medium">{{ $order['buyer_phone'] }}</div>
                        <p class="text-slate-600 leading-relaxed mt-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            📍 {{ $order['shipping_address'] }}
                        </p>
                    </div>

                    <!-- Financial Breakdown & Actions (3 Cols) -->
                    <div class="lg:col-span-3 text-xs flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-slate-100 lg:pl-6 pt-4 lg:pt-0">
                        <div class="space-y-1.5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Payment Breakdown</div>
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal:</span>
                                <span>₱{{ number_format($order['subtotal'], 2) }}</span>
                            </div>
                            @if($order['voucher_discount'] > 0)
                                <div class="flex justify-between text-emerald-600 font-medium">
                                    <span>Voucher Discount:</span>
                                    <span>-₱{{ number_format($order['voucher_discount'], 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-slate-500">
                                <span>Shipping Fee:</span>
                                <span>₱{{ number_format($order['shipping_fee'], 2) }}</span>
                            </div>
                            <div class="flex justify-between font-black text-slate-900 text-sm pt-1 border-t border-slate-200">
                                <span>Total Paid:</span>
                                <span class="text-[#564B68]">₱{{ number_format($order['total_amount'], 2) }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 pt-0.5">
                                Payment: <strong class="text-slate-700">{{ $order['payment_method'] }}</strong> ({{ strtoupper($order['payment_status']) }})
                            </div>
                            <div class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded font-bold">
                                Seller Net (90%): ₱{{ number_format($order['total_amount'] * 0.90, 2) }}
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 space-y-2">
                            @if(in_array($order['status'], ['new', 'to_pack']))
                                <form action="{{ route('seller.orders.pack', $order['id']) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="w-full bg-[#6F6382] hover:bg-[#564B68] text-white text-xs font-bold py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                                        <span>📦 Pack & Ready Order</span>
                                    </button>
                                </form>
                            @endif

                            @if($order['status'] === 'ready_pickup')
                                <a href="{{ route('seller.courier') }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                                    <span>🚚 Schedule Courier Pickup</span>
                                </a>
                            @endif

                            <div class="flex items-center gap-2">
                                <a href="{{ route('seller.orders.waybill', $order['id']) }}" target="_blank" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 rounded-xl transition text-center flex items-center justify-center gap-1">
                                    <span>🖨️ Print Waybill</span>
                                </a>
                                <button type="button" onclick="openOrderModal('{{ $order['id'] }}')" class="px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 rounded-xl transition">
                                    Details
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <div class="text-4xl mb-3">📦</div>
                <h3 class="text-base font-extrabold text-slate-800">No Orders in this Status</h3>
                <p class="text-xs text-slate-500 mt-1">There are currently no orders under "{{ ucfirst($statusFilter) }}".</p>
                <a href="{{ route('seller.orders', ['status' => 'all']) }}" class="mt-4 inline-block text-xs font-bold text-[#6F6382] hover:underline">
                    View All Orders
                </a>
            </div>
        @endforelse
    </div>

</div>

<!-- Order Detail Modal Script -->
<script>
    function openOrderModal(orderId) {
        alert('Displaying complete order manifest and verification details for Order #' + orderId + '. You can print shipping label directly.');
    }
</script>
@endsection
