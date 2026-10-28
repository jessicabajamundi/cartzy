@extends('layouts.admin')
@section('page_title', 'Logistics partners')
@section('page_description', 'Companies that deliver for Cartzy. Each approved partner reviews the riders who apply to it.')

@section('content')
<div class="logistics-workspace">
<nav class="partner-summary" aria-label="Filter logistics partners">
    @foreach(['all' => ['All partners', 'Your delivery network', 'truck'], 'approved' => ['Approved', 'Active delivery partners', 'shield'], 'pending' => ['Pending', 'Applications to review', 'file'], 'suspended' => ['Suspended', 'Temporarily restricted', 'archive']] as $key => [$label, $description, $icon])
        @php
            $selected = $key === 'all' ? empty($status) : $status === $key;
        @endphp
        <a class="partner-summary-card {{ $selected ? 'is-active' : '' }}" href="{{ route('admin.logistics', ['status' => $key === 'all' ? null : $key, 'search' => request('search')]) }}" @if($selected) aria-current="page" @endif>
            <span class="partner-summary-top"><span>{{ $label }}</span><span class="partner-summary-icon">@include('admin.partials.icon', ['name' => $icon])</span></span>
            <strong>{{ number_format($counts[$key]) }}</strong>
            <small>{{ $description }}</small>
        </a>
    @endforeach
</nav>

<section class="panel partner-directory" aria-labelledby="partner-directory-title">
    <header class="partner-directory-header">
        <div>
            <p class="eyebrow">DELIVERY NETWORK</p>
            <h2 id="partner-directory-title">{{ empty($status) ? 'Partner directory' : ucfirst($status).' partners' }}</h2>
            <p class="muted">Review applications, manage partner access, and monitor rider teams.</p>
        </div>
        <span class="partner-result-count">{{ $providers->total() }} {{ $providers->total() === 1 ? 'partner' : 'partners' }}</span>
    </header>
    <div class="partner-toolbar">
        <div class="partner-review-note">@include('admin.partials.icon', ['name' => 'shield'])<span>Approved partners manage their own rider applications.</span></div>
        <form method="GET" action="{{ route('admin.logistics') }}" class="partner-search" role="search">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <label for="partner-search-input" class="partner-search-label">Search partners</label>
            <input id="partner-search-input" type="search" name="search" value="{{ request('search') }}" placeholder="Company, contact, email or phone">
            <button type="submit" class="button subtle">Search</button>
            @if(request('search'))<a href="{{ route('admin.logistics', ['status' => $status]) }}" class="button subtle">Clear</a>@endif
        </form>
    </div>
    <!-- Logistics Partners Table matching Guide: COMPANY | CONTACT | STATUS | RIDERS | ACTIONS -->
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="min-width: 240px;">COMPANY</th>
                    <th style="min-width: 250px;">CONTACT</th>
                    <th style="min-width: 140px;">STATUS</th>
                    <th style="min-width: 140px;">RIDERS</th>
                    <th style="min-width: 200px;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            @forelse($providers as $provider)
                @php
                    $isApproved = $provider->status === 'approved';
                    $isPending = $provider->status === 'pending';
                    $isSuspended = $provider->status === 'suspended';
                    $isRejected = $provider->status === 'rejected';

                    $user = $provider->user;
                    $contactName = $user ? $user->name : $provider->name;
                    $contactEmail = $user ? $user->email : '—';
                    $contactPhone = $provider->contact_phone ?: ($user ? ($user->phone ?: ($user->telephone ?: '—')) : '—');

                    $coverage = collect([$user?->city, $user?->province])->filter()->join(', ');
                    if (empty($coverage)) {
                        $coverage = $user?->region ?: 'Metro Manila & Suburbs';
                    }

                    $ridersList = $provider->riders->map(function ($rider) {
                        return [
                            'id' => $rider->id,
                            'name' => $rider->user ? $rider->user->name : 'Rider #' . $rider->id,
                            'email' => $rider->user ? $rider->user->email : null,
                            'phone' => $rider->user ? $rider->user->phone : null,
                            'vehicle_type' => $rider->vehicle_type ?: 'Motorcycle',
                            'plate_no' => $rider->plate_no ?: '—',
                            'status' => $rider->status ?: 'approved',
                            'rating' => $rider->rating,
                        ];
                    });

                    $providerPayload = [
                        'id' => $provider->id,
                        'name' => $provider->name,
                        'slug' => $provider->slug,
                        'status' => $provider->status,
                        'status_label' => strtoupper($provider->status),
                        'coverage' => $coverage,
                        'contact_name' => $contactName,
                        'contact_email' => $contactEmail,
                        'contact_phone' => $contactPhone,
                        'rejection_reason' => $provider->rejection_reason,
                        'created_at' => $provider->created_at ? $provider->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') : '—',
                        'riders_count' => $provider->riders_count ?? $provider->riders->count(),
                        'pending_riders_count' => $provider->pending_riders_count ?? 0,
                        'riders' => $ridersList,
                        'status_update_url' => route('admin.logistics.status', $provider->id),
                    ];
                @endphp
                <tr>
                    <!-- 1. COMPANY -->
                    <td>
                        <div class="partner-company">
                            <span class="partner-company-icon" aria-hidden="true">@include('admin.partials.icon', ['name' => 'truck'])</span>
                            <strong style="font-size: 14px; color: var(--ink); line-height: 1.3; display: block;">{{ $provider->name }}</strong>
                            <small class="muted" style="display: block; margin-top: 2px; font-size: 12px;">Serves {{ $coverage }}</small>
                            <button type="button" class="js-open-provider-modal" style="background: none; border: none; padding: 0; font-size: 12px; color: var(--accent); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; margin-top: 5px;">
                                Review application →
                            </button>
                        </div>
                    </td>

                    <!-- 2. CONTACT -->
                    <td>
                        <div>
                            <strong style="font-size: 13px; color: var(--ink); display: block;">{{ $contactName }}</strong>
                            <small class="muted" style="display: block; margin-top: 2px; font-size: 12px;">
                                <span class="partner-contact-line">{{ $contactEmail }}</span><span class="partner-contact-line">{{ $contactPhone }}</span>
                            </small>
                        </div>
                    </td>

                    <!-- 3. STATUS -->
                    <td>
                        @if($isApproved)
                            <span class="badge good" style="background: #E8F5E9; color: #2E7D32; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 3px 9px; border-radius: 6px;">
                                APPROVED
                            </span>
                        @elseif($isPending)
                            <span class="badge warning" style="background: #FEF3C7; color: #B45309; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 3px 9px; border-radius: 6px;">
                                PENDING
                            </span>
                        @elseif($isSuspended)
                            <span class="badge danger" style="background: #FFF1F2; color: #BE123C; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 3px 9px; border-radius: 6px;">
                                SUSPENDED
                            </span>
                        @else
                            <span class="badge" style="background: #F4F4F5; color: #71717A; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 3px 9px; border-radius: 6px;">
                                {{ strtoupper($provider->status) }}
                            </span>
                        @endif

                        @if($provider->rejection_reason)
                            <small class="muted" style="display: block; margin-top: 4px; font-size: 10px; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $provider->rejection_reason }}">
                                {{ $provider->rejection_reason }}
                            </small>
                        @endif
                    </td>

                    <!-- 4. RIDERS -->
                    <td>
                        <div>
                            <strong style="font-size: 15px; color: var(--ink); display: block;">{{ $provider->riders_count ?? $provider->riders->count() }}</strong>
                            <small class="muted" style="font-size: 11px; display: block; margin-top: 1px;">
                                {{ $provider->pending_riders_count }} awaiting review
                            </small>
                        </div>
                    </td>

                    <!-- 5. ACTIONS (matching guide: Suspend / Deactivate) -->
                    <td>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            @if($isApproved)
                                <button type="button" class="button subtle js-open-suspend-modal" data-id="{{ $provider->id }}" data-name="{{ $provider->name }}" data-url="{{ route('admin.logistics.status', $provider->id) }}" style="font-size: 11px; padding: 5px 12px; color: var(--rose-ink); border-color: var(--rose-border); min-height: 32px; display: inline-flex; align-items: center; gap: 5px;">
                                    <span>⊘</span>
                                    <span>Suspend</span>
                                </button>

                                <form method="POST" action="{{ route('admin.logistics.status', $provider->id) }}" data-partner-confirm>
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="button subtle" style="font-size: 11px; padding: 5px 12px; color: var(--muted); min-height: 32px; display: inline-flex; align-items: center; gap: 5px;">
                                        <span>⏻</span>
                                        <span>Deactivate</span>
                                    </button>
                                </form>
                            @elseif($isPending)
                                <form method="POST" action="{{ route('admin.logistics.status', $provider->id) }}" data-partner-confirm>
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="button" style="background: #2E7D32; border-color: #2E7D32; color: #fff; font-size: 11px; padding: 5px 12px; min-height: 32px;">
                                        ✓ Approve
                                    </button>
                                </form>

                                <button type="button" class="button subtle js-open-reject-modal" data-id="{{ $provider->id }}" data-name="{{ $provider->name }}" data-url="{{ route('admin.logistics.status', $provider->id) }}" style="font-size: 11px; padding: 5px 12px; color: var(--rose-ink); border-color: var(--rose-border); min-height: 32px;">
                                    ✕ Reject
                                </button>
                            @elseif($isSuspended)
                                <form method="POST" action="{{ route('admin.logistics.status', $provider->id) }}" data-partner-confirm>
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="button" style="background: #2E7D32; border-color: #2E7D32; color: #fff; font-size: 11px; padding: 5px 12px; min-height: 32px;">
                                        ✓ Unsuspend
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.logistics.status', $provider->id) }}" data-partner-confirm>
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="button subtle" style="font-size: 11px; padding: 5px 12px; color: var(--muted); min-height: 32px;">
                                        ⏻ Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.logistics.status', $provider->id) }}" data-partner-confirm>
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="button subtle" style="color: #2E7D32; font-size: 11px; padding: 5px 12px; min-height: 32px;">
                                        Re-approve
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Hidden JSON payload for modal --}}
                        <script type="application/json" class="provider-payload">
                            {!! json_encode($providerPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                        </script>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty" style="padding: 48px 20px; text-align: center;">
                            <div style="width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 12px; background: var(--canvas); border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; color: var(--muted);">
                                @include('admin.partials.icon', ['name' => 'truck'])
                            </div>
                            <strong style="font-size: 15px; color: var(--ink); display: block; margin-bottom: 4px;">No logistics partners found</strong>
                            <p class="muted" style="font-size: 13px; margin: 0;">Companies registered for logistics and delivery will appear in this list.</p>
                            @if(request('search') || $status)
                                <div style="margin-top: 14px;">
                                    <a href="{{ route('admin.logistics') }}" class="button subtle" style="font-size: 12px;">Clear search filters</a>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @include('admin.partials.pagination', ['paginator' => $providers])
</section>
</div>

{{-- ======================================================== --}}
{{-- MODAL 1: Interactive Logistics Application Review Modal --}}
{{-- ======================================================== --}}
<dialog id="logistics-provider-modal" class="admin-modal" aria-labelledby="provider-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">LOGISTICS PARTNER · APPLICATION & FLEET</p>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h2 id="modal-provider-name" style="font-size: 18px; margin: 0; line-height: 1.3;">Partner Details</h2>
                    <span id="modal-provider-badge" class="badge"></span>
                </div>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <div class="admin-modal-body" style="padding: 20px 24px;">
            <!-- Company Profile & Contact Card -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: var(--canvas); border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; margin-bottom: 20px;">
                <div>
                    <small class="muted" style="display: block;">Company / Hub Name</small>
                    <strong id="modal-info-company" style="font-size: 14px; color: var(--ink);"></strong>
                </div>
                <div>
                    <small class="muted" style="display: block;">Assigned Coverage Area</small>
                    <span id="modal-info-coverage" style="font-weight: 600; color: var(--ink);"></span>
                </div>
                <div>
                    <small class="muted" style="display: block;">Contact Person / Representative</small>
                    <span id="modal-info-contact" style="font-weight: 500;"></span>
                </div>
                <div>
                    <small class="muted" style="display: block;">Contact Phone</small>
                    <span id="modal-info-phone" style="font-weight: 500;"></span>
                </div>
                <div style="grid-column: 1 / -1;">
                    <small class="muted" style="display: block;">Contact Email</small>
                    <span id="modal-info-email" style="font-weight: 500;"></span>
                </div>
            </div>

            <!-- Fleet Statistics Bar -->
            <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 130px; background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px;">
                    <small class="muted" style="display: block; font-size: 11px;">Total Fleet Size</small>
                    <strong id="modal-fleet-total" style="font-size: 20px; color: var(--ink);">0</strong>
                </div>
                <div style="flex: 1; min-width: 130px; background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px;">
                    <small class="muted" style="display: block; font-size: 11px;">Pending Rider Reviews</small>
                    <strong id="modal-fleet-pending" style="font-size: 20px; color: #B45309;">0</strong>
                </div>
            </div>

            <!-- Associated Riders Fleet List -->
            <div>
                <strong style="display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 8px;">
                    🛵 Fleet Riders Onboarded
                </strong>
                <div class="table-scroll" style="max-height: 240px; border: 1px solid var(--line); border-radius: 10px;">
                    <table style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Rider</th>
                                <th>Vehicle</th>
                                <th>Plate No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="modal-riders-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="admin-modal-footer">
            <button type="button" class="button subtle js-close-modal">Close</button>
            <div style="display: flex; gap: 8px;">
                <form data-partner-confirm id="modal-approve-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" id="modal-approve-btn" class="button" style="background: #2E7D32; border-color: #2E7D32; color: #fff; font-size: 11px;">
                        ✓ Approve Partner
                    </button>
                </form>
            </div>
        </footer>
    </div>
</dialog>

{{-- ======================================================== --}}
{{-- MODAL 2: Suspend Logistics Partner Modal                 --}}
{{-- ======================================================== --}}
<dialog id="logistics-suspend-modal" class="admin-modal" aria-labelledby="suspend-modal-title" aria-describedby="partner-suspend-description">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div class="partner-confirm-heading">
                <span class="partner-confirm-emblem" aria-hidden="true"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 7v10M15 7v10"/><circle cx="12" cy="12" r="9"/></svg></span>
                <p class="eyebrow">PARTNER ACCESS</p>
                <h2 id="suspend-modal-title">Suspend this partner?</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close confirmation">&times;</button>
        </header>
        <form id="suspend-form" method="POST" action="" class="form-stack">
            @csrf
            <input type="hidden" name="status" value="suspended">
            <div class="admin-modal-body">
                <div class="partner-action-company">
                    <span class="partner-confirm-company-icon">@include('admin.partials.icon', ['name' => 'truck'])</span>
                    <div><small>LOGISTICS PARTNER</small><strong id="suspend-target-name"></strong></div>
                </div>
                <p id="partner-suspend-description">This temporarily restricts the partner's account. While suspended, they cannot accept new dispatch requests or onboard new riders.</p>
                <p class="partner-suspend-note">You can restore access using Unsuspend once the issues are resolved.</p>
                <label class="partner-reject-label" for="partner-suspend-reason">Reason for suspension <span>Required</span></label>
                <textarea id="partner-suspend-reason" name="reason" rows="4" required maxlength="2000" aria-describedby="partner-suspend-hint" placeholder="Explain the issue and what the partner needs to resolve before access can be restored."></textarea>
                <p id="partner-suspend-hint">Your reason will be included in the partner's notification.</p>
            </div>
            <footer class="admin-modal-footer">
                <button type="button" class="button subtle js-close-modal">Cancel</button>
                <button type="submit" class="button danger">Confirm Suspension</button>
            </footer>
        </form>
    </div>
</dialog>

{{-- ======================================================== --}}
{{-- MODAL 3: Reject / Deactivate Logistics Partner Modal      --}}
{{-- ======================================================== --}}
<dialog id="logistics-reject-modal" class="admin-modal" aria-labelledby="reject-modal-title" aria-describedby="partner-reject-description">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div class="partner-confirm-heading">
                <span class="partner-confirm-emblem" aria-hidden="true">!</span>
                <p class="eyebrow">APPLICATION REVIEW</p>
                <h2 id="reject-modal-title">Reject this application?</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close confirmation">&times;</button>
        </header>
        <form id="reject-form" method="POST" action="" class="form-stack">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <div class="admin-modal-body">
                <div class="partner-action-company">
                    <span class="partner-confirm-company-icon">@include('admin.partials.icon', ['name' => 'truck'])</span>
                    <div><small>LOGISTICS PARTNER</small><strong id="reject-target-name"></strong></div>
                </div>
                <p id="partner-reject-description">This company will not be approved as a delivery partner. Explain what needs to be corrected so the applicant knows what to do next.</p>
                <label class="partner-reject-label" for="partner-reject-reason">Reason for rejection <span>Required</span></label>
                <textarea id="partner-reject-reason" name="reason" rows="4" required maxlength="2000" aria-describedby="partner-reject-hint" placeholder="e.g. The business permit is expired. Please provide a valid, updated copy."></textarea>
                <p id="partner-reject-hint">Your feedback will be included in the partner's notification.</p>
            </div>
            <footer class="admin-modal-footer">
                <button type="button" class="button subtle js-close-modal">Cancel</button>
                <button type="submit" class="button danger">Confirm Rejection</button>
            </footer>
        </form>
    </div>
</dialog>
<dialog id="partner-action-modal" class="admin-modal" aria-labelledby="partner-action-title" aria-describedby="partner-action-description">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div class="partner-confirm-heading">
                <span class="partner-confirm-emblem">@include('admin.partials.icon', ['name' => 'shield'])</span>
                <p class="eyebrow">PARTNER CONFIRMATION</p>
                <h2 id="partner-action-title"></h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close confirmation">&times;</button>
        </header>
        <form id="partner-action-form" method="POST" class="form-stack">
            @csrf
            <input type="hidden" name="status" id="partner-action-status">
            <div class="admin-modal-body">
                <div class="partner-action-company">
                    <span class="partner-confirm-company-icon">@include('admin.partials.icon', ['name' => 'truck'])</span>
                    <div><small>LOGISTICS PARTNER</small><strong id="partner-action-name"></strong></div>
                </div>
                <p id="partner-action-description"></p>
                <label id="partner-action-reason-label" hidden>Reason for deactivation
                    <textarea name="reason" id="partner-action-reason" rows="3" maxlength="2000" placeholder="Explain why this partner is being deactivated."></textarea>
                </label>
            </div>
            <footer class="admin-modal-footer">
                <button type="button" class="button subtle js-close-modal" autofocus>Cancel</button>
                <button type="submit" class="button" id="partner-action-submit"></button>
            </footer>
        </form>
    </div>
</dialog>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-logistics.css') }}?v={{ filemtime(public_path('css/admin-logistics.css')) }}">
<style>
/* ========================================================
   Admin Logistics Partners Workspace Styles (Cartzy Theme)
   ======================================================== */
.logistics-tab-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    text-decoration: none;
    transition: all 0.15s ease;
}
.logistics-tab-pill:hover {
    color: var(--ink);
    background: rgba(40, 33, 51, 0.05);
}
.logistics-tab-pill.active {
    background: var(--ink);
    color: #fff;
    box-shadow: 0 2px 6px rgba(40, 33, 51, 0.15);
}
.logistics-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    background: rgba(40, 33, 51, 0.08);
    color: var(--ink);
}
.logistics-tab-pill.active .logistics-tab-count {
    background: rgba(255, 255, 255, 0.22);
    color: #fff;
}

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

#logistics-provider-modal {
    width: min(720px, calc(100% - 32px));
    max-height: 90vh;
}

#partner-action-modal,
#logistics-suspend-modal,
#logistics-reject-modal {
    width: min(500px, calc(100% - 32px));
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
    padding: 20px 24px;
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
</style>
@endpush

@push('scripts')
<script>
(() => {
    const providerModal = document.getElementById('logistics-provider-modal');
    const suspendModal = document.getElementById('logistics-suspend-modal');
    const rejectModal = document.getElementById('logistics-reject-modal');
    const actionModal = document.getElementById('partner-action-modal');
    const actionForm = document.getElementById('partner-action-form');
    const actionSubmit = document.getElementById('partner-action-submit');
    let currentProvider = null;
    let actionTrigger = null;
    const actionCopy = {
        approve: ['Approve this partner?', 'Confirm that the application meets your requirements. Approval activates this partner’s account to manage riders and delivery operations.', 'Approve partner'],
        reapprove: ['Re-approve this partner?', 'Confirm that the issues behind the previous decision have been resolved. This will mark the partner as approved and reactivate its account.', 'Yes, re-approve'],
        unsuspend: ['Lift this suspension?', 'Confirm that the suspension issues have been resolved. This will restore approved status and reactivate the partner account.', 'Yes, unsuspend'],
        deactivate: ['Deactivate this partner?', 'This will remove approved access and mark the partner account as rejected/deactivated. Its records will remain available, and you can re-approve it later. Provide a reason for the partner notification.', 'Yes, deactivate']
    };
    document.querySelectorAll('form[data-partner-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            const payload = form.closest('tr')?.querySelector('.provider-payload');
            const data = payload ? JSON.parse(payload.textContent) : currentProvider;
            if (!data || actionModal.open) return;
            const deactivating = form.querySelector('[name="status"]').value === 'rejected';
            const action = deactivating ? 'deactivate' : (data.status === 'pending' ? 'approve' : data.status === 'suspended' ? 'unsuspend' : 'reapprove');
            const [title, description, label] = actionCopy[action];
            actionTrigger = event.submitter;
            actionForm.reset();
            actionForm.action = form.action;
            document.getElementById('partner-action-status').value = deactivating ? 'rejected' : 'approved';
            document.getElementById('partner-action-title').textContent = title;
            document.getElementById('partner-action-name').textContent = data.name;
            document.getElementById('partner-action-description').textContent = description;
            document.getElementById('partner-action-reason-label').hidden = !deactivating;
            const reason = document.getElementById('partner-action-reason');
            reason.setCustomValidity('');
            reason.required = deactivating;
            reason.disabled = !deactivating;
            actionModal.dataset.kind = deactivating ? 'danger' : 'approve';
            actionSubmit.textContent = label;
            actionSubmit.disabled = false;
            actionModal.showModal();
            actionModal.querySelector('[autofocus]').focus();
        });
    });
    actionModal.addEventListener('close', () => {
        actionTrigger?.focus();
        actionTrigger = null;
    });
    [actionForm, document.getElementById('suspend-form'), document.getElementById('reject-form')].forEach(form => {
        form.addEventListener('submit', event => {
            const reason = form.querySelector('textarea');
            if (reason?.required && !reason.value.trim()) {
                event.preventDefault();
                reason.setCustomValidity('Please enter a reason.');
                reason.reportValidity();
                return;
            }
            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            button.textContent = 'Saving...';
        });
        form.querySelector('textarea')?.addEventListener('input', event => event.target.setCustomValidity(''));
    });

    // Close modals on backdrop click or close button
    document.querySelectorAll('.admin-modal').forEach(dialog => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
        dialog.querySelectorAll('.js-close-modal').forEach(btn => {
            btn.addEventListener('click', () => dialog.close());
        });
    });

    // Open Review Application Modal
    document.querySelectorAll('.js-open-provider-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.provider-payload') : null;
            if (!payloadScript) return;

            const data = JSON.parse(payloadScript.textContent);
            currentProvider = data;

            document.getElementById('modal-provider-name').textContent = data.name;
            const badge = document.getElementById('modal-provider-badge');
            badge.textContent = data.status_label;
            badge.className = 'badge ' + (data.status === 'approved' ? 'good' : (data.status === 'suspended' || data.status === 'rejected' ? 'danger' : 'warning'));

            document.getElementById('modal-info-company').textContent = data.name;
            document.getElementById('modal-info-coverage').textContent = 'Serves ' + data.coverage;
            document.getElementById('modal-info-contact').textContent = data.contact_name;
            document.getElementById('modal-info-phone').textContent = data.contact_phone;
            document.getElementById('modal-info-email').textContent = data.contact_email;

            document.getElementById('modal-fleet-total').textContent = data.riders_count;
            document.getElementById('modal-fleet-pending').textContent = data.pending_riders_count;

            // Populate riders list
            const ridersTbody = document.getElementById('modal-riders-tbody');
            ridersTbody.innerHTML = '';
            if (data.riders && data.riders.length > 0) {
                data.riders.forEach(r => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <strong>${escapeHtml(r.name)}</strong>
                            <small class="muted" style="display:block;">${escapeHtml(r.phone || '—')}</small>
                        </td>
                        <td>${escapeHtml(r.vehicle_type)}</td>
                        <td><code>${escapeHtml(r.plate_no)}</code></td>
                        <td><span class="badge ${r.status === 'approved' ? 'good' : (r.status === 'pending' ? 'warning' : 'danger')}" style="font-size:10px;padding:2px 6px;">${escapeHtml(r.status.toUpperCase())}</span></td>
                    `;
                    ridersTbody.appendChild(row);
                });
            } else {
                ridersTbody.innerHTML = '<tr><td colspan="4" class="muted" style="text-align:center;padding:16px;">No riders enrolled with this partner yet.</td></tr>';
            }

            // Modal footer action buttons
            const approveForm = document.getElementById('modal-approve-form');
            approveForm.action = data.status_update_url;
            approveForm.style.display = data.status === 'approved' ? 'none' : 'block';

            providerModal.showModal();
        });
    });

    // Open Suspend Modal
    document.querySelectorAll('.js-open-suspend-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const url = btn.getAttribute('data-url');

            document.getElementById('suspend-target-name').textContent = name;
            const form = document.getElementById('suspend-form');
            form.reset();
            form.querySelector('textarea').setCustomValidity('');
            form.querySelector('[type="submit"]').disabled = false;
            form.querySelector('[type="submit"]').textContent = 'Confirm Suspension';
            form.action = url;

            suspendModal.showModal();
        });
    });

    // Open Reject Modal
    document.querySelectorAll('.js-open-reject-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const url = btn.getAttribute('data-url');

            document.getElementById('reject-target-name').textContent = name;
            const form = document.getElementById('reject-form');
            form.reset();
            form.querySelector('textarea').setCustomValidity('');
            form.querySelector('[type="submit"]').disabled = false;
            form.querySelector('[type="submit"]').textContent = 'Confirm Rejection';
            form.action = url;

            rejectModal.showModal();
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
@endpush
