@extends('layouts.admin')
@section('page_title', 'Activity logs')
@section('page_description', 'See who signed in and out of the marketplace, and when.')
@section('content')
@php
    $events = ['login' => ['Signed in', 'good'], 'logout' => ['Signed out', ''], 'failed_login' => ['Failed sign-in', 'danger']];
@endphp
<div class="stats-grid">
    @foreach($events as $key => [$label, $tone])
    <a class="stat-card" href="{{ route('admin.activity-logs', ['event' => $key, 'from' => today()->toDateString(), 'to' => today()->toDateString()]) }}"><div class="metric-heading"><span class="metric-icon">@include('admin.partials.icon', ['name' => $key === 'logout' ? 'logout' : ($key === 'login' ? 'user' : 'shield')])</span><span>{{ $label }}</span></div><span>Today</span><strong>{{ number_format($today[$key] ?? 0) }}</strong><small>View today's entries →</small></a>
    @endforeach
</div>
<section class="panel">
<form class="filters" method="GET">
<label class="search-field"><span>Search</span><input name="search" value="{{ request('search') }}" placeholder="Name, email or IP address"></label>
<label><span>Activity</span><select name="event"><option value="">All activity</option>@foreach($events as $key => [$label])<option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>@endforeach</select></label>
<label><span>Role</span><select name="role"><option value="">All roles</option>@foreach(['buyer', 'seller', 'courier', 'logistics', 'admin'] as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
<label><span>From</span><input type="date" name="from" value="{{ request('from') }}"></label>
<label><span>To</span><input type="date" name="to" value="{{ request('to') }}"></label>
<button class="button">Apply filters</button><a class="button subtle" href="{{ url()->current() }}">Reset</a>
</form>
<div class="table-scroll"><table><thead><tr><th>Date &amp; time</th><th>Account</th><th>Role</th><th>Activity</th><th>Details</th><th>IP address</th><th>Device</th></tr></thead><tbody>
@forelse($logs as $log)
@php [$label, $tone] = $events[$log->event] ?? [ucfirst($log->event), '']; @endphp
<tr>
<td class="nowrap">{{ $log->created_at?->format('M j, Y') }}<small>{{ $log->created_at?->format('g:i:s A') }}</small></td>
<td>@if($log->user)<strong>{{ $log->user->name }}</strong><small>{{ $log->user->email }}</small>@else<strong>{{ $log->email ?? 'Unknown' }}</strong><small>No matching account</small>@endif</td>
<td>{{ $log->role ? ucfirst($log->role) : '—' }}</td>
<td><span class="badge {{ $tone }}">{{ $label }}</span></td>
<td>{{ $log->method ? ucfirst($log->method) : '' }}@if($log->description)<small>{{ $log->description }}</small>@endif</td>
<td class="nowrap">{{ $log->ip_address ?? '—' }}</td>
<td><small title="{{ $log->user_agent }}">{{ \Illuminate\Support\Str::limit($log->user_agent ?? '—', 48) }}</small></td>
</tr>
@empty<tr><td colspan="7"><div class="empty"><strong>No activity recorded</strong><p>Sign-ins and sign-outs will appear here.</p></div></td></tr>@endforelse
</tbody></table></div>@include('admin.partials.pagination', ['paginator' => $logs])
</section>
@endsection
