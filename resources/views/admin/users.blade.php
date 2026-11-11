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
        <button type="button" class="icon-button user-status-modal-close" data-close-status-modal aria-label="Close dialog">&times;</button>
        <div id="status-modal-badge" class="user-status-modal-badge warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h2 id="status-modal-title" style="margin: 8px 0 6px; font-size: 20px;">Suspend Account</h2>
        <p id="status-modal-desc" style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
            Please enter the reason for this action. The user will be notified of this reason.
        </p>

        <form id="user-status-form" method="POST" action="">
            @csrf
            <input type="hidden" name="status" id="status-modal-action" value="suspended">
            
            <label style="display: flex; flex-direction: column; gap: 6px; font-size: 12px; font-weight: 600; color: var(--ink); text-align: left; margin-bottom: 22px;">
                <span id="status-modal-reason-label">Reason for suspension <strong style="color: #b91c1c;">*</strong></span>
                <textarea name="reason" id="status-modal-reason-input" rows="4" required placeholder="Type the reason for this action here..." style="width: 100%; border: 1px solid var(--line); border-radius: 9px; padding: 10px 12px; font-size: 13px; font-family: inherit; resize: vertical; line-height: 1.5;"></textarea>
            </label>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="button subtle" data-close-status-modal>Cancel</button>
                <button type="submit" id="status-modal-submit-btn" class="button" style="background: #b45309; border-color: #b45309; color: #fff;">Confirm Suspension</button>
            </div>
        </form>
    </div>
</dialog>

@push('styles')
<style>
    #user-status-modal {
        width: min(500px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        padding: 0;
        border: 1px solid var(--line);
        border-radius: 20px;
        background: var(--surface, #fff);
        color: var(--ink);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.25);
    }
    #user-status-modal::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(3px);
    }
    .user-status-modal-content {
        position: relative;
        padding: 30px;
    }
    .user-status-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 24px;
        cursor: pointer;
    }
    .user-status-modal-badge {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        margin-bottom: 16px;
        border-radius: 50%;
        background: #fef3c7;
        color: #b45309;
    }
    .user-status-modal-badge.deactivate {
        background: #f1f5f9;
        color: #475569;
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
