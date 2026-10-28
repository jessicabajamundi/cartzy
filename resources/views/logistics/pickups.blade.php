@extends('layouts.logistics')
@section('title', 'Pickup Requests | Logistics Hub | cartzy')
@section('page_title', 'Parcel Pickup Requests')
@section('page_subtitle', 'Confirm schedules and request nearby riders for merchant collection')

@section('content')
<div class="pickup-workspace">
    <header class="pickup-heading">
        <div>
            <p class="pickup-kicker">PARCEL DISPATCH &amp; OPERATIONS</p>
            <h2>Pickup requests &amp; rider dispatch</h2>
            <p>Smart area matching automatically suggests the closest available rider for each seller pickup.</p>
        </div>
        @if($counts['dispatchable'] > 0)
            <form method="POST" action="{{ route('logistics.pickups.dispatch-all') }}" class="ops-bulk" onsubmit="if (!confirm('Dispatch the nearest active rider to {{ $counts['dispatchable'] }} waiting pickup(s)?')) return false; this.querySelector('button').disabled = true;">@csrf<button type="submit" class="btn-dispatch-nearest">⚡ Dispatch nearest riders to all ({{ $counts['dispatchable'] }})</button></form>
        @else
            <span class="pickup-heading-icon">@include('seller.partials.icon', ['name' => 'truck', 'size' => 28])</span>
        @endif
    </header>

    {{-- Overview Metrics Cards --}}
    <div class="pickup-overview" aria-label="Pickup totals">
        <a href="{{ route('logistics.pickups', ['status' => 'pending']) }}" class="pickup-metric {{ $filter === 'pending' ? 'is-active-filter' : '' }} is-rose">
            <div>
                <span>Awaiting confirmation</span>
                <strong>{{ $counts['pending'] }}</strong>
                <small>Needs schedule review</small>
            </div>
            <span class="pickup-metric-icon">@include('seller.partials.icon', ['name' => 'box', 'size' => 21])</span>
        </a>

        <a href="{{ route('logistics.pickups', ['status' => 'unassigned']) }}" class="pickup-metric {{ $filter === 'unassigned' ? 'is-active-filter' : '' }} is-amber">
            <div>
                <span>Needs a rider</span>
                <strong>{{ $counts['unassigned'] }}</strong>
                <small>Ready for rider dispatch</small>
            </div>
            <span class="pickup-metric-icon">@include('seller.partials.icon', ['name' => 'user', 'size' => 21])</span>
        </a>

        <a href="{{ route('logistics.pickups', ['status' => 'assigned']) }}" class="pickup-metric {{ $filter === 'assigned' ? 'is-active-filter' : '' }} is-emerald">
            <div>
                <span>Rider dispatched</span>
                <strong>{{ $counts['assigned'] }}</strong>
                <small>Assigned to collection route</small>
            </div>
            <span class="pickup-metric-icon">@include('seller.partials.icon', ['name' => 'truck', 'size' => 21])</span>
        </a>

        <a href="{{ route('logistics.pickups', ['status' => 'all']) }}" class="pickup-metric {{ $filter === 'all' ? 'is-active-filter' : '' }}">
            <div>
                <span>Total requests</span>
                <strong>{{ $counts['all'] }}</strong>
                <small>All hub pickup orders</small>
            </div>
            <span class="pickup-metric-icon">@include('seller.partials.icon', ['name' => 'chart', 'size' => 21])</span>
        </a>
    </div>

    <section aria-label="Pickup request list">
        {{-- Toolbar: Filters & Search --}}
        <div class="pickup-toolbar">
            <nav class="pickup-tabs" aria-label="Pickup status filters">
                @php
                    $tabs = [
                        'all'        => 'All',
                        'pending'    => 'Pending',
                        'unassigned' => 'Needs Rider',
                        'assigned'   => 'Dispatched',
                        'confirmed'  => 'Confirmed',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('logistics.pickups', ['status' => $key, 'q' => $search]) }}" @if($filter === $key) aria-current="page" @endif>
                        {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('logistics.pickups') }}" class="pickup-search" role="search">
                <input type="hidden" name="status" value="{{ $filter }}">
                <label for="pickup-search" class="sr-only">Search seller, rider, address, or area</label>
                <span aria-hidden="true">@include('seller.partials.icon', ['name' => 'search', 'size' => 17])</span>
                <input id="pickup-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Seller, address, rider, area...">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="pickup-results">
            <span>
                {{ $pickups->total() }} {{ \Illuminate\Support\Str::plural('request', $pickups->total()) }}
                @if($search !== '') matching &ldquo;{{ $search }}&rdquo;@endif
                @if($filter !== 'all') &bull; Filtered by <strong>{{ ucfirst($filter) }}</strong>@endif
            </span>
            <span>Priority: Pending first &middot; Earliest pickup date first</span>
            @if($search !== '' || $filter !== 'all')
                <a href="{{ route('logistics.pickups') }}">Clear filters</a>
            @endif
        </div>

        {{-- Pickup Cards List --}}
        <div class="pickup-list">
        @forelse($pickups as $pickup)
            @php
                $isDispatched = !empty($pickup['rider_id']);
                $isPending    = $pickup['status'] === 'pending';
                $nearestRider = $pickup['nearest_rider'];
            @endphp
            <article class="pickup-card {{ $isPending ? 'is-pending' : '' }} {{ $isDispatched ? 'is-dispatched' : '' }}" id="pickup-card-{{ $pickup['id'] }}">
                <div class="pickup-card-heading">
                    <div class="pickup-seller">
                        <span class="pickup-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pickup['seller_name'], 0, 1)) }}</span>
                        <div>
                            <span class="pickup-reference">REQUEST #PR-{{ str_pad($pickup['id'], 4, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $pickup['seller_name'] }}</h3>
                        </div>
                    </div>

                    <div class="pickup-badges-row">
                        @if($isDispatched)
                            <span class="pickup-badge is-dispatched-badge">
                                @include('seller.partials.icon', ['name' => 'truck', 'size' => 13])
                                <span>Rider Dispatched</span>
                            </span>
                        @elseif($isPending)
                            <span class="pickup-badge is-pending-badge">
                                @include('seller.partials.icon', ['name' => 'box', 'size' => 13])
                                <span>Pending Confirmation</span>
                            </span>
                        @else
                            <span class="pickup-badge is-confirmed-badge">
                                @include('seller.partials.icon', ['name' => 'check', 'size' => 13])
                                <span>Confirmed Schedule</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Schedule & Package Specs --}}
                <div class="pickup-card-body">
                    <dl class="pickup-details">
                        <div>
                            <dt>Pickup Schedule</dt>
                            <dd>
                                <strong>{{ $pickup['pickup_date'] }}</strong>
                                <span>{{ $pickup['pickup_time'] }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt>Parcels &amp; Weight</dt>
                            <dd>
                                <strong>{{ $pickup['parcel_count'] }} parcels</strong>
                                <span>{{ number_format($pickup['weight_kg'], 1) }} kg total</span>
                            </dd>
                        </div>
                        <div class="pickup-address">
                            <dt>Seller Pickup Address</dt>
                            <dd class="address-text">
                                <span class="pin-icon" aria-hidden="true">📍</span>
                                <span>{{ $pickup['address'] ?: 'No address provided' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Seller Note if provided --}}
                @if($pickup['notes'])
                    <div class="pickup-note">
                        @include('seller.partials.icon', ['name' => 'chat', 'size' => 16])
                        <p><strong>Seller Note:</strong> {{ $pickup['notes'] }}</p>
                    </div>
                @endif

                {{-- RIDER DISPATCH & AREA MATCHING INTERFACE --}}
                <div class="pickup-dispatch-section">
                    @if($isDispatched && $pickup['rider'])
                        {{-- Rider is currently assigned --}}
                        <div class="assigned-rider-card">
                            <div class="assigned-rider-header">
                                <div class="assigned-rider-profile">
                                    <span class="assigned-rider-avatar">🛵</span>
                                    <div>
                                        <div class="assigned-rider-title-row">
                                            <span class="assigned-label">ASSIGNED PICKUP RIDER</span>
                                            <span class="assigned-status-dot" title="Active Rider">● Active</span>
                                        </div>
                                        <h4>{{ $pickup['rider']['name'] }}</h4>
                                        <p class="assigned-meta">
                                            <span class="rider-area-chip">📍 {{ $pickup['rider']['area'] }}</span>
                                            <span>🛵 {{ $pickup['rider']['vehicle_type'] }} ({{ $pickup['rider']['plate_no'] }})</span>
                                            @if($pickup['rider']['phone'])
                                                <a href="tel:{{ $pickup['rider']['phone'] }}" class="rider-phone-link">📞 {{ $pickup['rider']['phone'] }}</a>
                                            @endif
                                            <span>★ {{ number_format($pickup['rider']['rating'], 2) }}</span>
                                            <span>{{ $pickup['rider']['deliveries_today'] }} deliveries today</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="assigned-rider-actions">
                                    <button type="button" class="btn-change-rider" data-open-modal="rider-modal-{{ $pickup['id'] }}" title="Change or reassign rider">
                                        Change Rider
                                    </button>
                                    <form method="POST" action="{{ route('logistics.pickups.unassign', $pickup['id']) }}" onsubmit="return confirm('Remove rider assignment from this pickup?');">
                                        @csrf
                                        <button type="submit" class="btn-unassign-rider" title="Unassign rider">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if($pickup['assigned_at'])
                                <div class="assigned-timestamp">
                                    Dispatched on {{ $pickup['assigned_at'] }} &bull; Rider notified via app
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Rider has NOT been assigned yet: Show Nearby Matching & 1-Click Dispatch --}}
                        <div class="unassigned-dispatch-card">
                            @if($nearestRider)
                                <div class="nearest-suggestion-box">
                                    <div class="suggestion-header">
                                        <div class="suggestion-title-group">
                                            <span class="suggestion-icon">⚡</span>
                                            <div>
                                                <div class="suggestion-kicker-row">
                                                    <span class="suggestion-kicker">SMART PROXIMITY DISPATCH</span>
                                                    <span class="suggestion-badge {{ $nearestRider['is_nearby'] ? 'is-nearby' : '' }}">
                                                        {{ $nearestRider['match_badge'] }}
                                                    </span>
                                                </div>
                                                <h4 class="suggestion-rider-name">
                                                    {{ $nearestRider['name'] }}
                                                    <span class="suggestion-area">({{ $nearestRider['area'] }})</span>
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="suggestion-stats">
                                            <span class="suggestion-stat-item">★ {{ number_format($nearestRider['rating'], 2) }}</span>
                                            <span class="suggestion-stat-item">{{ $nearestRider['vehicle_type'] }}</span>
                                            <span class="suggestion-stat-item">{{ $nearestRider['deliveries_today'] }} active load</span>
                                        </div>
                                    </div>

                                    <div class="dispatch-cta-row">
                                        {{-- 1-Click Auto Dispatch Nearest Rider --}}
                                        <form method="POST" action="{{ route('logistics.pickups.auto-assign', $pickup['id']) }}" data-pickup-dispatch>
                                            @csrf
                                            <button type="submit" class="btn-dispatch-nearest" title="Instantly dispatch nearest rider {{ $nearestRider['name'] }}">
                                                @include('seller.partials.icon', ['name' => 'truck', 'size' => 17])
                                                <span>⚡ Request Nearest Rider ({{ Str::words($nearestRider['name'], 1, '') }})</span>
                                            </button>
                                        </form>

                                        {{-- Choose Another Rider Button --}}
                                        <button type="button" class="btn-choose-other" data-open-modal="rider-modal-{{ $pickup['id'] }}">
                                            <span>👤 Choose Rider...</span>
                                        </button>

                                        {{-- Secondary: Confirm schedule without rider if pending --}}
                                        @if($isPending)
                                            <form method="POST" action="{{ route('logistics.pickups.confirm', $pickup['id']) }}" data-pickup-confirm>
                                                @csrf
                                                <button type="submit" class="btn-confirm-plain" title="Confirm pickup schedule without rider assignment">
                                                    Confirm Schedule Only
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="no-riders-warning">
                                    <p>No active riders currently available in your hub fleet.</p>
                                    <a href="{{ route('logistics.riders') }}">Activate or approve a rider &rarr;</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <footer class="pickup-card-footer">
                    <span>Seller Email: <strong>{{ $pickup['seller_email'] }}</strong></span>
                    @if($pickup['seller_phone'] !== 'N/A')
                        <span>Seller Phone: <strong>{{ $pickup['seller_phone'] }}</strong></span>
                    @endif
                    @if($pickup['confirmed_at'])
                        <span>Confirmed: {{ $pickup['confirmed_at'] }}</span>
                    @endif
                </footer>

                {{-- Interactive Rider Selection Modal for this Pickup --}}
                <dialog id="rider-modal-{{ $pickup['id'] }}" class="pickup-rider-modal" aria-labelledby="modal-title-{{ $pickup['id'] }}">
                    <div class="modal-dialog-content">
                        <header class="modal-header">
                            <div>
                                <span class="modal-eyebrow">PICKUP RIDER ASSIGNMENT</span>
                                <h3 id="modal-title-{{ $pickup['id'] }}">Choose a pickup rider</h3>
                                <span class="modal-eyebrow">REQUEST #PR-{{ str_pad($pickup['id'], 4, '0', STR_PAD_LEFT) }}</span>
                                <p class="modal-pickup-location">
                                    📍 <strong>{{ $pickup['seller_name'] }}</strong> &bull; {{ $pickup['address'] }}
                                </p>
                            </div>
                            <button type="button" class="modal-close-btn" data-close-modal="rider-modal-{{ $pickup['id'] }}" aria-label="Close modal">&times;</button>
                        </header>

                        <div class="modal-filter-bar">
                            <label class="modal-search-label" for="rider-search-{{ $pickup['id'] }}">Find a rider</label>
                            <input id="rider-search-{{ $pickup['id'] }}" type="search" class="modal-rider-search" placeholder="Search by name, area or vehicle..." data-filter-target="rider-list-{{ $pickup['id'] }}">
                            <span class="modal-filter-hint">Ranked by proximity to this seller's pickup location</span>
                        </div>

                        <div class="modal-rider-list" id="rider-list-{{ $pickup['id'] }}">
                            @php
                                $nearbyMatches = collect($pickup['ranked_riders'] ?? [])->filter(fn($r) => $r['is_nearby']);
                                $otherRiders   = collect($pickup['ranked_riders'] ?? [])->reject(fn($r) => $r['is_nearby']);
                            @endphp

                            @if($nearbyMatches->isNotEmpty())
                                <div class="rider-group-heading">
                                    <span>Recommended nearby ({{ $nearbyMatches->count() }})</span>
                                </div>
                                @foreach($nearbyMatches as $r)
                                    <div class="modal-rider-item is-nearby-item" data-rider-search="{{ mb_strtolower($r['name'].' '.$r['area'].' '.$r['vehicle_type']) }}">
                                        <div class="rider-item-left">
                                            <span class="rider-badge-icon" aria-hidden="true">{{ mb_strtoupper(mb_substr($r['name'], 0, 1)) }}</span>
                                            <div>
                                                <div class="rider-item-name-row">
                                                    <strong>{{ $r['name'] }}</strong>
                                                    <span class="area-match-tag">{{ $r['match_badge'] }}</span>
                                                    @if($r['is_active'])
                                                        <span class="online-indicator">Online</span>
                                                    @endif
                                                </div>
                                                <div class="rider-item-meta">
                                                    <span>📍 {{ $r['area'] }}</span>
                                                    <span>{{ $r['vehicle_type'] }} ({{ $r['plate_no'] }})</span>
                                                    <span>★ {{ number_format($r['rating'], 2) }}</span>
                                                    <span>{{ $r['deliveries_today'] }} deliveries today</span>
                                                </div>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('logistics.pickups.assign', $pickup['id']) }}">
                                            @csrf
                                            <input type="hidden" name="rider_id" value="{{ $r['id'] }}">
                                            <button type="submit" class="btn-select-rider">
                                                <span>Dispatch &rarr;</span>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @endif

                            @if($otherRiders->isNotEmpty())
                                <div class="rider-group-heading">
                                    <span>Fleet Riders ({{ $otherRiders->count() }})</span>
                                </div>
                                @foreach($otherRiders as $r)
                                    <div class="modal-rider-item" data-rider-search="{{ mb_strtolower($r['name'].' '.$r['area'].' '.$r['vehicle_type']) }}">
                                        <div class="rider-item-left">
                                            <span class="rider-badge-icon" aria-hidden="true">{{ mb_strtoupper(mb_substr($r['name'], 0, 1)) }}</span>
                                            <div>
                                                <div class="rider-item-name-row">
                                                    <strong>{{ $r['name'] }}</strong>
                                                    <span class="area-match-tag is-other">{{ $r['area'] }}</span>
                                                </div>
                                                <div class="rider-item-meta">
                                                    <span>{{ $r['vehicle_type'] }} ({{ $r['plate_no'] }})</span>
                                                    <span>★ {{ number_format($r['rating'], 2) }}</span>
                                                    <span>{{ $r['deliveries_today'] }} deliveries</span>
                                                </div>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('logistics.pickups.assign', $pickup['id']) }}">
                                            @csrf
                                            <input type="hidden" name="rider_id" value="{{ $r['id'] }}">
                                            <button type="submit" class="btn-select-rider secondary">
                                                <span>Dispatch &rarr;</span>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @endif

                            @if($nearbyMatches->isEmpty() && $otherRiders->isEmpty())
                                <div class="modal-empty-riders">
                                    <p>No active riders available in hub.</p>
                                    <a href="{{ route('logistics.riders') }}">Manage and activate riders &rarr;</a>
                                </div>
                            @endif
                        </div>

                        <footer class="modal-footer">
                            <span class="modal-footer-note">Select Dispatch to assign a rider to this pickup.</span>
                            <button type="button" class="btn-modal-cancel" data-close-modal="rider-modal-{{ $pickup['id'] }}">Cancel</button>
                        </footer>
                    </div>
                </dialog>
            </article>
        @empty
            <div class="pickup-empty">
                <span>@include('seller.partials.icon', ['name' => 'box', 'size' => 32])</span>
                <h3>No pickup requests found.</h3>
                <p>{{ $search !== '' ? 'Try another seller name, email or pickup address.' : 'New requests from sellers will appear here.' }}</p>
                @if($search !== '' || $filter !== 'all')
                    <a href="{{ route('logistics.pickups') }}">View all pickup requests &rarr;</a>
                @endif
            </div>
        @endforelse
        </div>
    </section>

    @if($pickups->hasPages())
        <div class="pickup-pagination">{{ $pickups->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-pickups.css') }}?v={{ filemtime(public_path('css/logistics-pickups.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/logistics-pickups.js') }}?v={{ filemtime(public_path('js/logistics-pickups.js')) }}"></script>
@endpush
