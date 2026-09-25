/**
 * Cartzy Authentication - Registration Multi-step Wizard Scripts
 * Handles role selection, PSGC cascading address, OTP verification, and form validation
 */

let currentStep = 1;
const totalSteps = 3;

// Role switcher function
function selectRole(role) {
    const buyerRadio = document.getElementById('role_buyer');
    const sellerRadio = document.getElementById('role_seller');
    const buyerOpt = document.getElementById('roleOptionBuyer');
    const sellerOpt = document.getElementById('roleOptionSeller');
    const subtitle = document.querySelector('.auth-subtitle');
    const submitBtn = document.getElementById('submitRegBtn');
    const kycSection = document.getElementById('sellerKycSection');

    if (role === 'seller') {
        if (sellerRadio) sellerRadio.checked = true;
        if (buyerRadio) buyerRadio.checked = false;
        if (sellerOpt) sellerOpt.classList.add('selected');
        if (buyerOpt) buyerOpt.classList.remove('selected');
        if (subtitle) subtitle.innerText = 'Join Cartzy as a Seller and start selling your products';
        if (submitBtn) submitBtn.innerText = 'Complete & Submit Application';
        if (kycSection) kycSection.style.display = 'block';
    } else {
        if (buyerRadio) buyerRadio.checked = true;
        if (sellerRadio) sellerRadio.checked = false;
        if (buyerOpt) buyerOpt.classList.add('selected');
        if (sellerOpt) sellerOpt.classList.remove('selected');
        if (subtitle) subtitle.innerText = 'Join Cartzy and start shopping the best electronics';
        if (submitBtn) submitBtn.innerText = 'Complete & Start Shopping';
        if (kycSection) kycSection.style.display = 'none';
    }
}

// Initialize initial role on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const defaultRole = form?.dataset?.initialRole || 'buyer';
    const selectedRole = document.querySelector('input[name="role"]:checked')?.value || defaultRole;
    selectRole(selectedRole);
});

// Age auto-calculate from birthday
function autoCalcAge(dateStr) {
    if (!dateStr) { 
        const ageEl = document.getElementById('age_display');
        if (ageEl) ageEl.value = ''; 
        return; 
    }
    const today = new Date();
    const bday  = new Date(dateStr);
    let age = today.getFullYear() - bday.getFullYear();
    const m = today.getMonth() - bday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
    const ageEl = document.getElementById('age_display');
    if (ageEl) ageEl.value = age >= 0 ? age : '';
}

function updateStepperUI() {
    for (let i = 1; i <= totalSteps; i++) {
        const labelEl = document.getElementById('label-step-' + i);
        const lineEl = document.getElementById('line-' + i);

        if (i <= currentStep) {
            if (labelEl) labelEl.classList.remove('inactive');
            if (lineEl && (i < currentStep || currentStep === totalSteps)) {
                lineEl.classList.remove('inactive');
            }
        } else {
            if (labelEl) labelEl.classList.add('inactive');
            if (lineEl) lineEl.classList.add('inactive');
        }
    }

    const line1 = document.getElementById('line-1');
    const line2 = document.getElementById('line-2');
    if (currentStep >= 2) {
        if (line1) line1.classList.remove('inactive');
    } else {
        if (line1) line1.classList.add('inactive');
    }
    if (currentStep >= 3) {
        if (line2) line2.classList.remove('inactive');
    } else {
        if (line2) line2.classList.add('inactive');
    }

    for (let i = 1; i <= totalSteps; i++) {
        const stepDiv = document.getElementById('step-' + i);
        if (stepDiv) {
            stepDiv.style.display = (i === currentStep) ? 'block' : 'none';
        }
    }
}

function nextStep(step) {
    if (currentStep === 1) {
        const firstName = document.getElementById('first_name')?.value?.trim();
        const lastName = document.getElementById('last_name')?.value?.trim();
        const mi = document.getElementById('middle_initial')?.value?.trim() || '';
        const sex = document.getElementById('sex')?.value;
        const email = document.getElementById('email')?.value?.trim();

        if (!firstName) {
            document.getElementById('first_name')?.focus();
            return;
        }
        if (!lastName) {
            document.getElementById('last_name')?.focus();
            return;
        }
        if (!sex) {
            document.getElementById('sex')?.focus();
            return;
        }
        if (!email) {
            document.getElementById('email')?.focus();
            return;
        }
        const nameField = document.getElementById('name');
        if (nameField) {
            nameField.value = firstName + (mi ? ' ' + mi : '') + ' ' + lastName;
        }
    }
    if (currentStep === 2) {
        const phone = document.getElementById('phone')?.value?.trim();
        const birthday = document.getElementById('birthday')?.value?.trim();
        const street = document.getElementById('street_address')?.value?.trim();
        const region = document.getElementById('region')?.value;

        if (!phone) {
            document.getElementById('phone')?.focus();
            return;
        }
        if (!birthday) {
            document.getElementById('birthday')?.focus();
            return;
        }
        if (!street) {
            document.getElementById('street_address')?.focus();
            return;
        }
        if (!region) {
            document.getElementById('region')?.focus();
            return;
        }

        // If registering as Seller, validate business fields
        const chosenRole = document.querySelector('input[name="role"]:checked')?.value;
        if (chosenRole === 'seller') {
            const businessNameEl = document.getElementById('business_name');
            const businessName = businessNameEl?.value?.trim();
            const lineOfBusinessEl = document.getElementById('line_of_business');
            const lineOfBusiness = lineOfBusinessEl?.value;
            if (!businessName) {
                businessNameEl?.focus();
                if (businessNameEl) businessNameEl.style.borderColor = '#ef4444';
                return;
            }
            if (!lineOfBusiness) {
                lineOfBusinessEl?.focus();
                if (lineOfBusinessEl) lineOfBusinessEl.style.borderColor = '#ef4444';
                return;
            }
            // Reset error borders
            if (businessNameEl) businessNameEl.style.borderColor = '';
            if (lineOfBusinessEl) lineOfBusinessEl.style.borderColor = '';
        }
    }

    currentStep = step;
    updateStepperUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // When landing on Step 3 (Security & OTP)
    if (currentStep === 3) {
        sendAutomaticOtp();
        startOtpTimer();
    }
}

function prevStep(step) {
    currentStep = step;
    updateStepperUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// --- Cascading Philippine Address Dropdowns (PSGC API) ---
const PSGC = 'https://psgc.gitlab.io/api';

function populateSelect(sel, items, valueKey, labelKey, placeholder) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    items.sort((a, b) => a[labelKey].localeCompare(b[labelKey])).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item[valueKey];
        opt.textContent = item[labelKey];
        sel.appendChild(opt);
    });
    sel.disabled = false;
}

function resetSelect(sel, placeholder) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.disabled = true;
}

// Region -> Province
document.getElementById('region')?.addEventListener('change', function() {
    const regionCode = this.value;
    const provSel  = document.getElementById('province');
    const citySel  = document.getElementById('city');
    const brgySel  = document.getElementById('barangay');
    resetSelect(provSel,  'Select Province');
    resetSelect(citySel,  'Select City / Municipality');
    resetSelect(brgySel,  'Select Barangay');
    if (!regionCode) return;

    // NCR has no provinces — go straight to cities
    if (regionCode === 'NCR') {
        provSel.innerHTML = '<option value="Metro Manila" selected>Metro Manila</option>';
        provSel.disabled = false;
        provSel.value = 'Metro Manila';
        // Load NCR cities
        citySel.innerHTML = '<option value="">Loading...</option>';
        fetch(`${PSGC}/regions/130000000/cities-municipalities.json`)
            .then(r => r.json())
            .then(data => populateSelect(citySel, data, 'name', 'name', 'Select City / Municipality'))
            .catch(() => resetSelect(citySel, 'Select City / Municipality'));
        return;
    }

    // Map region code to PSGC numeric code
    const regionMap = {
        'CAR':'140000000','I':'010000000','II':'020000000','III':'030000000',
        'IV-A':'040000000','IV-B':'170000000','V':'050000000','VI':'060000000',
        'VII':'070000000','VIII':'080000000','IX':'090000000','X':'100000000',
        'XI':'110000000','XII':'120000000','XIII':'160000000','BARMM':'190000000'
    };
    const psgcCode = regionMap[regionCode];
    if (!psgcCode) return;

    provSel.innerHTML = '<option value="">Loading...</option>';
    fetch(`${PSGC}/regions/${psgcCode}/provinces.json`)
        .then(r => r.json())
        .then(data => populateSelect(provSel, data, 'name', 'name', 'Select Province'))
        .catch(() => resetSelect(provSel, 'Select Province'));
});

// Province -> City/Municipality
document.getElementById('province')?.addEventListener('change', function() {
    const provName = this.value;
    const citySel  = document.getElementById('city');
    const brgySel  = document.getElementById('barangay');
    resetSelect(citySel,  'Select City / Municipality');
    resetSelect(brgySel,  'Select Barangay');
    if (!provName) return;

    const regionCode = document.getElementById('region')?.value;
    const regionMap = {
        'NCR':'130000000','CAR':'140000000','I':'010000000','II':'020000000',
        'III':'030000000','IV-A':'040000000','IV-B':'170000000','V':'050000000',
        'VI':'060000000','VII':'070000000','VIII':'080000000','IX':'090000000',
        'X':'100000000','XI':'110000000','XII':'120000000','XIII':'160000000','BARMM':'190000000'
    };
    const psgcCode = regionMap[regionCode];
    if (!psgcCode) return;

    if (regionCode === 'NCR') {
        return;
    }

    citySel.innerHTML = '<option value="">Loading...</option>';
    fetch(`${PSGC}/regions/${psgcCode}/provinces.json`)
        .then(r => r.json())
        .then(provinces => {
            const prov = provinces.find(p => p.name === provName);
            if (!prov) { resetSelect(citySel, 'Select City / Municipality'); return; }
            return fetch(`${PSGC}/provinces/${prov.code}/cities-municipalities.json`);
        })
        .then(r => r && r.json())
        .then(data => data && populateSelect(citySel, data, 'name', 'name', 'Select City / Municipality'))
        .catch(() => resetSelect(citySel, 'Select City / Municipality'));
});

// City/Municipality -> Barangay
document.getElementById('city')?.addEventListener('change', function() {
    const cityName = this.value;
    const brgySel  = document.getElementById('barangay');
    resetSelect(brgySel, 'Select Barangay');
    if (!cityName) return;

    const regionCode = document.getElementById('region')?.value;
    const provName   = document.getElementById('province')?.value;
    const regionMap = {
        'NCR':'130000000','CAR':'140000000','I':'010000000','II':'020000000',
        'III':'030000000','IV-A':'040000000','IV-B':'170000000','V':'050000000',
        'VI':'060000000','VII':'070000000','VIII':'080000000','IX':'090000000',
        'X':'100000000','XI':'110000000','XII':'120000000','XIII':'160000000','BARMM':'190000000'
    };
    const psgcCode = regionMap[regionCode];
    if (!psgcCode) return;

    brgySel.innerHTML = '<option value="">Loading...</option>';

    // For NCR, fetch from region cities directly
    if (regionCode === 'NCR') {
        fetch(`${PSGC}/regions/130000000/cities-municipalities.json`)
            .then(r => r.json())
            .then(cities => {
                const city = cities.find(c => c.name === cityName);
                if (!city) { resetSelect(brgySel, 'Select Barangay'); return; }
                return fetch(`${PSGC}/cities-municipalities/${city.code}/barangays.json`);
            })
            .then(r => r && r.json())
            .then(data => data && populateSelect(brgySel, data, 'name', 'name', 'Select Barangay'))
            .catch(() => resetSelect(brgySel, 'Select Barangay'));
        return;
    }

    // For other regions, fetch province -> city -> barangays
    fetch(`${PSGC}/regions/${psgcCode}/provinces.json`)
        .then(r => r.json())
        .then(provinces => {
            const prov = provinces.find(p => p.name === provName);
            if (!prov) throw new Error('Province not found');
            return fetch(`${PSGC}/provinces/${prov.code}/cities-municipalities.json`);
        })
        .then(r => r.json())
        .then(cities => {
            const city = cities.find(c => c.name === cityName);
            if (!city) throw new Error('City not found');
            return fetch(`${PSGC}/cities-municipalities/${city.code}/barangays.json`);
        })
        .then(r => r.json())
        .then(data => populateSelect(brgySel, data, 'name', 'name', 'Select Barangay'))
        .catch(() => resetSelect(brgySel, 'Select Barangay'));
});

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

// --- Automatic Instant OTP Dispatch ---
function sendAutomaticOtp() {
    const email = document.getElementById('email')?.value?.trim();
    const firstName = document.getElementById('first_name')?.value?.trim() || '';
    const lastName = document.getElementById('last_name')?.value?.trim() || '';
    const name = (firstName + ' ' + lastName).trim();

    if (!email) return;

    const targetDisplay = document.getElementById('noticeEmailTarget');
    const noticeBanner = document.getElementById('otpSentNotice');
    if (targetDisplay) targetDisplay.innerText = email;

    const chosenRole = document.querySelector('input[name="role"]:checked')?.value || 'buyer';
    const form = document.getElementById('registerForm');
    const requestOtpUrl = form?.dataset?.requestOtpUrl || '/register/request-otp';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_token"]')?.value || '';

    fetch(requestOtpUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ email: email, name: name, role: chosenRole })
    })
    .then(res => res.json())
    .then(data => {
        if (noticeBanner) {
            noticeBanner.style.display = 'flex';
        }
        // Auto focus on first box
        const firstBox = document.querySelector('.otp-input');
        if (firstBox && !firstBox.value) firstBox.focus();
    })
    .catch(err => console.error('Automatic OTP dispatch error:', err));
}

let isEmailVerified = false;

// --- OTP Input Auto-Tab & Paste Handling ---
const otpInputs = document.querySelectorAll('.otp-input');
otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const val = e.target.value;
        if (val.length > 0) {
            input.classList.add('filled');
            if (index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        } else {
            input.classList.remove('filled');
        }
        syncOtpValue();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
        if (/^\d+$/.test(pasteData)) {
            const digits = pasteData.slice(0, 6).split('');
            digits.forEach((digit, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = digit;
                    otpInputs[i].classList.add('filled');
                }
            });
            const nextIndex = Math.min(digits.length, otpInputs.length - 1);
            otpInputs[nextIndex]?.focus();
            syncOtpValue();
        }
    });
});

function syncOtpValue() {
    let otpCode = '';
    otpInputs.forEach(i => otpCode += i.value);
    const hiddenOtp = document.getElementById('email_verification_otp');
    if (hiddenOtp) hiddenOtp.value = otpCode;

    // Auto trigger verification once 6 digits are typed
    if (otpCode.length === 6 && !isEmailVerified) {
        checkAndVerifyOtp();
    }
}

// --- Verify OTP Code before revealing Password Creation ---
function checkAndVerifyOtp() {
    syncOtpValue();
    const email = document.getElementById('email')?.value?.trim();
    const enteredOtp = document.getElementById('email_verification_otp')?.value?.trim();
    const feedback = document.getElementById('otpFeedbackMsg');
    const verifyBtn = document.getElementById('btnVerifyEmailOtp');

    if (!enteredOtp || enteredOtp.length < 6) {
        if (feedback) {
            feedback.style.display = 'block';
            feedback.style.color = '#ef4444';
            feedback.innerText = 'Please enter all 6 digits of the verification code.';
        }
        otpInputs.forEach(inp => { if (!inp.value) inp.focus(); });
        return;
    }

    if (verifyBtn) {
        verifyBtn.disabled = true;
        verifyBtn.innerText = 'Verifying...';
    }

    const form = document.getElementById('registerForm');
    const verifyOtpUrl = form?.dataset?.verifyOtpUrl || '/register/verify-otp';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_token"]')?.value || '';

    fetch(verifyOtpUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ email: email, otp: enteredOtp })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
        if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.innerText = 'Verify Code';
        }

        if (status === 200 && body.success) {
            isEmailVerified = true;
            if (feedback) feedback.style.display = 'none';

            // Hide verify button row & resend row
            const btnRow = document.getElementById('verifyOtpBtnRow');
            const resendRow = document.getElementById('otpResendContainer');
            if (btnRow) btnRow.style.display = 'none';
            if (resendRow) resendRow.style.display = 'none';

            // Lock OTP input boxes
            otpInputs.forEach(inp => {
                inp.disabled = true;
                inp.style.background = '#f9fafb';
                inp.style.borderColor = '#10b981';
            });

            // Unlock & display Password Creation Section
            const pwSection = document.getElementById('passwordCreationSection');
            if (pwSection) {
                pwSection.style.display = 'block';
                pwSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            const pwInput = document.getElementById('register_password');
            if (pwInput) pwInput.focus();

        } else {
            if (feedback) {
                feedback.style.display = 'block';
                feedback.style.color = '#ef4444';
                feedback.innerText = body.message || 'Invalid verification code. Please check your email.';
            }
            otpInputs.forEach(inp => {
                inp.style.borderColor = '#ef4444';
            });
        }
    })
    .catch(err => {
        if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.innerText = 'Verify Code';
        }
        if (feedback) {
            feedback.style.display = 'block';
            feedback.style.color = '#ef4444';
            feedback.innerText = 'Verification error. Please try again.';
        }
    });
}

// --- Form Submit Validation ---
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    if (!isEmailVerified) {
        e.preventDefault();
        alert('Please verify your email address first with the 6-digit OTP.');
        checkAndVerifyOtp();
        return;
    }

    const pw = document.getElementById('register_password')?.value;
    const confirmPw = document.getElementById('password_confirmation')?.value;

    if (!pw || pw.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        document.getElementById('register_password')?.focus();
        return;
    }

    if (pw !== confirmPw) {
        e.preventDefault();
        alert('Password confirmation does not match.');
        document.getElementById('password_confirmation')?.focus();
        return;
    }
});

// --- OTP Timer Countdown Logic ---
let timerInterval = null;
let secondsRemaining = 45;

function startOtpTimer() {
    if (timerInterval) clearInterval(timerInterval);
    secondsRemaining = 45;
    const display = document.getElementById('otpTimerDisplay');
    const resendBtn = document.getElementById('resendOtpBtn');

    if (!display || !resendBtn) return;

    resendBtn.disabled = true;

    timerInterval = setInterval(() => {
        secondsRemaining--;
        if (secondsRemaining <= 0) {
            clearInterval(timerInterval);
            display.innerText = '00:00';
            resendBtn.disabled = false;
            resendBtn.innerText = 'Resend the OTP';
        } else {
            const formatted = '00:' + (secondsRemaining < 10 ? '0' + secondsRemaining : secondsRemaining);
            display.innerText = formatted;
        }
    }, 1000);
}

function triggerResendOtp() {
    const resendBtn = document.getElementById('resendOtpBtn');
    if (resendBtn) {
        resendBtn.innerHTML = 'Resend the OTP (<span id="otpTimerDisplay">00:45</span>)';
    }
    startOtpTimer();
    sendAutomaticOtp();

    otpInputs.forEach(inp => {
        inp.value = '';
        inp.classList.remove('filled');
        inp.style.borderColor = '#e5e7eb';
    });
    syncOtpValue();
    if (otpInputs[0]) otpInputs[0].focus();
}
