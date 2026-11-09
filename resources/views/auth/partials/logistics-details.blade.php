@php
    $logisticsSignup = old('role', $selectedRole ?? 'buyer') === 'logistics';
    $chosenRegions = (array) old('service_regions', []);
@endphp
{{-- Logistics partner sign-up steps 2-5 of 7 (step 1 is the personal details in register.blade.php,
     6 is sign-in and 7 is email verification). register.js shows one step at a time. --}}
<div id="logisticsFieldsWrapper" style="display: {{ $logisticsSignup ? 'block' : 'none' }};">
    {{-- Step 2: Your company --}}
    <div data-signup-page="2">
        <div class="mb-4">
            <label class="field-label" for="business_name">Company name</label>
            <input class="auth-input lg-input" id="business_name" name="business_name" value="{{ old('business_name') }}" maxlength="255" autocomplete="organization" data-logistics-required>
            <p class="seller-field-hint">As registered with the SEC. It must not be used by another logistics partner.</p>
            <p class="seller-field-error" id="business_name_error" role="alert">{{ $errors->first('business_name') }}</p>
        </div>
        <div class="form-grid-2 mb-4">
            <div>
                <label class="field-label" for="sec_registration_no">SEC registration no.</label>
                <input class="auth-input lg-input" id="sec_registration_no" name="sec_registration_no" value="{{ old('sec_registration_no') }}" placeholder="CS202012345" maxlength="30" pattern="[A-Za-z0-9][A-Za-z0-9\-]{4,29}" title="Letters, digits and dashes, as shown on your SEC certificate." autocapitalize="characters" data-logistics-required>
                <p class="seller-field-error" id="sec_registration_no_error" role="alert">{{ $errors->first('sec_registration_no') }}</p>
            </div>
            <div>
                <label class="field-label" for="logistics_tin_number">TIN</label>
                <input class="auth-input lg-input" id="logistics_tin_number" name="tin_number" value="{{ $logisticsSignup ? old('tin_number') : '' }}" placeholder="000-000-000-000" inputmode="numeric" maxlength="15" pattern="[0-9]{3}-?[0-9]{3}-?[0-9]{3}-?[0-9]{3}" title="12 digits: your 9-digit TIN and 3-digit branch code." data-tin-input data-logistics-required>
                <p class="seller-field-error" id="logistics_tin_number_error" role="alert">{{ $logisticsSignup ? $errors->first('tin_number') : '' }}</p>
            </div>
        </div>
        <div class="mb-4">
            <label class="field-label" for="contact_position">Contact person's position</label>
            <input class="auth-input lg-input" id="contact_position" name="contact_position" value="{{ old('contact_position') }}" placeholder="e.g. Operations Manager" maxlength="100" autocomplete="organization-title" data-logistics-required>
            <p class="seller-field-hint">The contact person is the name you entered above.</p>
            <p class="seller-field-error" id="contact_position_error" role="alert">{{ $errors->first('contact_position') }}</p>
        </div>
    </div>

    {{-- Step 3: Service areas --}}
    <div data-signup-page="3">
        <fieldset class="lg-regions" aria-describedby="service_regions_hint service_regions_error">
            <legend class="sr-only">Service areas</legend>
            <p class="seller-field-hint" id="service_regions_hint">Choose every region you deliver to. Riders see these when choosing a partner.</p>
            <div class="lg-region-grid">
                @foreach(\App\Models\LogisticsProvider::SERVICE_REGIONS as $value => $label)
                    <label class="lg-region">
                        <input type="checkbox" name="service_regions[]" value="{{ $value }}" @checked(in_array($value, $chosenRegions, true)) data-service-region>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <p class="seller-field-error" id="service_regions_error" role="alert">{{ $errors->first('service_regions') ?: $errors->first('service_regions.*') }}</p>
        </fieldset>
    </div>

    {{-- Step 4: Office address --}}
    <div data-signup-page="4">
        <div class="psgc-address-form">
            <div class="form-grid-2 mb-4">
                @foreach(['region' => ['Region', 'Choose a region'], 'province' => ['Province', 'Choose a region first'], 'city' => ['City / Municipality', 'Choose a province first'], 'barangay' => ['Barangay', 'Choose a city / municipality first']] as $field => [$label, $placeholder])
                    <div>
                        <label class="field-label" for="office_{{ $field }}">{{ $label }}</label>
                        <select class="auth-input lg-input psgc-{{ $field }}" id="office_{{ $field }}" name="office_{{ $field }}" data-initial="{{ old('office_' . $field) }}" @if($field === 'region') data-api-regions @else disabled @endif data-logistics-required>
                            <option value="">{{ $placeholder }}</option>
                        </select>
                        <p class="seller-field-error" id="office_{{ $field }}_error" role="alert">{{ $errors->first('office_' . $field) }}</p>
                    </div>
                @endforeach
            </div>
            <p class="seller-field-hint" data-region-status role="status">Loading office locations...</p>
            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="office_street">Street address</label>
                    <input class="auth-input lg-input" id="office_street" name="office_street" value="{{ old('office_street') }}" maxlength="500" placeholder="House, unit or lot number and street" autocomplete="address-line1" data-logistics-required>
                    <p class="seller-field-error" id="office_street_error" role="alert">{{ $errors->first('office_street') }}</p>
                </div>
                <div>
                    <label class="field-label" for="office_postal_code">Postal code</label>
                    <input class="auth-input lg-input" id="office_postal_code" name="office_postal_code" value="{{ old('office_postal_code') }}" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" title="4 digits" autocomplete="postal-code" data-digits-only data-logistics-required>
                    <p class="seller-field-error" id="office_postal_code_error" role="alert">{{ $errors->first('office_postal_code') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 5: Business documents --}}
    <div data-signup-page="5">
        <p class="seller-field-hint">JPG, PNG or PDF, up to 5 MB each.</p>
        @foreach([
            'sec_certificate' => ['logistics_sec_certificate', 'SEC Certificate', 'SEC Certificate of Registration.'],
            'bir_certificate' => ['logistics_bir_certificate', 'BIR Form 2303', 'BIR Certificate of Registration (Form 2303).'],
            'business_permit' => ['business_permit', "Mayor's / Business Permit", "Current Mayor's or Business Permit."],
            'id_photo' => ['logistics_id_photo', 'Valid ID of Authorized Representative', 'Government-issued ID of the contact person named above.'],
        ] as $field => [$id, $label, $hint])
            <div class="mb-4">
                <label class="field-label" for="{{ $id }}">{{ $label }}</label>
                <input class="auth-input lg-input lg-file-input" type="file" id="{{ $id }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf" data-logistics-document>
                <p class="seller-field-hint">{{ $hint }}</p>
                <p class="seller-field-error" id="{{ $id }}_error" role="alert">{{ $logisticsSignup ? $errors->first($field) : '' }}</p>
            </div>
        @endforeach
        @if($logisticsSignup && $errors->any())<p class="seller-field-hint">Please select your documents again before continuing.</p>@endif
    </div>
</div>
<style>
#logisticsFieldsWrapper .lg-input { padding-left: 16px !important; width: 100%; }
#logisticsFieldsWrapper select:disabled { background: #f5f2f7; color: #91879b; }
#logisticsFieldsWrapper .lg-file-input { height: auto; padding: 12px !important; background: #f8f5fa; }
.lg-file-input::file-selector-button { border: 1px solid #ddd3e6; background: #f5f0f9; color: #564b68; border-radius: 6px; padding: 7px 12px; margin-right: 12px; font: inherit; font-weight: 600; cursor: pointer; }
.lg-regions { border: 0; margin: 0; padding: 0; min-width: 0; }
.lg-region-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 10px; }
.lg-region { display: flex; align-items: center; gap: 10px; min-height: 40px; padding: 8px 12px; border: 1px solid #e1dde7; border-radius: 8px; background: #fff; color: #282133; font-size: .8rem; line-height: 1.3; cursor: pointer; transition: border-color .15s ease, background-color .15s ease; }
.lg-region:hover { border-color: #c9bfd6; }
.lg-region:has(input:checked) { border-color: #6f6382; background: #f6f2f9; }
.lg-region:has(input:focus-visible) { outline: 2px solid #564b68; outline-offset: 2px; }
.lg-region input { flex: none; width: 15px; height: 15px; margin: 0; accent-color: #564b68; }
.lg-regions[aria-invalid="true"] .lg-region { border-color: #fca5a5; }
@media (max-width: 380px) { .lg-region-grid { grid-template-columns: 1fr; } }
</style>
