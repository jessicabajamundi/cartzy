@php
    $promoProduct = $discountProduct ?? null;
    $promoType = old('discount_type', $promoProduct?->discount_type);
    $promoValue = old('discount_value', $promoProduct?->discount_value);
@endphp
<div class="sale-settings" data-product-discount>
    <fieldset class="sale-section">
        <legend>Choose your discount</legend>
        <p class="sale-section-description">The same offer applies to every variation.</p>
        <div class="sale-types">
            @foreach(['percentage' => ['%', 'Percentage', 'Take a percent off'], 'fixed' => ['PHP', 'Fixed amount', 'Take pesos off'], '' => ['&mdash;', 'No discount', 'Use regular prices']] as $type => [$symbol, $title, $hint])
                <label class="sale-type">
                    <input type="radio" name="discount_type" value="{{ $type }}" @checked(($promoType ?? '') === $type)>
                    <span class="sale-type-body"><span class="sale-type-symbol" aria-hidden="true">{!! $symbol !!}</span><strong>{{ $title }}</strong><small>{{ $hint }}</small></span>
                </label>
            @endforeach
        </div>
        <div class="sale-value-row">
            <label for="sale-discount-value" data-discount-label>{{ $promoType === 'percentage' ? 'Percentage off (%)' : 'Amount off (PHP)' }}</label>
            <input id="sale-discount-value" name="discount_value" type="number" min="0.01" step="0.01" max="{{ $promoType === 'percentage' ? '99.99' : '99999999.99' }}" value="{{ $promoValue ?: '' }}" placeholder="Enter discount" aria-describedby="sale-value-hint sale-value-error" @disabled(!$promoType) @required($promoType)>
            <div class="sale-presets" aria-label="Quick percentage discounts" @if($promoType !== 'percentage') hidden @endif>
                @foreach([5, 10, 15, 20] as $percent)<button type="button" data-sale-preset="{{ $percent }}" aria-pressed="false">{{ $percent }}%</button>@endforeach
            </div>
            <small id="sale-value-hint">Your regular prices are kept. Choose No discount to end an offer.</small>
            <p id="sale-value-error" class="sale-field-error" role="status"></p>
        </div>
    </fieldset>
    <fieldset class="sale-section sale-schedule">
        <legend>Schedule <span>Optional</span></legend>
        <p class="sale-section-description">Leave blank to start now, with no end date. All times are PHT (UTC+8).</p>
        <div class="form-grid">
            <label>Starts at
                <input type="datetime-local" name="discount_starts_at" value="{{ old('discount_starts_at', $promoProduct?->discount_starts_at?->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')) }}" @disabled(!$promoType)>
            </label>
            <label>Ends at
                <input type="datetime-local" name="discount_ends_at" value="{{ old('discount_ends_at', $promoProduct?->discount_ends_at?->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')) }}" @disabled(!$promoType)>
            </label>
        </div>
        <p class="sale-field-error" data-schedule-error role="status"></p>
    </fieldset>
</div>
@once
@push('scripts')
<script type="module" src="{{ asset('js/seller-sale.js') }}?v={{ filemtime(public_path('js/seller-sale.js')) }}"></script>
@endpush
@endonce
