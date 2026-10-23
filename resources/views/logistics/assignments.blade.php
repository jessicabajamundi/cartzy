@extends('layouts.logistics')
@section('title', 'Delivery Assignment | Logistics Hub | cartzy')
@section('page_title', 'Delivery Assignment')
@section('page_subtitle', 'Match each parcel with a rider and keep deliveries moving')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-assignments.css') }}?v={{ filemtime(public_path('css/logistics-assignments.css')) }}">
@endpush
@section('content')
@php
    $waiting = collect($parcels)->filter(fn($p) => !$p['assigned_rider_id'] && $p['sort_status'] !== 'assigned')->count();
    $orderedParcels = collect($parcels)->sortBy(fn($p) => $p['assigned_rider_id'] || $p['sort_status'] === 'assigned' ? 1 : 0);
@endphp
<section class="assignment-workspace" aria-label="Delivery assignment">
    <header class="assignment-heading"><div><span class="assignment-eyebrow">DISPATCH DESK</span><h2>The right rider. The next delivery.</h2><p>Find a parcel, choose an active rider, then select Assign rider.</p></div><a class="assignment-button secondary" href="{{ route('logistics.sorting') }}">Go to sorting <span aria-hidden="true">→</span></a></header>
    <div class="assignment-metrics"><div class="rose"><span>Needs a rider</span><strong>{{ $waiting }}</strong><small>{{ $filter === 'all' ? 'Across all areas' : 'In the selected area' }}</small></div><div><span>Already assigned</span><strong>{{ count($parcels) - $waiting }}</strong><small>{{ $filter === 'all' ? 'Across all areas' : 'In the selected area' }}</small></div><div class="blend"><span>Active riders</span><strong>{{ count($riders) }}</strong><small>Approved riders in your hub</small></div></div>
    <div class="assignment-panel">
        <div class="assignment-toolbar"><div class="assignment-search"><label for="assignment-search">Find a parcel</label><input id="assignment-search" type="search" placeholder="Tracking number, recipient or address" autocomplete="off"></div><form action="{{ route('logistics.assignments') }}" method="GET" class="assignment-area"><div><label for="assignment-area">Delivery area</label><select id="assignment-area" name="area"><option value="all">All areas</option>@foreach($areas as $area)<option value="{{ $area }}" @selected($filter === $area)>{{ $area }}</option>@endforeach</select></div><button class="assignment-button secondary" type="submit">Apply area</button></form></div>
        <div class="assignment-tabs" role="group" aria-label="Filter assignment status"><button type="button" data-assignment-filter="all" aria-pressed="true">All parcels <span>{{ count($parcels) }}</span></button><button type="button" data-assignment-filter="waiting" aria-pressed="false">Needs a rider <span>{{ $waiting }}</span></button><button type="button" data-assignment-filter="assigned" aria-pressed="false">Assigned <span>{{ count($parcels) - $waiting }}</span></button></div>
        <div class="assignment-list-heading"><span id="assignment-result-count" role="status">{{ count($parcels) }} parcels</span><span>Parcels needing a rider appear first</span></div>
        @if(!$riders)<div class="assignment-notice">No active riders available. <a href="{{ route('logistics.riders', ['filter'=>'inactive']) }}">Activate a rider</a> to start assigning deliveries.</div>@endif
        @foreach($orderedParcels as $parcel)
        @php
            $assigned = (bool) $parcel['assigned_rider_id'] || $parcel['sort_status'] === 'assigned';
            $matching = collect($riders)->filter(fn($r) => mb_strtolower(trim($r['area'])) === mb_strtolower(trim($parcel['delivery_area'] ?? '')));
            $others = collect($riders)->reject(fn($r) => $matching->contains('id', $r['id']));
        @endphp
        <article class="assignment-row {{ $assigned ? '' : 'needs-rider' }}" data-assignment-state="{{ $assigned ? 'assigned' : 'waiting' }}" data-assignment-search="{{ $parcel['tracking_code'].' '.$parcel['recipient_name'].' '.$parcel['recipient_address'].' '.$parcel['delivery_area'].' '.$parcel['assigned_rider_name'] }}">
            <div class="assignment-parcel"><div class="assignment-code-row"><strong>{{ $parcel['tracking_code'] }}</strong><span class="assignment-status {{ $assigned ? '' : 'waiting' }}">{{ $assigned ? 'Assigned' : 'Needs a rider' }}</span></div><h3>{{ $parcel['recipient_name'] }}</h3><p class="assignment-address">{{ $parcel['recipient_address'] }}</p><div class="assignment-meta"><span class="assignment-area-tag">{{ $parcel['delivery_area'] ?: 'Area not set' }}</span><span>{{ $parcel['weight_kg'] }} kg</span><span class="{{ $parcel['payment_method'] === 'COD' ? 'assignment-cod' : '' }}">{{ $parcel['payment_method'] === 'COD' ? 'COD ₱'.number_format($parcel['cod_amount'],2) : 'Prepaid' }}</span></div><details class="assignment-details"><summary>View items</summary><p>{{ $parcel['items'] }}</p></details></div>
            <div class="assignment-action">
                @if($assigned)<div class="assignment-rider"><span class="assignment-rider-check" aria-hidden="true">✓</span><div><small>Assigned rider</small><strong>{{ $parcel['assigned_rider_name'] ?: 'Rider unavailable' }}</strong>@if($parcel['assigned_at'])<time>{{ $parcel['assigned_at'] }}</time>@endif</div></div>@endif
                @if($parcel['sort_status'] !== 'assigned')
                    @if($assigned)<details class="assignment-change"><summary>Change rider</summary>@endif
                    <form class="assignment-form" method="POST" action="{{ route('logistics.assignments.assign', ['id'=>$parcel['id']], false) }}">@csrf<label for="rider-{{ $parcel['id'] }}">{{ $assigned ? 'Choose a replacement rider' : 'Choose a rider' }}</label><select id="rider-{{ $parcel['id'] }}" name="rider_id" required @disabled(!$riders)><option value="">{{ $riders ? 'Select an active rider' : 'No active riders' }}</option>@if($matching->isNotEmpty())<optgroup label="Same delivery area">@foreach($matching as $rider)<option value="{{ $rider['id'] }}">{{ $rider['name'] }} — {{ $rider['area'] }}</option>@endforeach</optgroup>@endif @if($others->isNotEmpty())<optgroup label="{{ $matching->isNotEmpty() ? 'Other active riders' : 'Active riders' }}">@foreach($others as $rider)<option value="{{ $rider['id'] }}">{{ $rider['name'] }} — {{ $rider['area'] }}</option>@endforeach</optgroup>@endif</select><button class="assignment-button primary" type="submit" @disabled(!$riders)>{{ $assigned ? 'Update rider' : 'Assign rider' }} <span aria-hidden="true">→</span></button></form>
                    @if($assigned)</details>@endif
                @endif
            </div>
        </article>
        @endforeach
        <div class="assignment-empty" id="assignment-empty" @if(count($parcels)) hidden @endif><h3>No parcels to show</h3><p>Try another search or area. Parcels appear here after sorting.</p><button type="button" class="assignment-button secondary" id="assignment-clear">Clear search & status</button><a href="{{ route('logistics.sorting') }}">Go to sorting →</a></div>
    </div>
</section>
@endsection
@push('scripts')
<script src="{{ asset('js/logistics-assignments.js') }}?v={{ filemtime(public_path('js/logistics-assignments.js')) }}" defer></script>
@endpush
