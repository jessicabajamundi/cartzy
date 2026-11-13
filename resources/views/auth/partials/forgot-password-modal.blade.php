{{-- ── Step 3: Forgot Password Modal (Unified with Login Modal Design) ── --}}
<section class="lm-dialog lm-step" id="forgot-password-modal" data-lm-step="forgot" role="dialog" aria-modal="true" aria-labelledby="fpm-title-1" hidden>
    <div class="lm-panel">
        @if(!empty($standalone))
            <a href="{{ route('home') }}" class="lm-close" aria-label="Close and continue to the shop">{!! $closeIcon !!}</a>
        @else
            <button type="button" class="lm-close" data-lm-close aria-label="Close modal">{!! $closeIcon !!}</button>
        @endif

        {{-- ── Sub-step 1: Request OTP by entering Email ── --}}
        <div id="fpm-step-1" class="fpm-substep">
            <div class="lm-head">
                <button type="button" class="lm-role-badge" data-lm-step-to="login" title="Back to sign in">
                    <span class="lm-role-badge-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </span>
                    <span>Back to sign in</span>
                </button>
                <h2 class="lm-title" id="fpm-title-1">Forgot your password?</h2>
                <p class="lm-subtitle">Enter your registered email and we'll send a 6-digit verification code</p>
            </div>

            <div id="fpm-alert-1" class="lm-alert lm-alert-error" style="display: none;" role="alert"></div>

            <form id="fpm-email-form" onsubmit="fpmSubmitEmail(event)" class="lm-form" novalidate>
                <div class="lm-field">
                    <label for="fpm-email">Email address</label>
                    <div class="lm-input-wrap">
                        <svg class="lm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M2 7l10 7 10-7"/>
                        </svg>
                        <input type="email" id="fpm-email" name="email" required placeholder="you@example.com" autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="lm-submit lm-submit-main" id="fpm-send-btn">
                    <span class="lm-submit-shine" aria-hidden="true"></span>
                    <span class="lm-submit-spinner" aria-hidden="true"></span>
                    <span class="lm-submit-label" id="fpm-send-btn-text">Send Verification Code</span>
                    <svg class="lm-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <p class="lm-foot">
                Remember your password?
                <button type="button" class="lm-link" data-lm-step-to="login">Sign in</button>
            </p>
        </div>

        {{-- ── Sub-step 2: Enter 6-digit OTP ── --}}
        <div id="fpm-step-2" class="fpm-substep" style="display: none;">
            <div class="lm-head">
                <button type="button" class="lm-role-badge" onclick="fpmGoToStep(1)" title="Use a different email">
                    <span class="lm-role-badge-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </span>
                    <span>Change email</span>
                </button>
                <div class="lm-icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h9"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                        <circle cx="18" cy="19" r="3"></circle>
                        <polyline points="18 17 18 19 20 19"></polyline>
                    </svg>
                </div>
                <h2 class="lm-title">Check your email</h2>
                <p class="lm-subtitle">We sent a 6-digit code to</p>
                <span id="fpm-sent-email-display" class="lm-email-highlight"></span>
            </div>

            <div id="fpm-alert-2" class="lm-alert lm-alert-error" style="display: none;" role="alert"></div>

            <form id="fpm-otp-form" onsubmit="fpmSubmitOtp(event)" class="lm-form" novalidate>
                <div class="lm-otp-grid">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-1" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-2')" onpaste="fpmOtpPaste(event)">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-2" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-3', 'fpm-otp-1')">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-3" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-4', 'fpm-otp-2')">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-4" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-5', 'fpm-otp-3')">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-5" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-6', 'fpm-otp-4')">
                    <input type="text" maxlength="1" class="lm-otp-digit" id="fpm-otp-6" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, null, 'fpm-otp-5')">
                </div>

                <button type="submit" class="lm-submit lm-submit-main" id="fpm-verify-btn">
                    <span class="lm-submit-shine" aria-hidden="true"></span>
                    <span class="lm-submit-spinner" aria-hidden="true"></span>
                    <span class="lm-submit-label" id="fpm-verify-btn-text">Verify Code</span>
                    <svg class="lm-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <div class="lm-resend-row">
                Didn't receive the code?
                <button type="button" class="lm-link" id="fpm-resend-link" onclick="fpmResendOtp()">Resend code</button>
                <span id="fpm-resend-timer" style="display: none; color: var(--lm-rose-dark); font-weight: 700;"></span>
            </div>
        </div>

        {{-- ── Sub-step 3: Set New Password ── --}}
        <div id="fpm-step-3" class="fpm-substep" style="display: none;">
            <div class="lm-head">
                <div class="lm-icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h2 class="lm-title">Set new password</h2>
                <p class="lm-subtitle">Your identity has been verified. Create a strong new password for your account.</p>
            </div>

            <div id="fpm-alert-3" class="lm-alert lm-alert-error" style="display: none;" role="alert"></div>

            <form id="fpm-reset-form" onsubmit="fpmSubmitReset(event)" class="lm-form" novalidate>
                <div class="lm-field">
                    <label for="fpm-new-password">New password</label>
                    <div class="lm-input-wrap">
                        <svg class="lm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" id="fpm-new-password" name="password" required minlength="8" placeholder="At least 8 characters">
                        <button type="button" class="lm-eye" onclick="fpmTogglePw('fpm-new-password', this)" aria-label="Show password" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="lm-field">
                    <label for="fpm-confirm-password">Confirm new password</label>
                    <div class="lm-input-wrap">
                        <svg class="lm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" id="fpm-confirm-password" name="password_confirmation" required minlength="8" placeholder="Re-enter your password">
                        <button type="button" class="lm-eye" onclick="fpmTogglePw('fpm-confirm-password', this)" aria-label="Show password" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="lm-submit lm-submit-main" id="fpm-reset-btn">
                    <span class="lm-submit-shine" aria-hidden="true"></span>
                    <span class="lm-submit-spinner" aria-hidden="true"></span>
                    <span class="lm-submit-label" id="fpm-reset-btn-text">Reset Password</span>
                    <svg class="lm-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>

        {{-- ── Sub-step 4: Success confirmation ── --}}
        <div id="fpm-step-4" class="fpm-substep" style="display: none; text-align: center;">
            <div class="lm-head" style="margin-top: 10px;">
                <div class="lm-icon-badge lm-icon-badge-success" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h2 class="lm-title">Password updated!</h2>
                <p class="lm-subtitle">Your password has been changed successfully. You can now sign in with your new password.</p>
            </div>

            <button type="button" class="lm-submit lm-submit-main" id="fpm-finish-btn" onclick="fpmFinishToLogin()" style="margin-top: 20px;">
                <span class="lm-submit-shine" aria-hidden="true"></span>
                <span class="lm-submit-label">Continue to Sign In</span>
                <svg class="lm-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </div>
    </div>
</section>

<script>
    (function () {
        let activeEmail = '';
        let verifiedOtp = '';
        let resendTimer = null;
        let resendCountdown = 0;
        let currentSubStep = 1;

        window.fpmFocusCurrentStep = function () {
            if (currentSubStep === 1) {
                const emailInput = document.getElementById('fpm-email');
                if (emailInput) emailInput.focus({ preventScroll: true });
            } else if (currentSubStep === 2) {
                const otp1 = document.getElementById('fpm-otp-1');
                if (otp1) otp1.focus({ preventScroll: true });
            } else if (currentSubStep === 3) {
                const pw = document.getElementById('fpm-new-password');
                if (pw) pw.focus({ preventScroll: true });
            }
        };

        window.fpmGoToStep = function (stepNum, direction) {
            currentSubStep = stepNum;
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('fpm-step-' + i);
                if (el) {
                    if (i === stepNum) {
                        el.style.display = 'block';
                        el.classList.remove('is-entering-forward', 'is-entering-back');
                        void el.offsetWidth;
                        el.classList.add(direction === 'back' ? 'is-entering-back' : 'is-entering-forward');
                    } else {
                        el.style.display = 'none';
                    }
                }
            }
            fpmHideAlerts();
            setTimeout(window.fpmFocusCurrentStep, 70);
        };

        function fpmShowAlert(stepNum, msg) {
            const el = document.getElementById('fpm-alert-' + stepNum);
            if (el) {
                el.textContent = msg;
                el.style.display = 'block';
            }
        }

        function fpmHideAlerts() {
            for (let i = 1; i <= 3; i++) {
                const el = document.getElementById('fpm-alert-' + i);
                if (el) el.style.display = 'none';
            }
        }

        // Sub-step 1: Send OTP
        window.fpmSubmitEmail = function (e) {
            e.preventDefault();
            const email = (document.getElementById('fpm-email').value || '').trim();
            if (!email) return;

            const btn = document.getElementById('fpm-send-btn');
            btn.classList.add('is-loading');
            btn.disabled = true;
            fpmHideAlerts();

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('{{ route("password.send_otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ email: email })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                btn.classList.remove('is-loading');
                btn.disabled = false;

                if (ok && data.success) {
                    activeEmail = email;
                    const displayEl = document.getElementById('fpm-sent-email-display');
                    if (displayEl) displayEl.textContent = email;
                    fpmGoToStep(2, 'forward');
                    fpmStartResendTimer(60);
                } else {
                    fpmShowAlert(1, data.message || 'No account was found with that email address.');
                }
            })
            .catch(() => {
                btn.classList.remove('is-loading');
                btn.disabled = false;
                fpmShowAlert(1, 'Network connection error. Please try again.');
            });
        };

        // Sub-step 2: OTP Nav & Submit
        window.fpmOtpNav = function (current, nextId, prevId) {
            if (current.value.length >= 1 && nextId) {
                const nextEl = document.getElementById(nextId);
                if (nextEl) nextEl.focus();
            } else if (event && event.key === 'Backspace' && !current.value && prevId) {
                const prevEl = document.getElementById(prevId);
                if (prevEl) prevEl.focus();
            }
        };

        window.fpmOtpPaste = function (e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                for (let i = 0; i < 6; i++) {
                    const digitEl = document.getElementById('fpm-otp-' + (i + 1));
                    if (digitEl) digitEl.value = pasteData[i];
                }
                const btn = document.getElementById('fpm-verify-btn');
                if (btn) btn.focus();
            }
        };

        window.fpmSubmitOtp = function (e) {
            e.preventDefault();
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += (document.getElementById('fpm-otp-' + i)?.value || '').trim();
            }
            if (otp.length !== 6) {
                fpmShowAlert(2, 'Please enter all 6 digits of your verification code.');
                return;
            }

            const btn = document.getElementById('fpm-verify-btn');
            btn.classList.add('is-loading');
            btn.disabled = true;
            fpmHideAlerts();

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('{{ route("password.verify_otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ email: activeEmail, otp: otp })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                btn.classList.remove('is-loading');
                btn.disabled = false;

                if (ok && data.success) {
                    verifiedOtp = otp;
                    fpmGoToStep(3, 'forward');
                } else {
                    fpmShowAlert(2, data.message || 'Invalid or expired code. Please check your inbox.');
                }
            })
            .catch(() => {
                btn.classList.remove('is-loading');
                btn.disabled = false;
                fpmShowAlert(2, 'Network error. Please try again.');
            });
        };

        window.fpmResendOtp = function () {
            if (resendCountdown > 0) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route("password.send_otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ email: activeEmail })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    fpmStartResendTimer(60);
                }
            });
        };

        function fpmStartResendTimer(seconds) {
            resendCountdown = seconds;
            const link = document.getElementById('fpm-resend-link');
            const timer = document.getElementById('fpm-resend-timer');
            if (link) link.style.display = 'none';
            if (timer) {
                timer.style.display = 'inline';
                timer.textContent = ` (resend in ${resendCountdown}s)`;
            }

            if (resendTimer) clearInterval(resendTimer);
            resendTimer = setInterval(() => {
                resendCountdown--;
                if (resendCountdown <= 0) {
                    clearInterval(resendTimer);
                    if (link) link.style.display = 'inline';
                    if (timer) timer.style.display = 'none';
                } else {
                    if (timer) timer.textContent = ` (resend in ${resendCountdown}s)`;
                }
            }, 1000);
        }

        // Sub-step 3: Reset password
        window.fpmSubmitReset = function (e) {
            e.preventDefault();
            const pw = document.getElementById('fpm-new-password').value;
            const pwConfirm = document.getElementById('fpm-confirm-password').value;

            if (pw.length < 8) {
                fpmShowAlert(3, 'Password must be at least 8 characters long.');
                return;
            }
            if (pw !== pwConfirm) {
                fpmShowAlert(3, 'Password confirmation does not match.');
                return;
            }

            const btn = document.getElementById('fpm-reset-btn');
            btn.classList.add('is-loading');
            btn.disabled = true;
            fpmHideAlerts();

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const portal = (window.cartzyLogin && window.cartzyLogin.currentRole) ? window.cartzyLogin.currentRole() : 'buyer';

            fetch('{{ route("password.reset.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    email: activeEmail,
                    otp: verifiedOtp,
                    password: pw,
                    password_confirmation: pwConfirm,
                    portal: portal
                })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                btn.classList.remove('is-loading');
                btn.disabled = false;

                if (ok && data.success) {
                    fpmGoToStep(4, 'forward');
                } else {
                    let errMsg = data.message || 'Could not reset password. Please try again.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join(' ');
                    }
                    fpmShowAlert(3, errMsg);
                }
            })
            .catch(() => {
                btn.classList.remove('is-loading');
                btn.disabled = false;
                fpmShowAlert(3, 'Network error. Please try again.');
            });
        };

        window.fpmTogglePw = function (inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            btn.querySelector('svg').innerHTML = isPassword
                ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        };

        window.fpmFinishToLogin = function () {
            if (window.cartzyLogin) {
                window.cartzyLogin.showStep('login', { direction: 'back' });
            }
            const lm = document.getElementById('login-modal');
            if (lm) {
                if (activeEmail) {
                    const lmEmail = lm.querySelector('#lm-email');
                    if (lmEmail) lmEmail.value = activeEmail;
                    const lmPw = lm.querySelector('#lm-password');
                    if (lmPw) {
                        lmPw.value = '';
                        setTimeout(() => lmPw.focus(), 80);
                    }
                }
                const loginStep = lm.querySelector('.lm-step[data-lm-step="login"]');
                if (loginStep) {
                    const head = loginStep.querySelector('.lm-head');
                    let succ = loginStep.querySelector('.lm-alert-forgot-success');
                    if (!succ) {
                        succ = document.createElement('div');
                        succ.className = 'lm-alert lm-alert-success lm-alert-forgot-success';
                        succ.setAttribute('role', 'status');
                        if (head && head.nextSibling) {
                            head.parentNode.insertBefore(succ, head.nextSibling);
                        } else if (head) {
                            head.parentNode.appendChild(succ);
                        }
                    }
                    succ.textContent = 'Password updated successfully! Please sign in with your new password.';
                }
            }
        };
    })();
</script>