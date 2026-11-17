@if(auth()->check() && !auth()->user()->isAdmin())
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
    <form method="POST"
          action="{{ route('account.delete') }}"
          onsubmit="return confirm('Are you sure you want to permanently delete your account? This cannot be undone.');"
          style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
        @csrf
        @method('DELETE')
        <label style="flex: 1 1 240px; display: flex; flex-direction: column; gap: 6px; margin: 0;">
            <span style="font-size: 12px; font-weight: 600; color: #374151;">Confirm with your Cartzy password</span>
            <span style="position: relative; display: block;">
                <input type="password"
                   name="password"
                   autocomplete="current-password"
                   placeholder="Enter your Cartzy password"
                   required
                   style="width: 100%; padding: 10px 48px 10px 12px; border-radius: 10px; border: 1px solid rgba(225, 29, 72, 0.35); background: #ffffff; color: #111827; font-size: 13px;">
                <button type="button"
                        aria-label="Show Cartzy password"
                        aria-pressed="false"
                        onclick="const input = this.parentElement.querySelector('input'); const visible = input.type === 'password'; input.type = visible ? 'text' : 'password'; this.setAttribute('aria-label', visible ? 'Hide Cartzy password' : 'Show Cartzy password'); this.setAttribute('aria-pressed', String(visible)); this.querySelector('[data-eye-slash]').style.display = visible ? 'block' : 'none';"
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
</section>
@endif
