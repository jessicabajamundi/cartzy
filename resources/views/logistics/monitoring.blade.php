@extends('layouts.logistics')
@section('title', 'Delivery Monitoring | Logistics Hub | cartzy')
@section('page_eyebrow', 'PARCEL OPERATIONS / DELIVERY OVERVIEW')
@section('page_title', 'Delivery Monitoring')
@section('page_description', 'Find a parcel, check its progress across your delivery areas, and follow up with the assigned rider.')
@section('page_subtitle', 'Check delivery progress and follow up on parcels that need attention')
@section('page_actions')
<a class="button subtle monitor-button" href="{{ route('logistics.monitoring', request()->only('status','search','page')) }}"><span aria-hidden="true">↻</span> Refresh status</a>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-monitoring.css') }}?v={{ filemtime(public_path('css/logistics-monitoring.css')) }}">
@endpush
@section('content')
<section class="delivery-monitor" aria-label="Delivery monitoring">
    <div class="stats-grid dashboard-stats monitor-metrics">
        @foreach(['failed'=>['Needs attention','Alert','Failed deliveries to follow up','shield'], 'out_for_delivery'=>['Out for delivery','Active','On the way to the recipient','truck'], 'in_transit'=>['In transit','Moving','Moving to the next destination','activity'], 'delivered'=>['Delivered','Completed','Completed deliveries','box']] as $key=>[$title,$tag,$detail,$icon])
        <a class="stat-card monitor-metric {{ $key }}" href="{{ route('logistics.monitoring', ['status'=>$key]) }}">
            <div class="metric-heading">
                <span class="metric-icon">@include('admin.partials.icon', ['name' => $icon])</span>
                <span>{{ $tag }}</span>
            </div>
            <span>{{ $title }} <span aria-hidden="true">↗</span></span>
            <strong>{{ $counts[$key] }}</strong>
            <small>{{ $detail }}</small>
        </a>
        @endforeach
    </div>
    <div class="panel monitor-panel">
        <form method="GET" action="{{ route('logistics.monitoring') }}" class="monitor-search"><input type="hidden" name="status" value="{{ $filter }}"><div><label for="delivery-search">Find a delivery</label><input type="search" name="search" id="delivery-search" value="{{ $search }}" placeholder="Tracking number, recipient, rider or area"></div><button class="monitor-button primary" type="submit">Search deliveries</button>@if($search !== '')<a href="{{ route('logistics.monitoring', ['status'=>$filter]) }}" class="monitor-clear">Clear search</a>@endif</form>
        <nav class="monitor-tabs" aria-label="Delivery status">@foreach($labels as $key=>$label)<a href="{{ route('logistics.monitoring', ['status'=>$key,'search'=>$search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>@endforeach</nav>
        <div class="monitor-results"><span>{{ $parcels->total() }} {{ Str::plural('delivery', $parcels->total()) }}@if($search !== '') matching “{{ $search }}”@endif</span><span><i aria-hidden="true"></i> Failed deliveries appear first</span></div>
        <div class="monitor-table-wrap"><table class="monitor-table"><caption class="monitor-sr">Delivery progress, recipients, riders and follow-up actions</caption><thead><tr><th scope="col">Parcel & recipient</th><th scope="col">Area & rider</th><th scope="col">Payment</th><th scope="col">Delivery status</th><th scope="col">Follow up</th></tr></thead><tbody>
        @forelse($parcels as $parcel)
        @php($status = $parcel['delivery_status'])
        <tr class="{{ $status === 'failed' ? 'monitor-failed-row' : '' }}"><td data-label="Parcel & recipient"><strong class="monitor-code">{{ $parcel['tracking_code'] }}</strong><span class="monitor-recipient">{{ $parcel['recipient_name'] }}</span><details class="monitor-details"><summary>Delivery details</summary><p>{{ $parcel['recipient_address'] }}</p><p>{{ $parcel['items'] }}</p>@if($parcel['recipient_phone'])<a href="tel:{{ $parcel['recipient_phone'] }}" aria-label="Call recipient {{ $parcel['recipient_name'] }}">{{ $parcel['recipient_phone'] }}</a>@endif</details></td>
        <td data-label="Area & rider">
            <strong>{{ $parcel['delivery_area'] ?: 'Area not set' }}</strong>
            @if(!empty($parcel['barangay']))<small style="color:#4338CA;font-weight:600;">📍 Brgy. {{ $parcel['barangay'] }}</small>@endif
            <small>{{ $parcel['assigned_rider_name'] ?: 'No rider assigned' }}</small>
            @if(!empty($parcel['is_rescheduled']))
                <small style="color:#B45309;font-weight:700;">⏳ Rescheduled ({{ $parcel['rescheduled_days_left'] !== null ? $parcel['rescheduled_days_left'].'d left' : 'active' }})</small>
            @endif
        </td>
        <td data-label="Payment">@if($parcel['payment_method'] === 'COD')<strong class="monitor-cod">₱{{ number_format($parcel['cod_amount'],2) }}</strong><small>Cash on delivery</small>@else<strong class="monitor-prepaid">Prepaid</strong><small>No cash to collect</small>@endif</td>
        <td data-label="Delivery status"><span class="monitor-badge {{ $status }}"><span aria-hidden="true">{{ $status === 'failed' ? '!' : ($status === 'delivered' ? '✓' : '•') }}</span> {{ $labels[$status] ?? Str::headline($status) }}</span><small>Updated {{ $parcel['updated_at'] ?? '—' }}</small></td>
        <td data-label="Follow up">
            @if($status === 'delivered')
                <span class="monitor-done">✓ Completed</span>
            @elseif($status === 'returned')
                <span class="monitor-done" style="color:#DC2626;font-weight:600;">↩ Returned to Seller</span>
            @else
                <div style="display:flex;flex-direction:column;gap:4px;">
                    @if($parcel['assigned_rider_user_id'])
                        <a class="monitor-button {{ $status === 'failed' ? 'rose' : 'subtle' }}" href="{{ route('logistics.chat', ['contact'=>$parcel['assigned_rider_user_id']]) }}">Message rider <span aria-hidden="true">↗</span></a>
                    @elseif($parcel['status'] === 'pending_receipt')
                        <a class="monitor-button subtle" href="{{ route('logistics.parcels', ['search'=>$parcel['tracking_code']]) }}">View incoming parcel</a>
                    @elseif($parcel['sort_status'] === 'unsorted')
                        <a class="monitor-button subtle" href="{{ route('logistics.sorting') }}">Go to sorting</a>
                    @else
                        <a class="monitor-button subtle" href="{{ route('logistics.assignments', ['area'=>$parcel['delivery_area'] ?: 'all']) }}">Assign a rider</a>
                    @endif
                    <details style="font-size:11px;margin-top:2px;">
                        <summary style="cursor:pointer;color:#7C3AED;font-weight:600;">Reschedule / Return</summary>
                        <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:6px;border-radius:6px;margin-top:4px;display:flex;flex-direction:column;gap:4px;">
                            <form method="POST" action="{{ route('logistics.parcels.reschedule', ['id' => $parcel['id']], false) }}" style="display:flex;gap:4px;align-items:center;">
                                @csrf
                                <select name="days" style="padding:2px 4px;font-size:10px;border:1px solid #CBD5E1;border-radius:4px;">
                                    <option value="2">2 Days</option>
                                    <option value="3" selected>3 Days</option>
                                </select>
                                <button type="submit" style="padding:2px 6px;font-size:10px;background:#F59E0B;color:#fff;border:none;border-radius:4px;cursor:pointer;">⏳ Reschedule</button>
                            </form>
                            <form method="POST" action="{{ route('logistics.parcels.return', ['id' => $parcel['id']], false) }}" onsubmit="return confirm('Cancel this order and return parcel to seller?');">
                                @csrf
                                <button type="submit" style="width:100%;padding:2px 6px;font-size:10px;background:#EF4444;color:#fff;border:none;border-radius:4px;cursor:pointer;">↩ Cancel & RTS</button>
                            </form>
                        </div>
                    </details>
                </div>
            @endif
        </td></tr>
        @empty<tr><td colspan="5"><div class="monitor-empty"><h3>No deliveries found</h3><p>Try another tracking number, name, or status.</p><a class="monitor-button" href="{{ route('logistics.monitoring') }}">View all deliveries</a></div></td></tr>@endforelse
        </tbody></table></div>
        @if($parcels->hasPages())<div class="monitor-pagination">{{ $parcels->links() }}</div>@endif
    </div>
    <p class="monitor-footnote">All update times are shown in Philippine time.</p>
</section>
@endsection
