@extends('layouts.seller')
@section('page_title', 'Shipping')
@section('content')
<div class="page-heading"><div><div class="eyebrow">FULFILLMENT</div><h1>Shipping & pickup</h1><p class="subtitle">Request collection for packed orders and follow their shipment status.</p></div></div>
@forelse($orders as $order)
<section class="panel shipping-card"><div class="page-heading"><div><h2>{{ $order->order->reference }}</h2><p class="subtitle">{{ $order->order->buyer?->name }} · {{ $order->shipment?->tracking_code ?? 'Tracking not assigned' }}</p></div><span class="badge">{{ ucfirst(str_replace('_',' ',$order->shipment?->status ?? 'Awaiting pickup request')) }}</span></div><p class="muted">{{ $order->shipment?->logisticsProvider?->name ?? 'Choose a logistics provider' }}@if($order->shipment?->rider?->user) · Rider: {{ $order->shipment->rider->user->name }}@endif</p>@if($order->note)<p class="subtitle preserve">{{ $order->note }}</p>@endif
@if($order->status === 'ready_to_ship' && (!$order->shipment || $order->shipment->status === 'unassigned'))
@if($providers->isNotEmpty())<form class="filters" method="POST" action="{{ route('seller.courier.schedule',$order) }}">@csrf<label>Logistics provider<select name="logistics_provider_id" required>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($order->logistics_provider_id === $provider->id)>{{ $provider->name }}</option>@endforeach</select></label><label>Requested pickup date<input type="date" name="pickup_date" min="{{ now()->format('Y-m-d') }}" value="{{ old('pickup_date',now()->format('Y-m-d')) }}" required></label><label>Pickup notes<input name="notes" maxlength="1000" placeholder="Optional instructions"></label><button class="button" type="submit">Request pickup</button></form><p class="subtitle">Collection is subject to courier confirmation. Requesting pickup does not mark an order as shipped.</p>@else<p class="notice">No approved logistics providers are available yet.</p>@endif
@endif
</section>
@empty<div class="panel empty"><div class="empty-icon">@include('seller.partials.icon',['name'=>'truck'])</div><h3>No shipments to manage</h3><p>Mark an order as packed to prepare it for courier pickup.</p><a class="button secondary" href="{{ route('seller.orders') }}">View orders</a></div>@endforelse
@include('seller.partials.pagination',['paginator'=>$orders])
@endsection
