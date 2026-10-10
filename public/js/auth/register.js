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

// Age auto-calculate from birthday
function autoCalcAge(dateStr) {
    if (!dateStr) {
        const ageEl = document.getElementById('age_display');
        if (ageEl) ageEl.value = '';
        return;
    }
    const today = new Date();
    const bday = new Date(dateStr);
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
function nextStep(targetStep) {
    // Validate Step 1
    if (currentStep === 1) {
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
        const emailEl = document.getElementById('email');
        const passwordEl = document.getElementById('register_password');
        const confirmPwEl = document.getElementById('password_confirmation');

        const email = emailEl ? emailEl.value.trim() : '';
        const password = passwordEl ? passwordEl.value : '';
        const confirmPw = confirmPwEl ? confirmPwEl.value : '';

        // Email check
        if (!email || !email.includes('@') || !email.includes('.')) {
            emailEl.focus();
            emailEl.style.borderColor = '#ef4444';
            return;
        }
        emailEl.style.borderColor = '';

        // Password length check
        if (!password || password.length < 6) {
            passwordEl.focus();
            passwordEl.style.borderColor = '#ef4444';
            alert('Password must be at least 6 characters long.');
            return;
        }
        passwordEl.style.borderColor = '';

        // Password confirmation match
        if (password !== confirmPw) {
            confirmPwEl.focus();
            confirmPwEl.style.borderColor = '#ef4444';
            alert('The password confirmation does not match.');
            return;
        }
        confirmPwEl.style.borderColor = '';

        // Send OTP automatically when transitioning to Step 3
        sendAutomaticOtp();
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

// Toggle Password Visibility with SVG eye icon / Show-Hide text
function togglePasswordVisibility(inputId, btnEl) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        if (btnEl) {
            const textEl = btnEl.querySelector('.eye-text');
            if (textEl) textEl.innerText = 'Hide';
        }
    } else {
        input.type = 'password';
        if (btnEl) {
            const textEl = btnEl.querySelector('.eye-text');
            if (textEl) textEl.innerText = 'Show';
        }
    }
}

// Send Automatic OTP via AJAX
function sendAutomaticOtp() {
    const email = document.getElementById('email')?.value?.trim();
    const name = document.getElementById('name')?.value?.trim();
    const form = document.getElementById('registerForm');
    const requestOtpUrl = form?.dataset?.requestOtpUrl || '/register/request-otp';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_token"]')?.value || '';

    if (!email) return;

    fetch(requestOtpUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ email: email, name: name })
    })
    .then(res => res.json())
    .then(data => {
        console.log('OTP request status:', data.message || 'Code sent');
    })
    .catch(err => {
        console.warn('OTP request error:', err);
    });
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
function triggerResendOtp() {
    const resendBtn = document.getElementById('resendOtpBtn');
    if (resendBtn && resendBtn.disabled) return;

    sendAutomaticOtp();
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
        form.addEventListener('submit', function(e) {
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
    updateStepperUI();
});
