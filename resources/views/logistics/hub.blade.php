@extends('layouts.logistics')
@section('title', 'Hub Desk | Logistics Hub | cartzy')
@section('page_title', 'Hub Desk')
@section('page_subtitle', 'Receive, sort and assign parcels in one place')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-hub.css') }}?v={{ filemtime(public_path('css/logistics-hub.css')) }}">
@endpush
@section('content')
@php
    $deliveryAreas = \App\Http\Controllers\Logistics\LogisticsPortalController::DELIVERY_AREAS;
    $steps = [
        'receive'  => ['1', 'Receive', 'Parcels arriving at the hub'],
        'sort'     => ['2', 'Sort', 'Address had no known area'],
        'assign'   => ['3', 'Assign rider', 'Sorted, waiting for a rider'],
        'assigned' => ['✓', 'Assigned', 'Waiting for rider pickup'],
    ];
    $hints = [
        'receive'  => 'Mark parcels received as they arrive. Each one is sorted automatically when its address names a delivery area.',
        'sort'     => 'These addresses didn’t match a delivery area. Check the suggestion or choose the closest area.',
        'assign'   => 'The best rider for each parcel is already selected. Assign them all at once, or one at a time.',
        'assigned' => 'These parcels have a rider and are waiting to be picked up. Follow them in Delivery Monitoring.',
    ];
    $next = collect(['receive', 'sort', 'assign'])->first(fn ($key) => $key !== $tab && $counts[$key] > 0); // for the empty state
    $bulk = [
        'receive' => [$counts['receive'], 'logistics.parcels.receive-all', '✓ Receive all', 'Mark all :n waiting parcel(s) as received?'],
        'sort'    => [$sortSuggestable, 'logistics.sorting.sort-suggested', 'Sort all to suggested areas', 'Sort :n parcel(s) into the areas found in their addresses?'],
        'assign'  => [$riders ? $counts['assign'] : 0, 'logistics.assignments.auto-assign', 'Assign best riders to all', 'Assign the best-matching rider to all :n waiting parcel(s)?'],
    ][$tab] ?? null;
@endphp
<section class="hub-desk" aria-label="Hub desk">
    <header class="hub-heading">
        <div>
            <span class="hub-eyebrow">HUB DESK</span>
            <h2>Receive, sort and assign in one place.</h2>
            <p>Most parcels only need one click: <strong>Process all</strong> receives them, sorts them by address and gives each one the best rider.</p>
        </div>
        <div class="hub-heading-actions">
            @if($processable > 0)
                <form method="POST" action="{{ route('logistics.hub.process-all') }}" class="hub-confirm" data-confirm="Receive, sort and assign every waiting parcel now?">@csrf
                    <button type="submit" class="hub-button primary large">⚡ Process all <span class="hub-pill">{{ $processable }}</span></button>
                </form>
            @endif
            <a class="hub-button secondary" href="{{ route('logistics.monitoring') }}">Monitor deliveries <span aria-hidden="true">→</span></a>
        </div>
    </header>

    <nav class="hub-steps" aria-label="Parcel steps">
        @foreach($steps as $key => [$number, $label, $caption])
            <a href="{{ route('logistics.hub', ['tab' => $key]) }}" class="hub-step {{ $counts[$key] > 0 && $key !== 'assigned' ? 'has-work' : '' }}" @if($tab === $key) aria-current="page" @endif>
                <span class="hub-step-number" aria-hidden="true">{{ $number }}</span>
                <span class="hub-step-text"><strong>{{ $label }}</strong><small>{{ $caption }}</small></span>
                <span class="hub-step-count" aria-label="{{ $counts[$key] }} parcels">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="hub-panel">
        <div class="hub-toolbar">
            <p class="hub-hint">{{ $hints[$tab] }}</p>
            @if($bulk && $bulk[0] > 0)
                <form method="POST" action="{{ route($bulk[1]) }}" class="hub-confirm" data-confirm="{{ str_replace(':n', $bulk[0], $bulk[3]) }}">@csrf
                    <button type="submit" class="hub-button primary">{{ $bulk[2] }} <span class="hub-pill">{{ $bulk[0] }}</span></button>
                </form>
            @endif
        </div>
        @if($tab === 'assign' && !$riders)
            <p class="hub-notice">No active riders right now. <a href="{{ route('logistics.riders') }}">Activate a rider</a> to assign deliveries.</p>
        @endif
        <form class="hub-search" method="GET" action="{{ route('logistics.hub') }}" role="search">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="hub-sr" for="hub-search">Find a parcel</label>
            <input type="search" id="hub-search" name="q" value="{{ $search }}" placeholder="Find by tracking number, seller, recipient or address">
            <button type="submit" class="hub-button secondary">Find</button>
            @if($search !== '')<a class="hub-link" href="{{ route('logistics.hub', ['tab' => $tab]) }}">Clear</a>@endif
        </form>

        <ul class="hub-list">
            @forelse($parcels as $parcel)
                <li class="hub-row">
                    <div class="hub-parcel">
                        <div class="hub-parcel-top">
                            <strong class="hub-code">{{ $parcel['tracking_code'] }}</strong>
                            @if($parcel['payment_method'] === 'COD')<span class="hub-tag cod">COD ₱{{ number_format($parcel['cod_amount'], 2) }}</span>@else<span class="hub-tag">Prepaid</span>@endif
                            @if($parcel['delivery_area'])<span class="hub-tag area">{{ $parcel['delivery_area'] }}</span>@endif
                        </div>
                        <p class="hub-recipient"><strong>{{ $parcel['recipient_name'] }}</strong> · {{ $parcel['recipient_address'] }}</p>
                        <small class="hub-meta">From {{ $parcel['seller_name'] }} · {{ $parcel['items'] }}</small>
                    </div>
                    <div class="hub-action">
                        @if($tab === 'receive')
                            <form method="POST" action="{{ route('logistics.parcels.receive', ['id' => $parcel['id']], false) }}" class="hub-once">@csrf
                                <button type="submit" class="hub-button primary">✓ Receive</button>
                            </form>
                            <small>{{ $parcel['suggested_area'] ? 'Will sort to '.$parcel['suggested_area'] : 'Will need an area' }}</small>
                        @elseif($tab === 'sort')
                            <form method="POST" action="{{ route('logistics.sorting.sort', ['id' => $parcel['id']], false) }}" class="hub-inline hub-once">@csrf
                                <label class="hub-sr" for="area-{{ $parcel['id'] }}">Delivery area for {{ $parcel['tracking_code'] }}</label>
                                <select id="area-{{ $parcel['id'] }}" name="area" required>
                                    <option value="">Choose an area</option>
                                    @foreach($deliveryAreas as $area)<option value="{{ $area }}" @selected($area === $parcel['suggested_area'])>{{ $area }}</option>@endforeach
                                </select>
                                <button type="submit" class="hub-button primary">Sort</button>
                            </form>
                        @elseif($tab === 'assign')
                            <form method="POST" action="{{ route('logistics.assignments.assign', ['id' => $parcel['id']], false) }}" class="hub-inline hub-once">@csrf
                                <label class="hub-sr" for="rider-{{ $parcel['id'] }}">Rider for {{ $parcel['tracking_code'] }}</label>
                                <select id="rider-{{ $parcel['id'] }}" name="rider_id" required @disabled(!$riders)>
                                    <option value="">{{ $riders ? 'Choose a rider' : 'No active riders' }}</option>
                                    @foreach($riders as $rider)<option value="{{ $rider['id'] }}" @selected($rider['id'] === ($parcel['suggested_rider_id'] ?? null))>{{ $rider['name'] }} — {{ $rider['area'] }}{{ $rider['id'] === ($parcel['suggested_rider_id'] ?? null) ? ' (best)' : '' }}</option>@endforeach
                                </select>
                                <button type="submit" class="hub-button primary" @disabled(!$riders)>Assign</button>
                            </form>
                        @else
                            <span class="hub-assigned">✓ {{ $parcel['assigned_rider_name'] ?: 'Rider' }}</span>
                            <small>{{ $parcel['assigned_at'] ? 'Assigned '.$parcel['assigned_at'] : '' }}</small>
                            <a class="hub-link" href="{{ route('logistics.monitoring', ['search' => $parcel['tracking_code']]) }}">Track →</a>
                        @endif
                    </div>
                </li>
            @empty
                <li class="hub-empty">
                    <span aria-hidden="true">✓</span>
                    <h3>{{ $search !== '' ? 'No parcels match “'.$search.'”' : 'All clear here' }}</h3>
                    <p>{{ $search !== '' ? 'Try another tracking number or name.' : 'Nothing is waiting in this step.' }}</p>
                    @if($next && $search === '')<a class="hub-button secondary" href="{{ route('logistics.hub', ['tab' => $next]) }}">Go to {{ $steps[$next][1] }} ({{ $counts[$next] }}) →</a>@endif
                </li>
            @endforelse
        </ul>
        @if($parcels->hasPages())<div class="hub-pagination">{{ $parcels->links() }}</div>@endif
    </div>
</section>
@endsection
@push('scripts')
<script>
// Bulk buttons ask once; every form disables its button while submitting so nothing is sent twice.
document.querySelectorAll('.hub-desk form[method="POST"]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.confirm && !confirm(form.dataset.confirm)) { event.preventDefault(); return; }
        if (form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        form.querySelector('button[type="submit"]').disabled = true;
    });
});
window.addEventListener('pageshow', () => document.querySelectorAll('.hub-desk form[data-submitting]').forEach(form => {
    delete form.dataset.submitting;
    form.querySelector('button[type="submit"]').disabled = false;
}));
</script>
@endpush
