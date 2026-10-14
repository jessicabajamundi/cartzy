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
        const value = input.value.trim();
        let message = '';
        if (!value) {
            message = id === 'first_name' ? 'Please enter your first name.' : 'Please enter your last name.';
        } else if (!/^(?:\p{L}\p{M}*| )+$/u.test(value)) {
            message = 'Only letters are allowed.';
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
    if (document.getElementById('role_input')?.value === 'logistics') return true;
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
        if (!validateRegistrationBirthday(true)) return;
        if (!validateRegistrationNames(true)) return;
        if (!validateRegistrationPhone(true)) return;
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

        // Terms and Privacy Policy agreement check
        const termsCheckbox = document.getElementById('terms_agree');
        const termsError = document.getElementById('terms_agree_error');
        if (termsCheckbox && !termsCheckbox.checked) {
            if (termsError) {
                termsError.textContent = 'Please agree to the Cartzy Terms & Conditions and Privacy Policy to proceed.';
                termsError.hidden = false;
            }
            termsCheckbox.focus();
            return;
        } else if (termsError) {
            termsError.hidden = true;
        }

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

// Scroll to specific policy section (Terms & Conditions or Privacy Policy)
function scrollToPolicySection(sectionId) {
    const box = document.getElementById('termsPolicyScrollBox');
    const target = document.getElementById(sectionId);
    if (box && target) {
        const topPos = target.offsetTop - box.offsetTop;
        box.scrollTo({ top: Math.max(0, topPos - 4), behavior: 'smooth' });
    }
    const btnTerms = document.getElementById('btn-jump-terms');
    const btnPrivacy = document.getElementById('btn-jump-privacy');
    if (sectionId === 'terms-conditions-section') {
        btnTerms?.classList.add('active');
        btnPrivacy?.classList.remove('active');
    } else {
        btnPrivacy?.classList.add('active');
        btnTerms?.classList.remove('active');
    }
}
window.scrollToPolicySection = scrollToPolicySection;

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
        const data = await response.json();
        if (!response.ok || !data.success) {
            const field = data.errors?.phone ? 'phone' : 'email';
            currentStep = field === 'phone' ? 1 : 2;
            updateStepperUI();
            showRegistrationError(field, data.errors?.[field]?.[0] || data.message || 'Unable to send verification code. Please try again.', true);
            return false;
        }
        return true;
    } catch (error) {
        currentStep = 2;
        updateStepperUI();
        showRegistrationError('email', 'Unable to send verification code. Please try again.', true);
        return false;
    } finally {
        otpRequestPending = false;
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
        feedback.style.color = '#059669';
        feedback.innerText = 'A new verification code has been sent to your email!';
    }
}

// Role Selection function (Buyer, Seller, or Logistics)
function selectRole(role) {
    const birthdayInput = document.getElementById('birthday');
    if (birthdayInput) {
        birthdayInput.disabled = role === 'logistics';
        birthdayInput.required = role !== 'logistics';
        if (role === 'logistics') showRegistrationError('birthday', '');
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
    const footNoticeText = document.getElementById('footNoticeText');
    const linkLogisticsApply = document.getElementById('linkLogisticsApply');

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
}
window.selectRole = selectRole;

// 6-Box OTP Input Handling (Paste, Auto-focus, Backspace)
document.addEventListener('DOMContentLoaded', function() {
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
    document.getElementById('email')?.addEventListener('input', () => showRegistrationError('email', ''));
    document.getElementById('email')?.addEventListener('blur', () => validateRegistrationEmail());
    ['first_name', 'last_name'].forEach(id => {
        const input = document.getElementById(id);
        input?.addEventListener('beforeinput', event => {
            if (!event.isComposing && event.inputType === 'insertText' && event.data && /[^\p{L}\p{M} ]/u.test(event.data)) {
                event.preventDefault();
            }
        });
        const cleanName = () => {
            const original = input.value;
            const position = input.selectionStart;
            input.value = original.replace(/[^\p{L}\p{M} ]/gu, '');
            if (position !== null && original !== input.value) {
                const caret = original.slice(0, position).replace(/[^\p{L}\p{M} ]/gu, '').length;
                input.setSelectionRange(caret, caret);
            }
            validateRegistrationNames();
        };
        input?.addEventListener('input', event => {
            if (!event.isComposing) cleanName();
        });
        input?.addEventListener('compositionend', cleanName);
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
            const termsCheckbox = document.getElementById('terms_agree');
            if (termsCheckbox && !termsCheckbox.checked) {
                e.preventDefault();
                currentStep = 2;
                updateStepperUI();
                const termsError = document.getElementById('terms_agree_error');
                if (termsError) {
                    termsError.textContent = 'Please agree to the Cartzy Terms & Conditions and Privacy Policy to proceed.';
                    termsError.hidden = false;
                }
                termsCheckbox.focus();
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
        && !['first_name', 'last_name', 'phone', 'birthday'].some(id => document.getElementById(id + '_error')?.textContent.trim())) {
        currentStep = 2;
    }
    updateStepperUI();

    // Legal policy scroll tracking & checkbox event listener
    const policyBox = document.getElementById('termsPolicyScrollBox');
    const btnTerms = document.getElementById('btn-jump-terms');
    const btnPrivacy = document.getElementById('btn-jump-privacy');
    const privacySection = document.getElementById('privacy-policy-section');

    if (policyBox && privacySection) {
        policyBox.addEventListener('scroll', function() {
            const privacyOffset = privacySection.offsetTop - policyBox.offsetTop;
            if (policyBox.scrollTop >= privacyOffset - 30) {
                btnPrivacy?.classList.add('active');
                btnTerms?.classList.remove('active');
            } else {
                btnTerms?.classList.add('active');
                btnPrivacy?.classList.remove('active');
            }
        });
    }

    const termsAgreeInput = document.getElementById('terms_agree');
    const termsErrorEl = document.getElementById('terms_agree_error');
    if (termsAgreeInput && termsErrorEl) {
        termsAgreeInput.addEventListener('change', function() {
            if (this.checked) {
                termsErrorEl.hidden = true;
                termsErrorEl.textContent = '';
            }
        });
    }
});
