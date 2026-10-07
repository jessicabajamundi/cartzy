@extends('layouts.admin')
@section('page_title', 'Reports')
@section('page_description', 'Filter marketplace orders by creation date and export the recorded figures.')
@section('content')
<section class="panel"><form class="filters" method="GET"><label>From<input type="date" name="from" value="{{ $from }}" required></label><label>To<input type="date" name="to" value="{{ $to }}" required></label><button class="button">Update report</button><button class="button subtle" name="export" value="csv">Export CSV</button></form></section>
<div class="stats-grid">
<article class="stat-card"><span>Store orders</span><strong>{{ number_format($summary['orders']) }}</strong><small>One order per participating store</small></article>
<article class="stat-card"><span>Completed merchandise sales</span><strong>₱{{ number_format($summary['sales'], 2) }}</strong><small>Paid and delivered/completed only</small></article>
<article class="stat-card"><span>Earned commission</span><strong>₱{{ number_format($summary['commission'], 2) }}</strong><small>Paid and delivered/completed only</small></article>
<article class="stat-card"><span>Cancelled store orders</span><strong>{{ number_format($summary['cancelled']) }}</strong><small>Within the selected period</small></article>
</div>
<section class="panel"><header class="panel-header"><div><h2>Order report</h2><p>{{ $from }} to {{ $to }}</p></div></header>@include('admin.partials.orders')@include('admin.partials.pagination', ['paginator' => $orders])</section>
@endsection
