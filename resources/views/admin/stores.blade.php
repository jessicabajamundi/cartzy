@extends('layouts.admin')
@section('page_title', 'Registered Stores')
@section('page_description', 'View approved shops and manage their access to the marketplace.')
@section('content')
<section class="panel">
    <header class="panel-header">
        <div><h2>Registered stores</h2><p>{{ $stores->total() }} matching stores. Suspending or deactivating a store also disables its seller account.</p></div>
        <a class="button subtle" href="{{ route('admin.archive', ['tab' => 'users', 'role' => 'seller']) }}">View archive</a>
    </header>
    <form method="GET" action="{{ route('admin.stores') }}" class="filters">
        <label>Search stores<input type="search" name="search" value="{{ request('search') }}" placeholder="Store, owner, or email"></label>
        <label>Status<select name="status">
            <option value="">All statuses</option>
            @foreach(['active', 'suspended', 'deactivated'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select></label>
        <button class="button" type="submit">Filter</button>
        <a class="button subtle" href="{{ route('admin.stores') }}">Reset</a>
    </form>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Store</th><th>Owner</th><th>Products</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($stores as $store)
                <tr>
                    <td><strong>{{ $store->name }}</strong><small>/store/{{ $store->slug }}</small></td>
                    <td><strong>{{ $store->user->name }}</strong><small>{{ $store->user->email }}</small></td>
                    <td>{{ number_format($store->products_count) }}</td>
                    <td><span class="badge {{ $store->user->status === 'active' ? 'good' : 'warning' }}">{{ ucfirst($store->user->status) }}</span></td>
                    <td class="nowrap">{{ $store->created_at?->timezone('Asia/Manila')->format('M j, Y') ?? 'N/A' }}</td>
                    <td><div class="store-actions">
                        @foreach($store->user->status === 'active' ? ['suspended' => 'Suspend', 'deactivated' => 'Deactivate'] : ['active' => 'Reactivate'] as $status => $label)
                            <form method="POST" action="{{ route('admin.stores.status', $store->id) }}" data-confirm="{{ $label }} {{ $store->name }} and its seller account?">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button class="button {{ $status === 'active' ? '' : 'subtle' }} store-action store-action--{{ $status }}" type="submit">
                                    @if($status !== 'deactivated')
                                        @include('admin.partials.icon', ['name' => $status === 'suspended' ? 'shield' : 'refresh'])
                                    @endif
                                    <span>{{ $label }}</span>
                                </button>
                            </form>
                        @endforeach
                        @if(in_array($store->user->status, ['suspended', 'deactivated'], true))
                            <form method="POST" action="{{ route('admin.stores.archive', $store->id) }}" data-confirm="Move {{ $store->name }} and its seller account to Archive?">
                                @csrf
                                <button class="button subtle store-action store-action--archive" type="submit">
                                    @include('admin.partials.icon', ['name' => 'archive'])
                                    <span>Archive</span>
                                </button>
                            </form>
                        @endif
                        <a class="button subtle store-action store-action--view" href="{{ route('store.show', $store->slug) }}" target="_blank" rel="noopener">
                            @include('admin.partials.icon', ['name' => 'external'])
                            <span>View shop</span>
                        </a>
                        <button type="button" class="button subtle store-action store-action--remove" style="color: #dc2626; border-color: #fecaca;"
                            data-trigger-remove-store
                            data-store-id="{{ $store->id }}"
                            data-store-name="{{ $store->name }}"
                            data-action-url="{{ route('admin.stores.remove', $store->id) }}">
                            @include('admin.partials.icon', ['name' => 'trash'])
                            <span>Remove</span>
                        </button>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><strong>No registered stores found</strong><p>Approved stores appear here. Try changing your search or filters.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $stores])
</section>

{{-- Modal dialog for removing a store with reason input --}}
<dialog id="remove-store-modal" aria-labelledby="remove-store-title" aria-describedby="remove-store-desc">
    <div class="user-status-modal-content">
        <button type="button" class="user-status-modal-close" data-close-remove-modal aria-label="Close dialog">&times;</button>
        <div class="user-status-modal-badge danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </div>
        <h2 id="remove-store-title" class="user-status-modal-title">Remove Store</h2>
        <p id="remove-store-desc" class="user-status-modal-desc">
            Enter the reason why this store is being removed. Its seller account will be deactivated and products unlisted.
        </p>

        <form id="remove-store-form" method="POST" action="">
            @csrf
            <label class="user-status-modal-label">
                <span>Reason for removal <strong style="color: #dc2626;">*</strong></span>
                <textarea name="removal_reason" id="remove-store-reason-input" class="user-status-modal-textarea" rows="4" required placeholder="Type the reason why this store is being removed..."></textarea>
            </label>

            <div class="user-status-modal-actions">
                <button type="button" class="btn-cancel" data-close-remove-modal>Cancel</button>
                <button type="submit" class="btn-confirm" style="background: #dc2626; color: #fff;">Confirm Removal</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
@push('styles')
<style>
    .store-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .store-actions .button.store-action {
        font-size: 11px;
        min-height: 32px;
        padding: 5px 12px;
        white-space: nowrap;
    }
    .store-actions .button.store-action--suspended { color: #b45309; border-color: #fde68a; }
    .store-actions .button.store-action--deactivated { color: #475569; border-color: #cbd5e1; }
    .store-actions .button.store-action--view { color: #1d4ed8; border-color: #bfdbfe; }
    .store-actions .button.store-action--remove:hover { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }

    #remove-store-modal {
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
    #remove-store-modal::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(4px);
    }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('remove-store-modal');
        if (!modal) return;

        const form = document.getElementById('remove-store-form');
        const titleEl = document.getElementById('remove-store-title');
        const descEl = document.getElementById('remove-store-desc');
        const reasonInput = document.getElementById('remove-store-reason-input');

        document.querySelectorAll('[data-trigger-remove-store]').forEach(btn => {
            btn.addEventListener('click', function () {
                const storeName = this.dataset.storeName || 'Store';
                const actionUrl = this.dataset.actionUrl;

                form.action = actionUrl;
                reasonInput.value = '';
                titleEl.textContent = `Remove Store — ${storeName}`;
                descEl.textContent = `Enter the reason why ${storeName} is being removed. Its seller account will be marked as removed and all products unlisted.`;

                if (typeof modal.showModal === 'function') {
                    modal.showModal();
                } else {
                    modal.setAttribute('open', '');
                }
                setTimeout(() => reasonInput.focus(), 80);
            });
        });

        document.querySelectorAll('[data-close-remove-modal]').forEach(btn => {
            btn.addEventListener('click', function () {
                if (typeof modal.close === 'function') modal.close();
                else modal.removeAttribute('open');
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
