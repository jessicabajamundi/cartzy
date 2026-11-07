<div class="table-wrap"><table class="seller-orders-table"><thead><tr><th>Order</th><th>Buyer</th><th>Items</th><th>Total</th><th>Status</th><th>Next action</th></tr></thead><tbody>
@forelse($orders as $order)
@php
    $shipment = $order->shipment;
    $firstItem = $order->items->first();
    $moreItems = $order->items->count() - 1;
    // Once the parcel is on its way, the shipment says more than the order's "shipped".
    $statusLabel = $order->status === 'shipped' && $shipment ? $shipment->status : $order->status;
    $payment = $order->order;
    $handedOver = in_array($order->status, ['delivered', 'completed'], true)
        || ($shipment && !in_array($shipment->status, ['unassigned', 'assigned'], true));
    $canPack = in_array($order->status, ['pending', 'accepted', 'packed'], true)
        && !in_array($payment->payment_status, ['refunded', 'partially_refunded'], true)
        && ($payment->payment_method === 'cod' || $payment->payment_status === 'paid');
    $awaitingPayment = in_array($order->status, ['pending', 'accepted', 'packed'], true)
        && !$canPack && !in_array($payment->payment_status, ['refunded', 'partially_refunded'], true);
    $needsPickup = $order->status === 'ready_to_ship' && (!$shipment || $shipment->status === 'unassigned');
    $awaitingRider = $order->status === 'ready_to_ship' && !$needsPickup;
@endphp
<tr>
    <td class="so-order">
        <strong>{{ $payment->reference }}</strong>
        <small>{{ $order->created_at->format('M j, Y · g:i A') }}</small>
        @if($shipment?->tracking_code)<small class="so-tracking">{{ $shipment->tracking_code }}</small>@endif
    </td>
    <td class="so-buyer">
        <strong>{{ $payment->buyer?->name }}</strong>
    </td>
    <td class="so-items">
        @if($firstItem)
            <div class="so-item">
                <span class="so-thumb">
                    @if($firstItem->thumbnail_url)
                        <img src="{{ $firstItem->thumbnail_url }}" alt="" loading="lazy">
                    @else
                        @include('seller.partials.icon', ['name' => 'bag', 'size' => 20])
                    @endif
                </span>
                <span class="so-item-copy">
                    <strong>{{ $firstItem->product_name }}@if($firstItem->variant_name) — {{ $firstItem->variant_name }}@endif</strong>
                    <small>{{ $firstItem->quantity }} × ₱{{ number_format($firstItem->unit_price_minor / 100, 2) }}@if($moreItems > 0) · +{{ $moreItems }} more {{ Str::plural('item', $moreItems) }}@endif</small>
                </span>
                <span class="so-item-price">₱{{ number_format($firstItem->unit_price_minor * $firstItem->quantity / 100, 2) }}</span>
            </div>
        @endif
    </td>
    <td class="so-total">
        <strong>₱{{ number_format($order->buyerDueMinor() / 100, 2) }}</strong>
        <small>{{ strtoupper($payment->payment_method) }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_status)) }}</small>
    </td>
    <td class="so-status"><span class="badge {{ $statusLabel }}">{{ ucfirst(str_replace('_', ' ', $statusLabel)) }}</span></td>
    <td class="so-next">
        <div class="so-actions">
            @if($order->cancellation_status === 'pending')
                <div class="so-cancellation">
                    <strong>Cancellation requested</strong>
                    <small>{{ $order->cancellation_reason }}</small>
                    <form method="POST" action="{{ route('seller.orders.cancellation', $order) }}" class="inline">@csrf
                        @if($order->canBeCancelledByBuyer())<button class="button small" name="decision" value="approved" type="submit">Approve cancellation</button>@endif
                        <button class="button small secondary" name="decision" value="rejected" type="submit">Decline request</button>
                    </form>
                </div>
            @elseif($canPack)
                <form method="POST" action="{{ route('seller.orders.pack', $order) }}">@csrf<button class="button small" type="submit">Mark packed</button></form>
            @elseif($needsPickup)
                <a class="button small" href="{{ route('seller.courier', ['status' => 'ready_to_ship']) }}">Request pickup</a>
            @elseif($awaitingPayment)
                <span class="so-hint">Waiting for payment</span>
            @elseif($awaitingRider)
                <span class="so-hint">Waiting for rider pickup</span>
            @else
                <span class="so-hint">No action needed</span>
            @endif
            @if($handedOver)
                <span class="so-handover">@include('seller.partials.icon', ['name' => 'check', 'size' => 18])<span>Handover confirmed</span></span>
            @endif
            @if($shipment && !in_array($order->status, ['cancelled', 'delivered', 'completed'], true) && $shipment->status !== 'delivered')
                <a class="button small secondary so-print" href="{{ route('seller.orders.waybill', ['id' => $order->id, 'print' => 1]) }}">@include('seller.partials.icon', ['name' => 'printer', 'size' => 16])<span>Print waybill</span></a>
            @endif
            <a class="so-details" href="{{ route('seller.orders.waybill', $order) }}">Details</a>
            <a class="so-chat" href="{{ route('seller.chat', ['contact' => $order->id]) }}" aria-label="Message buyer for {{ $payment->reference }}" title="Message buyer">@include('seller.partials.icon', ['name' => 'chat', 'size' => 18])</a>
        </div>
        @if($order->status === 'cancelled' && $order->note)<small class="so-note">{{ $order->note }}</small>@endif
    </td>
</tr>
@empty
<tr><td colspan="6"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name'=>'bag'])</div><h3>No orders to show</h3><p>Orders for your store will appear here. If you used a filter, try another status or search.</p></div></td></tr>
@endforelse
</tbody></table></div>
