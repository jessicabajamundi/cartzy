<div class="table-scroll"><table>
<thead><tr><th>Order / buyer</th><th>Products</th><th>Store</th><th>Date</th><th>Status</th><th class="numeric">Merchandise</th><th class="numeric">Recorded commission</th></tr></thead>
<tbody>
@forelse($orders as $order)
<tr>
    <td><strong>{{ $order->order->reference }}</strong><small>{{ $order->order->buyer?->name ?? 'Unavailable buyer' }}</small></td>
    <td>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @forelse($order->items as $item)
                @php
                    $itemImg = $item->thumbnail_url ?: asset('images/logo-transparent.png');
                @endphp
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); flex-shrink: 0; background: #fff;" loading="lazy">
                    <div style="min-width: 0; max-width: 240px;">
                        <strong style="display: block; font-size: 13px; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $item->product_name }}">
                            {{ $item->product_name }}
                        </strong>
                        <small style="display: block; color: var(--muted); font-size: 11.5px;">
                            @if($item->variant_name && $item->variant_name !== 'Default') {{ $item->variant_name }} &middot; @endif
                            Qty: {{ $item->quantity }}
                        </small>
                    </div>
                </div>
            @empty
                <span class="muted" style="font-size: 12px;">No items recorded</span>
            @endforelse
        </div>
    </td>
    <td>{{ $order->seller?->name ?? 'Unavailable store' }}</td>
    <td class="nowrap">{{ $order->created_at->format('M j, Y') }}</td>
    <td><span class="badge {{ in_array($order->status, ['delivered', 'completed']) ? 'good' : '' }}">{{ str_replace('_', ' ', $order->status) }}</span><small>Payment: {{ str_replace('_', ' ', $order->order->payment_status) }}</small></td>
    <td class="numeric">₱{{ number_format($order->subtotal, 2) }}</td>
    <td class="numeric">₱{{ number_format($order->commission, 2) }}</td>
</tr>
@empty<tr><td colspan="7"><div class="empty"><strong>No orders to display</strong><p>Recorded orders will appear here.</p></div></td></tr>@endforelse
</tbody></table></div>
