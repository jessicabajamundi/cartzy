@extends('layouts.seller')
@section('page_title', 'Store settings')

@section('content')
@php
    $user = auth()->user();
    $nameParts = explode(' ', $user->name ?? '', 2);
    $firstName = old('first_name', $nameParts[0] ?? '');
    $lastName = old('last_name', $nameParts[1] ?? '');

    $valRegion = old('region', $user->region ?? '');
    $address = $shop?->pickupAddress;
    $valProvince = old('province', $address?->province ?? $user->province ?? '');
    $valCity = old('city', $address?->city ?? $user->city ?? '');
    $valBarangay = old('barangay', $address?->barangay ?? $user->barangay ?? '');
    $valLine1 = old('line1', $address?->line1 ?? $user->street_address ?? '');
    $valPostal = old('postal_code', $address?->postal_code ?? $user->postal_code ?? '');
    $valRecipient = old('recipient', $address?->recipient ?? $user->name);
    $valPhone = old('phone', $address?->phone ?? $user->phone);

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

    $lineOfBusinessList = [
        'Fashion & Apparel',
        'Beauty & Personal Care',
        'Health & Wellness',
        'Electronics & Gadgets',
        'Food & Beverages',
        'Home, Furniture & Living',
        'Baby, Kids & Toys',
        'Sports, Fitness & Outdoors',
        'Groceries & Daily Essentials',
        'Automotive & Motorcycle Parts',
        'Arts, Crafts & Stationery',
        'General Merchandise / Other',
    ];
@endphp

<div class="page-heading">
    <div>
        <div class="eyebrow">YOUR STORE</div>
        <h1>Store settings</h1>
        <p class="subtitle">Manage your business profile, owner information, verification documents, and courier pickup address.</p>
    </div>
    @if($shop)
        <span class="badge {{ $shop->status === 'approved' ? 'badge-success' : 'badge-warning' }}">
            {{ ucfirst($shop->status) }} store
        </span>
    @endif
</div>

<!-- TOP TWO COLUMNS: Store Profile & Owner Details -->
<div class="two-column">

    <!-- 1. Store Profile & Line of Business -->
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Store profile</h2>
                <small>Business branding and customer-facing store information</small>
            </div>
        </div>
        <form class="panel-body form-grid" method="POST" action="{{ route('seller.account.profile') }}" enctype="multipart/form-data">
            @csrf
            
            <label class="full">
                Business name / Store name <span style="color:#ef4444">*</span>
                <input type="text" name="store_name" value="{{ old('store_name', $shop?->name ?? $user->business_name) }}" maxlength="255" required placeholder="e.g. Acme Lifestyle Store">
            </label>

            <label class="full">
                Line of business (Category) <span style="color:#ef4444">*</span>
                <select name="line_of_business" required style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select line of business</option>
                    @foreach($lineOfBusinessList as $cat)
                        <option value="{{ $cat }}" @selected(old('line_of_business', $user->line_of_business) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </label>

            <label class="full">
                Contact No. (Store hotline / Mobile) <span style="color:#ef4444">*</span>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" placeholder="09171234567" oninput="this.value = this.value.replace(/\D/g, '')" required>
            </label>

            <label class="full">
                Store description
                <textarea name="description" rows="3" maxlength="5000" placeholder="Tell buyers what your shop offers, specialty products, or operating hours...">{{ old('description', $shop?->description) }}</textarea>
            </label>

            <div class="full" style="display:flex; justify-content:flex-end;">
                <button type="submit" class="button">Save store profile</button>
            </div>
        </form>
    </section>

    <!-- 2. Account Owner Personal Details -->
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Owner personal details</h2>
                <small>Registered owner identity and contact information</small>
            </div>
        </div>
        <form class="panel-body form-grid" method="POST" action="{{ route('seller.account.profile') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="store_name" value="{{ $shop?->name ?? $user->business_name ?? 'My Store' }}">

            <label>
                First name <span style="color:#ef4444">*</span>
                <input type="text" name="first_name" value="{{ $firstName }}" required maxlength="120" placeholder="First name">
            </label>

            <label>
                Last name <span style="color:#ef4444">*</span>
                <input type="text" name="last_name" value="{{ $lastName }}" required maxlength="120" placeholder="Last name">
            </label>

            <label>
                Middle initial
                <input type="text" name="middle_initial" value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="5" placeholder="e.g. M.">
            </label>

            <label>
                Sex <span style="color:#ef4444">*</span>
                <select name="sex" required style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select sex</option>
                    <option value="Male" @selected(old('sex', $user->sex) === 'Male')>Male</option>
                    <option value="Female" @selected(old('sex', $user->sex) === 'Female')>Female</option>
                    <option value="Prefer not to say" @selected(old('sex', $user->sex) === 'Prefer not to say')>Prefer not to say</option>
                </select>
            </label>

            <label>
                Birthday <span style="color:#ef4444">*</span>
                <input type="date" name="birthday" id="owner_birthday" value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}" max="{{ date('Y-m-d', strtotime('-1 day')) }}" required onchange="calculateSellerAge(this.value)">
            </label>

            <label>
                Age (autogen)
                <input type="text" id="owner_age" value="{{ $user->age ? $user->age . ' years old' : '—' }}" readonly style="background:var(--brand-soft); cursor:not-allowed;">
            </label>

            <label class="full">
                E-mail address
                <input type="email" value="{{ $user->email }}" readonly style="background:var(--brand-soft); cursor:not-allowed;">
                <small style="color:#10b981; font-weight:600; display:inline-flex; align-items:center; gap:4px; margin-top:4px;">
                    ✓ Verified email address
                </small>
            </label>

            <div class="full" style="display:flex; justify-content:flex-end;">
                <button type="submit" class="button">Save personal details</button>
            </div>
        </form>
    </section>

</div>

<!-- 3. Verification & KYC Documents (Upload ID & Business Permit) -->
<section class="panel" style="margin-bottom: 26px;">
    <div class="panel-header">
        <div>
            <h2>Identity &amp; business verification</h2>
            <small>Government ID and business permit verification for store compliance</small>
        </div>
        <div>
            @if($user->id_status === 'verified')
                <span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">✓ Verified Seller</span>
            @elseif($user->id_status === 'pending')
                <span class="badge" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a;">⏳ Pending Admin Approval</span>
            @elseif($user->id_status === 'rejected')
                <span class="badge" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca;">✕ Rejected - Please Re-upload</span>
            @else
                <span class="badge" style="background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb;">Unverified</span>
            @endif
        </div>
    </div>

    <form class="panel-body form-grid" method="POST" action="{{ route('seller.account.profile') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="store_name" value="{{ $shop?->name ?? $user->business_name ?? 'My Store' }}">

        <!-- Information Callout -->
        <div class="full" style="background: #FAF8FC; border: 1.5px solid #E8E2EE; border-left: 4px solid #6F6382; border-radius: 10px; padding: 14px 18px; margin-bottom: 8px;">
            <p style="font-size: 0.86rem; color: #4B453D; line-height: 1.5; margin: 0; font-weight: 500;">
                <strong>Administrator Review Notice:</strong> After submitting your registration and documents, please wait for the administrator's approval, which will be sent to your email.
            </p>
        </div>

        @if($user->id_rejection_reason)
            <div class="full" style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:8px; font-size:0.85rem;">
                <strong>Reason for rejection:</strong> {{ $user->id_rejection_reason }}
            </div>
        @endif

        <div class="full" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            
            <!-- Government ID Upload -->
            <div style="background:var(--surface); border:1px solid var(--line); border-radius:var(--radius-md); padding:18px;">
                <h3 style="font-size:0.95rem; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between;">
                    <span>Upload Government ID</span>
                    @if($user->id_photo)
                        <span style="font-size:0.75rem; color:#059669; font-weight:600;">File uploaded ✓</span>
                    @endif
                </h3>

                <label style="display:block; margin-bottom:12px;">
                    ID Type
                    <select name="id_type" style="width:100%; padding:9px 12px; border:1px solid var(--line); border-radius:var(--radius-sm); margin-top:6px; background:var(--surface);">
                        <option value="">Select ID Type</option>
                        @foreach(['Philippine National ID (PhilSys)', 'Driver\'s License', 'Passport', 'UMID', 'SSS ID', 'Voter\'s ID', 'Postal ID', 'PRC ID', 'TIN ID'] as $idOption)
                            <option value="{{ $idOption }}" @selected(old('id_type', $user->id_type) === $idOption)>{{ $idOption }}</option>
                        @endforeach
                    </select>
                </label>

                <label style="display:block; margin-bottom:12px;">
                    ID Number
                    <input type="text" name="id_number" value="{{ old('id_number', $user->id_number) }}" placeholder="e.g. 1234-5678-9012" style="margin-top:6px;">
                </label>

                <label style="display:block;">
                    Upload ID File (JPG, PNG, or PDF, max 5MB)
                    <input type="file" name="id_photo" accept=".jpg,.jpeg,.png,.pdf" style="margin-top:6px; width:100%;">
                </label>

                @if($user->id_photo)
                    <div style="margin-top:10px; font-size:0.8rem;">
                        <a href="{{ asset('storage/' . $user->id_photo) }}" target="_blank" style="color:#6F6382; text-decoration:underline; font-weight:600;">
                            View current ID file &nearr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Business Permit / DTI Upload -->
            <div style="background:var(--surface); border:1px solid var(--line); border-radius:var(--radius-md); padding:18px;">
                <h3 style="font-size:0.95rem; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between;">
                    <span>Upload Business Permit / DTI</span>
                    @if($user->dti_permit)
                        <span style="font-size:0.75rem; color:#059669; font-weight:600;">File uploaded ✓</span>
                    @endif
                </h3>

                <p style="font-size:0.8rem; color:var(--muted); margin-bottom:16px;">
                    Upload your valid DTI Registration Certificate, Mayor's Permit, or BIR Certificate of Registration (COR).
                </p>

                <label style="display:block;">
                    Upload Business Permit File (JPG, PNG, or PDF, max 5MB)
                    <input type="file" name="dti_permit" accept=".jpg,.jpeg,.png,.pdf" style="margin-top:6px; width:100%;">
                </label>

                @if($user->dti_permit)
                    <div style="margin-top:16px; font-size:0.8rem;">
                        <a href="{{ asset('storage/' . $user->dti_permit) }}" target="_blank" style="color:#6F6382; text-decoration:underline; font-weight:600;">
                            View current permit file &nearr;
                        </a>
                    </div>
                @endif
            </div>

        </div>

        <div class="full" style="display:flex; justify-content:flex-end; margin-top:8px;">
            <button type="submit" class="button">Submit verification documents</button>
        </div>
    </form>
</section>

<!-- 4. Pickup Address with PSGC Cascading API Dropdowns & Manual Entry -->
<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Pickup address</h2>
            <small>The exact warehouse or store location where our courier partners collect your parcels</small>
        </div>
    </div>

    @if($shop)
        <form class="panel-body form-grid psgc-address-form" method="POST" action="{{ route('seller.account.address') }}" data-form-key="seller-pickup">
            @csrf

            <!-- Contact Person & Phone -->
            <label>
                Contact person / Recipient <span style="color:#ef4444">*</span>
                <input type="text" name="recipient" value="{{ $valRecipient }}" required maxlength="255" placeholder="Full name of warehouse contact">
            </label>

            <label>
                Phone number <span style="color:#ef4444">*</span>
                <input type="tel" name="phone" value="{{ $valPhone }}" required maxlength="20" placeholder="e.g. 09171234567" oninput="this.value = this.value.replace(/\D/g, '')">
            </label>

            <!-- Cascading Philippine Address (PSGC API) -->
            <label>
                Region (API)
                <select name="region" class="psgc-region" data-initial="{{ $valRegion }}" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select Region</option>
                    @foreach($regionList as $code => $name)
                        <option value="{{ $code }}" @selected($valRegion === $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Province (Dropdown) <span style="color:#ef4444">*</span>
                <select name="province" class="psgc-province" required data-initial="{{ $valProvince }}" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select Province</option>
                    @if($valProvince)
                        <option value="{{ $valProvince }}" selected>{{ $valProvince }}</option>
                    @endif
                </select>
            </label>

            <label>
                City / Municipality (Dropdown) <span style="color:#ef4444">*</span>
                <select name="city" class="psgc-city" required data-initial="{{ $valCity }}" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select City / Municipality</option>
                    @if($valCity)
                        <option value="{{ $valCity }}" selected>{{ $valCity }}</option>
                    @endif
                </select>
            </label>

            <label>
                Barangay (Dropdown) <span style="color:#ef4444">*</span>
                <select name="barangay" class="psgc-barangay" required data-initial="{{ $valBarangay }}" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                    <option value="">Select Barangay</option>
                    @if($valBarangay)
                        <option value="{{ $valBarangay }}" selected>{{ $valBarangay }}</option>
                    @endif
                </select>
            </label>

            <!-- Manual Entry: Street & Postal Code -->
            <label class="full">
                Street, Building, Unit / House number (Manual entry) <span style="color:#ef4444">*</span>
                <input type="text" name="line1" value="{{ $valLine1 }}" required maxlength="500" placeholder="e.g. Unit 204, San Lorenzo Bldg, 124 Rizal Avenue">
            </label>

            <label>
                Postal code <span style="color:#ef4444">*</span>
                <input type="text" name="postal_code" value="{{ $valPostal }}" required maxlength="15" placeholder="e.g. 1000" oninput="this.value = this.value.replace(/\D/g, '')">
            </label>

            <div class="full" style="display:flex; justify-content:flex-end;">
                <button type="submit" class="button">Save pickup address</button>
            </div>
        </form>
    @else
        <div class="panel-body muted">
            Save your store profile first to add a pickup address.
        </div>
    @endif
</section>

<script>
function calculateSellerAge(dateStr) {
    if (!dateStr) {
        document.getElementById('owner_age').value = '—';
        return;
    }
    const today = new Date();
    const bday = new Date(dateStr);
    let age = today.getFullYear() - bday.getFullYear();
    const m = today.getMonth() - bday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
    document.getElementById('owner_age').value = (age >= 0 ? age + ' years old' : '—');
}
</script>

@push('scripts')
<script src="{{ asset('js/buyer/address-modal.js') }}"></script>
@endpush

@endsection
