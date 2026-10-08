@extends('layouts.seller')
@section('page_title', 'Messages')
@section('content')
@php($inboxRoute = $isSeller ? 'seller.chat' : 'buyer.messages')
<div class="page-heading"><div><div class="eyebrow">{{ $isSeller ? 'CUSTOMER CONVERSATIONS' : 'YOUR CONVERSATIONS' }}</div><h1>Messages</h1><p class="subtitle">{{ $isSeller ? 'A dedicated conversation for every customer order.' : 'Talk to your seller about your order.' }}</p></div><a class="button secondary" href="{{ route($inboxRoute,request()->only('contact','search')) }}">↻ Refresh inbox</a></div>
<section class="panel inbox">
    <aside class="conversation-list" aria-label="Conversations"><div class="inbox-search"><h2>Conversations <span class="muted">({{ $contacts->total() }})</span></h2><form method="GET" action="{{ route($inboxRoute) }}" class="inline"><input aria-label="Search conversations" name="search" value="{{ request('search') }}" placeholder="Search name or order…"><button type="submit" class="button small secondary">Find</button></form></div>
    @forelse($contacts as $contact)
        @php($contactName = $isSeller ? $contact->order->buyer?->name : $contact->seller?->name)
        <a href="{{ route($inboxRoute,['contact'=>$contact->id,'search'=>request('search')]) }}" class="contact {{ $current?->id === $contact->id ? 'selected' : '' }}" @if($current?->id === $contact->id) aria-current="true" @endif><div class="avatar">{{ mb_substr($contactName ?? 'C',0,1) }}</div><div class="contact-body"><div class="contact-heading"><strong>{{ $contactName }}</strong>@if($contact->unread_count)<span class="unread">{{ $contact->unread_count }}</span>@endif</div><small>{{ $contact->order->reference }}</small><p>{{ $contact->latestMessage?->body ?? 'Start a conversation about this order' }}</p>@if($contact->latestMessage)<small>{{ $contact->latestMessage->created_at->diffForHumans() }}</small>@endif</div></a>
    @empty<div class="empty"><h3>No conversations</h3><p>{{ request('search') ? 'Try another customer name or order number.' : 'Conversations become available when an order is placed.' }}</p>@if(request('search'))<a href="{{ route($inboxRoute) }}">Clear search</a>@endif</div>@endforelse
    @include('seller.partials.pagination',['paginator'=>$contacts])</aside>
    <div class="conversation">
    @if($current)
        <div class="conversation-header"><div class="inline"><div class="avatar">{{ mb_substr(($isSeller ? $current->order->buyer?->name : $current->seller?->name) ?? 'C',0,1) }}</div><div><h2>{{ $isSeller ? $current->order->buyer?->name : $current->seller?->name }}</h2><small>{{ $current->order->reference }} · {{ Str::limit($current->items->first()?->product_name,45) }}</small></div></div><span class="badge {{ $current->status }}">{{ ucfirst(str_replace('_',' ',$current->status)) }}</span></div>
        <div class="message-list" id="message-list">
            @if($messages->hasPages())@include('seller.partials.pagination',['paginator'=>$messages])@endif
            @forelse($messages->getCollection()->reverse() as $message)<div class="message {{ $message->sender_id === auth()->id() ? 'own' : '' }}"><div class="bubble">{{ $message->body }}</div><small>{{ $message->created_at->timezone('Asia/Manila')->format('M j, g:i A') }} · {{ $message->sender_id === auth()->id() ? 'You' : $message->sender?->name }}</small></div>@empty<div class="chat-empty"><div class="empty"><div class="empty-icon">@include('seller.partials.icon',['name'=>'chat'])</div><h3>Start the conversation</h3><p>Send a message about this order. Replies will appear in this inbox.</p></div></div>@endforelse
        </div>
        <form class="composer" method="POST" action="{{ route($isSeller ? 'seller.chat.send' : 'buyer.messages.send',$current) }}">@csrf<label for="message">Your message<textarea id="message" name="message" rows="1" placeholder="Write your message…" maxlength="5000" required>{{ old('message') }}</textarea></label><button class="button" type="submit">Send →</button></form>
    @else<div class="chat-empty"><div class="empty"><div class="empty-icon">@include('seller.partials.icon',['name'=>'chat'])</div><h3>Your conversations start here</h3><p>Choose an order conversation to read messages and send a reply.</p></div></div>@endif
    </div>
</section>
@endsection
@push('scripts')<script>const messageList = document.getElementById('message-list'); if (messageList) messageList.scrollTop = messageList.scrollHeight;</script>@endpush
