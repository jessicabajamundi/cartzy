/**
 * Cartzy Authentication - 3-Step Registration Wizard (Cartzy Original Design)
 * Sellers see it as 6 steps and logistics partners as 7: step 01 is split into sections (updateSignupPages).
 * Step 01: Details (First name, Last name, Mobile number, Birthday)
 * Step 02: Sign-in (Email, Password, Confirm password)
 * Step 03: Verify (6-digit OTP email verification)
 */

let currentStep = 1;
const totalSteps = 3;
let isEmailVerified = false;
let timerInterval = null;
let secondsRemaining = 60;
let otpRequestPending = false;

function showRegistrationError(id, message, focus = false) {
    const input = document.getElementById(id);
    const error = document.getElementById(id + '_error');
    if (error) {
        error.textContent = message;
        error.hidden = !message;
    }
    input.setAttribute('aria-invalid', message ? 'true' : 'false');
    input.style.borderColor = message ? '#ef4444' : '';
    if (message && focus) input.focus();
    return !message;
}

function validateRegistrationPhone(focus = false) {
    const value = document.getElementById('phone').value.trim();
    let message = '';
    if (!value && ['seller', 'logistics'].includes(document.getElementById('role_input').value)) message = 'Please enter your mobile number.';
    if (value) {
        if (!/^[0-9]+$/.test(value)) {
            message = 'Please enter digits only.';
        } else if (value.length !== 10) {
            message = 'Enter exactly 10 digits after +63.';
        } else if (!value.startsWith('9')) {
            message = 'Mobile number must start with 9 after +63.';
        }
    }
    return showRegistrationError('phone', message, focus);
}

async function checkPhoneAvailability(focus = false) {
    const phoneInput = document.getElementById('phone');
    if (!phoneInput) return true;
    const value = phoneInput.value.trim();
    if (!value || value.length !== 10) return true;

    const form = document.getElementById('registerForm');
    const checkUrl = form ? form.getAttribute('data-check-phone-url') : '/register/check-phone';
    if (!checkUrl) return true;

    try {
        const token = document.querySelector('input[name="_token"]')?.value;
        const res = await fetch(checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                phone: value,
                phone_country_code: '+63',
            }),
        });
        if (res.ok) {
            const data = await res.json();
            if (!data.available) {
                showRegistrationError('phone', data.message || 'This mobile number is already in use.', focus);
                return false;
            }
        }
    } catch (e) {
        // Continue if network fails, backend will validate
    }
    return true;
}

function validateRegistrationPasswords(focus = false) {
    const password = document.getElementById('register_password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    // Sellers and logistics partners need a strong password.
    const seller = ['seller', 'logistics'].includes(document.getElementById('role_input').value);
    let message = '';
    if (password.length < (seller ? 8 : 6)) {
        message = `Password must be at least ${seller ? 8 : 6} characters long.`;
    } else if (seller) {
        if (!/\p{Lu}/u.test(password)) message = 'Password must contain at least one uppercase letter.';
        else if (!/\p{Ll}/u.test(password)) message = 'Password must contain at least one lowercase letter.';
        else if (!/\p{N}/u.test(password)) message = 'Password must contain at least one number.';
        else if (!/[\p{Z}\p{S}\p{P}]/u.test(password)) message = 'Password must contain at least one symbol.';
    }
    const validPassword = showRegistrationError('register_password', message, focus);
    const validConfirmation = showRegistrationError('password_confirmation', password === confirmation ? '' : 'The password confirmation does not match.', focus && validPassword);
    return validPassword && validConfirmation;
}

function validateRegistrationEmail(focus = false) {
    const input = document.getElementById('email');
    input.value = input.value.trim();
    if (document.getElementById('role_input').value === 'seller') input.value = input.value.toLowerCase();
    const valid = /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/.test(input.value) && input.validity.valid;
    return showRegistrationError('email', valid ? '' : 'Please enter a valid email address.', focus);
}

function validateRegistrationNames(focusInvalid = false) {
    let firstInvalid = null;
    ['first_name', 'last_name'].forEach(id => {
        const input = document.getElementById(id);
        const error = document.getElementById(id + '_error');
        if (!input) return;
        const value = input.value.trim();
        let message = '';
        if (!value) {
            if (focusInvalid) {
                message = id === 'first_name' ? 'Please enter your first name.' : 'Please enter your last name.';
            }
        } else if (Array.from(value).length > 50) {
            message = `${id === 'first_name' ? 'First' : 'Last'} name must not exceed 50 characters.`;
        } else if (/[^\p{L}\p{M} ]/u.test(value)) {
            message = 'Special characters and numbers are not allowed.';
        }
        if (error) {
            error.textContent = message;
            error.hidden = !message;
        }
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        input.style.borderColor = message ? '#ef4444' : '';
        if (message && !firstInvalid) firstInvalid = input;
    });
    if (focusInvalid && firstInvalid) firstInvalid.focus();
    return !firstInvalid;
}

function validateRegistrationBirthday(focus = false) {
    if (['logistics', 'seller'].includes(document.getElementById('role_input')?.value)) return true;
    const input = document.getElementById('birthday');
    if (!input) return true;
    let message = '';
    if (!input.value) {
        message = 'Please enter your birthday.';
    } else if (!/^\d{4}-\d{2}-\d{2}$/.test(input.value) || input.validity.badInput) {
        message = 'Please enter a valid birthday.';
    } else if (input.value > input.max) {
        input.value = input.max;
        autoCalcAge(input.value);
    }
    return showRegistrationError('birthday', message, focus);
}

// Age auto-calculate from birthday with auto-clamp and live age display
function autoCalcAge(dateStr) {
    const input = document.getElementById('birthday');
    const ageEl = document.getElementById('age_display');
    const ageText = document.getElementById('birthday_age_text');

    if (!dateStr || !input) {
        if (ageEl) ageEl.value = '';
        if (ageText) ageText.textContent = '';
        return;
    }

    // Auto-clamp if date exceeds max allowed (e.g. 2026 or future dates)
    if (input.max && dateStr > input.max) {
        dateStr = input.max;
        input.value = dateStr;
    }

    const today = new Date();
    const bday = new Date(dateStr + 'T00:00:00');
    if (isNaN(bday.getTime())) {
        if (ageEl) ageEl.value = '';
        if (ageText) ageText.textContent = '';
        return;
    }

    let age = today.getFullYear() - bday.getFullYear();
    const m = today.getMonth() - bday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;

    if (ageEl) ageEl.value = age >= 0 ? age : '';
    if (ageText) {
        if (age >= 18) {
            ageText.textContent = `Age: ${age} years old`;
            ageText.style.color = '#059669';
        } else if (age >= 0) {
            ageText.textContent = `Age: ${age} (Must be 18+)`;
            ageText.style.color = '#ef4444';
        } else {
            ageText.textContent = '';
        }
    }
}

// Update Cartzy Stepper UI
function updateStepperUI() {
    for (let i = 1; i <= totalSteps; i++) {
        const labelEl = document.getElementById('label-step-' + i);
        const panelEl = document.getElementById('step-' + i);

        if (labelEl) {
            if (i <= currentStep) {
                labelEl.classList.remove('inactive');
            } else {
                labelEl.classList.add('inactive');
            }
        }

        if (panelEl) {
            panelEl.style.display = (i === currentStep) ? 'block' : 'none';
        }
    }
    if (currentStep === 3) {
        const applicant = document.getElementById('verificationApplicant');
        const name = [document.getElementById('first_name').value.trim(), document.getElementById('last_name').value.trim()].filter(Boolean).join(' ');
        const role = document.getElementById('role_input').value;
        if (applicant) applicant.textContent = `${name} — ${role === 'logistics' ? 'logistics partner' : role} account. Enter the 6-digit code sent to ${document.getElementById('email').value.trim()}.`;
        updateOtpPreviewUI();
    }

    updateSignupPages();

    const line1 = document.getElementById('line-1');
    const line2 = document.getElementById('line-2');
    if (line1) {
        if (currentStep >= 2) {
            line1.classList.remove('inactive');
        } else {
            line1.classList.add('inactive');
        }
    }
    if (line2) {
        if (currentStep >= 3) {
            line2.classList.remove('inactive');
        } else {
            line2.classList.add('inactive');
        }
    }
}

// Next Step transition
async function nextStep(targetStep) {
    if (otpRequestPending) return;
    // Sellers and logistics partners go through the details one section at a time before the sign-in step.
    const stepped = steppedSignup();
    if (currentStep === 1 && stepped && detailPage < stepped.detailPages) {
        // Ignore extra clicks while this step is still being checked (the phone check waits on the server).
        if (detailPageChecking) return;
        detailPageChecking = true;
        try {
            if (!await validateDetailPage(detailPage)) return;
        } finally {
            detailPageChecking = false;
        }
        detailPage = Math.min(detailPage + 1, stepped.detailPages);
        updateStepperUI();
        scrollFormToTop();
        return;
    }
    // Validate Step 1
    if (currentStep === 1) {
        if (!validateSellerDetails(true)) return;
        if (!validateLogisticsDetails(true)) return;
        if (!validateRegistrationBirthday(true)) return;
        if (!validateRegistrationNames(true)) return;
        if (!validateRegistrationPhone(true)) return;
        if (!await checkPhoneAvailability(true)) return;
        const firstNameEl = document.getElementById('first_name');
        const lastNameEl = document.getElementById('last_name');
        const firstName = firstNameEl ? firstNameEl.value.trim() : '';
        const lastName = lastNameEl ? lastNameEl.value.trim() : '';

        if (!firstName) {
            firstNameEl.focus();
            firstNameEl.style.borderColor = '#ef4444';
            return;
        }
        firstNameEl.style.borderColor = '';

        if (!lastName) {
            lastNameEl.focus();
            lastNameEl.style.borderColor = '#ef4444';
            return;
        }
        lastNameEl.style.borderColor = '';

        // Combine full name
        const nameField = document.getElementById('name');
        if (nameField) {
            nameField.value = `${firstName} ${lastName}`.trim();
        }
    }

    // Validate Step 2
    if (currentStep === 2) {
        // Email check
        if (!validateRegistrationEmail(true)) return;

        if (!validateRegistrationPasswords(true)) return;

        if (!validateLogisticsTerms(true)) return;

        // Send OTP automatically when transitioning to Step 3
        if (!await sendAutomaticOtp()) return;
        startOtpTimer();
    }

    currentStep = targetStep;
    updateStepperUI();
    scrollFormToTop();

    if (currentStep === 3) {
        setTimeout(() => {
            const firstOtp = document.querySelector('.otp-input[data-index="0"]');
            if (firstOtp) firstOtp.focus();
        }, 150);
    }
}

// In the sign-up modal the dialog panel scrolls, not the page.
function scrollFormToTop() {
    const panel = document.getElementById('registerForm')?.closest('.lm-panel');
    (panel || window).scrollTo({ top: 0, behavior: 'smooth' });
}

// Previous Step transition
function prevStep(targetStep) {
    currentStep = targetStep;
    updateStepperUI();
    scrollFormToTop();
}

// ── Sellers sign up in 6 steps, logistics partners in 7 ──
// The first steps are sections of the details panel (step-1, marked data-signup-page),
// then sign-in (step-2) and email verification (step-3).
const STEPPED_SIGNUPS = {
    seller: {
        titles: ['Your details', 'Your shop', 'Pickup address', 'Documents', 'Sign-in details', 'Verify your email'],
        detailPages: 4,
        wrapper: 'sellerFieldsWrapper',
        notice: 'sellerNoticeBox',
    },
    logistics: {
        titles: ['Your details', 'Your company', 'Service areas', 'Office address', 'Business documents', 'Sign-in details', 'Verify your email'],
        detailPages: 5,
        wrapper: 'logisticsFieldsWrapper',
        notice: 'logisticsNoticeBox',
    },
};
let detailPage = 1;
let detailPageChecking = false;

function steppedSignup() {
    return STEPPED_SIGNUPS[document.getElementById('role_input')?.value] || null;
}

async function validateDetailPage(page) {
    if (page === 1) {
        return validateRegistrationNames(true) && validateRegistrationPhone(true) && await checkPhoneAvailability(true);
    }
    return validateSellerDetails(true, page) && validateLogisticsDetails(true, page);
}

function sellerBack() {
    if (detailPage <= 1) return;
    detailPage--;
    updateStepperUI();
    scrollFormToTop();
}
window.sellerBack = sellerBack;

// Opens the sign-up step that holds a field, so a field on an earlier step can be shown and focused.
function showDetailPageOf(input) {
    const page = input?.closest('[data-signup-page]');
    if (!page || !steppedSignup()) return;
    currentStep = 1;
    detailPage = Number(page.dataset.signupPage);
    updateStepperUI();
}

// Show only the current section, and swap the 3-part stepper for the role's step progress.
function updateSignupPages() {
    const stepped = steppedSignup();
    const role = document.getElementById('role_input')?.value;
    const onDetails = currentStep === 1;
    const step = stepped ? (onDetails ? detailPage : stepped.detailPages + currentStep - 1) : 1;
    document.getElementById('registerForm')?.classList.toggle('is-signup-stepped', !!stepped);
    // Lets short screens drop the logo and subtitle after the first step (auth-modal.css).
    document.querySelector('.lm-dialog-form')?.classList.toggle('is-signup-later-step', !!stepped && step > 1);
    document.querySelectorAll('[data-signup-page]').forEach(section => {
        section.hidden = !!stepped && Number(section.dataset.signupPage) !== detailPage;
    });

    const stepper = document.getElementById('signupStepper');
    if (stepper) stepper.style.display = stepped ? 'none' : '';

    // Sellers: "Step X of 6" with one bar.
    const sellerProgress = document.getElementById('sellerProgress');
    if (sellerProgress) {
        sellerProgress.hidden = role !== 'seller';
        if (role === 'seller') {
            sellerProgress.querySelector('[data-sp-number]').textContent = step;
            sellerProgress.querySelector('[data-sp-title]').textContent = stepped.titles[step - 1];
            sellerProgress.querySelector('[data-sp-fill]').style.width = (step / stepped.titles.length * 100) + '%';
            sellerProgress.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', step);
        }
    }

    // Logistics partners: "STEP X OF 7", the step title and one bar segment per step.
    const logisticsProgress = document.getElementById('logisticsProgress');
    if (logisticsProgress) {
        logisticsProgress.hidden = role !== 'logistics';
        if (role === 'logistics') {
            logisticsProgress.querySelector('[data-lp-number]').textContent = step;
            logisticsProgress.querySelector('[data-lp-title]').textContent = stepped.titles[step - 1];
            logisticsProgress.querySelectorAll('[data-lp-segment]').forEach((segment, index) => {
                segment.classList.toggle('is-done', index + 1 < step);
                segment.classList.toggle('is-current', index + 1 === step);
            });
            logisticsProgress.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', step);
        }
    }

    // Page 1 keeps the full-width Continue; later detail pages get Back and Continue.
    const single = document.querySelector('.btn-details-continue');
    const nav = document.querySelector('.su-seller-nav');
    const label = stepped && detailPage === stepped.detailPages ? 'Continue to sign-in details' : 'Continue';
    if (single) {
        single.hidden = !!stepped && detailPage > 1;
        single.textContent = label;
    }
    if (nav) {
        nav.hidden = !(stepped && detailPage > 1);
        const next = nav.querySelector('.su-seller-continue');
        if (next) next.textContent = label;
    }

    // The approval notice only on the first step, to keep the later steps short.
    Object.entries(STEPPED_SIGNUPS).forEach(([name, config]) => {
        const notice = document.getElementById(config.notice);
        if (notice) notice.style.display = role === name && step === 1 ? 'block' : 'none';
    });
}

// Toggle password visibility and the eye icon.
function togglePasswordVisibility(inputId, btnEl) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    if (btnEl) {
        btnEl.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        btnEl.setAttribute('aria-pressed', String(visible));
        const slash = btnEl.querySelector('.eye-slash');
        if (slash) slash.style.display = visible ? '' : 'none';
    }
}

let latestOtpPreview = null;
let latestMailSent = true;

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
                devNotice.innerText = '✓ Verification email sent via Gmail. (Preview displayed for local testing)';
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

// Send Automatic OTP via AJAX
async function sendAutomaticOtp() {
    if (otpRequestPending || !validateRegistrationEmail(true)) return false;
    const email = document.getElementById('email')?.value?.trim();
    const name = document.getElementById('name')?.value?.trim();
    const form = document.getElementById('registerForm');
    const requestOtpUrl = form?.dataset?.requestOtpUrl || '/register/request-otp';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_token"]')?.value || '';

    if (!email) return;

    otpRequestPending = true;
    const sendButton = document.getElementById('sendVerificationCodeButton');
    const sendLabel = document.getElementById('sendVerificationCodeLabel');
    if (sendButton) sendButton.disabled = true;
    if (sendLabel) sendLabel.textContent = 'Sending code...';
    try {
        const response = await fetch(requestOtpUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ email: email, name: name, role: document.getElementById('role_input').value, phone: document.getElementById('phone').value.trim(), phone_country_code: '+63' })
        });
        if (response.status === 419) {
            throw new Error('Your session has expired. Refresh this page and try again.');
        }
        if (response.status === 429) {
            throw new Error('Too many requests. Please wait a minute before requesting another code.');
        }
        if (!response.headers.get('content-type')?.includes('application/json')) {
            throw new Error('The verification service is temporarily unavailable. Please try again shortly.');
        }
        const data = await response.json();
        if (!response.ok || !data.success) {
            const field = data.errors?.phone ? 'phone' : 'email';
            currentStep = field === 'phone' ? 1 : 2;
            // The mobile number is on the first step for sellers and logistics partners.
            if (field === 'phone') detailPage = 1;
            updateStepperUI();
            showRegistrationError(field, data.errors?.[field]?.[0] || data.message || 'Unable to send verification code. Please try again.', true);
            return false;
        }
        latestOtpPreview = data.otp_preview || null;
        latestMailSent = data.mail_sent !== false;
        updateOtpPreviewUI();
        return true;
    } catch (error) {
        currentStep = 2;
        updateStepperUI();
        showRegistrationError('email', error instanceof TypeError
            ? 'Cannot connect to the verification service. Check your connection and try again.'
            : error.message || 'Unable to send verification code. Please try again.', true);
        return false;
    } finally {
        otpRequestPending = false;
        if (sendButton) sendButton.disabled = false;
        if (sendLabel) sendLabel.textContent = 'Send verification code';
    }
}

// Start Resend OTP Timer
function startOtpTimer() {
    if (timerInterval) clearInterval(timerInterval);
    secondsRemaining = 60;
    const timerDisplay = document.getElementById('otpTimerDisplay');
    const resendBtn = document.getElementById('resendOtpBtn');

    if (resendBtn) {
        resendBtn.disabled = true;
    }
    if (timerDisplay) {
        timerDisplay.innerText = '00:60';
    }

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

// Trigger Resend OTP
async function triggerResendOtp() {
    const resendBtn = document.getElementById('resendOtpBtn');
    if (resendBtn && resendBtn.disabled) return;

    if (!await sendAutomaticOtp()) return;
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

// Role Selection function (Buyer, Seller, or Logistics)
function selectRole(role) {
    const birthdayInput = document.getElementById('birthday');
    if (birthdayInput) {
        birthdayInput.disabled = ['seller', 'logistics'].includes(role);
        birthdayInput.required = !birthdayInput.disabled;
        if (birthdayInput.disabled) showRegistrationError('birthday', '');
    }
    const roleInput = document.getElementById('role_input');
    const buyerOpt = document.getElementById('roleOptionBuyer');
    const sellerOpt = document.getElementById('roleOptionSeller');
    const standardRoleWrapper = document.getElementById('standardRoleWrapper');
    const birthdayFieldWrapper = document.getElementById('birthdayFieldWrapper');
    const logisticsFieldsWrapper = document.getElementById('logisticsFieldsWrapper');
    const logisticsNoticeBox = document.getElementById('logisticsNoticeBox');
    const googleSignupWrapper = document.getElementById('googleSignupWrapper');
    const googleSignupDivider = document.getElementById('googleSignupDivider');
    const authTitle = document.getElementById('authTitle');
    const authSubtitle = document.getElementById('authSubtitle');

    const linkLogisticsApply = document.getElementById('linkLogisticsApply');
    const linkLogisticsSeller = document.getElementById('linkLogisticsSeller');
    const logisticsSellerDivider = document.getElementById('logisticsSellerDivider');
    if (linkLogisticsSeller) linkLogisticsSeller.style.display = role === 'logistics' ? 'inline' : 'none';
    if (logisticsSellerDivider) logisticsSellerDivider.style.display = role === 'logistics' ? 'inline' : 'none';
    if (linkLogisticsApply) {
        linkLogisticsApply.href = linkLogisticsApply.href.split('?')[0] + '?role=' + (role === 'logistics' ? 'buyer' : 'logistics');
    }

    if (roleInput) {
        roleInput.value = role;
    }

    const isBuyer = role === 'buyer';
    if (googleSignupWrapper) googleSignupWrapper.style.display = isBuyer ? 'block' : 'none';
    if (googleSignupDivider) googleSignupDivider.style.display = isBuyer ? 'flex' : 'none';

    if (role === 'logistics') {
        if (standardRoleWrapper) standardRoleWrapper.style.display = 'none';
        if (birthdayFieldWrapper) birthdayFieldWrapper.style.display = 'none';
        if (logisticsFieldsWrapper) logisticsFieldsWrapper.style.display = 'block';
        if (logisticsNoticeBox) logisticsNoticeBox.style.display = 'block';
        if (authTitle) authTitle.innerText = 'Become a Logistics Partner';
        if (authSubtitle) authSubtitle.innerText = 'Register your delivery company and manage your own riders on CARTZY.';

        if (linkLogisticsApply) {
            linkLogisticsApply.innerText = 'Create new account';
            linkLogisticsApply.setAttribute('onclick', "selectRole('buyer'); return false;");
        }
    } else {
        if (standardRoleWrapper) standardRoleWrapper.style.display = 'block';
        if (birthdayFieldWrapper) birthdayFieldWrapper.style.display = role === 'seller' ? 'none' : 'block';
        if (logisticsFieldsWrapper) logisticsFieldsWrapper.style.display = 'none';
        if (logisticsNoticeBox) logisticsNoticeBox.style.display = 'none';
        if (authTitle) authTitle.innerText = 'Create New Account';
        if (authSubtitle) authSubtitle.innerText = 'Join cartzy and discover your everyday favorites';

        if (linkLogisticsApply) {
            linkLogisticsApply.innerText = 'Become a logistics partner';
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
    detailPage = 1;
    updateLogisticsDetails(role);
    updateSellerDetails(role);
}
window.selectRole = selectRole;

// Logistics fields are required, and submitted, only on the logistics sign-up.
function updateLogisticsDetails(role = document.getElementById('role_input').value) {
    const logistics = role === 'logistics';
    const wrapper = document.getElementById('logisticsFieldsWrapper');
    if (wrapper) {
        wrapper.querySelectorAll('[data-logistics-required], [data-logistics-document], [data-service-region]').forEach(input => {
            if (!input.hasAttribute('data-service-region')) input.required = logistics;
            // The location lists are enabled one by one by address-modal.js as each level loads.
            const psgcManaged = input.tagName === 'SELECT' && /^office_(region|province|city|barangay)$/.test(input.id);
            if (!psgcManaged) input.disabled = !logistics;
        });
    }
    const termsWrapper = document.getElementById('logisticsTermsWrapper');
    if (termsWrapper) {
        termsWrapper.style.display = logistics ? 'block' : 'none';
        const termsCheckbox = document.getElementById('logistics_terms_agreed');
        if (termsCheckbox) termsCheckbox.required = false;
        if (!logistics) {
            const error = document.getElementById('logistics_terms_error');
            if (error) error.hidden = true;
        }
    }
}

// Step 6 (for logistics): user must agree to Terms & Conditions and Privacy Policy to proceed.
function validateLogisticsTerms(focus = false) {
    const role = document.getElementById('role_input')?.value;
    if (role !== 'logistics') return true;

    const checkbox = document.getElementById('logistics_terms_agreed');
    const modalCheckbox = document.getElementById('modal_logistics_terms_agreed');
    const error = document.getElementById('logistics_terms_error');
    const openBtn = document.getElementById('openLogisticsTermsBtn');
    const badge = document.getElementById('logisticsTermsAgreedBadge');
    if (!checkbox) return true;

    if (modalCheckbox && modalCheckbox.checked !== checkbox.checked) {
        modalCheckbox.checked = checkbox.checked;
    }

    if (badge) {
        badge.style.display = checkbox.checked ? 'inline-flex' : 'none';
    }

    if (!checkbox.checked) {
        if (error) {
            error.textContent = 'You must open and agree to the Cartzy Logistics Terms & Conditions and Privacy Policy to proceed.';
            error.hidden = false;
        }
        if (openBtn) {
            openBtn.setAttribute('aria-invalid', 'true');
            if (focus) {
                openBtn.focus();
                openBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        return false;
    }

    if (error) {
        error.textContent = '';
        error.hidden = true;
    }
    if (openBtn) {
        openBtn.removeAttribute('aria-invalid');
    }
    return true;
}

function setupLogisticsTermsModal() {
    const dialog = document.getElementById('logisticsTermsDialog');
    const openBtn = document.getElementById('openLogisticsTermsBtn');
    const closeXBtn = document.getElementById('closeLogisticsTermsXBtn');
    const agreeBtn = document.getElementById('btnAgreeTermsModal');
    const formCheckbox = document.getElementById('logistics_terms_agreed');
    const modalCheckbox = document.getElementById('modal_logistics_terms_agreed');
    const modalBody = document.getElementById('logisticsTermsModalBody') || dialog?.querySelector('.logistics-terms-modal-body');

    if (!dialog) return;

    const outside = event => {
        const rect = dialog.getBoundingClientRect();
        return event.clientX < rect.left || event.clientX > rect.right
            || event.clientY < rect.top || event.clientY > rect.bottom;
    };
    let startedOutside = false;
    dialog.addEventListener('pointerdown', event => {
        startedOutside = event.target === dialog && outside(event);
    });
    dialog.addEventListener('click', event => {
        if (startedOutside && event.target === dialog && outside(event)) {
            closeModal();
        }
    });

    function openModal() {
        if (formCheckbox && modalCheckbox) {
            modalCheckbox.checked = formCheckbox.checked;
        }
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
        }

        if (modalBody && !formCheckbox?.checked) {
            modalBody.scrollTop = 0;
        }
    }

    function closeModal() {
        if (typeof dialog.close === 'function') {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }
    }

    openBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
    });

    closeXBtn?.addEventListener('click', closeModal);

    modalCheckbox?.addEventListener('change', () => {
        if (formCheckbox) {
            formCheckbox.checked = modalCheckbox.checked;
            validateLogisticsTerms();
        }
    });

    formCheckbox?.addEventListener('change', () => {
        if (modalCheckbox) {
            modalCheckbox.checked = formCheckbox.checked;
        }
        validateLogisticsTerms();
    });

    agreeBtn?.addEventListener('click', () => {
        if (formCheckbox) formCheckbox.checked = true;
        if (modalCheckbox) modalCheckbox.checked = true;
        validateLogisticsTerms();
        closeModal();
    });
}

// Error and focus for a sign-up detail field (text, select or file).
function detailFieldMessage(input) {
    input.setCustomValidity('');
    if (input.type === 'text') input.value = input.value.trim();
    if (input.tagName === 'SELECT' && input.disabled) return 'Choose the preceding location first, or retry loading locations.';
    if (input.required && !input.value) return 'Please complete this field.';
    if (input.type === 'file' && input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) return 'File must not exceed 5 MB.';
        if (!/\.(jpe?g|png|pdf)$/i.test(file.name)) return 'Choose a JPG, PNG or PDF file.';
    }
    if (input.id === 'sec_registration_no' && input.value) {
        if (!/^[A-Za-z]{2}[0-9]{13}$/.test(input.value)) {
            return 'Enter the SEC registration number as shown on your certificate (2 letters followed by 13 numbers, e.g. CS2020123456789).';
        }
    }
    return input.checkValidity() ? '' : input.validationMessage;
}

function focusDetailField(input) {
    showDetailPageOf(input);
    input.focus();
}

function showServiceRegionError(message) {
    const error = document.getElementById('service_regions_error');
    if (error) error.textContent = message;
    document.querySelector('#logisticsFieldsWrapper .lg-regions')?.setAttribute('aria-invalid', message ? 'true' : 'false');
}

// Checks the logistics fields; with a page (2-5), only the fields of that sign-up step.
function validateLogisticsDetails(focus = false, page = null) {
    if (document.getElementById('role_input').value !== 'logistics') return true;
    let firstInvalid = null;
    const scope = page ? `#logisticsFieldsWrapper [data-signup-page="${page}"]` : '#logisticsFieldsWrapper';
    document.querySelectorAll(`${scope} [data-logistics-required], ${scope} [data-logistics-document], ${scope} .lg-regions`).forEach(input => {
        if (input.classList.contains('lg-regions')) {
            const boxes = input.querySelectorAll('[data-service-region]');
            const chosen = Array.from(boxes).some(box => box.checked);
            showServiceRegionError(chosen ? '' : 'Choose at least one region you deliver to.');
            if (!chosen && !firstInvalid) firstInvalid = boxes[0];
            return;
        }
        const message = detailFieldMessage(input);
        showRegistrationError(input.id, message);
        if (message && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid && focus) focusDetailField(firstInvalid);
    return !firstInvalid;
}

function updateSellerDetails(role = document.getElementById('role_input').value) {
    const seller = role === 'seller';
    const wrapper = document.getElementById('sellerFieldsWrapper');
    if (!wrapper) return;
    wrapper.style.display = seller ? 'block' : 'none';
    document.getElementById('sellerNoticeBox').style.display = seller ? 'block' : 'none';
    document.getElementById('phone').required = seller || role === 'logistics';
    document.querySelector('.btn-details-continue').textContent = seller ? 'Continue to sign-in details' : 'Continue';
    wrapper.querySelectorAll('[data-seller-required]').forEach(input => {
        input.required = seller;
        if (input.tagName !== 'SELECT' || !input.classList.contains('psgc-' + input.id)) input.disabled = !seller;
    });
    const type = document.getElementById('seller_type').value;
    wrapper.querySelectorAll('[data-document-types]').forEach(block => {
        const applies = block.dataset.documentTypes === 'all' || block.dataset.documentTypes === type
            || (block.dataset.documentTypes === 'business' && ['sole_proprietorship', 'corporation'].includes(type));
        block.hidden = false;
        block.querySelector('.seller-required').hidden = !seller || !applies;
        const input = block.querySelector('input');
        input.required = seller && applies;
        input.disabled = !seller;
    });
    updateSignupPages();
}

// Checks the seller fields; with a page (2-4), only the fields of that sign-up step.
function validateSellerDetails(focus = false, page = null) {
    if (document.getElementById('role_input').value !== 'seller') return true;
    let firstInvalid = null;
    const scope = page ? `#sellerFieldsWrapper [data-signup-page="${page}"]` : '#sellerFieldsWrapper';
    document.querySelectorAll(`${scope} [data-seller-required], ${scope} [data-seller-document]`).forEach(input => {
        if (input.hasAttribute('data-seller-document') && input.disabled) return;
        const message = detailFieldMessage(input);
        showRegistrationError(input.id, message);
        if (message && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid && focus) focusDetailField(firstInvalid);
    return !firstInvalid;
}

// 6-Box OTP Input Handling (Paste, Auto-focus, Backspace)
function formatRegistrationTin(value) {
    return value.replace(/\D/g, '').slice(0, 12).replace(/(\d{3})(?=\d)/g, '$1-');
}

// Logistics SEC number: 2 letters followed by up to 13 digits (max 15 characters).
function formatRegistrationSec(value) {
    if (!value) return '';
    const clean = value.replace(/[^A-Za-z0-9]/g, '');
    let letters = '';
    let digits = '';
    for (let i = 0; i < clean.length && letters.length < 2; i++) {
        if (/[A-Za-z]/.test(clean[i])) {
            letters += clean[i].toUpperCase();
        } else {
            break;
        }
    }
    if (letters.length < 2) return letters;
    let letterCount = 0;
    for (let i = 0; i < clean.length && digits.length < 13; i++) {
        if (/[A-Za-z]/.test(clean[i]) && letterCount < 2) {
            letterCount++;
            continue;
        }
        if (/[0-9]/.test(clean[i])) {
            digits += clean[i];
        }
    }
    return letters + digits;
}

document.addEventListener('DOMContentLoaded', function() {
    // The seller TIN and the logistics TIN format the same way (000-000-000-000).
    document.querySelectorAll('#tin_number, [data-tin-input]').forEach(tinInput => {
        const formatTin = () => {
            const digitsBeforeCaret = tinInput.value.slice(0, tinInput.selectionStart ?? tinInput.value.length).replace(/\D/g, '').length;
            tinInput.value = formatRegistrationTin(tinInput.value);
            const caret = Math.min(tinInput.value.length, digitsBeforeCaret + Math.floor(Math.max(0, digitsBeforeCaret - 1) / 3));
            tinInput.setSelectionRange(caret, caret);
            showRegistrationError(tinInput.id, '');
        };
        tinInput.value = formatRegistrationTin(tinInput.value);
        tinInput.addEventListener('input', formatTin);
        tinInput.addEventListener('beforeinput', event => {
            const start = tinInput.selectionStart;
            const end = tinInput.selectionEnd;
            if (start !== end) return;
            // Include the neighboring digit when deleting across a separator.
            if (event.inputType === 'deleteContentBackward' && tinInput.value[start - 1] === '-') {
                tinInput.setSelectionRange(Math.max(0, start - 2), start);
            } else if (event.inputType === 'deleteContentForward' && tinInput.value[start] === '-') {
                tinInput.setSelectionRange(start, start + 2);
            }
        });
        tinInput.addEventListener('paste', event => {
            const pasted = event.clipboardData?.getData('text');
            if (pasted === undefined) return;
            event.preventDefault();
            tinInput.setRangeText(pasted.replace(/\D/g, ''), tinInput.selectionStart, tinInput.selectionEnd, 'end');
            formatTin();
        });
    });
    document.getElementById('seller_type')?.addEventListener('change', () => updateSellerDetails());
    // Logistics: SEC registration number (strictly 2 letters followed by 13 numbers, max 15 chars).
    const secInput = document.getElementById('sec_registration_no');
    if (secInput) {
        if (secInput.value) secInput.value = formatRegistrationSec(secInput.value);

        secInput.addEventListener('beforeinput', event => {
            if (!event.data || !event.inputType.startsWith('insert')) return;

            const start = secInput.selectionStart ?? 0;
            const end = secInput.selectionEnd ?? 0;
            const value = secInput.value;

            // Single-character insertion
            if (event.data.length === 1) {
                const char = event.data;
                if (start < 2) {
                    // First 2 positions can only take letters
                    if (!/[A-Za-z]/.test(char)) {
                        event.preventDefault();
                        return;
                    }
                    // Cannot insert a 3rd letter if 2 letters already exist and none is selected to replace
                    if (start === end && /^[A-Za-z]{2}/.test(value)) {
                        event.preventDefault();
                        return;
                    }
                } else {
                    // Position 2+ can only take numbers
                    if (!/[0-9]/.test(char)) {
                        event.preventDefault();
                        return;
                    }
                    // Cannot exceed 13 numbers
                    const existingDigits = (value.slice(2).match(/[0-9]/g) || []).length;
                    if (start === end && existingDigits >= 13) {
                        event.preventDefault();
                        return;
                    }
                }
            }

            // Boundary and length check on proposed outcome
            if (value.length >= 15 && start === end) {
                event.preventDefault();
                return;
            }
            const proposed = value.slice(0, start) + event.data + value.slice(end);
            const formatted = formatRegistrationSec(proposed);
            if (formatted === value && start === end) {
                event.preventDefault();
                return;
            }
        });

        secInput.addEventListener('paste', event => {
            const pasted = event.clipboardData?.getData('text');
            if (pasted === undefined) return;
            event.preventDefault();
            const start = secInput.selectionStart ?? 0;
            const end = secInput.selectionEnd ?? 0;
            const combined = secInput.value.slice(0, start) + pasted + secInput.value.slice(end);
            const formatted = formatRegistrationSec(combined);
            secInput.value = formatted;
            const newCaret = Math.min(formatted.length, start + pasted.replace(/[^A-Za-z0-9]/g, '').length);
            secInput.setSelectionRange(newCaret, newCaret);
            showRegistrationError('sec_registration_no', '');
        });

        secInput.addEventListener('input', () => {
            const caret = secInput.selectionStart;
            const prevVal = secInput.value;
            const formatted = formatRegistrationSec(prevVal);
            if (prevVal !== formatted) {
                secInput.value = formatted;
                if (caret !== null) {
                    const diff = prevVal.length - formatted.length;
                    const newCaret = Math.max(0, Math.min(formatted.length, caret - (diff > 0 ? diff : 0)));
                    secInput.setSelectionRange(newCaret, newCaret);
                }
            }
            showRegistrationError('sec_registration_no', '');
        });
    }
    document.querySelectorAll('#logisticsFieldsWrapper [data-logistics-required]').forEach(input => {
        input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', () => showRegistrationError(input.id, ''));
    });
    document.querySelectorAll('#logisticsFieldsWrapper [data-service-region]').forEach(box => {
        box.addEventListener('change', () => showServiceRegionError(''));
    });
    document.querySelectorAll('#sellerFieldsWrapper [data-seller-document], #logisticsFieldsWrapper [data-logistics-document]').forEach(input => {
        input.addEventListener('change', () => {
            let msg = '';
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) msg = 'File must not exceed 5 MB.';
                else if (!/\.(jpe?g|png|pdf)$/i.test(file.name)) msg = 'Choose a JPG, PNG or PDF file.';
            } else if (input.required) {
                msg = 'Please complete this field.';
            }
            showRegistrationError(input.id, msg);
        });
    });
    ['register_password', 'password_confirmation'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => validateRegistrationPasswords());
    });
    setupLogisticsTermsModal();
    const phoneInput = document.getElementById('phone');
    phoneInput?.addEventListener('beforeinput', event => {
        if (event.inputType === 'insertText' && event.data && /[^0-9]/.test(event.data)) {
            event.preventDefault();
        }
        if (event.inputType.startsWith('insert') && phoneInput.value.length >= 10
            && phoneInput.selectionStart === phoneInput.selectionEnd) {
            event.preventDefault();
        }
    });
    phoneInput?.addEventListener('input', () => {
        const start = phoneInput.selectionStart;
        const original = phoneInput.value;
        phoneInput.value = original.replace(/[^0-9]/g, '').slice(0, 10);
        if (start !== null && original !== phoneInput.value) {
            const position = original.slice(0, start).replace(/[^0-9]/g, '').length;
            phoneInput.setSelectionRange(position, position);
        }
        validateRegistrationPhone();
    });
    phoneInput?.addEventListener('paste', event => {
        const pasted = event.clipboardData?.getData('text');
        if (pasted === undefined) return;
        event.preventDefault();
        const normalized = pasted.replace(/[^0-9]/g, '').replace(/^(?:63|0)(?=9)/, '');
        const available = 10 - (phoneInput.value.length - (phoneInput.selectionEnd - phoneInput.selectionStart));
        const digits = normalized.slice(0, Math.max(0, available));
        phoneInput.setRangeText(digits, phoneInput.selectionStart, phoneInput.selectionEnd, 'end');
        phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
    });
    phoneInput?.addEventListener('blur', async () => {
        if (validateRegistrationPhone()) {
            await checkPhoneAvailability();
        }
    });
    document.getElementById('email')?.addEventListener('input', () => showRegistrationError('email', ''));
    document.getElementById('email')?.addEventListener('blur', () => validateRegistrationEmail());
    ['first_name', 'last_name'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        let warningTimer = null;
        const flashDisallowedWarning = () => {
            showRegistrationError(id, 'Special characters and numbers are not allowed.');
            if (warningTimer) clearTimeout(warningTimer);
            warningTimer = setTimeout(() => {
                const currentVal = input.value.trim();
                if (!currentVal || /^(?:\p{L}\p{M}*| )+$/u.test(currentVal)) {
                    showRegistrationError(id, '');
                }
            }, 2500);
        };

        // Prevent typing disallowed characters (numbers and special symbols)
        input.addEventListener('beforeinput', event => {
            if (!event.isComposing && event.inputType === 'insertText' && event.data && /[^\p{L}\p{M} ]/u.test(event.data)) {
                event.preventDefault();
                flashDisallowedWarning();
            }
        });

        // Strip any pasted or autofilled numbers / special characters so they never stay in the box
        input.addEventListener('input', event => {
            if (event.isComposing) return;
            const original = input.value;
            if (/[^\p{L}\p{M} ]/u.test(original)) {
                const caret = input.selectionStart;
                input.value = original.replace(/[^\p{L}\p{M} ]/gu, '');
                if (caret !== null) {
                    const newPos = original.slice(0, caret).replace(/[^\p{L}\p{M} ]/gu, '').length;
                    input.setSelectionRange(newPos, newPos);
                }
                flashDisallowedWarning();
            } else {
                if (warningTimer) {
                    clearTimeout(warningTimer);
                    warningTimer = null;
                }
                showRegistrationError(id, '');
            }
        });

        input.addEventListener('compositionend', () => {
            const original = input.value;
            if (/[^\p{L}\p{M} ]/u.test(original)) {
                input.value = original.replace(/[^\p{L}\p{M} ]/gu, '');
                flashDisallowedWarning();
            }
        });

        input.addEventListener('blur', () => {
            const val = input.value.trim();
            if (!val) {
                const msg = id === 'first_name' ? 'Please enter your first name.' : 'Please enter your last name.';
                showRegistrationError(id, msg);
            } else if (Array.from(val).length > 50) {
                showRegistrationError(id, `${id === 'first_name' ? 'First' : 'Last'} name must not exceed 50 characters.`);
            } else if (/[^\p{L}\p{M} ]/u.test(val)) {
                showRegistrationError(id, 'Special characters and numbers are not allowed.');
            } else {
                showRegistrationError(id, '');
            }
        });
    });
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

    // Form Submit Check
    const form = document.getElementById('registerForm');
    if (form) {
        const bdayInput = document.getElementById('birthday');
        if (bdayInput) {
            bdayInput.addEventListener('invalid', function(e) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateRegistrationBirthday(true);
            });
            bdayInput.addEventListener('focus', function() {
                if (!this.value && this.max) {
                    this.value = this.max;
                    autoCalcAge(this.value);
                    validateRegistrationBirthday();
                }
            });
            if (bdayInput.value) {
                autoCalcAge(bdayInput.value);
            }
        }
        form.addEventListener('submit', function(e) {
            if (!validateSellerDetails()) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateSellerDetails(true);
                return;
            }
            if (!validateLogisticsDetails()) {
                e.preventDefault();
                validateLogisticsDetails(true);
                return;
            }
            if (!validateRegistrationBirthday()) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateRegistrationBirthday(true);
                return;
            }
            if (!validateRegistrationPasswords()) {
                e.preventDefault();
                currentStep = 2;
                updateStepperUI();
                validateRegistrationPasswords(true);
                return;
            }
            if (!validateLogisticsTerms()) {
                e.preventDefault();
                currentStep = 2;
                updateStepperUI();
                validateLogisticsTerms(true);
                return;
            }
            if (!validateRegistrationPhone()) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateRegistrationPhone(true);
                return;
            }
            if (!validateRegistrationEmail()) {
                e.preventDefault();
                currentStep = 2;
                updateStepperUI();
                validateRegistrationEmail(true);
                return;
            }
            if (!validateRegistrationNames()) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateRegistrationNames(true);
                return;
            }
            const otpCode = syncOtp();
            const feedback = document.getElementById('otpFeedbackMsg');

            if (!otpCode || otpCode.length < 6) {
                e.preventDefault();
                otpInputs.forEach(inp => {
                    if (!inp.value) {
                        inp.focus();
                        inp.style.borderColor = '#ef4444';
                    }
                });
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.style.color = '#ef4444';
                    feedback.innerText = 'Please enter all 6 digits of the verification code.';
                }
                return;
            }

            const submitBtn = document.getElementById('btnVerifyEmailSubmit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'CREATING ACCOUNT...';
            }
        });
    }

    const initialRole = document.getElementById('role_input')?.value || 'buyer';
    selectRole(initialRole);
    if (['email', 'register_password', 'password_confirmation', 'logistics_terms', 'terms_agreed'].some(id => document.getElementById(id + '_error')?.textContent.trim())
        && !['first_name', 'last_name', 'phone', 'birthday'].some(id => document.getElementById(id + '_error')?.textContent.trim())
        && !Array.from(document.querySelectorAll(`#${steppedSignup()?.wrapper || 'sellerFieldsWrapper'} .seller-field-error`)).some(error => error.textContent.trim())) {
        currentStep = 2;
    }
    // A seller or logistics partner sent back with errors starts on the first step that has one.
    const steppedRole = steppedSignup();
    if (currentStep === 1 && steppedRole) {
        const errored = ['first_name', 'last_name', 'phone'].some(id => document.getElementById(id + '_error')?.textContent.trim())
            ? null
            : Array.from(document.querySelectorAll(`#${steppedRole.wrapper} .seller-field-error`)).find(error => error.textContent.trim());
        detailPage = errored ? Number(errored.closest('[data-signup-page]')?.dataset.signupPage || 1) : 1;
    }
    updateStepperUI();
    // Show the step holding the first field the browser rejects (later ones in the same submit are ignored).
    let invalidShown = false;
    form?.addEventListener('invalid', event => {
        if (invalidShown) return;
        invalidShown = true;
        setTimeout(() => { invalidShown = false; }, 0);
        const step = event.target.closest('[id^="step-"]');
        if (step) {
            currentStep = Number(step.id.replace('step-', ''));
            const page = event.target.closest('[data-signup-page]');
            if (page && steppedSignup()) detailPage = Number(page.dataset.signupPage);
            updateStepperUI();
        }
    }, true);
});
