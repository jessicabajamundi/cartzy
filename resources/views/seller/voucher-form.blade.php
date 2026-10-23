@extends('layouts.seller')
@section('page_title', $voucher ? 'Edit voucher' : 'Create voucher')
@section('content')
<link rel="stylesheet" href="{{ asset('css/seller-vouchers.css') }}?v={{ filemtime(public_path('css/seller-vouchers.css')) }}">
@php
    $tz = 'Asia/Manila';
    $presets = [
        'amount' => ['name' => 'Shop Sale', 'discount_type' => 'fixed', 'amount' => 50, 'min_spend' => 500, 'audience' => 'public'],
        'percent' => ['name' => 'Payday Deal', 'discount_type' => 'percent', 'percent' => 10, 'max_discount' => 100, 'min_spend' => 300, 'audience' => 'public'],
        'shipping' => ['name' => 'Free Shipping Deal', 'discount_type' => 'free_shipping', 'min_spend' => 500, 'audience' => 'public'],
        'code' => ['name' => 'Followers Treat', 'discount_type' => 'fixed', 'amount' => 30, 'min_spend' => 300, 'audience' => 'code'],
    ];
    $preset = $presets[$template ?? 'amount'] ?? $presets['amount'];
    $value = fn ($field, $fromVoucher, $default = null) => old($field, $voucher ? $fromVoucher : ($preset[$field] ?? $default));
    $pesos = fn ($minor) => $minor === null ? null : rtrim(rtrim(number_format($minor / 100, 2, '.', ''), '0'), '.');
    $type = $value('discount_type', $voucher?->discount_type, 'fixed');
    $audience = $value('audience', $voucher?->audience, 'public');
    $startDate = $value('starts_at', $voucher?->starts_at?->timezone($tz)->format('Y-m-d'), now($tz)->format('Y-m-d'));
    $endDate = $value('ends_at', $voucher?->ends_at?->timezone($tz)->format('Y-m-d'), now($tz)->addDays(30)->format('Y-m-d'));
@endphp
<div class="sv-page">
    <a class="sv-back" href="{{ route('seller.vouchers') }}">&larr; Back to vouchers</a>
    <div class="page-heading">
        <div>
            <div class="eyebrow">MARKETING</div>
            <h1>{{ $voucher ? 'Edit voucher' : 'Create a shop voucher' }}</h1>
            <p class="subtitle">{{ $voucher ? 'Update your voucher. Buyers see changes right away.' : 'Four quick steps. You can see exactly what buyers will get on the right.' }}</p>
        </div>
    </div>
    @if($locked)
        <div class="notice">Buyers have already used this voucher, so the deal itself is locked. You can still change its <strong>name</strong>, <strong>quantity</strong> and <strong>end date</strong>.</div>
    @endif

    <div class="sv-form-layout">
        <form class="sv-form" method="POST" action="{{ $voucher ? route('seller.vouchers.update', $voucher) : route('seller.vouchers.store') }}" id="sv-form" novalidate>
            @csrf
            <section class="sv-step">
                <h2><span>1</span>Voucher name</h2>
                <label>Name buyers will see
                    <input name="name" value="{{ $value('name', $voucher?->name) }}" maxlength="60" required placeholder="e.g. Payday Sale" data-preview="name">
                </label>
                <small class="sv-hint">Keep it short and clear, like “Payday Sale” or “New Arrival Deal”.</small>
                @error('name')<p class="sv-error">{{ $message }}</p>@enderror
            </section>

            <fieldset class="sv-step" @disabled($locked)>
                <legend><h2><span>2</span>Discount</h2></legend>
                <div class="sv-segmented" role="radiogroup" aria-label="Discount type">
                    <label><input type="radio" name="discount_type" value="fixed" @checked($type === 'fixed')><span>₱ Amount off</span></label>
                    <label><input type="radio" name="discount_type" value="percent" @checked($type === 'percent')><span>% Percent off</span></label>
                    <label><input type="radio" name="discount_type" value="free_shipping" @checked($type === 'free_shipping')><span>🚚 Free shipping</span></label>
                </div>
                <p class="sv-type-note" data-type-field="free_shipping">Waives the shipping fee of <strong>your store's parcel</strong> only. Fees depend on the buyer's location (₱{{ number_format(\App\Services\ShippingCalculator::lowestFeeMinor() / 100, 0) }}–₱{{ number_format(collect(\App\Services\ShippingCalculator::zones())->max('fee_minor') / 100, 0) }}), so you can set a cap.</p>
                <div class="sv-row">
                    <label data-type-field="fixed">Discount amount
                        <span class="sv-affix"><b>₱</b><input type="number" name="amount" min="1" step="1" inputmode="decimal" value="{{ $value('amount', $voucher?->discount_type === 'fixed' ? $pesos($voucher->amount_minor) : null) }}"></span>
                    </label>
                    <label data-type-field="percent">Percent off
                        <span class="sv-affix is-suffix"><input type="number" name="percent" min="1" max="{{ \App\Models\Voucher::SHOP_MAX_PERCENT }}" step="1" value="{{ $value('percent', $voucher?->percent) }}"><b>%</b></span>
                    </label>
                    <label data-type-field="percent">Max discount <em>(optional)</em>
                        <span class="sv-affix"><b>₱</b><input type="number" name="max_discount" min="1" step="1" value="{{ $value('max_discount', $pesos($voucher?->max_discount_minor)) }}" placeholder="No limit"></span>
                    </label>
                    <label data-type-field="free_shipping">Max shipping off <em>(optional)</em>
                        <span class="sv-affix"><b>₱</b><input type="number" name="shipping_cap" min="1" step="1" value="{{ $value('shipping_cap', $voucher?->discount_type === 'free_shipping' ? $pesos($voucher->amount_minor) : null) }}" placeholder="Fully free"></span>
                    </label>
                    <label>Minimum spend
                        <span class="sv-affix"><b>₱</b><input type="number" name="min_spend" min="0" step="1" value="{{ $value('min_spend', $pesos($voucher?->min_spend_minor), 0) }}"></span>
                    </label>
                </div>
                <small class="sv-hint" data-min-spend-hint>Counts only your items in the buyer's cart.</small>
                @foreach(['discount_type', 'amount', 'percent', 'max_discount', 'shipping_cap', 'min_spend'] as $field)@error($field)<p class="sv-error">{{ $message }}</p>@enderror @endforeach
            </fieldset>

            <fieldset class="sv-step" @disabled($locked)>
                <legend><h2><span>3</span>Who can use it</h2></legend>
                <div class="sv-choice-grid">
                    <label class="sv-choice"><input type="radio" name="audience" value="public" @checked($audience === 'public')><span><strong>Everyone</strong><small>Applied automatically at checkout for any buyer who meets the minimum spend.</small></span></label>
                    <label class="sv-choice"><input type="radio" name="audience" value="code" @checked($audience === 'code')><span><strong>Only with a code</strong><small>Buyers type the code at checkout. Great for followers or live-selling.</small></span></label>
                </div>
                <div class="sv-code-field" data-audience-field="code">
                    <label>Voucher code
                        <span class="sv-code-input"><input name="code" value="{{ $value('code', $voucher?->code) }}" maxlength="20" placeholder="e.g. {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $shop?->name ?? 'SHOP'), 6, '')) }}50" autocomplete="off" data-preview="code"><button type="button" class="button small secondary" data-generate-code>Generate</button></span>
                    </label>
                    <small class="sv-hint">3–20 letters or numbers, no spaces.</small>
                </div>
                @foreach(['audience', 'code'] as $field)@error($field)<p class="sv-error">{{ $message }}</p>@enderror @endforeach
            </fieldset>

            <section class="sv-step">
                <h2><span>4</span>Quantity &amp; schedule</h2>
                <div class="sv-row">
                    <label>Total vouchers
                        <input type="number" name="total_limit" min="{{ max(1, $voucher?->used_count ?? 1) }}" max="100000" value="{{ $value('total_limit', $voucher?->total_limit, 100) }}">
                    </label>
                    <label>Uses per buyer
                        <input type="number" name="per_user_limit" min="1" max="10" value="{{ $value('per_user_limit', $voucher?->per_user_limit, 1) }}" @disabled($locked)>
                    </label>
                    <label>Start date
                        <input type="date" name="starts_at" value="{{ $startDate }}" min="{{ $voucher ? '' : now($tz)->format('Y-m-d') }}" @disabled($locked)>
                    </label>
                    <label>End date
                        <input type="date" name="ends_at" value="{{ $endDate }}" min="{{ now($tz)->format('Y-m-d') }}">
                    </label>
                </div>
                <div class="sv-quick-dates" aria-label="Quick end dates">
                    <span>Run for</span>
                    @foreach([7, 14, 30] as $days)<button type="button" class="sv-pill" data-run-days="{{ $days }}">{{ $days }} days</button>@endforeach
                </div>
                <small class="sv-hint">The voucher stops when it reaches its end date or all vouchers are used — up to {{ \App\Models\Voucher::SHOP_MAX_DAYS }} days.</small>
                @foreach(['total_limit', 'per_user_limit', 'starts_at', 'ends_at'] as $field)@error($field)<p class="sv-error">{{ $message }}</p>@enderror @endforeach
            </section>

            <div class="sv-form-actions">
                <a class="button secondary" href="{{ route('seller.vouchers') }}">Cancel</a>
                <button class="button" type="submit">{{ $voucher ? 'Save changes' : 'Create voucher' }}</button>
            </div>
        </form>

        <aside class="sv-preview" aria-labelledby="sv-preview-title">
            <p class="eyebrow">LIVE PREVIEW</p>
            <h2 id="sv-preview-title">What buyers will see</h2>
            <div class="sv-ticket">
                <div class="sv-ticket-stub"><strong data-preview-headline></strong><small data-preview-audience></small></div>
                <div class="sv-ticket-body">
                    <span class="sv-ticket-shop">{{ $shop?->name }}</span>
                    <strong data-preview-name></strong>
                    <small data-preview-terms></small>
                </div>
            </div>
            <dl class="sv-summary">
                <div><dt>Example</dt><dd data-preview-example></dd></div>
                <div><dt>Runs</dt><dd data-preview-dates></dd></div>
                <div><dt>Who pays</dt><dd>Your store &middot; taken from that order's earnings</dd></div>
            </dl>
        </aside>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/seller-vouchers.js') }}?v={{ filemtime(public_path('js/seller-vouchers.js')) }}" defer></script>
@endpush
