@include('buyer.partials.settings-style')
<link rel="stylesheet" href="{{ asset('css/account-settings.css') }}?v={{ filemtime(public_path('css/account-settings.css')) }}">
@php
    $acctSections = [
        'personal'   => ['Personal details', 'M19 21v-2a7 7 0 0 0-14 0v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z'],
        'addresses'  => ['Delivery addresses', 'M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
        'id'         => ['ID verification', 'M3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z M9 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z M5.5 17a3.5 3.5 0 0 1 7 0 M15 10h3 M15 14h3'],
        'follows'    => ['Shops you follow', 'M3 10h18L19 3H5L3 10Z M5 10v11h14V10 M9 21v-7h6v7'],
        'complaints' => ['My complaints', 'M5 21V4 M5 4h11l-2 4 2 4H5'],
        'help'       => ['Help & support', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M5.6 5.6l3.9 3.9 M14.5 14.5l3.9 3.9 M18.4 5.6l-3.9 3.9 M9.5 14.5l-3.9 3.9'],
        'password'   => ['Password', 'M6 11h12v10H6Z M8.5 11V8a3.5 3.5 0 0 1 7 0v3 M12 15v2'],
        'activity'   => ['Account activity', 'M3 12a9 9 0 1 0 3-6.7L3 8 M3 3v5h5 M12 7v5l3 2'],
    ];
    // Sections that are still being built show a friendly placeholder.
    $acctComingSoon = [
        'follows'    => 'Keep track of the shops you love and see their newest drops in one place.',
        'complaints' => 'Follow up on problems you reported about your orders.',
        'help'       => 'Find answers to common questions or get in touch with the Cartzy team.',
        'activity'   => 'Review recent sign-ins and changes made to your account.',
    ];
    // Open the section a failed form came from, otherwise the one in the URL.
    $acctOpen = match (true) {
        $errors->addressBook->any() => 'addresses',
        $errors->devices->any() || $errors->hasAny(['current_password', 'password']) => 'password',
        $errors->hasAny(['id_type', 'id_number', 'id_photo']) => 'id',
        default => request('section', 'personal'),
    };
    if (!isset($acctSections[$acctOpen])) $acctOpen = 'personal';
    $acctInitial = mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1));
    $acctIdVerified = method_exists($user, 'isIdVerified') && $user->isIdVerified();
    $acctIdPending = ($user->id_status ?? '') === 'pending';
    $acctAge = $user->birthday ? \Carbon\Carbon::parse($user->birthday)->age : null;
@endphp

<div class="acct" data-acct>
    <aside class="acct-side">
        <div class="acct-who">
            <span class="acct-who-avatar">
                @if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="">@else{{ $acctInitial }}@endif
            </span>
            <div class="acct-who-text">
                <p class="acct-who-name">{{ $user->name }}</p>
                <p class="acct-who-email" title="{{ $user->email }}">{{ $user->email }}</p>
            </div>
        </div>
        <nav class="acct-nav" role="tablist" aria-label="Account settings" aria-orientation="vertical">
            @foreach($acctSections as $key => [$label, $icon])
                <button type="button" class="acct-nav-item" role="tab" id="acct-tab-{{ $key }}" data-acct-tab="{{ $key }}" aria-controls="acct-{{ $key }}" aria-selected="{{ $acctOpen === $key ? 'true' : 'false' }}" tabindex="{{ $acctOpen === $key ? '0' : '-1' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    <span>{{ $label }}</span>
                    @isset($acctComingSoon[$key])<span class="acct-soon-tag">Soon</span>@endisset
                </button>
            @endforeach
        </nav>
        <div class="acct-side-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="acct-nav-item acct-signout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9"/></svg>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="acct-main">
        {{-- Personal details --}}
        <section class="acct-card" id="acct-personal" role="tabpanel" aria-labelledby="acct-tab-personal" data-acct-section="personal" @if($acctOpen !== 'personal') hidden @endif>
            <header class="acct-head">
                <div>
                    <p class="acct-eyebrow">Account</p>
                    <h2 class="acct-title">Personal details</h2>
                </div>
                @if($acctIdVerified)
                    <span class="acct-badge acct-badge-verified"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm4.7 7.7-5.5 5.5a1 1 0 0 1-1.4 0l-2.5-2.5a1 1 0 1 1 1.4-1.4l1.8 1.8 4.8-4.8a1 1 0 1 1 1.4 1.4Z"/></svg>Verified Buyer</span>
                @elseif($acctIdPending)
                    <span class="acct-badge acct-badge-pending">Verification pending</span>
                @else
                    <span class="acct-badge acct-badge-unverified">Unverified buyer</span>
                @endif
            </header>

            <div class="acct-photo">
                <span class="acct-photo-avatar">
                    @if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">@else{{ $acctInitial }}@endif
                </span>
                <div class="acct-photo-body">
                    <div class="acct-photo-actions">
                        <form action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="avatar" id="acct-avatar-input" class="acct-file" accept="image/jpeg,image/png" data-acct-autosubmit>
                            <label for="acct-avatar-input" class="acct-btn acct-btn-primary" role="button" tabindex="0" data-acct-file-trigger>
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.4 4a2 2 0 0 0-1.7.9L6.9 6H5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-1.9l-.8-1.1a2 2 0 0 0-1.7-.9H9.4ZM12 9a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/></svg>
                                Change photo
                            </label>
                        </form>
                        @if($user->avatar)
                            <form action="{{ route('account.avatar.remove') }}" method="POST" onsubmit="return confirm('Remove your profile photo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="acct-btn acct-btn-outline">Remove</button>
                            </form>
                        @endif
                    </div>
                    <p class="acct-photo-hint">JPG or PNG, up to 2 MB.</p>
                </div>
            </div>

            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf
                <div class="acct-grid">
                    <label>
                        <span class="acct-label">Full name</span>
                        <input class="acct-input" name="name" autocomplete="name" maxlength="255" required value="{{ old('name', $user->name) }}">
                    </label>
                    <label>
                        <span class="acct-label">Mobile number</span>
                        <input class="acct-input" type="tel" name="phone" autocomplete="tel" maxlength="11" inputmode="numeric" pattern="[0-9]{1,11}" placeholder="09XXXXXXXXX" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" value="{{ old('phone', $user->phone) }}">
                    </label>
                    <label class="acct-field-wide">
                        <span class="acct-label">Email address</span>
                        <input class="acct-input" type="email" readonly value="{{ $user->email }}">
                        <span class="acct-hint">{{ $user->google_id ? 'Managed by your connected Google account.' : 'Your sign-in email address.' }}</span>
                    </label>
                    <label>
                        <span class="acct-label">Birthday</span>
                        <input class="acct-input" type="date" id="acct-birthday" name="birthday" autocomplete="bday" max="{{ now()->subYearsNoOverflow(18)->toDateString() }}" value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}">
                        <span class="acct-hint">Your birthday is used to keep your age accurate. You must be at least 18.</span>
                    </label>
                    <label>
                        <span class="acct-label">Current age</span>
                        <input class="acct-input" type="text" id="acct-age" readonly placeholder="Add your birthday" value="{{ $acctAge !== null ? $acctAge . ' years old' : '' }}">
                        <span class="acct-hint">Calculated automatically.</span>
                    </label>
                    <label>
                        <span class="acct-label">Sex</span>
                        <select name="sex" class="acct-input">
                            <option value="">Select</option>
                            @foreach(['Male', 'Female', 'Prefer not to say', 'Other'] as $sex)
                                <option value="{{ $sex }}" @selected(old('sex', $user->sex) === $sex)>{{ $sex }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="acct-footer">
                    <span>Keep your details up to date for easy deliveries.</span>
                    <button class="acct-btn acct-btn-primary" type="submit">Save changes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14 M13 6l6 6-6 6"/></svg></button>
                </div>
            </form>
        </section>

        {{-- Delivery addresses --}}
        <section class="acct-card" id="acct-addresses" role="tabpanel" aria-labelledby="acct-tab-addresses" data-acct-section="addresses" @if($acctOpen !== 'addresses') hidden @endif>
            <header class="acct-head">
                <div>
                    <p class="acct-eyebrow">Shipping</p>
                    <h2 class="acct-title">Delivery addresses</h2>
                    <p class="acct-lead">Add delivery locations and choose the one to use by default.</p>
                </div>
            </header>
            @include('buyer.partials.address-book')
        </section>

        {{-- ID verification --}}
        <section class="acct-card" id="acct-id" role="tabpanel" aria-labelledby="acct-tab-id" data-acct-section="id" @if($acctOpen !== 'id') hidden @endif>
            <header class="acct-head">
                <div>
                    <p class="acct-eyebrow">Trust &amp; safety</p>
                    <h2 class="acct-title">ID verification</h2>
                    <p class="acct-lead">A verified ID unlocks full checkout and buyer protection.</p>
                </div>
                @if($acctIdVerified)
                    <span class="acct-badge acct-badge-verified">Verified</span>
                @elseif($acctIdPending)
                    <span class="acct-badge acct-badge-pending">Pending review</span>
                @else
                    <span class="acct-badge acct-badge-unverified">Not submitted</span>
                @endif
            </header>
            <form action="{{ route('account.id.submit') }}" method="POST" enctype="multipart/form-data" class="acct-id">
                @csrf
                @include('buyer.partials.identity-verification')
                @unless($acctIdVerified || $acctIdPending)
                    <div class="acct-footer">
                        <span>Your ID is only used to verify your account.</span>
                        <button class="acct-btn acct-btn-primary" type="submit">Submit for review</button>
                    </div>
                @endunless
            </form>
        </section>

        {{-- Password & security --}}
        <section class="acct-card" id="acct-password" role="tabpanel" aria-labelledby="acct-tab-password" data-acct-section="password" @if($acctOpen !== 'password') hidden @endif>
            <header class="acct-head">
                <div>
                    <p class="acct-eyebrow">Security</p>
                    <h2 class="acct-title">Password</h2>
                    <p class="acct-lead">For your security, never share your password with anyone.</p>
                </div>
            </header>
            <div class="acct-row">
                <div><strong>Email verification</strong><span>{{ $user->email }}</span></div>
                <span class="acct-badge {{ $user->email_verified_at ? 'acct-badge-verified' : 'acct-badge-unverified' }}">{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</span>
            </div>
            @if($user->google_id)
                <p class="acct-lead">You can sign in with Google. If you have not set a password, sign out and choose Forgot password on the login screen to set one first.</p>
            @endif
            <div class="acct-divider"></div>
            <h3 class="acct-subhead">Change password</h3>
            <form action="{{ route('account.password.update') }}" method="POST">
                @csrf
                <div class="acct-grid">
                    <label class="acct-field-wide">
                        <span class="acct-label">Current password</span>
                        <input class="acct-input" type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<span class="acct-hint acct-hint-error">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="acct-label">New password</span>
                        <input class="acct-input" type="password" name="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="acct-hint acct-hint-error">{{ $message }}</span>@else<span class="acct-hint">Use at least 8 characters.</span>@enderror
                    </label>
                    <label>
                        <span class="acct-label">Confirm new password</span>
                        <input class="acct-input" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                    </label>
                </div>
                <div class="acct-footer">
                    <span>You will stay signed in on this device.</span>
                    <button class="acct-btn acct-btn-primary" type="submit">Update password</button>
                </div>
            </form>
            <div class="acct-divider"></div>
            <h3 class="acct-subhead">Other devices</h3>
            <p class="acct-lead">Sign out of your other sessions. You will stay signed in on this device.</p>
            @if($errors->devices->any())<p class="acct-hint acct-hint-error" role="alert">{{ $errors->devices->first() }}</p>@endif
            <form method="POST" action="{{ route('account.devices.logout') }}" class="acct-grid" style="margin-top: 18px; align-items: end;">
                @csrf
                <label>
                    <span class="acct-label">Confirm your password</span>
                    <input class="acct-input" type="password" name="device_password" autocomplete="current-password" required>
                </label>
                <div><button class="acct-btn acct-btn-danger" type="submit">Sign out other devices</button></div>
            </form>
        </section>

        {{-- Coming soon --}}
        @foreach($acctComingSoon as $key => $blurb)
            <section class="acct-card" id="acct-{{ $key }}" role="tabpanel" aria-labelledby="acct-tab-{{ $key }}" data-acct-section="{{ $key }}" @if($acctOpen !== $key) hidden @endif>
                <header class="acct-head">
                    <div>
                        <p class="acct-eyebrow">Account</p>
                        <h2 class="acct-title">{{ $acctSections[$key][0] }}</h2>
                    </div>
                </header>
                <div class="acct-soon">
                    <span class="acct-soon-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $acctSections[$key][1] }}"/></svg></span>
                    <h3>Coming soon</h3>
                    <p>{{ $blurb }} We're still building this part of Cartzy.</p>
                </div>
            </section>
        @endforeach
    </div>
</div>

<script>
(() => {
    const root = document.querySelector('[data-acct]');
    if (!root) return;
    const tabs = [...root.querySelectorAll('[data-acct-tab]')];
    const sections = [...root.querySelectorAll('[data-acct-section]')];

    const showSection = (key, focusTab = false) => {
        if (!sections.some((s) => s.dataset.acctSection === key)) key = 'personal';
        tabs.forEach((tab) => {
            const on = tab.dataset.acctTab === key;
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
            if (on && focusTab) tab.focus();
            if (on) tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        });
        sections.forEach((s) => { s.hidden = s.dataset.acctSection !== key; });
        const url = new URL(location.href);
        url.searchParams.set('section', key);
        history.replaceState(null, '', url);
    };
    window.showAccountSection = showSection;

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
            showSection(tab.dataset.acctTab);
            if (matchMedia('(max-width: 1024px)').matches) root.querySelector('.acct-main').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        // Arrow keys move between tabs.
        tab.addEventListener('keydown', (event) => {
            const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[event.key];
            if (!step) return;
            event.preventDefault();
            showSection(tabs[(i + step + tabs.length) % tabs.length].dataset.acctTab, true);
        });
    });

    // "Change photo" uploads as soon as a file is picked.
    root.querySelectorAll('[data-acct-autosubmit]').forEach((input) => {
        input.addEventListener('change', () => { if (input.files.length) input.form.submit(); });
    });
    root.querySelectorAll('[data-acct-file-trigger]').forEach((label) => {
        label.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); label.click(); }
        });
    });

    // Keep "Current age" in step with the birthday field.
    const birthday = document.getElementById('acct-birthday');
    const age = document.getElementById('acct-age');
    birthday?.addEventListener('input', () => {
        const born = new Date(`${birthday.value}T00:00:00`);
        if (!birthday.value || Number.isNaN(born.getTime())) { age.value = ''; return; }
        const today = new Date();
        let years = today.getFullYear() - born.getFullYear();
        const m = today.getMonth() - born.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < born.getDate())) years--;
        age.value = years >= 0 ? `${years} years old` : '';
    });
})();
</script>
