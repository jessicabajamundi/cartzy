@extends('layouts.logistics')
@section('title', 'Sorting | Logistics Hub | cartzy')
@section('page_title', 'Sorting of Parcels')
@section('page_subtitle', 'Choose the delivery area and prepare each parcel for a rider')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-sorting.css') }}?v={{ filemtime(public_path('css/logistics-sorting.css')) }}">
@endpush
@section('content')
@php($deliveryAreas = ['Laguna - Santa Cruz','Laguna - San Pablo','Laguna - Calamba','Laguna - Los Baños','Laguna - Biñan / Santa Rosa','Batangas - Lipa','Batangas - Mataasnakahoy','Quezon - Lucena','Quezon - Tayabas','Metro Manila - South'])
<section class="sorting-workspace" aria-label="Parcel sorting">
<header class="sorting-heading"><div><span class="sorting-eyebrow">PARCEL SORTING</span><h2>A clear route for every parcel.</h2><p>Check the recipient’s address, choose the delivery area, then mark the parcel sorted.</p></div><a class="sorting-button secondary" href="{{ route('logistics.assignments') }}">Assign riders <span aria-hidden="true">→</span></a></header>
<div class="sorting-metrics">@foreach(['unsorted'=>['Needs sorting','Choose a delivery area'], 'sorted'=>['Sorted','Ready for rider assignment'], 'assigned'=>['Assigned','Already assigned to a rider']] as $key=>[$label,$hint])<a class="sorting-metric {{ $key }}" href="{{ route('logistics.sorting', ['status'=>$key]) }}"><span>{{ $label }} <span aria-hidden="true">↗</span></span><strong>{{ $counts[$key] }}</strong><small>{{ $hint }}</small></a>@endforeach</div>
<div class="sorting-panel">
<form class="sorting-search" method="GET" action="{{ route('logistics.sorting') }}"><input type="hidden" name="status" value="{{ $filter }}"><div><label for="sorting-search">Find a parcel</label><input type="search" id="sorting-search" name="search" value="{{ $search }}" placeholder="Tracking number, recipient, seller or address"></div><button class="sorting-button primary" type="submit">Find parcel</button>@if($search !== '')<a class="sorting-clear" href="{{ route('logistics.sorting', ['status'=>$filter]) }}">Clear search</a>@endif</form>
<nav class="sorting-tabs" aria-label="Sorting status">@foreach(['all'=>'All parcels','unsorted'=>'Needs sorting','sorted'=>'Sorted','assigned'=>'Assigned'] as $key=>$label)<a href="{{ route('logistics.sorting', ['status'=>$key, 'search'=>$search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>@endforeach</nav>
<div class="sorting-results"><span>{{ count($parcels) }} {{ Str::plural('parcel', count($parcels)) }}@if($search !== '') matching “{{ $search }}”@endif</span><span>Unsorted parcels appear first</span></div>
@forelse($parcels as $parcel)
<article class="sorting-row {{ $parcel['sort_status'] === 'unsorted' ? 'needs-sorting' : '' }}">
<div class="sorting-parcel"><div class="sorting-code-row"><strong>{{ $parcel['tracking_code'] }}</strong><span class="sorting-badge {{ $parcel['sort_status'] }}">{{ $parcel['sort_status'] === 'unsorted' ? 'Needs sorting' : ucfirst($parcel['sort_status']) }}</span></div><h3>{{ $parcel['recipient_name'] }}</h3><div class="sorting-address"><span class="sorting-label">DELIVER TO</span><p>{{ $parcel['recipient_address'] }}</p></div><div class="sorting-meta"><span>{{ $parcel['seller_name'] }}</span><span>{{ $parcel['weight_kg'] }} kg</span><span class="{{ $parcel['payment_method'] === 'COD' ? 'sorting-cod' : '' }}">{{ $parcel['payment_method'] === 'COD' ? 'COD ₱'.number_format($parcel['cod_amount'],2) : 'Prepaid' }}</span></div><details class="sorting-details"><summary>View items</summary><p>{{ $parcel['items'] }}</p></details></div>
<div class="sorting-action">
@if($parcel['sort_status'] === 'unsorted')
<form class="sorting-form" method="POST" action="{{ route('logistics.sorting.sort', ['id'=>$parcel['id']], false) }}">@csrf<label for="sort-area-{{ $parcel['id'] }}">Delivery area</label><select id="sort-area-{{ $parcel['id'] }}" name="area" required aria-describedby="sort-help-{{ $parcel['id'] }}"><option value="">Choose an area</option>@foreach($deliveryAreas as $area)<option value="{{ $area }}">{{ $area }}</option>@endforeach</select><p id="sort-help-{{ $parcel['id'] }}" class="sorting-help">Match the area to the recipient’s address.</p><button class="sorting-button primary" type="submit">Mark as sorted <span aria-hidden="true">✓</span></button></form>
@elseif($parcel['sort_status'] === 'sorted')
<div class="sorting-complete"><span aria-hidden="true">✓</span><div><small>Sorted to</small><strong>{{ $parcel['delivery_area'] ?: 'Area not set' }}</strong>@if($parcel['sorted_at'])<time>{{ $parcel['sorted_at'] }}</time>@endif</div></div><a class="sorting-button secondary" href="{{ route('logistics.assignments', ['area'=>$parcel['delivery_area'] ?: 'all']) }}">Assign a rider →</a>
@else
<div class="sorting-complete"><span aria-hidden="true">✓</span><div><small>Assigned rider</small><strong>{{ $parcel['assigned_rider_name'] ?: 'Rider unavailable' }}</strong><p>{{ $parcel['delivery_area'] }}</p></div></div><a class="sorting-link" href="{{ route('logistics.monitoring', ['search'=>$parcel['tracking_code']]) }}">View delivery status →</a>
@endif
</div></article>
@empty
<div class="sorting-empty"><h3>No parcels found</h3><p>Try a different search or status, or check incoming parcels.</p><a class="sorting-button secondary" href="{{ route('logistics.sorting') }}">View all parcels</a><a class="sorting-link" href="{{ route('logistics.parcels') }}">View incoming parcels →</a></div>
@endforelse
</div></section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.sorting-form').forEach(form => {
    const select = form.querySelector('select');
    const button = form.querySelector('button');
    const update = () => { button.disabled = !select.value; };
    select.addEventListener('change', update);
    form.addEventListener('submit', event => {
        if (!select.value || form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        button.disabled = true;
        button.textContent = 'Saving…';
    });
    window.addEventListener('pageshow', () => { delete form.dataset.submitting; button.textContent = 'Mark as sorted ✓'; update(); });
    update();
});
</script>
@endpush
