@extends('layouts.admin')
@section('page_title', 'My account')
@section('page_description', 'Manage your administrator profile and sign-in credentials.')
@push('styles')
<style>
    .admin-password-field { position: relative; display: block; width: 100%; }
    .admin-password-field input { padding-right: 60px; }
    .admin-password-toggle svg { display: block; width: 20px; height: 20px; margin: auto; }
    .admin-password-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); min-width: 48px; min-height: 36px; padding: 4px 8px; border: 0; border-radius: 6px; background: transparent; color: var(--accent); font: inherit; font-weight: 600; cursor: pointer; }
    .admin-password-toggle:hover { background: var(--canvas); }
    .admin-password-toggle:focus-visible { outline: 2px solid var(--focus); outline-offset: 1px; }
</style>
@endpush
@section('content')
<div class="two-columns">
<section class="panel"><header class="panel-header"><div><h2>Profile details</h2><p>{{ $adminUser->email }}</p></div></header><form class="form-stack padded" action="{{ route('admin.account.update') }}" method="POST">@csrf<input type="hidden" name="section" value="profile">
<label>Name<input name="name" value="{{ old('name', $adminUser->name) }}" maxlength="255" required autocomplete="name"></label><label>Email<input type="email" name="email" value="{{ old('email', $adminUser->email) }}" maxlength="255" required autocomplete="email"></label><label>Phone<input name="phone" value="{{ old('phone', $adminUser->phone) }}" maxlength="30" autocomplete="tel"></label><button class="button">Save profile</button></form></section>
<section class="panel">
    <header class="panel-header">
        <div>
            <h2>Change password</h2>
            <p>Use at least 10 characters for your new password.</p>
        </div>
    </header>

    {{-- Standard Password Update Form --}}
    <form id="admin-standard-pw-form" class="form-stack padded" action="{{ route('admin.account.update') }}" method="POST">
        @csrf
        <input type="hidden" name="section" value="password">
        
        <label>
            <span style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span>Current password</span>
                <a href="javascript:void(0)" onclick="toggleAdminForgotMode()" class="text-link" style="font-size: 11px; font-weight: 600; color: var(--rose-ink); text-decoration: none;">Forgot password?</a>
            </span>
            <input type="password" name="current_password" required autocomplete="current-password">
            @error('current_password')
                <small style="color: #dc2626; margin-top: 4px;">{{ $message }} <a href="javascript:void(0)" onclick="toggleAdminForgotMode()" style="font-weight: 700; text-decoration: underline; color: inherit;">Reset with OTP instead</a></small>
            @enderror
        </label>
        
        <label>New password
            <input type="password" name="password" minlength="10" required autocomplete="new-password">
            @error('password')
                <small style="color: #dc2626; margin-top: 4px;">{{ $message }}</small>
            @enderror
        </label>
        
        <label>Confirm new password
            <input type="password" name="password_confirmation" minlength="10" required autocomplete="new-password">
        </label>
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <button class="button">Update password</button>
            <a href="{{ route('password.forgot', ['email' => $adminUser->email, 'portal' => 'admin']) }}" class="text-link" style="font-size: 11px; color: var(--muted);">Forgot current password?</a>
        </div>
    </form>

    {{-- In-place Forgot Password / OTP Reset Form --}}
    <div id="admin-forgot-pw-container" class="padded" style="display: none; border-top: 1px solid var(--line); background: var(--canvas);">
        <div style="padding: 13px 15px; background: var(--rose-soft); border: 1px solid var(--rose-border); border-radius: 10px; margin-bottom: 16px;">
            <div style="display: flex; align-items: flex-start; gap: 9px;">
                <svg style="width: 17px; height: 17px; color: var(--rose-ink); flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                </svg>
                <div style="font-size: 12px; color: var(--rose-ink); line-height: 1.5;">
                    <strong>Forgot your current password?</strong><br>
                    We can send a 6-digit verification code to <strong>{{ $adminUser->email }}</strong> to reset your password without entering your old password.
                </div>
            </div>
        </div>

        {{-- Step 1: Request Code --}}
        <div id="admin-fp-step-1">
            <button type="button" class="button" id="admin-fp-send-btn" onclick="adminSendResetOtp()" style="display: flex; margin-inline: auto; max-width: 100%;">
                <span id="admin-fp-send-text">Send verification code to {{ $adminUser->email }}</span>
                <span id="admin-fp-send-spinner" style="display: none;">Sending code...</span>
            </button>
            <p id="admin-fp-msg" style="margin-top: 10px; font-size: 12px; display: none;"></p>
        </div>

        {{-- Step 2: Enter OTP & Set New Password --}}
        <form id="admin-fp-step-2" action="{{ route('password.reset.submit') }}" method="POST" class="form-stack" style="display: none; margin-top: 12px;">
            @csrf
            <input type="hidden" name="portal" value="admin">
            <input type="hidden" name="email" value="{{ $adminUser->email }}">

            <label>
                <span style="display: flex; justify-content: space-between; align-items: center;">
                    <span>6-Digit Verification Code</span>
                    <a href="javascript:void(0)" onclick="adminSendResetOtp()" id="admin-fp-resend-btn" style="font-size: 11px; color: var(--rose-ink); font-weight: 600;">Resend code</a>
                </span>
                <input type="text" name="otp" id="admin-fp-otp" maxlength="6" inputmode="numeric" placeholder="Enter 6-digit code from email" required style="letter-spacing: 3px; font-weight: 700; font-size: 15px;">
            </label>

            <label>New password (at least 10 characters)
                <input type="password" name="password" minlength="10" placeholder="Enter new password" required autocomplete="new-password">
            </label>

            <label>Confirm new password
                <input type="password" name="password_confirmation" minlength="10" placeholder="Confirm new password" required autocomplete="new-password">
            </label>

            <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
                <button type="submit" class="button">Reset &amp; save new password</button>
                <button type="button" class="button subtle" onclick="toggleAdminForgotMode()">Cancel</button>
            </div>
        </form>

        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px dashed var(--line); font-size: 11px; color: var(--muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <a href="javascript:void(0)" onclick="toggleAdminForgotMode()" class="text-link">&larr; Return to regular change password</a>
            <a href="{{ route('password.forgot', ['email' => $adminUser->email, 'portal' => 'admin']) }}" target="_blank" class="text-link" style="color: var(--rose-ink);">Open dedicated reset page &nearr;</a>
        </div>
    </div>
</section>
</div><section class="panel"><header class="panel-header"><div><h2>Your recent activity</h2><p>Actions recorded for your administrator account</p></div></header>@include('admin.partials.activity')</section>

@push('scripts')
<script>
    document.querySelectorAll('#admin-standard-pw-form input[type="password"], #admin-fp-step-2 input[type="password"]').forEach((input, index) => {
        const wrapper = document.createElement('span');
        wrapper.className = 'admin-password-field';
        input.before(wrapper);
        wrapper.append(input);
        input.id ||= 'admin-password-' + index;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'admin-password-toggle';
        const eyeIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
        const eyeOffIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18M10.6 5.1A12 12 0 0 1 12 5c6.5 0 10 7 10 7a19 19 0 0 1-3.2 4.1M6.2 6.2A20 20 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.8-1.8M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
        toggle.innerHTML = eyeIcon;
        toggle.setAttribute('aria-controls', input.id);
        toggle.setAttribute('aria-label', 'Show ' + input.name.replaceAll('_', ' '));
        toggle.setAttribute('aria-pressed', 'false');
        toggle.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            toggle.innerHTML = visible ? eyeOffIcon : eyeIcon;
            toggle.setAttribute('aria-label', (visible ? 'Hide ' : 'Show ') + input.name.replaceAll('_', ' '));
            toggle.setAttribute('aria-pressed', String(visible));
        });
        wrapper.append(toggle);
    });

    function toggleAdminForgotMode() {
        const stdForm = document.getElementById('admin-standard-pw-form');
        const fpContainer = document.getElementById('admin-forgot-pw-container');
        const toggleBtnText = document.getElementById('admin-forgot-btn-text');
        
        if (fpContainer.style.display === 'none') {
            stdForm.style.display = 'none';
            fpContainer.style.display = 'block';
            if (toggleBtnText) toggleBtnText.textContent = 'Regular change password';
        } else {
            stdForm.style.display = 'flex';
            fpContainer.style.display = 'none';
            if (toggleBtnText) toggleBtnText.textContent = 'Forgot password?';
        }
    }

    function adminSendResetOtp() {
        const btn = document.getElementById('admin-fp-send-btn');
        const text = document.getElementById('admin-fp-send-text');
        const spinner = document.getElementById('admin-fp-send-spinner');
        const msg = document.getElementById('admin-fp-msg');
        
        if (btn) btn.disabled = true;
        if (text) text.style.display = 'none';
        if (spinner) spinner.style.display = 'inline';
        if (msg) msg.style.display = 'none';

        fetch('{{ route("password.send_otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: '{{ $adminUser->email }}' })
        })
        .then(r => r.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (text) text.style.display = 'inline';
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                document.getElementById('admin-fp-step-1').style.display = 'none';
                document.getElementById('admin-fp-step-2').style.display = 'flex';
                const otpInput = document.getElementById('admin-fp-otp');
                if (otpInput) otpInput.focus();
                if (data.otp_preview) {
                    console.log('%c[DEV] Admin OTP Preview: ' + data.otp_preview, 'color: #8C5A50; font-weight: bold; font-size: 14px;');
                }
            } else {
                if (msg) {
                    msg.style.display = 'block';
                    msg.style.color = '#dc2626';
                    msg.textContent = data.message || 'Could not send verification code.';
                }
            }
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            if (text) text.style.display = 'inline';
            if (spinner) spinner.style.display = 'none';
            if (msg) {
                msg.style.display = 'block';
                msg.style.color = '#dc2626';
                msg.textContent = 'Network error. Please try again.';
            }
        });
    }
</script>
@endpush
@endsection
