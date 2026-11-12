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
    .store-actions .button.store-action {
        font-size: 11px;
        min-height: 32px;
        padding: 5px 12px;
        white-space: nowrap;
    }
    .store-actions .button.store-action--suspended { color: #b45309; border-color: #fde68a; }
    .store-actions .button.store-action--deactivated { color: #475569; border-color: #cbd5e1; }
    .store-actions .button.store-action--view { color: #1d4ed8; border-color: #bfdbfe; }
</style>
@endpush
