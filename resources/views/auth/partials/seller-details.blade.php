<div id="sellerFieldsWrapper" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'seller' ? 'block' : 'none' }};">
    <div class="field-block"><h3 class="field-heading">Your shop</h3></div>
    <div class="mb-4">
        <label class="field-label" for="shop_name">Shop name <span class="seller-required">*</span></label>
        <input class="auth-input seller-detail-input" id="shop_name" name="shop_name" value="{{ old('shop_name') }}" minlength="3" maxlength="30" data-seller-required>
        <p class="seller-field-hint">3 to 30 characters. Buyers see this name. It must not be used by another shop.</p>
        <p class="seller-field-error" id="shop_name_error" role="alert">{{ $errors->first('shop_name') }}</p>
    </div>
    <div class="form-grid-2 mb-4">
        <div>
            <label class="field-label" for="shop_category">Shop category <span class="seller-required">*</span></label>
            <select class="auth-input seller-detail-input" id="shop_category" name="shop_category" data-seller-required>
                <option value="">Choose a category</option>
                @foreach($shopCategories as $category)
                    <option value="{{ $category->id }}" @selected(old('shop_category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <p class="seller-field-error" id="shop_category_error" role="alert">{{ $errors->first('shop_category') }}</p>
        </div>
        <div>
            <label class="field-label" for="seller_type">Seller type <span class="seller-required">*</span></label>
            <select class="auth-input seller-detail-input" id="seller_type" name="seller_type" data-seller-required>
                <option value="">Choose a seller type</option>
                @foreach(['individual' => 'Individual', 'sole_proprietorship' => 'Sole Proprietorship', 'corporation' => 'Corporation'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('seller_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="seller-field-error" id="seller_type_error" role="alert">{{ $errors->first('seller_type') }}</p>
        </div>
    </div>
    <div class="mb-4">
        <label class="field-label" for="tin_number">TIN <span class="seller-required">*</span></label>
        <input class="auth-input seller-detail-input" id="tin_number" name="tin_number" value="{{ old('tin_number') }}" placeholder="000-000-000-000" inputmode="numeric" maxlength="15" pattern="[0-9]{3}-?[0-9]{3}-?[0-9]{3}-?[0-9]{3}" data-seller-required>
        <p class="seller-field-hint">12 digits: your 9-digit TIN and 3-digit branch code (000 for the main branch).</p>
        <p class="seller-field-error" id="tin_number_error" role="alert">{{ $errors->first('tin_number') }}</p>
    </div>
    <div class="field-block"><h3 class="field-heading">Pickup address</h3></div>
    <div class="psgc-address-form">
        <div class="form-grid-2 mb-4">
            @foreach(['region' => 'Region', 'province' => 'Province', 'city' => 'City / Municipality', 'barangay' => 'Barangay'] as $field => $label)
                <div>
                    <label class="field-label" for="{{ $field }}">{{ $label }} <span class="seller-required">*</span></label>
                    <select class="auth-input seller-detail-input psgc-{{ $field }}" id="{{ $field }}" name="{{ $field }}" data-initial="{{ old($field) }}" @if($field === 'region') data-api-regions @else disabled @endif data-seller-required>
                        <option value="">Choose {{ strtolower($label) }}</option>
                    </select>
                    <p class="seller-field-error" id="{{ $field }}_error" role="alert">{{ $errors->first($field) }}</p>
                </div>
            @endforeach
        </div>
        <p class="seller-field-hint" data-region-status role="status">Loading pickup locations...</p>
        <div class="form-grid-2 mb-4">
            <div>
                <label class="field-label" for="street_address">Street address <span class="seller-required">*</span></label>
                <input class="auth-input seller-detail-input" id="street_address" name="street_address" value="{{ old('street_address') }}" maxlength="500" placeholder="House, unit or lot number and street" data-seller-required>
                <p class="seller-field-error" id="street_address_error" role="alert">{{ $errors->first('street_address') }}</p>
            </div>
            <div>
                <label class="field-label" for="postal_code">Postal code <span class="seller-required">*</span></label>
                <input class="auth-input seller-detail-input" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" data-seller-required>
                <p class="seller-field-error" id="postal_code_error" role="alert">{{ $errors->first('postal_code') }}</p>
            </div>
        </div>
    </div>
    <div class="field-block"><h3 class="field-heading">Documents</h3><p class="seller-field-hint">JPG, PNG or PDF, up to 5 MB each. You may upload any supporting documents below. Required documents are marked with an asterisk (*).</p></div>
    @foreach([
        'id_photo' => ['Government-issued ID', 'Required for all seller types. For corporations, the ID of the authorized representative.', 'all'],
        'dti_permit' => ['DTI Certificate', 'Required for sole proprietorships.', 'sole_proprietorship'],
        'sec_certificate' => ['SEC Certificate', 'Required for corporations.', 'corporation'],
        'bir_certificate' => ['BIR Form 2303', 'Required for sole proprietorships and corporations.', 'business'],
    ] as $field => [$label, $hint, $types])
        <div class="mb-4" data-document-types="{{ $types }}">
            <label class="field-label" for="{{ $field }}">{{ $label }} <span class="seller-required">*</span></label>
            <input class="auth-input seller-detail-input seller-file-input" type="file" id="{{ $field }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf" data-seller-document>
            <p class="seller-field-hint">{{ $hint }}</p>
            <p class="seller-field-error" id="{{ $field }}_error" role="alert">{{ $errors->first($field) }}</p>
        </div>
    @endforeach
    @if($errors->any())<p class="seller-field-hint">Please select your documents again before continuing.</p>@endif
</div>
<style>
#sellerFieldsWrapper .seller-detail-input { padding-left: 16px !important; width: 100%; }
#sellerFieldsWrapper .field-block { margin-top: 26px; }
#sellerFieldsWrapper .field-heading { font-size: 17px; }
.seller-field-hint { font-size: 12px; line-height: 1.6; color: #82768d; margin: 7px 0 15px; }
.seller-field-error { color: #dc2626; font-size: 12px; margin-top: 6px; }
.seller-field-error:empty { display: none; }
.seller-required { color: #dc2626; }
#sellerFieldsWrapper .seller-file-input { height: auto; padding: 12px !important; }
.seller-file-input::file-selector-button { border: 1px solid #ddd3e6; background: #f5f0f9; color: #564b68; border-radius: 6px; padding: 8px 12px; margin-right: 12px; font: inherit; cursor: pointer; }
#sellerFieldsWrapper select:disabled { background: #f5f2f7; color: #91879b; }
</style>

