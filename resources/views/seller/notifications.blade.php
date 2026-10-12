@extends('layouts.seller')
@section('page_title', 'Notifications')
@section('content')
<div class="page-heading">
    <div><div class="eyebrow">STORE UPDATES</div><h1>Notifications</h1><p class="subtitle">Cancellation requests, product updates, and stock alerts for your store.</p></div>
</div>
<section class="panel" aria-label="Seller notifications">
    <div class="panel-header"><h2>Recent notifications</h2><span class="muted" data-notification-status role="status">Updates automatically every 15 seconds.</span></div>
    <div data-notification-items>@include('seller.partials.notification-items')</div>
</section>
@endsection
