@if(auth()->check() && !auth()->user()->isAdmin())
<link rel="stylesheet" href="{{ asset('css/delete-account.css') }}?v={{ filemtime(public_path('css/delete-account.css')) }}">
<section id="delete-account-section" style="margin-top: 24px; padding: 20px 22px; border-radius: 16px; border: 1px solid rgba(225, 29, 72, 0.28); background: rgba(255, 241, 242, 0.55);">
    <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px;">
        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #be123c;">Danger Zone</span>
        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #881337;">Delete account</h3>
        <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #6b7280;">
            Permanently remove your account from Cartzy. This action cannot be undone. Accounts with active orders, pending deliveries, or unremitted COD cannot be deleted until resolved.
        </p>
    </div>

    @if($errors->deleteAccount->any())
        <div role="alert" style="margin-bottom: 14px; padding: 10px 14px; border-radius: 10px; border: 1px solid #fda4af; background: #fff1f2; color: #9f1239; font-size: 13px; font-weight: 600;">
            {{ $errors->deleteAccount->first() }}
        </div>
    @endif

    @if(auth()->user()->google_id)
        <p style="font-size:13px;margin-bottom:12px">Google sign-in does not give Cartzy your Google password. Confirm deletion with your own Cartzy password. If you have never set one, use the shortcut below to verify your email and create one.</p>
        <p style="margin-bottom:14px"><a href="{{ route('password.forgot', ['email' => auth()->user()->email, 'context' => 'account-deletion']) }}" style="text-decoration: underline; text-underline-offset: 3px; font-weight: 700;">Set or reset Cartzy password by email</a></p>
    @endif
    <form method="POST" data-delete-account-form
          action="{{ route('account.delete') }}"
          onsubmit="if (!this.dataset.confirmationReady) return confirm('Are you sure you want to permanently delete your Cartzy account? This action cannot be undone.');"
          style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
        @csrf
        @method('DELETE')
        <label style="flex: 1 1 240px; display: flex; flex-direction: column; gap: 6px; margin: 0;">
            <span style="font-size: 12px; font-weight: 600; color: #374151; text-decoration: underline; text-underline-offset: 3px;">Confirm with your Cartzy password</span>
            <span style="position: relative; display: block;">
                <input type="password"
                   name="password"
                   autocomplete="current-password"
                   placeholder="Enter your Cartzy password"
                   required
                   style="width: 100%; padding: 10px 48px 10px 12px; border-radius: 10px; border: 1px solid rgba(225, 29, 72, 0.35); background: #ffffff; color: #111827; font-size: 13px;">
                <button type="button"
                        data-delete-password-toggle
                        aria-label="Show Cartzy password"
                        aria-pressed="false"
                        style="position: absolute; right: 0; top: 0; bottom: 0; width: 44px; display: flex; align-items: center; justify-content: center; border: none; border-radius: 0 10px 10px 0; background: transparent; color: #6b7280; cursor: pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path data-eye-slash d="M3 3l18 18" style="display: none;"/>
                    </svg>
                </button>
            </span>
        </label>
        <button type="submit"
                style="padding: 10px 18px; border-radius: 10px; border: none; background: #be123c; color: #ffffff; font-size: 13px; font-weight: 700; cursor: pointer;">
            Delete Account
        </button>
    </form>
    <dialog id="delete-account-confirmation" class="delete-account-dialog" aria-labelledby="delete-account-confirm-title" aria-describedby="delete-account-confirm-description">
        <div class="delete-account-dialog-icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6M14 10v6"/></svg>
        </div>
        <h3 id="delete-account-confirm-title">Permanently delete your account?</h3>
        <p id="delete-account-confirm-description">Are you sure you want to permanently delete your Cartzy account? This action cannot be undone. You will lose access to this account.</p>
        <div class="delete-account-dialog-actions">
            <button type="button" data-delete-cancel autofocus>Cancel</button>
            <button type="button" data-delete-confirm>Yes, delete my account</button>
        </div>
    </dialog>
</section>
<script src="{{ asset('js/delete-account.js') }}?v={{ filemtime(public_path('js/delete-account.js')) }}" defer></script>
@endif
