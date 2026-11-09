@extends('layouts.seller')
@section('page_title', 'Shipping')
@section('content')
@php
    $recommended = $rankedProviders->firstWhere('recommended', true);
@endphp
<style>
.handover{display:flex;gap:10px;flex-wrap:wrap;border:0;padding:0;margin:0}
.handover legend{font-size:12px;font-weight:600;margin-bottom:6px}
.handover-option{flex:1 1 220px;display:flex;gap:10px;align-items:flex-start;border:1px solid var(--line);border-radius:12px;padding:12px 14px;cursor:pointer;background:var(--surface)}
.handover-option:has(input:checked){border-color:var(--accent);background:var(--accent-light)}
.handover-option input{margin-top:3px}
.handover-option strong{display:block;font-size:13px}
.handover-option small{display:block;color:var(--muted);font-size:11px;margin-top:2px}
.hub-hint{font-size:12px;color:var(--muted);margin:6px 0 0}
.dropoff-where{border:1px dashed var(--line);border-radius:10px;padding:10px 12px;font-size:12px;margin-top:8px}
.shipping-form{display:grid;gap:12px;margin-top:12px}
.shipping-form .row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
.shipping-form .row label{flex:1 1 200px;min-width:0}
.shipping-form select{max-width:100%}
</style>

<div class="page-heading"><div><div class="eyebrow">FULFILLMENT</div><h1>Shipping & pickup</h1><p class="subtitle">Choose a logistics hub for each packed order, then have a courier pick it up or drop it off at the hub yourself.</p></div></div>
@if(request()->filled('status'))
<div class="notice">Showing {{ request('status') === 'ready_to_ship' ? 'orders ready for pickup' : 'shipped orders' }}. <a href="{{ route('seller.courier') }}">View all shipments</a></div>
@endif

@if($providers->isNotEmpty())
<div class="notice">
    @if(!$pickupAddress)
        You haven't set a pickup address, so we can't tell which hub is closest. Add one in your shop settings to get a recommendation.
    @elseif($recommended)
        Closest hub to your pickup address ({{ \App\Support\PlaceMatcher::describe($pickupAddress) }}): <strong>{{ $recommended['provider']->name }}</strong> · {{ $recommended['reason'] }}@if($recommended['location']) · {{ $recommended['location'] }}@endif
    @else
        No hub lists an office or service area near your pickup address ({{ \App\Support\PlaceMatcher::describe($pickupAddress) }}). Pick the hub you prefer.
    @endif
</div>
@endif

@forelse($orders as $order)
@php
    $shipment = $order->shipment;
    $isDropoff = $shipment?->isDropoff() && !$shipment->received_at;
    $statusLabel = match (true) {
        !$shipment => 'Awaiting pickup request',
        $isDropoff && $shipment->status === 'unassigned' => 'Drop-off at hub',
        $shipment->status === 'unassigned' && $shipment->received_at !== null => 'At hub',
        default => ucfirst(str_replace('_', ' ', $shipment->status)),
    };
    $hub = $shipment?->logisticsProvider;
    $hubWhere = $hub ? \App\Services\HubRecommender::location($hub) : null;
    $selectedId = old('logistics_provider_id', $order->logistics_provider_id ?? $recommended['provider']->id ?? null);
    $selectedMethod = old('handover_method', $shipment?->handover_method ?? 'pickup');
@endphp
<section class="panel shipping-card">
    <div class="page-heading"><div><h2>{{ $order->order->reference }}</h2><p class="subtitle">{{ $order->order->buyer?->name }} · {{ $shipment?->tracking_code ?? 'Tracking not assigned' }}</p></div><span class="badge">{{ $statusLabel }}</span></div>
    <p class="muted">{{ $hub?->name ?? 'Choose a logistics hub' }}@if($shipment?->pickupRider?->user) · Pickup courier: {{ $shipment->pickupRider->user->name }}@endif @if($shipment?->rider?->user) · Rider: {{ $shipment->rider->user->name }}@endif</p>
    @if($isDropoff && $hub)
        <div class="dropoff-where"><strong>Bring this parcel to {{ $hub->name }}</strong><br>{{ $hubWhere ?? 'The hub has not added its office address yet. Message the hub for directions.' }}</div>
    @endif
    @if($order->note)<p class="subtitle preserve">{{ $order->note }}</p>@endif

    @if($order->status === 'ready_to_ship' && (!$shipment || ($shipment->status === 'unassigned' && !$shipment->received_at)))
        @if($providers->isNotEmpty())
        <form class="shipping-form" method="POST" action="{{ route('seller.courier.schedule', $order) }}" data-shipping-form>
            @csrf
            <fieldset class="handover">
                <legend>How will the parcel reach the hub?</legend>
                <label class="handover-option"><input type="radio" name="handover_method" value="pickup" @checked($selectedMethod === 'pickup')><span><strong>Pick-up</strong><small>A courier from the hub collects it from your pickup address.</small></span></label>
                <label class="handover-option"><input type="radio" name="handover_method" value="dropoff" @checked($selectedMethod === 'dropoff')><span><strong>Drop-off</strong><small>You bring it to the hub yourself. No courier is sent.</small></span></label>
            </fieldset>
            <div class="row">
                <label>Logistics hub
                    <select name="logistics_provider_id" required data-hub-select>
                        @foreach($rankedProviders as $ranked)
                            @php $p = $ranked['provider']; @endphp
                            <option value="{{ $p->id }}" data-where="{{ $ranked['location'] }}" @selected((int) $selectedId === $p->id)>{{ $ranked['recommended'] ? '★ ' : '' }}{{ $p->name }}{{ $ranked['reason'] ? ' · '.$ranked['reason'] : '' }}{{ $ranked['recommended'] ? ' (Recommended)' : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span data-date-label>{{ $selectedMethod === 'dropoff' ? 'Drop-off date' : 'Requested pickup date' }}</span><input type="date" name="pickup_date" min="{{ now()->format('Y-m-d') }}" value="{{ old('pickup_date', now()->format('Y-m-d')) }}" required></label>
                <label>Notes<input name="notes" maxlength="1000" placeholder="Optional instructions" value="{{ old('notes') }}"></label>
                <button class="button" type="submit" data-submit>{{ $selectedMethod === 'dropoff' ? 'Save drop-off' : 'Request pickup' }}</button>
            </div>
            <p class="hub-hint" data-dropoff-where @if($selectedMethod !== 'dropoff') hidden @endif></p>
        </form>
        <p class="subtitle">Pick-up is subject to courier confirmation. Neither choice marks the order as shipped until the parcel is picked up or received at the hub.</p>
        @else<p class="notice">No approved logistics providers are available yet.</p>@endif
    @endif
</section>
@empty<div class="panel empty"><div class="empty-icon">@include('seller.partials.icon',['name'=>'truck'])</div><h3>No shipments to manage</h3><p>Mark an order as packed to prepare it for courier pickup.</p><a class="button secondary" href="{{ route('seller.orders') }}">View orders</a></div>@endforelse
@include('seller.partials.pagination',['paginator'=>$orders])

<script>
document.querySelectorAll('[data-shipping-form]').forEach(function (form) {
    var hub = form.querySelector('[data-hub-select]');
    var where = form.querySelector('[data-dropoff-where]');
    var dateLabel = form.querySelector('[data-date-label]');
    var submit = form.querySelector('[data-submit]');
    function update() {
        var checked = form.querySelector('input[name="handover_method"]:checked');
        var dropoff = checked && checked.value === 'dropoff';
        var option = hub.options[hub.selectedIndex];
        var address = option ? option.getAttribute('data-where') : '';
        dateLabel.textContent = dropoff ? 'Drop-off date' : 'Requested pickup date';
        submit.textContent = dropoff ? 'Save drop-off' : 'Request pickup';
        where.hidden = !dropoff;
        where.textContent = dropoff
            ? (address ? 'Drop-off address: ' + address : 'This hub has not added its office address yet. Message the hub for directions.')
            : '';
    }
    form.addEventListener('change', update);
    update();
});
</script>
@endsection
