@extends('layouts.admin')
@section('page_title', 'Overview')
@section('page_description', 'A clear view of your marketplace and the work that needs attention.')
@section('content')
<section class="overview-banner">
    <div class="overview-copy"><span class="eyebrow">YOUR MARKETPLACE, AT A GLANCE</span><h2>Welcome back, {{ auth()->user()->name }}.</h2><p>A little oversight. A better marketplace.<br>Here’s what’s happening across Cartzy today.</p><a class="button" href="{{ route('admin.reports') }}">Explore reports @include('admin.partials.icon', ['name' => 'arrow'])</a></div>
    <a class="review-spotlight" href="{{ route('admin.registrations', ['status' => 'pending']) }}">
        <span class="spotlight-heading">@include('admin.partials.icon', ['name' => 'file']) Registration desk</span>
        <strong>{{ number_format($stats['pending']) }}</strong>
        <span>applications awaiting review</span>
        <span class="spotlight-action">Review registrations @include('admin.partials.icon', ['name' => 'arrow'])</span>
    </a>
</section>
<div class="section-heading"><div><span class="eyebrow">PERFORMANCE</span><h2>Marketplace overview</h2></div><span class="muted">All-time totals</span></div>
<div class="stats-grid">
    <article class="stat-card"><span>Completed merchandise sales</span><strong>₱{{ number_format($stats['sales'], 2) }}</strong><small>Paid, delivered or completed orders</small></article>
    <article class="stat-card"><span>Earned commission</span><strong>₱{{ number_format($stats['commission'], 2) }}</strong><small>Actual recorded order commission</small></article>
    <article class="stat-card"><span>Total orders</span><strong>{{ number_format($stats['orders']) }}</strong><small>Buyer orders across all stores</small></article>
    <a class="stat-card" href="{{ route('admin.registrations') }}"><span>Awaiting review</span><strong>{{ number_format($stats['pending']) }}</strong><small>Identity and account applications →</small></a>
</div>
<nav class="quick-actions" aria-label="Quick actions">
    <a href="{{ route('admin.users') }}">@include('admin.partials.icon', ['name' => 'users'])<span><strong>Manage accounts</strong><small>Buyers, sellers & couriers</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.compliance') }}">@include('admin.partials.icon', ['name' => 'shield'])<span><strong>Product compliance</strong><small>Keep your catalog in check</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
    <a href="{{ route('admin.disputes') }}">@include('admin.partials.icon', ['name' => 'message'])<span><strong>Resolve disputes</strong><small>Support your marketplace</small></span>@include('admin.partials.icon', ['name' => 'arrow'])</a>
</nav>
<div class="population-strip">
    @foreach(['buyers' => 'Buyers', 'sellers' => 'Seller accounts', 'couriers' => 'Courier accounts', 'products' => 'Products'] as $key => $label)
    <div><span class="population-number">{{ number_format($stats[$key]) }}</span><span>{{ $label }}</span></div>
    @endforeach
</div>
<div class="two-columns">
<section class="panel"><header class="panel-header"><div><h2>Recent registrations</h2><p>Latest identity and account submissions</p></div><a class="text-link" href="{{ route('admin.registrations') }}">View all →</a></header>
<div class="table-scroll"><table><thead><tr><th>Applicant</th><th>Role</th><th>ID review</th></tr></thead><tbody>
@forelse($registrations as $user)<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td><td>{{ ucfirst($user->role) }}</td><td><span class="badge {{ $user->id_status === 'verified' ? 'good' : '' }}">{{ ucfirst($user->id_status) }}</span></td></tr>
@empty<tr><td colspan="3"><div class="empty"><strong>No registrations yet</strong><p>Applications will appear here when submitted.</p></div></td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><header class="panel-header"><div><h2>Recent activity</h2><p>Recorded administrator actions</p></div></header>@include('admin.partials.activity')</section>
</div>
<section class="panel"><header class="panel-header"><div><h2>Latest orders</h2><p>Recent orders across your marketplace</p></div><a class="text-link" href="{{ route('admin.reports') }}">Open reports →</a></header>@include('admin.partials.orders')</section>
@endsection
