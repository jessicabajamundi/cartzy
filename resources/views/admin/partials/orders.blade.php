<div class="table-scroll"><table>
<thead><tr><th>Order / buyer</th><th>Store</th><th>Date</th><th>Status</th><th class="numeric">Merchandise</th><th class="numeric">Recorded commission</th></tr></thead>
<tbody>
@forelse($orders as $order)
<tr><td><strong>{{ $order->order->reference }}</strong><small>{{ $order->order->buyer?->name ?? 'Unavailable buyer' }}</small></td><td>{{ $order->seller?->name ?? 'Unavailable store' }}</td><td class="nowrap">{{ $order->created_at->format('M j, Y') }}</td><td><span class="badge {{ in_array($order->status, ['delivered', 'completed']) ? 'good' : '' }}">{{ str_replace('_', ' ', $order->status) }}</span><small>Payment: {{ str_replace('_', ' ', $order->order->payment_status) }}</small></td><td class="numeric">₱{{ number_format($order->subtotal, 2) }}</td><td class="numeric">₱{{ number_format($order->commission, 2) }}</td></tr>
@empty<tr><td colspan="6"><div class="empty"><strong>No orders to display</strong><p>Recorded orders will appear here.</p></div></td></tr>@endforelse
</tbody></table></div>
