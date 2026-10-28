{{-- Floating seller chat (Messages or Logistics). Hidden until opened from its sidebar link; − shrinks it to the bubble, × closes it.
     Params: $id, $label, $title, $subtitle, $conversationsUrl, $inboxUrl, $emptyText. --}}
<div class="seller-chat-bubble" id="{{ $id }}-bubble-wrap" hidden>
    <a class="chat-bubble" id="{{ $id }}-bubble" href="{{ $inboxUrl }}" aria-controls="{{ $id }}" aria-expanded="false" aria-label="Open {{ $label }} chat">@include('seller.partials.icon', ['name' => 'chat-round', 'size' => 22])<span>{{ $label }}</span><b class="seller-chat-badge" id="{{ $id }}-badge" hidden></b></a>
</div>
<section class="pd-chat cw seller-chat-popup" id="{{ $id }}" role="dialog" aria-labelledby="{{ $id }}-title" data-conversations-url="{{ $conversationsUrl }}" data-inbox-url="{{ $inboxUrl }}" data-title="{{ $title }}" data-subtitle="{{ $subtitle }}" data-empty-text="{{ $emptyText }}" hidden>
    <header class="pd-chat-head">
        <button class="pd-chat-icon" type="button" id="{{ $id }}-back" aria-label="Back to conversations" title="Back" hidden>&larr;</button>
        <div><strong id="{{ $id }}-title">{{ $title }}</strong><small id="{{ $id }}-subtitle">{{ $subtitle }}</small></div>
        <a class="pd-chat-icon" id="{{ $id }}-expand" href="{{ $inboxUrl }}" aria-label="Open the full {{ $label }} page" title="Open full page">&#x2197;</a>
        <button class="pd-chat-icon pd-chat-minimize" type="button" id="{{ $id }}-minimize" aria-label="Minimize to bubble" title="Minimize">&minus;</button>
        <button class="pd-chat-icon" type="button" id="{{ $id }}-close" aria-label="Close chat" title="Close">&times;</button>
    </header>
    <div class="cw-list" id="{{ $id }}-list" aria-live="polite"></div>
    <div class="pd-chat-messages" id="{{ $id }}-messages" aria-live="polite" hidden></div>
    <form class="pd-chat-compose" id="{{ $id }}-compose" hidden>
        <label class="pd-chat-sr" for="{{ $id }}-input">Message</label>
        <textarea id="{{ $id }}-input" name="message" rows="1" maxlength="5000" placeholder="Write a message…" required></textarea>
        <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
    </form>
    <p class="pd-chat-error" id="{{ $id }}-error" role="alert" hidden></p>
</section>
