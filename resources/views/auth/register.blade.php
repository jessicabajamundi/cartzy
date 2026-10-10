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
                            value="{{ old('first_name') }}"
                            placeholder="First name"
                            class="auth-input"
                            required
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
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="field-label" for="phone">Mobile number</label>
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
                        placeholder="09171234567"
                        class="auth-input"
                        inputmode="numeric"
                        maxlength="11"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    >
                </div>
            </div>

            <!-- Birthday Field (Shown for Buyer & Seller) -->
            <div id="birthdayFieldWrapper" style="display: {{ old('role', $selectedRole ?? 'buyer') === 'logistics' ? 'none' : 'block' }}; margin-bottom: 26px;">
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
                        value="{{ old('birthday') }}"
                        class="auth-input"
                        max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                        onchange="autoCalcAge(this.value)"
                    >
                </div>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; margin-bottom: 0;">Your age is calculated automatically from your birthday.</p>
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

            <button type="button" class="btn-primary btn-single" style="margin-top: 26px;" onclick="nextStep(2)">Continue</button>
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
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="auth-input"
                        required
                    >
                </div>
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
                        placeholder="Create a password"
                        class="auth-input"
                        oncopy="return false;"
                        oncut="return false;"
                        onpaste="return false;"
                        ondrop="return false;"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="eye-btn text-toggle-btn" onclick="togglePasswordVisibility('register_password', this)" tabindex="-1">
                        <span class="eye-text">Show</span>
                    </button>
                </div>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; margin-bottom: 14px;">Use at least 8 characters, one uppercase letter, one lowercase letter, and one symbol.</p>
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
                        placeholder="Repeat your password"
                        class="auth-input"
                        oncopy="return false;"
                        oncut="return false;"
                        onpaste="return false;"
                        ondrop="return false;"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="eye-btn text-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" tabindex="-1">
                        <span class="eye-text">Show</span>
                    </button>
                </div>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; line-height: 1.45;">Please type both passwords manually. Copying, cutting, pasting and dropping text are disabled in these fields.</p>
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 6px; line-height: 1.45;">We'll send a six-digit verification code to this email address. You'll need the code to finish creating your CARTZY account.</p>
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

<style>
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
        font-family: inherit;
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
        box-shadow: 0 4px 14px rgba(111, 99, 130, 0.35);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
    }
    .btn-action-continue:hover {
        background: linear-gradient(135deg, #9E94AB 0%, #7B6E90 50%, #625575 100%);
        box-shadow: 0 6px 20px rgba(111, 99, 130, 0.45);
        transform: translateY(-1px);
    }
    .btn-action-continue:active {
        transform: translateY(0) scale(0.99);
        box-shadow: 0 2px 8px rgba(111, 99, 130, 0.25);
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

    .eye-btn.text-toggle-btn {
        background: none;
        border: none;
        padding: 4px 10px;
        color: #6b7280;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: color 0.15s ease;
    }
    .eye-btn.text-toggle-btn:hover {
        color: #6F6382;
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
    window.selectRole = function(role) {
        var roleInput = document.getElementById('role_input');
        var buyerOpt = document.getElementById('roleOptionBuyer');
        var sellerOpt = document.getElementById('roleOptionSeller');
        var standardRoleWrapper = document.getElementById('standardRoleWrapper');
        var birthdayFieldWrapper = document.getElementById('birthdayFieldWrapper');
        var logisticsFieldsWrapper = document.getElementById('logisticsFieldsWrapper');
        var logisticsNoticeBox = document.getElementById('logisticsNoticeBox');
        var googleSignupWrapper = document.getElementById('googleSignupWrapper');
        var googleSignupDivider = document.getElementById('googleSignupDivider');
        var authTitle = document.getElementById('authTitle');
        var authSubtitle = document.getElementById('authSubtitle');
        var footNoticeText = document.getElementById('footNoticeText');
        var linkLogisticsApply = document.getElementById('linkLogisticsApply');

        if (roleInput) {
            roleInput.value = role;
        }

        if (role === 'logistics') {
            if (standardRoleWrapper) standardRoleWrapper.style.display = 'none';
            if (birthdayFieldWrapper) birthdayFieldWrapper.style.display = 'none';
            if (logisticsFieldsWrapper) logisticsFieldsWrapper.style.display = 'block';
            if (logisticsNoticeBox) logisticsNoticeBox.style.display = 'block';
            if (googleSignupWrapper) googleSignupWrapper.style.display = 'none';
            if (googleSignupDivider) googleSignupDivider.style.display = 'none';
            if (authTitle) authTitle.innerText = 'Become a Logistics Partner';
            if (authSubtitle) authSubtitle.innerText = 'Register your delivery company and manage your own riders on CARTZY.';
            if (footNoticeText) footNoticeText.innerText = 'Want to register as buyer or seller?';
            if (linkLogisticsApply) {
                linkLogisticsApply.innerText = 'Switch here';
                linkLogisticsApply.setAttribute('onclick', "selectRole('buyer'); return false;");
            }
        } else {
            if (standardRoleWrapper) standardRoleWrapper.style.display = 'block';
            if (birthdayFieldWrapper) birthdayFieldWrapper.style.display = 'block';
            if (logisticsFieldsWrapper) logisticsFieldsWrapper.style.display = 'none';
            if (logisticsNoticeBox) logisticsNoticeBox.style.display = 'none';
            if (googleSignupWrapper) googleSignupWrapper.style.display = 'block';
            if (googleSignupDivider) googleSignupDivider.style.display = 'flex';
            if (authTitle) authTitle.innerText = 'Create your account';
            if (authSubtitle) authSubtitle.innerText = 'Join cartzy and discover your everyday favorites';
            if (footNoticeText) footNoticeText.innerText = 'Do you want to be a logistics in cartzy?';
            if (linkLogisticsApply) {
                linkLogisticsApply.innerText = 'Apply here';
                linkLogisticsApply.setAttribute('onclick', "selectRole('logistics'); return false;");
            }

            if (role === 'seller') {
                if (buyerOpt) buyerOpt.classList.remove('selected');
                if (sellerOpt) sellerOpt.classList.add('selected');
            } else {
                if (sellerOpt) sellerOpt.classList.remove('selected');
                if (buyerOpt) buyerOpt.classList.add('selected');
            }
        }
    };

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
