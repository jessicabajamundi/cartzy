@extends('layouts.app')

@section('title', 'Checkout | cartzy')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/buyer-checkout.css') }}?v={{ filemtime(public_path('css/buyer-checkout.css')) }}">
@endpush

@section('content')
<div class="checkout-page">
    <div class="checkout-shell">
        <nav class="checkout-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span>
            <a href="{{ route('cart.index') }}">Cart</a><span aria-hidden="true">/</span><span aria-current="page">Checkout</span>
        </nav>
        <header class="checkout-heading">
            <div><p class="checkout-eyebrow">ONE LAST STEP</p><h1>Review your order</h1><p>Check your items, delivery details, and total before placing your order.</p></div>
            <a class="checkout-back" href="{{ route('cart.index') }}">&larr; Back to cart</a>
        </header>

        @if($errors->any())
            <div class="checkout-alert" role="alert"><strong>Please check your checkout details.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('checkout.voucher') }}" method="POST" id="voucher-code-form" hidden>
            @csrf
            @foreach($selectedKeys as $key)<input type="hidden" name="selected_items[]" value="{{ $key }}">@endforeach
        </form>

        <form action="{{ route('checkout.process') }}" method="POST" class="checkout-layout" id="checkout-form" data-subtotal="{{ (int) round($subtotal * 100) }}" data-shipping="{{ (int) round($shippingFee * 100) }}" data-product-savings="{{ (int) round($productDiscount * 100) }}" data-shop-discount="{{ (int) round(($shopVoucherDiscount + $shopShippingDiscount) * 100) }}">
            @csrf
            @foreach($selectedKeys as $key)<input type="hidden" name="selected_items[]" value="{{ $key }}">@endforeach
            <input type="hidden" name="delivery_address" value="{{ $checkoutAddress ?? '' }}">

            <div class="checkout-sections">
                <section class="checkout-card" aria-labelledby="delivery-title">
                    <div class="checkout-card-heading"><div class="checkout-section-title"><span class="checkout-step">01</span><h2 id="delivery-title">Delivery details</h2></div><a href="{{ route('buyer.dashboard', ['tab' => 'addresses']) }}">{{ $checkoutAddress ? 'Change address' : 'Add address' }}</a></div>
                    @if($checkoutAddress)
                        <div class="checkout-recipient"><strong>{{ $deliveryAddress?->recipient ?? $user->name }}</strong>@if($deliveryAddress?->is_default)<span class="checkout-badge">Default address</span>@endif</div>
                        <p class="checkout-phone">{{ $deliveryAddress?->phone ?? $user->phone ?? 'No phone specified' }}</p>
                        <p class="checkout-address">{{ $checkoutAddress }}</p>
                        <p class="checkout-help">Please make sure your address and contact number are correct.</p>
                    @else
                        <div class="checkout-alert">Add a delivery address before placing your order.</div>
                    @endif
                </section>

                <section class="checkout-card" aria-labelledby="items-title">
                    <div class="checkout-card-heading"><div class="checkout-section-title"><span class="checkout-step">02</span><h2 id="items-title">Order items <span class="checkout-count">{{ $totalQuantity }}</span></h2></div><a href="{{ route('cart.index') }}">Edit items</a></div>
                    <p class="checkout-help checkout-items-intro">{{ count($checkoutItems) }} {{ count($checkoutItems) === 1 ? 'product selection' : 'product selections' }} &middot; {{ $totalQuantity }} {{ $totalQuantity === 1 ? 'item' : 'items' }} in total</p>
                    {{-- One card per store: its items, its shop vouchers and its own parcel's shipping (Shopee/Lazada style). --}}
                    @foreach($storeItems as $sellerId => $items)
                        @php
                            $quote = $shippingQuotes[$sellerId] ?? null;
                            $shopShipping = $shopShippingGroups[$sellerId]['selected'] ?? null;
                            $parcelFee = $quote['fee_minor'] ?? 0;
                            $parcelDue = $parcelFee - ($shopShipping['discount_minor'] ?? 0);
                        @endphp
                        <div class="checkout-store">
                            <header class="checkout-store-head">
                                <span class="checkout-store-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9M3 9h18M3 9v11h18V9M9 20v-6h6v6"/></svg></span>
                                <strong>{{ $sellerId ? ($shopNames[$sellerId] ?? 'Store') : 'cartzy' }}</strong>
                            </header>
                            <div class="checkout-product-list">
                                @foreach($items as $item)
                                    @php
                                        $unitPrice = $item['price'];
                                        $originalPrice = max($item['original_price'] ?? $unitPrice, $unitPrice);
                                        $lineSavings = ($originalPrice - $unitPrice) * $item['quantity'];
                                    @endphp
                                    <article class="checkout-product">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" width="80" height="80">
                                        <div class="checkout-product-info">
                                            <h3>{{ $item['name'] }}</h3>
                                            <p class="checkout-variant">{{ $item['variation'] ?? 'Standard' }}</p>
                                            <div class="checkout-unit-price">@if($originalPrice > $unitPrice)<del>&#8369;{{ number_format($originalPrice, 2) }}</del>@endif<strong>&#8369;{{ number_format($unitPrice, 2) }}</strong><span>each</span></div>
                                            @if($lineSavings > 0)<p class="checkout-saving">You save &#8369;{{ number_format($lineSavings, 2) }} on this item</p>@endif
                                            @if(!empty($item['unavailable']))<p class="checkout-item-error">Unavailable. Please remove this item from your cart.</p>@endif
                                        </div>
                                        <div class="checkout-line-total"><span class="checkout-quantity">Qty: {{ $item['quantity'] }}</span><strong>&#8369;{{ number_format($unitPrice * $item['quantity'], 2) }}</strong><span>Item total</span></div>
                                    </article>
                                @endforeach
                            </div>

                            @if($quote)
                                @foreach([['shop_voucher', 'Shop voucher', $shopDiscountGroups[$sellerId] ?? null], ['shop_shipping_voucher', 'Shop free shipping', $shopShippingGroups[$sellerId] ?? null]] as [$fieldName, $groupLabel, $group])
                                    @continue(!$sellerId || !$group)
                                    <fieldset class="checkout-vouchers is-shop">
                                        <legend><span class="checkout-shop-tag">{{ $groupLabel }}</span></legend>
                                        @foreach($group['options'] as $option)
                                            @php $v = $option['voucher']; @endphp
                                            <label class="checkout-voucher {{ $option['eligible'] ? '' : 'is-disabled' }} {{ $v->isShipping() ? 'is-shipping' : '' }}">
                                                <span class="checkout-voucher-stub"><strong>{{ $v->headline() }}</strong></span>
                                                <span class="checkout-voucher-copy">
                                                    <strong>{{ $v->name }}</strong>
                                                    <small>{{ $v->terms() }} &middot; on this store's {{ $v->isShipping() ? 'parcel' : 'items' }}</small>
                                                    @if($option['eligible'])<small class="checkout-voucher-save">Saves &#8369;{{ number_format($option['discount_minor'] / 100, 2) }}</small>
                                                    @else<small class="checkout-voucher-reason">{{ $option['reason'] }}</small>@endif
                                                </span>
                                                <input type="radio" name="{{ $fieldName }}[{{ $sellerId }}]" value="{{ $v->id }}" data-shop-voucher="{{ $sellerId }}" data-shop-field="{{ $fieldName }}" @checked($group['selected'] && $group['selected']['voucher']->id === $v->id) @disabled(!$option['eligible']) aria-label="Use {{ $v->name }}">
                                            </label>
                                        @endforeach
                                        <label class="checkout-voucher is-none">
                                            <span class="checkout-voucher-copy"><strong>Don't use a {{ strtolower($groupLabel) }} voucher</strong></span>
                                            <input type="radio" name="{{ $fieldName }}[{{ $sellerId }}]" value="" data-shop-voucher="{{ $sellerId }}" data-shop-field="{{ $fieldName }}" @checked(!$group['selected'])>
                                        </label>
                                    </fieldset>
                                @endforeach

                                <div class="checkout-store-shipping">
                                    <span class="checkout-store-shipping-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h11v12H3zM14 9h4l3 4v4h-7M7 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4M17 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/></svg></span>
                                    <div>
                                        <strong>Shipping</strong>
                                        <small>
                                            @if($quote && $quote['zone'] !== 'standard'){{ $quote['from'] }} &rarr; {{ $destination?->province }} &middot; {{ $quote['label'] }}
                                            @else Standard rate &middot; add a full address with province for an exact fee @endif
                                        </small>
                                    </div>
                                    <span class="checkout-store-shipping-fee">
                                        @if($shopShipping)<del>&#8369;{{ number_format($parcelFee / 100, 2) }}</del>@endif
                                        <strong>{{ $parcelDue <= 0 ? 'FREE' : '₱'.number_format($parcelDue / 100, 2) }}</strong>
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </section>

                <section class="checkout-card" aria-labelledby="shipping-title">
                    <div class="checkout-card-heading"><div class="checkout-section-title"><span class="checkout-step">03</span><h2 id="shipping-title">Shipping &amp; vouchers</h2></div><a href="{{ route('buyer.dashboard', ['tab' => 'vouchers']) }}">My vouchers</a></div>
                    <div class="checkout-shipping"><div><strong>Standard delivery &middot; {{ count($shippingQuotes) }} {{ \Illuminate\Support\Str::plural('parcel', count($shippingQuotes)) }}</strong><p>Each store ships separately. The fee depends on where the store ships from and your address.</p></div><strong>&#8369;{{ number_format($shippingFee, 2) }}</strong></div>
                    <p class="checkout-help">Delivery timing will be confirmed when your order is dispatched. You can track updates in My Orders.</p>

                    @php $anyVoucherApplied = $selectedItemVoucher || $selectedShippingVoucher || $shopDiscountGroups->contains(fn ($g) => $g['selected']) || $shopShippingGroups->contains(fn ($g) => $g['selected']); @endphp
                    <div class="checkout-offer {{ $anyVoucherApplied ? 'is-applied' : '' }}" id="voucher-status">
                        <span class="checkout-offer-icon" aria-hidden="true">{{ $anyVoucherApplied ? '✓' : '%' }}</span>
                        <div><strong>{{ $anyVoucherApplied ? 'Best vouchers applied for you' : 'Vouchers' }}</strong><p>@if($anyVoucherApplied)We picked the vouchers that save you the most. You can change them below.@else No voucher can be used on this order yet. Have a code? Enter it below.@endif</p></div>
                    </div>

                    @foreach([['cartzy vouchers', 'voucher_id', $itemVoucherOptions, $selectedItemVoucher], ['Shipping vouchers', 'shipping_voucher_id', $shippingVoucherOptions, $selectedShippingVoucher]] as [$groupLabel, $fieldName, $groupOptions, $groupSelected])
                        @if($groupOptions->isNotEmpty())
                            <fieldset class="checkout-vouchers">
                                <legend>{{ $groupLabel }}</legend>
                                @foreach($groupOptions as $option)
                                    @php $v = $option['voucher']; @endphp
                                    <label class="checkout-voucher {{ $option['eligible'] ? '' : 'is-disabled' }} {{ $v->isShipping() ? 'is-shipping' : '' }}">
                                        <span class="checkout-voucher-stub"><strong>{{ $v->headline() }}</strong></span>
                                        <span class="checkout-voucher-copy">
                                            <strong>{{ $v->name }}</strong>
                                            <small>{{ $v->terms() }}</small>
                                            @if($option['eligible'])<small class="checkout-voucher-save">Saves &#8369;{{ number_format($option['discount_minor'] / 100, 2) }}</small>
                                            @else<small class="checkout-voucher-reason">{{ $option['reason'] }}</small>@endif
                                        </span>
                                        <input type="radio" name="{{ $fieldName }}" value="{{ $v->id }}" data-voucher-discount="{{ $option['discount_minor'] }}" @checked($groupSelected && $groupSelected['voucher']->id === $v->id) @disabled(!$option['eligible']) aria-label="Use {{ $v->name }}">
                                    </label>
                                @endforeach
                                <label class="checkout-voucher is-none">
                                    <span class="checkout-voucher-copy"><strong>Don't use a {{ strtolower(\Illuminate\Support\Str::singular($groupLabel)) }}</strong></span>
                                    <input type="radio" name="{{ $fieldName }}" value="" data-voucher-discount="0" @checked(!$groupSelected)>
                                </label>
                            </fieldset>
                        @endif
                    @endforeach

                    <div class="checkout-voucher-code">
                        <label for="voucher-code-input">Have a voucher code?</label>
                        <div><input id="voucher-code-input" name="voucher_code" form="voucher-code-form" maxlength="40" placeholder="Enter code, e.g. PASKO100" autocomplete="off" value="{{ old('voucher_code') }}"><button type="submit" form="voucher-code-form">Apply</button></div>
                        @error('voucher_code')<p class="checkout-item-error">{{ $message }}</p>@enderror
                        @if(session('voucher_success'))<p class="checkout-voucher-save" role="status">{{ session('voucher_success') }}</p>@endif
                    </div>
                </section>

                <section class="checkout-card" aria-labelledby="payment-title">
                    <div class="checkout-card-heading"><div class="checkout-section-title"><span class="checkout-step">04</span><h2 id="payment-title">Payment method</h2></div></div>
                    <fieldset class="checkout-payments"><legend class="checkout-sr-only">Choose your payment method</legend>
                        <label class="checkout-payment is-selected">
                            <span class="checkout-payment-icon" aria-hidden="true">&#8369;</span>
                            <span class="checkout-payment-copy"><strong>Cash on Delivery (COD)</strong><small>Pay the rider in cash when your order arrives.</small></span>
                            <input type="radio" name="payment_method" value="COD" checked required>
                        </label>
                        <label class="checkout-payment is-disabled">
                            <span class="checkout-payment-icon gcash" aria-hidden="true">G</span>
                            <span class="checkout-payment-copy"><strong>GCash <span class="checkout-badge">Unavailable</span></strong><small>Online payments are not available yet. Please use COD.</small></span>
                            <input type="radio" name="payment_method" value="GCash" disabled aria-label="GCash unavailable">
                        </label>
                    </fieldset>
                    <div class="checkout-payment-note">No payment is collected now. Prepare <strong data-total>&#8369;{{ number_format($total, 2) }}</strong> for delivery.</div>
                </section>
            </div>

            <aside class="checkout-summary" aria-labelledby="summary-title">
                <div class="checkout-card">
                    <div class="checkout-summary-heading"><p class="checkout-eyebrow">YOUR ORDER AT A GLANCE</p><h2 id="summary-title">Order summary</h2><p>{{ $totalQuantity }} {{ $totalQuantity === 1 ? 'item' : 'items' }} &middot; All amounts in PHP</p></div>
                    <dl class="checkout-totals">
                        <div><dt>Items before discounts</dt><dd>&#8369;{{ number_format($originalSubtotal, 2) }}</dd></div>
                        <div class="checkout-saving"><dt>Product discounts</dt><dd>{{ $productDiscount > 0 ? '−' : '' }}&#8369;{{ number_format($productDiscount, 2) }}</dd></div>
                        <div class="checkout-subtotal"><dt>Merchandise subtotal</dt><dd>&#8369;{{ number_format($subtotal, 2) }}</dd></div>
                        <div><dt>Shipping fee <small>({{ count($shippingQuotes) }} {{ \Illuminate\Support\Str::plural('parcel', count($shippingQuotes)) }})</small></dt><dd>&#8369;{{ number_format($shippingFee, 2) }}</dd></div>
                        @if($shopShippingDiscount > 0)<div class="checkout-saving"><dt>Shop free shipping</dt><dd>−&#8369;{{ number_format($shopShippingDiscount, 2) }}</dd></div>@endif
                        <div class="checkout-saving"><dt>cartzy shipping discount</dt><dd data-shipping-discount>{{ $discount > 0 ? '−' : '' }}&#8369;{{ number_format($discount, 2) }}</dd></div>
                        @if($shopVoucherDiscount > 0)<div class="checkout-saving"><dt>Shop voucher discount</dt><dd>−&#8369;{{ number_format($shopVoucherDiscount, 2) }}</dd></div>@endif
                        <div class="checkout-saving"><dt>cartzy voucher discount</dt><dd data-voucher-discount-total>{{ $voucherDiscount > 0 ? '−' : '' }}&#8369;{{ number_format($voucherDiscount, 2) }}</dd></div>
                    </dl>
                    <div class="checkout-grand-total"><div><strong>Total payment</strong><span>Pay on delivery</span></div><strong data-total>&#8369;{{ number_format($total, 2) }}</strong></div>
                    <div class="checkout-total-savings" data-savings @if($totalSavings <= 0) hidden @endif>You're saving <strong>&#8369;{{ number_format($totalSavings, 2) }}</strong> on this order</div>
                    <div class="checkout-review"><span>Payment method</span><strong>Cash on Delivery</strong></div>
                    <button type="submit" class="checkout-submit" @disabled(empty($checkoutAddress) || $hasUnavailableItems)><span>Place order</span><span data-total-button>&#8369;{{ number_format($total, 2) }} &rarr;</span></button>
                    @if(empty($checkoutAddress))<p class="checkout-item-error">Add your delivery address to continue.</p>@elseif($hasUnavailableItems)<p class="checkout-item-error">Remove unavailable items from your cart to continue.</p>@endif
                    <p class="checkout-submit-help" id="checkout-submit-status" aria-live="polite">Review your details before confirming. Your order will appear in My Orders after it is placed.</p>
                    <a class="checkout-summary-edit" href="{{ route('cart.index') }}">Need to change something? Edit your cart</a>
                </div>
            </aside>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Live totals when the buyer switches vouchers; the server re-checks everything on submit.
    const form = document.getElementById('checkout-form');
    const peso = minor => '₱' + (minor / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const picked = name => Number(form.querySelector('input[name="' + name + '"]:checked')?.dataset.voucherDiscount || 0);
    function refresh() {
        const items = picked('voucher_id'), shipping = picked('shipping_voucher_id'), shop = Number(form.dataset.shopDiscount || 0);
        const total = Math.max(0, Number(form.dataset.subtotal) + Number(form.dataset.shipping) - items - shipping - shop);
        const savings = Number(form.dataset.productSavings) + items + shipping + shop;
        form.querySelector('[data-shipping-discount]').textContent = (shipping ? '−' : '') + peso(shipping);
        form.querySelector('[data-voucher-discount-total]').textContent = (items ? '−' : '') + peso(items);
        form.querySelectorAll('[data-total]').forEach(el => el.textContent = peso(total));
        form.querySelector('[data-total-button]').textContent = peso(total) + ' →';
        const box = form.querySelector('[data-savings]');
        box.hidden = savings <= 0;
        box.querySelector('strong').textContent = peso(savings);
    }
    form.querySelectorAll('input[data-voucher-discount]').forEach(input => input.addEventListener('change', refresh));

    // A shop voucher changes what the cartzy vouchers apply to, so reload checkout with the new choice (Shopee recalculates too).
    form.querySelectorAll('input[data-shop-voucher]').forEach(input => input.addEventListener('change', () => {
        const url = new URL(window.location.href.split('?')[0]);
        url.searchParams.set('selection', '1');
        form.querySelectorAll('input[name="selected_items[]"]').forEach(item => url.searchParams.append('selected_items[]', item.value));
        form.querySelectorAll('input[data-shop-voucher]:checked').forEach(choice => url.searchParams.set(`${choice.dataset.shopField}[${choice.dataset.shopVoucher}]`, choice.value || 'none'));
        const platform = form.querySelector('input[name="voucher_id"]:checked');
        const shipping = form.querySelector('input[name="shipping_voucher_id"]:checked');
        if (platform) url.searchParams.set('voucher', platform.value || 'none');
        if (shipping) url.searchParams.set('shipping_voucher', shipping.value || 'none');
        form.querySelectorAll('.checkout-store').forEach(group => group.setAttribute('aria-busy', 'true'));
        window.location.assign(url.toString());
    }));
})();

document.getElementById('checkout-form').addEventListener('submit', function (event) {
    if (this.dataset.submitting === 'true') { event.preventDefault(); return; }
    this.dataset.submitting = 'true';
    const button = this.querySelector('.checkout-submit');
    button.disabled = true;
    button.querySelector('span').textContent = 'Placing order…';
    document.getElementById('checkout-submit-status').textContent = 'Please wait while we place your order.';
});
window.addEventListener('pageshow', function (event) { if (event.persisted) window.location.reload(); });
</script>
@endpush
