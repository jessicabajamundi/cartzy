@forelse($sellerStockNotifications as $item)
    <a class="seller-notification unread" href="{{ route('seller.inventory', ['search' => $item->sku]) }}">
        <strong>{{ $item->stock > 0 ? 'Low stock' : 'Out of stock' }}</strong>
        <span>{{ $item->product->name }} — {{ $item->name }}: {{ $item->stock }} left.</span>
        <small>Needs restocking</small>
    </a>
@empty
@endforelse
@foreach($sellerNotifications as $item)
    <article class="seller-notification {{ $item->is_read ? '' : 'unread' }}">
        <strong>{{ $item->title }}</strong>
        <span>{{ $item->message }}</span>
        <small>{{ $item->created_at->diffForHumans() }}</small>
        <div class="inline">
            @if($item->link && (str_starts_with($item->link, url('/').'/') || (str_starts_with($item->link, '/') && !str_starts_with($item->link, '//'))))
                <a href="{{ $item->link }}">View details &rarr;</a>
            @endif
            @unless($item->is_read)<button type="button" class="button small secondary" data-notification-read="{{ route('seller.notifications.read', $item->id, false) }}">Mark as read</button>@endunless
        </div>
    </article>
@endforeach
@if($sellerStockNotifications->isEmpty() && $sellerNotifications->isEmpty())
    <p class="seller-notification">No notifications yet.</p>
@endif
<p class="seller-notification muted">Showing recent updates. Low-stock alerts stay until restocked. <a href="{{ route('seller.inventory') }}">View inventory</a></p>
