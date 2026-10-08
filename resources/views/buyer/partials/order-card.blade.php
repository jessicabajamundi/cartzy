<article data-status="{{ $o['status'] }}" class="overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm transition hover:shadow-lg">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-[#FAF9FB] px-5 py-3">
        <p class="text-sm font-bold text-gray-900">{{ $o['store'] }} <span class="ml-2 text-xs font-semibold text-gray-400">{{ $o['id'] }} · {{ $o['date'] }}</span></p>
        <span class="rounded-full px-3 py-1 text-[11px] font-bold ring-1 {{ $statusMap[$o['status']]['cls'] }}">{{ $statusMap[$o['status']]['label'] }}</span>
    </div>
    <div class="divide-y divide-gray-50 px-5">
        @foreach($o['items'] as $it)
            <div class="flex items-center gap-4 py-4">
                <img src="{{ $it['image'] }}" alt="" class="h-16 w-16 rounded-xl border border-gray-100 object-cover">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-gray-900">{{ $it['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $it['variation'] }} · x{{ $it['qty'] }}</p>
                </div>
                <p class="text-sm font-bold text-gray-700">{{ $peso($it['price']) }}</p>
            </div>
        @endforeach
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4">
        <p class="text-sm text-gray-500">Order total <span class="ml-1.5 text-lg font-extrabold text-gray-900">{{ $peso($o['total']) }}</span></p>
        <div class="flex gap-2">
            <a href="{{ route('buyer.messages', ['contact' => $o['seller_order_id']]) }}" class="{{ $btnGhost }}">Contact seller</a>
            @if($o['status'] === 'to_pay')
                <form action="{{ route('orders.pay', $o['id']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="{{ $btnPrimary }}">Pay now</button>
                </form>
            @elseif($o['status'] === 'to_receive')
                <a href="{{ route('buyer.dashboard', ['tab' => 'track', 'track' => $o['id']]) }}" class="{{ $btnPrimary }}">Track order</a>
            @elseif($o['status'] === 'completed')
                <button type="button" data-go="reviews" class="{{ $btnGhost }}">Rate</button>
                <a href="{{ route('home') }}" class="{{ $btnPrimary }}">Buy again</a>
            @elseif($o['status'] === 'cancelled')
                <a href="{{ route('home') }}" class="{{ $btnPrimary }}">Buy again</a>
            @else
            @endif
        </div>
    </div>
</article>
