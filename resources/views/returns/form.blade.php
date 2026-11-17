<form method="POST" action="{{ route('returns.action', [$case->id, $action]) }}" class="return-form" @if($action === 'refund') enctype="multipart/form-data" @endif>
    @csrf
    @if($action === 'approve')
        <label>Refund amount (PHP)<input name="amount" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?" value="{{ number_format($case->amount_minor ?: $report->sellerOrder->buyerDueMinor(), 2, '.', '') / 100 }}" required></label>
        <small>Review the affected items and actual payment. Other approved refunds count toward the remaining balance. Approval does not send money.</small>
        @if($report->requested_resolution === 'return_refund' && !$case->received_at)
            <label>Seller return address and instructions<textarea name="return_address" rows="3" maxlength="2000" required>{{ $case->return_address ?? \App\Support\PlaceMatcher::describe($report->sellerOrder->seller->pickupAddress, withStreet: true) }}</textarea></label>
            <small>Include the recipient and contact details, and clarify any return shipping fee arrangements.</small>
        @endif
    @elseif($action === 'schedule')
        <label>Handover method<select name="handover_method" required><option value="pickup">Pick up from my address</option><option value="dropoff">I will drop off at the logistics hub</option></select></label>
        <label>Logistics partner<select name="logistics_provider_id" required><option value="">Choose a partner</option>@foreach($providers as $provider)<option value="{{ $provider->id }}">{{ $provider->name }} — {{ \App\Services\HubRecommender::location($provider) ?? 'Contact hub for address' }}</option>@endforeach</select></label>
        <label>Requested date<input type="date" name="scheduled_date" min="{{ now()->format('Y-m-d') }}" required></label>
        <label>Pickup address (required for pickup)<textarea name="pickup_address" rows="3" maxlength="2000">{{ $report->order->shipping_address['address'] ?? '' }}</textarea></label>
        <small>The logistics partner coordinates the return. Do not hand over the parcel until the approved return instructions are clear.</small>
    @elseif($action === 'dispatch')
        <label>Online return rider<select name="rider_id" required><option value="">Choose a rider</option>@foreach($riders->where('logistics_provider_id', $case->logistics_provider_id) as $rider)<option value="{{ $rider->id }}">{{ $rider->user?->name }}</option>@endforeach</select></label>
    @elseif($action === 'inspect')
        <label>Inspection result<select name="result" required><option value="accepted">Accepted — proceed to refund</option><option value="rejected">Rejected — explain findings</option></select></label>
    @elseif($action === 'refund')
        <p class="return-notice">Credit the approved refund to the buyer's Cartzy Wallet. It can pay for any Cartzy product and cannot be withdrawn. Approved amount: PHP {{ number_format($case->amount_minor / 100, 2) }}.</p>
        <input type="hidden" name="method" value="cartzy_wallet">
        <small>Submitting this form credits the wallet immediately and records a unique refund transaction.</small>

    @endif
    <label>{{ in_array($action, ['reject', 'appeal', 'inspect']) ? 'Reason / findings' : 'Notes' }}<textarea name="notes" rows="3" minlength="3" maxlength="2000" required></textarea></label>
    <button type="submit" class="return-button">{{ $label }}</button>
</form>
