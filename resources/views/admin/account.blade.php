@extends('layouts.admin')
@section('page_title', 'My account')
@section('page_description', 'Manage your administrator profile and sign-in credentials.')
@section('content')
<div class="two-columns">
<section class="panel"><header class="panel-header"><div><h2>Profile details</h2><p>{{ $adminUser->email }}</p></div></header><form class="form-stack padded" action="{{ route('admin.account.update') }}" method="POST">@csrf<input type="hidden" name="section" value="profile">
<label>Name<input name="name" value="{{ old('name', $adminUser->name) }}" maxlength="255" required autocomplete="name"></label><label>Email<input type="email" name="email" value="{{ old('email', $adminUser->email) }}" maxlength="255" required autocomplete="email"></label><label>Phone<input name="phone" value="{{ old('phone', $adminUser->phone) }}" maxlength="30" autocomplete="tel"></label><button class="button">Save profile</button></form></section>
<section class="panel"><header class="panel-header"><div><h2>Change password</h2><p>Use at least 10 characters for your new password.</p></div></header><form class="form-stack padded" action="{{ route('admin.account.update') }}" method="POST">@csrf<input type="hidden" name="section" value="password"><label>Current password<input type="password" name="current_password" required autocomplete="current-password"></label><label>New password<input type="password" name="password" minlength="10" required autocomplete="new-password"></label><label>Confirm new password<input type="password" name="password_confirmation" minlength="10" required autocomplete="new-password"></label><button class="button">Update password</button></form></section>
</div><section class="panel"><header class="panel-header"><div><h2>Your recent activity</h2><p>Actions recorded for your administrator account</p></div></header>@include('admin.partials.activity')</section>
@endsection
