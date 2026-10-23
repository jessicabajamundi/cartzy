@extends('layouts.admin')
@section('page_title', 'Vouchers')
@section('page_description', 'Create discount and free-shipping vouchers and give them to buyers. The platform pays for every voucher: sellers still receive their full merchandise amount and the courier still gets the full shipping fee.')
@section('content')
<div class="stats-grid three">
    <article class="stat-card"><span>Live vouchers</span><strong>{{ number_format($stats['active']) }}</strong><small>Buyers can use these right now</small></article>
    <article class="stat-card"><span>Times used</span><strong>{{ number_format($stats['uses']) }}</strong><small>On orders that were not cancelled</small></article>
    <article class="stat-card"><span>Discounts given</span><strong>₱{{ number_format($stats['given'], 2) }}</strong><small>Paid by the platform</small></article>
</div>

@php $voucherTab = ($errors->any() || request('tab') === 'create') ? 'create' : (request('tab') === 'shops' ? 'shops' : 'list'); @endphp
<section class="panel">
<nav class="archive-tabs" role="tablist" aria-label="Vouchers">
    <a href="{{ route('admin.vouchers') }}" class="archive-tab {{ $voucherTab === 'list' ? 'active' : '' }}" role="tab" data-voucher-tab="list" aria-controls="voucher-panel-list" aria-selected="{{ $voucherTab === 'list' ? 'true' : 'false' }}">All vouchers <span class="tab-badge">{{ $vouchers->total() }}</span></a>
    <a href="{{ route('admin.vouchers', ['tab' => 'create']) }}" class="archive-tab {{ $voucherTab === 'create' ? 'active' : '' }}" role="tab" data-voucher-tab="create" aria-controls="voucher-panel-create" aria-selected="{{ $voucherTab === 'create' ? 'true' : 'false' }}">+ Create voucher</a>
    <a href="{{ route('admin.vouchers', ['tab' => 'shops']) }}" class="archive-tab {{ $voucherTab === 'shops' ? 'active' : '' }}" role="tab" aria-controls="voucher-panel-shops" aria-selected="{{ $voucherTab === 'shops' ? 'true' : 'false' }}">Seller vouchers <span class="tab-badge">{{ $shopVouchers->total() }}</span></a>
</nav>

<div id="voucher-panel-shops" role="tabpanel" data-voucher-panel="shops" @if($voucherTab !== 'shops') hidden @endif>
    <p class="padded" style="padding-bottom:0">Shop vouchers are created and paid for by sellers and only discount their own items. You can pause one that breaks the rules; the seller is notified.</p>
    <form class="filters" method="GET">
        <input type="hidden" name="tab" value="shops">
        <label class="search-field">Search
            <input name="shop_search" value="{{ request('shop_search') }}" placeholder="Store, voucher name or code">
        </label>
        <button class="button">Search</button>
    </form>
    <div class="table-scroll"><table>
        <thead><tr><th>Store</th><th>Voucher</th><th>Discount</th><th class="numeric">Used</th><th class="numeric">Given</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($shopVouchers as $voucher)
            @php $status = $voucher->status(); @endphp
            <tr>
                <td>@if($voucher->seller)<a class="text-link" href="{{ route('store.show', $voucher->seller->slug) }}" target="_blank" rel="noopener">{{ $voucher->seller->name }}</a>@else — @endif</td>
                <td><strong>{{ $voucher->name }}</strong><small>{{ $voucher->code ? 'Code: '.$voucher->code : 'Everyone' }}</small></td>
                <td><span class="voucher-ticket"><strong>{{ $voucher->headline() }}</strong><small>{{ $voucher->terms() }}</small></span></td>
                <td class="numeric">{{ number_format($voucher->used_count) }}{{ $voucher->total_limit ? ' / '.number_format($voucher->total_limit) : '' }}</td>
                <td class="numeric">₱{{ number_format(($voucher->discount_given_minor ?? 0) / 100, 2) }}</td>
                <td><span class="badge {{ $status === 'active' ? 'good' : ($status === 'scheduled' ? 'warning' : '') }}">{{ ucfirst($status) }}</span></td>
                <td><form method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}">@csrf<button class="button subtle">{{ $voucher->is_active ? 'Pause' : 'Resume' }}</button></form></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty"><strong>No seller vouchers yet</strong><p>Vouchers that sellers create in their Seller Centre will appear here.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('admin.partials.pagination', ['paginator' => $shopVouchers])
</div>

<div id="voucher-panel-create" role="tabpanel" data-voucher-panel="create" @if($voucherTab !== 'create') hidden @endif>
    <form class="padded" method="POST" action="{{ route('admin.vouchers.store') }}">
        @csrf
        @include('admin.partials.voucher-form')
        <div class="spaced voucher-actions">
            <button class="button">Create voucher</button>
            <a class="button subtle" href="{{ route('admin.vouchers') }}" data-voucher-tab="list">Cancel</a>
        </div>
    </form>
</div>

<div id="voucher-panel-list" role="tabpanel" data-voucher-panel="list" @if($voucherTab !== 'list') hidden @endif>
    <form class="filters" method="GET">
        <label class="search-field">Search
            <input name="search" value="{{ request('search') }}" placeholder="Voucher name or code">
        </label>
        <button class="button">Search</button>
    </form>
    <div class="table-scroll"><table>
        <thead><tr><th>Voucher</th><th>Discount</th><th>Who can use it</th><th class="numeric">Used</th><th class="numeric">Given</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($vouchers as $voucher)
            @php $status = $voucher->status(); @endphp
            <tr>
                <td><strong>{{ $voucher->name }}</strong>@if($voucher->code)<small>Code: {{ $voucher->code }}</small>@endif</td>
                <td><span class="voucher-ticket"><strong>{{ $voucher->headline() }}</strong><small>{{ $voucher->terms() }}</small></span></td>
                <td>{{ \App\Models\Voucher::AUDIENCES[$voucher->audience] }}@if(in_array($voucher->audience, ['assigned', 'code']))<small>{{ number_format($voucher->users_count) }} buyer(s) have it</small>@endif</td>
                <td class="numeric">{{ number_format($voucher->used_count) }}{{ $voucher->total_limit ? ' / '.number_format($voucher->total_limit) : '' }}</td>
                <td class="numeric">₱{{ number_format(($voucher->discount_given_minor ?? 0) / 100, 2) }}</td>
                <td><span class="badge {{ $status === 'active' ? 'good' : ($status === 'scheduled' ? 'warning' : '') }}">{{ ucfirst($status) }}</span></td>
                <td><div class="voucher-actions">
                    <a class="button subtle" href="{{ route('admin.vouchers.show', $voucher) }}">Manage</a>
                    <form method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}">@csrf<button class="button subtle">{{ $voucher->is_active ? 'Pause' : 'Resume' }}</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty"><strong>No vouchers yet</strong><p><a class="text-link" href="{{ route('admin.vouchers', ['tab' => 'create']) }}" data-voucher-tab="create">Create your first voucher</a></p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('admin.partials.pagination', ['paginator' => $vouchers])
</div>
</section>
@endsection

@push('scripts')
<script>
// Switch between the list and the create form inside this page, without reloading or leaving the tab.
(function () {
    const tabs = document.querySelectorAll('.archive-tabs .archive-tab');
    function show(name) {
        document.querySelectorAll('[data-voucher-panel]').forEach(p => p.hidden = p.dataset.voucherPanel !== name);
        tabs.forEach(t => {
            const on = t.dataset.voucherTab === name;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        const url = new URL(location.href);
        if (name === 'create') url.searchParams.set('tab', 'create'); else url.searchParams.delete('tab');
        history.replaceState(null, '', url);
        if (name === 'create') document.querySelector('#voucher-panel-create input[name="name"]')?.focus();
    }
    document.querySelectorAll('[data-voucher-tab]').forEach(link => link.addEventListener('click', function (event) {
        event.preventDefault();
        show(this.dataset.voucherTab);
    }));
})();
</script>
@endpush
