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

    <form method="POST"
          action="{{ route('account.delete') }}"
          onsubmit="return confirm('Are you sure you want to permanently delete your account? This cannot be undone.');"
          style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
        @csrf
        @method('DELETE')
        <label style="flex: 1 1 240px; display: flex; flex-direction: column; gap: 6px; margin: 0;">
            <span style="font-size: 12px; font-weight: 600; color: #374151;">Confirm with your current password</span>
            <input type="password"
                   name="password"
                   autocomplete="current-password"
                   placeholder="Enter your password"
                   required
                   style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(225, 29, 72, 0.35); background: #ffffff; color: #111827; font-size: 13px;">
        </label>
        <button type="submit"
                style="padding: 10px 18px; border-radius: 10px; border: none; background: #be123c; color: #ffffff; font-size: 13px; font-weight: 700; cursor: pointer;">
            Delete Account
        </button>
    </form>
</section>
@endif
