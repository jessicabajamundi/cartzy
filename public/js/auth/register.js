/**
 * Cartzy Authentication - 3-Step Registration Wizard (Cartzy Original Design)
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
    if (!value && document.getElementById('role_input').value === 'seller') message = 'Please enter your mobile number.';
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
    const seller = document.getElementById('role_input').value === 'seller';
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
    let message = '';
    if (!input.value) {
        message = 'Please enter your birthday.';
    } else if (!/^\d{4}-\d{2}-\d{2}$/.test(input.value) || input.validity.badInput) {
        message = 'Please enter a valid birthday.';
    } else if (input.value > input.max) {
        message = 'You must be at least 18 years old to register.';
    }
    return showRegistrationError('birthday', message, focus);
}

// Age auto-calculate from birthday
function autoCalcAge(dateStr) {
    if (!dateStr) {
        const ageEl = document.getElementById('age_display');
        if (ageEl) ageEl.value = '';
        return;
    }
    const today = new Date();
    const bday = new Date(dateStr + 'T00:00:00');
    let age = today.getFullYear() - bday.getFullYear();
    const m = today.getMonth() - bday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
    const ageEl = document.getElementById('age_display');
    if (ageEl) ageEl.value = age >= 0 ? age : '';
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
    // Validate Step 1
    if (currentStep === 1) {
        if (!validateSellerDetails(true)) return;
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

        const role = document.getElementById('role_input')?.value || 'buyer';
        if (role === 'logistics') {
            const companyEl = document.getElementById('business_name');
            const companyName = companyEl ? companyEl.value.trim() : '';
            if (!companyName) {
                if (companyEl) {
                    companyEl.focus();
                    companyEl.style.borderColor = '#ef4444';
                }
                return;
            }
            if (companyEl) companyEl.style.borderColor = '';
        }

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

        // Send OTP automatically when transitioning to Step 3
        if (!await sendAutomaticOtp()) return;
        startOtpTimer();
    }

    currentStep = targetStep;
    updateStepperUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });

    if (currentStep === 3) {
        setTimeout(() => {
            const firstOtp = document.querySelector('.otp-input[data-index="0"]');
            if (firstOtp) firstOtp.focus();
        }, 150);
    }
}

// Previous Step transition
function prevStep(targetStep) {
    currentStep = targetStep;
    updateStepperUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
        if (authTitle) authTitle.innerText = 'Create your account';
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
    updateSellerDetails(role);
}
window.selectRole = selectRole;

function updateSellerDetails(role = document.getElementById('role_input').value) {
    const seller = role === 'seller';
    const wrapper = document.getElementById('sellerFieldsWrapper');
    if (!wrapper) return;
    wrapper.style.display = seller ? 'block' : 'none';
    document.getElementById('sellerNoticeBox').style.display = seller ? 'block' : 'none';
    document.getElementById('phone').required = seller;
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
}

function validateSellerDetails(focus = false) {
    if (document.getElementById('role_input').value !== 'seller') return true;
    let firstInvalid = null;
    document.querySelectorAll('#sellerFieldsWrapper [data-seller-required], #sellerFieldsWrapper [data-seller-document]').forEach(input => {
        if (input.hasAttribute('data-seller-document') && input.disabled) return;
        input.setCustomValidity('');
        if (input.type === 'text') input.value = input.value.trim();
        let message = '';
        if (input.required && !input.value) {
            message = 'Please complete this field.';
        }
        if (input.disabled && input.hasAttribute('data-seller-required')) message = 'Choose the preceding location first, or retry loading locations.';
        if (!message && input.type === 'file' && input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) message = 'File must not exceed 5 MB.';
            else if (!/\.(jpe?g|png|pdf)$/i.test(file.name)) message = 'Choose a JPG, PNG or PDF file.';
        }
        if (!message && !input.checkValidity()) message = input.validationMessage;
        showRegistrationError(input.id, message);
        if (message && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid && focus) firstInvalid.focus();
    return !firstInvalid;
}

// 6-Box OTP Input Handling (Paste, Auto-focus, Backspace)
function formatRegistrationTin(value) {
    return value.replace(/\D/g, '').slice(0, 12).replace(/(\d{3})(?=\d)/g, '$1-');
}

document.addEventListener('DOMContentLoaded', function() {
    const tinInput = document.getElementById('tin_number');
    if (tinInput) {
        const formatTin = () => {
            const digitsBeforeCaret = tinInput.value.slice(0, tinInput.selectionStart ?? tinInput.value.length).replace(/\D/g, '').length;
            tinInput.value = formatRegistrationTin(tinInput.value);
            const caret = Math.min(tinInput.value.length, digitsBeforeCaret + Math.floor(Math.max(0, digitsBeforeCaret - 1) / 3));
            tinInput.setSelectionRange(caret, caret);
            showRegistrationError('tin_number', '');
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
    }
    document.getElementById('seller_type')?.addEventListener('change', () => updateSellerDetails());
    document.querySelectorAll('#sellerFieldsWrapper [data-seller-document]').forEach(input => {
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
        document.getElementById('birthday')?.addEventListener('invalid', function(e) {
            e.preventDefault();
            currentStep = 1;
            updateStepperUI();
            validateRegistrationBirthday(true);
        });
        form.addEventListener('submit', function(e) {
            if (!validateSellerDetails()) {
                e.preventDefault();
                currentStep = 1;
                updateStepperUI();
                validateSellerDetails(true);
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
    if (['email', 'register_password', 'password_confirmation'].some(id => document.getElementById(id + '_error')?.textContent.trim())
        && !['first_name', 'last_name', 'phone', 'birthday'].some(id => document.getElementById(id + '_error')?.textContent.trim())
        && !Array.from(document.querySelectorAll('#sellerFieldsWrapper .seller-field-error')).some(error => error.textContent.trim())) {
        currentStep = 2;
    }
    updateStepperUI();
    form?.addEventListener('invalid', event => {
        const step = event.target.closest('[id^="step-"]');
        if (step) {
            currentStep = Number(step.id.replace('step-', ''));
            updateStepperUI();
        }
    }, true);
});
