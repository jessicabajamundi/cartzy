{{-- Header chat popup: single click on the header Messages icon opens it, double click opens the Messages page. --}}
<section class="pd-chat cw" id="cw" role="dialog" aria-labelledby="cw-title" data-conversations-url="{{ route('buyer.popup.conversations') }}" data-inbox-url="{{ route('buyer.dashboard', ['tab' => 'messages']) }}" data-typing-url="{{ route('chat.typing') }}" hidden>
    <header class="pd-chat-head">
        <button class="pd-chat-icon" type="button" id="cw-back" aria-label="Back to conversations" title="Back" hidden>&larr;</button>
        <div><strong id="cw-title">Messages</strong><small id="cw-subtitle">Your recent conversations</small></div>
        <a class="pd-chat-icon" id="cw-expand" href="{{ route('buyer.dashboard', ['tab' => 'messages']) }}" aria-label="Open in Messages" title="Open in Messages">&#x2197;</a>
        <button class="pd-chat-icon pd-chat-minimize" type="button" id="cw-minimize" aria-label="Minimize chat" title="Minimize">&minus;</button>
        <button class="pd-chat-icon" type="button" id="cw-close" aria-label="Close chat" title="Close">&times;</button>
    </header>
    <div class="cw-list" id="cw-list" aria-live="polite"></div>
    <div class="pd-chat-messages" id="cw-messages" aria-live="polite" hidden></div>
    <form class="pd-chat-compose" id="cw-compose" hidden>
        <label class="pd-chat-sr" for="cw-input">Message</label>
        <textarea id="cw-input" name="message" rows="1" maxlength="5000" placeholder="Write a message…" required></textarea>
        <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
    </form>
    <p class="pd-chat-error" id="cw-error" role="alert" hidden></p>
</section>
