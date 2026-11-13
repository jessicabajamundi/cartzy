@extends('layouts.admin')
@section('page_title', 'User accounts')
@section('page_description', 'Manage access for active, suspended, and deactivated accounts registered on your marketplace.')
@section('content')
<section class="panel">
@include('admin.partials.user-filters', ['statuses' => ['active', 'suspended', 'deactivated']])
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Manage access</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $user->email }}</small>
                    <small>{{ $user->phone ?? 'No phone number' }}</small>
                </td>
                <td>
                    <span class="badge" style="text-transform: capitalize;">{{ $user->role }}</span>
                    @if($user->business_name)
                        <small style="color: var(--muted); margin-top: 3px;">Store: <strong>{{ $user->business_name }}</strong></small>
                    @endif
                </td>
                <td class="nowrap">{{ $user->created_at?->format('M j, Y') }}</td>
                <td>
                    @if($user->status === 'active')
                        <span class="badge good">Active</span>
                    @elseif($user->status === 'suspended')
                        <span class="badge danger">Suspended</span>
                        @if($user->status_reason)
                            <small class="muted" title="{{ $user->status_reason }}" style="display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 3px;">
                                <strong>Reason:</strong> {{ $user->status_reason }}
                            </small>
                        @endif
                    @elseif($user->status === 'deactivated')
                        <span class="badge" style="background: #e2e8f0; color: #475569;">Deactivated</span>
                        @if($user->status_reason)
                            <small class="muted" title="{{ $user->status_reason }}" style="display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 3px;">
                                <strong>Reason:</strong> {{ $user->status_reason }}
                            </small>
                        @endif
                    @else
                        <span class="badge">{{ ucfirst($user->status) }}</span>
                    @endif
                </td>
                <td>
                    @if($user->isAdmin())
                        <span class="muted">Administrator</span>
                    @elseif($user->status === 'active')
                        {{-- Active user: separate Suspend and Deactivate buttons --}}
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" class="button subtle" style="min-height: 32px; padding: 5px 12px; font-size: 11px; color: #b45309; border-color: #fde68a;"
                                data-trigger-action="suspend"
                                data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-role="{{ ucfirst($user->role) }}"
                                data-action-url="{{ route('admin.users.status', $user->id) }}">
                                @include('admin.partials.icon', ['name' => 'shield']) Suspend
                            </button>
                            <button type="button" class="button subtle" style="min-height: 32px; padding: 5px 12px; font-size: 11px; color: #475569; border-color: #cbd5e1;"
                                data-trigger-action="deactivate"
                                data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-role="{{ ucfirst($user->role) }}"
                                data-action-url="{{ route('admin.users.status', $user->id) }}">
                                Deactivate
                            </button>
                        </div>
                    @else
                        {{-- Suspended or Deactivated user: replaced by Reactivate and Archive buttons --}}
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" data-confirm="Reactivate and restore this account to active status?">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="button" style="min-height: 32px; padding: 5px 12px; font-size: 11px;">
                                    @include('admin.partials.icon', ['name' => 'refresh']) Reactivate
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.archive', $user->id) }}" data-confirm="Move this account to the Archive section?">
                                @csrf
                                <button type="submit" class="button subtle" style="min-height: 32px; padding: 5px 12px; font-size: 11px;">
                                    @include('admin.partials.icon', ['name' => 'archive']) Archive
                                </button>
                            </form>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty"><strong>No matching accounts</strong><p>Try changing your search or filters.</p></div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $users])
</section>

{{-- Modal dialog for Suspend & Deactivate with reason input --}}
<dialog id="user-status-modal" aria-labelledby="status-modal-title" aria-describedby="status-modal-desc">
    <div class="user-status-modal-content">
        <button type="button" class="user-status-modal-close" data-close-status-modal aria-label="Close dialog">&times;</button>
        <div id="status-modal-badge" class="user-status-modal-badge warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h2 id="status-modal-title" class="user-status-modal-title">Suspend Account</h2>
        <p id="status-modal-desc" class="user-status-modal-desc">
            Please enter the reason for this action. The user will be notified of this reason.
        </p>

        <form id="user-status-form" method="POST" action="">
            @csrf
            <input type="hidden" name="status" id="status-modal-action" value="suspended">
            
            <label class="user-status-modal-label">
                <span id="status-modal-reason-label">Reason for suspension <strong style="color: #dc2626;">*</strong></span>
                <textarea name="reason" id="status-modal-reason-input" class="user-status-modal-textarea" rows="4" required placeholder="Type the reason for this action here..."></textarea>
            </label>

            <div class="user-status-modal-actions">
                <button type="button" class="btn-cancel" data-close-status-modal>Cancel</button>
                <button type="submit" id="status-modal-submit-btn" class="btn-confirm" style="background: #b45309;">Confirm Suspension</button>
            </div>
        </form>
    </div>
</dialog>

@push('styles')
<style>
    #user-status-modal {
        width: min(500px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--line, #e2e8f0);
        border-radius: 20px;
        background: var(--surface, #fff);
        color: var(--ink, #1f2937);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.28);
        overflow: hidden;
    }
    #user-status-modal::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(4px);
    }
    #user-status-modal[open] {
        animation: user-modal-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes user-modal-pop {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .user-status-modal-content {
        position: relative;
        padding: 32px 30px 28px;
        text-align: left;
    }
    .user-status-modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f8fafc;
        color: #64748b;
        font-size: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all 0.15s ease;
    }
    .user-status-modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: scale(1.05);
    }
    .user-status-modal-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        margin-bottom: 18px;
        border-radius: 14px;
        background: #fef3c7;
        color: #b45309;
        flex-shrink: 0;
    }
    .user-status-modal-badge.deactivate {
        background: #f1f5f9;
        color: #475569;
    }
    .user-status-modal-badge.danger {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }
    .user-status-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }
    .user-status-modal-desc {
        font-size: 13.5px;
        color: #64748b;
        margin: 0 0 22px;
        line-height: 1.55;
    }
    .user-status-modal-label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        text-align: left;
        margin-bottom: 24px;
    }
    .user-status-modal-textarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        line-height: 1.5;
        box-sizing: border-box;
        background: #f8fafc;
        color: #1e293b;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .user-status-modal-textarea:focus {
        background: #fff;
        border-color: #b45309;
        outline: none;
        box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.12);
    }
    .user-status-modal-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }
    .user-status-modal-actions .btn-cancel {
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: background 0.15s;
    }
    .user-status-modal-actions .btn-cancel:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .user-status-modal-actions .btn-confirm {
        min-height: 42px;
        padding: 0 22px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
    }
    .user-status-modal-actions .btn-confirm:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('user-status-modal');
        if (!modal) return;

        const form = document.getElementById('user-status-form');
        const actionInput = document.getElementById('status-modal-action');
        const titleEl = document.getElementById('status-modal-title');
        const descEl = document.getElementById('status-modal-desc');
        const labelEl = document.getElementById('status-modal-reason-label');
        const reasonInput = document.getElementById('status-modal-reason-input');
        const submitBtn = document.getElementById('status-modal-submit-btn');
        const badgeEl = document.getElementById('status-modal-badge');

        document.querySelectorAll('[data-trigger-action]').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.dataset.triggerAction; // 'suspend' or 'deactivate'
                const userName = this.dataset.userName || 'User';
                const actionUrl = this.dataset.actionUrl;

                form.action = actionUrl;
                actionInput.value = action === 'suspend' ? 'suspended' : 'deactivated';
                reasonInput.value = '';

                if (action === 'suspend') {
                    titleEl.textContent = `Suspend Account — ${userName}`;
                    descEl.textContent = `Temporarily suspend ${userName}'s account access. A notification stating this reason will be recorded for the user.`;
                    labelEl.innerHTML = `Reason for suspension <strong style="color: #b91c1c;">*</strong>`;
                    reasonInput.placeholder = `Describe why ${userName}'s account is being suspended...`;
                    submitBtn.textContent = 'Confirm Suspension';
                    submitBtn.style.background = '#b45309';
                    submitBtn.style.borderColor = '#b45309';
                    badgeEl.className = 'user-status-modal-badge';
                } else {
                    titleEl.textContent = `Deactivate Account — ${userName}`;
                    descEl.textContent = `Deactivate ${userName}'s account. A notification stating this reason will be recorded for the user.`;
                    labelEl.innerHTML = `Reason for deactivation <strong style="color: #b91c1c;">*</strong>`;
                    reasonInput.placeholder = `Describe why ${userName}'s account is being deactivated...`;
                    submitBtn.textContent = 'Confirm Deactivation';
                    submitBtn.style.background = '#475569';
                    submitBtn.style.borderColor = '#475569';
                    badgeEl.className = 'user-status-modal-badge deactivate';
                }

                if (typeof modal.showModal === 'function') {
                    modal.showModal();
                } else {
                    modal.setAttribute('open', '');
                }
                setTimeout(() => reasonInput.focus(), 80);
            });
        });

        document.querySelectorAll('[data-close-status-modal]').forEach(btn => {
            btn.addEventListener('click', function () {
                if (typeof modal.close === 'function') {
                    modal.close();
                } else {
                    modal.removeAttribute('open');
                }
            });
        });

        modal.addEventListener('click', function (e) {
            const rect = modal.getBoundingClientRect();
            if (
                e.clientX < rect.left ||
                e.clientX > rect.right ||
                e.clientY < rect.top ||
                e.clientY > rect.bottom
            ) {
                if (typeof modal.close === 'function') modal.close();
                else modal.removeAttribute('open');
            }
        });
    });
</script>
@endpush
@endsection
