@extends('layouts.admin')

@section('title', 'Admin Chat & Messaging | cartzy')
@section('page_title', 'Admin Communication Hub')

@section('content')
<div class="max-w-7xl mx-auto h-[calc(100vh-180px)] min-h-[600px] flex flex-col bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Left Contacts Sidebar -->
        <div class="w-80 sm:w-96 border-r border-slate-200 flex flex-col bg-slate-50/50 shrink-0">
            
            <div class="p-4 border-b border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-black text-sm text-slate-900 flex items-center gap-2">
                        <span>💬</span> Direct Messages
                    </h2>
                    <span class="bg-indigo-100 text-indigo-800 text-[10px] font-black px-2 py-0.5 rounded-full">
                        {{ count($contacts) }} Active
                    </span>
                </div>
                <input type="text" placeholder="Search conversations..." class="w-full text-xs p-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-600">
            </div>

            <!-- Contact List -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                @foreach($contacts as $c)
                <a href="{{ route('admin.chat', ['contact' => $c['id']]) }}" class="p-4 flex items-start gap-3 transition hover:bg-white {{ $c['id'] == $currentContact['id'] ? 'bg-white border-l-4 border-indigo-600 shadow-2xs' : '' }}">
                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-xl shrink-0 shadow-2xs relative">
                        {{ $c['avatar'] }}
                        @if($c['status'] === 'online')
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-xs text-slate-900 truncate">{{ $c['name'] }}</h3>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $c['time'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded {{ $c['role'] === 'Seller' ? 'bg-purple-100 text-purple-700' : ($c['role'] === 'Courier' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $c['role'] }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-[11px] truncate mt-1">{{ $c['last_message'] }}</p>
                    </div>
                </a>
                @endforeach
            </div>

        </div>

        <!-- Right Active Chat Window -->
        <div class="flex-1 flex flex-col bg-white">
            
            <!-- Chat Header -->
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-xl shadow-2xs">
                        {{ $currentContact['avatar'] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-sm text-slate-900">{{ $currentContact['name'] }}</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $currentContact['status'] === 'online' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                &bull; {{ ucfirst($currentContact['status']) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500">Channel: Direct Admin Support & Escalation</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.users') }}" class="text-xs bg-white hover:bg-slate-100 text-slate-700 font-bold px-3 py-1.5 rounded-xl border border-slate-200">
                        View User Profile
                    </a>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 p-6 overflow-y-auto space-y-4 bg-slate-50/30">
                
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold bg-slate-100 text-slate-500 px-3 py-1 rounded-full">
                        Today &bull; Official Platform Support Record
                    </span>
                </div>

                @foreach($messages as $msg)
                @if($msg['sender'] === 'admin')
                    <!-- Admin Message (Right) -->
                    <div class="flex justify-end items-end gap-2">
                        <div class="max-w-md space-y-1">
                            <div class="bg-indigo-600 text-white text-xs p-3.5 rounded-2xl rounded-br-none shadow-sm leading-relaxed">
                                {{ $msg['text'] }}
                            </div>
                            <div class="text-[10px] text-slate-400 text-right">{{ $msg['time'] }} &bull; Platform Admin</div>
                        </div>
                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-[10px] font-black shrink-0">
                            AD
                        </div>
                    </div>
                @else
                    <!-- Contact Message (Left) -->
                    <div class="flex justify-start items-end gap-2">
                        <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-sm shrink-0 shadow-2xs">
                            {{ $currentContact['avatar'] }}
                        </div>
                        <div class="max-w-md space-y-1">
                            <div class="bg-white border border-slate-200 text-slate-900 text-xs p-3.5 rounded-2xl rounded-bl-none shadow-2xs leading-relaxed">
                                {{ $msg['text'] }}
                            </div>
                            <div class="text-[10px] text-slate-400">{{ $msg['time'] }}</div>
                        </div>
                    </div>
                @endif
                @endforeach

            </div>

            <!-- Message Input Form -->
            <div class="p-4 border-t border-slate-200 bg-white">
                
                <!-- Quick Canned Replies -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2 text-[11px]">
                    <span class="text-slate-400 font-bold mr-1">Quick:</span>
                    <button type="button" onclick="setQuickMessage('Your KYC documents have been reviewed and approved.')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg shrink-0">✓ KYC Approved</button>
                    <button type="button" onclick="setQuickMessage('Please re-upload a clear copy of your Mayor Permit 2026.')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg shrink-0">📄 Request Re-upload</button>
                    <button type="button" onclick="setQuickMessage('Your dispute refund has been approved and credited.')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg shrink-0">💸 Refund Issued</button>
                </div>

                <form action="{{ route('admin.chat.send', $currentContact['id']) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" id="chatMessageInput" name="message" required placeholder="Type official response to {{ $currentContact['name'] }}..." class="flex-1 text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-3 rounded-xl shadow-md transition flex items-center gap-1.5">
                        <span>Send</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script src="{{ asset('js/admin/chat.js') }}"></script>
@endpush
@endsection
