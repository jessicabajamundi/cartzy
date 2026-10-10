@php
    $addressKey = $entry?->id ?? ($legacyEntry ?? false ? 'legacy' : 'new');
    $restoreAddress = $errors->addressBook->any() && (string) old('form_key') === (string) $addressKey;

    $valLabel = $restoreAddress ? old('label') : ($entry?->label ?? 'Home');
    $valRecipient = $restoreAddress ? old('recipient') : ($entry?->recipient ?? $user->name);
    $valPhone = $restoreAddress ? old('phone') : ($entry?->phone ?? $user->phone);
    $valLine1 = $restoreAddress ? old('line1') : ($entry?->line1 ?? '');
    $valProvince = $restoreAddress ? old('province') : ($entry?->province ?? '');
    $valCity = $restoreAddress ? old('city') : ($entry?->city ?? '');
    $valBarangay = $restoreAddress ? old('barangay') : ($entry?->barangay ?? '');
    $valPostal = $restoreAddress ? old('postal_code') : ($entry?->postal_code ?? '');
    $valRegion = $restoreAddress ? old('region') : ($valProvince === 'Metro Manila' ? 'NCR' : ($user->region ?? ''));

    $regionList = [
        'NCR'   => 'NCR – Metro Manila',
        'CAR'   => 'CAR – Cordillera Administrative Region',
        'I'     => 'Region I – Ilocos Region',
        'II'    => 'Region II – Cagayan Valley',
        'III'   => 'Region III – Central Luzon',
        'IV-A'  => 'Region IV-A – CALABARZON',
        'IV-B'  => 'Region IV-B – MIMAROPA',
        'V'     => 'Region V – Bicol Region',
        'VI'    => 'Region VI – Western Visayas',
        'VII'   => 'Region VII – Central Visayas',
        'VIII'  => 'Region VIII – Eastern Visayas',
        'IX'    => 'Region IX – Zamboanga Peninsula',
        'X'     => 'Region X – Northern Mindanao',
        'XI'    => 'Region XI – Davao Region',
        'XII'   => 'Region XII – SOCCSKSARGEN',
        'XIII'  => 'Region XIII – CARAGA',
        'BARMM' => 'BARMM – Bangsamoro',
    ];
@endphp
<form method="POST" action="{{ route('account.addresses.save') }}" class="space-y-4 mt-4 psgc-address-form" data-form-key="{{ $addressKey }}">
    @csrf
    <input type="hidden" name="form_key" value="{{ $addressKey }}">
    @if($entry?->id)<input type="hidden" name="address_id" value="{{ $entry->id }}">@endif
    @if($legacyEntry ?? false)<input type="hidden" name="legacy_address" value="1">@endif

    <div class="grid gap-4 sm:grid-cols-2">
        {{-- Label --}}
        <label class="block">
            <span class="settings-label">Address label</span>
            <select name="label" class="settings-input" required>
                @foreach(['Home', 'Work', 'School', 'Other'] as $label)
                    <option value="{{ $label }}" @selected($valLabel === $label)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        {{-- Recipient Name --}}
        <label class="block">
            <span class="settings-label">Recipient name</span>
            <input class="settings-input" name="recipient" type="text" required maxlength="255"
                placeholder="Full name of recipient"
                value="{{ $valRecipient }}">
        </label>

        {{-- Contact Number --}}
        <label class="block">
            <span class="settings-label">Contact number</span>
            <input class="settings-input" name="phone" type="tel" inputmode="numeric" pattern="[0-9]*" required maxlength="20"
                placeholder="e.g. 09171234567"
                value="{{ $valPhone }}"
                oninput="this.value = this.value.replace(/\D/g, '')">
        </label>

        {{-- Region (PSGC API) --}}
        <label class="block">
            <span class="settings-label">Region</span>
            <select name="region" class="settings-input psgc-region" data-initial="{{ $valRegion }}">
                <option value="">Select Region</option>
                @foreach($regionList as $code => $name)
                    <option value="{{ $code }}" @selected($valRegion === $code)>{{ $name }}</option>
                @endforeach
            </select>
        </label>

        {{-- Province (PSGC API) --}}
        <label class="block">
            <span class="settings-label">Province</span>
            <select name="province" class="settings-input psgc-province" required data-initial="{{ $valProvince }}">
                <option value="">Select Province</option>
                @if($valProvince)
                    <option value="{{ $valProvince }}" selected>{{ $valProvince }}</option>
                @endif
            </select>
        </label>

        {{-- City / Municipality (PSGC API) --}}
        <label class="block">
            <span class="settings-label">City / Municipality</span>
            <select name="city" class="settings-input psgc-city" required data-initial="{{ $valCity }}">
                <option value="">Select City / Municipality</option>
                @if($valCity)
                    <option value="{{ $valCity }}" selected>{{ $valCity }}</option>
                @endif
            </select>
        </label>

        {{-- Barangay (PSGC API) --}}
        <label class="block">
            <span class="settings-label">Barangay</span>
            <select name="barangay" class="settings-input psgc-barangay" required data-initial="{{ $valBarangay }}">
                <option value="">Select Barangay</option>
                @if($valBarangay)
                    <option value="{{ $valBarangay }}" selected>{{ $valBarangay }}</option>
                @endif
            </select>
        </label>

        {{-- Postal Code --}}
        <label class="block">
            <span class="settings-label">Postal code</span>
            <input class="settings-input" name="postal_code" type="text" inputmode="numeric" pattern="[0-9]*" required maxlength="20"
                placeholder="e.g. 1100"
                value="{{ $valPostal }}"
                oninput="this.value = this.value.replace(/\D/g, '')">
        </label>

        {{-- House / Unit and Street --}}
        <label class="block sm:col-span-2">
            <span class="settings-label">House / unit and street</span>
            <input class="settings-input" name="line1" type="text" required maxlength="255"
                placeholder="Building, street name, house/unit number"
                value="{{ $valLine1 }}">
        </label>
    </div>

    @if(!$entry?->is_default)
        <label class="flex items-center gap-2 text-sm text-gray-600 mt-2">
            <input type="checkbox" name="is_default" value="1" @checked($restoreAddress && old('is_default'))>
            Use as my default delivery address
        </label>
    @endif

    @if($isModal ?? false)
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
            <button type="button" data-close-address-modal class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 hover:text-black transition cursor-pointer">Cancel</button>
            <button class="settings-button" type="submit">Save address</button>
        </div>
    @else
        <button class="settings-button" type="submit">Save address</button>
    @endif
</form>
