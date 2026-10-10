@extends('layouts.auth')

@section('title', 'Registration Submitted | cartzy')

@section('auth_form')

<div style="text-align:center; padding: 12px 0 8px;">
    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px solid #a7f3d0;">
        <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:36px;height:36px;">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
    </div>

    <h2 class="auth-title" style="font-size:1.6rem;margin-bottom:10px;">Registration Submitted!</h2>
    <p style="font-size:0.95rem;color:#6b7280;margin-bottom:6px;">
        Thank you, <strong style="color:#111;">{{ $pending_name }}</strong>!
    </p>
    <p style="font-size:0.88rem;color:#6b7280;line-height:1.6;margin-bottom:24px;">
        Your registration has been submitted successfully. Please wait for the
        <strong style="color:#374151;">administrator's approval</strong>, which will be
        sent to your email:
    </p>

    <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:14px 20px;margin-bottom:28px;display:inline-block;">
        <span style="font-size:0.95rem;font-weight:700;color:#065f46;display:inline-flex;align-items:center;gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>{{ $pending_email ?: 'your registered email' }}</span>
        </span>
    </div>

    <div style="background:#F1EFF5;border:1.5px solid #E1DDE7;border-radius:10px;padding:14px 20px;margin-bottom:32px;text-align:left;">
        <p style="font-size:0.82rem;font-weight:700;color:#564B68;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>What happens next?</span>
        </p>
        <ul style="font-size:0.85rem;color:#6F6382;padding-left:18px;line-height:1.8;margin:0;">
            <li>Our team will review your submitted registration details.</li>
            <li>You will receive an email once your account is approved.</li>
            @if (($pending_role ?? '') === 'logistics')
                <li>Once approved, riders can apply to your company and you can manage deliveries on cartzy.</li>
            @else
                <li>Once approved, you can log in to start managing your store and selling products on cartzy.</li>
            @endif
        </ul>
    </div>

    <a href="{{ route('login') }}" class="btn-primary" style="display:inline-block;width:auto;padding:14px 40px;text-decoration:none;border-radius:9999px;">
        Back to Login
    </a>
</div>

<div class="auth-foot">
    Need help? <a href="mailto:administrationa570@gmail.com">Contact Support</a>
</div>

@endsection
