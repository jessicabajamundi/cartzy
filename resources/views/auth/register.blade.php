@extends('layouts.auth')

@section('title', 'Sign Up | cartzy')

@section('auth_form')

    <h2 class="auth-title">Create your account</h2>
    <p class="auth-subtitle">Join Cartzy and start shopping the best electronics</p>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('auth.google') }}" class="btn-google">
            <svg viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Sign up with Google</span>
        </a>
    </div>

    <div class="divider" style="margin: 0 0 24px 0;">or register with email</div>

    <div class="stepper">
        <div class="step">
            <span class="step-label" id="label-step-1">Personal info</span>
        </div>
        <div class="step-line" id="line-1"></div>
        <div class="step">
            <span class="step-label inactive" id="label-step-2">Contact info</span>
        </div>
        <div class="step-line inactive" id="line-2"></div>
        <div class="step">
            <span class="step-label inactive" id="label-step-3">Security</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('register.submit') }}" method="POST" id="registerForm" enctype="multipart/form-data"
          data-initial-role="{{ old('role', $selectedRole ?? 'buyer') }}"
          data-request-otp-url="{{ route('register.request_otp') }}"
          data-verify-otp-url="{{ route('register.verify_otp') }}">
        @csrf

        <!-- STEP 1: Personal Info -->
        <div id="step-1">
            <!-- Role Selection: Buyer vs Seller -->
            <div class="role-selection-wrapper">
                <label class="field-label" style="margin-bottom: 8px;">I want to register as <span style="color:#ef4444">*</span></label>
                <div class="role-grid">
                    <label class="role-option {{ old('role', $selectedRole ?? 'buyer') === 'buyer' ? 'selected' : '' }}" id="roleOptionBuyer" onclick="selectRole('buyer')">
                        <input type="radio" name="role" id="role_buyer" value="buyer" {{ old('role', $selectedRole ?? 'buyer') === 'buyer' ? 'checked' : '' }} style="display:none;">
                        <div class="role-option-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 01-8 0"></path>
                            </svg>
                        </div>
                        <div class="role-option-details">
                            <div class="role-option-title">Buyer</div>
                            <div class="role-option-desc">Shop &amp; discover items</div>
                        </div>
                        <div class="role-option-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </label>

                    <label class="role-option {{ old('role', $selectedRole ?? 'buyer') === 'seller' ? 'selected' : '' }}" id="roleOptionSeller" onclick="selectRole('seller')">
                        <input type="radio" name="role" id="role_seller" value="seller" {{ old('role', $selectedRole ?? 'buyer') === 'seller' ? 'checked' : '' }} style="display:none;">
                        <div class="role-option-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                        <div class="role-option-details">
                            <div class="role-option-title">Seller</div>
                            <div class="role-option-desc">Sell &amp; manage store</div>
                        </div>
                        <div class="role-option-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>

            <div class="field-block" style="margin-top: 22px; margin-bottom: 18px; padding-top: 18px; border-top: 1.5px solid #f3f4f6;">
                <label class="field-heading" for="first_name">Personal information</label>
                <p class="field-subheading" style="margin-bottom: 0;">Enter your basic details</p>
            </div>

            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="first_name">First name <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="First name"
                            class="auth-input"
                        >
                    </div>
                </div>
                <div>
                    <label class="field-label" for="last_name">Last name <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon" style="visibility:hidden;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="Last name"
                            class="auth-input"
                            style="padding-left: 16px !important;"
                        >
                    </div>
                </div>
            </div>

            {{-- Middle Initial + Sex --}}
            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="middle_initial">Middle Initial</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7h16M4 12h16M4 17h10"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="middle_initial"
                            id="middle_initial"
                            value="{{ old('middle_initial') }}"
                            placeholder="e.g. B."
                            class="auth-input"
                            maxlength="5"
                        >
                    </div>
                </div>
                <div>
                    <label class="field-label" for="sex">Sex <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                            </svg>
                        </span>
                        <select name="sex" id="sex" class="auth-input select-input">
                            <option value="">Select sex</option>
                            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Prefer not to say" {{ old('sex') == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="field-label" for="email">E-mail <span style="color:#ef4444">*</span></label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M2 7l10 7 10-7"/>
                        </svg>
                    </span>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="Enter your email address"
                        class="auth-input @error('email') error @enderror"
                    >
                </div>
            </div>

            <button type="button" class="btn-primary btn-single" onclick="nextStep(2)">Continue</button>
        </div>

        <input type="hidden" name="name" id="name" value="{{ old('name') }}">

        <!-- STEP 2: Contact Info -->
        <div id="step-2" style="display:none;">
            <div class="field-block">
                <label class="field-heading" for="phone">Contact information</label>
                <p class="field-subheading">Enter your contact and address details</p>
            </div>

            <div class="mb-4">
                <label class="field-label" for="phone">Contact No. <span style="color:#ef4444">*</span></label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </span>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="{{ old('phone') }}"
                        placeholder="09XXXXXXXXX"
                        class="auth-input"
                        inputmode="numeric"
                        maxlength="11"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    >
                </div>
            </div>

            {{-- Birthday + Age --}}
            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="birthday">Birthday <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <input
                            type="date"
                            name="birthday"
                            id="birthday"
                            value="{{ old('birthday') }}"
                            class="auth-input"
                            max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                            onchange="autoCalcAge(this.value)"
                        >
                    </div>
                </div>
                <div>
                    <label class="field-label" for="age_display">Age <span style="font-size:0.78rem;font-weight:500;color:#9ca3af;">(auto)</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="age_display"
                            name="age"
                            value="{{ old('age') }}"
                            placeholder="Auto-calculated"
                            class="auth-input"
                            readonly
                            style="background:#f9fafb !important; cursor:not-allowed;"
                        >
                    </div>
                </div>
            </div>

            {{-- Residential Address Section --}}
            <div class="addr-section-label">Residential Address <span class="addr-required">*</span></div>

            {{-- House/Unit No. & Street --}}
            <div class="mb-4">
                <label class="field-label" for="street_address">House/Unit No. &amp; Street</label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                            <path d="M9 21V12h6v9"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="street_address"
                        id="street_address"
                        value="{{ old('street_address') }}"
                        placeholder="e.g. 123 Rizal Street"
                        class="auth-input"
                    >
                </div>
            </div>

            {{-- Region | Province --}}
            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="region">Region</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                            </svg>
                        </span>
                        <select name="region" id="region" class="auth-input select-input addr-select">
                            <option value="">Select Region</option>
                            <option value="NCR" {{ old('region')=='NCR'?'selected':'' }}>NCR – Metro Manila</option>
                            <option value="CAR" {{ old('region')=='CAR'?'selected':'' }}>CAR – Cordillera Administrative Region</option>
                            <option value="I" {{ old('region')=='I'?'selected':'' }}>Region I – Ilocos Region</option>
                            <option value="II" {{ old('region')=='II'?'selected':'' }}>Region II – Cagayan Valley</option>
                            <option value="III" {{ old('region')=='III'?'selected':'' }}>Region III – Central Luzon</option>
                            <option value="IV-A" {{ old('region')=='IV-A'?'selected':'' }}>Region IV-A – CALABARZON</option>
                            <option value="IV-B" {{ old('region')=='IV-B'?'selected':'' }}>Region IV-B – MIMAROPA</option>
                            <option value="V" {{ old('region')=='V'?'selected':'' }}>Region V – Bicol Region</option>
                            <option value="VI" {{ old('region')=='VI'?'selected':'' }}>Region VI – Western Visayas</option>
                            <option value="VII" {{ old('region')=='VII'?'selected':'' }}>Region VII – Central Visayas</option>
                            <option value="VIII" {{ old('region')=='VIII'?'selected':'' }}>Region VIII – Eastern Visayas</option>
                            <option value="IX" {{ old('region')=='IX'?'selected':'' }}>Region IX – Zamboanga Peninsula</option>
                            <option value="X" {{ old('region')=='X'?'selected':'' }}>Region X – Northern Mindanao</option>
                            <option value="XI" {{ old('region')=='XI'?'selected':'' }}>Region XI – Davao Region</option>
                            <option value="XII" {{ old('region')=='XII'?'selected':'' }}>Region XII – SOCCSKSARGEN</option>
                            <option value="XIII" {{ old('region')=='XIII'?'selected':'' }}>Region XIII – CARAGA</option>
                            <option value="BARMM" {{ old('region')=='BARMM'?'selected':'' }}>BARMM – Bangsamoro</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="field-label" for="province">Province</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01"/>
                            </svg>
                        </span>
                        <select name="province" id="province" class="auth-input select-input addr-select" disabled>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- City/Municipality | Barangay --}}
            <div class="form-grid-2 mb-4">
                <div>
                    <label class="field-label" for="city">City / Municipality</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <path d="M8 21h8M12 17v4"/>
                            </svg>
                        </span>
                        <select name="city" id="city" class="auth-input select-input addr-select" disabled>
                            <option value="">Select City / Municipality</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="field-label" for="barangay">Barangay</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                <path d="M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </span>
                        <select name="barangay" id="barangay" class="auth-input select-input addr-select" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Postal Code --}}
            <div class="mb-6">
                <label class="field-label" for="postal_code">Postal Code</label>
                <div class="input-group" style="max-width: 260px;">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M2 7l10 7 10-7"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="postal_code"
                        id="postal_code"
                        value="{{ old('postal_code') }}"
                        placeholder="e.g. 1100"
                        class="auth-input"
                        maxlength="4"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    >
                </div>
            </div>

            {{-- Seller Verification / KYC Section (Shown when registering as Seller) --}}
            <div id="sellerKycSection" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'seller' ? 'block' : 'none' }}; margin-bottom: 24px; padding: 18px; background: #F8F6FA; border: 1.5px dashed #A8A0B2; border-radius: 12px;">

                {{-- Section Header --}}
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#6F6382; color:#ffffff; font-weight:700; font-size:0.85rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </span>
                    <div>
                        <label class="field-label" style="margin-bottom:0; font-size:0.92rem; color:#111;">Seller Business Information <span style="font-size:0.8rem; font-weight:600; color:#6F6382;">(Required for Merchant Account)</span></label>
                        <p style="font-size:0.78rem; color:#6b7280; margin:0;">Provide your business details and upload a valid government ID for KYC verification</p>
                    </div>
                </div>

                {{-- Business Name --}}
                <div class="mb-4">
                    <label class="field-label" for="business_name">Business Name <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2"/>
                                <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                <line x1="12" y1="12" x2="12" y2="16"/>
                                <line x1="10" y1="14" x2="14" y2="14"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="business_name"
                            id="business_name"
                            value="{{ old('business_name') }}"
                            placeholder="e.g. Maria's Electronics Store"
                            class="auth-input"
                            maxlength="255"
                        >
                    </div>
                </div>

                {{-- Line of Business (Category) --}}
                <div class="mb-4">
                    <label class="field-label" for="line_of_business">Line of Business <span style="color:#ef4444">*</span> <span style="font-size:0.77rem; color:#9ca3af; font-weight:500;">(Category)</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16M4 10h16M4 14h10"/>
                            </svg>
                        </span>
                        <select name="line_of_business" id="line_of_business" class="auth-input select-input">
                            <option value="">Select your line of business</option>
                            <option value="Electronics & Gadgets" {{ old('line_of_business') == 'Electronics & Gadgets' ? 'selected' : '' }}>Electronics & Gadgets</option>
                            <option value="Fashion & Apparel" {{ old('line_of_business') == 'Fashion & Apparel' ? 'selected' : '' }}>Fashion & Apparel</option>
                            <option value="Health & Beauty" {{ old('line_of_business') == 'Health & Beauty' ? 'selected' : '' }}>Health & Beauty</option>
                            <option value="Home & Living" {{ old('line_of_business') == 'Home & Living' ? 'selected' : '' }}>Home & Living</option>
                            <option value="Sports & Outdoors" {{ old('line_of_business') == 'Sports & Outdoors' ? 'selected' : '' }}>Sports & Outdoors</option>
                            <option value="Food & Beverages" {{ old('line_of_business') == 'Food & Beverages' ? 'selected' : '' }}>Food & Beverages</option>
                            <option value="Toys & Games" {{ old('line_of_business') == 'Toys & Games' ? 'selected' : '' }}>Toys & Games</option>
                            <option value="Books & Stationery" {{ old('line_of_business') == 'Books & Stationery' ? 'selected' : '' }}>Books & Stationery</option>
                            <option value="Automotive Parts & Accessories" {{ old('line_of_business') == 'Automotive Parts & Accessories' ? 'selected' : '' }}>Automotive Parts & Accessories</option>
                            <option value="Pet Supplies" {{ old('line_of_business') == 'Pet Supplies' ? 'selected' : '' }}>Pet Supplies</option>
                            <option value="Baby & Kids" {{ old('line_of_business') == 'Baby & Kids' ? 'selected' : '' }}>Baby & Kids</option>
                            <option value="Arts & Crafts" {{ old('line_of_business') == 'Arts & Crafts' ? 'selected' : '' }}>Arts & Crafts</option>
                            <option value="Agricultural Products" {{ old('line_of_business') == 'Agricultural Products' ? 'selected' : '' }}>Agricultural Products</option>
                            <option value="Other / General Merchandise" {{ old('line_of_business') == 'Other / General Merchandise' ? 'selected' : '' }}>Other / General Merchandise</option>
                        </select>
                    </div>
                </div>

                {{-- Government ID Upload --}}
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; margin-top:16px; padding-top:14px; border-top:1px solid #E5E0EE;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#6F6382; color:#ffffff; font-weight:700; font-size:0.85rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <circle cx="9" cy="10" r="2"></circle>
                            <line x1="15" y1="8" x2="17" y2="8"></line>
                            <line x1="15" y1="12" x2="17" y2="12"></line>
                            <line x1="7" y1="16" x2="17" y2="16"></line>
                        </svg>
                    </span>
                    <div>
                        <label class="field-label" style="margin-bottom:0; font-size:0.92rem; color:#111;">Valid Government ID <span style="font-size:0.8rem; font-weight:600; color:#6F6382;">(KYC Verification)</span></label>
                        <p style="font-size:0.78rem; color:#6b7280; margin:0;">Upload ID for merchant account verification &amp; KYC compliance</p>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom:6px;">
                    <input
                        type="file"
                        name="id_photo"
                        id="id_photo"
                        accept="image/png,image/jpeg,image/jpg,application/pdf"
                        class="auth-input"
                        style="padding-top:10px !important; padding-bottom:10px !important; height:auto;"
                    >
                </div>
                <p style="font-size:0.75rem; color:#6b7280; margin:0;">Accepted formats: JPG, PNG, or PDF (Max 5MB). e.g. Passport, Driver's License, UMID, PhilID, or Voter's ID.</p>
            </div>

            <div class="form-btn-row">
                <button type="button" class="btn-action-back" onclick="prevStep(1)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn-action-continue" onclick="nextStep(3)">Continue</button>
            </div>
        </div>

        <!-- STEP 3: Security & Verification -->
        <div id="step-3" style="display:none;">

            <!-- Phase 1: Verify Email OTP (Always Done First) -->
            <div class="security-section" id="otpVerificationSection">
                <label class="field-heading">Verify Your Email</label>
                <p class="field-subheading">Enter the 6-digit verification code sent to your email to verify your account before creating a password.</p>
                
                <!-- Instant Email Sent Confirmation Banner -->
                <div id="otpSentNotice" class="otp-sent-banner" style="display:none;">
                    <span class="banner-icon">✓</span>
                    <span class="banner-text">We sent a 6-digit verification code to <strong id="noticeEmailTarget">your email</strong>.</span>
                </div>

                <div class="otp-boxes-wrapper">
                    <input type="text" maxlength="1" class="otp-input" data-index="0" placeholder="—" inputmode="numeric" autocomplete="one-time-code">
                    <input type="text" maxlength="1" class="otp-input" data-index="1" placeholder="—" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="2" placeholder="—" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="3" placeholder="—" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="4" placeholder="—" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="5" placeholder="—" inputmode="numeric">
                </div>
                <input type="hidden" name="email_verification_otp" id="email_verification_otp" value="">

                <!-- OTP Verification Feedback Message -->
                <div id="otpFeedbackMsg" style="display:none; margin-top: 10px; font-size: 0.82rem; font-weight: 600;"></div>

                <div class="otp-resend-row" id="otpResendContainer">
                    Didn't receive the code?
                    <button type="button" id="resendOtpBtn" class="resend-otp-btn" onclick="triggerResendOtp()">Resend the OTP (<span id="otpTimerDisplay">00:45</span>)</button>
                </div>

                <!-- Verify OTP Button Row (Visible before email is verified) -->
                <div class="form-btn-row" id="verifyOtpBtnRow" style="margin-top: 22px;">
                    <button type="button" class="btn-action-back" onclick="prevStep(2)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back
                    </button>
                    <button type="button" class="btn-action-continue" id="btnVerifyEmailOtp" onclick="checkAndVerifyOtp()">Verify Code</button>
                </div>
            </div>

            <!-- Phase 2: Password Creation Section (Only Shown After OTP is Verified) -->
            <div id="passwordCreationSection" style="display:none; margin-top: 24px; padding-top: 20px; border-top: 1.5px solid #f3f4f6;">
                
                <!-- Email Verified Success Badge -->
                <div style="display:flex; align-items:center; gap:8px; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; font-size:0.82rem; font-weight:600; padding:10px 14px; border-radius:8px; margin-bottom:18px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;flex-shrink:0;">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>Email verified! You can now set up your account password below.</span>
                </div>

                <!-- Create Password Block -->
                <div class="security-section">
                    <label class="field-heading" for="register_password">Create Password <span style="color:#ef4444">*</span></label>
                    <p class="field-subheading">Create a strong password to protect your account (min. 6 characters)</p>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="11" width="14" height="10" rx="2"/>
                                <path d="M8 11V7a4 4 0 018 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="register_password"
                            placeholder="Enter your password"
                            class="auth-input @error('password') error @enderror"
                        >
                        <button type="button" class="eye-btn" onclick="togglePasswordVisibility('register_password', 'reg_eye_icon')" tabindex="-1">
                            <svg id="reg_eye_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Block -->
                <div class="security-section">
                    <label class="field-heading" for="password_confirmation">Confirm Password <span style="color:#ef4444">*</span></label>
                    <p class="field-subheading">Re-enter your password to confirm</p>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="11" width="14" height="10" rx="2"/>
                                <path d="M8 11V7a4 4 0 018 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Confirm your password"
                            class="auth-input"
                        >
                        <button type="button" class="eye-btn" onclick="togglePasswordVisibility('password_confirmation', 'confirm_eye_icon')" tabindex="-1">
                            <svg id="confirm_eye_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons: Back & Complete Registration -->
                <div class="form-btn-row">
                    <button type="button" class="btn-action-back" onclick="prevStep(2)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back
                    </button>
                    <button type="submit" class="btn-action-continue" id="submitRegBtn">Complete &amp; Start Shopping</button>
                </div>
            </div>

        </div>

    </form>

    <div class="auth-foot">
        Already have an account?
        <a href="{{ route('login') }}">Log in</a>
    </div>

<style>
    /* Security Step & Refined Form Styles */
    .field-block {
        margin-bottom: 20px;
    }
    .field-heading {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: #111111;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
    }
    .field-subheading {
        font-size: 0.82rem;
        color: #6b7280;
        margin-bottom: 12px;
        font-weight: 400;
    }
    .security-section {
        margin-bottom: 20px;
    }
    .security-section .input-group {
        margin-bottom: 0;
    }

    /* Instant OTP Sent Banner */
    .otp-sent-banner {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        color: #065f46;
        font-size: 0.8rem;
        margin-bottom: 14px;
        animation: fadeIn 0.2s ease-in;
    }
    .otp-sent-banner .banner-icon {
        font-weight: 800;
        font-size: 0.95rem;
    }

    /* OTP Inputs */
    .otp-boxes-wrapper {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
        max-width: 440px;
        margin-top: 6px;
    }
    .otp-input {
        width: 100%;
        height: 52px;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111111;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .otp-input::placeholder {
        color: #d1d5db;
        font-weight: 400;
        font-size: 1.1rem;
    }
    .otp-input:focus {
        border-color: #A8A0B2;
        box-shadow: 0 0 0 3px rgba(168, 160, 178, 0.28);
    }
    .otp-input.filled {
        border-color: #A8A0B2;
        background-color: #F1EFF5;
    }

    /* Resend OTP Row */
    .otp-resend-row {
        font-size: 0.82rem;
        color: #4b5563;
        font-weight: 500;
        margin-top: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .resend-otp-btn {
        background: none;
        border: none;
        color: #6F6382;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
        text-decoration: none;
        transition: opacity 0.15s, color 0.15s;
    }
    .resend-otp-btn:hover:not(:disabled) {
        color: #564B68;
        text-decoration: underline;
    }
    .resend-otp-btn:disabled {
        color: #91879E;
        cursor: default;
    }

    /* Button Rows */
    .form-btn-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 28px;
    }
    .btn-action-back {
        width: 38%;
        height: 50px;
        background: #ffffff;
        color: #374151;
        border: 1.5px solid #e5e7eb;
        border-radius: 9999px;
        font-size: 0.95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-action-back:hover {
        background: #f9fafb;
        border-color: #d1d5db;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .btn-action-back svg {
        width: 18px;
        height: 18px;
    }
    .btn-action-continue {
        flex: 1;
        height: 50px;
        background: linear-gradient(135deg, #91879E 0%, #6F6382 50%, #564B68 100%);
        color: #ffffff;
        border: none;
        border-radius: 9999px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.2px;
        box-shadow: 0 4px 14px rgba(138, 104, 96, 0.35);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-action-continue:hover {
        background: linear-gradient(135deg, #B89B92 0%, #96746C 50%, #785851 100%);
        box-shadow: 0 6px 20px rgba(138, 104, 96, 0.45);
        transform: translateY(-1px);
    }
    .btn-action-continue:active {
        transform: translateY(0) scale(0.99);
        box-shadow: 0 2px 8px rgba(138, 104, 96, 0.25);
    }
    .btn-single {
        border-radius: 9999px !important;
        height: 50px;
    }

    @media (max-width: 480px) {
        .otp-boxes-wrapper {
            gap: 8px;
        }
        .otp-input {
            height: 46px;
            font-size: 1.1rem;
        }
    }

    /* Residential Address Label */
    .addr-section-label {
        font-size: 0.82rem;
        font-weight: 800;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 1.5px solid #f3f4f6;
    }
    .addr-required { color: #ef4444; }
    /* Role Selector Cards */
    .role-selection-wrapper {
        margin-bottom: 22px;
    }
    .role-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .role-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 15px;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        user-select: none;
    }
    .role-option:hover {
        border-color: #A8A0B2;
        background: #FAF8FC;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(111, 99, 130, 0.08);
    }
    .role-option.selected {
        border-color: #6F6382;
        background: #F6F4F8;
        box-shadow: 0 0 0 2px rgba(111, 99, 130, 0.2), 0 4px 12px rgba(111, 99, 130, 0.1);
    }
    .role-option-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f3f4f6;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .role-option-icon svg {
        width: 20px;
        height: 20px;
    }
    .role-option.selected .role-option-icon {
        background: #6F6382;
        color: #ffffff;
    }
    .role-option-details {
        flex: 1;
        min-width: 0;
    }
    .role-option-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
        transition: color 0.18s;
    }
    .role-option.selected .role-option-title {
        color: #564B68;
    }
    .role-option-desc {
        font-size: 0.74rem;
        color: #6b7280;
        line-height: 1.25;
    }
    .role-option-badge {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1.5px solid #d1d5db;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: transparent;
        transition: all 0.2s ease;
    }
    .role-option-badge svg {
        width: 12px;
        height: 12px;
    }
    .role-option.selected .role-option-badge {
        background: #6F6382;
        border-color: #6F6382;
        color: #ffffff;
    }

    @media (max-width: 480px) {
        .role-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }

</style>

@push('scripts')
<script src="{{ asset('js/auth/register.js') }}"></script>
@endpush

@endsection


