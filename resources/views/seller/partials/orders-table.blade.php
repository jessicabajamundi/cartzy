<div class="table-wrap"><table><thead><tr><th>Order / Customer</th><th>Items</th><th>Subtotal</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td><strong>{{ $order->order->reference }}</strong><small>{{ $order->order->buyer?->name }} · {{ $order->created_at->format('M j, Y') }}</small></td><td>{{ $order->items->sum('quantity') }} items<small>{{ Str::limit($order->items->first()?->product_name, 34) }}</small></td><td class="amount">₱{{ number_format($order->subtotal_minor / 100, 2) }}</td><td><span class="badge {{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>@if(in_array($order->status, ['delivered', 'completed']))<small style="color:#059669; font-weight:700; display:block; margin-top:2px;">✓ Rider Scanned</small>@endif</td><td><span class="badge {{ $order->order->payment_status }}">{{ ucfirst(str_replace('_', ' ', $order->order->payment_status)) }}</span><small>{{ strtoupper($order->order->payment_method) }}</small></td><td><div class="inline">
@if($order->cancellation_status === 'pending')
<div><strong>Cancellation requested</strong><small>{{ $order->cancellation_reason }}</small>
<form method="POST" action="{{ route('seller.orders.cancellation', $order) }}" class="inline">@csrf
@if($order->canBeCancelledByBuyer())<button class="button small" name="decision" value="approved" type="submit">Approve cancellation</button>@endif
<button class="button small secondary" name="decision" value="rejected" type="submit">Decline request</button>
</form></div>
@elseif(in_array($order->status, ['pending','accepted','packed']) && !in_array($order->order->payment_status, ['refunded','partially_refunded']) && ($order->order->payment_method === 'cod' || $order->order->payment_status === 'paid'))<form method="POST" action="{{ route('seller.orders.pack', $order) }}">@csrf<button class="button small" type="submit">Mark packed</button></form>@endif
@if($order->status === 'cancelled' && $order->note)<small style="white-space: pre-line">{{ $order->note }}</small>@endif
<a class="button small secondary" href="{{ route('seller.orders.waybill', $order) }}">Details</a><a href="{{ route('seller.chat', ['contact' => $order->id]) }}" aria-label="Message buyer for {{ $order->order->reference }}">@include('seller.partials.icon', ['name'=>'chat'])</a></div></td></tr>
@empty
<tr><td colspan="6"><div class="empty"><div class="empty-icon">@include('seller.partials.icon', ['name'=>'bag'])</div><h3>No orders to show</h3><p>Orders for your store will appear here. If you used a filter, try another status or search.</p></div></td></tr>
@endforelse
</tbody></table></div>
