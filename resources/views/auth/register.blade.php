@extends('layouts.auth')

@section('title', 'Sign Up | cartzy')
@section('auth_visual', 'products')

@section('auth_form')

    <h2 class="auth-title" id="authTitle">{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'Become a Logistics Partner' : 'Create your account' }}</h2>
    <p class="auth-subtitle" id="authSubtitle">{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'Register your delivery company and manage your own riders on CARTZY.' : 'Join cartzy and discover your everyday favorites' }}</p>

    <!-- Logistics Partner Requirement Notice (shown only for logistics) -->
    <div id="logisticsNoticeBox" class="logistics-notice-box" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'block' : 'none' }};">
        Logistics partner accounts require email verification and administrator approval. Once approved, riders can apply to your company.
    </div>

    <div id="googleSignupWrapper" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'none' : 'block' }}; margin-bottom: 20px;">
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

    <div class="divider" id="googleSignupDivider" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'none' : 'flex' }}; margin: 0 0 24px 0;">or register with email</div>

    <!-- Cartzy Original Stepper Component -->
    <div class="stepper">
        <div class="step">
            <span class="step-label" id="label-step-1">Details</span>
        </div>
        <div class="step-line inactive" id="line-1"></div>
        <div class="step">
            <span class="step-label inactive" id="label-step-2">Sign-in</span>
        </div>
        <div class="step-line inactive" id="line-2"></div>
        <div class="step">
            <span class="step-label inactive" id="label-step-3">Verify</span>
        </div>
    </div>

    @if (request('from') === 'cart' || session('info'))
        <div class="alert alert-info" style="margin-bottom: 20px; padding: 12px 16px; background: #FAF5FF; border: 1.5px solid #C08B7F; color: #564B68; border-radius: 10px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <svg style="width: 20px; height: 20px; flex-shrink: 0; color: #C08B7F;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span>{{ session('info') ?? 'Please sign up or create an account to add items to your cart!' }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px 16px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #B91C1C; border-radius: 10px; font-size: 13px;">
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

        <input type="hidden" name="name" id="name" value="{{ old('name') }}">
        <input type="hidden" name="role" id="role_input" value="{{ old('role', $selectedRole ?? 'buyer') }}">
        <input type="hidden" name="age" id="age_display" value="{{ old('age') }}">

        <!-- STEP 1: Details (Your personal details) -->
        <div id="step-1">
            <!-- Role Selection: Buyer or Seller -->
            <div class="role-selection-wrapper" id="standardRoleWrapper" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'none' : 'block' }};">
                <label class="field-label" style="margin-bottom: 8px;">I want to register as <span style="color:#ef4444">*</span></label>
                <div class="role-grid">
                    <div class="role-option {{ old('role', $selectedRole ?? 'buyer') === 'buyer' ? 'selected' : '' }}" id="roleOptionBuyer" onclick="selectRole('buyer')">
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
                    </div>

                    <div class="role-option {{ old('role', $selectedRole ?? 'buyer') === 'seller' ? 'selected' : '' }}" id="roleOptionSeller" onclick="selectRole('seller')">
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
                    </div>
                </div>
            </div>

            <div class="field-block" style="margin-top: 18px;">
                <label class="field-heading">Your personal details</label>
                <p class="field-subheading" style="margin-bottom: 0;">Enter your name and personal details</p>
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
                            aria-describedby="first_name_error"
                            aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}"
                            value="{{ old('first_name') }}"
                            placeholder="First name"
                            class="auth-input"
                            required
                        >
                    </div>
                    <p id="first_name_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('first_name')) hidden @endif>{{ $errors->first('first_name') }}</p>
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
                            aria-describedby="last_name_error"
                            aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}"
                            value="{{ old('last_name') }}"
                            placeholder="Last name"
                            class="auth-input"
                            style="padding-left: 16px !important;"
                            required
                        >
                    </div>
                    <p id="last_name_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('last_name')) hidden @endif>{{ $errors->first('last_name') }}</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="field-label" for="phone">Mobile number</label>
                <input type="hidden" name="phone_country_code" value="+63">
                <div class="input-group registration-phone-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </span>
                    <span class="registration-phone-prefix" aria-hidden="true">+63</span>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        aria-describedby="phone_hint phone_error"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        value="{{ preg_replace('/^(?:\+63|0)(?=9)/', '', old('phone', '')) }}"
                        placeholder="9171234567"
                        class="auth-input"
                        inputmode="numeric"
                        pattern="9[0-9]{9}"
                        maxlength="10"
                        autocomplete="tel-national"
                        title="Enter 10 digits starting with 9 after +63."
                    >
                </div>
                <p id="phone_hint" style="font-size:12px; line-height:1.5; color:#4b5563; margin:6px 0 20px;">+63 Philippines · Enter 10 digits starting with 9.</p>
                <p id="phone_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('phone')) hidden @endif>{{ $errors->first('phone') }}</p>
            </div>

            <!-- Birthday applies to individual account registrations. -->
            <div id="birthdayFieldWrapper" style="{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'display: none; ' : '' }}margin-bottom: 26px;">
                <label class="field-label" for="birthday">Birthday</label>
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
                        @disabled(old('role', $selectedRole ?? 'buyer') === 'logistics')
                        value="{{ old('birthday') }}"
                        class="auth-input"
                        max="{{ now()->subYearsNoOverflow(18)->toDateString() }}"
                        required
                        aria-describedby="birthday_error"
                        aria-invalid="{{ $errors->has('birthday') ? 'true' : 'false' }}"
                        onchange="autoCalcAge(this.value); validateRegistrationBirthday()"
                    >
                </div>
                <p id="birthday_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('birthday')) hidden @endif>{{ $errors->first('birthday') }}</p>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; margin-bottom: 0;">You must be at least 18 years old to register.</p>
            </div>

            <!-- Logistics Partner Company & Service Area Fields (Shown for Logistics) -->
            <div id="logisticsFieldsWrapper" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'block' : 'none' }}; margin-bottom: 26px;">
                <div class="mb-4">
                    <label class="field-label" for="business_name">Company name <span style="color:#ef4444">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="business_name"
                            id="business_name"
                            value="{{ old('business_name') }}"
                            placeholder="Company name"
                            class="auth-input"
                        >
                    </div>
                </div>

                <div class="mb-2">
                    <label class="field-label" for="service_area">Service area <span style="font-weight:400; color:#6b7280;">(optional)</span></label>
                    <div class="input-group" style="margin-bottom: 6px !important;">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="service_area"
                            id="service_area"
                            value="{{ old('service_area', old('address')) }}"
                            placeholder="e.g. Metro Manila, Cavite"
                            class="auth-input"
                        >
                    </div>
                    <p style="font-size: 0.78rem; color: #6b7280; margin-top: 4px; margin-bottom: 0;">Riders see this when choosing a partner to apply to.</p>
                </div>
            </div>

            <button type="button" class="btn-primary btn-single btn-details-continue" style="margin-top: 26px;" onclick="nextStep(2)">Continue</button>
        </div>

        <!-- STEP 2: Sign-in (Your sign-in details) -->
        <div id="step-2" style="display:none;">
            <div class="field-block">
                <label class="field-heading">Your sign-in details</label>
                <p class="field-subheading" style="margin-bottom: 0;">Set up your login credentials</p>
            </div>

            <div class="mb-4">
                <label class="field-label" for="email">Email address <span style="color:#ef4444">*</span></label>
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
                        aria-describedby="email_error"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="auth-input"
                        required
                    >
                </div>
                <p id="email_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
            </div>

            <div class="mb-4">
                <label class="field-label" for="register_password">Password <span style="color:#ef4444">*</span></label>
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
                        aria-describedby="register_password_error password-entry-note"
                        placeholder="Create a password"
                        class="auth-input"
                        oncopy="return false;"
                        oncut="return false;"
                        onpaste="return false;"
                        ondrop="return false;"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="eye-btn password-toggle-btn" onclick="togglePasswordVisibility('register_password', this)" aria-label="Show password" aria-controls="register_password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path class="eye-slash" d="M3 3l18 18" style="display:none"/>
                        </svg>
                    </button>
                </div>
                <p id="register_password_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" @if (!$errors->has('password')) hidden @endif>{{ $errors->first('password') }}</p>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; margin-bottom: 14px;">Use at least 8 characters, one uppercase letter, one lowercase letter, one number, and one symbol.</p>
            </div>

            <div class="mb-6">
                <label class="field-label" for="password_confirmation">Confirm password <span style="color:#ef4444">*</span></label>
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
                        aria-describedby="password_confirmation_error password-entry-note"
                        placeholder="Repeat your password"
                        class="auth-input"
                        oncopy="return false;"
                        oncut="return false;"
                        onpaste="return false;"
                        ondrop="return false;"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="eye-btn password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Show password" aria-controls="password_confirmation" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path class="eye-slash" d="M3 3l18 18" style="display:none"/>
                        </svg>
                    </button>
                </div>
                <p id="password_confirmation_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:6px;" hidden></p>
                <p id="password-entry-note" style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; line-height: 1.45;">Please type both passwords manually. Copying, cutting, pasting and dropping text are disabled in these fields.</p>

                <!-- Terms & Conditions and Privacy Policy agreement row -->
                <div class="terms-agreement-row">
                    <label class="terms-checkbox-label" for="terms_agree">
                        <input type="checkbox" id="terms_agree" name="terms_agree" value="1" required class="terms-checkbox">
                        <span class="terms-checkbox-text">
                            I agree to the <a href="javascript:void(0)" class="terms-policy-link" onclick="openLegalModal('terms'); event.stopPropagation();">Cartzy Terms &amp; Conditions and Privacy Policy</a>. <span style="color:#ef4444">*</span>
                        </span>
                    </label>
                    <p id="terms_agree_error" role="alert" style="color:#ef4444; font-size:13px; margin-top:5px;" hidden></p>
                </div>

                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; line-height: 1.45;">We'll send a six-digit verification code to this email address to finish creating your Cartzy account.</p>
            </div>

            <div class="form-btn-row">
                <button type="button" class="btn-action-back" onclick="prevStep(1)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn-action-continue" onclick="nextStep(3)">
                    Send verification code
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- STEP 3: Verify (Email verification code) -->
        <div id="step-3" style="display:none;">
            <!-- Instant Sent Banner -->
            <div id="otpSentNotice" class="otp-sent-banner">
                <span class="banner-icon">✓</span>
                <span class="banner-text">Your verification code has been sent.</span>
            </div>

            <div class="security-section">
                <label class="field-heading">Email verification code</label>
                <p class="field-subheading" style="margin-bottom: 8px;">Enter the 6-digit code sent to your email.</p>

                <div class="otp-boxes-wrapper">
                    <input type="text" maxlength="1" class="otp-input" data-index="0" placeholder="0" inputmode="numeric" autocomplete="one-time-code">
                    <input type="text" maxlength="1" class="otp-input" data-index="1" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="2" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="3" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="4" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="5" placeholder="0" inputmode="numeric">
                </div>
                <input type="hidden" name="email_verification_otp" id="email_verification_otp" value="">
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 8px;">Your code expires after 10 minutes. You can paste all six digits here.</p>
            </div>

            <div id="otpFeedbackMsg" style="display:none; margin-top: 10px; font-size: 0.82rem; font-weight: 600;"></div>

            <button type="submit" class="btn-primary btn-single" id="btnVerifyEmailSubmit" style="margin-top: 22px;">VERIFY EMAIL &amp; CONTINUE</button>

            <div class="otp-resend-row" style="margin-top: 18px; flex-direction: column; align-items: flex-start; gap: 8px;">
                <span style="font-size: 0.8rem; color: #6b7280; line-height: 1.45;">Didn't receive a code? Check your spam folder or request another. Please wait one minute between requests.</span>
                <button type="button" id="resendOtpBtn" class="resend-otp-btn" onclick="triggerResendOtp()" style="font-weight: 700; color: #6F6382; text-decoration: underline;">Resend code (<span id="otpTimerDisplay">00:60</span>)</button>
            </div>

            <div style="margin-top: 18px; text-align: center;">
                <button type="button" onclick="prevStep(2)" style="background: none; border: none; font-size: 0.82rem; font-weight: 600; color: #6F6382; cursor: pointer; text-decoration: underline;">← Change email or password</button>
            </div>
        </div>

    </form>

    <div class="auth-foot">
        Already have an account?
        <a href="{{ route('login') }}">Log in</a>
    </div>

    <div class="auth-foot" style="margin-top: 10px; padding-top: 12px; border-top: 1px dashed #e5e7eb; font-size: 0.88rem; color: #4b5563;">
        <span id="footNoticeText">{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'Want to register as buyer or seller?' : 'Do you want to be a logistics in cartzy?' }}</span>
        <a href="{{ route('register') }}" id="linkLogisticsApply" onclick="selectRole('{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'buyer' : 'logistics' }}'); return false;" style="font-weight: 700; color: #6F6382; margin-left: 4px; text-decoration: underline;">{{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'Switch here' : 'Apply here' }}</a>
    </div>

    <!-- Legal Agreements Modal (Terms & Conditions and Privacy Policy) -->
    <div id="legalModalBackdrop" class="legal-modal-backdrop" style="display: none;" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="legalModalTitle">
        <div class="legal-modal-container">
            <!-- Modal Header -->
            <div class="legal-modal-header">
                <div class="legal-modal-title-wrap">
                    <div class="legal-modal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h3 id="legalModalTitle" class="legal-modal-title">Cartzy Legal Agreements</h3>
                        <p class="legal-modal-subtitle">Terms &amp; Conditions and Privacy Policy</p>
                    </div>
                </div>
                <button type="button" class="legal-modal-close" onclick="closeLegalModal()" aria-label="Close modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Navigation Tabs inside Modal -->
            <div class="legal-modal-tabs">
                <button type="button" class="legal-tab-btn active" id="modalTabTerms" onclick="switchModalTab('terms')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;display:inline-block;vertical-align:middle;margin-right:4px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Terms &amp; Conditions
                </button>
                <button type="button" class="legal-tab-btn" id="modalTabPrivacy" onclick="switchModalTab('privacy')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;display:inline-block;vertical-align:middle;margin-right:4px;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Privacy Policy
                </button>
            </div>

            <!-- Scrollable Modal Body -->
            <div class="legal-modal-body" id="legalModalBody">
                <!-- Terms & Conditions Section -->
                <div id="modalTermsSection" class="legal-content-section active">
                    <h4 class="terms-section-title">Terms &amp; Conditions</h4>
                    <p class="terms-section-intro">The terms and rules that every Cartzy user agrees to follow when creating an account and using the platform. It explains the responsibilities of buyers and sellers, orders, payments, products, deliveries, returns, and proper use of Cartzy.</p>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Account Registration</h5>
                        <p class="terms-item-body">Users must provide accurate information when creating an account and are responsible for keeping their account credentials secure.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Buyer Responsibilities</h5>
                        <p class="terms-item-body">Buyers are responsible for reviewing product details, prices, quantities, shipping information, and order details before placing an order.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Seller Responsibilities</h5>
                        <p class="terms-item-body">Sellers must provide accurate product information, prices, images, availability, and descriptions. Sellers must only list products that are allowed on Cartzy.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Products and Listings</h5>
                        <p class="terms-item-body">Cartzy may remove or restrict listings that contain prohibited, misleading, fraudulent, or inappropriate products or information.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Orders and Payments</h5>
                        <p class="terms-item-body">Users are responsible for completing their transactions through the payment methods provided by Cartzy. Orders may be subject to cancellation according to Cartzy's applicable policies.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Shipping and Delivery</h5>
                        <p class="terms-item-body">Delivery times may vary depending on the seller, courier, location, and other circumstances. Cartzy is not responsible for delays caused by circumstances beyond its reasonable control.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Returns, Refunds, and Cancellations</h5>
                        <p class="terms-item-body">Buyers may request returns, refunds, or cancellations according to the applicable Cartzy policies and the conditions of the order.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Reviews and User Content</h5>
                        <p class="terms-item-body">Users must provide honest and appropriate reviews or other content. Fake, abusive, misleading, or offensive content may be removed.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Prohibited Activities</h5>
                        <p class="terms-item-body">Users must not use Cartzy for fraud, scams, unauthorized access, harassment, or other illegal or abusive activities.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Account Suspension or Termination</h5>
                        <p class="terms-item-body">Cartzy may suspend or terminate accounts that violate these Terms &amp; Conditions or misuse the platform.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Changes to Cartzy</h5>
                        <p class="terms-item-body">Cartzy may update its services, features, and these Terms &amp; Conditions when necessary.</p>
                    </div>
                </div>

                <!-- Privacy Policy Section -->
                <div id="modalPrivacySection" class="legal-content-section" style="display: none;">
                    <h4 class="terms-section-title">Privacy Policy</h4>
                    <p class="terms-section-intro">The policy that explains how Cartzy collects, uses, stores, shares, and protects users' personal information when they use the platform.</p>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Information We Collect</h5>
                        <p class="terms-item-body">Cartzy may collect information such as your name, email address, contact number, account details, delivery address, order details, payment-related information, and information you provide when using the platform.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">How We Use Your Information</h5>
                        <p class="terms-item-body" style="margin-bottom: 4px;">We may use your information to:</p>
                        <ul class="terms-bullet-list">
                            <li>Create and manage your account</li>
                            <li>Process orders and payments</li>
                            <li>Facilitate delivery</li>
                            <li>Connect buyers and sellers when necessary</li>
                            <li>Provide customer support</li>
                            <li>Send important updates about orders and accounts</li>
                            <li>Improve and secure Cartzy</li>
                        </ul>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Sharing of Information</h5>
                        <p class="terms-item-body">Cartzy may share necessary information with sellers, delivery providers, payment providers, and other authorized service providers when needed to complete transactions or operate the platform.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Payment Information</h5>
                        <p class="terms-item-body">Payment information may be processed through authorized payment providers. Cartzy should only retain payment information when necessary and permitted by applicable law.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Data Security</h5>
                        <p class="terms-item-body">Cartzy will take reasonable measures to protect users' personal information from unauthorized access, disclosure, alteration, or loss.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Data Retention</h5>
                        <p class="terms-item-body">Personal information will only be kept for as long as necessary for legitimate purposes, transactions, legal requirements, or other applicable purposes.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Your Privacy Rights</h5>
                        <p class="terms-item-body">Users may have the right to access, correct, object to the processing of, or request deletion/blocking of their personal information, subject to applicable law.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Cookies and Usage Information</h5>
                        <p class="terms-item-body">Cartzy may use cookies or similar technologies, when applicable, to improve functionality, security, and user experience.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Changes to This Privacy Policy</h5>
                        <p class="terms-item-body">Cartzy may update this Privacy Policy when necessary and will communicate significant changes appropriately.</p>
                    </div>

                    <div class="terms-item">
                        <h5 class="terms-item-title">Contact Us</h5>
                        <p class="terms-item-body">For any questions, concerns, or privacy requests regarding the Terms &amp; Conditions or Privacy Policy, users may contact the Cartzy team through the contact information provided in the application</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="legal-modal-footer">
                <button type="button" class="btn-modal-close" onclick="closeLegalModal()">Close</button>
                <button type="button" class="btn-modal-agree" onclick="acceptTermsFromModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    I Agree &amp; Accept
                </button>
            </div>
        </div>
    </div>

<style>
    .registration-phone-group .registration-phone-prefix {
        position: absolute;
        left: 48px;
        top: 50%;
        transform: translateY(-50%);
        color: #374151;
        font-weight: 600;
        pointer-events: none;
    }
    .registration-phone-group input.auth-input {
        padding-left: 94px !important;
    }
    /* Role Selector Cards */
    .role-selection-wrapper {
        margin-bottom: 20px;
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
        background-color: #FAF7F2;
        border: 1px solid #EFE8DE;
        border-radius: 8px;
        color: #37332D;
        font-size: 0.84rem;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .otp-sent-banner .banner-icon {
        font-weight: 800;
        font-size: 0.95rem;
        color: #059669;
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
        font-family: inherit;
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
        border-color: #6F6382;
        background-color: #F8F6FA;
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
    .auth-split-page .btn-primary.btn-details-continue {
        background: linear-gradient(110deg, var(--brand-dark, #564B68) 0%, var(--brand-lavender, #6F6382) 48%, var(--rose-dark, #8C5A50) 100%) !important;
        background-size: 100% 100% !important;
    }
    .auth-split-page .btn-primary.btn-details-continue:hover {
        background: linear-gradient(110deg, var(--brand-dark, #564B68) 0%, var(--rose-dark, #8C5A50) 100%) !important;
    }
    .form-btn-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 28px;
    }
    .btn-action-back {
        width: 38%;
        height: 50px;
        background: linear-gradient(120deg, var(--brand-soft, #FAF9FB), var(--rose-light, #FCEAE6));
        color: var(--brand-dark, #564B68);
        border: 1px solid var(--rose-primary, #D4A69A);
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
        font-family: inherit;
    }
    .btn-action-back:hover {
        background: linear-gradient(120deg, #EEEAF3, var(--rose-muted, #F5CFC7));
        border-color: var(--rose-deep, #C08B7F);
        box-shadow: 0 4px 12px rgba(111, 99, 130, 0.12);
    }
    .btn-action-back svg {
        width: 18px;
        height: 18px;
    }
    .btn-action-continue {
        flex: 1;
        height: 50px;
        background: linear-gradient(110deg, var(--brand-dark, #564B68) 0%, var(--brand-lavender, #6F6382) 48%, var(--rose-dark, #8C5A50) 100%);
        color: #ffffff;
        border: none;
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.2px;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(111, 99, 130, 0.2);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
    }
    .btn-action-continue:hover {
        background: linear-gradient(110deg, var(--brand-dark, #564B68) 0%, var(--rose-dark, #8C5A50) 100%);
        box-shadow: 0 6px 16px rgba(140, 90, 80, 0.25);
        transform: translateY(-1px);
    }
    .btn-action-continue:active {
        transform: translateY(0) scale(0.99);
        box-shadow: 0 2px 8px rgba(111, 99, 130, 0.25);
    }
    .btn-action-continue svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }
    .btn-action-back:focus-visible,
    .btn-action-continue:focus-visible {
        outline: 3px solid var(--brand-lavender, #6F6382);
        outline-offset: 4px;
    }
    @media (prefers-reduced-motion: reduce) {
        .btn-action-back,
        .btn-action-continue {
            transition: none;
            transform: none;
        }
    }
    .btn-single {
        border-radius: 9999px !important;
        height: 50px;
        width: 100%;
        margin-top: 26px !important;
    }
    .logistics-notice-box {
        margin: 16px 0 20px 0;
        padding: 14px 18px;
        background: #FAF8F6;
        border: 1.5px solid #EFEAE6;
        border-left: 4px solid #C08B7F;
        border-radius: 12px;
        color: #4B453D;
        font-size: 0.82rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .eye-btn.password-toggle-btn {
        background: none;
        border: none;
        padding: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: color 0.15s ease;
    }
    .eye-btn.password-toggle-btn:hover {
        color: #6F6382;
    }

    /* Terms & Privacy Policy Checkbox & Link */
    .terms-agreement-row {
        margin: 14px 0 10px 0;
        padding: 4px 0;
    }
    .terms-checkbox-label {
        display: inline-flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.82rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        user-select: none;
        line-height: 1.5;
    }
    .terms-checkbox {
        width: 18px;
        height: 18px;
        accent-color: #6F6382;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .terms-policy-link {
        color: #6F6382;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.15s ease;
        cursor: pointer;
    }
    .terms-policy-link:hover {
        color: #564B68;
        text-decoration: underline;
    }

    /* Legal Modal Backdrop & Container */
    .legal-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(30, 24, 38, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        animation: legalFadeIn 0.2s ease-out;
    }
    @keyframes legalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .legal-modal-container {
        background: #ffffff;
        width: 100%;
        max-width: 620px;
        max-height: 86vh;
        border-radius: 20px;
        box-shadow: 0 24px 48px -12px rgba(45, 30, 60, 0.35);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #EFEAE6;
        animation: legalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes legalSlideUp {
        from { transform: translateY(24px) scale(0.96); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
    .legal-modal-header {
        padding: 18px 24px 14px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #EFEAE6;
        background: #FAF8F6;
    }
    .legal-modal-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .legal-modal-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #EEE7E2;
        color: #564B68;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .legal-modal-icon svg {
        width: 20px;
        height: 20px;
    }
    .legal-modal-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #564B68;
        margin: 0;
        line-height: 1.3;
    }
    .legal-modal-subtitle {
        font-size: 0.76rem;
        color: #6b7280;
        margin: 2px 0 0 0;
    }
    .legal-modal-close {
        background: none;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .legal-modal-close:hover {
        background: #EAE4DF;
        color: #111827;
    }
    .legal-modal-close svg {
        width: 18px;
        height: 18px;
    }
    .legal-modal-tabs {
        display: flex;
        gap: 8px;
        padding: 12px 24px;
        background: #FAF8F6;
        border-bottom: 1px solid #EAE4DF;
    }
    .legal-tab-btn {
        background: #ffffff;
        border: 1px solid #D8CFC8;
        color: #564B68;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 9999px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease;
        font-family: inherit;
    }
    .legal-tab-btn:hover {
        background: #F4EFF7;
        border-color: #6F6382;
    }
    .legal-tab-btn.active {
        background: linear-gradient(110deg, #564B68 0%, #6F6382 100%);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 2px 6px rgba(111, 99, 130, 0.25);
    }
    .legal-modal-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1;
        font-size: 0.8rem;
        line-height: 1.55;
        color: #4B453D;
        background: #ffffff;
    }
    .legal-modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .legal-modal-body::-webkit-scrollbar-track {
        background: #FAF8F6;
    }
    .legal-modal-body::-webkit-scrollbar-thumb {
        background: #D4CBD8;
        border-radius: 4px;
    }
    .legal-modal-body::-webkit-scrollbar-thumb:hover {
        background: #6F6382;
    }
    .terms-section-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #564B68;
        margin: 0 0 6px 0;
        letter-spacing: -0.2px;
    }
    .terms-section-intro {
        font-size: 0.78rem;
        color: #6b7280;
        margin-bottom: 12px;
        line-height: 1.45;
    }
    .terms-item {
        margin-bottom: 12px;
    }
    .terms-item-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #374151;
        margin: 0 0 3px 0;
    }
    .terms-item-body {
        font-size: 0.78rem;
        color: #4b5563;
        line-height: 1.45;
        margin: 0;
    }
    .terms-bullet-list {
        margin: 4px 0 0 18px;
        padding: 0;
        list-style-type: disc;
        color: #4b5563;
        font-size: 0.78rem;
        line-height: 1.45;
    }
    .terms-bullet-list li {
        margin-bottom: 3px;
    }
    .legal-modal-footer {
        padding: 14px 24px;
        background: #FAF8F6;
        border-top: 1px solid #EFEAE6;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }
    .btn-modal-close {
        padding: 8px 18px;
        background: #ffffff;
        border: 1px solid #D8CFC8;
        color: #4b5563;
        border-radius: 12px;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        font-family: inherit;
    }
    .btn-modal-close:hover {
        background: #F5EFEB;
        color: #111827;
    }
    .btn-modal-agree {
        padding: 8px 20px;
        background: linear-gradient(110deg, #564B68 0%, #6F6382 50%, #8C5A50 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 3px 8px rgba(111, 99, 130, 0.25);
        font-family: inherit;
    }
    .btn-modal-agree:hover {
        background: linear-gradient(110deg, #564B68 0%, #8C5A50 100%);
        box-shadow: 0 4px 12px rgba(140, 90, 80, 0.3);
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
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var buyerOpt = document.getElementById('roleOptionBuyer');
        var sellerOpt = document.getElementById('roleOptionSeller');

        if (buyerOpt) {
            buyerOpt.addEventListener('click', function(e) {
                e.preventDefault();
                window.selectRole('buyer');
            });
        }
        if (sellerOpt) {
            sellerOpt.addEventListener('click', function(e) {
                e.preventDefault();
                window.selectRole('seller');
            });
        }

        var curRole = document.getElementById('role_input') ? document.getElementById('role_input').value : 'buyer';
        window.selectRole(curRole || 'buyer');
    });
</script>
<script src="{{ asset('js/auth/register.js') }}?v={{ file_exists(public_path('js/auth/register.js')) ? filemtime(public_path('js/auth/register.js')) : time() }}"></script>
@endpush

@endsection
