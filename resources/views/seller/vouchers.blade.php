@extends('layouts.seller')
@section('page_title', 'Vouchers')
@section('content')
<link rel="stylesheet" href="{{ asset('css/seller-vouchers.css') }}?v={{ filemtime(public_path('css/seller-vouchers.css')) }}">
@php
    $peso = fn ($minor) => '₱'.number_format($minor / 100, $minor % 100 ? 2 : 0);
    $statusLabels = ['active' => 'Ongoing', 'scheduled' => 'Upcoming', 'paused' => 'Paused', 'expired' => 'Ended', 'used up' => 'Fully used'];
@endphp
<div class="sv-page">
    <div class="page-heading">
        <div>
            <div class="eyebrow">MARKETING</div>
            <h1>Shop vouchers</h1>
            <p class="subtitle">Give buyers a discount on your items to win more orders. Your store pays for the discount.</p>
        </div>
        @if($canManage)<a class="button" href="{{ route('seller.vouchers.create') }}">+ Create voucher</a>@endif
    </div>

    @unless($canManage)
        <div class="notice">An approved, active seller account is needed to create vouchers. <a href="{{ route('seller.account') }}">Check your account &rarr;</a></div>
    @endunless

    @if($canManage)
    <section class="sv-quick" aria-labelledby="sv-quick-title">
        <h2 id="sv-quick-title">Start in one click</h2>
        <div class="sv-quick-grid">
            <a class="sv-quick-card" href="{{ route('seller.vouchers.create', ['template' => 'amount']) }}">
                <span class="sv-quick-icon">₱</span>
                <span><strong>Amount off</strong><small>e.g. ₱50 off orders of ₱500 or more</small></span>
            </a>
            <a class="sv-quick-card" href="{{ route('seller.vouchers.create', ['template' => 'percent']) }}">
                <span class="sv-quick-icon">%</span>
                <span><strong>Percent off</strong><small>e.g. 10% off, up to ₱100</small></span>
            </a>
            <a class="sv-quick-card" href="{{ route('seller.vouchers.create', ['template' => 'shipping']) }}">
                <span class="sv-quick-icon">🚚</span>
                <span><strong>Free shipping</strong><small>e.g. Free shipping on orders ₱500+</small></span>
            </a>
            <a class="sv-quick-card" href="{{ route('seller.vouchers.create', ['template' => 'code']) }}">
                <span class="sv-quick-icon">#</span>
                <span><strong>Private code</strong><small>Only buyers you share the code with</small></span>
            </a>
        </div>
    </section>
    @endif

    <div class="metrics sv-metrics">
        <div class="metric"><div class="metric-top">Ongoing vouchers</div><div class="metric-value">{{ $stats['live'] }}</div></div>
        <div class="metric"><div class="metric-top">Times used</div><div class="metric-value">{{ number_format($stats['uses']) }}</div></div>
        <div class="metric"><div class="metric-top">Discount given</div><div class="metric-value">{{ $peso($stats['given']) }}</div><small>Taken from your earnings</small></div>
    </div>

    <details class="sv-howto">
        <summary>How shop vouchers work</summary>
        <ul>
            <li><strong>Only your items.</strong> The discount and minimum spend count only your products in the buyer's cart.</li>
            <li><strong>One of each per order.</strong> Buyers can use one discount voucher and one free-shipping voucher from your store, together with cartzy vouchers.</li>
            <li><strong>Free shipping covers your parcel.</strong> It waives the shipping fee for your items only. Fees depend on the buyer's location, so you can set a cap.</li>
            <li><strong>You pay for it.</strong> The discount is taken from that order's earnings. Commission stays on the full item price.</li>
            <li><strong>Applied for buyers.</strong> Your best voucher is picked automatically at checkout. Code vouchers apply once the buyer enters the code.</li>
        </ul>
    </details>

    <nav class="sv-tabs" aria-label="Filter vouchers">
        @foreach($tabs as $key => $label)
            <a href="{{ route('seller.vouchers', $key === 'all' ? [] : ['tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>{{ $label }}<span>{{ $counts[$key] ?: '' }}</span></a>
        @endforeach
    </nav>

    <div class="sv-list">
        @forelse($vouchers as $voucher)
            @php
                $status = $voucher->status();
                $used = $voucher->used_count;
                $limit = $voucher->total_limit;
            @endphp
            <article class="sv-card is-{{ str_replace(' ', '-', $status) }} {{ $voucher->isShipping() ? 'is-shipping' : '' }}">
                <div class="sv-stub">
                    <strong>{{ $voucher->headline() }}</strong>
                    <small>{{ $voucher->audience === 'code' ? 'CODE' : ($voucher->isShipping() ? 'SHIPPING' : 'SHOP VOUCHER') }}</small>
                </div>
                <div class="sv-body">
                    <div class="sv-title-row">
                        <h3>{{ $voucher->name }}</h3>
                        <span class="sv-status">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                    </div>
                    <p class="sv-terms">{{ $voucher->terms() }}@if($voucher->starts_at) &middot; From {{ $voucher->starts_at->timezone('Asia/Manila')->format('M j') }}@endif</p>
                    <div class="sv-meta">
                        @if($voucher->code)<span class="sv-code" title="Buyers type this at checkout">{{ $voucher->code }}</span>@else<span class="sv-chip">Everyone</span>@endif
                        <span class="sv-chip">{{ $voucher->per_user_limit }} per buyer</span>
                        <span class="sv-chip">{{ $peso((int) $voucher->discount_given_minor) }} given</span>
                    </div>
                    <div class="sv-usage" aria-label="{{ $used }} of {{ $limit }} used">
                        <span class="sv-usage-bar"><span style="width: {{ $limit ? min(100, $used / $limit * 100) : 0 }}%"></span></span>
                        <small>{{ number_format($used) }} / {{ number_format($limit) }} used</small>
                    </div>
                </div>
                @if($canManage)
                <div class="sv-actions">
                    <a class="button small secondary" href="{{ route('seller.vouchers.edit', $voucher) }}">Edit</a>
                    @unless(in_array($status, ['expired', 'used up'], true))
                        <form method="POST" action="{{ route('seller.vouchers.toggle', $voucher) }}">@csrf<button class="button small secondary" type="submit">{{ $voucher->is_active ? 'Pause' : 'Resume' }}</button></form>
                    @endunless
                    @if($used === 0)
                        <form method="POST" action="{{ route('seller.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Delete the voucher “{{ addslashes($voucher->name) }}”? This can\'t be undone.')">@csrf @method('DELETE')<button class="button small text sv-delete" type="submit">Delete</button></form>
                    @endif
                </div>
                @endif
            </article>
        @empty
            <div class="empty sv-empty">
                <span class="empty-icon">@include('seller.partials.icon', ['name' => 'ticket', 'size' => 26])</span>
                <h3>{{ $tab === 'all' ? 'No vouchers yet' : 'Nothing in this tab' }}</h3>
                <p>{{ $tab === 'all' ? 'Create your first voucher to give buyers a reason to check out today.' : 'Vouchers with this status will show up here.' }}</p>
                @if($canManage && $tab === 'all')<a class="button" href="{{ route('seller.vouchers.create') }}">+ Create voucher</a>@endif
            </div>
        @endforelse
    </div>
</div>
@endsection
