@extends('layouts.seller')
@section('page_title', 'Deliveries')
@section('content')
<div class="page-heading"><div><div class="eyebrow">FULFILLMENT HISTORY</div><h1>Delivered orders</h1><p class="subtitle">Delivery updates recorded by your courier and customers.</p></div><a class="button secondary" href="{{ route('seller.reports') }}">View sales report →</a></div>
<section class="panel">@include('seller.partials.orders-table')@include('seller.partials.pagination',['paginator'=>$orders])</section>
@endsection
