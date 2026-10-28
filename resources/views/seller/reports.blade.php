@extends('layouts.seller')
@section('page_title', 'Sales reports')
@section('content')
<div class="page-heading"><div><div class="eyebrow">BUSINESS INSIGHTS</div><h1>Sales reports</h1><p class="subtitle">Paid, delivered orders with the commission recorded at checkout.</p></div><a class="button secondary" href="{{ route('seller.reports',['from_date'=>$from,'to_date'=>$to,'export'=>'csv']) }}">↓ Export CSV</a></div>
<form class="filters" method="GET"><label>From<input type="date" name="from_date" value="{{ $from }}" required></label><label>To<input type="date" name="to_date" value="{{ $to }}" required></label><button class="button" type="submit">Update report</button></form>
<div class="metrics sales-report-metrics">
    @foreach([
        ['Gross sales', $summary['gross'], 'chart', 'gross', 'Before deductions'],
        ['Net sales', $summary['gross']-$summary['commission']-$summary['shop_vouchers'], 'trending', 'net', 'After commission & store promotions'],
        ['Commission', $summary['commission'], 'tag', 'commission', 'Recorded at checkout'],
        ['Your vouchers & free shipping', $summary['shop_vouchers'], 'ticket', 'promotions', 'Promotions funded by your store'],
    ] as [$label, $value, $icon, $tone, $detail])
        <div class="metric sales-report-card sales-report-card-{{ $tone }}">
            <div class="metric-top"><span>{{ $label }}</span><span class="sales-report-icon">@include('seller.partials.icon', ['name' => $icon])</span></div>
            <div class="metric-value">₱{{ number_format($value/100,2) }}</div>
            <small>{{ $detail }}</small>
        </div>
    @endforeach
    <div class="metric sales-report-card sales-report-card-orders">
        <div class="metric-top"><span>Paid & delivered orders</span><span class="sales-report-icon">@include('seller.partials.icon', ['name' => 'check'])</span></div>
        <div class="metric-value">{{ number_format($summary['count']) }}</div>
        <small>Completed in the selected period</small>
    </div>
</div>
<p class="subtitle" style="margin-bottom:20px">Net sales are gross sales less commission and the shop vouchers and free shipping your store paid for. They exclude shipping and are not a withdrawable wallet balance. Pending, cancelled and refunded payments are excluded.</p>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Order</th><th>Delivery date</th><th>Gross sales</th><th>Net sales</th><th>Commission</th><th>Your vouchers</th></tr></thead><tbody>@forelse($orders as $order)<tr><td>{{ $order->order->reference }}</td><td>{{ $order->delivered_at?->format('M j, Y') }}</td><td class="amount">₱{{ number_format($order->subtotal_minor/100,2) }}</td><td class="amount">₱{{ number_format($order->sellerNetMinor()/100,2) }}</td><td class="amount">₱{{ number_format($order->commission_minor/100,2) }}</td><td class="amount">{{ $order->sellerFundedMinor() ? '−₱'.number_format($order->sellerFundedMinor()/100,2) : '—' }}</td></tr>@empty<tr><td colspan="6"><div class="empty"><h3>No sales in this period</h3><p>Try a different date range. Paid orders appear here once delivered.</p></div></td></tr>@endforelse</tbody></table></div>@include('seller.partials.pagination',['paginator'=>$orders])</section>
@endsection
