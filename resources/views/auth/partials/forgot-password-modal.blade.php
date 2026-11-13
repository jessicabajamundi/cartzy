{{-- Forgot Password Pop-up Modal Dialog --}}
<dialog id="forgot-password-modal" class="forgot-password-dialog" aria-labelledby="fpm-title-1" aria-modal="true">
    <div class="forgot-password-card">
        <button type="button" class="forgot-password-close" data-close-forgot-modal aria-label="Close dialog">&times;</button>

        {{-- ── Step 1: Request OTP by entering Email ── --}}
        <div id="fpm-step-1" class="fpm-step">
            <div class="fpm-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2 id="fpm-title-1" class="fpm-title">Forgot your password?</h2>
            <p class="fpm-subtitle">Enter your registered email address and we'll send a 6-digit verification code to reset your password.</p>

            <div id="fpm-alert-1" class="fpm-alert" style="display: none;"></div>

            <form id="fpm-email-form" onsubmit="fpmSubmitEmail(event)" class="fpm-form">
                <div class="fpm-field">
                    <label for="fpm-email">Email address</label>
                    <div class="fpm-input-wrap">
                        <svg class="fpm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M2 7l10 7 10-7"/>
                        </svg>
                        <input type="email" id="fpm-email" name="email" required placeholder="you@example.com" autocomplete="email" class="fpm-input">
                    </div>
                </div>

                <button type="submit" class="fpm-submit-btn" id="fpm-send-btn">
                    <span id="fpm-send-btn-text">Send Verification Code</span>
                    <span id="fpm-send-btn-spinner" class="fpm-spinner" style="display: none;"></span>
                </button>
            </form>

            <div class="fpm-foot">
                Remember your password?
                <button type="button" class="fpm-foot-link" data-close-forgot-modal>Back to login</button>
            </div>
        </div>

        {{-- ── Step 2: Enter 6-digit OTP ── --}}
        <div id="fpm-step-2" class="fpm-step" style="display: none;">
            <div class="fpm-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h9"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                    <circle cx="18" cy="19" r="3"></circle>
                    <polyline points="18 17 18 19 20 19"></polyline>
                </svg>
            </div>
            <h2 class="fpm-title">Check your email</h2>
            <p class="fpm-subtitle">We sent a 6-digit code to <strong id="fpm-sent-email-display" style="color: var(--ink, #1f2937); overflow-wrap: anywhere;"></strong></p>

            <div id="fpm-alert-2" class="fpm-alert" style="display: none;"></div>

            <form id="fpm-otp-form" onsubmit="fpmSubmitOtp(event)" class="fpm-form">
                <div class="fpm-otp-grid">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-1" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-2')" onpaste="fpmOtpPaste(event)">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-2" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-3', 'fpm-otp-1')">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-3" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-4', 'fpm-otp-2')">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-4" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-5', 'fpm-otp-3')">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-5" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, 'fpm-otp-6', 'fpm-otp-4')">
                    <input type="text" maxlength="1" class="fpm-otp-digit" id="fpm-otp-6" inputmode="numeric" pattern="[0-9]" required onkeyup="fpmOtpNav(this, null, 'fpm-otp-5')">
                </div>

                <button type="submit" class="fpm-submit-btn" id="fpm-verify-btn">
                    <span id="fpm-verify-btn-text">Verify Code</span>
                    <span id="fpm-verify-btn-spinner" class="fpm-spinner" style="display: none;"></span>
                </button>
            </form>

            <div class="fpm-foot">
                Didn't receive the code?
                <button type="button" class="fpm-foot-link" id="fpm-resend-link" onclick="fpmResendOtp()">Resend code</button>
                <span id="fpm-resend-timer" style="display: none; color: #8C5A50; font-weight: 600;"></span>
                <div style="margin-top: 10px;">
                    <button type="button" class="fpm-subtle-link" onclick="fpmGoToStep(1)">← Use a different email</button>
                </div>
            </div>
        </div>

        {{-- ── Step 3: Set New Password ── --}}
        <div id="fpm-step-3" class="fpm-step" style="display: none;">
            <div class="fpm-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <h2 class="fpm-title">Set new password</h2>
            <p class="fpm-subtitle">Your identity has been verified. Create a strong new password for your account.</p>

            <div id="fpm-alert-3" class="fpm-alert" style="display: none;"></div>

            <form id="fpm-reset-form" onsubmit="fpmSubmitReset(event)" class="fpm-form">
                <div class="fpm-field">
                    <label for="fpm-new-password">New password</label>
                    <div class="fpm-input-wrap">
                        <svg class="fpm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" id="fpm-new-password" name="password" required minlength="8" placeholder="At least 8 characters" class="fpm-input">
                        <button type="button" class="fpm-eye-btn" onclick="fpmTogglePw('fpm-new-password', this)" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="fpm-field">
                    <label for="fpm-confirm-password">Confirm new password</label>
                    <div class="fpm-input-wrap">
                        <svg class="fpm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" id="fpm-confirm-password" name="password_confirmation" required minlength="8" placeholder="Re-enter your password" class="fpm-input">
                        <button type="button" class="fpm-eye-btn" onclick="fpmTogglePw('fpm-confirm-password', this)" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="fpm-submit-btn" id="fpm-reset-btn">
                    <span id="fpm-reset-btn-text">Reset Password</span>
                    <span id="fpm-reset-btn-spinner" class="fpm-spinner" style="display: none;"></span>
                </button>
            </form>
        </div>

        {{-- ── Step 4: Success confirmation ── --}}
        <div id="fpm-step-4" class="fpm-step" style="display: none;">
            <div class="fpm-badge fpm-badge-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2 class="fpm-title">Password updated!</h2>
            <p class="fpm-subtitle">Your password has been changed successfully. You can now sign in with your new password.</p>

            <button type="button" class="fpm-submit-btn" data-close-forgot-modal style="margin-top: 20px;">
                Continue to Login
            </button>
        </div>
    </div>
</dialog>

<style>
    .forgot-password-dialog {
        width: min(460px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--line, #e2e8f0);
        border-radius: 24px;
        background: var(--surface, #fff);
        color: var(--ink, #1f2937);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.28);
        overflow: hidden;
        font-family: inherit;
    }
    .forgot-password-dialog::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(4px);
    }
    .forgot-password-dialog[open] {
        animation: fpm-pop 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes fpm-pop {
        from { opacity: 0; transform: scale(0.95) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .forgot-password-card {
        position: relative;
        padding: 34px 30px 30px;
        text-align: left;
    }
    .forgot-password-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f8fafc;
        color: #64748b;
        font-size: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all 0.15s ease;
    }
    .forgot-password-close:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: scale(1.05);
    }
    .fpm-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        margin-bottom: 18px;
        border-radius: 14px;
        background: #FFF5F3;
        border: 1px solid #F5CFC7;
        color: #8C5A50;
    }
    .fpm-badge svg {
        width: 24px;
        height: 24px;
    }
    .fpm-badge.fpm-badge-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #16a34a;
    }
    .fpm-title {
        font-size: 21px;
        font-weight: 750;
        color: #111827;
        margin: 0 0 8px;
        line-height: 1.25;
        letter-spacing: -0.015em;
    }
    .fpm-subtitle {
        font-size: 13.5px;
        color: #64748b;
        margin: 0 0 22px;
        line-height: 1.5;
    }
    .fpm-alert {
        padding: 11px 14px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.45;
        margin-bottom: 18px;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #b91c1c;
    }
    .fpm-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .fpm-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .fpm-field label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }
    .fpm-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .fpm-input-icon {
        position: absolute;
        left: 14px;
        width: 19px;
        height: 19px;
        color: #9ca3af;
        pointer-events: none;
    }
    .fpm-input {
        width: 100%;
        min-height: 44px;
        padding: 10px 14px 10px 42px;
        font-size: 14px;
        font-family: inherit;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        color: #1e293b;
        box-sizing: border-box;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .fpm-input:focus {
        background: #fff;
        border-color: #8C5A50;
        outline: none;
        box-shadow: 0 0 0 3px rgba(140, 90, 80, 0.15);
    }
    .fpm-eye-btn {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        padding: 6px;
        cursor: pointer;
        color: #9ca3af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fpm-eye-btn:hover {
        color: #4b5563;
    }
    .fpm-eye-btn svg {
        width: 18px;
        height: 18px;
    }
    .fpm-submit-btn {
        width: 100%;
        min-height: 46px;
        padding: 0 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        background: #8C5A50;
        color: #fff;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.15s, transform 0.1s;
    }
    .fpm-submit-btn:hover {
        background: #6B3F37;
    }
    .fpm-submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .fpm-otp-grid {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin: 6px 0 10px;
    }
    .fpm-otp-digit {
        width: 48px;
        height: 52px;
        text-align: center;
        font-size: 20px;
        font-weight: 750;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        color: #111827;
        box-sizing: border-box;
        transition: all 0.15s;
    }
    .fpm-otp-digit:focus {
        background: #fff;
        border-color: #8C5A50;
        outline: none;
        box-shadow: 0 0 0 3px rgba(140, 90, 80, 0.18);
    }
    .fpm-foot {
        margin-top: 22px;
        font-size: 13px;
        color: #64748b;
        text-align: center;
    }
    .fpm-foot-link {
        background: none;
        border: none;
        font-weight: 700;
        color: #8C5A50;
        cursor: pointer;
        padding: 0;
        font-size: inherit;
        text-decoration: none;
    }
    .fpm-foot-link:hover {
        text-decoration: underline;
    }
    .fpm-subtle-link {
        background: none;
        border: none;
        font-size: 12px;
        color: #64748b;
        cursor: pointer;
        padding: 0;
    }
    .fpm-subtle-link:hover {
        color: #1e293b;
        text-decoration: underline;
    }
    .fpm-spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2.5px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: fpm-spin 0.8s linear infinite;
    }
    @keyframes fpm-spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    (function () {
        const modal = document.getElementById('forgot-password-modal');
        if (!modal) return;

        let activeEmail = '';
        let verifiedOtp = '';
        let resendTimer = null;
        let resendCountdown = 0;
        let activePortal = 'buyer';

        window.cartzyForgotModal = {
            open: function (opts) {
                opts = opts || {};
                activePortal = opts.portal || (document.getElementById('login-modal') ? document.getElementById('login-modal').dataset.portal : 'buyer') || 'buyer';
                
                fpmGoToStep(1);
                const emailInput = document.getElementById('fpm-email');
                if (opts.email && emailInput) {
                    emailInput.value = opts.email;
                }

                if (typeof modal.showModal === 'function') {
                    if (!modal.open) modal.showModal();
                } else {
                    modal.setAttribute('open', '');
                }

                setTimeout(() => {
                    if (emailInput && !emailInput.value) emailInput.focus();
                }, 80);
            },
            close: function () {
                if (typeof modal.close === 'function') modal.close();
                else modal.removeAttribute('open');
            }
        };

        // Wire close buttons
        document.querySelectorAll('[data-close-forgot-modal]').forEach(btn => {
            btn.addEventListener('click', function () {
                window.cartzyForgotModal.close();
                // If login modal exists, focus email
                const lm = document.getElementById('login-modal');
                if (lm && activeEmail) {
                    const lmEmail = lm.querySelector('#lm-email');
                    if (lmEmail) {
                        lmEmail.value = activeEmail;
                        const lmPw = lm.querySelector('#lm-password');
                        if (lmPw) lmPw.focus();
                    }
                }
            });
        });

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            const rect = modal.getBoundingClientRect();
            if (
                e.clientX < rect.left ||
                e.clientX > rect.right ||
                e.clientY < rect.top ||
                e.clientY > rect.bottom
            ) {
                window.cartzyForgotModal.close();
            }
        });

        // Step navigation
        window.fpmGoToStep = function (stepNum) {
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('fpm-step-' + i);
                if (el) el.style.display = (i === stepNum) ? 'block' : 'none';
            }
            fpmHideAlerts();
            if (stepNum === 1) {
                const emailInput = document.getElementById('fpm-email');
                if (emailInput) setTimeout(() => emailInput.focus(), 60);
            } else if (stepNum === 2) {
                const otp1 = document.getElementById('fpm-otp-1');
                if (otp1) setTimeout(() => otp1.focus(), 60);
            } else if (stepNum === 3) {
                const pw = document.getElementById('fpm-new-password');
                if (pw) setTimeout(() => pw.focus(), 60);
            }
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

        // Step 1: Send OTP
        window.fpmSubmitEmail = function (e) {
            e.preventDefault();
            const email = (document.getElementById('fpm-email').value || '').trim();
            if (!email) return;

            const btn = document.getElementById('fpm-send-btn');
            const text = document.getElementById('fpm-send-btn-text');
            const spinner = document.getElementById('fpm-send-btn-spinner');

            btn.disabled = true;
            text.style.display = 'none';
            spinner.style.display = 'inline-block';
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
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';

                if (ok && data.success) {
                    activeEmail = email;
                    document.getElementById('fpm-sent-email-display').textContent = email;
                    fpmGoToStep(2);
                    fpmStartResendTimer(60);
                    if (data.otp_preview) {
                        console.log('%c[Cartzy Dev] Reset OTP: ' + data.otp_preview, 'color: #8C5A50; font-weight: bold;');
                    }
                } else {
                    fpmShowAlert(1, data.message || 'No account was found with that email address.');
                }
            })
            .catch(() => {
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';
                fpmShowAlert(1, 'Network connection error. Please try again.');
            });
        };

        // Step 2: OTP digits
        window.fpmOtpNav = function (current, nextId, prevId) {
            if (current.value.length >= 1 && nextId) {
                const nextEl = document.getElementById(nextId);
                if (nextEl) nextEl.focus();
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
            const text = document.getElementById('fpm-verify-btn-text');
            const spinner = document.getElementById('fpm-verify-btn-spinner');

            btn.disabled = true;
            text.style.display = 'none';
            spinner.style.display = 'inline-block';
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
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';

                if (ok && data.success) {
                    verifiedOtp = otp;
                    fpmGoToStep(3);
                } else {
                    fpmShowAlert(2, data.message || 'Invalid or expired code. Please check your inbox.');
                }
            })
            .catch(() => {
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';
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
                    if (data.otp_preview) {
                        console.log('%c[Cartzy Dev] New OTP: ' + data.otp_preview, 'color: #8C5A50; font-weight: bold;');
                    }
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

        // Step 3: Reset password
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
            const text = document.getElementById('fpm-reset-btn-text');
            const spinner = document.getElementById('fpm-reset-btn-spinner');

            btn.disabled = true;
            text.style.display = 'none';
            spinner.style.display = 'inline-block';
            fpmHideAlerts();

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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
                    portal: activePortal
                })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';

                if (ok && data.success) {
                    fpmGoToStep(4);
                } else {
                    let errMsg = data.message || 'Could not reset password. Please try again.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join(' ');
                    }
                    fpmShowAlert(3, errMsg);
                }
            })
            .catch(() => {
                btn.disabled = false;
                text.style.display = 'inline';
                spinner.style.display = 'none';
                fpmShowAlert(3, 'Network error. Please try again.');
            });
        };

        window.fpmTogglePw = function (inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.style.opacity = isPassword ? '1' : '0.6';
        };

        // Wire hash triggers on load
        if (window.location.hash === '#forgot-password') {
            window.cartzyForgotModal.open();
        }
        window.addEventListener('hashchange', function () {
            if (window.location.hash === '#forgot-password') {
                window.cartzyForgotModal.open();
            }
        });
    })();
</script>
