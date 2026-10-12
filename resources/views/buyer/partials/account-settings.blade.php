@include('buyer.partials.settings-style')
<div class="account-settings">
<header class="settings-page-heading">
    <div>
        <p class="settings-eyebrow">YOUR ACCOUNT, YOUR WAY</p>
        <h2>Account Settings<span>.</span></h2>
        <p>A few personal touches. A smoother shopping experience.</p>
    </div>
    <span class="settings-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z M8.5 12l2.5 2.5 4.5-5"/></svg></span>
</header>
<nav class="settings-shortcuts" aria-label="Account settings sections">
    <button type="button" onclick="document.getElementById('settings-profile').scrollIntoView({behavior:'smooth',block:'start'})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>Personal details<span>01</span></button>
    <button type="button" onclick="document.getElementById('settings-security').scrollIntoView({behavior:'smooth',block:'start'})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>Security<span>02</span></button>
    <button type="button" onclick="document.getElementById('saved-addresses-card').scrollIntoView({behavior:'smooth',block:'start'})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>Delivery addresses<span>03</span></button>
</nav>
<div class="settings-card settings-split-card" id="settings-profile">
    <div class="settings-section-heading"><span class="settings-section-number">01 / PROFILE</span><h3>Profile information</h3><p>Make yourself at home. Keep your name and contact details up to date.</p></div>
    <div class="settings-section-content">
    <form action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data" class="settings-photo-form">
        @csrf
        <div class="settings-photo-avatar">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
            @else
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div class="settings-photo-details">
            <label class="settings-label" for="settings-avatar">Profile photo</label>
            <input id="settings-avatar" class="settings-file-input" type="file" name="avatar" accept="image/jpeg,image/png,image/jpg" required aria-describedby="settings-photo-help">
            <p id="settings-photo-help" class="mt-2 text-xs text-gray-500">JPG or PNG, up to 2 MB.</p>
        </div>
        <button class="settings-button settings-button-secondary" type="submit">Upload photo</button>
    </form>
    <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="settings-label">Full name</span><input class="settings-input" name="name" autocomplete="name" maxlength="255" required value="{{ old('name', $user->name) }}"></label>
            <label><span class="settings-label">Phone number</span><input class="settings-input" type="tel" name="phone" autocomplete="tel" maxlength="11" inputmode="numeric" pattern="[0-9]{1,11}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" value="{{ old('phone', $user->phone) }}"></label>
            <label class="sm:col-span-2"><span class="settings-label">Email address</span><input class="settings-input" type="email" readonly value="{{ $user->email }}"><span class="mt-1 block text-xs text-gray-500">{{ $user->google_id ? 'Connected to your Google account.' : 'Your sign-in email address.' }}</span></label>
            <label><span class="settings-label">Sex</span>
                <select name="sex" class="settings-input">
                    <option value="">Select</option>
                    @foreach(['Male', 'Female', 'Prefer not to say', 'Other'] as $sex)
                        <option value="{{ $sex }}" @selected(old('sex', $user->sex) === $sex)>{{ $sex }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="settings-label">Birthday</span><input class="settings-input" type="date" name="birthday" autocomplete="bday" max="{{ now()->subYearsNoOverflow(18)->toDateString() }}" value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}"><span class="mt-1 block text-xs text-gray-500">You must be at least 18 years old.</span></label>
        </div>
        @include('buyer.partials.identity-verification')
        <div class="settings-form-footer"><span>Keep your details up to date for easy deliveries.</span><button class="settings-button" type="submit">Save changes <span aria-hidden="true">&rarr;</span></button></div>
    </form>
    </div>
</div>
<div class="settings-card settings-split-card" id="settings-security">
    <div class="settings-section-heading"><span class="settings-section-number">02 / SECURITY</span><h3>Security</h3><p>A little peace of mind. Manage your password, verification, and signed-in devices.</p></div>
    <div class="settings-section-content">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 rounded-xl bg-[#FAF9FC] p-4">
        <div><p class="text-sm font-semibold text-gray-800">Email verification</p><p class="text-xs text-gray-500 break-all">{{ $user->email }}</p></div>
        <span class="settings-status {{ $user->email_verified_at ? '!bg-emerald-50 !text-emerald-700 ring-1 ring-emerald-200/80' : '!bg-amber-50 !text-amber-700 ring-1 ring-amber-200/80' }}">{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</span>
    </div>
    @if($user->google_id)
        <p class="mb-5 text-sm text-gray-600">You can sign in with Google. If you have not set a password, sign out and choose Forgot password on the login screen to set one before using these controls.</p>
    @endif
    <form action="{{ route('account.password.update') }}" method="POST" class="space-y-4">
        @csrf
        <h4 class="text-sm font-bold text-gray-800" style="font-family: inherit;">Change password</h4>
        <label class="block"><span class="settings-label">Current password</span><input class="settings-input" type="password" name="current_password" autocomplete="current-password" required></label>
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="settings-label">New password</span><input class="settings-input" type="password" name="password" autocomplete="new-password" minlength="8" required><span class="mt-1 block text-xs text-gray-500">Use at least 8 characters.</span></label>
            <label><span class="settings-label">Confirm new password</span><input class="settings-input" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></label>
        </div>
        <button class="settings-button" type="submit">Update password</button>
    </form>
    <div class="mt-6 border-t border-gray-100 pt-6">
        <h4 class="text-sm font-bold text-gray-800" style="font-family: inherit;">Other devices</h4>
        <p class="mt-1 mb-4 text-sm text-gray-500">Sign out of your other sessions. You will stay signed in on this device.</p>
        @if($errors->devices->any())<div class="settings-error" role="alert">{{ $errors->devices->first() }}</div>@endif
        <form method="POST" action="{{ route('account.devices.logout') }}" class="space-y-4">
            @csrf
            <label class="block"><span class="settings-label">Confirm your password</span><input class="settings-input" type="password" name="device_password" autocomplete="current-password" required></label>
            <button class="settings-button" type="submit">Sign out other devices</button>
        </form>
    </div>
    </div>
</div>
@include('buyer.partials.address-book')
</div>
