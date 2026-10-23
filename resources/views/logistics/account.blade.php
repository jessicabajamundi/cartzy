@extends('layouts.logistics')
@section('title', 'Account Management | Logistics Hub | cartzy')
@section('page_title', 'Account Management')
@section('page_subtitle', 'Manage your registration details, business information, and credentials')
@section('content')
@php
    $nameParts = explode(' ', $hub->name ?? '', 2);
    $inputClass = 'w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300';
@endphp
<div class="hub-account">
    <div class="account-heading"><div><p class="account-eyebrow">YOUR WORKSPACE</p><h2>Account settings</h2><p>Keep your personal details and hub information up to date.</p></div><a href="{{ route('logistics.dashboard') }}" class="account-back">Back to dashboard &rarr;</a></div>
    <section class="account-overview" aria-label="Account overview">
        <div class="account-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($hub->business_name ?: $hub->name, 0, 1)) }}</div>
        <div class="account-identity"><p class="account-eyebrow">LOGISTICS ACCOUNT</p><h3>{{ $hub->business_name ?: $hub->name }}</h3><p>{{ $hub->email }}</p></div>
        <div class="account-statuses"><span class="account-badge {{ $hub->status === 'active' ? 'is-approved' : '' }}">Account: {{ ucfirst($hub->status) }}</span><span class="account-badge {{ $hub->logisticsProvider?->status === 'approved' ? 'is-approved' : '' }}">Registration: {{ ucfirst($hub->logisticsProvider?->status ?? 'pending') }}</span></div>
    </section>
    <div class="account-layout">
    <aside class="account-aside"><nav aria-label="Account sections"><a href="#account-personal"><span>01</span> Personal information</a><a href="#account-address"><span>02</span> Hub address</a><a href="#account-business"><span>03</span> Business &amp; documents</a><a href="#account-security"><span>04</span> Password &amp; security</a></nav><div class="account-note"><strong>Your account, up to date</strong><p>Use your personal name for the account holder and your business name for the hub.</p><p>New registrations require administrator approval. Decisions are sent to your email.</p></div></aside>
    <form id="logistics-account-form" data-errors="{{ $errors->toJson() }}" method="POST" action="{{ route('logistics.account.update') }}" enctype="multipart/form-data" class="psgc-address-form account-form">
        @csrf
        <p class="text-sm text-slate-500">Fields marked * are required.</p>
        <fieldset id="account-personal" class="account-card">
            <legend>Personal information</legend>
            <p class="account-section-help">Details of the person managing this logistics account.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['last_name' => ['Last name *', $nameParts[1] ?? ''], 'first_name' => ['First name *', $nameParts[0] ?? '']] as $field => [$label, $value])
                    <label class="block text-sm font-bold text-slate-600">{{ $label }}
                        <input class="{{ $inputClass }} mt-1" type="text" name="{{ $field }}" value="{{ old($field, $value) }}" maxlength="100" required>
                    </label>
                @endforeach
                <label class="block text-sm font-bold text-slate-600">Sex *
                    <select name="sex" required class="{{ $inputClass }} mt-1">
                        <option value="">Select sex</option>
                        @foreach(['Male', 'Female', 'Prefer not to say'] as $sex)
                            <option value="{{ $sex }}" @selected(old('sex', $hub->sex) === $sex)>{{ $sex }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-bold text-slate-600">E-mail (Login) *
                    <input class="{{ $inputClass }} mt-1 bg-slate-50" type="email" value="{{ $hub->email }}" readonly aria-describedby="email-help">
                    <span id="email-help" class="text-xs font-normal">Your registered login email is read-only.</span>
                </label>
                <label class="block text-sm font-bold text-slate-600">Mobile / phone number *
                    <input class="{{ $inputClass }} mt-1" type="tel" name="phone" data-international-phone inputmode="tel" autocomplete="tel" value="{{ old('phone', $hub->phone) }}" required pattern="(09[0-9]{9}|\+639[0-9]{9})" minlength="11" maxlength="13" placeholder="09171234567 or +639171234567" title="Use 09 followed by 9 digits, or +639 followed by 9 digits." aria-describedby="mobile-help">
                    <span id="mobile-help" class="block text-xs font-normal mt-1">Use 09XXXXXXXXX or +639XXXXXXXXX. Must not belong to another account.</span>
                </label>
                <label class="block text-sm font-bold text-slate-600">Telephone number *
                    <input class="{{ $inputClass }} mt-1" type="tel" name="telephone" data-digits-only inputmode="numeric" autocomplete="tel-national" value="{{ old('telephone', $hub->telephone) }}" required pattern="0[2-8][0-9]{8}" minlength="10" maxlength="10" placeholder="0281234567" title="Enter exactly 10 digits including the area code." aria-describedby="telephone-help">
                    <span id="telephone-help" class="block text-xs font-normal mt-1">10 digits with area code, e.g. 0281234567 or 0491234567. Must be unique.</span>
                </label>
                <label class="block text-sm font-bold text-slate-600">Birthday *
                    <input class="{{ $inputClass }} mt-1" type="date" id="logistics-birthday" name="birthday" value="{{ old('birthday', $hub->birthday) }}" max="{{ now()->toDateString() }}" required>
                </label>
                <label class="block text-sm font-bold text-slate-600">Age (automatically calculated) *
                    <input class="{{ $inputClass }} mt-1 bg-slate-50" type="text" id="logistics-age" value="{{ $hub->birthday ? (int) \Illuminate\Support\Carbon::parse($hub->birthday)->age : '' }}" readonly>
                </label>
            </div>
        </fieldset>
        <fieldset id="account-address" class="account-card">
            <legend>Hub address</legend>
            <p class="account-section-help">Choose your region first, then complete your hub's location.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block text-sm font-bold text-slate-600">Region
                    <select name="region" class="psgc-region {{ $inputClass }} mt-1" data-api-regions data-initial="{{ old('region', $hub->region) }}" aria-describedby="region-api-status">
                        <option value="">Select region</option>
                        @if(old('region', $hub->region))
                            <option value="{{ old('region', $hub->region) }}" selected>{{ old('region', $hub->region) }}</option>
                        @endif
                    </select>
                    <span id="region-api-status" data-region-status class="block text-xs font-normal mt-1" role="status">Regions are loaded from PSGC.</span>
                    <button type="button" data-region-retry hidden class="text-xs underline mt-1">Retry loading regions</button>
                </label>
                @foreach(['province' => 'Province', 'city' => 'Municipality / City', 'barangay' => 'Barangay'] as $field => $label)
                    <label class="block text-sm font-bold text-slate-600">{{ $label }} *
                        <select name="{{ $field }}" class="psgc-{{ $field }} {{ $inputClass }} mt-1" data-initial="{{ old($field, $hub->$field) }}" required>
                            <option value="">Select {{ strtolower($label) }}</option>
                            @if(old($field, $hub->$field))
                                <option value="{{ old($field, $hub->$field) }}" selected>{{ old($field, $hub->$field) }}</option>
                            @endif
                        </select>
                    </label>
                @endforeach
                <label class="block text-sm font-bold text-slate-600 sm:col-span-2">Street, house number, building, etc. *
                    <input class="{{ $inputClass }} mt-1" type="text" name="street_address" value="{{ old('street_address', $hub->street_address) }}" maxlength="500" required>
                </label>
                <label class="block text-sm font-bold text-slate-600">Postal code *
                    <input class="{{ $inputClass }} mt-1" type="text" name="postal_code" value="{{ old('postal_code', $hub->postal_code) }}" inputmode="numeric" autocomplete="postal-code" minlength="4" maxlength="4" pattern="[0-9]{4}" required placeholder="e.g. 4027" title="Enter exactly 4 digits." aria-describedby="postal-code-help">
                    <span id="postal-code-help" class="block text-xs font-normal mt-1">Enter exactly 4 digits.</span>
                </label>
            </div>
        </fieldset>
        <fieldset id="account-business" class="account-card account-documents">
            <legend>Business and registration documents</legend>
            <p class="account-section-help">Manage your hub name and the documents used for verification.</p>
            <label class="block text-sm font-bold text-slate-600">Business name
                <input class="{{ $inputClass }} mt-1" type="text" name="business_name" value="{{ old('business_name', $hub->business_name) }}" maxlength="255">
            </label>
            <label class="block text-sm font-bold text-slate-600">TIN number (optional)
                <input class="{{ $inputClass }} mt-1" type="text" name="tin_number" value="{{ old('tin_number', $hub->tin_number) }}" data-digits-only inputmode="numeric" minlength="9" maxlength="9" pattern="[0-9]{9}" placeholder="123456789" title="Enter exactly 9 digits without hyphens or a branch code." aria-describedby="tin-help">
                <span id="tin-help" class="block text-xs font-normal mt-1">Enter your 9-digit Taxpayer Identification Number, without hyphens or a branch code.</span>
            </label>
            <p class="text-sm text-slate-500">JPG, PNG, or PDF, up to 5 MB each. Leave an upload empty to keep your current document. A replacement ID is marked pending review.</p>
            @foreach(['id_photo' => 'Upload ID', 'dti_permit' => 'Upload business / DTI permit'] as $field => $label)
                <label class="block text-sm font-bold text-slate-600">{{ $label }}
                    <input class="{{ $inputClass }} mt-1" type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf">
                </label>
                @if($hub->$field)
                    <a class="inline-block text-sm underline text-purple-700" href="{{ asset('storage/' . $hub->$field) }}" target="_blank" rel="noopener">View current {{ $field === 'id_photo' ? 'ID' : 'business / DTI permit' }}</a>
                @else
                    <p class="text-xs text-slate-500">No {{ $field === 'id_photo' ? 'ID' : 'business / DTI permit' }} uploaded.</p>
                @endif
            @endforeach
            <p class="text-sm text-slate-500">ID status: {{ ucfirst($hub->id_status ?? 'unverified') }}</p>
        </fieldset>
        <fieldset id="account-security" class="account-card">
            <legend>Change password</legend>
            <p class="text-sm text-slate-500 mb-3">Leave blank to keep your current password. New passwords must contain at least 8 characters.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block text-sm font-bold text-slate-600">New password
                    <input class="{{ $inputClass }} mt-1" type="password" name="password" minlength="8" autocomplete="new-password">
                </label>
                <label class="block text-sm font-bold text-slate-600">Confirm new password
                    <input class="{{ $inputClass }} mt-1" type="password" name="password_confirmation" autocomplete="new-password">
                </label>
            </div>
        </fieldset>
        <div class="account-savebar"><span id="account-save-status" role="status">Changes are saved when you click Save Changes.</span><button type="submit" class="account-save">Save Changes</button></div>
    </form>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="account-signout">
        @csrf
        <div><strong>Finished for now?</strong><p>Sign out of the Logistics Hub on this device.</p></div>
        <button type="submit">Logout</button>
    </form>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-account.css') }}?v={{ filemtime(public_path('css/logistics-account.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/buyer/address-modal.js') }}?v={{ filemtime(public_path('js/buyer/address-modal.js')) }}"></script>
<script src="{{ asset('js/logistics-account.js') }}?v={{ filemtime(public_path('js/logistics-account.js')) }}"></script>
@endpush
