@extends('layouts.seller')
@section('page_title', 'Logistics chat')
@section('content')
<div class="page-heading messages-heading">
    <div><div class="eyebrow">PICKUPS &amp; DELIVERIES</div><h1>Logistics chat</h1><p class="subtitle">Message the logistics hubs about pickup schedules, parcels, and delivery issues.</p></div>
    <a class="button secondary" href="{{ route('seller.logistics', ['hub' => $current['id'] ?? null]) }}">@include('seller.partials.icon', ['name' => 'refresh', 'size' => 16]) Refresh</a>
</div>
<section class="panel inbox messages-inbox" aria-label="Logistics conversations">
    <aside class="conversation-list" aria-label="Logistics hubs">
        <div class="inbox-search"><div class="inbox-list-heading"><h2>Logistics hubs</h2>@if($hubs->isNotEmpty())<span class="inbox-total">{{ $hubs->count() }}</span>@endif</div></div>
        @forelse($hubs as $hub)
            @php($selected = $current && $current['id'] === $hub['id'])
            <a href="{{ route('seller.logistics', ['hub' => $hub['id']]) }}" class="contact {{ $selected ? 'selected' : '' }}" @if($selected) aria-current="true" @endif><div class="avatar">{{ mb_strtoupper(mb_substr($hub['name'], 0, 1)) }}</div><div class="contact-body"><div class="contact-heading"><strong>{{ $hub['name'] }}</strong>@if($hub['unread'] && !$selected)<span class="unread">{{ $hub['unread'] }}</span>@endif</div><small>{{ $hub['subtitle'] }}</small><p>{{ $hub['preview'] }}</p>@if($hub['time'])<small>{{ $hub['time'] }} ago</small>@endif</div></a>
        @empty
            <div class="empty inbox-list-empty"><div class="list-empty-icon">@include('seller.partials.icon', ['name' => 'truck', 'size' => 24])</div><h3>No logistics hubs yet</h3><p>Approved logistics hubs will appear here once they join the platform.</p></div>
        @endforelse
    </aside>
    <div class="conversation">
    @if($current)
        <div class="conversation-header"><div class="inline"><div class="avatar">{{ mb_strtoupper(mb_substr($current['name'], 0, 1)) }}</div><div><h2>{{ $current['name'] }}</h2><small>{{ $current['subtitle'] }}@if($current['provider']->contact_phone) · {{ $current['provider']->contact_phone }}@endif</small></div></div><a class="button secondary" href="{{ route('seller.courier') }}">Shipping</a></div>
        <div class="message-list" id="message-list">
            @forelse($messages as $message)<div class="message {{ $message['mine'] ? 'own' : '' }}"><div class="bubble">{{ $message['body'] }}</div><small>{{ $message['time'] }} · {{ $message['mine'] ? 'You' : $current['name'] }}@if($message['mine']) · <span class="msg-status msg-status-{{ $message['status'] }}">{{ $message['status'] === 'seen' ? '✓✓ Seen' : '✓ Sent' }}</span>@endif</small></div>@empty<div class="chat-empty"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name' => 'truck'])</div><h3>Start the conversation</h3><p>Ask {{ $current['name'] }} about pickups, schedules, or parcels. Replies appear here and in the chat bubble.</p></div></div>@endforelse
        </div>
        <form class="composer" method="POST" action="{{ route('seller.logistics.send', $current['id']) }}">@csrf<label for="message">Your message<textarea id="message" name="message" rows="2" placeholder="Write to {{ $current['name'] }}…" maxlength="5000" required>{{ old('message') }}</textarea></label><button class="button" type="submit">Send @include('seller.partials.icon', ['name' => 'send', 'size' => 16])</button></form>
    @else
        <div class="chat-empty inbox-welcome"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name' => 'truck', 'size' => 32])</div><h3>No hubs to message yet</h3><p>Once a logistics hub is approved you can chat with them here.</p></div></div>
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
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
        event.preventDefault();
        if (!sending && message.value.trim()) composer.requestSubmit();
    });
    composer.addEventListener('submit', event => {
        if (sending || !message.value.trim()) { event.preventDefault(); return; }
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
