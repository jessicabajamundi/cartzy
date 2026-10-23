@extends('layouts.logistics')
@section('title', 'Incoming Parcels | Logistics Hub | cartzy')
@section('page_title', 'Incoming Parcels')
@section('page_subtitle', 'Receive arriving parcels and get them ready for sorting')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-parcels.css') }}?v={{ filemtime(public_path('css/logistics-parcels.css')) }}">
@endpush
@section('content')
<section class="parcel-workspace" aria-label="Incoming parcels">
    <header class="parcel-heading"><div><span class="parcel-eyebrow">HUB RECEIVING</span><h2>Receive. Check. Keep things moving.</h2><p>Find the parcel, check its details, then mark it received when it arrives.</p></div><a class="parcel-button parcel-secondary" href="{{ route('logistics.sorting') }}">Go to sorting <span aria-hidden="true">→</span></a></header>
    <div class="parcel-metrics">
        <a class="parcel-metric attention" href="{{ route('logistics.parcels', ['status'=>'pending_receipt']) }}"><span>Awaiting receipt <span aria-hidden="true">↗</span></span><strong>{{ $counts['pending_receipt'] }}</strong><small>Mark received when the parcel arrives</small></a>
        <a class="parcel-metric" href="{{ route('logistics.parcels', ['status'=>'received']) }}"><span>Received <span aria-hidden="true">↗</span></span><strong>{{ $counts['received'] }}</strong><small>Parcels with a confirmed receipt</small></a>
        <a class="parcel-metric blended" href="{{ route('logistics.parcels', ['status'=>'at_hub']) }}"><span>Currently at hub <span aria-hidden="true">↗</span></span><strong>{{ $counts['at_hub'] }}</strong><small>Received and awaiting dispatch</small></a>
    </div>
    <div class="parcel-panel">
        <form class="parcel-search" method="GET" action="{{ route('logistics.parcels') }}"><input type="hidden" name="status" value="{{ $filter }}"><div><label for="parcel-search">Find a parcel</label><input type="search" id="parcel-search" name="search" value="{{ $search }}" placeholder="Tracking number, seller, recipient or address"></div><button class="parcel-button parcel-primary" type="submit">Find parcel</button>@if($search !== '')<a class="parcel-clear" href="{{ route('logistics.parcels', ['status'=>$filter]) }}">Clear search</a>@endif</form>
        <nav class="parcel-tabs" aria-label="Filter parcels">@foreach(['all'=>'All parcels','pending_receipt'=>'Awaiting receipt','received'=>'Received','at_hub'=>'At hub'] as $key=>$label)<a href="{{ route('logistics.parcels', ['status'=>$key, 'search'=>$search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>@endforeach</nav>
        <div class="parcel-results"><span>{{ $parcels->total() }} {{ Str::plural('parcel', $parcels->total()) }}@if($search !== '') matching “{{ $search }}”@endif</span><span><i aria-hidden="true"></i> Awaiting receipt shown first</span></div>
        <div class="parcel-table-wrap"><table class="parcel-table"><caption class="parcel-sr">Incoming parcels, delivery details and receiving actions</caption><thead><tr><th scope="col">Parcel & seller</th><th scope="col">Deliver to</th><th scope="col">Payment</th><th scope="col">Receipt status</th><th scope="col">Next step</th></tr></thead><tbody>
        @forelse($parcels as $parcel)
        <tr class="{{ $parcel['status'] === 'pending_receipt' ? 'parcel-awaiting' : '' }}"><td data-label="Parcel"><strong class="parcel-code">{{ $parcel['tracking_code'] }}</strong><span class="parcel-seller">{{ $parcel['seller_name'] }}</span><details class="parcel-details"><summary>View parcel details</summary><div><span class="parcel-detail-label">Contents</span><p>{{ $parcel['items'] }}</p><span class="parcel-detail-label">Weight</span><p>{{ $parcel['weight_kg'] }} kg</p></div></details></td>
        <td data-label="Deliver to"><strong>{{ $parcel['recipient_name'] }}</strong><p class="parcel-address">{{ $parcel['recipient_address'] }}</p></td>
        <td data-label="Payment">@if($parcel['payment_method']==='COD')<strong class="parcel-cod">₱{{ number_format($parcel['cod_amount'],2) }}</strong><small>Cash on delivery</small>@else<span class="parcel-prepaid">Prepaid</span><small>No cash to collect</small>@endif</td>
        <td data-label="Receipt status"><span class="parcel-badge {{ $parcel['status'] === 'pending_receipt' ? 'pending' : '' }}">{{ $parcel['status'] === 'pending_receipt' ? 'Awaiting receipt' : 'Received' }}</span>@if($parcel['received_at'])<small>{{ $parcel['received_at'] }}</small>@endif</td>
        <td data-label="Next step">@if($parcel['status'] === 'pending_receipt')<form class="parcel-receive-form" method="POST" action="{{ route('logistics.parcels.receive', ['id'=>$parcel['id']], false) }}">@csrf<button type="submit" class="parcel-button parcel-primary" aria-label="Mark {{ $parcel['tracking_code'] }} received"><span aria-hidden="true">✓</span> Mark received</button></form>@else<span class="parcel-complete"><span aria-hidden="true">✓</span> Receipt confirmed</span>@endif</td></tr>
        @empty<tr><td colspan="5"><div class="parcel-empty"><span aria-hidden="true">⌕</span><h3>{{ $search !== '' ? 'No matching parcels' : 'No parcels in this view' }}</h3><p>{{ $search !== '' ? 'Check the tracking number or try a different name.' : 'Choose another status to view your hub’s parcels.' }}</p><a class="parcel-button parcel-secondary" href="{{ route('logistics.parcels') }}">View all parcels</a></div></td></tr>@endforelse
        </tbody></table></div>
        @if($parcels->hasPages())<div class="parcel-pagination">{{ $parcels->links() }}</div>@endif
    </div>
</section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.parcel-receive-form').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        const button = form.querySelector('button');
        button.disabled = true;
        button.textContent = 'Receiving…';
    });
});
window.addEventListener('pageshow', () => document.querySelectorAll('.parcel-receive-form').forEach(form => {
    delete form.dataset.submitting;
    const button = form.querySelector('button');
    button.disabled = false;
    button.textContent = '✓ Mark received';
}));
</script>
@endpush
