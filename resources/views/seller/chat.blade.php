@extends('layouts.seller')
@section('page_title', 'Messages')
@section('content')
@php($inboxRoute = $isSeller ? 'seller.chat' : 'buyer.messages')
<div class="page-heading messages-heading">
    <div><div class="eyebrow">{{ $isSeller ? 'CUSTOMER SUPPORT' : 'ORDER SUPPORT' }}</div><h1>Messages</h1><p class="subtitle">{{ $isSeller ? 'Answer product questions, store chats, and order conversations in one place.' : 'Talk to your seller about your order.' }}</p></div>
    <a class="button secondary" href="{{ route($inboxRoute,request()->only('contact','inquiry','search','unread')) }}">@include('seller.partials.icon', ['name' => 'refresh', 'size' => 16]) Refresh inbox</a>
</div>
@if(request()->boolean('unread'))
<div class="notice">Showing conversations with unread messages. Opening a conversation marks its displayed messages as read. <a href="{{ route($inboxRoute) }}">View all conversations</a></div>
@endif
<section class="panel inbox messages-inbox" aria-label="Order messages">
    <aside class="conversation-list" aria-label="Conversations">
    <div class="inbox-search">
        <div class="inbox-list-heading"><h2>Conversations</h2>@if($contacts->total() + $inquiries->total() > 0)<span class="inbox-total">{{ $contacts->total() + $inquiries->total() }}</span>@endif</div>
        <form method="GET" action="{{ route($inboxRoute) }}" class="inbox-search-form">
            @if(request()->boolean('unread'))<input type="hidden" name="unread" value="1">@endif
            @include('seller.partials.icon', ['name' => 'search', 'size' => 18])
            <input aria-label="Search conversations" name="search" value="{{ request('search') }}" placeholder="Search name, product or order…">
            <button type="submit" aria-label="Find conversations" title="Search">@include('seller.partials.icon', ['name' => 'arrow-right', 'size' => 18])</button>
        </form>
    </div>
    @if($inquiries->total() > 0)
        <p class="inbox-section-label">Product &amp; store questions <span>{{ $inquiries->total() }}</span></p>
        @foreach($inquiries as $inquiry)
            @php($inquirySelected = $currentInquiry?->id === $inquiry->id)
            <a href="{{ route($inboxRoute, ['inquiry' => $inquiry->id, 'search' => request('search'), 'unread' => request('unread')]) }}" class="contact {{ $inquirySelected ? 'selected' : '' }}" @if($inquirySelected) aria-current="true" @endif><div class="avatar">{{ mb_substr(($isSeller ? $inquiry->buyer?->name : $inquiry->seller?->name) ?? 'B', 0, 1) }}</div><div class="contact-body"><div class="contact-heading"><strong>{{ $isSeller ? ($inquiry->buyer?->name ?? 'Buyer') : $inquiry->seller?->name }}</strong>@if($inquiry->unread_count)<span class="unread">{{ $inquiry->unread_count }}</span>@endif</div><small>{{ $inquiry->product_id === null ? 'Store chat' : 'About: '.Str::limit($inquiry->product?->name ?? 'Product no longer available', 40) }}</small><p>{{ $inquiry->latestMessage?->body ?? 'No messages yet' }}</p>@if($inquiry->latestMessage)<small>{{ $inquiry->latestMessage->created_at->diffForHumans() }}</small>@endif</div></a>
        @endforeach
        @include('seller.partials.pagination', ['paginator' => $inquiries])
        @if($contacts->total() > 0)<p class="inbox-section-label">Order conversations <span>{{ $contacts->total() }}</span></p>@endif
    @endif
    @forelse($contacts as $contact)
        @php($contactName = $isSeller ? $contact->order->buyer?->name : $contact->seller?->name)
        <a href="{{ route($inboxRoute,['contact'=>$contact->id,'search'=>request('search'),'unread'=>request('unread')]) }}" class="contact {{ $current?->id === $contact->id ? 'selected' : '' }}" @if($current?->id === $contact->id) aria-current="true" @endif><div class="avatar">{{ mb_substr($contactName ?? 'C',0,1) }}</div><div class="contact-body"><div class="contact-heading"><strong>{{ $contactName }}</strong>@if($contact->unread_count)<span class="unread">{{ $contact->unread_count }}</span>@endif</div><small>{{ $contact->order->reference }}</small><p>{{ $contact->latestMessage?->body ?? 'Start a conversation about this order' }}</p>@if($contact->latestMessage)<small>{{ $contact->latestMessage->created_at->diffForHumans() }}</small>@endif</div></a>
    @empty @if($inquiries->total() === 0)<div class="empty inbox-list-empty"><div class="list-empty-icon">@include('seller.partials.icon',['name'=> request('search') ? 'search' : 'chat', 'size'=>24])</div><h3>{{ request('search') ? 'No conversations found' : 'Your inbox is clear' }}</h3><p>{{ request('search') ? 'Try another customer name, product or order number.' : 'Buyer questions and order conversations will appear here.' }}</p>@if(request('search'))<a href="{{ route($inboxRoute) }}">Clear search</a>@endif</div>@endif @endforelse
    @include('seller.partials.pagination',['paginator'=>$contacts])</aside>
    <div class="conversation">
    @if($currentInquiry)
        @php($inquiryName = $isSeller ? ($currentInquiry->buyer?->name ?? 'Buyer') : $currentInquiry->seller?->name)
        <div class="conversation-header"><div class="inline"><div class="avatar">{{ mb_substr($inquiryName ?? 'B', 0, 1) }}</div><div><h2>{{ $inquiryName }}</h2><small>{{ $currentInquiry->product_id === null ? 'Store chat · not linked to an order' : 'Question about '.Str::limit($currentInquiry->product?->name ?? 'a product that is no longer available', 50) }}</small></div></div>
            @if($currentInquiry->product_id === null && $currentInquiry->seller?->isApproved())
                <a class="button secondary" href="{{ route('store.show', $currentInquiry->seller->slug) }}" target="_blank" rel="noopener">View store</a>
            @elseif($currentInquiry->product?->is_active)
                <a class="button secondary" href="{{ route('products.show', $currentInquiry->product_id) }}" target="_blank" rel="noopener">View product</a>
            @else
                <span class="badge">{{ $currentInquiry->product_id === null ? 'Store chat' : 'Pre-purchase' }}</span>
            @endif
        </div>
        <div class="message-list" id="message-list">
            @if($inquiryMessages->hasPages())@include('seller.partials.pagination', ['paginator' => $inquiryMessages])@endif
            @forelse($inquiryMessages->getCollection()->reverse() as $message)<div class="message {{ $message->sender_id === auth()->id() ? 'own' : '' }}"><div class="bubble">{{ $message->body }}</div><small>{{ $message->created_at->timezone('Asia/Manila')->format('M j, g:i A') }} · {{ $message->sender_id === auth()->id() ? 'You' : $message->sender?->name }}@if($message->sender_id === auth()->id()) · <span class="msg-status msg-status-{{ $message->read_at ? 'seen' : ($message->delivered_at ? 'delivered' : 'sent') }}">{{ $message->read_at ? '✓✓ Seen' : ($message->delivered_at ? '✓✓ Delivered' : '✓ Sent') }}</span>@endif</small></div>@empty<div class="chat-empty"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name' => 'chat'])</div><h3>No messages yet</h3><p>Messages from this buyer will appear here.</p></div></div>@endforelse
        </div>
        <form class="composer" method="POST" action="{{ route($isSeller ? 'seller.chat.inquiry.send' : 'buyer.inquiries.send', $currentInquiry->id) }}">@csrf<label for="message">Your message<textarea id="message" name="message" rows="2" placeholder="{{ $currentInquiry->product_id === null ? 'Reply to this buyer…' : 'Answer their question about this product…' }}" maxlength="5000" required>{{ old('message') }}</textarea></label><button class="button" type="submit">Send @include('seller.partials.icon', ['name' => 'send', 'size' => 16])</button></form>
    @elseif($current)
        <div class="conversation-header"><div class="inline"><div class="avatar">{{ mb_substr(($isSeller ? $current->order->buyer?->name : $current->seller?->name) ?? 'C',0,1) }}</div><div><h2>{{ $isSeller ? $current->order->buyer?->name : $current->seller?->name }}</h2><small>{{ $current->order->reference }} · {{ Str::limit($current->items->first()?->product_name,45) }}</small></div></div><span class="badge {{ $current->status }}">{{ ucfirst(str_replace('_',' ',$current->status)) }}</span></div>
        <div class="message-list" id="message-list">
            @if($messages->hasPages())@include('seller.partials.pagination',['paginator'=>$messages])@endif
            @forelse($messages->getCollection()->reverse() as $message)<div class="message {{ $message->sender_id === auth()->id() ? 'own' : '' }}"><div class="bubble">{{ $message->body }}</div><small>{{ $message->created_at->timezone('Asia/Manila')->format('M j, g:i A') }} · {{ $message->sender_id === auth()->id() ? 'You' : $message->sender?->name }}@if($message->sender_id === auth()->id()) · <span class="msg-status msg-status-{{ $message->read_at ? 'seen' : ($message->delivered_at ? 'delivered' : 'sent') }}">{{ $message->read_at ? '✓✓ Seen' : ($message->delivered_at ? '✓✓ Delivered' : '✓ Sent') }}</span>@endif</small></div>@empty<div class="chat-empty"><div class="empty"><div class="empty-icon">@include('seller.partials.icon',['name'=>'chat'])</div><h3>Start the conversation</h3><p>Send a message about this order. Replies will appear in this inbox.</p></div></div>@endforelse
        </div>
        <form class="composer" method="POST" action="{{ route($isSeller ? 'seller.chat.send' : 'buyer.messages.send',$current) }}">@csrf<label for="message">Your message<textarea id="message" name="message" rows="2" placeholder="Write a reply about this order…" maxlength="5000" required>{{ old('message') }}</textarea></label><button class="button" type="submit">Send @include('seller.partials.icon', ['name' => 'send', 'size' => 16])</button></form>
    @else
        <div class="conversation-placeholder-header">@include('seller.partials.icon', ['name' => 'chat', 'size' => 18])<span>Conversations</span></div>
        <div class="chat-empty inbox-welcome"><div class="empty">
            <div class="empty-icon">@include('seller.partials.icon',['name'=>'chat', 'size'=>32])</div>
            <div class="eyebrow">EVERY ORDER, ONE CONVERSATION</div>
            <h3>Your conversations start here</h3>
            <p>{{ $contacts->total() + $inquiries->total() > 0 ? 'Select a conversation to answer product questions, reply to store chats, and keep customers updated on their orders.' : 'Buyer questions and order conversations will appear here.' }}</p>
            <div class="inbox-welcome-note">@include('seller.partials.icon', ['name' => 'bag', 'size' => 16])<span>Messages stay linked to their order</span></div>
        </div></div>
    @endif
    </div>
</section>
@endsection
@push('scripts')
<script>
(() => {
    const messageList = document.getElementById('message-list');
    if (messageList) messageList.scrollTop = messageList.scrollHeight;
    const composer = document.querySelector('form.composer');
    const message = composer?.querySelector('textarea[name="message"]');
    if (!message) return;
    let sending = false;
    message.addEventListener('keydown', event => {
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.keyCode === 229) return;
        event.preventDefault();
        if (event.repeat || sending || !message.value.trim()) return;
        composer.requestSubmit();
    });
    composer.addEventListener('submit', event => {
        if (sending || !message.value.trim()) {
            event.preventDefault();
            return;
        }
        sending = true;
        composer.querySelector('button[type="submit"]').disabled = true;
    });
    window.addEventListener('pageshow', () => {
        sending = false;
        composer.querySelector('button[type="submit"]').disabled = false;
    });
})();
</script>
@endpush
