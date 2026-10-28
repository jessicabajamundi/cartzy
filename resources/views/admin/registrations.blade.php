@extends('layouts.admin')
@section('page_title', 'Store & Partner Registrations')
@section('page_description', 'Review and approve onboarding applications for new merchant stores (Sellers), delivery riders (Couriers), and logistics hubs.')

@section('content')
<!-- Quick Navigation Switcher between Registrations and ID Checks -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 14px;">
    <a href="{{ route('admin.registrations') }}" class="button" style="font-size: 12px; gap: 8px; border-radius: 20px; min-height: 38px; padding: 6px 16px; background: var(--accent); border-color: var(--accent); color: #fff;">
        @include('admin.partials.icon', ['name' => 'file'])
        <span>Store & Partner Registrations</span>
        @if(!empty($adminPendingRegistrationsCount))
            <span class="badge" style="background: #fff; color: var(--accent-hover); font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">{{ $adminPendingRegistrationsCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.id-checks') }}" class="button subtle" style="font-size: 12px; gap: 8px; border-radius: 20px; min-height: 38px; padding: 6px 16px;">
        @include('admin.partials.icon', ['name' => 'shield'])
        <span>Government ID Checks</span>
        @if(!empty($adminPendingIdChecksCount))
            <span class="badge warning" style="background: #EFF6FF; color: #1E40AF; font-size: 10px; padding: 2px 7px; border-radius: 10px;">{{ $adminPendingIdChecksCount }}</span>
        @endif
    </a>
</div>

<!-- Top KPI Metric Cards for Registrations -->
<div class="stats-grid dashboard-stats">
    <a class="stat-card" href="{{ route('admin.registrations') }}">
        <div class="metric-heading">
            <span class="metric-icon">@include('admin.partials.icon', ['name' => 'file'])</span>
            <span>All Registrations</span>
        </div>
        <span>Total Partner Applications</span>
        <strong>{{ number_format($statusCounts['all']) }}</strong>
        <small>Total merchant, courier & hub accounts</small>
    </a>

    <a class="stat-card" href="{{ route('admin.registrations', ['status' => 'pending']) }}" style="@if($statusCounts['pending'] > 0) border-top-color: #D97706; background: #FFFDF5; @endif">
        <div class="metric-heading">
            <span class="metric-icon" style="@if($statusCounts['pending'] > 0) background: #FEF3C7; color: #B45309; @endif">@include('admin.partials.icon', ['name' => 'file'])</span>
            <span style="@if($statusCounts['pending'] > 0) color: #B45309; font-weight: 700; @endif">Needs Action</span>
        </div>
        <span>Pending Onboarding</span>
        <strong style="@if($statusCounts['pending'] > 0) color: #B45309; @endif">{{ number_format($statusCounts['pending']) }}</strong>
        <small>{{ $statusCounts['pending'] > 0 ? 'Applications awaiting business approval →' : 'No applications in queue' }}</small>
    </a>

    <a class="stat-card" href="{{ route('admin.registrations', ['status' => 'active']) }}">
        <div class="metric-heading">
            <span class="metric-icon" style="background: #E8F5E9; color: #2E7D32;">@include('admin.partials.icon', ['name' => 'box'])</span>
            <span>Active Partners</span>
        </div>
        <span>Approved Stores & Couriers</span>
        <strong>{{ number_format($statusCounts['active']) }}</strong>
        <small>Authorized partners actively operating</small>
    </a>

    <a class="stat-card" href="{{ route('admin.registrations', ['status' => 'rejected']) }}">
        <div class="metric-heading">
            <span class="metric-icon" style="background: var(--rose-light); color: var(--rose-ink);">@include('admin.partials.icon', ['name' => 'archive'])</span>
            <span>Disapproved</span>
        </div>
        <span>Rejected Applications</span>
        <strong>{{ number_format($statusCounts['rejected']) }}</strong>
        <small>Applications rejected with compliance notes</small>
    </a>
</div>

<!-- Main Section -->
<section class="panel">
    <!-- Filter Toolbar -->
    <form method="GET" class="filters" action="{{ route('admin.registrations') }}">
        <label class="search-field">Search
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by merchant name, business name, email, phone, or TIN">
        </label>

        <label>Application Status
            <select name="status">
                <option value="">All statuses ({{ $statusCounts['all'] }})</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending Approval ({{ $statusCounts['pending'] }})</option>
                <option value="active" @selected(request('status') === 'active')>Active & Approved ({{ $statusCounts['active'] }})</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected ({{ $statusCounts['rejected'] }})</option>
            </select>
        </label>

        <label>Partner Role
            <select name="role">
                <option value="">All Partner Roles</option>
                <option value="seller" @selected(request('role') === 'seller')>Seller (Merchant Store)</option>
                <option value="courier" @selected(request('role') === 'courier')>Courier (Delivery Rider)</option>
                <option value="logistics" @selected(request('role') === 'logistics')>Logistics (Hub Operator)</option>
            </select>
        </label>

        <label>Seller Classification
            <select name="seller_type">
                <option value="">All Classifications</option>
                <option value="sole_proprietorship" @selected(request('seller_type') === 'sole_proprietorship')>Sole Proprietorship</option>
                <option value="corporation" @selected(request('seller_type') === 'corporation')>Corporation</option>
                <option value="partnership" @selected(request('seller_type') === 'partnership')>Partnership</option>
            </select>
        </label>

        <div style="display: flex; gap: 8px;">
            <button class="button" type="submit">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.registrations') }}">Reset</a>
        </div>
    </form>

    <!-- Registrations Table -->
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="min-width: 250px;">Applicant Account</th>
                    <th style="min-width: 240px;">Business & Store Profile</th>
                    <th style="min-width: 200px;">Permits & Documents</th>
                    <th style="min-width: 170px;">Onboarding Status</th>
                    <th style="min-width: 220px;">Review Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($registrations as $user)
                @php
                    $isPending = $user->status === 'pending';
                    $isActive = $user->status === 'active';
                    $isRejected = $user->status === 'rejected';

                    $dtiUrl = null;
                    if ($user->dti_permit) {
                        $dtiUrl = (str_starts_with($user->dti_permit, 'http') || str_starts_with($user->dti_permit, '/'))
                            ? $user->dti_permit
                            : Storage::disk('public')->url($user->dti_permit);
                    }

                    $idDocUrl = null;
                    if ($user->id_photo) {
                        $idDocUrl = (str_starts_with($user->id_photo, 'http') || str_starts_with($user->id_photo, '/'))
                            ? $user->id_photo
                            : Storage::disk('public')->url($user->id_photo);
                    }

                    $secUrl = $user->sec_certificate ? route('registration.document', [$user->id, 'sec_certificate']) : null;
                    $birUrl = $user->bir_certificate ? route('registration.document', [$user->id, 'bir_certificate']) : null;

                    $avatarUrl = $user->avatar_url;
                    $initials = mb_strtoupper(mb_substr($user->name, 0, 1));

                    $roleBadgeStyle = match($user->role) {
                        'seller' => 'background: #F3EFF7; color: #564B68; border: 1px solid #E1DDE7;',
                        'courier' => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE;',
                        'logistics' => 'background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0;',
                        default => 'background: #F4F4F5; color: #3F3F46; border: 1px solid #E4E4E7;',
                    };

                    $addressFull = collect([
                        $user->street_address,
                        $user->barangay,
                        $user->city,
                        $user->province,
                        $user->postal_code,
                    ])->filter()->join(', ');
                    if (empty($addressFull)) {
                        $addressFull = $user->address ?: 'No business address specified';
                    }

                    $regPayload = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?: ($user->telephone ?: '—'),
                        'role' => $user->role,
                        'role_label' => ucfirst($user->role),
                        'avatar' => $avatarUrl,
                        'initials' => $initials,
                        'business_name' => $user->business_name ?: ($user->seller?->name ?? 'No store name specified'),
                        'tin_number' => $user->tin_number ?: 'Not supplied',
                        'seller_type' => $user->seller_type ? ucwords(str_replace('_', ' ', $user->seller_type)) : 'Individual / Unspecified',
                        'line_of_business' => $user->line_of_business ?: 'General Merchandise',
                        'address' => $addressFull,
                        'id_photo' => $idDocUrl,
                        'id_type' => $user->id_type ?: 'Government ID',
                        'id_number' => $user->id_number ?: '—',
                        'id_status' => $user->id_status,
                        'dti_permit' => $dtiUrl,
                        'sec_certificate' => $secUrl,
                        'bir_certificate' => $birUrl,
                        'status' => $user->status,
                        'rejection_reason' => $user->id_rejection_reason ?: ($user->seller?->rejection_reason ?? null),
                        'created_at' => $user->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
                        'status_update_url' => route('admin.registrations.status', $user->id),
                        'id_checks_url' => route('admin.id-checks', ['search' => $user->email]),
                        'is_pending' => $isPending,
                        'is_active' => $isActive,
                        'is_rejected' => $isRejected,
                        'has_id_doc' => (bool) $user->id_photo,
                    ];
                @endphp
                <tr>
                    <!-- 1. Applicant Account -->
                    <td>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line); flex-shrink: 0; background: #fff;">
                            @else
                                <div class="avatar" style="width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; font-size: 16px;">
                                    {{ $initials }}
                                </div>
                            @endif

                            <div style="min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 3px;">
                                    <strong style="font-size: 13px; line-height: 1.3;">{{ $user->name }}</strong>
                                    <span class="badge" style="{{ $roleBadgeStyle }} font-size: 9px; padding: 1px 7px;">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>

                                <small class="muted" style="display: block; word-break: break-all;">
                                    {{ $user->email }}
                                </small>

                                @if($user->phone || $user->telephone)
                                    <small style="color: var(--ink); margin-top: 2px; display: block;">
                                        📞 {{ $user->phone ?: $user->telephone }}
                                    </small>
                                @endif

                                <small class="muted" style="margin-top: 4px; display: block; font-size: 10px;">
                                    Applied {{ $user->created_at?->timezone('Asia/Manila')->format('M j, Y') }}
                                </small>
                            </div>
                        </div>
                    </td>

                    <!-- 2. Business & Store Profile -->
                    <td>
                        <div>
                            @if($user->business_name)
                                <strong style="font-size: 13px; color: var(--ink);">
                                    🏪 {{ $user->business_name }}
                                </strong>
                            @else
                                <span class="muted" style="font-size: 12px;">No Store Name Stated</span>
                            @endif

                            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
                                @if($user->seller_type)
                                    <span class="badge" style="background: var(--accent-light); color: var(--accent); font-size: 9px;">
                                        {{ ucwords(str_replace('_', ' ', $user->seller_type)) }}
                                    </span>
                                @endif
                                @if($user->line_of_business)
                                    <span class="badge" style="background: #FFFDF5; border: 1px solid #FDE68A; color: #92400E; font-size: 9px;">
                                        {{ $user->line_of_business }}
                                    </span>
                                @endif
                            </div>

                            @if($user->tin_number)
                                <div style="margin-top: 5px; font-size: 11px;">
                                    <small class="muted">TIN:</small>
                                    <code>{{ $user->tin_number }}</code>
                                </div>
                            @endif

                            @if($addressFull !== 'No business address specified')
                                <div style="margin-top: 4px; font-size: 11px; color: var(--muted); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $addressFull }}">
                                    📍 {{ $addressFull }}
                                </div>
                            @endif
                        </div>
                    </td>

                    <!-- 3. Permits & Documents -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                            @if($dtiUrl)
                                <a href="{{ $dtiUrl }}" target="_blank" rel="noopener" class="text-link" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span>📄 DTI Business Permit</span> ↗
                                </a>
                            @endif

                            @if($secUrl)
                                <a href="{{ $secUrl }}" target="_blank" rel="noopener" class="text-link" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span>📄 SEC Certificate</span> ↗
                                </a>
                            @endif

                            @if($birUrl)
                                <a href="{{ $birUrl }}" target="_blank" rel="noopener" class="text-link" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span>📄 BIR Form 2303</span> ↗
                                </a>
                            @endif

                            @if($idDocUrl)
                                <div style="margin-top: 4px;">
                                    <a href="{{ route('admin.id-checks', ['search' => $user->email]) }}" class="button subtle" style="padding: 2px 8px; font-size: 10px; min-height: 26px; border-radius: 6px; display: inline-flex; gap: 4px;">
                                        @include('admin.partials.icon', ['name' => 'shield'])
                                        <span>Check Gov't ID ({{ $user->id_type ?: 'ID' }}) ↗</span>
                                    </a>
                                </div>
                            @else
                                <small class="muted" style="color: #DC2626;">⚠️ No Gov't ID attached</small>
                            @endif

                            @if(!$dtiUrl && !$secUrl && !$birUrl && !$idDocUrl)
                                <span class="muted" style="font-size: 11px;">No business documents uploaded</span>
                            @endif
                        </div>
                    </td>

                    <!-- 4. Onboarding Status -->
                    <td>
                        @if($isPending)
                            <span class="badge warning" style="background: #FEF3C7; color: #B45309; font-weight: 700; padding: 4px 10px;">
                                ⏳ Pending Onboarding
                            </span>
                            <small class="muted" style="margin-top: 5px; display: block;">Awaiting approval to sell</small>
                        @elseif($isActive)
                            <span class="badge good" style="background: #E8F5E9; color: #2E7D32; font-weight: 700; padding: 4px 10px;">
                                ✓ Approved & Active
                            </span>
                            <small class="muted" style="margin-top: 5px; display: block;">Operating merchant account</small>
                        @elseif($isRejected)
                            <span class="badge danger" style="background: var(--rose-light); color: var(--rose-ink); font-weight: 700; padding: 4px 10px;">
                                ✕ Disapproved / Rejected
                            </span>
                            @if($regPayload['rejection_reason'])
                                <div style="margin-top: 6px; padding: 6px 9px; background: #FFF5F3; border-left: 2px solid var(--rose-border); border-radius: 4px; font-size: 11px; color: var(--rose-ink); line-height: 1.35;">
                                    <strong>Reason:</strong> {{ $regPayload['rejection_reason'] }}
                                </div>
                            @endif
                        @else
                            <span class="badge" style="background: #F4F4F5; color: #71717A; font-weight: 600; padding: 4px 10px;">
                                {{ ucfirst($user->status) }}
                            </span>
                        @endif
                    </td>

                    <!-- 5. Review Actions -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 7px; min-width: 175px;">
                            <!-- Full Application Modal Trigger -->
                            <button type="button" class="button subtle js-open-reg-modal" style="width: 100%; min-height: 35px; padding: 6px 12px; font-size: 11px; justify-content: center; gap: 6px; font-weight: 600;">
                                @include('admin.partials.icon', ['name' => 'file'])
                                <span>Review Application</span>
                            </button>

                            <!-- Direct Decision Buttons -->
                            <div style="display: flex; gap: 6px;">
                                @if(!$isActive)
                                    <form method="POST" action="{{ route('admin.registrations.status', $user->id) }}" style="flex: 1;" data-confirm="Approve and activate store account for '{{ addslashes($user->name) }}'?">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="button" @disabled(!$user->id_photo) style="width: 100%; min-height: 33px; padding: 5px 8px; font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff; justify-content: center;" title="{{ !$user->id_photo ? 'An uploaded ID is required before approval' : '' }}">
                                            ✓ Approve
                                        </button>
                                    </form>
                                @endif

                                @if(!$isRejected)
                                    <button type="button" class="button subtle js-open-reject-reg" style="flex: 1; min-height: 33px; padding: 5px 8px; font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border); justify-content: center;">
                                        ✕ Reject
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Hidden JSON payload for modal --}}
                        <script type="application/json" class="reg-payload">
                            {!! json_encode($regPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                        </script>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <span class="icon">@include('admin.partials.icon', ['name' => 'file'])</span>
                            <strong>No partner applications found</strong>
                            <p>No seller or partner registrations match your current filters. Try resetting search filters.</p>
                            <a class="button subtle" href="{{ route('admin.registrations') }}" style="margin-top: 14px;">View all registrations</a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @include('admin.partials.pagination', ['paginator' => $registrations])
</section>

{{-- ======================================================== --}}
{{-- MODAL 1: Review Partner Application Modal Pop-up         --}}
{{-- ======================================================== --}}
<dialog id="registration-review-modal" class="admin-modal" aria-labelledby="reg-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">MERCHANT ONBOARDING · APPLICATION REVIEW</p>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h2 id="reg-modal-title" style="font-size: 18px; margin: 0; line-height: 1.3;">Store Application</h2>
                    <span id="reg-role-badge" class="badge"></span>
                    <span id="reg-status-badge" class="badge"></span>
                </div>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <div class="admin-modal-body" style="padding: 22px 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                <!-- Left: Merchant & Store Information -->
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="modal-info-card">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">
                            🏪 Store & Entity Information
                        </strong>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                            <div style="grid-column: 1 / -1;">
                                <small class="muted" style="display: block;">Registered Store / Business Name</small>
                                <strong id="reg-biz-name" style="font-size: 14px; color: var(--ink);"></strong>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Seller Classification</small>
                                <span id="reg-seller-type" style="font-weight: 500;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Line of Business / Category</small>
                                <span id="reg-biz-line" style="font-weight: 500;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Tax Identification Number (TIN)</small>
                                <code id="reg-tin" style="font-size: 12px; font-weight: 600;"></code>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Contact Phone</small>
                                <span id="reg-phone" style="font-weight: 500;"></span>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <small class="muted" style="display: block;">Operating / Pickup Address</small>
                                <span id="reg-address" style="font-weight: 500; font-size: 11px; line-height: 1.4;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-info-card">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">
                            👤 Authorized Representative
                        </strong>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                            <div>
                                <small class="muted" style="display: block;">Account Holder Name</small>
                                <strong id="reg-user-name"></strong>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Account Email</small>
                                <span id="reg-email" style="word-break: break-all;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Attached Documents & Compliance -->
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="modal-info-card">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">
                            📁 Attached Business Documents
                        </strong>
                        <ul id="reg-docs-list" style="margin: 0; padding-left: 18px; font-size: 12px; line-height: 1.8;"></ul>
                    </div>

                    <div class="modal-info-card" style="background: #F0FDF4; border-color: #BBF7D0;">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #166534; margin-bottom: 8px;">
                            🛡️ Identity Check Cross-Reference
                        </strong>
                        <p style="font-size: 11px; color: #166534; margin: 0 0 10px; line-height: 1.4;">
                            The applicant's government ID can be inspected with full zoom and rotation in the ID Checks workspace.
                        </p>
                        <a id="reg-jump-id-checks" href="#" class="button subtle" style="font-size: 11px; padding: 6px 12px; gap: 6px;">
                            @include('admin.partials.icon', ['name' => 'shield'])
                            <span>Open Government ID in ID Checks ↗</span>
                        </a>
                    </div>

                    <div id="reg-prev-reason-card" class="modal-info-card" style="display: none; background: #FFF5F3; border-color: var(--rose-border);">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--rose-ink); margin-bottom: 4px;">
                            Previous Rejection Feedback
                        </strong>
                        <p id="reg-prev-reason-text" style="font-size: 11px; color: var(--rose-ink); margin: 0;"></p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="admin-modal-footer">
            <div>
                <span class="muted" style="font-size: 11px;">
                    Approving grants portal access to start selling or delivering on Cartzy.
                </span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="button subtle js-close-modal" style="font-size: 11px;">Close</button>
                <button type="button" id="reg-btn-reject" class="button subtle" style="font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border);">
                    ✕ Reject Application
                </button>
                <form id="reg-approve-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" id="reg-btn-approve" class="button" style="font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff;">
                        ✓ Approve Store Application
                    </button>
                </form>
            </div>
        </footer>
    </div>
</dialog>

{{-- ======================================================== --}}
{{-- MODAL 2: Reject Partner Application Pop-up               --}}
{{-- ======================================================== --}}
<dialog id="registration-reject-modal" class="admin-modal" aria-labelledby="reg-reject-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header" style="border-bottom-color: var(--rose-border); background: #FFFBFB;">
            <div>
                <p class="eyebrow" style="color: var(--rose-ink); margin-bottom: 3px;">DECISION · APPLICATION REJECTION</p>
                <h2 id="reg-reject-title" style="font-size: 17px; margin: 0; line-height: 1.3;">Disapprove Merchant Application</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <form id="reg-reject-form" method="POST" action="" class="form-stack">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <div class="admin-modal-body">
                <!-- Overview Card -->
                <div style="display: flex; gap: 12px; align-items: center; padding: 12px 14px; background: var(--canvas); border: 1px solid var(--line); border-radius: 10px; margin-bottom: 16px;">
                    <div id="reg-reject-avatar" class="avatar" style="width: 44px; height: 44px; border-radius: 50%; font-size: 16px; flex-shrink: 0;"></div>
                    <div style="min-width: 0;">
                        <strong id="reg-reject-name" style="font-size: 13px; line-height: 1.3; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></strong>
                        <small id="reg-reject-meta" class="muted" style="display: block; margin-top: 2px;"></small>
                    </div>
                </div>

                <div class="notice danger" style="margin-bottom: 16px; font-size: 12px; padding: 12px 14px; border-radius: 8px;">
                    The applicant will receive an email and in-app notice explaining what business permits or corrections are needed before resubmitting.
                </div>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink);">
                    Standard Rejection Reason (Autofill):
                    <select id="reg-reject-preset" style="margin-top: 6px; font-size: 12px;">
                        <option value="">-- Choose common reason to autofill --</option>
                        <option value="DTI / SEC Business Permit is missing, expired, or unreadable.">Missing or Expired DTI / SEC Permit</option>
                        <option value="The registered business name does not match the submitted trade documents.">Business Name Mismatch</option>
                        <option value="TIN (Tax Identification Number) is invalid, missing, or belongs to another entity.">Invalid or Missing TIN Number</option>
                        <option value="BIR Form 2303 Certificate of Registration is required for your seller classification.">Missing BIR Form 2303</option>
                        <option value="Line of business or products are restricted or prohibited under marketplace policies.">Restricted Line of Business</option>
                        <option value="Physical store or warehouse pickup address could not be verified.">Unverifiable Business Address</option>
                    </select>
                </label>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink); margin-top: 12px;">
                    Detailed Feedback for Applicant (Required):
                    <textarea id="reg-reject-reason" name="reason" rows="4" required placeholder="Specify exactly what requirements are missing or why this merchant application was disapproved..." style="margin-top: 6px; font-size: 12px; line-height: 1.5;"></textarea>
                </label>
            </div>

            <footer class="admin-modal-footer">
                <button type="button" class="button subtle js-close-modal">Cancel</button>
                <button type="submit" class="button danger" style="min-width: 140px;">
                    Confirm Rejection
                </button>
            </footer>
        </form>
    </div>
</dialog>
@endsection

@push('styles')
<style>
.admin-modal {
    margin: auto;
    padding: 0;
    border: 1px solid var(--line);
    border-radius: 18px;
    background: var(--surface);
    color: var(--ink);
    box-shadow: 0 30px 90px -15px rgba(40, 33, 51, 0.35);
    overflow: hidden;
}

.admin-modal::backdrop {
    background: rgba(40, 33, 51, 0.65);
    backdrop-filter: blur(4px);
}

.admin-modal[open] {
    animation: admin-modal-in 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes admin-modal-in {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

#registration-review-modal {
    width: min(920px, calc(100% - 32px));
    max-height: 90vh;
}

#registration-reject-modal {
    width: min(520px, calc(100% - 32px));
    max-height: 90vh;
}

.admin-modal-panel {
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.admin-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
    flex-shrink: 0;
}

.admin-modal-body {
    padding: 22px 24px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.admin-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-top: 1px solid var(--line);
    background: var(--canvas);
    flex-shrink: 0;
    gap: 12px;
    flex-wrap: wrap;
}

.modal-info-card {
    background: var(--canvas);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 14px 16px;
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const reviewModal = document.getElementById('registration-review-modal');
    const rejectModal = document.getElementById('registration-reject-modal');
    let currentRegData = null;

    // Helper: close dialogs on backdrop or close button
    document.querySelectorAll('.admin-modal').forEach(dialog => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
        dialog.querySelectorAll('.js-close-modal').forEach(btn => {
            btn.addEventListener('click', () => dialog.close());
        });
    });

    // Populate and open Review Application Modal
    document.querySelectorAll('.js-open-reg-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.reg-payload') : null;
            if (!payloadScript) return;

            const data = JSON.parse(payloadScript.textContent);
            currentRegData = data;

            // Header
            document.getElementById('reg-modal-title').textContent = data.business_name;
            const roleBadge = document.getElementById('reg-role-badge');
            roleBadge.textContent = data.role_label;
            roleBadge.className = 'badge';

            const statusBadge = document.getElementById('reg-status-badge');
            statusBadge.textContent = data.status.toUpperCase();
            statusBadge.className = 'badge ' + (data.is_active ? 'good' : (data.is_rejected ? 'danger' : 'warning'));

            // Store Info
            document.getElementById('reg-biz-name').textContent = data.business_name;
            document.getElementById('reg-seller-type').textContent = data.seller_type;
            document.getElementById('reg-biz-line').textContent = data.line_of_business;
            document.getElementById('reg-tin').textContent = data.tin_number;
            document.getElementById('reg-phone').textContent = data.phone;
            document.getElementById('reg-address').textContent = data.address;

            // Representative
            document.getElementById('reg-user-name').textContent = data.name;
            document.getElementById('reg-email').textContent = data.email;

            // Docs list
            const docsList = document.getElementById('reg-docs-list');
            docsList.innerHTML = '';
            let docCount = 0;

            if (data.dti_permit) {
                docsList.innerHTML += `<li><a href="${data.dti_permit}" target="_blank" rel="noopener" class="text-link">DTI Business Registration Permit ↗</a></li>`;
                docCount++;
            }
            if (data.sec_certificate) {
                docsList.innerHTML += `<li><a href="${data.sec_certificate}" target="_blank" rel="noopener" class="text-link">SEC Certificate of Incorporation ↗</a></li>`;
                docCount++;
            }
            if (data.bir_certificate) {
                docsList.innerHTML += `<li><a href="${data.bir_certificate}" target="_blank" rel="noopener" class="text-link">BIR Form 2303 Certificate ↗</a></li>`;
                docCount++;
            }
            if (data.id_photo) {
                docsList.innerHTML += `<li><a href="${data.id_photo}" target="_blank" rel="noopener" class="text-link">Representative Government ID (${data.id_type}) ↗</a></li>`;
                docCount++;
            }
            if (docCount === 0) {
                docsList.innerHTML = `<li class="muted">No trade permits or certificates attached.</li>`;
            }

            // Jump link to ID checks
            document.getElementById('reg-jump-id-checks').href = data.id_checks_url;

            // Previous rejection feedback
            const prevCard = document.getElementById('reg-prev-reason-card');
            if (data.rejection_reason) {
                prevCard.style.display = 'block';
                document.getElementById('reg-prev-reason-text').textContent = data.rejection_reason;
            } else {
                prevCard.style.display = 'none';
            }

            // Footer form actions
            const approveForm = document.getElementById('reg-approve-form');
            const approveBtn = document.getElementById('reg-btn-approve');
            const rejectBtn = document.getElementById('reg-btn-reject');

            approveForm.action = data.status_update_url;
            approveForm.style.display = data.is_active ? 'none' : 'block';
            approveBtn.disabled = !data.has_id_doc;

            rejectBtn.style.display = data.is_rejected ? 'none' : 'inline-flex';

            reviewModal.showModal();
        });
    });

    // Open Reject Modal
    function openRegRejectModal(data) {
        if (!data) return;
        currentRegData = data;

        const form = document.getElementById('reg-reject-form');
        form.action = data.status_update_url;

        const avatarEl = document.getElementById('reg-reject-avatar');
        if (data.avatar) {
            avatarEl.innerHTML = `<img src="${data.avatar}" alt="${data.name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
        } else {
            avatarEl.textContent = data.initials;
        }

        document.getElementById('reg-reject-name').textContent = data.business_name || data.name;
        document.getElementById('reg-reject-meta').textContent = `${data.role_label} · ${data.email} · ${data.name}`;

        const presetSelect = document.getElementById('reg-reject-preset');
        presetSelect.value = '';

        const reasonTextarea = document.getElementById('reg-reject-reason');
        reasonTextarea.value = data.rejection_reason || '';

        if (reviewModal.open) reviewModal.close();

        rejectModal.showModal();
        reasonTextarea.focus();
    }

    // Direct Reject button on row
    document.querySelectorAll('.js-open-reject-reg').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.reg-payload') : null;
            if (!payloadScript) return;
            const data = JSON.parse(payloadScript.textContent);
            openRegRejectModal(data);
        });
    });

    // Reject button inside Review Modal footer
    document.getElementById('reg-btn-reject').addEventListener('click', () => {
        if (currentRegData) {
            openRegRejectModal(currentRegData);
        }
    });

    // Preset reason autofill
    document.getElementById('reg-reject-preset').addEventListener('change', function() {
        if (this.value) {
            const textarea = document.getElementById('reg-reject-reason');
            textarea.value = this.value;
            textarea.focus();
        }
    });
})();
</script>
@endpush
