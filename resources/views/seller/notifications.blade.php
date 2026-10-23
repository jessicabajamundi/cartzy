@extends('layouts.seller')
@section('page_title', 'Notifications')
@section('content')
<link rel="stylesheet" href="{{ asset('css/seller-notifications.css') }}?v={{ filemtime(public_path('css/seller-notifications.css')) }}">
<div class="sn-page">
    <header class="sn-header">
        <div>
            <div class="eyebrow">ACTIVITY & ALERTS</div>
            <h1>Notifications</h1>
            <p>Stay on top of orders, products, and stock.</p>
        </div>
        <form action="{{ route('seller.notifications.markAllRead') }}" method="POST" data-mark-all-wrapper @if(!$notificationUnreadCount) hidden @endif>
            @csrf
            <button type="submit" class="sn-mark-all" id="mark-all-read-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/></svg>
                Mark all as read
            </button>
        </form>
    </header>

    <div class="sn-toolbar">
        <nav class="sn-tabs" aria-label="Filter notifications">
            @foreach(['all' => 'All', 'unread' => 'Unread', 'stock' => 'Low stock', 'orders' => 'Orders', 'products' => 'Products', 'archived' => 'Archived'] as $key => $label)
                <a href="{{ route('seller.notifications', ['filter' => $key]) }}" data-notification-filter="{{ $key }}" @if($notificationFilter === $key) aria-current="page" @endif>
                    {{ $label }}<span class="sn-count" data-filter-count="{{ $key }}">{{ $notificationFilterCounts[$key] ?: '' }}</span>
                </a>
            @endforeach
        </nav>
        <span class="sn-status" data-notification-status role="status">Updates automatically</span>
    </div>

    <section class="sn-card" aria-label="Seller notifications">
        <div data-notification-items>@include('seller.partials.notification-items')</div>
    </section>
</div>

{{-- One confirm modal for Archive and Delete; seller-notifications.js fills it from the clicked menu item. --}}
<dialog class="sn-confirm" id="sn-confirm" aria-labelledby="sn-confirm-title" aria-describedby="sn-confirm-text">
    <div class="sn-confirm-panel">
        <span class="sn-confirm-icon" data-confirm-icon aria-hidden="true">
            <svg data-icon="delete" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
            <svg data-icon="archive" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h18v4H3zM5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8M10 12h4"/></svg>
        </span>
        <h2 id="sn-confirm-title" data-confirm-title></h2>
        <p id="sn-confirm-text" data-confirm-text></p>
        <blockquote class="sn-confirm-preview" data-confirm-preview></blockquote>
        <div class="sn-confirm-actions">
            <button type="button" class="button secondary" data-confirm-cancel>Cancel</button>
            <button type="button" class="button" data-confirm-ok></button>
        </div>
    </div>
</dialog>
@endsection
