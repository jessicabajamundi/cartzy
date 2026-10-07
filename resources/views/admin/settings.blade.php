@extends('layouts.admin')
@section('page_title', 'Platform settings')
@section('page_description', 'Publish notices to registered users and maintain your administration policy notes.')
@section('content')
<div class="two-columns">
<section class="panel"><header class="panel-header"><div><h2>Publish an announcement</h2><p>Delivered to the selected accounts as notifications.</p></div></header><form class="form-stack padded" method="POST" action="{{ route('admin.settings.announcement') }}">@csrf
<label>Title<input name="title" value="{{ old('title') }}" maxlength="200" required></label><label>Audience<select name="target">@foreach(['all' => 'All non-admin users', 'buyer' => 'Buyers', 'seller' => 'Sellers', 'courier' => 'Couriers', 'logistics' => 'Logistics'] as $value => $label)<option value="{{ $value }}" @selected(old('target') === $value)>{{ $label }}</option>@endforeach</select></label><label>Message<textarea name="content" maxlength="5000" rows="5" required>{{ old('content') }}</textarea></label><button class="button">Publish announcement</button></form></section>
<section class="panel"><header class="panel-header"><div><h2>Published announcements</h2><p>Notices saved by your administrators</p></div></header>
@forelse($announcements as $announcement)<article class="case"><span class="badge">{{ ucfirst($announcement->target) }}</span><h3>{{ $announcement->title }}</h3><p class="preserve-lines">{{ $announcement->content }}</p><small class="muted">{{ \Carbon\Carbon::parse($announcement->created_at)->format('M j, Y · g:i A') }}</small></article>@empty<div class="empty"><strong>No announcements yet</strong><p>Published notices will appear here.</p></div>@endforelse
@include('admin.partials.pagination', ['paginator' => $announcements])</section></div>
<section class="panel"><header class="panel-header"><div><h2>Policy notes</h2><p>Internal reference for administrators. These notes do not change checkout rates or automate refunds.</p></div></header>
<form class="form-stack padded" action="{{ route('admin.settings.policies') }}" method="POST">@csrf
@foreach(['terms_of_service' => 'Terms of service', 'seller_guidelines' => 'Seller guidelines', 'dispute_policy' => 'Dispute policy'] as $key => $label)<label>{{ $label }}<textarea name="{{ $key }}" maxlength="20000" rows="4">{{ old($key, $policies[$key] ?? '') }}</textarea></label>@endforeach
<button class="button">Save policy notes</button></form></section>
@endsection
