{{--
    Logistics sign-up, steps 3 to 5 (js/auth/logistics-steps.js shows one step at a time):
      3. Hub location: region, province, city, barangay
      4. Hub address: street and postal code
      5. Documents: valid ID and business permit
    The hub address uses hub_* names so it never clashes with the seller's pickup address on the same form.
    The documents use the same names as the seller's (id_photo, dti_permit); those are disabled unless signing up as a seller.
--}}
<div class="lg-only lg-page psgc-address-form" data-lg-page="3">
    @foreach(['region' => 'Region', 'province' => 'Province', 'city' => 'City / Municipality', 'barangay' => 'Barangay'] as $field => $label)
        <div class="mb-4">
            <label class="field-label" for="hub_{{ $field }}">{{ $label }} <span class="seller-required">*</span></label>
            <select class="auth-input lg-input psgc-{{ $field }}" id="hub_{{ $field }}" name="hub_{{ $field }}" data-initial="{{ old('hub_'.$field) }}" @if($field === 'region') data-api-regions @else disabled @endif data-lg-required>
                <option value="">Choose {{ strtolower($label) }}</option>
            </select>
            <p class="seller-field-error" id="hub_{{ $field }}_error" role="alert">{{ $errors->first('hub_'.$field) }}</p>
        </div>
    @endforeach
    <p class="seller-field-hint" data-region-status role="status">Loading locations...</p>
    <button type="button" class="lg-retry" data-region-retry hidden>Retry loading locations</button>
</div>

<div class="lg-only lg-page" data-lg-page="4">
    <div class="mb-4">
        <label class="field-label" for="hub_street_address">Street address <span class="seller-required">*</span></label>
        <input class="auth-input lg-input" id="hub_street_address" name="hub_street_address" value="{{ old('hub_street_address') }}" maxlength="500" placeholder="Building, unit or lot number and street" autocomplete="address-line1" data-lg-required>
        <p class="seller-field-hint">Where sellers and riders bring parcels.</p>
        <p class="seller-field-error" id="hub_street_address_error" role="alert">{{ $errors->first('hub_street_address') }}</p>
    </div>
    <div class="mb-4">
        <label class="field-label" for="hub_postal_code">Postal code <span class="seller-required">*</span></label>
        <input class="auth-input lg-input" id="hub_postal_code" name="hub_postal_code" value="{{ old('hub_postal_code') }}" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="4027" autocomplete="postal-code" data-lg-required>
        <p class="seller-field-error" id="hub_postal_code_error" role="alert">{{ $errors->first('hub_postal_code') }}</p>
    </div>
</div>

<div class="lg-only lg-page" data-lg-page="5">
    <p class="seller-field-hint lg-docs-hint">JPG, PNG or PDF, up to 5 MB each. Your administrator checks these before approving your hub.</p>
    @foreach([
        'id_photo' => ['Valid government ID', 'The ID of the person managing the hub.'],
        'dti_permit' => ['Business permit', 'DTI or SEC registration, or your mayor\'s permit.'],
    ] as $field => [$label, $hint])
        <div class="mb-4">
            <label class="field-label" for="lg_{{ $field }}">{{ $label }} <span class="seller-required">*</span></label>
            <input class="auth-input lg-input seller-file-input" type="file" id="lg_{{ $field }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf" data-lg-required data-lg-file>
            <p class="seller-field-hint">{{ $hint }}</p>
            <p class="seller-field-error" id="lg_{{ $field }}_error" role="alert">{{ $errors->first($field) }}</p>
        </div>
    @endforeach
    @if($errors->any())<p class="seller-field-hint">Please choose your documents again before continuing.</p>@endif
</div>
