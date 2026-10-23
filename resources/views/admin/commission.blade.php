@extends('layouts.admin')
@section('page_title', 'Commission')
@section('page_description', 'A flat '.(\App\Models\SellerOrder::COMMISSION_BPS / 100).'% commission on every item sold. Earnings count paid orders that have been delivered or completed.')
@section('content')
<div class="stats-grid three">
<article class="stat-card"><span>Merchandise sales</span><strong>₱{{ number_format($totals['sales'], 2) }}</strong><small>Excludes shipping fees</small></article>
<article class="stat-card"><span>Recorded commission</span><strong>₱{{ number_format($totals['commission'], 2) }}</strong><small>{{ \App\Models\SellerOrder::COMMISSION_BPS / 100 }}% of merchandise sales</small></article>
<article class="stat-card"><span>Seller net merchandise</span><strong>₱{{ number_format($totals['sales'] - $totals['commission'], 2) }}</strong><small>Sales less commission; not a payout record</small></article>
</div>
<section class="panel"><header class="panel-header"><div><h2>Commission ledger</h2><p>Historical amounts are preserved when seller rates change.</p></div></header>
@include('admin.partials.orders', ['orders' => $transactions])
@include('admin.partials.pagination', ['paginator' => $transactions])</section>
@endsection
