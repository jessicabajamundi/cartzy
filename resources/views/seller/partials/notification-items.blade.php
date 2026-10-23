@php
    $isSafeLink = fn ($link) => $link && (str_starts_with($link, url('/').'/') || (str_starts_with($link, '/') && !str_starts_with($link, '//')));
    $orderTypes = ['order_update', 'order_cancellation', 'cancellation_approved', 'cancellation_rejected'];
    $dayLabel = function ($date) {
        $date = $date->copy()->timezone('Asia/Manila');
        $today = now('Asia/Manila');
        if ($date->isSameDay($today)) return 'Today';
        if ($date->isSameDay($today->copy()->subDay())) return 'Yesterday';
        return 'Earlier';
    };
    $currentGroup = null;
@endphp

@if($sellerStockNotifications->isNotEmpty())
    <h3 class="sn-group is-alert">Needs restocking</h3>
    @foreach($sellerStockNotifications as $item)
        <article class="sn-item is-stock" data-notification-category="stock">
            <span class="sn-icon is-stock">@include('seller.partials.icon', ['name' => 'box', 'size' => 18])</span>
            <div class="sn-body">
                <p class="sn-title">{{ $item->stock > 0 ? 'Low stock' : 'Out of stock' }}: {{ $item->product->name }}</p>
                <p class="sn-text">{{ $item->name }} — {{ $item->stock > 0 ? 'only '.$item->stock.' left' : 'none left' }}. Action needed</p>
            </div>
            <div class="sn-side">
                <a class="sn-view" href="{{ route('seller.inventory', ['search' => $item->sku]) }}" aria-label="View stock for {{ $item->product->name }} — {{ $item->name }}">View</a>
            </div>
        </article>
    @endforeach
@endif

@foreach($sellerNotifications as $item)
    @php
        $type = $item->type ?? '';
        $kind = $type === 'product_update' ? 'product' : (in_array($type, $orderTypes) ? 'order' : 'general');
        $group = $dayLabel($item->created_at);
        $viewLink = $isSafeLink($item->link) ? $item->link : match ($kind) {
            'product' => route('seller.products'),
            'order' => route('seller.orders'),
            default => route('seller.dashboard').'#needs-attention',
        };
    @endphp
    @if($group !== $currentGroup)
        @php($currentGroup = $group)
        <h3 class="sn-group">{{ $group }}</h3>
    @endif
    <article class="sn-item {{ $item->is_read ? 'is-read' : 'unread' }}" data-notification-category="{{ $kind === 'product' ? 'products' : ($kind === 'order' ? 'orders' : 'general') }}">
        <span class="sn-icon is-{{ $kind }}">@include('seller.partials.icon', ['name' => ['product' => 'tag', 'order' => 'bag', 'general' => 'bell'][$kind], 'size' => 18])</span>
        <div class="sn-body">
            <p class="sn-title">{{ $item->title }}</p>
            <p class="sn-text">{{ $item->message }}</p>
            <time class="sn-time" datetime="{{ $item->created_at->toIso8601String() }}" title="{{ $item->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }} PHT">{{ $item->created_at->diffForHumans() }}</time>
        </div>
        <div class="sn-side">
            @unless($item->is_read)
                <button type="button" class="sn-dot" data-notification-read="{{ route('seller.notifications.read', $item->id, false) }}" title="Mark as read" aria-label="Mark “{{ $item->title }}” as read"></button>
            @endunless
            <a class="sn-view" href="{{ $viewLink }}">View</a>
            <div class="sn-menu">
                <button type="button" class="sn-menu-btn" data-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="More options for “{{ $item->title }}”">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                </button>
                <div class="sn-menu-list" role="menu" hidden>
                    @if($item->archived_at)
                        <button type="button" role="menuitem" data-notification-action="{{ route('seller.notifications.restore', $item->id, false) }}" data-method="POST" data-done="Moved back to notifications">
                            @include('seller.partials.icon', ['name' => 'refresh', 'size' => 18]) Unarchive
                        </button>
                    @else
                        <button type="button" role="menuitem" data-notification-action="{{ route('seller.notifications.archive', $item->id, false) }}" data-method="POST" data-done="Archived"
                            data-confirm="archive" data-confirm-title="Archive this notification?" data-confirm-text="It will be moved out of your notifications and kept in the Archived tab. You can unarchive it anytime." data-confirm-ok="Archive" data-confirm-item="{{ $item->title }}">
                            @include('seller.partials.icon', ['name' => 'archive', 'size' => 18]) Archive
                        </button>
                    @endif
                    <hr>
                    <button type="button" role="menuitem" class="is-danger" data-notification-action="{{ route('seller.notifications.destroy', $item->id, false) }}" data-method="DELETE" data-done="Deleted"
                        data-confirm="delete" data-confirm-title="Delete this notification?" data-confirm-text="This notification will be permanently removed. This can't be undone." data-confirm-ok="Delete" data-confirm-item="{{ $item->title }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18 M8 6V4h8v2 M19 6l-1 14H6L5 6 M10 11v6 M14 11v6"/></svg> Delete
                    </button>
                </div>
            </div>
        </div>
    </article>
@endforeach

@if($sellerStockNotifications->isEmpty() && $sellerNotifications->isEmpty())
    <div class="sn-empty">
        <span class="sn-empty-icon">@include('seller.partials.icon', ['name' => 'bell', 'size' => 24])</span>
        <h3>{{ $notificationFilter === 'all' ? "You're all caught up" : ($notificationFilter === 'archived' ? 'No archived notifications' : 'Nothing here') }}</h3>
        <p>
            @if($notificationFilter === 'all')
                New orders, product changes, and stock alerts will show up here.
            @elseif($notificationFilter === 'archived')
                Notifications you archive will be kept here.
            @else
                No notifications in this tab. <a href="{{ route('seller.notifications') }}">See all notifications</a>
            @endif
        </p>
    </div>
@endif

@if($notificationPaginator->lastPage() > 1)
    <nav class="sn-pager" aria-label="Notification pages">
        <span>Page {{ $notificationPaginator->currentPage() }} of {{ $notificationPaginator->lastPage() }}</span>
        <div>
            @if($notificationPaginator->onFirstPage())<span aria-disabled="true">← Newer</span>@else<a href="{{ $notificationPaginator->previousPageUrl() }}">← Newer</a>@endif
            @if($notificationPaginator->hasMorePages())<a href="{{ $notificationPaginator->nextPageUrl() }}">Older →</a>@else<span aria-disabled="true">Older →</span>@endif
        </div>
    </nav>
@endif
