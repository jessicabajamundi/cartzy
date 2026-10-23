@php
    $notificationIcons = [
        'delivery' => 'M3 6h11v12H3V6Zm11 4h4l3 4v4h-7M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm11 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z',
        'payment' => 'M3 5h18v14H3V5Zm0 5h18M7 15h3',
        'order' => 'm12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5',
        'review' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z',
    ];
    $notificationBell = 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4';
    $archivedNotifications ??= [];
    $showingArchive = ($notificationBox ?? 'inbox') === 'archived';
    $visibleNotifications = $showingArchive ? $archivedNotifications : $notifications;
@endphp
<header class="commerce-heading">
    <div><p class="commerce-eyebrow">YOUR UPDATES</p><h2>Notifications</h2><p>Stay in the loop with your orders, deliveries, and account activity.</p></div>
    @if(count($notifications) && !$showingArchive)
        <button type="button" onclick="markAllNotificationsAsRead()" class="notification-read-all"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 4 4 9-9m-4 9 9-9"/></svg>Mark all as read</button>
    @endif
</header>
@if(count($notifications) || count($archivedNotifications))
    <div class="notification-inbox">
        <div class="notification-toolbar">
            <nav class="notification-boxes" aria-label="Notification folders">
                <a href="{{ route('buyer.dashboard', ['tab' => 'notifications']) }}" @if(!$showingArchive) aria-current="page" @endif>Inbox <span>{{ count($notifications) }}</span></a>
                <a href="{{ route('buyer.dashboard', ['tab' => 'notifications', 'box' => 'archived']) }}" @if($showingArchive) aria-current="page" @endif>Archived <span>{{ count($archivedNotifications) }}</span></a>
            </nav>
            <div class="notification-search">
                <label for="dashNotifSearch" class="sr-only">Search notifications</label>
                <input type="search" id="dashNotifSearch" placeholder="Search notifications" oninput="filterDashNotifications(this.value)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            </div>
        </div>
        <div id="dashNotifList">
            @forelse($visibleNotifications as $n)
                <div class="dash-notif-row notification-item" data-title="{{ strtolower($n['title']) }}" data-message="{{ strtolower($n['message']) }}">
                <button type="button" data-notif-id="{{ $n['id'] ?? '' }}"
                        onclick="markNotificationAsRead({{ $n['id'] ?? 'null' }}, this)" class="notification-row">
                    <span class="notification-type-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $notificationIcons[$n['type']] ?? $notificationBell }}"/></svg></span>
                    <span class="notification-body">
                        <span class="notification-meta"><span>{{ ucfirst($n['type'] ?? 'Account') }} update</span><span>{{ $n['time'] }}</span></span>
                        <span class="notification-title">{{ $n['title'] }}</span>
                        <span class="notification-message">{{ $n['message'] }}</span>
                    </span>
                    @if($n['unread'])<span class="dash-notif-dot notification-unread"><span class="sr-only">Unread</span></span>@endif
                </button>
                @if(!empty($n['id']))
                    <form method="POST" action="{{ route($showingArchive ? 'notifications.restore' : 'notifications.archive', $n['id']) }}" class="notification-archive-form">
                        @csrf
                        <button type="submit" class="notification-archive" aria-label="{{ $showingArchive ? 'Move back to inbox' : 'Archive' }}: {{ $n['title'] }}" title="{{ $showingArchive ? 'Move back to inbox' : 'Archive' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $showingArchive ? 'M3 7v6h6 M3 13a9 9 0 1 0 3-7.7L3 7' : 'M3 4h18v4H3z M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8 M10 12h4' }}"/></svg>
                            <span>{{ $showingArchive ? 'Restore' : 'Archive' }}</span>
                        </button>
                    </form>
                @endif
                </div>
            @empty
                <p class="notification-no-match">{{ $showingArchive ? 'No archived notifications. Notifications you archive will be kept here.' : 'Your inbox is clear. Archived notifications are still in the Archived folder.' }}</p>
            @endforelse
            <div id="dashNotifNoMatch" class="hidden notification-no-match" role="status">No notifications matching your search. Try another keyword.</div>
        </div>
        <p class="notification-inbox-footer">{{ $showingArchive ? 'Restore a notification to move it back to your inbox.' : 'Select a notification to mark it as read. Archive the ones you no longer need.' }}</p>
    </div>
@else
    @include('buyer.partials.commerce-empty', [
        'emptyPath' => $notificationBell,
        'eyebrow' => 'ALL QUIET FOR NOW',
        'title' => 'No notifications yet',
        'text' => 'Order updates, delivery progress, and account activity will appear here. We will keep you posted.',
        'cta' => 'View my orders',
        'href' => route('buyer.dashboard', ['tab' => 'orders']),
        'note' => 'Your next update will be waiting right here.'
    ])
@endif
