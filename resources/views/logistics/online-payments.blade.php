@extends('layouts.logistics')
@section('title', 'Online payments & refunds | Cartzy')
@section('page_title', 'Online payments & refunds')
@section('page_description', 'Payment verification and refund records for parcels handled by your hub.')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-payments.css') }}?v={{ filemtime(public_path('css/logistics-payments.css')) }}">
@endpush
@section('content')
<div class="payment-ledger">
    <h1>Online payments & refunds</h1>
    <p class="ledger-muted">Follow the payment reference, seller verification, return progress and completed refund for each parcel.</p>
    <div class="ledger-notice"><strong>Direct GCash payment to seller</strong><p>These are manually verified transfers, not gateway-confirmed payments. The logistics hub does not hold this money. Administration must arrange and record completed online refunds with payment evidence. Seller receipt of funds is separate from COD remittance; no online payout hold is enforced by this system.</p></div>
    <form method="GET" class="ledger-filters">
        <label>Search<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order, tracking, seller or payment reference"></label>
        <label>Payment status<select name="status"><option value="">All statuses</option>@foreach(['pending' => 'Awaiting verification', 'paid' => 'Seller verified', 'payment_rejected' => 'Payment rejected', 'partially_refunded' => 'Partially refunded', 'refunded' => 'Refunded'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Orders from<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
        <label>Orders to<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
        <button class="button" type="submit">Filter</button><a href="{{ route('logistics.online_payments') }}">Reset</a>
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    @forelse($orders as $parcel)
        @php($order = $parcel->order)
        <article class="ledger-card">
            <header><div><h2>{{ $order->reference }}</h2><p>{{ $parcel->seller->name }} &middot; {{ $parcel->shipment?->tracking_code ?? 'Awaiting shipment' }}</p></div><span class="ledger-status">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</span></header>
            <dl class="ledger-facts"><div><dt>This store's order amount</dt><dd>PHP {{ number_format($parcel->buyerDueMinor() / 100, 2) }}</dd></div><div><dt>Delivery</dt><dd>{{ ucfirst(str_replace('_', ' ', $parcel->shipment?->status ?? $parcel->status)) }}</dd></div><div><dt>Seller funds</dt><dd>Paid directly to seller when verified; not held by logistics</dd></div></dl>
            <div class="ledger-table"><table><caption>Payment records for this buyer order</caption><thead><tr><th>Method / submitted</th><th>Whole-order amount</th><th>Reference / status</th><th>Verification</th><th>Evidence</th></tr></thead><tbody>
            @forelse($order->payments->sortByDesc('id') as $payment)
                <tr><td>{{ strtoupper($payment->method) }}<small>{{ $payment->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}</small></td><td>PHP {{ number_format($payment->amount_minor / 100, 2) }}</td><td>{{ $payment->provider_ref ?: 'No reference submitted' }}<small>{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</small></td><td>{{ $payment->verifier?->name ?? 'Not verified' }}<small>{{ $payment->verified_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}</small>@if($payment->verification_notes)<small>{{ $payment->verification_notes }}</small>@endif</td><td>@if($payment->receipt_path)<a href="{{ route('logistics.online_payments.receipt', [$parcel->id, $payment->id]) }}">Download buyer receipt</a>@else No receipt @endif</td></tr>
            @empty<tr><td colspan="5">No payment record found. A receipt alone does not confirm payment.</td></tr>@endforelse
            </tbody></table></div>
            <h3>Returns & refunds</h3>
            @forelse($parcel->reports->filter(fn ($report) => $report->returnCase) as $report)
                @php($case = $report->returnCase)
                <div class="ledger-case"><a href="{{ route('returns.index', ['case' => $case->id]) }}">Case #{{ $case->id }}</a><strong>{{ ucfirst(str_replace('_', ' ', $case->status)) }}</strong><p>{{ $report->subject }} &middot; Requested {{ $case->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}</p><p>Case amount: PHP {{ number_format($case->amount_minor / 100, 2) }} &middot; Return tracking: {{ $case->tracking_code ?: 'Not scheduled' }}</p>
                @if($case->refund)<p>Completed refund: PHP {{ number_format($case->refund->amount_minor / 100, 2) }} via {{ strtoupper($case->refund->method) }} &middot; Reference {{ $case->refund->reference }}<br>Paid {{ $case->refund->paid_at->timezone('Asia/Manila')->format('M j, Y g:i A') }} &middot; Recorded by {{ $case->refund->recorder?->name ?? 'Former staff member' }}<br><a href="{{ route('returns.proof', $case->id) }}">Download refund receipt</a></p>
                @else<p class="ledger-muted">No completed refund has been recorded. Approval is not proof of payment.</p>@endif
                <details><summary>Case audit history</summary><ol>@foreach($case->events as $event)<li>{{ $event->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }} &middot; {{ $event->actor?->name ?? 'Former account' }} &middot; {{ $event->notes }}</li>@endforeach</ol></details></div>
            @empty<p class="ledger-muted">No return or refund request for this parcel.</p>@endforelse
        </article>
    @empty<div class="ledger-card">No online payment records match your filters.</div>@endforelse
    {{ $orders->links() }}
</div>
@endsection
