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
    $needsPickup = $order->status === 'ready_to_ship' && (!$shipment || ($shipment->status === 'unassigned' && !$shipment->received_at));
    $awaitingRider = $order->status === 'ready_to_ship' && !$needsPickup;
    $address = $payment->shipping_address ?? [];
    $tracking = app(\App\Services\OrderTracking::class)->build($order);
    $manilaTime = fn ($date) => $date?->copy()->timezone('Asia/Manila')->format('M d, Y · h:i A');
    $shippedAt = $shipment?->deliveryEvents->firstWhere('status', 'picked_up')?->occurred_at;
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
        <small>{{ strtoupper($payment?->payment_method ?? 'COD') }} · {{ ucfirst(str_replace('_', ' ', $payment?->payment_status ?? 'pending')) }}</small>
        @if(($payment?->payment_method ?? 'cod') === 'cod')
            @if($shipment?->isCodRemitted())
                <small style="display:block; color:#16a34a; font-weight:700;">✓ COD Remitted by Hub ({{ strtoupper($shipment->cod_remittance_method ?? 'GCash') }})</small>
            @elseif($shipment?->isCodHolding())
                <small style="display:block; color:#ea580c; font-weight:700;">⏳ COD Escrow ({{ $shipment->codHoldingDaysRemaining() }}d holding)</small>
            @elseif($shipment?->isCodEligibleForRemittance())
                <small style="display:block; color:#0284c7; font-weight:700;">✓ 7d Passed · Awaiting Hub Remittance</small>
            @endif
        @else
            <small style="display:block; color:#007DFE; font-weight:700;">Direct Online Payment</small>
        @endif
        @php $latestPay = $payment?->payments?->first(); @endphp
        @if($latestPay?->provider_ref)
            <small style="display:block; color:#007DFE; font-weight:700;">Ref: {{ $latestPay->provider_ref }}</small>
        @endif
        @if($latestPay?->receipt_url)
            <a href="{{ $latestPay->receipt_url }}" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:3px; font-size:11px; color:#007DFE; text-decoration:underline; font-weight:700; margin-top:2px;">
                <span>View Receipt ↗</span>
            </a>
        @endif
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
                <a class="button small" href="{{ route('seller.courier', ['status' => 'ready_to_ship', 'order' => $order->id]) }}">Pickup or drop-off</a>
                @if($shipment?->isDropoff())<span class="so-hint">Drop off at the selected logistics partner</span>@endif
            @elseif($awaitingPayment)
                @if($latestPay && ($latestPay->provider_ref || $latestPay->receipt_path))
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <span class="so-hint" style="color:#d97706; font-weight:700; font-size:11px;">Verify Payment</span>
                        <div style="display:flex; gap:4px; flex-wrap:wrap;">
                            <form method="POST" action="{{ route('seller.orders.payment-verify', ['id' => $order->id]) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="decision" value="approved">
                                <button class="button small" type="submit" style="background:#16a34a; border-color:#16a34a; color:#fff;" onclick="return confirm('Approve this GCash payment receipt?');">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('seller.orders.payment-verify', ['id' => $order->id]) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="decision" value="rejected">
                                <button class="button small secondary" type="submit" style="color:#dc2626;" onclick="return confirm('Reject this GCash payment receipt?');">Reject</button>
                            </form>
                        </div>
                    </div>
                @else
                    <span class="so-hint">Waiting for payment</span>
                @endif
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
            <button type="button" class="so-details" popovertarget="so-details-{{ $order->id }}">Details</button>
            <a class="so-chat" href="{{ route('seller.chat', ['contact' => $order->id]) }}" aria-label="Message buyer for {{ $payment->reference }}" title="Message buyer">@include('seller.partials.icon', ['name' => 'chat', 'size' => 18])</a>
        </div>
        @if($order->status === 'cancelled' && $order->note)<small class="so-note">{{ $order->note }}</small>@endif
        <div class="so-popover" id="so-details-{{ $order->id }}" popover aria-label="Details for {{ $payment->reference }}">
            <h3>Delivery details</h3>
            <p>{{ $address['recipient'] ?? $payment->buyer?->name }} · {{ $address['phone'] ?? $payment->buyer?->phone ?? 'No phone number' }}</p>
            <p>{{ $address['address'] ?? implode(', ', array_filter(Arr::only($address, ['line1', 'barangay', 'city', 'province', 'postal_code']))) }}</p>
            @if($shippedAt)<p>Shipped: {{ $manilaTime($shippedAt) }}</p>@endif
            @if($order->delivered_at)<p>Delivered: {{ $manilaTime($order->delivered_at) }}</p>@endif
            <h3>Status history</h3>
            <ul class="so-history">
                <li><span>Placed</span><time>{{ $tracking['steps'][0]['time'] }}</time></li>
                @foreach(array_reverse($tracking['history']) as $event)
                    <li><span>{{ $event['label'] }}</span><time>{{ $event['time'] }}</time></li>
                @endforeach
            </ul>
            <a class="so-waybill-link" href="{{ route('seller.orders.waybill', $order) }}">View full waybill</a>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="6"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name'=>'bag'])</div><h3>No orders to show</h3><p>Orders for your store will appear here. If you used a filter, try another status or search.</p></div></td></tr>
@endforelse
</tbody></table></div>
@once
<script>
// Opens each order's details box next to its button, flipping up when there's no room below.
document.addEventListener('toggle', (event) => {
    const box = event.target;
    if (!box.classList?.contains('so-popover') || event.newState !== 'open') return;
    const button = document.querySelector(`[popovertarget="${box.id}"]`).getBoundingClientRect();
    const gap = 8, edge = 12, size = box.getBoundingClientRect();
    const below = button.bottom + gap;
    const top = below + size.height <= innerHeight - edge ? below : button.top - gap - size.height;
    box.style.top = `${Math.max(edge, top)}px`;
    box.style.left = `${Math.max(edge, Math.min(button.right - size.width, innerWidth - size.width - edge))}px`;
}, true);
window.addEventListener('scroll', () => {
    document.querySelectorAll('.so-popover:popover-open').forEach((box) => box.hidePopover());
}, { passive: true });
</script>
@endonce
