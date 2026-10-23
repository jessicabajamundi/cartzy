@extends('layouts.courier')

@section('title', 'Rider Messaging | Cartzy Courier')
@section('header_title', 'Chat & Messages')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col md:flex-row gap-4">

    <!-- Conversations Sidebar -->
    <div class="w-full md:w-80 shrink-0 rider-card flex flex-col overflow-hidden h-full">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-[#ECE7EF]">
            <h2 class="font-heading text-lg font-bold text-[#564B68]">Messages & Inquiries</h2>
            <p class="text-xs text-gray-500">Coordinate directly with customers and hub dispatchers</p>
        </div>

        <!-- Conversation Threads List -->
        <div class="flex-1 overflow-y-auto divide-y divide-[#ECE7EF]">
            
            <!-- Logistics Hub Dispatcher Thread -->
            <a href="{{ route('courier.chat', ['type' => 'hub']) }}" 
               class="p-3.5 block transition {{ request('type') === 'hub' ? 'bg-[#F4EFF7] border-l-4 border-[#564B68]' : 'hover:bg-[#FAF9FC]' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs" style="background: var(--gradient-master);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs text-gray-900 truncate">Cartzy Central Dispatch</h4>
                            <span class="text-[10px] text-gray-400">Hub</span>
                        </div>
                        <p class="text-[11px] text-[#6F6382] truncate mt-0.5">
                            {{ $hubMessages->first()?->message ?? 'Dispatch notifications & route updates' }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Customer Orders Threads Header -->
            <div class="px-4 py-2 bg-[#F8F6FA] text-[10px] font-black uppercase tracking-wider text-gray-400">
                Customer Delivery Threads
            </div>

            @forelse($orders as $orderItem)
                @php
                    $buyer = $orderItem->order?->buyer;
                    $recipient = $orderItem->order?->shipping_address['recipient'] ?? $buyer?->name ?? 'Customer';
                    $isSelected = request('type') !== 'hub' && $activeOrder?->id === $orderItem->id;
                    $latestMsg = $orderItem->latestMessage;
                @endphp
                <a href="{{ route('courier.chat', ['order_id' => $orderItem->id]) }}" 
                   class="p-3.5 block transition {{ $isSelected ? 'bg-[#F4EFF7] border-l-4 border-[#8C5A50]' : 'hover:bg-[#FAF9FC]' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-heading text-sm font-bold text-white shrink-0 shadow-xs" style="background: var(--gradient-rose);">
                            {{ strtoupper(substr($recipient, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs text-gray-900 truncate">{{ $recipient }}</h4>
                                <span class="text-[10px] font-mono text-gray-400">
                                    {{ $latestMsg ? $latestMsg->created_at->format('h:i A') : '' }}
                                </span>
                            </div>
                            <p class="text-[10px] text-gray-500 font-medium truncate mt-0.5">
                                {{ $orderItem->order?->reference ?? 'Order Ref' }} • {{ $orderItem->seller?->name ?? 'Store' }}
                            </p>
                            <p class="text-[11px] text-gray-600 truncate mt-0.5 font-normal">
                                {{ $latestMsg ? $latestMsg->body : 'Start coordinating delivery with customer' }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-6 text-center text-xs text-gray-400">
                    No active delivery orders currently assigned.
                </div>
            @endforelse

        </div>
    </div>

    <!-- Active Chat Conversation Box -->
    <div class="flex-1 rider-card flex flex-col overflow-hidden h-full">

        @if(request('type') === 'hub')
            <!-- ================= HUB DISPATCH CHAT VIEW ================= -->
            <!-- Header -->
            <div class="p-4 border-b border-[#ECE7EF] flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs" style="background: var(--gradient-master);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-[#564B68]">Cartzy Logistics Hub Dispatcher</h3>
                        <p class="text-xs text-gray-500">Direct hotline with central depot supervisor & fleet management</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Hub Online
                    </span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#FAF9FC]">
                @forelse($hubMessages as $msg)
                    @php $isMe = ($msg->sender_type ?? '') === 'contact'; @endphp
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] space-y-1">
                            <div class="p-3.5 rounded-2xl text-xs {{ $isMe ? 'text-white rounded-br-xs shadow-xs' : 'bg-white text-gray-800 border border-[#ECE7EF] rounded-bl-xs shadow-2xs' }}"
                                 style="{{ $isMe ? 'background: var(--gradient-master);' : '' }}">
                                <p class="whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                            </div>
                            <p class="text-[10px] text-gray-400 {{ $isMe ? 'text-right' : 'text-left' }} px-1">
                                {{ $msg->created_at ? $msg->created_at->format('M d, h:i A') : 'Recently' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 space-y-2 text-gray-400">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-xs">No dispatch bulletins or notes yet. Send a message to report route delays or hub questions.</p>
                    </div>
                @endforelse
            </div>

            <!-- Bottom Reply Input -->
            <div class="p-3 border-t border-[#ECE7EF] bg-white">
                <form method="POST" action="{{ route('courier.chat.send', 0) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="hub">
                    <input 
                        type="text" 
                        name="message" 
                        placeholder="Type message to Central Dispatcher..." 
                        required 
                        autocomplete="off"
                        class="flex-1 px-4 py-2.5 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white"
                    >
                    <button type="submit" class="rider-btn-primary px-4 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 shadow-2xs">
                        <span>Send</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>

        @elseif($activeOrder)
            <!-- ================= CUSTOMER ORDER CHAT VIEW ================= -->
            @php
                $buyer = $activeOrder->order?->buyer;
                $recipient = $activeOrder->order?->shipping_address['recipient'] ?? $buyer?->name ?? 'Customer';
                $phone = $activeOrder->order?->shipping_address['phone'] ?? $buyer?->phone ?? '';
            @endphp
            <!-- Header -->
            <div class="p-4 border-b border-[#ECE7EF] flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-heading text-sm font-bold text-white shrink-0 shadow-xs" style="background: var(--gradient-rose);">
                        {{ strtoupper(substr($recipient, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm text-gray-900">{{ $recipient }}</h3>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-purple-50 text-[#564B68] font-bold">
                                {{ $activeOrder->order?->reference }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Store: {{ $activeOrder->seller?->name }} • {{ $activeOrder->status }}</p>
                    </div>
                </div>

                <!-- Call Button -->
                @if($phone)
                    <a href="tel:{{ $phone }}" class="rider-btn-outline px-3 py-1.5 rounded-xl text-xs font-bold text-[#8C5A50] border-[#8C5A50] flex items-center gap-1.5 hover:bg-rose-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Call</span>
                    </a>
                @endif
            </div>

            <!-- Quick Reply Chips -->
            <div class="px-4 py-2 border-b border-[#ECE7EF] bg-[#F8F6FA] flex items-center gap-2 overflow-x-auto text-[11px]">
                <button type="button" onclick="setReply('I am on my way to deliver your parcel!')" class="px-2.5 py-1 rounded-full bg-white border border-[#ECE7EF] text-[#564B68] font-semibold hover:border-[#564B68] whitespace-nowrap transition">
                    🚚 On the way
                </button>
                <button type="button" onclick="setReply('I have arrived outside your delivery address.')" class="px-2.5 py-1 rounded-full bg-white border border-[#ECE7EF] text-[#564B68] font-semibold hover:border-[#564B68] whitespace-nowrap transition">
                    📍 Arrived outside
                </button>
                <button type="button" onclick="setReply('Please prepare the exact COD payment amount. Thank you!')" class="px-2.5 py-1 rounded-full bg-white border border-[#ECE7EF] text-[#564B68] font-semibold hover:border-[#564B68] whitespace-nowrap transition">
                    💵 Prepare COD cash
                </button>
                <button type="button" onclick="setReply('Package received by authorized representative.')" class="px-2.5 py-1 rounded-full bg-white border border-[#ECE7EF] text-[#564B68] font-semibold hover:border-[#564B68] whitespace-nowrap transition">
                    ✍️ Package handed over
                </button>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#FAF9FC]">
                @forelse($messages as $msg)
                    @php $isMe = $msg->sender_id === $courier->id; @endphp
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] space-y-1">
                            <div class="p-3.5 rounded-2xl text-xs {{ $isMe ? 'text-white rounded-br-xs shadow-xs' : 'bg-white text-gray-800 border border-[#ECE7EF] rounded-bl-xs shadow-2xs' }}"
                                 style="{{ $isMe ? 'background: var(--gradient-master);' : '' }}">
                                <p class="whitespace-pre-line leading-relaxed">{{ $msg->body }}</p>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] text-gray-400 {{ $isMe ? 'justify-end' : 'justify-start' }} px-1">
                                <span>{{ $msg->created_at->format('h:i A') }}</span>
                                @if($isMe)
                                    <span class="text-purple-300">✓✓</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 space-y-2 text-gray-400">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-xs">No previous messages with {{ $recipient }}.</p>
                        <p class="text-[11px] text-gray-400">Send an ETA update or ask about building/gate instructions.</p>
                    </div>
                @endforelse
            </div>

            <!-- Bottom Reply Input -->
            <div class="p-3 border-t border-[#ECE7EF] bg-white">
                <form method="POST" action="{{ route('courier.chat.send', $activeOrder->id) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="order">
                    <input 
                        type="text" 
                        id="chatInputBox"
                        name="message" 
                        placeholder="Write a message to {{ $recipient }}..." 
                        required 
                        autocomplete="off"
                        class="flex-1 px-4 py-2.5 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white"
                    >
                    <button type="submit" class="rider-btn-primary px-4 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 shadow-2xs">
                        <span>Send</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>

        @else
            <!-- No Conversation Selected -->
            <div class="h-full flex flex-col items-center justify-center text-center p-6 space-y-3 text-gray-400">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-[#564B68] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h4 class="font-heading text-base font-bold text-gray-900">Select a Conversation</h4>
                <p class="text-xs text-gray-500 max-w-xs">
                    Choose a customer order thread from the sidebar or reach out to Cartzy Logistics Central Dispatch.
                </p>
            </div>
        @endif

    </div>

</div>

<script>
    function setReply(text) {
        const input = document.getElementById('chatInputBox');
        if (input) {
            input.value = text;
            input.focus();
        }
    }
</script>
@endsection
