@extends('layouts.logistics')
@section('title', 'Messages | Logistics Hub | cartzy')
@section('page_title', 'Chat / Messaging')
@section('page_subtitle', 'Stay connected with sellers, riders, and your platform team')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-chat.css') }}?v={{ filemtime(public_path('css/logistics-chat.css')) }}">
@endpush
@section('content')
<div class="hub-chat-heading"><div><span class="hub-chat-eyebrow">TEAM COMMUNICATION</span><h2>Good conversations. Smooth deliveries.</h2><p>Keep pickups, deliveries, and updates in one place.</p></div><span class="hub-chat-label">Your hub inbox</span></div>
<section class="hub-chat" aria-label="Messaging workspace">
    <aside class="hub-chat-sidebar" aria-label="Conversations">
        <div class="hub-chat-sidebar-head"><h2>Conversations <span>{{ count($contacts) }}</span></h2><label for="conversation-search" class="hub-chat-sr">Search conversations</label><div class="hub-chat-search"><span aria-hidden="true">⌕</span><input id="conversation-search" type="search" placeholder="Search a name or message…" autocomplete="off"></div><div class="hub-chat-filters" role="group" aria-label="Filter conversations"><button type="button" data-chat-filter="all" aria-pressed="true">All</button><button type="button" data-chat-filter="Rider" aria-pressed="false">Riders</button><button type="button" data-chat-filter="Seller" aria-pressed="false">Sellers</button><button type="button" data-chat-filter="Admin" aria-pressed="false">Admin</button></div></div>
        <div class="hub-chat-contacts">
        @foreach($contacts as $contact)
            <a href="{{ route('logistics.chat', ['contact'=>$contact['id']]) }}" class="hub-chat-contact" data-contact-role="{{ $contact['role'] }}" @if($currentContact['id']==$contact['id']) aria-current="page" @endif>
                <span class="hub-chat-avatar {{ $contact['role'] === 'Seller' ? 'rose' : '' }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($contact['name'], 0, 1)) }}</span>
                <span class="hub-chat-contact-body"><span class="hub-chat-contact-top"><strong>{{ $contact['name'] }}</strong><time>{{ $contact['time'] }}</time></span><span class="hub-chat-preview">{{ $contact['last_message'] }}</span><span class="hub-chat-contact-bottom"><small>{{ $contact['role'] }}</small>@if($contact['unread'] && $currentContact['id'] != $contact['id'])<span class="hub-chat-unread" aria-label="{{ $contact['unread'] }} unread messages">{{ $contact['unread'] }}</span>@endif</span></span>
            </a>
        @endforeach
        <div id="conversation-empty" class="hub-chat-empty" hidden>No conversations found.<br><small>Try another name or filter.</small></div>
        </div>
        <div class="hub-chat-sidebar-foot"><span aria-hidden="true">✦</span> A little update goes a long way.</div>
    </aside>
    <div class="hub-chat-thread">
        <header class="hub-chat-thread-head"><span class="hub-chat-avatar rose" aria-hidden="true">{{ mb_strtoupper(mb_substr($currentContact['name'], 0, 1)) }}</span><div><h2>{{ $currentContact['name'] }}</h2><p>{{ $currentContact['role'] }} <span aria-hidden="true">·</span> Direct conversation</p></div><a class="hub-chat-refresh" href="{{ route('logistics.chat', ['contact' => $currentContact['id']]) }}" aria-label="Refresh conversation">↻ <span>Refresh</span></a></header>
        <div id="chatMessages" class="hub-chat-messages" tabindex="0" aria-label="Message history">
            <div class="hub-chat-history-label"><span>Conversation with {{ $currentContact['name'] }}</span></div>
            @foreach($messages as $msg)
            <div class="hub-chat-message {{ $msg['sender'] === 'hub' ? 'outgoing' : 'incoming' }}"><div class="hub-chat-bubble">{{ $msg['text'] }}</div><div class="hub-chat-message-meta">{{ $msg['sender'] === 'hub' ? 'You' : $currentContact['name'] }} <span aria-hidden="true">·</span> {{ $msg['time'] }}</div></div>
            @endforeach
        </div>
        <div class="hub-chat-compose-area">
            <div class="hub-chat-suggestions" aria-label="Suggested replies"><span>Quick replies</span><button type="button" data-quick-reply="On the way! 🛵">🛵 On the way</button><button type="button" data-quick-reply="Parcel is ready for pickup. 📦">📦 Ready for pickup</button><button type="button" data-quick-reply="Thank you! 🙌">🙌 Thank you</button></div>
            <form method="POST" action="{{ route('logistics.chat.send', ['contactId' => $currentContact['id']], false) }}" id="hub-chat-form">
                @csrf
                <div class="hub-chat-composer"><label for="hub-chat-message" class="hub-chat-sr">Your message</label><textarea id="hub-chat-message" name="message" rows="1" maxlength="5000" required placeholder="Write a message…">{{ old('message') }}</textarea><div class="hub-chat-composer-tools"><div class="hub-chat-emoji-wrap"><button type="button" id="hub-chat-emoji-toggle" class="hub-chat-emoji-toggle" aria-label="Add an emoji" aria-expanded="false" aria-controls="hub-chat-emoji-panel" title="Add an emoji">☺</button><div id="hub-chat-emoji-panel" class="hub-chat-emoji-panel" hidden role="group" aria-label="Choose an emoji"><div class="hub-chat-emoji-heading"><strong>Emojis</strong><button type="button" id="hub-chat-emoji-close" aria-label="Close emoji picker">×</button></div>
<label class="hub-chat-sr" for="hub-emoji-search">Search emojis</label><input id="hub-emoji-search" class="hub-emoji-search" type="search" placeholder="Search emojis" autocomplete="off">
<div class="hub-emoji-categories" role="group" aria-label="Emoji categories">
<button type="button" data-emoji-category="recent" aria-label="Recently used" title="Recently used" aria-pressed="false">◷</button>
<button type="button" data-emoji-category="faces" aria-label="Smileys and people" title="Smileys and people" aria-pressed="true">☺</button>
<button type="button" data-emoji-category="gestures" aria-label="Gestures" title="Gestures" aria-pressed="false">✌</button>
<button type="button" data-emoji-category="hearts" aria-label="Hearts and symbols" title="Hearts and symbols" aria-pressed="false">♡</button>
<button type="button" data-emoji-category="objects" aria-label="Travel and objects" title="Travel and objects" aria-pressed="false">♧</button>
</div><div id="hub-emoji-section" class="hub-emoji-section">Smileys and people</div><div class="hub-chat-emoji-grid" id="hub-emoji-grid"></div><p id="hub-emoji-empty" class="hub-emoji-empty" role="status" hidden>No emojis found.</p></div></div><span class="hub-chat-compose-hint">Shift + Enter for a new line</span><button type="submit" id="hub-chat-send" class="hub-chat-send" aria-label="Send message" title="Send message"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg></button></div></div>
                @error('message')<p class="hub-chat-error" role="alert">{{ $message }}</p>@enderror
                <p id="hub-chat-feedback" class="hub-chat-error" role="status" hidden></p>
            </form>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script src="{{ asset('js/logistics-chat.js') }}?v={{ filemtime(public_path('js/logistics-chat.js')) }}" defer></script>
@endpush
