@extends('layouts.auth')

@section('title', 'Forgot Password | cartzy')
@section('auth_visual', 'products')

@section('auth_form')

    <h2 class="auth-title" id="fp-title">Forgot your password?</h2>
    <p class="auth-subtitle" id="fp-subtitle">Enter your email address and we'll send you a 6-digit code to reset your password.</p>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <svg style="width: 18px; height: 18px; flex-shrink: 0; color: #16A34A;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" id="fp-error">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ═══ STEP 1: Enter Email ═══ --}}
    <div id="fp-step-1">
        <label class="field-label" for="fp_email">Email address</label>
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
                id="fp_email"
                required
                autofocus
                placeholder="Enter your registered email"
                class="auth-input"
            >
        </div>

        <button type="button" class="btn-primary" id="fp-send-btn" onclick="fpSendOtp()">
            <span id="fp-send-text">Send Verification Code</span>
            <svg id="fp-send-spinner" style="display:none; width:20px; height:20px; margin:0 auto; animation: spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10" stroke-dasharray="50" stroke-dashoffset="15" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="auth-foot" style="margin-top: 24px;">
            Remember your password?
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>

    {{-- ═══ STEP 2: Enter OTP ═══ --}}
    <div id="fp-step-2" style="display: none;">

        <div style="text-align: center; margin-bottom: 18px; padding: 14px 18px; background: #FFF5F3; border: 1.5px solid #F5CFC7; border-radius: 12px;">
            <div style="font-size: 0.78rem; color: #8C5A50; font-weight: 600; margin-bottom: 4px;">Verification code sent to</div>
            <div style="font-size: 0.92rem; font-weight: 800; color: #282133;" id="fp-sent-email"></div>
        </div>

        <label class="field-label">Enter your 6-digit code</label>
        <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 18px;">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp1" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, 'otp2')" onpaste="fpOtpPaste(event)">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp2" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, 'otp3', 'otp1')">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp3" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, 'otp4', 'otp2')">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp4" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, 'otp5', 'otp3')">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp5" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, 'otp6', 'otp4')">
            <input type="text" maxlength="1" class="auth-input fp-otp-digit" id="otp6" inputmode="numeric" pattern="[0-9]" style="width: 48px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 800; padding: 0 !important; letter-spacing: 0;" onkeyup="fpOtpNav(this, null, 'otp5')">
        </div>

        <div id="fp-otp-error" style="display:none; text-align:center; font-size:0.82rem; color:#ef4444; font-weight:600; margin-bottom:12px;"></div>

        <button type="button" class="btn-primary" id="fp-verify-btn" onclick="fpVerifyOtp()">
            <span id="fp-verify-text">Verify Code</span>
            <svg id="fp-verify-spinner" style="display:none; width:20px; height:20px; margin:0 auto; animation: spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10" stroke-dasharray="50" stroke-dashoffset="15" stroke-linecap="round"/>
            </svg>
        </button>

        <div style="text-align: center; margin-top: 14px; font-size: 0.82rem; color: #6b7280;">
            Didn't receive the code?
            <a href="#" onclick="fpResendOtp(); return false;" style="font-weight: 700; color: #8C5A50;" id="fp-resend-link">Resend</a>
            <span id="fp-resend-timer" style="display:none; font-weight: 600; color: #A8A0B2;"></span>
        </div>
    </div>

    {{-- ═══ STEP 3: Set New Password ═══ --}}
    <div id="fp-step-3" style="display: none;">
        <form action="{{ route('password.reset.submit') }}" method="POST" id="fp-reset-form">
            @csrf
            <input type="hidden" name="email" id="fp_reset_email">
            <input type="hidden" name="otp" id="fp_reset_otp">

            <label class="field-label" for="fp_new_password">New password</label>
            <div class="input-group">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 018 0v4"/>
                    </svg>
                </span>
                <input type="password" name="password" id="fp_new_password" required placeholder="Create a new password (min 8 chars)" class="auth-input" minlength="8">
                <button type="button" class="eye-btn" onclick="fpTogglePw('fp_new_password', 'fp_eye_new')" tabindex="-1">
                    <svg id="fp_eye_new" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <label class="field-label" for="fp_confirm_password">Confirm new password</label>
            <div class="input-group">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 018 0v4"/>
                    </svg>
                </span>
                <input type="password" name="password_confirmation" id="fp_confirm_password" required placeholder="Re-enter your new password" class="auth-input" minlength="8">
                <button type="button" class="eye-btn" onclick="fpTogglePw('fp_confirm_password', 'fp_eye_confirm')" tabindex="-1">
                    <svg id="fp_eye_confirm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <div id="fp-pw-error" style="display:none; font-size:0.82rem; color:#ef4444; font-weight:600; margin-bottom:12px;"></div>

            <button type="submit" class="btn-primary">Reset Password</button>
        </form>

        <div class="auth-foot" style="margin-top: 20px;">
            <a href="{{ route('login') }}">← Back to login</a>
        </div>
    </div>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .fp-otp-digit:focus {
        border-color: #C08B7F !important;
        box-shadow: 0 0 0 3px rgba(192, 139, 127, 0.25) !important;
    }
</style>

@push('scripts')
<script>
    const fpCsrfToken = '{{ csrf_token() }}';
    let fpEmail = '';
    let fpResendCooldown = 0;

    function fpSendOtp() {
        const emailInput = document.getElementById('fp_email');
        const email = emailInput.value.trim();
        if (!email || !email.includes('@')) {
            emailInput.focus();
            return;
        }

        const btn = document.getElementById('fp-send-btn');
        const text = document.getElementById('fp-send-text');
        const spinner = document.getElementById('fp-send-spinner');
        btn.disabled = true;
        text.style.display = 'none';
        spinner.style.display = 'block';

        // Clear previous errors
        const errEl = document.getElementById('fp-error');
        if (errEl) errEl.style.display = 'none';

        fetch('{{ route("password.send_otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fpCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            text.style.display = 'inline';
            spinner.style.display = 'none';

            if (data.success) {
                fpEmail = email;
                document.getElementById('fp-sent-email').textContent = email;
                document.getElementById('fp-step-1').style.display = 'none';
                document.getElementById('fp-step-2').style.display = 'block';
                document.getElementById('fp-title').textContent = 'Check your email';
                document.getElementById('fp-subtitle').textContent = 'We sent a 6-digit verification code to your inbox. Enter it below.';
                document.getElementById('otp1').focus();
                fpStartResendTimer(60);

                // Show OTP preview in debug mode
                if (data.otp_preview) {
                    console.log('%c[DEV] OTP Preview: ' + data.otp_preview, 'color: #C08B7F; font-weight: bold; font-size: 14px;');
                }
            } else {
                alert(data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            text.style.display = 'inline';
            spinner.style.display = 'none';
            alert('Network error. Please check your connection.');
        });
    }

    function fpVerifyOtp() {
        let otp = '';
        for (let i = 1; i <= 6; i++) {
            otp += document.getElementById('otp' + i).value;
        }
        if (otp.length !== 6) {
            document.getElementById('fp-otp-error').textContent = 'Please enter all 6 digits.';
            document.getElementById('fp-otp-error').style.display = 'block';
            return;
        }

        const btn = document.getElementById('fp-verify-btn');
        const text = document.getElementById('fp-verify-text');
        const spinner = document.getElementById('fp-verify-spinner');
        btn.disabled = true;
        text.style.display = 'none';
        spinner.style.display = 'block';
        document.getElementById('fp-otp-error').style.display = 'none';

        fetch('{{ route("password.verify_otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fpCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ email: fpEmail, otp: otp })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            text.style.display = 'inline';
            spinner.style.display = 'none';

            if (data.success) {
                document.getElementById('fp_reset_email').value = fpEmail;
                document.getElementById('fp_reset_otp').value = otp;
                document.getElementById('fp-step-2').style.display = 'none';
                document.getElementById('fp-step-3').style.display = 'block';
                document.getElementById('fp-title').textContent = 'Create new password';
                document.getElementById('fp-subtitle').textContent = 'Your identity has been verified. Set a new password for your account.';
                document.getElementById('fp_new_password').focus();
            } else {
                document.getElementById('fp-otp-error').textContent = data.message || 'Incorrect code. Please try again.';
                document.getElementById('fp-otp-error').style.display = 'block';
            }
        })
        .catch(() => {
            btn.disabled = false;
            text.style.display = 'inline';
            spinner.style.display = 'none';
            document.getElementById('fp-otp-error').textContent = 'Network error. Please try again.';
            document.getElementById('fp-otp-error').style.display = 'block';
        });
    }

    function fpResendOtp() {
        if (fpResendCooldown > 0) return;
        fpSendOtpSilent();
    }

    function fpSendOtpSilent() {
        fetch('{{ route("password.send_otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fpCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ email: fpEmail })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                fpStartResendTimer(60);
                if (data.otp_preview) {
                    console.log('%c[DEV] New OTP: ' + data.otp_preview, 'color: #C08B7F; font-weight: bold; font-size: 14px;');
                }
            }
        });
    }

    function fpStartResendTimer(seconds) {
        fpResendCooldown = seconds;
        const link = document.getElementById('fp-resend-link');
        const timer = document.getElementById('fp-resend-timer');
        link.style.display = 'none';
        timer.style.display = 'inline';

        const interval = setInterval(() => {
            fpResendCooldown--;
            timer.textContent = '(Resend in ' + fpResendCooldown + 's)';
            if (fpResendCooldown <= 0) {
                clearInterval(interval);
                link.style.display = 'inline';
                timer.style.display = 'none';
            }
        }, 1000);
    }

    // OTP digit navigation
    function fpOtpNav(current, nextId, prevId) {
        const val = current.value;
        if (val && val.length === 1 && nextId) {
            document.getElementById(nextId).focus();
        }
        if (!val && prevId && event.key === 'Backspace') {
            document.getElementById(prevId).focus();
        }
    }

    function fpOtpPaste(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        for (let i = 0; i < pasted.length; i++) {
            const el = document.getElementById('otp' + (i + 1));
            if (el) el.value = pasted[i];
        }
        const nextFocus = document.getElementById('otp' + Math.min(pasted.length + 1, 6));
        if (nextFocus) nextFocus.focus();
    }

    function fpTogglePw(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (field.type === 'password') {
            field.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            field.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    // Client-side password validation before submit
    document.getElementById('fp-reset-form')?.addEventListener('submit', function(e) {
        const pw = document.getElementById('fp_new_password').value;
        const confirm = document.getElementById('fp_confirm_password').value;
        const errEl = document.getElementById('fp-pw-error');

        if (pw.length < 8) {
            e.preventDefault();
            errEl.textContent = 'Password must be at least 8 characters.';
            errEl.style.display = 'block';
            return;
        }
        if (pw !== confirm) {
            e.preventDefault();
            errEl.textContent = 'Passwords do not match.';
            errEl.style.display = 'block';
            return;
        }
        errEl.style.display = 'none';
    });
</script>
@endpush

@endsection
