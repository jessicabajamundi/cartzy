@extends('layouts.auth')

@section('title', 'Become a Rider | cartzy')
@section('auth_visual', 'courier')

@section('auth_form')
<div class="rider-registration-container">
    <!-- Header Badge & Title -->
    <div class="rider-header-block">
        <span class="rider-partner-tag">CARTZY PARTNERS</span>
        <h2 class="auth-title rider-auth-title">Become a rider</h2>
        <p class="rider-auth-subtitle">Apply to deliver for one of CARTZY's logistics partners.</p>
    </div>

    <!-- Logistics Notice Callout -->
    <div class="rider-notice-banner">
        <svg class="rider-notice-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <p class="rider-notice-text">
            The logistics partner you choose reviews your application. You can sign in and accept deliveries once they approve you.
        </p>
    </div>

    <!-- Rider registration progress -->
    <div class="stepper" aria-label="Rider registration steps">
        <div class="step" id="tab-step-1" aria-current="step">
            <span class="step-label" id="label-step-1">Details</span>
        </div>
        <div class="step-line inactive" id="line-1" aria-hidden="true"></div>
        <div class="step" id="tab-step-2">
            <span class="step-label inactive" id="label-step-2">Sign-in</span>
        </div>
        <div class="step-line inactive" id="line-2" aria-hidden="true"></div>
        <div class="step" id="tab-step-3">
            <span class="step-label inactive" id="label-step-3">Verify</span>
        </div>
    </div>

    <!-- Registration Form (Steps 1, 2, 3) -->
    <form id="riderDetailsForm" method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data" novalidate data-request-otp-url="{{ route('register.request_otp', [], false) }}">
        @csrf
        <input type="hidden" name="role" id="role_input" value="courier">
        <input type="hidden" name="phone_country_code" value="+63">
        <input type="hidden" name="name" id="name" value="{{ old('name') }}">

        <!-- STEP 1 CONTAINER -->
        <div id="riderStepDetails">

            <!-- Section 1: Your personal details -->
            <div class="rider-form-section">
                <div class="field-block" style="border-top: 1px solid #E1DDE7; padding-top: 18px; margin-bottom: 20px;">
                    <h3 class="field-heading">Your personal details</h3>
                    <p class="field-subheading">Enter your name and personal details</p>
                </div>

                <div class="form-grid-2">
                    <div class="rider-field-group">
                        <label class="field-label" for="first_name">First name <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   maxlength="50"
                                   class="auth-input rider-input rider-has-icon"
                                   placeholder="First name"
                                   value="{{ old('first_name') }}"
                                   required
                                   autocomplete="given-name"
                                   aria-describedby="error_first_name">
                        </div>
                        <p class="rider-field-error" id="error_first_name"></p>
                    </div>

                    <div class="rider-field-group">
                        <label class="field-label" for="last_name">Last name <span class="required-star">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   maxlength="50"
                                   class="auth-input rider-input"
                                   style="padding-left: 16px !important;"
                                   placeholder="Last name"
                                   value="{{ old('last_name') }}"
                                   required
                                   autocomplete="family-name"
                                   aria-describedby="error_last_name">
                        </div>
                        <p class="rider-field-error" id="error_last_name"></p>
                    </div>
                </div>

                <div class="rider-field-group">
                    <label class="field-label" for="phone">Mobile number <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </span>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               class="auth-input rider-input rider-has-icon"
                               placeholder="09171234567"
                               value="{{ old('phone') }}"
                               inputmode="numeric"
                               pattern="[0-9+]*"
                               maxlength="13"
                               required
                               autocomplete="tel"
                               onkeydown="filterPhoneKey(event)"
                               oninput="sanitizePhoneInput(this)"
                               onpaste="handlePhonePaste(event, this)">
                    </div>
                    <p class="rider-field-hint">09XXXXXXXXX or +639XXXXXXXXX. Each mobile number can only be used for one account.</p>
                    <p class="rider-field-error" id="error_phone"></p>
                </div>

                <div class="rider-field-group">
                    <label class="field-label" for="birthday">Birthday <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <input type="date"
                               id="birthday"
                               name="birthday"
                               class="auth-input rider-input rider-has-icon"
                               value="{{ old('birthday') }}"
                               max="{{ now()->subYears(18)->toDateString() }}"
                               required>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <p class="rider-field-hint" style="margin: 0;">Riders must be at least 18 years old.</p>
                        <span id="rider_birthday_age_text" style="font-size: 0.8rem; font-weight: 600; color: #059669;"></span>
                    </div>
                    <p class="rider-field-error" id="error_birthday"></p>
                </div>
            </div>

            <!-- Section 2: Where you'll deliver -->
            <div class="rider-form-section">
                <div class="rider-section-divider"></div>
                <h3 class="rider-section-heading">Where you'll deliver</h3>

                <div class="rider-field-group">
                    <label class="field-label" for="logistics_provider_id">Logistics partner <span class="required-star">*</span></label>
                    <div class="rider-select-wrapper input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </span>
                        <select id="logistics_provider_id"
                                name="logistics_provider_id"
                                class="auth-input rider-input rider-select rider-has-icon"
                                required>
                            <option value="" disabled {{ old('logistics_provider_id') ? '' : 'selected' }}>Choose who you'll deliver for</option>
                            @php
                                $providers = $logisticsProviders ?? \App\Models\LogisticsProvider::where('status', 'approved')->orderBy('name')->get();
                            @endphp

                            @if($providers && $providers->count() > 0)
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old('logistics_provider_id') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="1" {{ old('logistics_provider_id') == 1 ? 'selected' : '' }}>Express Delivery — Metro Manila</option>
                                <option value="2" {{ old('logistics_provider_id') == 2 ? 'selected' : '' }}>Cartzy Quick Logistics — Luzon Central</option>
                            @endif
                        </select>
                        <svg class="rider-select-chevron" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="rider-field-error" id="error_logistics_provider_id"></p>
                </div>
            </div>

            <!-- Section 3: Your vehicle -->
            <div class="rider-form-section">
                <div class="rider-section-divider"></div>
                <h3 class="rider-section-heading">Your vehicle</h3>

                <div class="form-grid-2">
                    <div class="rider-field-group">
                        <label class="field-label" for="vehicle_type">Vehicle type <span class="required-star">*</span></label>
                        <div class="rider-select-wrapper input-group">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3 3 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                    <circle cx="7" cy="17" r="2"/>
                                    <circle cx="17" cy="17" r="2"/>
                                </svg>
                            </span>
                            <select id="vehicle_type"
                                    name="vehicle_type"
                                    class="auth-input rider-input rider-select rider-has-icon"
                                    required
                                    onchange="handleVehicleTypeChange(this.value)">
                                <option value="" disabled {{ old('vehicle_type') ? '' : 'selected' }}>Select vehicle</option>
                                <option value="motorcycle" {{ old('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                <option value="bicycle" {{ old('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                                <option value="ebike" {{ old('vehicle_type') === 'ebike' ? 'selected' : '' }}>E-bike</option>
                                <option value="tricycle" {{ old('vehicle_type') === 'tricycle' ? 'selected' : '' }}>Tricycle</option>
                                <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Car</option>
                                <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Van</option>
                                <option value="truck" {{ old('vehicle_type') === 'truck' ? 'selected' : '' }}>Truck</option>
                            </select>
                            <svg class="rider-select-chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="rider-field-error" id="error_vehicle_type"></p>
                    </div>

                    <div class="rider-field-group">
                        <label class="field-label" for="plate_no">
                            Plate number
                            <span class="required-star" id="plate_star">*</span>
                            <span class="rider-optional-pill" id="plate_optional" style="display: none;">Optional</span>
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                    <circle cx="6" cy="12" r="1"/>
                                    <circle cx="18" cy="12" r="1"/>
                                    <line x1="10" y1="10" x2="14" y2="10"/>
                                    <line x1="10" y1="14" x2="14" y2="14"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="plate_no"
                                   name="plate_no"
                                   maxlength="7"
                                   class="auth-input rider-input rider-has-icon"
                                   style="text-transform: uppercase;"
                                   placeholder="123ABC or NBC1234"
                                   value="{{ old('plate_no') }}"
                                   autocomplete="off"
                                   autocapitalize="characters"
                                   spellcheck="false"
                                   aria-describedby="error_plate_no">
                        </div>
                        <p class="rider-field-error" id="error_plate_no"></p>
                    </div>
                </div>

                <p class="rider-field-hint" id="vehicle_rule_hint">
                    Bicycle and e-bike riders don't need a plate, driver's license or OR/CR.
                </p>
            </div>

            <!-- Section 4: Driver's license -->
            <div class="rider-form-section" id="license_section">
                <div class="rider-section-divider"></div>
                <h3 class="rider-section-heading">Driver's license</h3>

                <div class="form-grid-2">
                    <div class="rider-field-group">
                        <label class="field-label" for="license_number">
                            License number
                            <span class="required-star" id="license_star">*</span>
                            <span class="rider-optional-pill" id="license_optional" style="display: none;">Optional</span>
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <circle cx="8" cy="10" r="2"/>
                                    <line x1="14" y1="9" x2="18" y2="9"/>
                                    <line x1="14" y1="13" x2="18" y2="13"/>
                                    <line x1="6" y1="16" x2="18" y2="16"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="license_number"
                                   name="license_number"
                                   class="auth-input rider-input rider-has-icon"
                                   placeholder="A00-00-000000"
                                   value="{{ old('license_number') }}"
                                   maxlength="13"
                                   style="text-transform: uppercase;"
                                   autocapitalize="characters"
                                   autocomplete="off">
                        </div>
                        <p class="rider-field-hint" style="margin-top: 4px;">Format: A00-00-000000 (e.g. H83-78-937937)</p>
                        <p class="rider-field-error" id="error_license_number"></p>
                    </div>

                    <div class="rider-field-group">
                        <label class="field-label" for="license_expiry">
                            Expiry date
                            <span class="required-star" id="expiry_star">*</span>
                            <span class="rider-optional-pill" id="expiry_optional" style="display: none;">Optional</span>
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                    <polyline points="12 13 12 16 14 16"/>
                                </svg>
                            </span>
                            <input type="date"
                                   id="license_expiry"
                                   name="license_expiry"
                                   class="auth-input rider-input rider-has-icon"
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('license_expiry') }}">
                        </div>
                        <p class="rider-field-error" id="error_license_expiry"></p>
                    </div>
                </div>
            </div>

            <!-- Section 5: Documents -->
            <div class="rider-form-section" id="documents_section">
                <div class="rider-section-divider"></div>
                <h3 class="rider-section-heading">Documents</h3>
                <p class="rider-field-hint" style="margin-bottom: 8px;">
                    JPG, PNG or PDF, up to 5 MB each. Your logistics partner checks these before approving you.
                </p>

                <!-- Document 1: Driver's license upload -->
                <div class="rider-field-group">
                    <label class="field-label" for="license_doc">
                        Driver's license
                        <span class="required-star" id="license_doc_star">*</span>
                        <span class="rider-optional-pill" id="license_doc_optional" style="display: none;">Optional</span>
                    </label>

                    <div class="rider-file-upload-box" id="license_doc_box" onclick="triggerFileInput('license_doc')">
                        <input type="file"
                               id="license_doc"
                               name="license_doc"
                               class="rider-hidden-file-input"
                               accept=".jpg,.jpeg,.png,.pdf"
                               onchange="handleFileSelected(this, 'license_doc_status', 'license_doc_box')">

                        <div class="rider-file-left">
                            <span class="rider-doc-lead-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <circle cx="8" cy="10" r="2"/>
                                    <line x1="14" y1="9" x2="18" y2="9"/>
                                    <line x1="14" y1="13" x2="18" y2="13"/>
                                </svg>
                            </span>
                            <button type="button" class="rider-choose-file-btn" aria-label="Choose driver's license file">Choose file</button>
                            <span class="rider-file-status" id="license_doc_status">No file chosen</span>
                        </div>

                        <div class="rider-file-right" id="license_doc_action" style="display: none;">
                            <span class="rider-file-pill-ready">Ready</span>
                            <button type="button" class="rider-clear-file-btn" onclick="clearSelectedFile(event, 'license_doc', 'license_doc_status', 'license_doc_box')" title="Remove file">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                    <p class="rider-field-hint" style="margin-top: 4px;">A clear photo of the front of your license.</p>
                    <p class="rider-field-error" id="error_license_doc"></p>
                </div>

                <!-- Document 2: Vehicle OR/CR upload -->
                <div class="rider-field-group">
                    <label class="field-label" for="or_cr_doc">
                        Vehicle OR/CR
                        <span class="required-star" id="or_cr_star">*</span>
                        <span class="rider-optional-pill" id="or_cr_optional" style="display: none;">Optional</span>
                    </label>

                    <div class="rider-file-upload-box" id="or_cr_doc_box" onclick="triggerFileInput('or_cr_doc')">
                        <input type="file"
                               id="or_cr_doc"
                               name="or_cr_doc"
                               class="rider-hidden-file-input"
                               accept=".jpg,.jpeg,.png,.pdf"
                               onchange="handleFileSelected(this, 'or_cr_doc_status', 'or_cr_doc_box')">

                        <div class="rider-file-left">
                            <span class="rider-doc-lead-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <path d="M9 13h6"/>
                                    <path d="M9 17h6"/>
                                </svg>
                            </span>
                            <button type="button" class="rider-choose-file-btn" aria-label="Choose vehicle OR/CR file">Choose file</button>
                            <span class="rider-file-status" id="or_cr_doc_status">No file chosen</span>
                        </div>

                        <div class="rider-file-right" id="or_cr_doc_action" style="display: none;">
                            <span class="rider-file-pill-ready">Ready</span>
                            <button type="button" class="rider-clear-file-btn" onclick="clearSelectedFile(event, 'or_cr_doc', 'or_cr_doc_status', 'or_cr_doc_box')" title="Remove file">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                    <p class="rider-field-hint" style="margin-top: 4px;">The Official Receipt and Certificate of Registration of the vehicle you will use.</p>
                    <p class="rider-field-error" id="error_or_cr_doc"></p>
                </div>
            </div>

            <!-- Submit / Continue Button -->
            <div style="margin-top: 16px;">
                <button type="button" id="btnContinueToSignIn" class="btn-primary rider-btn-continue" onclick="handleContinueToSignIn()">
                    CONTINUE TO SIGN-IN DETAILS
                </button>
            </div>

        </div>

        <!-- STEP 2 CONTAINER: Sign-in -->
        <div id="riderStepSignIn" style="display: none;">
            <div class="field-block" style="border-top: 1px solid #E1DDE7; padding-top: 18px; margin-bottom: 20px;">
                <h3 class="field-heading">Your sign-in details</h3>
                <p class="field-subheading" style="margin-bottom: 0;">Set up your login credentials</p>
            </div>

            <div class="rider-field-group mb-4">
                <label class="field-label" for="email">Email address <span class="required-star">*</span></label>
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
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="auth-input rider-input rider-has-icon"
                        required
                        autocomplete="email"
                    >
                </div>
                <p id="email_error" class="rider-field-error" role="alert"></p>
            </div>

            <div class="rider-field-group mb-4">
                <label class="field-label" for="register_password">Password <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        name="password"
                        id="register_password"
                        aria-describedby="register_password_error password-entry-note"
                        placeholder="Create a password"
                        class="auth-input rider-input rider-has-icon"
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
                <p id="register_password_error" class="rider-field-error" role="alert"></p>
                <p class="rider-field-hint" style="margin-top: 6px; margin-bottom: 14px;">Use at least 8 characters, one uppercase letter, one lowercase letter, one number, and one symbol.</p>
            </div>

            <div class="rider-field-group mb-6">
                <label class="field-label" for="password_confirmation">Confirm password <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        aria-describedby="password_confirmation_error password-entry-note"
                        placeholder="Repeat your password"
                        class="auth-input rider-input rider-has-icon"
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
                <p id="password_confirmation_error" class="rider-field-error" role="alert"></p>
                <p id="password-entry-note" class="rider-field-hint" style="margin-top: 6px; line-height: 1.45;">Please type both passwords manually. Copying, cutting, pasting and dropping text are disabled in these fields.</p>
            </div>

            <p class="field-subheading" style="margin-bottom: 20px;">We'll send a six-digit verification code to this email address to finish creating your Cartzy account.</p>

            <div class="form-btn-row">
                <button type="button" class="btn-action-back" onclick="goToRiderStep(1)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn-action-continue" id="sendVerificationCodeButton" onclick="handleContinueToVerify()">
                    <span id="sendVerificationCodeLabel">Send verification code</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- STEP 3 CONTAINER: Verify -->
        <div id="riderStepVerify" style="display: none;">
            <!-- Instant Sent Banner -->
            <div id="otpSentNotice" class="otp-sent-banner">
                <span class="banner-icon">✓</span>
                <span class="banner-text">Your verification code has been sent.</span>
            </div>

            <div class="security-section">
                <div class="field-block" style="border-top: 1px solid #E1DDE7; padding-top: 18px; margin-bottom: 16px;">
                    <h3 class="field-heading">Email verification code</h3>
                    <p class="field-subheading" id="verificationApplicant" style="margin-bottom: 8px;">Enter the 6-digit code sent to your email.</p>
                </div>

                <div class="otp-boxes-wrapper">
                    <input type="text" maxlength="1" class="otp-input" data-index="0" placeholder="0" inputmode="numeric" autocomplete="one-time-code">
                    <input type="text" maxlength="1" class="otp-input" data-index="1" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="2" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="3" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="4" placeholder="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" data-index="5" placeholder="0" inputmode="numeric">
                </div>
                <input type="hidden" name="email_verification_otp" id="email_verification_otp" value="{{ old('email_verification_otp') }}">
                <p class="rider-field-hint" style="margin-top: 8px;">Your code expires after 10 minutes. You can paste all six digits here.</p>

                <div id="devOtpBanner" style="display:none; margin-top: 14px; padding: 12px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 13px; color: #1e40af;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <span><strong>Verification code:</strong> <span id="devOtpCode" style="font-family: monospace; font-weight: 800; font-size: 16px; letter-spacing: 3px; color: #1d4ed8;"></span></span>
                        <button type="button" id="btnAutofillOtp" style="padding: 4px 10px; font-size: 12px; font-weight: 600; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Auto-fill code</button>
                    </div>
                    <div id="devMailStatusNotice" style="font-size: 11px; margin-top: 4px; color: #64748b;"></div>
                </div>
            </div>

            <div id="otpFeedbackMsg" style="display:none; margin-top: 10px; font-size: 0.82rem; font-weight: 600;"></div>

            <button type="submit" class="btn-primary rider-btn-continue" id="btnVerifyEmailSubmit" style="margin-top: 22px;">
                VERIFY EMAIL &amp; SUBMIT APPLICATION
            </button>

            <div class="otp-resend-row" style="margin-top: 18px; flex-direction: column; align-items: flex-start; gap: 8px;">
                <span style="font-size: 0.8rem; color: #6b7280; line-height: 1.45;">Didn't receive a code? Check your spam folder or request another. Please wait one minute between requests.</span>
                <button type="button" id="resendOtpBtn" class="resend-otp-btn" onclick="triggerResendOtp()" style="font-weight: 700; color: #6F6382; text-decoration: underline;">Resend code (<span id="otpTimerDisplay">00:60</span>)</button>
            </div>

            <div style="margin-top: 18px; text-align: center;">
                <button type="button" onclick="goToRiderStep(2)" style="background: none; border: none; font-size: 0.82rem; font-weight: 600; color: #6F6382; cursor: pointer; text-decoration: underline;">← Change email or password</button>
            </div>
        </div>
    </form>

    <!-- Foot Link -->
    <div class="auth-foot" style="margin-top: 16px;">
        Already applied or registered?
        <a href="{{ route('login') }}">Log in</a>
    </div>
    <div class="auth-foot rider-signup-links" aria-label="Other registration options">
        <a href="{{ route('register', ['role' => 'buyer']) }}">Create new account</a>
        <span aria-hidden="true">|</span>
        <a href="{{ route('register', ['role' => 'seller']) }}">Sell in cartzy</a>
        <span aria-hidden="true">|</span>
        <a href="{{ route('register', ['role' => 'logistics']) }}">Become a logistics partner</a>
    </div>
</div>

<style>
    .auth-foot.rider-signup-links {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        gap: 6px;
        margin-top: 12px;
        padding-top: 14px;
        border-top: 1px dashed #e5e7eb;
        font-size: clamp(0.58rem, 2.2vw, 0.82rem);
        white-space: nowrap;
    }
    .rider-signup-links a { margin-left: 0; }
    .rider-signup-links > span { color: #d1d5db; }

    /* ═══════════════════════════════════════════════════════════════════
       Cartzy Design System: Rider Registration Styles
       Palette: #564B68, #6F6382, #91879E, #A8A0B2, #C08B7F
       Typography: Lato & Cormorant Garamond
       ═══════════════════════════════════════════════════════════════════ */
    .rider-registration-container {
        width: 100%;
        max-width: 620px;
        margin: 0 auto;
    }

    .rider-header-block {
        text-align: center;
        margin-bottom: 12px;
    }

    .rider-partner-tag {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6F6382;
        margin-bottom: 4px;
    }

    .rider-auth-title {
        text-align: center !important;
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
        line-height: 1.15;
    }

    .rider-auth-subtitle {
        text-align: center !important;
        font-size: 0.92rem;
        color: #4b5563;
        margin-bottom: 14px;
        line-height: 1.4;
    }

    /* Notice Banner */
    .rider-notice-banner {
        background-color: #FAF8F6;
        border: 1.5px solid #EFEAE6;
        border-left: 4px solid #C08B7F;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .rider-notice-icon {
        width: 18px;
        height: 18px;
        color: #C08B7F;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .rider-notice-text {
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1.45;
        color: #4B453D;
        margin: 0;
    }

    /* Stepper Tabs */
    .rider-stepper-tabs {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 16px;
        user-select: none;
    }

    .rider-step-tab {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        cursor: default;
        position: relative;
    }

    .rider-step-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 4px;
        background: #ffffff;
        border: 1.5px solid #d1d5db;
        color: #9ca3af;
        transition: all 0.2s ease;
    }

    .rider-step-tab.active .rider-step-badge {
        background: #564B68;
        border-color: #564B68;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(86, 75, 104, 0.25);
    }

    .rider-step-title {
        font-size: 0.88rem;
        font-weight: 600;
        color: #9ca3af;
        margin-bottom: 6px;
        transition: color 0.2s ease;
    }

    .rider-step-tab.active .rider-step-title {
        color: #111827;
        font-weight: 700;
    }

    .rider-step-bar {
        width: 100%;
        height: 2px;
        background: #e5e7eb;
        border-radius: 2px;
        transition: background 0.2s ease;
    }

    .rider-step-tab.active .rider-step-bar {
        background: #564B68;
        height: 2.5px;
    }

    /* Form Section - Compact Spacing */
    .rider-form-section {
        margin-bottom: 10px;
    }

    .rider-section-divider {
        height: 1px;
        background: #f0edf4;
        margin: 12px 0 10px;
    }

    .rider-section-heading {
        font-family: 'Lato', sans-serif;
        font-size: 1.02rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
        letter-spacing: -0.2px;
    }

    .rider-field-group {
        margin-bottom: 10px;
    }

    .rider-registration-container .field-label {
        margin-bottom: 4px;
        font-size: 0.84rem;
        font-weight: 600;
        display: inline-block;
    }

    .rider-registration-container .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 4px;
    }

    .input-group {
        position: relative;
        width: 100%;
        margin-bottom: 0;
    }

    .input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        display: flex;
        align-items: center;
        pointer-events: none;
        z-index: 2;
    }

    .input-icon svg {
        width: 17px;
        height: 17px;
    }

    .rider-input {
        padding-left: 14px !important;
        padding-right: 14px !important;
        height: 42px;
        border-radius: 8px !important;
        font-size: 0.9rem !important;
    }

    .rider-input.rider-has-icon {
        padding-left: 38px !important;
    }

    .rider-doc-lead-icon {
        display: flex;
        align-items: center;
        color: #6F6382;
        flex-shrink: 0;
        margin-right: 2px;
    }

    .rider-doc-lead-icon svg {
        width: 18px;
        height: 18px;
    }

    .rider-select-wrapper {
        position: relative;
        width: 100%;
    }

    .rider-select {
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        background-color: #ffffff !important;
        padding-right: 36px !important;
    }

    .rider-select-chevron {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #6F6382;
        pointer-events: none;
    }

    .rider-field-hint {
        font-size: 0.76rem;
        color: #6b7280;
        margin-top: 3px;
        line-height: 1.35;
    }

    #vehicle_rule_hint {
        margin: 2px 0 6px 0;
        font-size: 0.76rem;
        line-height: 1.35;
    }

    .rider-field-error {
        font-size: 0.76rem;
        color: #ef4444;
        margin-top: 3px;
        display: none;
        font-weight: 600;
    }

    .rider-field-error.visible {
        display: block;
    }

    .required-star {
        color: #ef4444;
        font-weight: 700;
    }

    .rider-optional-pill {
        font-size: 0.7rem;
        font-weight: 700;
        color: #6F6382;
        background: #F3F0F7;
        padding: 1px 6px;
        border-radius: 9999px;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Document Upload Component */
    #documents_section .rider-field-group {
        margin-bottom: 10px;
    }

    #documents_section .rider-field-hint {
        line-height: 1.4;
    }

    .rider-file-upload-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 12px;
        background: #fcfbfd;
        border: 1px dashed #d8d1e1;
        border-radius: 8px;
        cursor: pointer;
        min-height: 52px;
        transition: all 0.2s ease;
    }

    .rider-file-upload-box:hover {
        border-color: #A8A0B2;
        background: #FAF8FC;
    }

    .rider-file-upload-box:focus-within {
        outline: 2px solid #6F6382;
        outline-offset: 3px;
    }

    .rider-file-upload-box.has-file {
        border-color: #6F6382;
        background: #FAF8FC;
    }

    .rider-file-upload-box.drag-over {
        border-color: #564B68;
        background: #F3EEF8;
        box-shadow: 0 0 0 3px rgba(86, 75, 104, 0.15);
    }

    .rider-hidden-file-input {
        display: none;
    }

    .rider-file-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .rider-choose-file-btn {
        background: #F5F0F9;
        color: #564B68;
        border: 1px solid #DDD3E6;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        font-family: inherit;
        min-height: 32px;
        transition: background 0.15s ease;
    }

    .rider-file-status {
        min-width: 0;
        font-size: 0.9rem;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
    }

    .rider-file-upload-box.has-file .rider-file-status {
        color: #111827;
        font-weight: 600;
    }

    .rider-file-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        margin-left: 10px;
    }

    .rider-file-pill-ready {
        font-size: 0.72rem;
        font-weight: 700;
        color: #065f46;
        background: #d1fae5;
        padding: 3px 8px;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .rider-clear-file-btn {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: color 0.15s, background 0.15s;
    }

    .rider-clear-file-btn:hover {
        color: #ef4444;
        background: #fee2e2;
    }

    .rider-clear-file-btn svg {
        width: 18px;
        height: 18px;
    }

    /* Continue Button */
    .rider-btn-continue {
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(111, 99, 130, 0.35) !important;
    }

    /* Modal for Details Step Complete */
    .rider-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(17, 24, 39, 0.55);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .rider-modal-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 32px 28px;
        max-width: 440px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
        border: 1px solid #ECE7F2;
        animation: riderModalIn 0.25s ease-out;
    }

    @keyframes riderModalIn {
        from { opacity: 0; transform: scale(0.94) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .rider-modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #F3EEF8;
        color: #564B68;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
    }

    .rider-modal-icon svg {
        width: 28px;
        height: 28px;
    }

    .rider-modal-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.6rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 10px;
    }

    .rider-modal-body {
        font-size: 0.92rem;
        color: #4b5563;
        line-height: 1.55;
        margin-bottom: 24px;
    }

    @media (max-width: 640px) {
        .rider-auth-title {
            font-size: 1.85rem;
        }
        .rider-file-upload-box {
            padding: 14px;
            flex-wrap: wrap;
        }
        .rider-file-left {
            display: grid;
            grid-template-columns: 24px minmax(0, 1fr);
            gap: 10px;
            flex-basis: 100%;
        }
        .rider-choose-file-btn {
            justify-self: start;
        }
        .rider-file-status {
            grid-column: 2;
        }
        .rider-file-right {
            margin-left: 34px;
        }
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

    .form-btn-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 24px;
    }
    .btn-action-back {
        width: 38%;
        height: 48px;
        background: linear-gradient(120deg, var(--brand-soft, #FAF9FB), var(--rose-light, #FCEAE6));
        color: var(--brand-dark, #564B68);
        border: 1px solid var(--rose-primary, #D4A69A);
        border-radius: 12px;
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
        height: 48px;
        background: linear-gradient(110deg, var(--brand-dark, #564B68) 0%, var(--brand-lavender, #6F6382) 48%, var(--rose-dark, #8C5A50) 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
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

<script>
    // Filter out non-numeric characters on keydown
    function filterPhoneKey(event) {
        // Allow navigation/editing keys
        const allowedKeys = [
            'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
            'Home', 'End', 'Tab', 'Enter', 'Escape'
        ];
        if (allowedKeys.includes(event.key)) {
            return;
        }

        // Allow Ctrl / Cmd combinations (Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z)
        if (event.ctrlKey || event.metaKey) {
            return;
        }

        // Allow '+' only as the very first character if cursor is at position 0
        const input = event.target;
        if (event.key === '+') {
            if (input.selectionStart === 0 && !input.value.includes('+')) {
                return;
            }
            event.preventDefault();
            return;
        }

        // Allow digits 0-9 only, block all letters, spaces, symbols
        if (!/^[0-9]$/.test(event.key)) {
            event.preventDefault();
        }
    }

    // Sanitize input to guarantee no letters or spaces remain (handles paste, drag, autofill)
    function sanitizePhoneInput(input) {
        const errorEl = document.getElementById('error_phone');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }

        let val = input.value;
        const hasLeadingPlus = val.startsWith('+');
        // Strip everything that is not a digit
        const digits = val.replace(/\D/g, '');

        if (hasLeadingPlus) {
            input.value = '+' + digits.slice(0, 12);
        } else {
            input.value = digits.slice(0, 11);
        }
    }

    // Handle paste to strip any letters, spaces, or illegal chars
    function handlePhonePaste(event, input) {
        event.preventDefault();
        const clipboardData = event.clipboardData || window.clipboardData;
        const pastedText = clipboardData ? clipboardData.getData('text') : '';
        if (!pastedText) return;

        const hasLeadingPlus = pastedText.trim().startsWith('+');
        const digits = pastedText.replace(/\D/g, '');

        if (hasLeadingPlus) {
            input.value = '+' + digits.slice(0, 12);
        } else {
            input.value = digits.slice(0, 11);
        }

        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Trigger native file picker
    function triggerFileInput(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.click();
        }
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // Handle file selection & display
    function handleFileSelected(input, statusId, boxId) {
        const statusEl = document.getElementById(statusId);
        const boxEl = document.getElementById(boxId);
        const actionEl = document.getElementById(input.id + '_action');
        const errorEl = document.getElementById('error_' + input.id);

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxBytes = 5 * 1024 * 1024; // 5 MB

            if (file.size > maxBytes) {
                if (errorEl) {
                    errorEl.textContent = 'File exceeds the 5 MB limit. Please select a smaller file.';
                    errorEl.classList.add('visible');
                }
                input.value = '';
                if (statusEl) statusEl.textContent = 'No file chosen';
                if (boxEl) boxEl.classList.remove('has-file');
                if (actionEl) actionEl.style.display = 'none';
                return;
            }

            // Valid file chosen
            if (statusEl) {
                statusEl.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            }
            if (boxEl) {
                boxEl.classList.add('has-file');
            }
            if (actionEl) {
                actionEl.style.display = 'flex';
            }
        } else {
            if (statusEl) statusEl.textContent = 'No file chosen';
            if (boxEl) boxEl.classList.remove('has-file');
            if (actionEl) actionEl.style.display = 'none';
        }
    }

    // Clear selected file
    function clearSelectedFile(event, inputId, statusId, boxId) {
        event.stopPropagation();
        const input = document.getElementById(inputId);
        const statusEl = document.getElementById(statusId);
        const boxEl = document.getElementById(boxId);
        const actionEl = document.getElementById(inputId + '_action');
        const errorEl = document.getElementById('error_' + inputId);

        if (input) input.value = '';
        if (statusEl) statusEl.textContent = 'No file chosen';
        if (boxEl) boxEl.classList.remove('has-file');
        if (actionEl) actionEl.style.display = 'none';
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }
    }

    // Drag and drop support for document upload boxes & buyer-matching name restrictions
    document.addEventListener('DOMContentLoaded', function () {
        // Enforce name rules matching buyer registration / account: letters and spaces only, max 50 chars
        ['first_name', 'last_name'].forEach(function (id) {
            const input = document.getElementById(id);
            const errorEl = document.getElementById('error_' + id);
            if (!input) return;

            let warningTimer = null;
            const flashDisallowedWarning = function () {
                if (errorEl) {
                    errorEl.textContent = 'Special characters and numbers are not allowed.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
                if (warningTimer) clearTimeout(warningTimer);
                warningTimer = setTimeout(function () {
                    const currentVal = input.value.trim();
                    if (!currentVal || /^(?:\p{L}\p{M}*|[ .'-])+$/u.test(currentVal)) {
                        if (errorEl) {
                            errorEl.textContent = '';
                            errorEl.classList.remove('visible');
                        }
                        input.classList.remove('error');
                    }
                }, 2500);
            };

            // Prevent typing disallowed characters (numbers and special symbols)
            input.addEventListener('beforeinput', function (event) {
                if (!event.isComposing && event.inputType === 'insertText' && event.data && /[^\p{L}\p{M} .'-]/u.test(event.data)) {
                    event.preventDefault();
                    flashDisallowedWarning();
                }
            });

            // Strip any pasted or autofilled numbers / special characters
            input.addEventListener('input', function (event) {
                if (event.isComposing) return;
                const original = input.value;
                if (/[^\p{L}\p{M} .'-]/u.test(original)) {
                    const caret = input.selectionStart;
                    input.value = original.replace(/[^\p{L}\p{M} .'-]/gu, '');
                    if (caret !== null) {
                        const newPos = original.slice(0, caret).replace(/[^\p{L}\p{M} .'-]/gu, '').length;
                        input.setSelectionRange(newPos, newPos);
                    }
                    flashDisallowedWarning();
                } else {
                    if (warningTimer) {
                        clearTimeout(warningTimer);
                        warningTimer = null;
                    }
                    if (errorEl) {
                        errorEl.textContent = '';
                        errorEl.classList.remove('visible');
                    }
                    input.classList.remove('error');
                }
            });

            input.addEventListener('compositionend', function () {
                const original = input.value;
                if (/[^\p{L}\p{M} .'-]/u.test(original)) {
                    input.value = original.replace(/[^\p{L}\p{M} .'-]/gu, '');
                    flashDisallowedWarning();
                }
            });

            input.addEventListener('blur', function () {
                const val = input.value.trim();
                const label = id === 'first_name' ? 'first name' : 'last name';
                if (!val) {
                    if (errorEl) {
                        errorEl.textContent = 'Please enter your ' + label + '.';
                        errorEl.classList.add('visible');
                    }
                    input.classList.add('error');
                } else if (Array.from(val).length > 50) {
                    if (errorEl) {
                        errorEl.textContent = (id === 'first_name' ? 'First' : 'Last') + ' name must not exceed 50 characters.';
                        errorEl.classList.add('visible');
                    }
                    input.classList.add('error');
                } else if (/[^\p{L}\p{M} .'-]/u.test(val)) {
                    if (errorEl) {
                        errorEl.textContent = 'Special characters and numbers are not allowed.';
                        errorEl.classList.add('visible');
                    }
                    input.classList.add('error');
                } else {
                    if (errorEl) {
                        errorEl.textContent = '';
                        errorEl.classList.remove('visible');
                    }
                    input.classList.remove('error');
                }
            });
        });

        ['license_doc', 'or_cr_doc'].forEach(function (id) {
            const box = document.getElementById(id + '_box');
            const input = document.getElementById(id);
            if (!box || !input) return;

            input.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            ['dragenter', 'dragover'].forEach(function (eventName) {
                box.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    box.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                box.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    box.classList.remove('drag-over');
                }, false);
            });

            box.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    input.files = dt.files;
                    handleFileSelected(input, id + '_status', id + '_box');
                }
            }, false);
        });

        // Plate number strict restrictions (letters and numbers only, max 7 chars, uppercase)
        const plateInput = document.getElementById('plate_no');
        const plateError = document.getElementById('error_plate_no');

        if (plateInput) {
            plateInput.addEventListener('beforeinput', function (event) {
                if (event.isComposing) return;

                if (event.inputType === 'insertText' && event.data) {
                    // Only letters and numbers
                    if (!/^[a-zA-Z0-9]+$/.test(event.data)) {
                        event.preventDefault();
                        return;
                    }

                    // Block typing if already 7 characters (walang sosobra)
                    const selStart = plateInput.selectionStart;
                    const selEnd = plateInput.selectionEnd;
                    const hasSelection = (selStart !== null && selEnd !== null && selStart !== selEnd);
                    if (!hasSelection && plateInput.value.length >= 7) {
                        event.preventDefault();
                        return;
                    }
                }
            });

            plateInput.addEventListener('input', function () {
                const original = plateInput.value;
                const cleaned = original.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 7);
                if (original !== cleaned) {
                    plateInput.value = cleaned;
                }

                const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
                const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');

                if (!isExempt && plateError && plateError.classList.contains('visible')) {
                    if (cleaned.length >= 6 && /(?=.*[A-Z])(?=.*[0-9])[A-Z0-9]{6,7}/.test(cleaned)) {
                        plateError.textContent = '';
                        plateError.classList.remove('visible');
                        plateInput.classList.remove('error');
                    }
                }
            });

            plateInput.addEventListener('blur', function () {
                const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
                const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');
                if (!isExempt && plateInput.value.trim().length > 0) {
                    validatePlateNumber(true);
                }
            });
        }

        // Driver's License Number strict format: A00-00-000000 (1 letter, 10 digits with hyphens)
        const licenseInput = document.getElementById('license_number');
        const licenseError = document.getElementById('error_license_number');

        if (licenseInput) {
            licenseInput.addEventListener('beforeinput', function (event) {
                if (event.isComposing) return;

                if (event.inputType === 'insertText' && event.data) {
                    const selStart = licenseInput.selectionStart;
                    const selEnd = licenseInput.selectionEnd;
                    const hasSelection = (selStart !== null && selEnd !== null && selStart !== selEnd);
                    const currentVal = licenseInput.value;

                    // First character must strictly be a letter [a-zA-Z]
                    if (selStart === 0 && (!hasSelection || selEnd === 0)) {
                        if (!/^[a-zA-Z]$/.test(event.data)) {
                            event.preventDefault();
                            return;
                        }
                    } else {
                        // After position 0, only digits 0-9 are allowed
                        if (!/^[0-9]$/.test(event.data)) {
                            event.preventDefault();
                            return;
                        }
                    }

                    // Walang sosobra: 1 letter + 10 digits = 11 alphanumeric characters max
                    const rawAlphaNum = currentVal.replace(/[^A-Za-z0-9]/g, '');
                    if (!hasSelection && rawAlphaNum.length >= 11) {
                        event.preventDefault();
                        return;
                    }
                }
            });

            licenseInput.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace') {
                    const selStart = licenseInput.selectionStart;
                    const selEnd = licenseInput.selectionEnd;
                    if (selStart === selEnd && (selStart === 4 || selStart === 7)) {
                        event.preventDefault();
                        const val = licenseInput.value;
                        const updated = val.slice(0, selStart - 2) + val.slice(selStart);
                        const formatted = formatLicenseNumber(updated);
                        licenseInput.value = formatted;
                        const newPos = Math.max(0, selStart - 2);
                        licenseInput.setSelectionRange(newPos, newPos);
                        licenseInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });

            licenseInput.addEventListener('paste', function (event) {
                event.preventDefault();
                const clipboardData = event.clipboardData || window.clipboardData;
                const pasted = clipboardData ? clipboardData.getData('text') : '';
                if (!pasted) return;

                licenseInput.value = formatLicenseNumber(pasted);
                licenseInput.dispatchEvent(new Event('input', { bubbles: true }));
            });

            licenseInput.addEventListener('input', function () {
                const original = licenseInput.value;
                const formatted = formatLicenseNumber(original);
                if (original !== formatted) {
                    const isAtEnd = (licenseInput.selectionStart === original.length);
                    licenseInput.value = formatted;
                    if (isAtEnd) {
                        licenseInput.setSelectionRange(formatted.length, formatted.length);
                    }
                }

                const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
                const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');

                if (!isExempt && licenseError && licenseError.classList.contains('visible')) {
                    if (/^[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}$/.test(formatted)) {
                        licenseError.textContent = '';
                        licenseError.classList.remove('visible');
                        licenseInput.classList.remove('error');
                    }
                }
            });

            licenseInput.addEventListener('blur', function () {
                const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
                const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');
                if (!isExempt && licenseInput.value.trim().length > 0) {
                    validateLicenseNumber(true);
                }
            });
        }
    });

    // Format Philippine Driver's License Number to A00-00-000000
    function formatLicenseNumber(val) {
        if (!val) return '';
        val = val.toUpperCase().replace(/\s+/g, '');
        const firstLetterMatch = val.match(/[A-Z]/);
        if (!firstLetterMatch) return '';
        const letter = firstLetterMatch[0];

        const afterLetter = val.slice(val.indexOf(letter) + 1);
        const digits = afterLetter.replace(/[^0-9]/g, '').slice(0, 10);

        let formatted = letter;
        if (digits.length > 0) {
            formatted += digits.slice(0, 2);
        }
        if (digits.length > 2) {
            formatted += '-' + digits.slice(2, 4);
        }
        if (digits.length > 4) {
            formatted += '-' + digits.slice(4, 10);
        }
        return formatted;
    }

    // Validate License Number (Walang sosobra, walang magkukulang)
    function validateLicenseNumber(showEmpty = true) {
        const input = document.getElementById('license_number');
        const errorEl = document.getElementById('error_license_number');
        if (!input) return true;

        const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
        const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');

        if (isExempt) {
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.remove('visible');
            }
            input.classList.remove('error');
            return true;
        }

        const val = input.value.trim().toUpperCase();

        if (!val) {
            if (showEmpty) {
                if (errorEl) {
                    errorEl.textContent = 'Please enter your driver\'s license number.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
            }
            return false;
        }

        const licenseRegex = /^[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}$/;
        if (!licenseRegex.test(val)) {
            if (errorEl) {
                if (val.length < 13) {
                    errorEl.textContent = 'License number is incomplete. Must be format A00-00-000000 (e.g. H83-78-937937).';
                } else {
                    errorEl.textContent = 'Please follow the official license format: A00-00-000000 (e.g. H83-78-937937).';
                }
                errorEl.classList.add('visible');
            }
            input.classList.add('error');
            return false;
        }

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }
        input.classList.remove('error');
        return true;
    }

    // Validate Plate Number (Walang sosobra, walang magkukulang)
    function validatePlateNumber(showEmpty = true) {
        const input = document.getElementById('plate_no');
        const errorEl = document.getElementById('error_plate_no');
        if (!input) return true;

        const vehicleType = document.getElementById('vehicle_type') ? document.getElementById('vehicle_type').value : '';
        const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');

        if (isExempt) {
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.remove('visible');
            }
            input.classList.remove('error');
            return true;
        }

        const val = input.value.trim().toUpperCase();

        if (!val) {
            if (showEmpty) {
                if (errorEl) {
                    errorEl.textContent = 'Please enter your plate number.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
            }
            return false;
        }

        if (val.length < 6) {
            if (errorEl) {
                errorEl.textContent = 'Plate number is incomplete. Must be 6 or 7 characters (e.g. 123ABC or NBC1234).';
                errorEl.classList.add('visible');
            }
            input.classList.add('error');
            return false;
        }

        if (val.length > 7) {
            if (errorEl) {
                errorEl.textContent = 'Plate number must not exceed 7 characters.';
                errorEl.classList.add('visible');
            }
            input.classList.add('error');
            return false;
        }

        if (!/(?=.*[A-Z])(?=.*[0-9])[A-Z0-9]{6,7}/.test(val)) {
            if (errorEl) {
                errorEl.textContent = 'Please enter a valid plate number format (e.g. 123ABC or NBC1234).';
                errorEl.classList.add('visible');
            }
            input.classList.add('error');
            return false;
        }

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }
        input.classList.remove('error');
        return true;
    }

    // Handle Vehicle Type change (Bicycle / E-bike special rule)
    function handleVehicleTypeChange(type) {
        const isExempt = (type === 'bicycle' || type === 'ebike');

        const plateStar = document.getElementById('plate_star');
        const plateOpt = document.getElementById('plate_optional');
        const plateInput = document.getElementById('plate_no');
        const plateErr = document.getElementById('error_plate_no');

        const licenseStar = document.getElementById('license_star');
        const licenseOpt = document.getElementById('license_optional');
        const licenseInput = document.getElementById('license_number');
        const licenseErr = document.getElementById('error_license_number');

        const expiryStar = document.getElementById('expiry_star');
        const expiryOpt = document.getElementById('expiry_optional');
        const expiryInput = document.getElementById('license_expiry');

        const licenseDocStar = document.getElementById('license_doc_star');
        const licenseDocOpt = document.getElementById('license_doc_optional');

        const orCrStar = document.getElementById('or_cr_star');
        const orCrOpt = document.getElementById('or_cr_optional');

        if (isExempt) {
            if (plateStar) plateStar.style.display = 'none';
            if (plateOpt) plateOpt.style.display = 'inline';
            if (plateInput) {
                plateInput.placeholder = 'Not required for ' + (type === 'ebike' ? 'E-bike' : 'Bicycle');
                plateInput.classList.remove('error');
            }
            if (plateErr) {
                plateErr.textContent = '';
                plateErr.classList.remove('visible');
            }

            if (licenseStar) licenseStar.style.display = 'none';
            if (licenseOpt) licenseOpt.style.display = 'inline';
            if (licenseInput) {
                licenseInput.placeholder = 'Not required for ' + (type === 'ebike' ? 'E-bike' : 'Bicycle');
                licenseInput.classList.remove('error');
            }
            if (licenseErr) {
                licenseErr.textContent = '';
                licenseErr.classList.remove('visible');
            }

            if (expiryStar) expiryStar.style.display = 'none';
            if (expiryOpt) expiryOpt.style.display = 'inline';

            if (licenseDocStar) licenseDocStar.style.display = 'none';
            if (licenseDocOpt) licenseDocOpt.style.display = 'inline';

            if (orCrStar) orCrStar.style.display = 'none';
            if (orCrOpt) orCrOpt.style.display = 'inline';
        } else {
            if (plateStar) plateStar.style.display = 'inline';
            if (plateOpt) plateOpt.style.display = 'none';
            if (plateInput) plateInput.placeholder = '123ABC or NBC1234';

            if (licenseStar) licenseStar.style.display = 'inline';
            if (licenseOpt) licenseOpt.style.display = 'none';
            if (licenseInput) licenseInput.placeholder = 'A00-00-000000';

            if (expiryStar) expiryStar.style.display = 'inline';
            if (expiryOpt) expiryOpt.style.display = 'none';

            if (licenseDocStar) licenseDocStar.style.display = 'inline';
            if (licenseDocOpt) licenseDocOpt.style.display = 'none';

            if (orCrStar) orCrStar.style.display = 'inline';
            if (orCrOpt) orCrOpt.style.display = 'none';
        }
    }

    let currentRiderStep = 1;
    let timerInterval = null;
    let secondsRemaining = 60;
    let otpRequestPending = false;
    let latestOtpPreview = null;
    let latestMailSent = true;

    function showFieldError(id, message, focus = false) {
        const input = document.getElementById(id);
        const error = document.getElementById(id + '_error') || document.getElementById('error_' + id);
        if (error) {
            error.textContent = message;
            if (message) error.classList.add('visible');
            else error.classList.remove('visible');
        }
        if (input) {
            if (message) input.classList.add('error');
            else input.classList.remove('error');
            if (message && focus) input.focus();
        }
        return !message;
    }

    function goToRiderStep(targetStep) {
        currentRiderStep = targetStep;

        const step1El = document.getElementById('riderStepDetails');
        const step2El = document.getElementById('riderStepSignIn');
        const step3El = document.getElementById('riderStepVerify');

        if (step1El) step1El.style.display = (targetStep === 1) ? 'block' : 'none';
        if (step2El) step2El.style.display = (targetStep === 2) ? 'block' : 'none';
        if (step3El) step3El.style.display = (targetStep === 3) ? 'block' : 'none';

        for (let i = 1; i <= 3; i++) {
            const label = document.getElementById('label-step-' + i);
            if (label) {
                if (i <= targetStep) {
                    label.classList.remove('inactive');
                } else {
                    label.classList.add('inactive');
                }
            }
        }
        const line1 = document.getElementById('line-1');
        const line2 = document.getElementById('line-2');
        if (line1) {
            if (targetStep >= 2) line1.classList.remove('inactive');
            else line1.classList.add('inactive');
        }
        if (line2) {
            if (targetStep >= 3) line2.classList.remove('inactive');
            else line2.classList.add('inactive');
        }

        if (targetStep === 3) {
            const applicant = document.getElementById('verificationApplicant');
            const firstName = document.getElementById('first_name')?.value?.trim() || '';
            const lastName = document.getElementById('last_name')?.value?.trim() || '';
            const fullName = [firstName, lastName].filter(Boolean).join(' ');
            const email = document.getElementById('email')?.value?.trim() || '';
            if (applicant) {
                applicant.textContent = `${fullName} — courier account. Enter the 6-digit code sent to ${email}.`;
            }
            updateOtpPreviewUI();
            setTimeout(() => {
                const firstOtp = document.querySelector('.otp-input[data-index="0"]');
                if (firstOtp) firstOtp.focus();
            }, 150);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function togglePasswordVisibility(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        if (btnEl) {
            btnEl.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
            btnEl.setAttribute('aria-controls', inputId);
            btnEl.setAttribute('aria-pressed', String(visible));
            const slash = btnEl.querySelector('.eye-slash');
            if (slash) slash.style.display = visible ? '' : 'none';
        }
    }

    function validateRegistrationEmail(focus = false) {
        const input = document.getElementById('email');
        if (!input) return true;
        const val = input.value.trim().toLowerCase();
        input.value = val;
        let message = '';
        if (!val) {
            message = 'Please enter your email address.';
        } else if (!/^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/.test(val) || !input.validity.valid) {
            message = 'Please enter a valid email address.';
        }
        return showFieldError('email', message, focus);
    }

    function validateRegistrationPasswords(focus = false) {
        const passwordInput = document.getElementById('register_password');
        const confirmInput = document.getElementById('password_confirmation');
        if (!passwordInput || !confirmInput) return true;

        const password = passwordInput.value;
        const confirmation = confirmInput.value;
        let message = '';

        if (password.length < 8) {
            message = 'Password must be at least 8 characters long.';
        } else if (!/\p{Lu}/u.test(password)) {
            message = 'Password must contain at least one uppercase letter.';
        } else if (!/\p{Ll}/u.test(password)) {
            message = 'Password must contain at least one lowercase letter.';
        } else if (!/\p{N}/u.test(password)) {
            message = 'Password must contain at least one number.';
        } else if (!/[\p{Z}\p{S}\p{P}]/u.test(password)) {
            message = 'Password must contain at least one symbol.';
        }

        const validPassword = showFieldError('register_password', message, focus);
        const validConfirmation = showFieldError('password_confirmation', password === confirmation ? '' : 'The password confirmation does not match.', focus && validPassword);
        return validPassword && validConfirmation;
    }

    async function sendAutomaticOtp() {
        if (otpRequestPending || !validateRegistrationEmail(true)) return false;
        const emailInput = document.getElementById('email');
        const email = emailInput ? emailInput.value.trim().toLowerCase() : '';
        const name = document.getElementById('name')?.value?.trim() || 'Rider Applicant';
        const form = document.getElementById('riderDetailsForm');
        const requestOtpUrl = form?.dataset?.requestOtpUrl || '{{ route("register.request_otp", [], false) }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                       || document.querySelector('input[name="_token"]')?.value || '';

        if (!email) return false;

        otpRequestPending = true;
        const sendButton = document.getElementById('sendVerificationCodeButton');
        const sendLabel = document.getElementById('sendVerificationCodeLabel');
        if (sendButton) sendButton.disabled = true;
        if (sendLabel) sendLabel.textContent = 'Sending code...';

        const phoneVal = document.getElementById('phone')?.value?.trim() || '';

        try {
            const response = await fetch(requestOtpUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    email: email,
                    name: name,
                    role: 'courier',
                    phone: phoneVal,
                    phone_country_code: '+63'
                })
            });

            if (response.status === 419) {
                throw new Error('Your session has expired. Please refresh the page and try again.');
            }
            if (response.status === 429) {
                throw new Error('Too many requests. Please wait a minute before requesting another code.');
            }

            const data = await response.json();
            if (!response.ok || !data.success) {
                const err = data.errors?.email?.[0] || data.errors?.phone?.[0] || data.message || 'Unable to send verification code. Please try again.';
                showFieldError('email', err, true);
                return false;
            }

            latestOtpPreview = data.otp_preview || null;
            latestMailSent = data.mail_sent !== false;
            updateOtpPreviewUI();
            return true;
        } catch (error) {
            showFieldError('email', error.message || 'Unable to send verification code. Please check your connection and try again.', true);
            return false;
        } finally {
            otpRequestPending = false;
            if (sendButton) sendButton.disabled = false;
            if (sendLabel) sendLabel.textContent = 'Send verification code';
        }
    }

    async function handleContinueToVerify() {
        if (!validateRegistrationEmail(true)) return;
        if (!validateRegistrationPasswords(true)) return;

        const sent = await sendAutomaticOtp();
        if (!sent) return;

        startOtpTimer();
        goToRiderStep(3);
    }

    function startOtpTimer() {
        if (timerInterval) clearInterval(timerInterval);
        secondsRemaining = 60;
        const timerDisplay = document.getElementById('otpTimerDisplay');
        const resendBtn = document.getElementById('resendOtpBtn');

        if (resendBtn) resendBtn.disabled = true;
        if (timerDisplay) timerDisplay.innerText = '00:60';

        timerInterval = setInterval(() => {
            secondsRemaining--;
            if (secondsRemaining <= 0) {
                clearInterval(timerInterval);
                if (timerDisplay) timerDisplay.innerText = '00:00';
                if (resendBtn) {
                    resendBtn.disabled = false;
                    resendBtn.innerText = 'Resend code';
                }
            } else {
                const formatted = '00:' + (secondsRemaining < 10 ? '0' + secondsRemaining : secondsRemaining);
                if (timerDisplay) timerDisplay.innerText = formatted;
            }
        }, 1000);
    }

    async function triggerResendOtp() {
        const resendBtn = document.getElementById('resendOtpBtn');
        if (resendBtn && resendBtn.disabled) return;

        const sent = await sendAutomaticOtp();
        if (!sent) return;
        startOtpTimer();

        const feedback = document.getElementById('otpFeedbackMsg');
        if (feedback) {
            feedback.style.display = 'block';
            if (latestMailSent) {
                feedback.style.color = '#059669';
                feedback.innerText = 'A new verification code has been sent to your email!';
            } else {
                feedback.style.color = '#d97706';
                feedback.innerText = 'A new verification code has been generated (see code preview below).';
            }
        }
    }

    function fillOtpBoxes(code) {
        if (!code) return;
        const otpInputs = document.querySelectorAll('.otp-input');
        const digits = code.toString().split('');
        digits.forEach((d, i) => {
            if (otpInputs[i]) {
                otpInputs[i].value = d;
                otpInputs[i].classList.add('filled');
            }
        });
        const hiddenOtp = document.getElementById('email_verification_otp');
        if (hiddenOtp) hiddenOtp.value = code;
    }

    function updateOtpPreviewUI() {
        const devBanner = document.getElementById('devOtpBanner');
        const devCode = document.getElementById('devOtpCode');
        const devNotice = document.getElementById('devMailStatusNotice');
        const autofillBtn = document.getElementById('btnAutofillOtp');
        const otpSentBanner = document.getElementById('otpSentNotice');

        if (latestOtpPreview) {
            if (devCode) devCode.innerText = latestOtpPreview;
            if (devNotice) {
                if (!latestMailSent) {
                    devNotice.innerText = '⚠️ Email server could not be reached via SMTP. Use this test verification code:';
                    devNotice.style.color = '#b91c1c';
                } else {
                    devNotice.innerText = '✓ Verification email sent. (Preview displayed for local testing)';
                    devNotice.style.color = '#15803d';
                }
            }
            if (devBanner) devBanner.style.display = 'block';

            if (autofillBtn) {
                autofillBtn.onclick = function() {
                    fillOtpBoxes(latestOtpPreview);
                };
            }

            if (otpSentBanner && !latestMailSent) {
                otpSentBanner.style.backgroundColor = '#fffbeb';
                otpSentBanner.style.borderColor = '#fde68a';
                otpSentBanner.style.color = '#92400e';
                const bannerText = otpSentBanner.querySelector('.banner-text');
                if (bannerText) bannerText.innerText = 'Mail server unreachable. Use the test code below to continue.';
                const bannerIcon = otpSentBanner.querySelector('.banner-icon');
                if (bannerIcon) bannerIcon.innerText = '⚠️';
            }
        }
    }

    // Step 1 Validation & Continue Handler
    function handleContinueToSignIn() {
        let hasError = false;

        function checkField(id, errorMsg) {
            const el = document.getElementById(id);
            const errEl = document.getElementById('error_' + id);
            if (!el) return true;

            const val = el.value.trim();
            if (!val) {
                if (errEl) {
                    errEl.textContent = errorMsg;
                    errEl.classList.add('visible');
                }
                el.classList.add('error');
                if (!hasError) el.focus();
                hasError = true;
                return false;
            } else {
                if (errEl) {
                    errEl.textContent = '';
                    errEl.classList.remove('visible');
                }
                el.classList.remove('error');
                return true;
            }
        }

        function validateNameField(id) {
            const input = document.getElementById(id);
            const errorEl = document.getElementById('error_' + id);
            if (!input) return true;
            const val = input.value.trim();
            const label = id === 'first_name' ? 'first name' : 'last name';

            if (!val) {
                if (errorEl) {
                    errorEl.textContent = 'Please enter your ' + label + '.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
                if (!hasError) input.focus();
                hasError = true;
                return false;
            } else if (Array.from(val).length > 50) {
                if (errorEl) {
                    errorEl.textContent = (id === 'first_name' ? 'First' : 'Last') + ' name must not exceed 50 characters.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
                if (!hasError) input.focus();
                hasError = true;
                return false;
            } else if (/[^\p{L}\p{M} .'-]/u.test(val)) {
                if (errorEl) {
                    errorEl.textContent = 'Special characters and numbers are not allowed.';
                    errorEl.classList.add('visible');
                }
                input.classList.add('error');
                if (!hasError) input.focus();
                hasError = true;
                return false;
            } else {
                if (errorEl) {
                    errorEl.textContent = '';
                    errorEl.classList.remove('visible');
                }
                input.classList.remove('error');
                return true;
            }
        }

        validateNameField('first_name');
        validateNameField('last_name');

        const phoneEl = document.getElementById('phone');
        const phoneVal = phoneEl ? phoneEl.value.trim() : '';
        const phoneErr = document.getElementById('error_phone');
        const phoneRegex = /^(09\d{9}|\+639\d{9})$/;

        if (!phoneVal) {
            checkField('phone', 'Please enter your mobile number.');
        } else if (!phoneRegex.test(phoneVal)) {
            if (phoneErr) {
                phoneErr.textContent = 'Please enter a valid mobile number (e.g. 09171234567 or +639171234567).';
                phoneErr.classList.add('visible');
            }
            phoneEl.classList.add('error');
            if (!hasError) phoneEl.focus();
            hasError = true;
        } else {
            if (phoneErr) {
                phoneErr.textContent = '';
                phoneErr.classList.remove('visible');
            }
            phoneEl.classList.remove('error');
        }

        checkField('birthday', 'Please select your birthday.');
        checkField('logistics_provider_id', 'Please choose a logistics partner.');
        checkField('vehicle_type', 'Please select your vehicle type.');

        const vehicleType = document.getElementById('vehicle_type').value;
        const isExempt = (vehicleType === 'bicycle' || vehicleType === 'ebike');

        if (!isExempt) {
            const plateValid = validatePlateNumber(true);
            if (!plateValid) {
                if (!hasError) {
                    const plateInput = document.getElementById('plate_no');
                    if (plateInput) plateInput.focus();
                }
                hasError = true;
            }

            const licenseValid = validateLicenseNumber(true);
            if (!licenseValid) {
                if (!hasError) {
                    const licenseInput = document.getElementById('license_number');
                    if (licenseInput) licenseInput.focus();
                }
                hasError = true;
            }
            checkField('license_expiry', 'Please enter your license expiry date.');

            // Check files
            const licenseDoc = document.getElementById('license_doc');
            const errLicenseDoc = document.getElementById('error_license_doc');
            if (licenseDoc && (!licenseDoc.files || !licenseDoc.files[0])) {
                if (errLicenseDoc) {
                    errLicenseDoc.textContent = 'Please upload a photo of your driver\'s license.';
                    errLicenseDoc.classList.add('visible');
                }
                hasError = true;
            }

            const orCrDoc = document.getElementById('or_cr_doc');
            const errOrCrDoc = document.getElementById('error_or_cr_doc');
            if (orCrDoc && (!orCrDoc.files || !orCrDoc.files[0])) {
                if (errOrCrDoc) {
                    errOrCrDoc.textContent = 'Please upload your vehicle OR/CR.';
                    errOrCrDoc.classList.add('visible');
                }
                hasError = true;
            }
        }

        if (hasError) return;

        // Details step is completely valid!
        const firstName = document.getElementById('first_name')?.value?.trim() || '';
        const lastName = document.getElementById('last_name')?.value?.trim() || '';
        const nameInput = document.getElementById('name');
        if (nameInput) nameInput.value = `${firstName} ${lastName}`.trim();

        goToRiderStep(2);
    }

    function setupRiderBirthdayHandling() {
        const bday = document.getElementById('birthday');
        const ageText = document.getElementById('rider_birthday_age_text');
        const bdayErr = document.getElementById('error_birthday');
        if (!bday) return;

        function calcAndClampRiderAge() {
            let val = bday.value;
            if (!val) {
                if (ageText) ageText.textContent = '';
                return;
            }
            if (bday.max && val > bday.max) {
                val = bday.max;
                bday.value = val;
            }
            const birth = new Date(val + 'T00:00:00');
            if (isNaN(birth.getTime())) {
                if (ageText) ageText.textContent = '';
                return;
            }
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;

            if (ageText) {
                if (age >= 18) {
                    ageText.textContent = `Age: ${age} years old`;
                    ageText.style.color = '#059669';
                    if (bdayErr) {
                        bdayErr.textContent = '';
                        bdayErr.classList.remove('visible');
                    }
                    bday.classList.remove('error');
                } else if (age >= 0) {
                    ageText.textContent = `Age: ${age} (Must be 18+)`;
                    ageText.style.color = '#ef4444';
                }
            }
        }

        bday.addEventListener('input', calcAndClampRiderAge);
        bday.addEventListener('change', calcAndClampRiderAge);
        bday.addEventListener('focus', function() {
            if (!this.value && this.max) {
                this.value = this.max;
                calcAndClampRiderAge();
            }
        });

        if (bday.value) calcAndClampRiderAge();
    }

    // OTP Boxes Handlers & Form Submission
    document.addEventListener('DOMContentLoaded', function () {
        setupRiderBirthdayHandling();
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenOtp = document.getElementById('email_verification_otp');

        function syncOtp() {
            let code = '';
            otpInputs.forEach(inp => { code += inp.value; });
            if (hiddenOtp) hiddenOtp.value = code;
            return code;
        }

        otpInputs.forEach((input, idx) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value) {
                    this.classList.add('filled');
                    if (idx < otpInputs.length - 1) {
                        otpInputs[idx + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }
                syncOtp();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    otpInputs[idx - 1].focus();
                    otpInputs[idx - 1].value = '';
                    otpInputs[idx - 1].classList.remove('filled');
                    syncOtp();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
                const digits = pasteData.replace(/[^0-9]/g, '').slice(0, 6);
                if (digits) {
                    digits.split('').forEach((d, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = d;
                            otpInputs[i].classList.add('filled');
                        }
                    });
                    syncOtp();
                    const focusIdx = Math.min(digits.length, otpInputs.length - 1);
                    otpInputs[focusIdx].focus();
                }
            });
        });

        ['register_password', 'password_confirmation'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => validateRegistrationPasswords());
        });
        document.getElementById('email')?.addEventListener('input', () => showFieldError('email', ''));
        document.getElementById('email')?.addEventListener('blur', () => validateRegistrationEmail());

        const form = document.getElementById('riderDetailsForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const code = syncOtp();
                if (code.length !== 6) {
                    e.preventDefault();
                    const feedback = document.getElementById('otpFeedbackMsg');
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.style.color = '#ef4444';
                        feedback.innerText = 'Please enter the complete 6-digit verification code.';
                    }
                    const firstEmpty = Array.from(otpInputs).find(inp => !inp.value);
                    if (firstEmpty) firstEmpty.focus();
                    return false;
                }
            });
        }

        @if($errors->has('email_verification_otp'))
            goToRiderStep(3);
        @elseif($errors->has('email') || $errors->has('password'))
            goToRiderStep(2);
        @endif
    });
</script>
@endsection
