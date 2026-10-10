@include('buyer.partials.settings-style')
<header>
    <h2 class="text-2xl font-bold text-gray-900" style="font-family: 'Lato', sans-serif;">Account Settings</h2>
    <p class="mt-1 text-sm text-gray-500">Manage your profile, account security, and delivery preferences.</p>
</header>
<div class="settings-card">
    <div class="settings-section-heading"><h3>Profile information</h3><p>Keep your name and contact details up to date.</p></div>
    <form action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-4 mb-6">
        @csrf
        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#F1EFF5] text-xl font-bold text-[#564B68] ring-2 ring-[#E1DDE7]">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
            @else
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <label class="settings-label" for="settings-avatar">Profile photo</label>
            <input id="settings-avatar" class="max-w-full text-xs" type="file" name="avatar" accept="image/jpeg,image/png,image/jpg" required>
            <p class="mt-1 text-xs text-gray-500">JPG or PNG, up to 2 MB. Uploading a custom photo updates your profile picture.</p>
        </div>
        <button class="settings-button" type="submit">Upload photo</button>
    </form>
    <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="settings-label">Full name</span><input class="settings-input" name="name" autocomplete="name" maxlength="255" required value="{{ old('name', $user->name) }}"></label>
            <label><span class="settings-label">Phone number</span><input class="settings-input" type="tel" name="phone" autocomplete="tel" maxlength="20" value="{{ old('phone', $user->phone) }}"></label>
            <label class="sm:col-span-2"><span class="settings-label">Email address</span><input class="settings-input" type="email" readonly value="{{ $user->email }}"><span class="mt-1 block text-xs text-gray-500">{{ $user->google_id ? 'Connected to your Google account.' : 'Your sign-in email address.' }}</span></label>
            <label><span class="settings-label">Sex</span>
                <select name="sex" class="settings-input">
                    <option value="">Select</option>
                    @foreach(['Male', 'Female', 'Prefer not to say', 'Other'] as $sex)
                        <option value="{{ $sex }}" @selected(old('sex', $user->sex) === $sex)>{{ $sex }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="settings-label">Birthday</span><input class="settings-input" type="date" name="birthday" autocomplete="bday" max="{{ now()->subDay()->toDateString() }}" value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}"></label>
        </div>
        @include('buyer.partials.identity-verification')
        <button class="settings-button" type="submit">Save changes</button>
    </form>
</div>
<div class="settings-card">
    <div class="settings-section-heading"><h3>Security</h3><p>Review your verification status and manage account access.</p></div>
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
@include('buyer.partials.address-book')
