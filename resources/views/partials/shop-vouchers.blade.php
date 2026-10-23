{{-- Public shop vouchers for one store (store page and product page). Expects $shopVouchers and $shopName. --}}
@if(($shopVouchers ?? collect())->isNotEmpty())
<link rel="stylesheet" href="{{ asset('css/shop-vouchers.css') }}?v={{ filemtime(public_path('css/shop-vouchers.css')) }}">
<section class="shv {{ $compact ?? false ? 'is-compact' : '' }}" aria-labelledby="shv-title-{{ $shopVouchers->first()->seller_id }}">
    <div class="shv-head">
        <h2 id="shv-title-{{ $shopVouchers->first()->seller_id }}"><span aria-hidden="true">🎟</span> Shop vouchers</h2>
        <small>Applied automatically at checkout on {{ $shopName }} items</small>
    </div>
    <ul class="shv-list">
        @foreach($shopVouchers as $voucher)
            @php $left = $voucher->total_limit ? $voucher->total_limit - $voucher->used_count : null; @endphp
            <li class="shv-ticket">
                <span class="shv-stub"><strong>{{ $voucher->headline() }}</strong></span>
                <span class="shv-body">
                    <strong>{{ $voucher->min_spend_minor > 0 ? 'Min. spend ₱'.number_format($voucher->min_spend_minor / 100, 0) : 'No min. spend' }}</strong>
                    <small>
                        @if($voucher->discount_type === 'percent' && $voucher->max_discount_minor)Up to ₱{{ number_format($voucher->max_discount_minor / 100, 0) }} &middot; @endif
                        @if($voucher->ends_at)Until {{ $voucher->ends_at->timezone('Asia/Manila')->format('M j') }}@endif
                        @if($left !== null && $left <= 20) &middot; <em>Only {{ $left }} left</em>@endif
                    </small>
                </span>
            </li>
        @endforeach
    </ul>
</section>
@endif
