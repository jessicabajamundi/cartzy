@php
    $readyCount = $vouchers->where('eligible', true)->count();
    $walletShopVouchers ??= collect();
    $voucherTotal = $vouchers->count() + $walletShopVouchers->count();
@endphp
<header class="commerce-heading">
    <div><p class="commerce-eyebrow">SAVE ON YOUR NEXT ORDER</p><h2 id="vouchers-title">Vouchers</h2><p>Your vouchers are applied for you at checkout. No need to copy anything.</p></div>
    <span class="commerce-count">{{ $voucherTotal }} {{ $voucherTotal === 1 ? 'voucher' : 'vouchers' }}</span>
</header>

<form method="POST" action="{{ route('vouchers.claim') }}" class="buyer-voucher-code">
    @csrf
    <label for="buyer-voucher-code">Have a voucher code?</label>
    <div>
        <input id="buyer-voucher-code" name="voucher_code" maxlength="40" placeholder="Enter code, e.g. PASKO100" autocomplete="off" required value="{{ old('voucher_code') }}">
        <button type="submit">Add voucher</button>
    </div>
    @error('voucher_code')<p class="buyer-voucher-error" role="alert">{{ $message }}</p>@enderror
</form>

@if($walletShopVouchers->isNotEmpty())
    <h3 class="buyer-voucher-group">Shop vouchers</h3>
    <div class="buyer-voucher-grid">
        @foreach($walletShopVouchers as $v)
            <article class="buyer-voucher is-shop">
                <div class="buyer-voucher-stub">
                    <span>SHOP</span>
                    <strong>{{ $v->headline() }}</strong>
                </div>
                <div class="buyer-voucher-body">
                    <h3>{{ $v->name }}</h3>
                    <p class="buyer-voucher-shop">{{ $v->seller?->name }}</p>
                    <p class="buyer-voucher-terms">{{ $v->terms() }}</p>
                    @if($v->seller)<a class="buyer-voucher-status is-ready" href="{{ route('store.show', $v->seller->slug) }}">Shop at {{ $v->seller->name }} &rarr;</a>@endif
                </div>
            </article>
        @endforeach
    </div>
    @if($vouchers->isNotEmpty())<h3 class="buyer-voucher-group">cartzy vouchers</h3>@endif
@endif

@if($vouchers->isNotEmpty())
    <div class="buyer-voucher-grid">
        @foreach($vouchers as $option)
            @php $v = $option['voucher']; @endphp
            <article class="buyer-voucher {{ $v->isShipping() ? 'is-shipping' : '' }} {{ $option['eligible'] ? '' : 'is-waiting' }}">
                <div class="buyer-voucher-stub">
                    <span>{{ $v->isShipping() ? 'SHIPPING' : 'DISCOUNT' }}</span>
                    <strong>{{ $v->headline() }}</strong>
                </div>
                <div class="buyer-voucher-body">
                    <h3>{{ $v->name }}</h3>
                    @if($v->description)<p>{{ $v->description }}</p>@endif
                    <p class="buyer-voucher-terms">{{ $v->terms() }}</p>
                    @if($option['eligible'])
                        <span class="buyer-voucher-status is-ready">✓ Ready to use on your cart</span>
                    @elseif($option['reason'])
                        <span class="buyer-voucher-status">{{ $option['reason'] }}</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    @if($readyCount)
        <a href="{{ route('cart.index') }}" class="commerce-button buyer-voucher-cta">Use my vouchers at checkout<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg></a>
    @endif
@elseif($walletShopVouchers->isEmpty())
    @include('buyer.partials.commerce-empty', [
        'emptyPath' => 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4zM9 9l6 6',
        'eyebrow' => 'NOTHING HERE YET',
        'note' => 'We will notify you when a new voucher is available.',
        'title' => 'No vouchers right now',
        'text' => 'Vouchers from Cartzy promos will show up here. Got a code? Add it above.',
        'cta' => 'Start shopping',
        'href' => route('home'),
    ])
@endif

@once
<style>
.buyer-voucher-code{background:#fff;border:1px solid #E1DDE7;border-radius:18px;padding:18px 20px}
.buyer-voucher-code label{display:block;font-size:13px;font-weight:800;color:#3E354C;margin-bottom:8px}
.buyer-voucher-code>div{display:flex;gap:8px}
.buyer-voucher-code input{flex:1;min-width:0;border:1px solid #E1DDE7;border-radius:12px;padding:10px 14px;font-size:14px;font-weight:600;text-transform:uppercase}
.buyer-voucher-code input:focus{outline:none;border-color:#8d7a9e;box-shadow:0 0 0 3px #6f638220}
.buyer-voucher-code button{border:0;border-radius:12px;background:#564B68;color:#fff;font-size:13px;font-weight:700;padding:0 18px;cursor:pointer;white-space:nowrap}
.buyer-voucher-code button:hover{background:#3E354C}
.buyer-voucher-error{color:#b45045;font-size:12px;margin-top:8px}
.buyer-voucher-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px}
.buyer-voucher{display:flex;background:#fff;border:1px solid #E1DDE7;border-radius:16px;overflow:hidden;min-height:118px}
.buyer-voucher-stub{width:118px;flex-shrink:0;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:4px;text-align:center;padding:14px 10px;background:linear-gradient(160deg,#6F6382,#564B68);color:#fff;border-right:2px dashed #fff}
.buyer-voucher-stub span{font-size:9px;letter-spacing:1.5px;opacity:.8;font-weight:700}
.buyer-voucher-stub strong{font-size:16px;font-weight:900;line-height:1.2}
.buyer-voucher.is-shipping .buyer-voucher-stub{background:linear-gradient(160deg,#3d8a72,#28735d)}
.buyer-voucher-body{padding:14px 16px;display:flex;flex-direction:column;gap:4px;min-width:0}
.buyer-voucher-body h3{font-size:14px;font-weight:800;color:#282133;margin:0}
.buyer-voucher-body p{font-size:12px;color:#756b80;margin:0;line-height:1.5}
.buyer-voucher-terms{font-weight:600}
.buyer-voucher-status{margin-top:auto;font-size:11px;font-weight:700;color:#a56f60}
.buyer-voucher-status.is-ready{color:#28735d}
.buyer-voucher.is-waiting .buyer-voucher-stub{filter:saturate(.45)}
.buyer-voucher-cta{display:inline-flex}
.buyer-voucher-group{margin:6px 0 -4px;font-size:13px;font-weight:800;color:#3E354C}
.buyer-voucher.is-shop .buyer-voucher-stub{background:linear-gradient(160deg,#a25369,#7c4a6b)}
.buyer-voucher-shop{color:#a25369!important;font-weight:700;font-size:11px!important;letter-spacing:.04em;text-transform:uppercase}
a.buyer-voucher-status{text-decoration:none}
a.buyer-voucher-status:hover{text-decoration:underline}
@media(max-width:520px){.buyer-voucher-grid{grid-template-columns:1fr}.buyer-voucher-stub{width:96px}.buyer-voucher-stub strong{font-size:13px}.buyer-voucher-code>div{flex-direction:column}.buyer-voucher-code button{padding:11px}}
</style>
@endonce
