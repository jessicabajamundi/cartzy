@extends('layouts.admin')
@section('page_title', 'User accounts')
@section('page_description', 'Manage access for the accounts registered on your marketplace.')
@section('content')
<section class="panel">
@include('admin.partials.user-filters', ['statuses' => ['active', 'pending', 'rejected', 'suspended', 'deactivated']])
<div class="table-scroll"><table><thead><tr><th>Account</th><th>Role</th><th>Joined</th><th>Status</th><th>Manage access</th></tr></thead><tbody>
@forelse($users as $user)
<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small><small>{{ $user->phone }}</small></td><td>{{ ucfirst($user->role) }}</td><td class="nowrap">{{ $user->created_at?->format('M j, Y') }}</td><td><span class="badge {{ $user->status === 'active' ? 'good' : '' }}">{{ ucfirst($user->status) }}</span></td><td>
@if($user->isAdmin())<span class="muted">Administrator</span>
@elseif(in_array($user->status, ['pending', 'rejected']))<a class="text-link" href="{{ route('admin.registrations', ['search' => $user->email]) }}">Review registration →</a>
@else<form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline-form" data-confirm="Save this account access change?">@csrf<select name="status" aria-label="Status for {{ $user->name }}">@foreach(['active', 'suspended', 'deactivated'] as $status)<option @selected($user->status === $status) value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select><button class="button subtle">Save</button></form>@endif
</td></tr>
@empty<tr><td colspan="5"><div class="empty"><strong>No matching accounts</strong><p>Try changing your search or filters.</p></div></td></tr>@endforelse
</tbody></table></div>@include('admin.partials.pagination', ['paginator' => $users])
</section>
@endsection
