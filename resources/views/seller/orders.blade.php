@extends('layouts.seller')
@section('page_title', 'Orders')
@section('content')
<div class="page-heading"><div><div class="eyebrow">ORDER MANAGEMENT</div><h1>Your orders</h1><p class="subtitle">Prepare customer orders and keep fulfillment moving.</p></div><a class="button secondary" href="{{ route('seller.courier') }}">Manage shipping →</a></div>
<form class="filters" method="GET"><label>Search orders<input name="search" value="{{ request('search') }}" placeholder="Order number or customer"></label><label>Order status<select name="status">@foreach(['all','to_prepare','pending','accepted','packed','ready_to_ship','shipped','delivered','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status','all') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></label><button class="button" type="submit">Apply filters</button><a class="button secondary" href="{{ route('seller.orders') }}">Reset</a></form>
<section class="panel">@include('seller.partials.orders-table')@include('seller.partials.pagination',['paginator'=>$orders])</section>
@endsection
