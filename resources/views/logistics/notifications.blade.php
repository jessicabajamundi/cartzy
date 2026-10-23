@extends('layouts.logistics')
@section('title', 'Notifications | Logistics Hub | cartzy')
@section('page_title', 'Notifications')
@section('page_subtitle', 'Announcements and updates for your logistics account')
@section('content')
<div class="hub-notifications">
    <header class="notice-heading">
        <div><p class="notice-eyebrow">YOUR LOGISTICS HUB</p><h2>Notifications</h2><p class="notice-subtitle">A little update. Everything you need to know.</p></div>
        <form method="POST" action="{{ route('logistics.notifications.readAll') }}">
            @csrf
            <button type="submit" class="notice-button" @disabled($unreadCount === 0)>@include('seller.partials.icon', ['name' => 'check', 'size' => 17]) Mark all as read</button>
        </form>
    </header>
    <section class="notice-summary" aria-label="Notification summary">
        <span class="notice-summary-icon">@include('seller.partials.icon', ['name' => 'bell', 'size' => 24])</span>
        <div><h3>{{ $unreadCount ? 'You have new updates' : 'You’re all caught up' }}</h3><p>{{ $unreadCount ? 'Your latest hub updates are ready to read.' : 'We’ll keep your hub updates together, right here.' }}</p></div>
        <span class="notice-summary-count"><strong>{{ $unreadCount }}</strong> unread</span>
    </section>
    <section class="notice-inbox" aria-label="Your notifications">
    <div class="notice-toolbar">
        <nav class="notice-tabs" aria-label="Notification filters">
            <a href="{{ route('logistics.notifications') }}" @if($filter === 'all') aria-current="page" @endif>All <span>{{ $totalCount }}</span></a>
            <a href="{{ route('logistics.notifications', ['filter' => 'unread']) }}" @if($unreadOnly) aria-current="page" @endif>Unread <span>{{ $unreadCount }}</span></a>
            <a href="{{ route('logistics.notifications', ['filter' => 'archived']) }}" @if($filter === 'archived') aria-current="page" @endif>Archived <span>{{ $archivedCount }}</span></a>
        </nav>
        <span class="notice-sort">Newest first</span>
    </div>
    <div class="notice-list">
        @forelse($notifications as $notification)
            <article class="notice-item {{ $notification->is_read ? 'is-read' : 'is-unread' }}">
                <span class="notice-item-icon">@include('seller.partials.icon', ['name' => match($notification->type) { 'delivery', 'pickup' => 'truck', 'admin_message' => 'chat', 'rider' => 'user', default => 'bell' }, 'size' => 20])</span>
                <div class="notice-item-body">
                <div class="notice-item-heading">
                    <h3>{{ $notification->title }}</h3>
                    <span class="notice-read-state">{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
                </div>
                <p class="notice-message">{{ $notification->message }}</p>
                <div class="notice-item-footer">
                    <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}">{{ $notification->created_at->diffForHumans() }}</time>
                    <div class="notice-actions">
                        @php
                            $link = $notification->link ?? '';
                            $safeLink = !str_contains($link, '\\') && (str_starts_with($link, '/logistics/') || str_starts_with($link, url('/logistics') . '/'));
                        @endphp
                        @if($safeLink)<a href="{{ $link }}" class="underline">View details</a>@endif
                        @unless($notification->is_read)
                            <form method="POST" action="{{ route('logistics.notifications.read', $notification->id) }}">@csrf<button type="submit" class="underline">Mark as read</button></form>
                        @endunless
                        @if($notification->archived_at)
                            <form method="POST" action="{{ route('logistics.notifications.restore', $notification->id) }}">@csrf<button type="submit" class="underline" aria-label="Move back to inbox: {{ $notification->title }}">Restore</button></form>
                        @else
                            <form method="POST" action="{{ route('logistics.notifications.archive', $notification->id) }}">@csrf<button type="submit" class="underline" aria-label="Archive: {{ $notification->title }}">Archive</button></form>
                        @endif
                    </div>
                </div>
                </div>
            </article>
        @empty
            <div class="notice-empty">
                <div class="notice-empty-art" aria-hidden="true"><span class="notice-empty-ring"></span><span class="notice-empty-bell">@include('seller.partials.icon', ['name' => 'bell', 'size' => 38])</span><span class="notice-empty-check">@include('seller.partials.icon', ['name' => 'check', 'size' => 19])</span></div>
                <p class="notice-eyebrow">{{ $filter === 'archived' ? 'NOTHING ARCHIVED' : ($unreadOnly ? 'ALL UP TO DATE' : 'A QUIET MOMENT') }}</p>
                <h3>{{ $filter === 'archived' ? 'No archived notifications' : ($unreadOnly ? 'You’re all caught up' : 'No notifications yet') }}</h3>
                <p class="notice-empty-copy">{{ $filter === 'archived' ? 'Notifications you archive are kept here, out of your inbox.' : ($unreadOnly ? 'There are no unread notifications.' : 'Updates and announcements for your account will appear here.') }}</p>
                <a class="notice-empty-link" href="{{ $filter !== 'all' ? route('logistics.notifications') : route('logistics.dashboard') }}">{{ $filter !== 'all' ? 'View all notifications' : 'Back to dashboard' }} @include('seller.partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
            </div>
        @endforelse
    </div>
    </section>
    @if($notifications->hasPages())<div class="notice-pagination">{{ $notifications->links() }}</div>@endif
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-notifications.css') }}?v={{ filemtime(public_path('css/logistics-notifications.css')) }}">
@endpush
