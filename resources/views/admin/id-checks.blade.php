@extends('layouts.admin')
@section('page_title', 'ID checks')
@section('page_description', "Buyers can browse and fill their cart right away, but can't check out until you approve their ID.")

@section('content')
<div class="id-review-workspace">
<nav class="id-status-grid" aria-label="Filter identity checks">
    @foreach(['to_review' => ['To review', 'Waiting for your decision'], 'approved' => ['Approved', 'Verified identities'], 'rejected' => ['Rejected', 'Declined submissions']] as $status => [$label, $description])
        <a href="{{ route('admin.id-checks', ['tab' => $status]) }}" class="id-status-card {{ $tab === $status ? 'is-active' : '' }}" @if($tab === $status) aria-current="page" @endif>
            <span class="id-status-label"><span class="id-status-dot id-status-dot--{{ $status }}"></span>{{ $label }}</span>
            <strong>{{ number_format($counts[$status]) }}</strong>
            <span class="id-status-description">{{ $description }}</span>
        </a>
    @endforeach
</nav>
<aside class="id-review-guide" aria-labelledby="id-guide-title">
    <div class="id-guide-heading">@include('admin.partials.icon', ['name' => 'shield'])<h2 id="id-guide-title">Before you approve</h2></div>
    <ul>
        <li><span>01</span>Match the name & date of birth</li>
        <li><span>02</span>Check the photo & text are clear</li>
        <li><span>03</span>Confirm the ID is not expired</li>
    </ul>
</aside>
<section class="panel id-review-panel" aria-labelledby="id-queue-title">
    <header class="id-queue-heading">
        <div>
            <p class="eyebrow">IDENTITY VERIFICATION</p>
            <h2 id="id-queue-title">{{ ['to_review' => 'Ready for review', 'approved' => 'Approved identities', 'rejected' => 'Rejected submissions'][$tab] }}</h2>
            <p class="muted">{{ $tab === 'to_review' ? 'Inspect each document before making a decision.' : 'Review past identity verification decisions.' }}</p>
        </div>
        <span class="id-queue-total">{{ $idChecks->total() }} {{ $idChecks->total() === 1 ? 'submission' : 'submissions' }}</span>
    </header>
    <!-- ID Checks Table matching Guide Columns: BUYER | ID TYPE | SUBMITTED | DOCUMENT | DECISION -->
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="min-width: 260px;">BUYER</th>
                    <th style="min-width: 200px;">ID TYPE</th>
                    <th style="min-width: 160px;">SUBMITTED</th>
                    <th style="min-width: 170px;">DOCUMENT</th>
                    <th style="min-width: 180px;">DECISION</th>
                </tr>
            </thead>
            <tbody>
            @forelse($idChecks as $user)
                @php
                    $isIdVerified = $user->id_status === 'verified';
                    $isPending = $user->id_status === 'pending';
                    $isRejected = $user->id_status === 'rejected';

                    $idDocUrl = null;
                    $isPdf = false;
                    if ($user->id_photo) {
                        $idDocUrl = (str_starts_with($user->id_photo, 'http') || str_starts_with($user->id_photo, '/'))
                            ? $user->id_photo
                            : Storage::disk('public')->url($user->id_photo);
                        $isPdf = str_ends_with(strtolower($user->id_photo), '.pdf');
                    }

                    $avatarUrl = $user->avatar_url;
                    $initials = mb_strtoupper(mb_substr($user->name, 0, 1));

                    $addressFull = collect([
                        $user->street_address,
                        $user->barangay,
                        $user->city,
                        $user->province,
                        $user->postal_code,
                    ])->filter()->join(', ');
                    if (empty($addressFull)) {
                        $addressFull = $user->address ?: 'No address specified';
                    }

                    $userPayload = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'middle_initial' => $user->middle_initial,
                        'email' => $user->email,
                        'phone' => $user->phone ?: ($user->telephone ?: '—'),
                        'role' => 'buyer',
                        'role_label' => 'Buyer',
                        'avatar' => $avatarUrl,
                        'initials' => $initials,
                        'sex' => $user->sex ? ucfirst($user->sex) : '—',
                        'birthday' => $user->birthday ? date('M j, Y', strtotime($user->birthday)) : '—',
                        'age' => $user->age ?: '—',
                        'address' => $addressFull,
                        'id_type' => $user->id_type ?: 'Government ID',
                        'id_number' => $user->id_number ?: '—',
                        'id_status' => $user->id_status ?: 'unverified',
                        'id_rejection_reason' => $user->id_rejection_reason,
                        'id_verified_at' => $user->id_verified_at ? $user->id_verified_at->timezone('Asia/Manila')->format('M j, Y g:i A') : null,
                        'created_at' => $user->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
                        'id_doc_url' => $idDocUrl,
                        'is_pdf' => $isPdf,
                        'status_update_url' => route('admin.id-checks.status', $user->id),
                        'is_verified' => $isIdVerified,
                        'is_pending' => $isPending,
                        'is_rejected' => $isRejected,
                        'has_document' => (bool) $user->id_photo,
                    ];
                @endphp
                <tr>
                    <!-- 1. BUYER -->
                    <td>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line); flex-shrink: 0; background: #fff;">
                            @else
                                <div class="avatar" style="width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; font-size: 15px;">
                                    {{ $initials }}
                                </div>
                            @endif

                            <div style="min-width: 0;">
                                <strong style="font-size: 13px; line-height: 1.3; color: var(--ink); display: block;">{{ $user->name }}</strong>
                                <small class="muted" style="display: block; word-break: break-all; font-size: 12px; margin-top: 1px;">
                                    {{ $user->email }}
                                </small>

                                @if($user->phone || $user->telephone)
                                    <small style="color: var(--ink); margin-top: 2px; display: block; font-size: 11px;">
                                        📞 {{ $user->phone ?: $user->telephone }}
                                    </small>
                                @endif

                                @if($user->birthday)
                                    <small class="muted" style="margin-top: 2px; display: block; font-size: 11px;">
                                        DOB: <strong>{{ date('M j, Y', strtotime($user->birthday)) }}</strong>@if($user->age) ({{ $user->age }} yrs)@endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>

                    <!-- 2. ID TYPE -->
                    <td>
                        <div>
                            <span class="badge" style="background: var(--accent-light); color: var(--accent-hover); font-weight: 700; font-size: 11px; padding: 2px 8px;">
                                {{ $user->id_type ?: 'Government ID' }}
                            </span>

                            @if($user->id_number)
                                <div style="margin-top: 5px;">
                                    <code style="display: inline-block; padding: 2px 6px; background: var(--canvas); border: 1px solid var(--line); border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        {{ $user->id_number }}
                                    </code>
                                </div>
                            @endif

                            @if($addressFull !== 'No address specified')
                                <div style="margin-top: 4px; font-size: 11px; color: var(--muted); max-width: 190px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $addressFull }}">
                                    📍 {{ $addressFull }}
                                </div>
                            @endif
                        </div>
                    </td>

                    <!-- 3. SUBMITTED -->
                    <td>
                        <div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--ink);">
                                {{ $user->created_at ? $user->created_at->timezone('Asia/Manila')->format('M j, Y') : '—' }}
                            </span>
                            <small class="muted" style="display: block; margin-top: 2px; font-size: 11px;">
                                {{ $user->created_at ? $user->created_at->timezone('Asia/Manila')->format('g:i A') : '' }}
                            </small>
                            <small class="muted" style="display: block; font-size: 10px;">
                                {{ $user->created_at ? $user->created_at->diffForHumans() : '' }}
                            </small>
                        </div>
                    </td>

                    <!-- 4. DOCUMENT -->
                    <td>
                        @if($idDocUrl)
                            <div class="id-thumb-card js-open-id-modal" style="cursor: pointer;" title="Click to inspect ID in full viewer">
                                @if($isPdf)
                                    <div class="id-pdf-placeholder">
                                        <span style="font-size: 20px;">📄</span>
                                        <small style="font-weight: 600; color: #DC2626; font-size: 10px;">PDF Document</small>
                                    </div>
                                @else
                                    <div class="id-thumb-img-wrap">
                                        <img src="{{ $idDocUrl }}" alt="Submitted ID" class="id-thumb-img">
                                        <div class="id-thumb-hover-overlay">
                                            <span>🔍 Zoom</span>
                                        </div>
                                    </div>
                                @endif

                                <button type="button" class="button subtle js-open-id-modal" style="width: 100%; min-height: 28px; padding: 4px 8px; font-size: 11px; justify-content: center; margin-top: 5px;">
                                    Inspect ID ↗
                                </button>
                            </div>
                        @else
                            <div style="padding: 10px; background: var(--canvas); border: 1px dashed var(--line); border-radius: 8px; text-align: center; max-width: 120px;">
                                <small class="muted" style="font-size: 11px;">No ID file</small>
                            </div>
                        @endif
                    </td>

                    <!-- 5. DECISION -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px; min-width: 150px;">
                            @if($tab === 'to_review' || $isPending)
                                <form method="POST" action="{{ route('admin.id-checks.status', $user->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="button" @disabled(!$user->id_photo) style="width: 100%; min-height: 33px; padding: 5px 10px; font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff; justify-content: center; font-weight: 600;" title="{{ !$user->id_photo ? 'An uploaded ID is required before approval' : '' }}">
                                        ✓ Approve
                                    </button>
                                </form>

                                <button type="button" class="button subtle js-open-reject-modal" style="width: 100%; min-height: 33px; padding: 5px 10px; font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border); justify-content: center; font-weight: 600;">
                                    ✕ Reject
                                </button>
                            @elseif($tab === 'approved' || $isIdVerified)
                                <span class="badge good" style="background: #E8F5E9; color: #2E7D32; font-weight: 700; padding: 4px 10px; width: fit-content;">
                                    ✓ Approved
                                </span>
                                @if($user->id_verified_at)
                                    <small class="muted" style="font-size: 11px;">
                                        Verified {{ $user->id_verified_at->timezone('Asia/Manila')->format('M j, Y') }}
                                    </small>
                                @endif
                                <button type="button" class="button subtle js-open-reject-modal" style="color: var(--rose-ink); border-color: var(--rose-border); font-size: 10px; min-height: 26px; padding: 2px 8px; width: fit-content; margin-top: 4px;">
                                    Revoke
                                </button>
                            @elseif($tab === 'rejected' || $isRejected)
                                <span class="badge danger" style="background: var(--rose-light); color: var(--rose-ink); font-weight: 700; padding: 4px 10px; width: fit-content;">
                                    ✕ Rejected
                                </span>
                                @if($user->id_rejection_reason)
                                    <div style="font-size: 11px; color: var(--rose-ink); line-height: 1.35; max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 3px;" title="{{ $user->id_rejection_reason }}">
                                        <strong>Reason:</strong> {{ $user->id_rejection_reason }}
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('admin.id-checks.status', $user->id) }}" style="margin-top: 4px;">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="button subtle" style="color: #2E7D32; font-size: 10px; min-height: 26px; padding: 2px 8px;">
                                        Re-approve
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Hidden JSON payload for modals --}}
                        <script type="application/json" class="user-payload">
                            {!! json_encode($userPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                        </script>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty" style="padding: 48px 20px; text-align: center;">
                            <div style="width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 12px; background: var(--canvas); border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; color: var(--muted);">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="16" rx="3"/>
                                    <circle cx="9" cy="10" r="2"/>
                                    <line x1="15" y1="9" x2="17" y2="9"/>
                                    <line x1="15" y1="13" x2="17" y2="13"/>
                                    <line x1="7" y1="16" x2="17" y2="16"/>
                                </svg>
                            </div>

                            @if($tab === 'to_review')
                                <strong style="font-size: 15px; color: var(--ink); display: block; margin-bottom: 4px;">No IDs waiting for review</strong>
                                <p class="muted" style="font-size: 13px; margin: 0;">New buyer uploads appear here.</p>
                            @elseif($tab === 'approved')
                                <strong style="font-size: 15px; color: var(--ink); display: block; margin-bottom: 4px;">No approved IDs yet</strong>
                                <p class="muted" style="font-size: 13px; margin: 0;">Buyer identities you approve will appear in this list.</p>
                            @else
                                <strong style="font-size: 15px; color: var(--ink); display: block; margin-bottom: 4px;">No rejected IDs</strong>
                                <p class="muted" style="font-size: 13px; margin: 0;">Rejected buyer identity submissions will appear here.</p>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @include('admin.partials.pagination', ['paginator' => $idChecks])
</section>
</div>

{{-- ======================================================== --}}
{{-- MODAL 1: Interactive Buyer ID Inspection & Verification Modal --}}
{{-- ======================================================== --}}
<dialog id="id-inspection-modal" class="admin-modal" aria-labelledby="inspection-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">BUYER KYC COMPLIANCE · IDENTITY VERIFICATION</p>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h2 id="inspection-modal-title" style="font-size: 18px; margin: 0; line-height: 1.3;">Buyer Verification</h2>
                    <span class="badge" style="background: #F4F4F5; color: #3F3F46;">Buyer</span>
                    <span id="modal-status-badge" class="badge"></span>
                </div>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <div class="admin-modal-body" style="padding: 20px 24px;">
            <div class="modal-kyc-layout">
                <!-- Left: Document Inspector Canvas -->
                <div class="modal-doc-inspector">
                    <div class="doc-toolbar">
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <span id="current-doc-label" style="font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase;">Government ID Document</span>
                        </div>
                        <div class="doc-controls">
                            <button type="button" class="doc-btn" id="btn-zoom-in" title="Zoom in">+</button>
                            <button type="button" class="doc-btn" id="btn-zoom-out" title="Zoom out">−</button>
                            <button type="button" class="doc-btn" id="btn-rotate" title="Rotate 90 degrees">↻</button>
                            <button type="button" class="doc-btn" id="btn-reset-zoom" title="Reset view">⤢</button>
                            <a id="btn-open-external" href="#" target="_blank" rel="noopener" class="doc-btn" title="Open full resolution in new tab">↗</a>
                        </div>
                    </div>

                    <div class="doc-canvas-viewport" id="doc-viewport">
                        <img id="doc-inspect-img" src="" alt="Government ID" class="doc-inspect-img" style="display: none;">
                        <iframe id="doc-inspect-pdf" src="" class="doc-inspect-pdf" style="display: none;"></iframe>
                        <div id="doc-empty-state" class="doc-empty-state" style="display: none;">
                            <span style="font-size: 32px;">📄</span>
                            <strong style="margin-top: 8px;">No Document Uploaded</strong>
                            <p class="muted" style="font-size: 11px;">This buyer has not submitted an identity document.</p>
                        </div>
                    </div>

                    <div class="doc-caption">
                        <small class="muted" style="font-size: 10px;">
                            💡 Tip: Cross-check document security features, legal name, DOB, and expiry date.
                        </small>
                    </div>
                </div>

                <!-- Right: Credentials & Cross-Verification Checklist -->
                <div class="modal-kyc-details">
                    <!-- Status Banner -->
                    <div id="modal-status-banner" class="modal-status-banner"></div>

                    <!-- Personal Profile Comparison -->
                    <div class="modal-info-card">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">
                            👤 Buyer Profile & Stated Details
                        </strong>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                            <div>
                                <small class="muted" style="display: block;">Full Legal Name</small>
                                <strong id="modal-user-name" style="font-size: 13px; color: var(--ink);"></strong>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Stated ID Type</small>
                                <span id="modal-id-type" class="badge" style="background: var(--accent-light); color: var(--accent-hover); font-weight: 700;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Declared ID Number</small>
                                <code id="modal-id-number" style="font-size: 12px; font-weight: 600; padding: 2px 6px; background: var(--surface); border: 1px solid var(--line); border-radius: 4px; display: inline-block;"></code>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Birthdate & Age</small>
                                <span id="modal-birth-age" style="font-weight: 500;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Sex / Gender</small>
                                <span id="modal-sex" style="font-weight: 500;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Contact Phone</small>
                                <span id="modal-phone" style="font-weight: 500;"></span>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <small class="muted" style="display: block;">Account Email</small>
                                <span id="modal-email" style="font-weight: 500;"></span>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <small class="muted" style="display: block;">Delivery / Registered Address</small>
                                <span id="modal-address" style="font-weight: 500; font-size: 11px; line-height: 1.4;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin KYC Verification Checklist (Matching Guide Rules) -->
                    <div class="modal-info-card" style="margin-top: 12px; background: #FFFDF9; border-color: #FDE68A;">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #92400E; margin-bottom: 8px;">
                            📋 Compliance Verification Checklist
                        </strong>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11px; color: #78350F;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex-direction: row;">
                                <input type="checkbox" id="chk-name-match" checked style="width: auto; min-height: auto;">
                                <span>Name on document matches buyer's account</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex-direction: row;">
                                <input type="checkbox" id="chk-dob-match" checked style="width: auto; min-height: auto;">
                                <span>Date of birth matches buyer's account</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex-direction: row;">
                                <input type="checkbox" id="chk-legible" checked style="width: auto; min-height: auto;">
                                <span>Photo and text are clearly readable and uncropped</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex-direction: row;">
                                <input type="checkbox" id="chk-not-expired" checked style="width: auto; min-height: auto;">
                                <span>ID is valid, official, and unexpired</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="admin-modal-footer">
            <div>
                <span id="modal-footer-notice" class="muted" style="font-size: 11px;">
                    Reviewing buyer identity before checkout privileges.
                </span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="button subtle js-close-modal" style="font-size: 11px;">Close</button>
                <button type="button" id="modal-btn-reject" class="button subtle" style="font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border);">
                    ✕ Reject ID
                </button>
                <form id="modal-approve-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" id="modal-btn-approve" class="button" style="font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff;">
                        ✓ Approve & Verify ID
                    </button>
                </form>
            </div>
        </footer>
    </div>
</dialog>

{{-- ======================================================== --}}
{{-- MODAL 2: Reject Buyer Identity Verification Pop-up        --}}
{{-- ======================================================== --}}
<dialog id="id-approve-modal" class="admin-modal" aria-labelledby="approve-modal-title" aria-describedby="approve-modal-description">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div><p class="eyebrow">IDENTITY VERIFICATION</p><h2 id="approve-modal-title">Approve this ID?</h2></div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close confirmation">&times;</button>
        </header>
        <div class="admin-modal-body">
            <p id="approve-modal-description">Are you sure you want to approve the identity document for <strong id="approve-preview-name"></strong>?</p>
            <div class="notice success" style="margin-top: 16px;">Their identity will be marked as verified. Buyers with an approved ID can proceed to checkout.</div>
            <p class="muted" style="font-size: 13px; margin-top: 12px;">Check that the name and date of birth match the account, the document is readable, and the ID has not expired.</p>
        </div>
        <footer class="admin-modal-footer">
            <button type="button" class="button subtle js-close-modal" autofocus>Cancel</button>
            <button type="button" class="button" id="confirm-id-approval" style="background: #2E7D32; border-color: #2E7D32; color: #fff;">Yes, approve ID</button>
        </footer>
    </div>
</dialog>

<dialog id="id-reject-modal" class="admin-modal" aria-labelledby="reject-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header" style="border-bottom-color: var(--rose-border); background: #FFFBFB;">
            <div>
                <p class="eyebrow" style="color: var(--rose-ink); margin-bottom: 3px;">DECISION · IDENTITY REJECTION</p>
                <h2 id="reject-modal-title" style="font-size: 17px; margin: 0; line-height: 1.3;">Reject this ID?</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <form id="reject-form" method="POST" action="" class="form-stack">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <div class="admin-modal-body">
                <!-- Buyer Overview Card -->
                <div style="display: flex; gap: 12px; align-items: center; padding: 12px 14px; background: var(--canvas); border: 1px solid var(--line); border-radius: 10px; margin-bottom: 16px;">
                    <div id="reject-preview-avatar" class="avatar" style="width: 44px; height: 44px; border-radius: 50%; font-size: 16px; flex-shrink: 0;"></div>
                    <div style="min-width: 0;">
                        <strong id="reject-preview-name" style="font-size: 13px; line-height: 1.3; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></strong>
                        <small id="reject-preview-meta" class="muted" style="display: block; margin-top: 2px;"></small>
                    </div>
                </div>

                <div class="notice danger" style="margin-bottom: 16px; font-size: 12px; padding: 12px 14px; border-radius: 8px;">
                    The buyer will receive an in-app notice with your feedback so they can upload a valid replacement ID.
                </div>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink);">
                    Standard Rejection Reason (Autofill):
                    <select id="reject-preset-select" style="margin-top: 6px; font-size: 12px;">
                        <option value="">-- Choose common reason to autofill --</option>
                        <option value="Photo is blurry, low-resolution, or too dark to read the document details.">Blurry, Unreadable, or Low Quality Photo</option>
                        <option value="The full name or date of birth does not match the registered account details.">Name or Date of Birth Mismatch</option>
                        <option value="Document appears expired or past the validity period.">Expired or Invalid Document</option>
                        <option value="Document corners or critical information are cut off or obscured.">Cut-off Edges or Obscured Information</option>
                        <option value="Submitted file is not a valid recognized government-issued photo ID.">Unaccepted Document Type</option>
                        <option value="Black-and-white photocopy or screen capture is not acceptable. Please upload original colored ID.">Black-and-White or Low Quality Photocopy</option>
                        <option value="Document appears digitally altered, falsified, or tampered with.">Suspected Altered or Manipulated Document</option>
                    </select>
                </label>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink); margin-top: 12px;">
                    Detailed Feedback for Buyer (Required):
                    <textarea id="reject-reason-textarea" name="reason" rows="4" required maxlength="2000" placeholder="Explain specifically why the ID document was disapproved and what the buyer needs to provide..." style="margin-top: 6px; font-size: 12px; line-height: 1.5;"></textarea>
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
/* ========================================================
   Admin ID Checks & KYC Workspace Styles (Cartzy Theme)
   ======================================================== */
.id-status-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
.id-status-card { display: flex; flex-direction: column; align-items: flex-start; padding: 22px 24px; border: 1px solid var(--line); border-radius: 16px; background: var(--surface); color: var(--ink); }
.id-status-card:hover { border-color: var(--accent); box-shadow: 0 4px 14px #2821330a; }
.id-status-card.is-active { border-color: var(--accent); background: linear-gradient(120deg, #f1ebf8, #fff9f6); box-shadow: inset 0 3px 0 var(--accent); }
.id-status-label { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 650; }
.id-status-dot { width: 7px; height: 7px; border-radius: 50%; background: #b67b22; }
.id-status-dot--approved { background: #338359; }
.id-status-dot--rejected { background: #b64b59; }
.id-status-card strong { margin: 12px 0 4px; font-size: 32px; line-height: 1.1; letter-spacing: -1px; }
.id-status-description { color: var(--muted); font-size: 12px; }
.id-review-guide { padding: 20px 24px; margin-bottom: 24px; border: 1px solid var(--line); border-radius: 14px; background: #eeebf440; }
.id-guide-heading { display: flex; align-items: center; gap: 10px; color: var(--accent); }
.id-guide-heading h2 { font-size: 13px; color: var(--ink); }
.id-review-guide ul { list-style: none; display: flex; flex-wrap: wrap; gap: 12px 28px; padding: 0; margin: 14px 0 0; }
.id-review-guide li { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); }
.id-review-guide li span { font-size: 10px; font-weight: 700; color: var(--accent); background: var(--surface); border: 1px solid var(--line); padding: 3px 6px; border-radius: 5px; }
.id-review-panel { padding: 0; overflow: hidden; border-radius: 16px; }
.id-queue-heading { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 24px; }
.id-queue-heading h2 { font-size: 20px; margin: 5px 0; }
.id-queue-heading .muted { font-size: 12px; }
.id-queue-total { flex-shrink: 0; color: var(--muted); background: var(--canvas); border: 1px solid var(--line); padding: 6px 10px; border-radius: 8px; font-size: 11px; }
.id-review-panel thead th { background: #f8f7fb; padding-top: 14px; padding-bottom: 14px; }
.id-review-panel tbody td { padding-top: 22px; padding-bottom: 22px; vertical-align: top; }
.id-review-panel tbody tr:hover { background: #fcfbfe; }
.id-review-workspace a:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
@media (max-width: 640px) {
    .id-status-grid { gap: 8px; }
    .id-status-card { padding: 16px 12px; border-radius: 12px; }
    .id-status-card strong { font-size: 26px; }
    .id-status-label { font-size: 11px; gap: 5px; }
    .id-status-description { font-size: 10px; }
    .id-review-guide, .id-queue-heading { padding: 18px; }
    .id-review-guide ul { flex-direction: column; }
    .id-queue-heading { align-items: flex-start; flex-wrap: wrap; }
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

#id-inspection-modal {
    width: min(980px, calc(100% - 32px));
    max-height: 90vh;
}

#id-approve-modal,
#id-reject-modal {
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

/* Document Thumbnail in Table */
.id-thumb-card {
    display: inline-block;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 4px;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    max-width: 125px;
}

.id-thumb-card:hover {
    border-color: var(--accent);
    box-shadow: 0 4px 12px rgba(40, 33, 51, 0.08);
}

.id-thumb-img-wrap {
    position: relative;
    width: 115px;
    height: 70px;
    border-radius: 6px;
    overflow: hidden;
    background: #F4F4F5;
}

.id-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s;
}

.id-thumb-card:hover .id-thumb-img {
    transform: scale(1.05);
}

.id-thumb-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(40, 33, 51, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.15s;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
}

.id-thumb-card:hover .id-thumb-hover-overlay {
    opacity: 1;
}

.id-pdf-placeholder {
    width: 115px;
    height: 70px;
    border-radius: 6px;
    background: #FEF2F2;
    border: 1px dashed #FCA5A5;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
}

/* 2-Column KYC Layout in Modal */
.modal-kyc-layout {
    display: grid;
    grid-template-columns: 460px 1fr;
    gap: 22px;
}

@media(max-width: 860px) {
    .modal-kyc-layout {
        grid-template-columns: 1fr;
    }
}

/* Document Inspector Canvas */
.modal-doc-inspector {
    display: flex;
    flex-direction: column;
    background: var(--canvas);
    border: 1px solid var(--line);
    border-radius: 12px;
    overflow: hidden;
}

.doc-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #fff;
    border-bottom: 1px solid var(--line);
}

.doc-controls {
    display: flex;
    gap: 4px;
}

.doc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: var(--surface);
    color: var(--ink);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}

.doc-btn:hover {
    background: var(--accent-light);
    border-color: var(--accent);
}

.doc-canvas-viewport {
    height: 380px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #241D2D;
    position: relative;
    user-select: none;
}

.doc-inspect-img {
    max-width: 95%;
    max-height: 95%;
    object-fit: contain;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    border-radius: 6px;
}

.doc-inspect-pdf {
    width: 100%;
    height: 100%;
    border: 0;
    background: #fff;
}

.doc-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #A8A0B2;
}

.doc-caption {
    padding: 8px 14px;
    background: #fff;
    border-top: 1px solid var(--line);
}

.modal-info-card {
    background: var(--canvas);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 14px 16px;
}

.modal-status-banner {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 12px;
    font-size: 12px;
}

.modal-status-banner.pending {
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    color: #92400E;
}

.modal-status-banner.verified {
    background: #E8F5E9;
    border: 1px solid #A5D6A7;
    color: #1B5E20;
}

.modal-status-banner.rejected {
    background: var(--rose-soft);
    border: 1px solid var(--rose-border);
    color: var(--rose-ink);
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const inspectionModal = document.getElementById('id-inspection-modal');
    const rejectModal = document.getElementById('id-reject-modal');
    let currentUserData = null;
    const approveModal = document.getElementById('id-approve-modal');
    const confirmApproval = document.getElementById('confirm-id-approval');
    let pendingApproval = null;
    let approvedForm = null;
    document.querySelectorAll('form').forEach(form => {
        if (!form.querySelector('input[name="status"][value="approved"]')) return;
        form.addEventListener('submit', event => {
            if (approvedForm === form) return;
            event.preventDefault();
            const payload = form.closest('tr')?.querySelector('.user-payload');
            const data = payload ? JSON.parse(payload.textContent) : currentUserData;
            if (!data || !data.has_document || approveModal.open) return;
            pendingApproval = { form, submitter: event.submitter };
            document.getElementById('approve-preview-name').textContent = data.name;
            confirmApproval.disabled = false;
            confirmApproval.textContent = 'Yes, approve ID';
            approveModal.showModal();
            approveModal.querySelector('[autofocus]').focus();
        });
    });
    confirmApproval.addEventListener('click', () => {
        if (!pendingApproval || confirmApproval.disabled) return;
        const { form, submitter } = pendingApproval;
        confirmApproval.disabled = true;
        confirmApproval.textContent = 'Approving...';
        approvedForm = form;
        try { form.requestSubmit(submitter || undefined); }
        finally { approvedForm = null; approveModal.close(); }
    });
    approveModal.addEventListener('close', () => {
        pendingApproval?.submitter?.focus();
        pendingApproval = null;
    });

    // Image Zoom / Rotate state
    let zoomLevel = 1;
    let rotationDeg = 0;

    const inspectImg = document.getElementById('doc-inspect-img');
    const inspectPdf = document.getElementById('doc-inspect-pdf');
    const emptyState = document.getElementById('doc-empty-state');
    const externalLink = document.getElementById('btn-open-external');
    const docLabel = document.getElementById('current-doc-label');

    function updateTransform() {
        if (inspectImg) {
            inspectImg.style.transform = `scale(${zoomLevel}) rotate(${rotationDeg}deg)`;
        }
    }

    document.getElementById('btn-zoom-in')?.addEventListener('click', () => {
        zoomLevel = Math.min(zoomLevel + 0.25, 3.5);
        updateTransform();
    });

    document.getElementById('btn-zoom-out')?.addEventListener('click', () => {
        zoomLevel = Math.max(zoomLevel - 0.25, 0.5);
        updateTransform();
    });

    document.getElementById('btn-rotate')?.addEventListener('click', () => {
        rotationDeg = (rotationDeg + 90) % 360;
        updateTransform();
    });

    document.getElementById('btn-reset-zoom')?.addEventListener('click', () => {
        zoomLevel = 1;
        rotationDeg = 0;
        updateTransform();
    });

    function setDocument(url, label, isPdf) {
        zoomLevel = 1;
        rotationDeg = 0;
        updateTransform();

        docLabel.textContent = label;
        externalLink.href = url || '#';

        if (!url) {
            inspectImg.style.display = 'none';
            inspectPdf.style.display = 'none';
            emptyState.style.display = 'flex';
            externalLink.style.display = 'none';
            return;
        }

        externalLink.style.display = 'inline-flex';
        emptyState.style.display = 'none';

        if (isPdf) {
            inspectImg.style.display = 'none';
            inspectPdf.style.display = 'block';
            inspectPdf.src = url;
        } else {
            inspectPdf.style.display = 'none';
            inspectImg.style.display = 'block';
            inspectImg.src = url;
        }
    }

    // Close modals on backdrop click or close button
    document.querySelectorAll('.admin-modal').forEach(dialog => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
        dialog.querySelectorAll('.js-close-modal').forEach(btn => {
            btn.addEventListener('click', () => dialog.close());
        });
    });

    // Populate and open Buyer ID Inspection Modal
    document.querySelectorAll('.js-open-id-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.user-payload') : null;
            if (!payloadScript) return;

            const data = JSON.parse(payloadScript.textContent);
            currentUserData = data;

            // Header info
            document.getElementById('inspection-modal-title').textContent = data.name;

            const statusBadge = document.getElementById('modal-status-badge');
            statusBadge.textContent = data.id_status.toUpperCase();
            statusBadge.className = 'badge ' + (data.is_verified ? 'good' : (data.is_rejected ? 'danger' : 'warning'));

            // Status Banner
            const statusBanner = document.getElementById('modal-status-banner');
            statusBanner.className = 'modal-status-banner ' + data.id_status;
            if (data.is_pending) {
                statusBanner.innerHTML = '<strong>⏳ Awaiting Buyer ID Verification Decision</strong><div style="margin-top:3px;font-size:11px;">Cross-reference the document photo with the declared personal details below. Approve if authentic, or reject with clear feedback.</div>';
            } else if (data.is_verified) {
                const verifiedDate = data.id_verified_at ? ' on ' + data.id_verified_at : '';
                statusBanner.innerHTML = '<strong>✓ Buyer Identity Verified & Active</strong><div style="margin-top:3px;font-size:11px;">This buyer has been verified' + verifiedDate + '. Full checkout privileges are active.</div>';
            } else if (data.is_rejected) {
                const reasonText = data.id_rejection_reason ? '<div style="margin-top:4px;padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--rose-border);"><strong>Feedback given:</strong> ' + escapeHtml(data.id_rejection_reason) + '</div>' : '';
                statusBanner.innerHTML = '<strong>✕ Buyer Identity Document Disapproved</strong><div style="margin-top:3px;font-size:11px;">This identity submission was rejected.</div>' + reasonText;
            } else {
                statusBanner.innerHTML = '<strong>⚠️ Unverified Buyer Profile</strong><div style="margin-top:3px;font-size:11px;">No verification has been processed yet.</div>';
            }

            // Buyer Details
            document.getElementById('modal-user-name').textContent = data.name + (data.middle_initial ? ' ' + data.middle_initial + '.' : '');
            document.getElementById('modal-id-type').textContent = data.id_type;
            document.getElementById('modal-id-number').textContent = data.id_number;
            document.getElementById('modal-birth-age').textContent = data.birthday + (data.age && data.age !== '—' ? ' (' + data.age + ' yrs old)' : '');
            document.getElementById('modal-sex').textContent = data.sex;
            document.getElementById('modal-phone').textContent = data.phone;
            document.getElementById('modal-email').textContent = data.email;
            document.getElementById('modal-address').textContent = data.address;

            // Document view
            if (data.id_doc_url) {
                setDocument(data.id_doc_url, 'Government ID (' + data.id_type + ')', data.is_pdf);
            } else {
                setDocument(null, 'No Document', false);
            }

            // Footer form actions
            const approveForm = document.getElementById('modal-approve-form');
            const approveBtn = document.getElementById('modal-btn-approve');
            const rejectBtn = document.getElementById('modal-btn-reject');

            approveForm.action = data.status_update_url;
            approveForm.style.display = data.is_verified ? 'none' : 'block';
            approveBtn.disabled = !data.has_document;

            rejectBtn.style.display = data.is_rejected ? 'none' : 'inline-flex';

            inspectionModal.showModal();
        });
    });

    // Open Reject Modal
    function openRejectModal(data) {
        if (!data) return;
        currentUserData = data;

        const form = document.getElementById('reject-form');
        form.action = data.status_update_url;

        const avatarEl = document.getElementById('reject-preview-avatar');
        if (data.avatar) {
            avatarEl.innerHTML = `<img src="${data.avatar}" alt="${data.name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
        } else {
            avatarEl.textContent = data.initials;
        }

        document.getElementById('reject-preview-name').textContent = data.name;
        document.getElementById('reject-preview-meta').textContent = `Buyer · ${data.id_type} · ${data.email}`;

        const presetSelect = document.getElementById('reject-preset-select');
        presetSelect.value = '';

        const reasonTextarea = document.getElementById('reject-reason-textarea');
        reasonTextarea.value = data.id_rejection_reason || '';

        if (inspectionModal.open) inspectionModal.close();

        rejectModal.showModal();
        reasonTextarea.focus();
    }

    // Direct Reject button on table row
    document.querySelectorAll('.js-open-reject-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.user-payload') : null;
            if (!payloadScript) return;
            const data = JSON.parse(payloadScript.textContent);
            openRejectModal(data);
        });
    });

    // Reject button inside Inspection Modal footer
    document.getElementById('modal-btn-reject').addEventListener('click', () => {
        if (currentUserData) {
            openRejectModal(currentUserData);
        }
    });

    // Preset reason autofill
    document.getElementById('reject-preset-select').addEventListener('change', function() {
        if (this.value) {
            const textarea = document.getElementById('reject-reason-textarea');
            textarea.value = this.value;
            textarea.focus();
        }
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
