@extends('layouts.seller')

@section('title', 'Chat & Messaging | Seller Centre')
@section('page_title', 'Customer Chat & Inquiries')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col md:flex-row bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    
    <!-- Left: Contacts & Inquiries List -->
    <div class="w-full md:w-80 lg:w-96 border-r border-slate-200 flex flex-col shrink-0">
        
        <!-- Search & Filter Header -->
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <h2 class="text-sm font-extrabold text-slate-900 mb-2">Customer Messages</h2>
            <div class="relative">
                <input type="text" placeholder="Search conversations..." class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                <span class="absolute left-3 top-2.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>
        </div>

        <!-- Contacts Scroll Area -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 text-xs">
            @foreach($contacts as $contact)
                <a href="{{ route('seller.chat', ['contact' => $contact['id']]) }}" class="block p-4 transition hover:bg-slate-50 {{ $contact['id'] == $currentContact['id'] ? 'bg-purple-50/60 border-l-4 border-[#6F6382]' : '' }}">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center border border-slate-200 shrink-0">
                                @if(($contact['role'] ?? '') === 'Admin Support')
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @else
                                    {{ strtoupper(substr($contact['name'] ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <span class="font-extrabold text-slate-900 truncate">{{ $contact['name'] }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $contact['time'] }}</span>
                    </div>

                    @if(!empty($contact['item_inquiry']))
                        <div class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-md mb-1.5 truncate max-w-[200px]">
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span class="truncate">{{ $contact['item_inquiry'] }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <p class="text-[11px] text-slate-500 truncate max-w-[220px]">{{ $contact['last_message'] }}</p>
                        @if($contact['unread'] > 0)
                            <span class="w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center shrink-0">
                                {{ $contact['unread'] }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

    </div>

    <!-- Right: Active Chat Thread -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50/50">
        
        <!-- Active Chat Header -->
        <div class="p-4 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-black text-sm flex items-center justify-center border border-slate-200 shrink-0">
                    @if(($currentContact['role'] ?? '') === 'Admin Support')
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    @else
                        {{ strtoupper(substr($currentContact['name'] ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-900 text-sm">{{ $currentContact['name'] }}</h3>
                        <span class="w-2 h-2 rounded-full bg-emerald-500" title="Online"></span>
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Inquiry: <strong class="text-slate-700">{{ $currentContact['item_inquiry'] ?? 'General Store Question' }}</strong>
                        @if(!empty($currentContact['item_price']))
                            • <span class="font-bold text-[#564B68]">{{ $currentContact['item_price'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold px-2.5 py-1 rounded-full">
                    98% Store Response Rate
                </span>
            </div>
        </div>

        <!-- Chat Messages Log -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            @foreach($messages as $msg)
                @if($msg['sender'] === 'seller')
                    <!-- Outgoing Message (Seller) -->
                    <div class="flex flex-col items-end space-y-1">
                        <div class="max-w-md bg-[#6F6382] text-white p-3.5 rounded-2xl rounded-tr-none text-xs leading-relaxed shadow-xs">
                            {{ $msg['text'] }}
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $msg['time'] }}</span>
                    </div>
                @else
                    <!-- Incoming Message (Buyer / Admin) -->
                    <div class="flex flex-col items-start space-y-1">
                        <div class="max-w-md bg-white border border-slate-200 text-slate-800 p-3.5 rounded-2xl rounded-tl-none text-xs leading-relaxed shadow-2xs">
                            {{ $msg['text'] }}
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $msg['time'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Quick Reply Chips -->
        <div class="px-4 py-2 bg-white border-t border-slate-100 flex items-center gap-2 overflow-x-auto text-[11px]">
            <span class="text-slate-400 font-bold shrink-0">Quick Replies:</span>
            <button type="button" onclick="setQuickReply('Yes po, 100% authentic and on-hand ready to dispatch today!')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium shrink-0">
                "Yes on-hand & ready to ship"
            </button>
            <button type="button" onclick="setQuickReply('Thank you for ordering! We will wrap and pack your package carefully with bubble wrap.')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium shrink-0">
                "Will pack carefully"
            </button>
            <button type="button" onclick="setQuickReply('Yes po, we include official BIR Sales Invoice receipt inside the parcel package.')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium shrink-0">
                "Includes official invoice"
            </button>
        </div>

        <!-- Message Input Box -->
        <form action="{{ route('seller.chat.send', $currentContact['id']) }}" method="POST" class="p-4 bg-white border-t border-slate-200 flex items-center gap-3 m-0">
            @csrf
            <input 
                type="text" 
                name="message" 
                id="chatInput" 
                required 
                placeholder="Type your message to {{ $currentContact['name'] }}..." 
                class="flex-1 bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]"
            >
            <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-5 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2 text-xs">
                <span>Send</span>
                <span>&rarr;</span>
            </button>
        </form>

    </div>

</div>

<script>
    function setQuickReply(text) {
        document.getElementById('chatInput').value = text;
        document.getElementById('chatInput').focus();
    }
</script>
@endsection
