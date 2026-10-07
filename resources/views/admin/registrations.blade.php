@extends('layouts.admin')
@section('page_title', 'Registrations')
@section('page_description', 'Review submitted identity documents and account applications.')
@section('content')
<section class="panel">
@include('admin.partials.user-filters', ['statuses' => ['unverified', 'pending', 'verified', 'rejected']])
<div class="table-scroll"><table><thead><tr><th>Applicant</th><th>Role / business</th><th>Submitted</th><th>ID status</th><th>Review</th></tr></thead><tbody>
@forelse($registrations as $user)
<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small><small>{{ $user->phone }}</small></td><td>{{ ucfirst($user->role) }}<small>{{ $user->business_name }}</small></td><td class="nowrap">{{ $user->created_at?->format('M j, Y') }}</td><td><span class="badge {{ $user->id_status === 'verified' ? 'good' : '' }}">{{ ucfirst($user->id_status) }}</span><small>Account: {{ $user->status }}</small></td><td>
<details class="review-details"><summary>Review application</summary><div>
<p><strong>ID type:</strong> {{ $user->id_type ?: 'Not supplied' }}</p><p><strong>ID number:</strong> {{ $user->id_number ?: 'Not supplied' }}</p>
@if($user->id_photo)<a class="text-link" href="{{ asset('storage/'.$user->id_photo) }}" target="_blank" rel="noopener">View identity document ↗</a>@else<p class="muted">No identity document uploaded.</p>@endif
@if($user->dti_permit)<p><a class="text-link" href="{{ asset('storage/'.$user->dti_permit) }}" target="_blank" rel="noopener">View business permit ↗</a></p>@endif
@if($user->id_rejection_reason)<p class="muted">Previous decision: {{ $user->id_rejection_reason }}</p>@endif
<form method="POST" action="{{ route('admin.registrations.status', $user->id) }}" class="form-stack" data-confirm="Save this registration decision?">@csrf
<label>Decision<select name="status"><option value="approved" @disabled(!$user->id_photo)>Approve</option><option value="rejected">Reject</option></select></label>
<label>Reason for rejection<textarea name="reason" maxlength="2000" rows="3"></textarea></label>
<button class="button">Save decision</button>
<small class="muted" style="display:block;margin-top:4px;font-size:11px;">Applicant will be notified of this decision via email and in-app notice.</small>
</form></div></details>
</td></tr>
@empty<tr><td colspan="5"><div class="empty"><strong>No matching registrations</strong><p>Submitted applications will appear here.</p></div></td></tr>@endforelse
</tbody></table></div>@include('admin.partials.pagination', ['paginator' => $registrations])
</section>
@endsection
