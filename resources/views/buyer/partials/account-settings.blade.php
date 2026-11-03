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
    ];
    $acctGroupIcons = [
        'security' => 'M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z',
        'account'  => 'M19 21v-2a7 7 0 0 0-14 0v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z',
        'orders'   => 'M5 7h14l1 14H4L5 7Z M9 7V5a3 3 0 0 1 6 0v2',
    ];
    $acctActivityDays = ($activityLogs ?? collect())->groupBy(fn ($log) => $log->created_at->toDateString());
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
        <nav class="acct-nav" aria-label="Account settings">
            @foreach($acctSections as $key => [$label, $icon])
                <a href="#acct-{{ $key }}" class="acct-nav-item" id="acct-tab-{{ $key }}" data-acct-tab="{{ $key }}" @if($acctOpen === $key) aria-current="true" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    <span>{{ $label }}</span>
                    @isset($acctComingSoon[$key])<span class="acct-soon-tag">Soon</span>@endisset
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="acct-main">
        {{-- Personal details --}}
        <section class="acct-card" id="acct-personal" data-acct-section="personal">
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
                            <input type="file" name="avatar" id="acct-avatar-input" class="acct-file" accept="image/jpeg,image/png" data-acct-photo-input>
                            <label for="acct-avatar-input" class="acct-btn acct-btn-primary" role="button" tabindex="0" data-acct-file-trigger>
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.4 4a2 2 0 0 0-1.7.9L6.9 6H5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-1.9l-.8-1.1a2 2 0 0 0-1.7-.9H9.4ZM12 9a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/></svg>
                                Change photo
                            </label>
                        </form>
                        @if($user->avatar)
                            <button type="button" class="acct-btn acct-btn-outline" data-acct-photo-edit="{{ route('account.avatar.source') }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.9 3.3a2.5 2.5 0 0 1 3.5 0l.3.3a2.5 2.5 0 0 1 0 3.5L9.4 18.4a2 2 0 0 1-.9.5l-4.2 1.2a1 1 0 0 1-1.2-1.2l1.2-4.2a2 2 0 0 1 .5-.9L16.9 3.3Z"/></svg>
                                Edit
                            </button>
                            <form action="{{ route('account.avatar.remove') }}" method="POST" onsubmit="return confirm('Remove your profile photo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="acct-btn acct-btn-outline">Remove</button>
                            </form>
                        @endif
                    </div>
                    <p class="acct-photo-hint">JPG or PNG. You can crop, zoom, and rotate it before saving.</p>
                </div>
            </div>

            {{-- Photo editor: drag to position, zoom, rotate, then save as a square JPEG. --}}
            <dialog class="acct-editor" id="acct-photo-editor" aria-labelledby="acct-editor-title">
                <div class="acct-editor-panel">
                    <header class="acct-editor-head">
                        <h3 id="acct-editor-title">Edit photo</h3>
                        <button type="button" class="acct-editor-close" data-acct-editor-cancel aria-label="Close">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                        </button>
                    </header>
                    <div class="acct-editor-stage" data-acct-editor-stage>
                        <canvas width="640" height="640" data-acct-editor-canvas aria-label="Photo preview. Drag to reposition."></canvas>
                        <span class="acct-editor-ring" aria-hidden="true"></span>
                        <p class="acct-editor-status" data-acct-editor-status hidden>Loading photo…</p>
                    </div>
                    <p class="acct-editor-tip">Drag the photo to position it inside the circle.</p>
                    <div class="acct-editor-controls">
                        <label class="acct-editor-zoom">
                            <span class="acct-label">Zoom</span>
                            <input type="range" min="1" max="4" step="0.01" value="1" data-acct-editor-zoom>
                        </label>
                        <div class="acct-editor-tools">
                            <button type="button" class="acct-editor-tool" data-acct-editor-rotate="-90" aria-label="Rotate left" title="Rotate left">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4v6h6"/><path d="M3.5 15a9 9 0 1 0 2.1-9.4L3 10"/></svg>
                            </button>
                            <button type="button" class="acct-editor-tool" data-acct-editor-rotate="90" aria-label="Rotate right" title="Rotate right">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 4v6h-6"/><path d="M20.5 15a9 9 0 1 1-2.1-9.4L21 10"/></svg>
                            </button>
                            <button type="button" class="acct-editor-tool" data-acct-editor-flip aria-label="Flip horizontally" title="Flip">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M8 7 3 17h5V7Z"/><path d="m16 7 5 10h-5V7Z"/></svg>
                            </button>
                            <button type="button" class="acct-editor-tool" data-acct-editor-reset aria-label="Reset" title="Reset">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        </div>
                    </div>
                    <footer class="acct-editor-foot">
                        <button type="button" class="acct-btn acct-btn-outline" data-acct-editor-cancel>Cancel</button>
                        <button type="button" class="acct-btn acct-btn-primary" data-acct-editor-save>Save photo</button>
                    </footer>
                </div>
            </dialog>

            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf
                <div class="acct-grid">
                    <label>
                        <span class="acct-label">Full name</span>
                        <input class="acct-input" name="name" autocomplete="name" maxlength="255" required value="{{ old('name', $user->name) }}">
                    </label>
                    <label>
                        <span class="acct-label">Mobile number</span>
                        <input class="acct-input @error('phone') is-invalid @enderror" type="tel" name="phone" id="acct-phone" autocomplete="tel" maxlength="13" inputmode="tel" pattern="09[0-9]{9}|\+639[0-9]{9}" placeholder="09XXXXXXXXX or +639XXXXXXXXX" aria-describedby="acct-phone-error" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" value="{{ old('phone', $user->phone) }}">
                        <span class="acct-hint acct-hint-error" id="acct-phone-error" role="alert" @unless($errors->has('phone')) hidden @endunless>{{ $errors->first('phone') }}</span>
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
        <section class="acct-card" id="acct-addresses" data-acct-section="addresses">
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
        <section class="acct-card" id="acct-id" data-acct-section="id">
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

        {{-- Coming soon --}}
        @foreach($acctComingSoon as $key => $blurb)
            <section class="acct-card acct-card-soon" id="acct-{{ $key }}" data-acct-section="{{ $key }}">
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

        {{-- Password & security --}}
        <section class="acct-card" id="acct-password" data-acct-section="password">
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

        {{-- Account activity --}}
        <section class="acct-card" id="acct-activity" data-acct-section="activity">
            <header class="acct-head">
                <div>
                    <p class="acct-eyebrow">Security</p>
                    <h2 class="acct-title">Account activity</h2>
                    <p class="acct-lead">Sign-ins, account changes and orders from the website and the Cartzy app. Don't recognise something? Change your password right away.</p>
                </div>
            </header>
            @php($acctGroup = $activityGroup ?? 'all')
            @if($acctActivityDays->isEmpty() && $acctGroup === 'all')
                <div class="acct-soon">
                    <span class="acct-soon-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $acctSections['activity'][1] }}"/></svg></span>
                    <h3>No activity yet</h3>
                    <p>Your sign-ins and account changes will show up here.</p>
                </div>
            @else
                <div class="acct-filters" role="group" aria-label="Filter activity">
                    @foreach(['all' => 'All', 'security' => 'Sign-ins & security', 'account' => 'Account changes', 'orders' => 'Orders'] as $filter => $filterLabel)
                        <a class="acct-chip" href="{{ route('buyer.dashboard', array_filter(['tab' => 'settings', 'section' => 'activity', 'activity_group' => $filter === 'all' ? null : $filter])) }}" @if($acctGroup === $filter) aria-current="true" @endif>{{ $filterLabel }}</a>
                    @endforeach
                </div>
                @foreach($acctActivityDays as $day => $dayLogs)
                    @php($dayDate = \Carbon\Carbon::parse($day))
                    <div class="acct-day">
                        <h3 class="acct-day-label">{{ $dayDate->isToday() ? 'Today' : ($dayDate->isYesterday() ? 'Yesterday' : $dayDate->format('F j, Y')) }}</h3>
                        <ol class="acct-log-list">
                            @foreach($dayLogs as $log)
                                @php($entry = $log->toFeedArray())
                                <li class="acct-log {{ $log->event === 'failed_login' ? 'acct-log-alert' : '' }}" data-group="{{ $entry['group'] }}">
                                    <span class="acct-log-icon acct-log-{{ $entry['group'] }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $acctGroupIcons[$entry['group']] }}"/></svg></span>
                                    <div class="acct-log-body">
                                        <strong>{{ $entry['title'] }}</strong>
                                        @if($entry['description'])<p>{{ $entry['description'] }}</p>@endif
                                        <small><span class="acct-log-channel">{{ $entry['channel'] === 'mobile' ? 'Mobile app' : 'Website' }}</span> {{ $entry['device'] }}@if($entry['ip_address']) · IP {{ $entry['ip_address'] }}@endif</small>
                                    </div>
                                    <time datetime="{{ $entry['created_at'] }}" title="{{ $log->created_at->format('F j, Y g:i A') }}">{{ $log->created_at->format('g:i A') }}</time>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
                @if($activityLogs->isEmpty())
                    <p class="acct-hint">No activity of this kind yet.</p>
                @else
                    {{-- 10 entries per page --}}
                    <nav class="acct-pager" aria-label="Activity pages">
                        <span class="acct-pager-summary">Showing {{ $activityLogs->firstItem() }}–{{ $activityLogs->lastItem() }} of {{ $activityLogs->total() }}</span>
                        @if($activityLogs->hasPages())
                            <div class="acct-pager-controls">
                                @if($activityLogs->onFirstPage())
                                    <span class="acct-btn acct-btn-outline is-disabled" aria-disabled="true">&larr; Previous</span>
                                @else
                                    <a class="acct-btn acct-btn-outline" href="{{ $activityLogs->previousPageUrl() }}" rel="prev">&larr; Previous</a>
                                @endif
                                <span class="acct-pager-page">Page {{ $activityLogs->currentPage() }} of {{ $activityLogs->lastPage() }}</span>
                                @if($activityLogs->hasMorePages())
                                    <a class="acct-btn acct-btn-outline" href="{{ $activityLogs->nextPageUrl() }}" rel="next">Next &rarr;</a>
                                @else
                                    <span class="acct-btn acct-btn-outline is-disabled" aria-disabled="true">Next &rarr;</span>
                                @endif
                            </div>
                        @endif
                    </nav>
                @endif
            @endif
        </section>

    </div>
</div>

<script>
(() => {
    const root = document.querySelector('[data-acct]');
    if (!root) return;
    const tabs = [...root.querySelectorAll('[data-acct-tab]')];
    const sections = [...root.querySelectorAll('[data-acct-section]')];

    // All sections live on one page: the sidebar scrolls to a section and follows along as you scroll.
    const setActive = (key) => tabs.forEach((tab) => {
        if (tab.dataset.acctTab === key) tab.setAttribute('aria-current', 'true');
        else tab.removeAttribute('aria-current');
    });
    const showSection = (key, smooth = true) => {
        const section = sections.find((s) => s.dataset.acctSection === key);
        if (!section) return;
        setActive(key);
        section.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        const url = new URL(location.href);
        url.searchParams.set('section', key);
        history.replaceState(null, '', url);
    };
    window.showAccountSection = showSection;

    tabs.forEach((tab) => tab.addEventListener('click', (event) => {
        event.preventDefault();
        showSection(tab.dataset.acctTab);
    }));

    // Highlight the section nearest the top of the screen.
    const visible = new Map();
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => visible.set(entry.target, entry.isIntersecting));
        const current = sections.find((s) => visible.get(s));
        if (current) setActive(current.dataset.acctSection);
    }, { rootMargin: '-140px 0px -55% 0px' });
    sections.forEach((s) => observer.observe(s));

    // Jump to the section named in the URL (or the one a failed form came from) once the dashboard has settled.
    const opened = @json($acctOpen);
    if (opened !== 'personal') setTimeout(() => showSection(opened, false), 350);

    // Photo editor: a picked file (or the current photo, via "Edit") opens in the editor, and "Save photo"
    // uploads the circle's contents as a 640px square JPEG through the same avatar form.
    const editor = document.getElementById('acct-photo-editor');
    const photoInput = root.querySelector('[data-acct-photo-input]');
    if (editor && photoInput) {
        const canvas = editor.querySelector('[data-acct-editor-canvas]');
        const ctx = canvas.getContext('2d');
        const zoomInput = editor.querySelector('[data-acct-editor-zoom]');
        const status = editor.querySelector('[data-acct-editor-status]');
        const saveButton = editor.querySelector('[data-acct-editor-save]');
        const size = canvas.width;
        let image = null;
        let state = { zoom: 1, rotation: 0, flip: 1, x: 0, y: 0 };

        // Size of the image on the canvas at zoom 1: just large enough to cover the whole square.
        const baseScale = () => {
            const turned = state.rotation % 180 !== 0;
            const w = turned ? image.height : image.width;
            const h = turned ? image.width : image.height;
            return size / Math.min(w, h);
        };
        // Keep the photo covering the square so no empty corners show.
        const clampOffset = () => {
            const turned = state.rotation % 180 !== 0;
            const scale = baseScale() * state.zoom;
            const w = (turned ? image.height : image.width) * scale;
            const h = (turned ? image.width : image.height) * scale;
            const maxX = Math.max(0, (w - size) / 2);
            const maxY = Math.max(0, (h - size) / 2);
            state.x = Math.min(maxX, Math.max(-maxX, state.x));
            state.y = Math.min(maxY, Math.max(-maxY, state.y));
        };
        const draw = () => {
            ctx.clearRect(0, 0, size, size);
            if (!image) return;
            clampOffset();
            const scale = baseScale() * state.zoom;
            ctx.save();
            ctx.translate(size / 2 + state.x, size / 2 + state.y);
            ctx.rotate((state.rotation * Math.PI) / 180);
            ctx.scale(scale * state.flip, scale);
            ctx.drawImage(image, -image.width / 2, -image.height / 2);
            ctx.restore();
        };
        const reset = () => {
            state = { zoom: 1, rotation: 0, flip: 1, x: 0, y: 0 };
            zoomInput.value = 1;
            draw();
        };
        const setStatus = (text) => {
            status.hidden = !text;
            status.textContent = text || '';
            saveButton.disabled = Boolean(text);
        };
        const open = (src, revoke) => {
            image = null;
            draw();
            setStatus('Loading photo…');
            if (!editor.open) editor.showModal();
            const img = new Image();
            img.onload = () => {
                if (revoke) URL.revokeObjectURL(src);
                image = img;
                setStatus('');
                reset();
            };
            img.onerror = () => setStatus('This photo could not be opened. Try another JPG or PNG.');
            img.src = src;
        };
        const close = () => {
            photoInput.value = '';
            editor.close();
        };

        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (file) open(URL.createObjectURL(file), true);
        });
        root.querySelector('[data-acct-photo-edit]')?.addEventListener('click', (event) => {
            open(event.currentTarget.dataset.acctPhotoEdit, false);
        });

        zoomInput.addEventListener('input', () => {
            state.zoom = Number(zoomInput.value);
            draw();
        });
        editor.querySelectorAll('[data-acct-editor-rotate]').forEach((button) => button.addEventListener('click', () => {
            state.rotation = (state.rotation + Number(button.dataset.acctEditorRotate) + 360) % 360;
            draw();
        }));
        editor.querySelector('[data-acct-editor-flip]').addEventListener('click', () => {
            state.flip *= -1;
            draw();
        });
        editor.querySelector('[data-acct-editor-reset]').addEventListener('click', reset);
        editor.querySelectorAll('[data-acct-editor-cancel]').forEach((button) => button.addEventListener('click', close));
        editor.addEventListener('cancel', (event) => { event.preventDefault(); close(); });

        // Drag (mouse or touch) to move the photo, and scroll to zoom.
        let drag = null;
        canvas.addEventListener('pointerdown', (event) => {
            if (!image) return;
            drag = { id: event.pointerId, x: event.clientX, y: event.clientY };
            canvas.setPointerCapture(event.pointerId);
        });
        canvas.addEventListener('pointermove', (event) => {
            if (!drag || drag.id !== event.pointerId) return;
            const ratio = size / canvas.getBoundingClientRect().width;
            state.x += (event.clientX - drag.x) * ratio;
            state.y += (event.clientY - drag.y) * ratio;
            drag.x = event.clientX;
            drag.y = event.clientY;
            draw();
        });
        ['pointerup', 'pointercancel'].forEach((type) => canvas.addEventListener(type, () => { drag = null; }));
        canvas.addEventListener('wheel', (event) => {
            if (!image) return;
            event.preventDefault();
            state.zoom = Math.min(4, Math.max(1, state.zoom - event.deltaY * 0.002));
            zoomInput.value = state.zoom;
            draw();
        }, { passive: false });

        saveButton.addEventListener('click', () => {
            if (!image) return;
            saveButton.disabled = true;
            saveButton.textContent = 'Saving…';
            // JPEG has no transparency, so paint white behind the photo first.
            const out = document.createElement('canvas');
            out.width = out.height = size;
            const outCtx = out.getContext('2d');
            outCtx.fillStyle = '#fff';
            outCtx.fillRect(0, 0, size, size);
            outCtx.drawImage(canvas, 0, 0);
            out.toBlob((blob) => {
                const files = new DataTransfer();
                files.items.add(new File([blob], 'avatar.jpg', { type: 'image/jpeg' }));
                photoInput.files = files.files;
                photoInput.form.submit();
            }, 'image/jpeg', 0.9);
        });
    }
    root.querySelectorAll('[data-acct-file-trigger]').forEach((label) => {
        label.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); label.click(); }
        });
    });

    // Mobile number: show the red message under the field (like sign-up) instead of the browser's popup.
    const phone = document.getElementById('acct-phone');
    const phoneError = document.getElementById('acct-phone-error');
    if (phone && phoneError) {
        const phoneMessage = 'Enter a valid mobile number: 09 followed by 9 digits, or +639 followed by 9 digits.';
        const showPhoneError = (show) => {
            phone.classList.toggle('is-invalid', show);
            phone.setAttribute('aria-invalid', show ? 'true' : 'false');
            phoneError.hidden = !show;
            if (show) phoneError.textContent = phoneMessage;
        };
        phone.addEventListener('input', () => {
            phone.value = phone.value.replace(/(?!^\+)[^\d]/g, '').slice(0, phone.value.startsWith('+') ? 13 : 11);
            if (phone.classList.contains('is-invalid')) showPhoneError(!phone.validity.valid);
        });
        phone.addEventListener('blur', () => showPhoneError(phone.value !== '' && !phone.validity.valid));
        phone.addEventListener('invalid', (event) => {
            event.preventDefault();
            showPhoneError(true);
            phone.focus();
        });
    }

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
