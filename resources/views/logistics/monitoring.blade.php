@extends('layouts.logistics')
@section('title', 'Delivery Monitoring | Logistics Hub | cartzy')
@section('page_title', 'Delivery Monitoring')
@section('page_subtitle', 'Check delivery progress and follow up on parcels that need attention')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-monitoring.css') }}?v={{ filemtime(public_path('css/logistics-monitoring.css')) }}">
@endpush
@section('content')
<section class="delivery-monitor" aria-label="Delivery monitoring">
    <header class="monitor-heading"><div><span class="monitor-eyebrow">DELIVERY OVERVIEW</span><h2>Every delivery, easy to follow.</h2><p>Find a parcel, check its progress, and follow up with the rider.</p></div><div class="monitor-refresh"><a class="monitor-button" href="{{ route('logistics.monitoring', request()->only('status','search','page')) }}"><span aria-hidden="true">↻</span> Refresh status</a><small>Refresh to see the latest updates</small></div></header>
    <div class="monitor-metrics">
        @foreach(['failed'=>['Needs attention','Failed deliveries to follow up'], 'out_for_delivery'=>['Out for delivery','On the way to the recipient'], 'in_transit'=>['In transit','Moving to the next destination'], 'delivered'=>['Delivered','Completed deliveries']] as $key=>[$title,$detail])
        <a class="monitor-metric {{ $key }}" href="{{ route('logistics.monitoring', ['status'=>$key]) }}"><span>{{ $title }} <span aria-hidden="true">↗</span></span><strong>{{ $counts[$key] }}</strong><small>{{ $detail }}</small></a>
        @endforeach
    </div>
    <div class="monitor-panel">
        <form method="GET" action="{{ route('logistics.monitoring') }}" class="monitor-search"><input type="hidden" name="status" value="{{ $filter }}"><div><label for="delivery-search">Find a delivery</label><input type="search" name="search" id="delivery-search" value="{{ $search }}" placeholder="Tracking number, recipient, rider or area"></div><button class="monitor-button primary" type="submit">Search deliveries</button>@if($search !== '')<a href="{{ route('logistics.monitoring', ['status'=>$filter]) }}" class="monitor-clear">Clear search</a>@endif</form>
        <nav class="monitor-tabs" aria-label="Delivery status">@foreach($labels as $key=>$label)<a href="{{ route('logistics.monitoring', ['status'=>$key,'search'=>$search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>@endforeach</nav>
        <div class="monitor-results"><span>{{ $parcels->total() }} {{ Str::plural('delivery', $parcels->total()) }}@if($search !== '') matching “{{ $search }}”@endif</span><span><i aria-hidden="true"></i> Failed deliveries appear first</span></div>
        <div class="monitor-table-wrap"><table class="monitor-table"><caption class="monitor-sr">Delivery progress, recipients, riders and follow-up actions</caption><thead><tr><th scope="col">Parcel & recipient</th><th scope="col">Area & rider</th><th scope="col">Payment</th><th scope="col">Delivery status</th><th scope="col">Follow up</th></tr></thead><tbody>
        @forelse($parcels as $parcel)
        @php($status = $parcel['delivery_status'])
        <tr class="{{ $status === 'failed' ? 'monitor-failed-row' : '' }}"><td data-label="Parcel & recipient"><strong class="monitor-code">{{ $parcel['tracking_code'] }}</strong><span class="monitor-recipient">{{ $parcel['recipient_name'] }}</span><details class="monitor-details"><summary>Delivery details</summary><p>{{ $parcel['recipient_address'] }}</p><p>{{ $parcel['items'] }}</p>@if($parcel['recipient_phone'])<a href="tel:{{ $parcel['recipient_phone'] }}" aria-label="Call recipient {{ $parcel['recipient_name'] }}">{{ $parcel['recipient_phone'] }}</a>@endif</details></td>
        <td data-label="Area & rider"><strong>{{ $parcel['delivery_area'] ?: 'Area not set' }}</strong><small>{{ $parcel['assigned_rider_name'] ?: 'No rider assigned' }}</small></td>
        <td data-label="Payment">@if($parcel['payment_method'] === 'COD')<strong class="monitor-cod">₱{{ number_format($parcel['cod_amount'],2) }}</strong><small>Cash on delivery</small>@else<strong class="monitor-prepaid">Prepaid</strong><small>No cash to collect</small>@endif</td>
        <td data-label="Delivery status"><span class="monitor-badge {{ $status }}"><span aria-hidden="true">{{ $status === 'failed' ? '!' : ($status === 'delivered' ? '✓' : '•') }}</span> {{ $labels[$status] ?? Str::headline($status) }}</span><small>Updated {{ $parcel['updated_at'] ?? '—' }}</small></td>
        <td data-label="Follow up">@if($status === 'delivered')<span class="monitor-done">✓ Completed</span>@elseif($parcel['assigned_rider_user_id'])<a class="monitor-button {{ $status === 'failed' ? 'rose' : 'subtle' }}" href="{{ route('logistics.chat', ['contact'=>$parcel['assigned_rider_user_id']]) }}">Message rider <span aria-hidden="true">↗</span></a>@elseif($parcel['status'] === 'pending_receipt')<a class="monitor-button subtle" href="{{ route('logistics.parcels', ['search'=>$parcel['tracking_code']]) }}">View incoming parcel</a>@elseif($parcel['sort_status'] === 'unsorted')<a class="monitor-button subtle" href="{{ route('logistics.sorting') }}">Go to sorting</a>@else<a class="monitor-button subtle" href="{{ route('logistics.assignments', ['area'=>$parcel['delivery_area'] ?: 'all']) }}">Assign a rider</a>@endif</td></tr>
        @empty<tr><td colspan="5"><div class="monitor-empty"><h3>No deliveries found</h3><p>Try another tracking number, name, or status.</p><a class="monitor-button" href="{{ route('logistics.monitoring') }}">View all deliveries</a></div></td></tr>@endforelse
        </tbody></table></div>
        @if($parcels->hasPages())<div class="monitor-pagination">{{ $parcels->links() }}</div>@endif
    </div>
    <p class="monitor-footnote">All update times are shown in Philippine time.</p>
</section>
@endsection
