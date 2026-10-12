<article data-status="{{ $o['status'] }}" class="buyer-order-card">
    <div class="buyer-order-header">
        <div class="buyer-order-store"><span class="buyer-store-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18l-2-7H5l-2 7ZM5 10v11h14V10M9 21v-7h6v7"/></svg></span><div><p>{{ $o['store'] }}</p><span>{{ $o['id'] }} &middot; {{ $o['date'] }}</span></div></div>
        <span class="buyer-order-status"><span aria-hidden="true"></span>{{ $statusMap[$o['status']]['label'] }}</span>
    </div>
    <div class="divide-y divide-gray-50 px-5">
        @foreach($o['items'] as $it)
            <div class="buyer-order-product flex items-center gap-4 py-4">
                <img src="{{ $it['image'] }}" alt="" class="h-16 w-16 rounded-xl border border-gray-100 object-cover">
                <div class="min-w-0 flex-1">
                    <p class="buyer-order-product-name text-sm font-bold">{{ $it['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $it['variation'] }} · x{{ $it['qty'] }}</p>
                </div>
                <p class="text-sm font-bold text-gray-700">{{ $peso($it['price']) }}</p>
            </div>
        @endforeach
    </div>
    @if(($o['cancellation_status'] ?? null) === 'pending')
        <p class="px-5 py-3 text-sm font-semibold text-amber-800 bg-amber-50" role="status">Cancellation requested — awaiting seller approval.</p>
    @elseif(($o['cancellation_status'] ?? null) === 'rejected')
        <p class="px-5 py-3 text-sm text-gray-600">The seller declined your cancellation request. Your order remains active.</p>
    @elseif(($o['cancellation_status'] ?? null) === 'approved')
        <p class="px-5 py-3 text-sm text-gray-600">Cancellation approved by seller. If already paid, contact the seller to arrange your refund.</p>
    @endif
    <div class="buyer-order-footer">
        <p class="buyer-order-total">Order total <strong>{{ $peso($o['total']) }}</strong></p>
        <div class="buyer-order-actions">
            @if($o['can_cancel'])
                <button type="button" class="buyer-order-secondary" data-cancel-order="{{ route('orders.cancel', $o['seller_order_id']) }}" data-order-reference="{{ $o['id'] }}" aria-haspopup="dialog">Cancel order</button>
            @endif
            <a href="{{ route('buyer.messages', ['contact' => $o['seller_order_id']]) }}" class="buyer-order-secondary">Contact seller</a>
            @if($o['status'] === 'to_pay')
                <form action="{{ route('orders.pay', $o['id']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="commerce-button">Pay now</button>
                </form>
            @elseif($o['status'] === 'to_receive')
                <a href="{{ route('buyer.dashboard', ['tab' => 'track', 'track' => $o['id']]) }}" class="commerce-button">Track order</a>
            @elseif($o['status'] === 'completed')
                <button type="button" data-go="reviews" class="buyer-order-secondary">Rate</button>
                <a href="{{ route('home') }}" class="commerce-button">Buy again</a>
            @elseif($o['status'] === 'cancelled')
                <a href="{{ route('home') }}" class="commerce-button">Buy again</a>
            @else
            @endif
        </div>
    </div>
</article>
@if($o['can_cancel'])
    @once
        @include('buyer.partials.cancel-order-modal')
    @endonce
@endif
