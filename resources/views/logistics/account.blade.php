@extends('layouts.logistics')
@section('title', 'Account Management | Logistics Hub | cartzy')
@section('page_title', 'Account Management')
@section('page_subtitle', 'Manage your registration details, business information, and credentials')
@section('content')
@php
    $nameParts = explode(' ', $hub->name ?? '', 2);
    $registration = $hub->logisticsProvider?->status ?? 'pending';
    $idStatus = $hub->id_status ?? 'unverified';
@endphp
<div class="hub-account">
    <div class="page-heading">
        <div class="page-identity">
            <span class="page-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($hub->business_name ?: $hub->name, 0, 1)) }}</span>
            <div>
                <p class="eyebrow">Logistics account</p>
                <h2>{{ $hub->business_name ?: $hub->name }}</h2>
                <p class="muted">{{ $hub->email }}</p>
            </div>
        </div>
        <a href="{{ route('logistics.dashboard') }}" class="button subtle">Back to dashboard</a>
    </div>

    <section class="stats-grid" aria-label="Account overview">
        <div class="stat-card"><span>Account status</span><strong>{{ ucfirst($hub->status) }}</strong><small><span class="badge {{ $hub->status === 'active' ? 'good' : 'warning' }}">{{ $hub->status === 'active' ? 'Can sign in' : 'Restricted' }}</span></small></div>
        <div class="stat-card"><span>Registration</span><strong>{{ ucfirst($registration) }}</strong><small>Decisions are sent to your email</small></div>
        <div class="stat-card"><span>ID verification</span><strong>{{ ucfirst($idStatus) }}</strong><small>A replacement ID is marked pending review</small></div>
        <div class="stat-card"><span>Member since</span><strong>{{ $hub->created_at?->format('M j, Y') ?? '—' }}</strong><small>{{ $hub->city ?: 'Hub location not set' }}</small></div>
    </section>

    <form id="logistics-account-form" data-errors="{{ $errors->toJson() }}" method="POST" action="{{ route('logistics.account.update') }}" enctype="multipart/form-data" class="psgc-address-form">
        @csrf
        <div class="two-columns">
            <div>
                <section id="account-personal" class="panel" aria-labelledby="account-personal-title">
                    <header class="panel-header"><div><h3 id="account-personal-title">Personal information</h3><p>Details of the person managing this logistics account. Fields marked * are required.</p></div></header>
                    <div class="form-grid padded">
                        @foreach(['last_name' => ['Last name *', $nameParts[1] ?? ''], 'first_name' => ['First name *', $nameParts[0] ?? '']] as $field => [$label, $value])
                            <label>{{ $label }}
                                <input type="text" name="{{ $field }}" value="{{ old($field, $value) }}" maxlength="100" required>
                            </label>
                        @endforeach
                        <label>Sex *
                            <select name="sex" required>
                                <option value="">Select sex</option>
                                @foreach(['Male', 'Female', 'Prefer not to say'] as $sex)
                                    <option value="{{ $sex }}" @selected(old('sex', $hub->sex) === $sex)>{{ $sex }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>E-mail (Login) *
                            <input type="email" value="{{ $hub->email }}" readonly aria-describedby="email-help">
                            <small id="email-help">Your registered login email is read-only.</small>
                        </label>
                        <label>Mobile / phone number *
                            <input type="tel" name="phone" data-international-phone inputmode="tel" autocomplete="tel" value="{{ old('phone', $hub->phone) }}" required pattern="(09[0-9]{9}|\+639[0-9]{9})" minlength="11" maxlength="13" placeholder="09171234567 or +639171234567" title="Use 09 followed by 9 digits, or +639 followed by 9 digits." aria-describedby="mobile-help">
                            <small id="mobile-help">Use 09XXXXXXXXX or +639XXXXXXXXX. Must not belong to another account.</small>
                        </label>
                        <label>Telephone number *
                            <input type="tel" name="telephone" data-digits-only inputmode="numeric" autocomplete="tel-national" value="{{ old('telephone', $hub->telephone) }}" required pattern="0[2-8][0-9]{8}" minlength="10" maxlength="10" placeholder="0281234567" title="Enter exactly 10 digits including the area code." aria-describedby="telephone-help">
                            <small id="telephone-help">10 digits with area code, e.g. 0281234567. Must be unique.</small>
                        </label>
                        <label>Birthday *
                            <input type="date" id="logistics-birthday" name="birthday" value="{{ old('birthday', $hub->birthday) }}" max="{{ now()->subYearsNoOverflow(18)->toDateString() }}" required aria-describedby="birthday-help">
                            <small id="birthday-help">You must be at least 18 years old.</small>
                        </label>
                        <label>Age (automatically calculated)
                            <input type="text" id="logistics-age" value="{{ $hub->birthday ? (int) \Illuminate\Support\Carbon::parse($hub->birthday)->age : '' }}" readonly>
                        </label>
                    </div>
                </section>

                <section id="account-address" class="panel" aria-labelledby="account-address-title">
                    <header class="panel-header"><div><h3 id="account-address-title">Hub address</h3><p>Choose your region first, then complete your hub's location.</p></div></header>
                    <div class="form-grid padded">
                        <label>Region
                            <select name="region" class="psgc-region" data-api-regions data-initial="{{ old('region', $hub->region) }}" aria-describedby="region-api-status">
                                <option value="">Select region</option>
                                @if(old('region', $hub->region))
                                    <option value="{{ old('region', $hub->region) }}" selected>{{ old('region', $hub->region) }}</option>
                                @endif
                            </select>
                            <small id="region-api-status" data-region-status role="status">Regions are loaded from PSGC.</small>
                            <button type="button" data-region-retry hidden class="text-link">Retry loading regions</button>
                        </label>
                        @foreach(['province' => 'Province', 'city' => 'Municipality / City', 'barangay' => 'Barangay'] as $field => $label)
                            <label>{{ $label }} *
                                <select name="{{ $field }}" class="psgc-{{ $field }}" data-initial="{{ old($field, $hub->$field) }}" required>
                                    <option value="">Select {{ strtolower($label) }}</option>
                                    @if(old($field, $hub->$field))
                                        <option value="{{ old($field, $hub->$field) }}" selected>{{ old($field, $hub->$field) }}</option>
                                    @endif
                                </select>
                            </label>
                        @endforeach
                        <label class="full">Street, house number, building, etc. *
                            <input type="text" name="street_address" value="{{ old('street_address', $hub->street_address) }}" maxlength="500" required>
                        </label>
                        <label>Postal code *
                            <input type="text" name="postal_code" value="{{ old('postal_code', $hub->postal_code) }}" inputmode="numeric" autocomplete="postal-code" minlength="4" maxlength="4" pattern="[0-9]{4}" required placeholder="e.g. 4027" title="Enter exactly 4 digits." aria-describedby="postal-code-help">
                            <small id="postal-code-help">Enter exactly 4 digits.</small>
                        </label>
                    </div>
                </section>
            </div>

            <div>
                <section id="account-business" class="panel" aria-labelledby="account-business-title">
                    <header class="panel-header"><div><h3 id="account-business-title">Business &amp; documents</h3><p>Your hub name and the documents used for verification.</p></div><span class="badge {{ $idStatus === 'verified' ? 'good' : 'warning' }}">ID {{ $idStatus }}</span></header>
                    <div class="form-stack padded">
                        <label>Business name
                            <input type="text" name="business_name" value="{{ old('business_name', $hub->business_name) }}" maxlength="255">
                        </label>
                        <label>TIN number (optional)
                            <input type="text" name="tin_number" value="{{ old('tin_number', $hub->tin_number) }}" data-digits-only inputmode="numeric" minlength="9" maxlength="9" pattern="[0-9]{9}" placeholder="123456789" title="Enter exactly 9 digits without hyphens or a branch code." aria-describedby="tin-help">
                            <small id="tin-help">9-digit Taxpayer Identification Number, without hyphens or a branch code.</small>
                        </label>
                        <p class="notice">JPG, PNG, or PDF, up to 5 MB each. Leave an upload empty to keep your current document. A replacement ID is marked pending review.</p>
                        @foreach(['id_photo' => ['Upload ID', 'ID'], 'dti_permit' => ['Upload business / DTI permit', 'business / DTI permit']] as $field => [$label, $noun])
                            <label>{{ $label }}
                                <input type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf">
                                @if($hub->$field)
                                    <a class="text-link" href="{{ Storage::disk('public')->url($hub->$field) }}" target="_blank" rel="noopener">View current {{ $noun }} &nearr;</a>
                                @else
                                    <small>No {{ $noun }} uploaded.</small>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>

                <section id="account-security" class="panel" aria-labelledby="account-security-title">
                    <header class="panel-header"><div><h3 id="account-security-title">Change password</h3><p>Leave blank to keep your current password. Use at least 8 characters.</p></div></header>
                    <div class="form-stack padded">
                        <label>New password
                            <input type="password" name="password" minlength="8" autocomplete="new-password">
                        </label>
                        <label>Confirm new password
                            <input type="password" name="password_confirmation" autocomplete="new-password">
                        </label>
                    </div>
                </section>
            </div>
        </div>

        <div class="panel account-savebar">
            <span id="account-save-status" role="status">Changes are saved when you click Save changes.</span>
            <button type="submit" class="button">Save changes</button>
        </div>
    </form>

    <section class="panel">
        <form method="POST" action="{{ route('logout') }}" class="panel-header account-signout">
            @csrf
            <div><h3>Finished for now?</h3><p>Sign out of the Logistics Hub on this device.</p></div>
            <button type="submit" class="button danger">Log out</button>
        </form>
    </section>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-account.css') }}?v={{ filemtime(public_path('css/logistics-account.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/buyer/address-modal.js') }}?v={{ filemtime(public_path('js/buyer/address-modal.js')) }}"></script>
<script src="{{ asset('js/logistics-account.js') }}?v={{ filemtime(public_path('js/logistics-account.js')) }}"></script>
@endpush
