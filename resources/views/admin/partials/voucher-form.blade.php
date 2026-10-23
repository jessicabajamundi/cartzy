@php
    $v = $voucher ?? null;
    $pesos = fn ($minor) => $minor === null ? null : rtrim(rtrim(number_format($minor / 100, 2, '.', ''), '0'), '.');
    $type = old('discount_type', $v?->discount_type ?? 'fixed');
    $audience = old('audience', $v?->audience ?? 'public');
@endphp
<div class="form-grid voucher-form" data-voucher-form>
    <label>Voucher name
        <input name="name" value="{{ old('name', $v?->name) }}" required maxlength="120" placeholder="e.g. Payday Sale ₱100 off">
    </label>
    <label>What does it discount?
        <select name="discount_type" data-voucher-type>
            @foreach(\App\Models\Voucher::TYPES as $key => $label)<option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>@endforeach
        </select>
    </label>

    <label data-show-type="fixed">Amount off (₱)
        <input type="number" name="amount" step="0.01" min="1" value="{{ old('amount', $type === 'fixed' ? $pesos($v?->amount_minor) : null) }}" placeholder="e.g. 100" data-amount-fixed>
    </label>
    <label data-show-type="free_shipping">Max shipping off (₱)
        <input type="number" name="amount" step="0.01" min="1" value="{{ old('amount', $type === 'free_shipping' ? $pesos($v?->amount_minor) : null) }}" placeholder="Leave blank for fully free shipping">
        <small class="muted">Shipping depends on the buyer's location (₱{{ number_format(\App\Services\ShippingCalculator::lowestFeeMinor() / 100, 0) }}–₱{{ number_format(collect(\App\Services\ShippingCalculator::zones())->max('fee_minor') / 100, 0) }} per store parcel). <a class="text-link" href="{{ route('admin.shipping') }}">Shipping rates</a></small>
    </label>
    <label data-show-type="percent">Percent off (%)
        <input type="number" name="percent" min="1" max="100" value="{{ old('percent', $v?->percent) }}" placeholder="e.g. 10">
    </label>
    <label data-show-type="percent">Maximum discount (₱)
        <input type="number" name="max_discount" step="0.01" min="1" value="{{ old('max_discount', $pesos($v?->max_discount_minor)) }}" placeholder="Optional, e.g. 200">
    </label>

    <label>Minimum spend (₱)
        <input type="number" name="min_spend" step="0.01" min="0" value="{{ old('min_spend', $pesos($v?->min_spend_minor) ?? 0) }}">
        <small class="muted">Items total needed before the voucher works. 0 = no minimum.</small>
    </label>
    <label>Who can use it?
        <select name="audience" data-voucher-audience>
            @foreach(\App\Models\Voucher::AUDIENCES as $key => $label)<option value="{{ $key }}" @selected($audience === $key)>{{ $label }}</option>@endforeach
        </select>
    </label>
    <label data-show-audience="code">Voucher code
        <input name="code" value="{{ old('code', $v?->code) }}" maxlength="40" placeholder="e.g. PASKO100" style="text-transform:uppercase">
        <small class="muted">Buyers type this at checkout. Letters, numbers, - and _ only.</small>
    </label>

    <label>Total vouchers available
        <input type="number" name="total_limit" min="1" value="{{ old('total_limit', $v?->total_limit) }}" placeholder="Leave blank for unlimited">
    </label>
    <label>Uses per buyer
        <input type="number" name="per_user_limit" min="0" value="{{ old('per_user_limit', $v?->per_user_limit ?? 1) }}" required>
        <small class="muted">0 = buyers can use it on every order.</small>
    </label>
    <label>Starts on
        <input type="date" name="starts_at" value="{{ old('starts_at', $v?->starts_at?->timezone('Asia/Manila')->format('Y-m-d')) }}">
        <small class="muted">Blank = starts right away.</small>
    </label>
    <label>Ends on
        <input type="date" name="ends_at" value="{{ old('ends_at', $v?->ends_at?->timezone('Asia/Manila')->format('Y-m-d')) }}">
        <small class="muted">Blank = never expires.</small>
    </label>
    <label class="full">Description for buyers (optional)
        <input name="description" value="{{ old('description', $v?->description) }}" maxlength="500" placeholder="e.g. Thank you for shopping with Cartzy!">
    </label>

    @if(!$v)
        <fieldset class="full voucher-deliver" data-show-audience="assigned,code">
            <legend>Give it to buyers now (optional)</legend>
            <label class="voucher-choice"><input type="radio" name="recipients" value="" @checked(!old('recipients'))> Not now</label>
            <label class="voucher-choice"><input type="radio" name="recipients" value="all_buyers" @checked(old('recipients') === 'all_buyers')> All buyers</label>
            <label class="voucher-choice"><input type="radio" name="recipients" value="emails" @checked(old('recipients') === 'emails')> Specific buyers</label>
            <label class="full">Buyer emails (one per line or separated by commas)
                <textarea name="emails" rows="3" placeholder="juan@example.com, maria@example.com">{{ old('emails') }}</textarea>
            </label>
        </fieldset>
        <label class="full voucher-choice" data-show-audience="public,new_buyers">
            <input type="checkbox" name="announce" value="1" @checked(old('announce'))> Send a notification to buyers about this voucher
        </label>
    @endif
</div>

@once
@push('styles')
<style>
.voucher-form small{font-size:11px;font-weight:400}
.voucher-form fieldset{border:1px solid var(--line);border-radius:10px;padding:14px 16px;display:flex;flex-wrap:wrap;gap:10px 22px;margin:0}
.voucher-form legend{font-size:12px;font-weight:650;color:var(--ink);padding:0 6px}
.voucher-choice{flex-direction:row;align-items:center;gap:8px;color:var(--ink);font-size:13px}
.voucher-choice input{width:16px;min-height:16px;height:16px;margin:0}
.voucher-form [hidden]{display:none!important}
.voucher-ticket{display:inline-flex;flex-direction:column;gap:2px}
.voucher-ticket strong{color:var(--accent-hover)}
.voucher-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.voucher-actions form{margin:0}
</style>
@endpush
@push('scripts')
<script>
document.querySelectorAll('[data-voucher-form]').forEach(function (form) {
    const type = form.querySelector('[data-voucher-type]');
    const audience = form.querySelector('[data-voucher-audience]');
    function sync() {
        form.querySelectorAll('[data-show-type]').forEach(function (el) {
            const show = el.dataset.showType.split(',').includes(type.value);
            el.hidden = !show;
            el.querySelectorAll('input').forEach(i => i.disabled = !show);
        });
        form.querySelectorAll('[data-show-audience]').forEach(function (el) {
            const show = el.dataset.showAudience.split(',').includes(audience.value);
            el.hidden = !show;
            el.querySelectorAll('input,textarea').forEach(i => i.disabled = !show);
        });
    }
    type.addEventListener('change', sync);
    audience.addEventListener('change', sync);
    sync();
});
</script>
@endpush
@endonce
