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
                                <button class="button store-action store-action--{{ $status }}" type="submit">
                                    @include('admin.partials.icon', ['name' => ['suspended' => 'pause', 'deactivated' => 'power', 'active' => 'refresh'][$status]])
                                    <span>{{ $label }}</span>
                                </button>
                            </form>
                        @endforeach
                        @if(in_array($store->user->status, ['suspended', 'deactivated'], true))
                            <form method="POST" action="{{ route('admin.stores.archive', $store->id) }}" data-confirm="Move {{ $store->name }} and its seller account to Archive?">
                                @csrf
                                <button class="button store-action store-action--archive" type="submit">
                                    @include('admin.partials.icon', ['name' => 'archive'])
                                    <span>Archive</span>
                                </button>
                            </form>
                        @endif
                        <a class="button store-action store-action--view" href="{{ route('store.show', $store->slug) }}" target="_blank" rel="noopener">
                            @include('admin.partials.icon', ['name' => 'external'])
                            <span>View shop</span>
                        </a>
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
@endsection
@push('styles')
<style>
    .store-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .store-actions .store-action {
        font-size: 12px;
        font-weight: 700;
        min-height: 36px;
        padding: 7px 12px;
        gap: 7px;
        white-space: nowrap;
        color: var(--action-ink);
        background: var(--action-bg);
        border-color: var(--action-border);
    }
    .store-action--suspended { --action-ink: #92400e; --action-bg: #fffbeb; --action-border: #fcd34d; --action-hover: #fef3c7; }
    .store-action--deactivated { --action-ink: #b91c1c; --action-bg: #fef2f2; --action-border: #fca5a5; --action-hover: #fee2e2; }
    .store-action--active { --action-ink: #166534; --action-bg: #f0fdf4; --action-border: #86efac; --action-hover: #dcfce7; }
    .store-action--archive { --action-ink: #475569; --action-bg: #f1f5f9; --action-border: #cbd5e1; --action-hover: #e2e8f0; }
    .store-action--view { --action-ink: #1d4ed8; --action-bg: #eff6ff; --action-border: #93c5fd; --action-hover: #dbeafe; }
    .store-actions .store-action:hover { background: var(--action-hover); border-color: var(--action-ink); }
    .store-actions .store-action:focus-visible { outline: 2px solid var(--action-ink); outline-offset: 3px; }
    .store-actions .store-action .icon { width: 16px; height: 16px; flex-shrink: 0; stroke-width: 2; }
</style>
@endpush
