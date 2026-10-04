@extends('layouts.logistics')

@section('title', 'Chat | Logistics Hub | cartzy')
@section('page_title', 'Chat / Messaging')
@section('page_subtitle', 'Communicate with sellers, riders, and platform admin')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 h-[calc(100vh-220px)] min-h-[500px]">

    {{-- Contacts Panel --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <div class="px-4 py-3.5 border-b border-slate-100 font-black text-slate-700 text-sm flex items-center gap-2">
            💬 Conversations
        </div>
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            @foreach($contacts as $contact)
            <a href="{{ route('logistics.chat', ['contact'=>$contact['id']]) }}"
               class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50/40 transition
                      {{ $currentContact['id']==$contact['id'] ? 'bg-blue-50 border-l-4 border-l-[#1A6FA8]' : '' }}">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-[#1A6FA8]/10 flex items-center justify-center text-xl">{{ $contact['avatar'] }}</div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white
                        {{ $contact['status']==='online' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-sm font-bold text-slate-800 truncate">{{ $contact['name'] }}</span>
                        <span class="text-[10px] text-slate-400 shrink-0">{{ $contact['time'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-1 mt-0.5">
                        <span class="text-xs text-slate-500 truncate">{{ $contact['last_message'] }}</span>
                        @if($contact['unread'])
                            <span class="bg-[#1A6FA8] text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center shrink-0">{{ $contact['unread'] }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-semibold text-slate-400">{{ $contact['role'] }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Chat Panel --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        {{-- Header --}}
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#1A6FA8]/10 flex items-center justify-center text-xl">{{ $currentContact['avatar'] }}</div>
            <div>
                <div class="font-black text-slate-800 text-sm">{{ $currentContact['name'] }}</div>
                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $currentContact['status']==='online' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    {{ ucfirst($currentContact['status']) }} &mdash; {{ $currentContact['role'] }}
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-4">
            @foreach($messages as $msg)
            <div class="flex {{ $msg['sender']==='hub' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%]">
                    <div class="px-4 py-2.5 rounded-2xl text-sm font-medium shadow-sm
                        {{ $msg['sender']==='hub'
                            ? 'bg-[#1A6FA8] text-white rounded-br-none'
                            : 'bg-slate-100 text-slate-800 rounded-bl-none' }}">
                        {{ $msg['text'] }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-1 {{ $msg['sender']==='hub' ? 'text-right' : 'text-left' }}">{{ $msg['time'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Input --}}
        <div class="px-5 py-4 border-t border-slate-100">
            <form method="POST" action="{{ route('logistics.chat.send', $currentContact['id']) }}" class="flex items-center gap-3">
                @csrf
                <input type="text" name="message" placeholder="Type a message..." autocomplete="off"
                       class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                <button type="submit" class="bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black px-5 py-2.5 rounded-xl transition shadow">
                    Send
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const chatEl = document.getElementById('chatMessages');
if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;
</script>
@endpush
