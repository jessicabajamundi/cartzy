@extends('layouts.logistics')
@section('title', 'Pickup Requests | Logistics Hub | cartzy')
@section('page_title', 'Parcel Pickup Requests')
@section('page_subtitle', 'Confirm and verify seller parcel pickup schedules')
@section('content')
<div class="pickup-workspace">
    <header class="pickup-heading"><div><p class="pickup-kicker">PARCEL OPERATIONS</p><h2>Pickup requests</h2><p>Review the details. Confirm the schedule. Keep parcels moving.</p></div><span class="pickup-heading-icon">@include('seller.partials.icon', ['name' => 'truck', 'size' => 28])</span></header>
    <div class="pickup-overview" aria-label="Pickup totals">
        @foreach(['pending' => ['Awaiting confirmation', 'Your next action', 'box'], 'confirmed' => ['Confirmed pickups', 'Schedules confirmed', 'check'], 'all' => ['Total requests', 'Across your hub', 'truck']] as $key => [$label, $hint, $icon])
            <a href="{{ route('logistics.pickups', ['status' => $key]) }}" class="pickup-metric {{ $key === 'pending' ? 'is-rose' : '' }}"><div><span>{{ $label }}</span><strong>{{ $counts[$key] }}</strong><small>{{ $hint }}</small></div><span class="pickup-metric-icon">@include('seller.partials.icon', ['name' => $icon, 'size' => 21])</span></a>
        @endforeach
    </div>
    <section aria-label="Pickup request list">
        <div class="pickup-toolbar">
            <nav class="pickup-tabs" aria-label="Pickup status filters">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed'] as $key => $label)
                    <a href="{{ route('logistics.pickups', ['status' => $key, 'q' => $search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('logistics.pickups') }}" class="pickup-search" role="search">
                <input type="hidden" name="status" value="{{ $filter }}">
                <label for="pickup-search" class="sr-only">Search seller, email or address</label>
                <span aria-hidden="true">@include('seller.partials.icon', ['name' => 'search', 'size' => 17])</span>
                <input id="pickup-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Seller, email or address...">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="pickup-results"><span>{{ $pickups->total() }} {{ \Illuminate\Support\Str::plural('request', $pickups->total()) }}@if($search !== '') matching &ldquo;{{ $search }}&rdquo;@endif</span><span>Pending first &middot; Earliest date first</span>@if($search !== '')<a href="{{ route('logistics.pickups', ['status' => $filter]) }}">Clear search</a>@endif</div>
        <div class="pickup-list">
        @forelse($pickups as $pickup)
            <article class="pickup-card {{ $pickup['status'] === 'pending' ? 'is-pending' : '' }}">
                <div class="pickup-card-heading">
                    <div class="pickup-seller"><span class="pickup-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pickup['seller_name'], 0, 1)) }}</span><div><span class="pickup-reference">REQUEST #{{ str_pad($pickup['id'], 4, '0', STR_PAD_LEFT) }}</span><h3>{{ $pickup['seller_name'] }}</h3></div></div>
                    <span class="pickup-badge">{{ ucfirst($pickup['status']) }}</span>
                </div>
                <div class="pickup-card-body">
                    <dl class="pickup-details">
                        <div><dt>Pickup schedule</dt><dd>{{ $pickup['pickup_date'] }}<span>{{ $pickup['pickup_time'] }}</span></dd></div>
                        <div><dt>Parcels &amp; weight</dt><dd>{{ $pickup['parcel_count'] }} parcels<span>{{ $pickup['weight_kg'] }} kg total</span></dd></div>
                        <div class="pickup-address"><dt>Pickup address</dt><dd>{{ $pickup['address'] ?: 'No address provided' }}</dd></div>
                    </dl>
                    <div class="pickup-action">
                        @if($pickup['status'] === 'pending')
                            <form method="POST" action="{{ route('logistics.pickups.confirm', $pickup['id']) }}" data-pickup-confirm>@csrf<button type="submit">@include('seller.partials.icon', ['name' => 'check', 'size' => 17]) <span>Confirm pickup</span></button></form>
                        @else
                            <span class="pickup-completed">@include('seller.partials.icon', ['name' => 'check', 'size' => 18]) {{ ucfirst($pickup['status']) }}</span>
                        @endif
                    </div>
                </div>
                @if($pickup['notes'])<div class="pickup-note">@include('seller.partials.icon', ['name' => 'chat', 'size' => 16])<p><strong>Seller note:</strong> {{ $pickup['notes'] }}</p></div>@endif
                <footer class="pickup-card-footer"><span>Seller email: {{ $pickup['seller_email'] }}</span>@if($pickup['confirmed_at'])<span>Confirmed: {{ $pickup['confirmed_at'] }}</span>@endif</footer>
            </article>
        @empty
            <div class="pickup-empty"><span>@include('seller.partials.icon', ['name' => 'box', 'size' => 32])</span><h3>No pickup requests found.</h3><p>{{ $search !== '' ? 'Try another seller name, email or pickup address.' : 'New requests from sellers will appear here.' }}</p>@if($search !== '' || $filter !== 'all')<a href="{{ route('logistics.pickups') }}">View all pickup requests &rarr;</a>@endif</div>
        @endforelse
        </div>
    </section>
    @if($pickups->hasPages())<div class="pickup-pagination">{{ $pickups->links() }}</div>@endif
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-pickups.css') }}?v={{ filemtime(public_path('css/logistics-pickups.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/logistics-pickups.js') }}?v={{ filemtime(public_path('js/logistics-pickups.js')) }}"></script>
@endpush
