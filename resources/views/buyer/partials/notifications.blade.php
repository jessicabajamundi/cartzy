@php
    $notificationIcons = [
        'delivery' => 'M3 6h11v12H3V6Zm11 4h4l3 4v4h-7M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm11 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z',
        'payment' => 'M3 5h18v14H3V5Zm0 5h18M7 15h3',
        'order' => 'm12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5',
        'review' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z',
    ];
    $notificationBell = 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4';
@endphp
<header class="commerce-heading">
    <div><p class="commerce-eyebrow">YOUR UPDATES</p><h2>Notifications</h2><p>Stay in the loop with your orders, deliveries, and account activity.</p></div>
    @if(count($notifications))
        <button type="button" onclick="markAllNotificationsAsRead()" class="notification-read-all"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 4 4 9-9m-4 9 9-9"/></svg>Mark all as read</button>
    @endif
</header>
@if(count($notifications))
    <div class="notification-inbox">
        <div class="notification-toolbar">
            <div class="notification-inbox-title">Your inbox <span>{{ count($notifications) }}</span></div>
            <div class="notification-search">
                <label for="dashNotifSearch" class="sr-only">Search notifications</label>
                <input type="search" id="dashNotifSearch" placeholder="Search notifications" oninput="filterDashNotifications(this.value)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            </div>
        </div>
        <div id="dashNotifList">
            @foreach($notifications as $n)
                <button type="button" data-title="{{ strtolower($n['title']) }}" data-message="{{ strtolower($n['message']) }}" data-notif-id="{{ $n['id'] ?? '' }}"
                        onclick="markNotificationAsRead({{ $n['id'] ?? 'null' }}, this)" class="dash-notif-row notification-row">
                    <span class="notification-type-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $notificationIcons[$n['type']] ?? $notificationBell }}"/></svg></span>
                    <span class="notification-body">
                        <span class="notification-meta"><span>{{ ucfirst($n['type'] ?? 'Account') }} update</span><span>{{ $n['time'] }}</span></span>
                        <span class="notification-title">{{ $n['title'] }}</span>
                        <span class="notification-message">{{ $n['message'] }}</span>
                    </span>
                    @if($n['unread'])<span class="dash-notif-dot notification-unread"><span class="sr-only">Unread</span></span>@endif
                </button>
            @endforeach
            <div id="dashNotifNoMatch" class="hidden notification-no-match" role="status">No notifications matching your search. Try another keyword.</div>
        </div>
        <p class="notification-inbox-footer">Select a notification to mark it as read.</p>
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
