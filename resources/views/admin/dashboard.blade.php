@extends('layouts.admin')
@section('page_title', 'Overview')
@section('page_description', 'A clear view of your marketplace and the work that needs attention.')
@section('content')
<section class="overview-banner"><div><span class="eyebrow">YOUR MARKETPLACE, AT A GLANCE</span><h2>Welcome back, {{ auth()->user()->name }}.</h2><p>Review registrations, oversee orders, and keep your marketplace running smoothly.</p></div><a class="button" href="{{ route('admin.registrations', ['status' => 'pending']) }}">Review registrations @include('admin.partials.icon', ['name' => 'arrow'])</a></section>
<div class="stats-grid">
    <article class="stat-card"><span>Completed merchandise sales</span><strong>₱{{ number_format($stats['sales'], 2) }}</strong><small>Paid, delivered or completed orders</small></article>
    <article class="stat-card"><span>Earned commission</span><strong>₱{{ number_format($stats['commission'], 2) }}</strong><small>Actual recorded order commission</small></article>
    <article class="stat-card"><span>Total orders</span><strong>{{ number_format($stats['orders']) }}</strong><small>Buyer orders across all stores</small></article>
    <a class="stat-card" href="{{ route('admin.registrations') }}"><span>Awaiting review</span><strong>{{ number_format($stats['pending']) }}</strong><small>Identity and account applications →</small></a>
</div>
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
