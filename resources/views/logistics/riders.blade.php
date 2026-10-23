@extends('layouts.logistics')
@section('title', 'Rider Management | Logistics Hub | cartzy')
@section('page_title', 'Rider Management')
@section('page_subtitle', 'Review applications and manage your delivery team')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-riders.css') }}?v={{ filemtime(public_path('css/logistics-riders.css')) }}">
@endpush
@section('content')
<section class="rider-workspace" aria-label="Rider management">
    <div class="rider-heading"><div><span class="rider-eyebrow">YOUR DELIVERY TEAM</span><h2>Rider directory</h2><p>Find a rider, review an application, or update availability.</p></div><span class="rider-total">{{ $counts['all'] }} {{ Str::plural('rider', $counts['all']) }} in your hub</span></div>
    <div class="rider-metrics">
        @foreach(['pending' => ['Awaiting review', 'Applications that need your attention'], 'active' => ['Active riders', 'Approved and marked active'], 'inactive' => ['Inactive riders', 'Approved and currently inactive']] as $key => [$label, $detail])
        <a class="rider-metric rider-metric-{{ $key }}" href="{{ route('logistics.riders', ['filter' => $key]) }}"><span>{{ $label }} <span aria-hidden="true">↗</span></span><strong>{{ $counts[$key] }}</strong><small>{{ $detail }}</small></a>
        @endforeach
    </div>
    <div class="rider-panel">
        <form method="GET" action="{{ route('logistics.riders') }}" class="rider-search">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div><label for="rider-search">Find a rider</label><input id="rider-search" type="search" name="search" value="{{ $search }}" placeholder="Name, email, phone, area or plate number"></div>
            <button class="rider-button rider-primary" type="submit">Search riders</button>
            @if($search !== '')<a class="rider-button" href="{{ route('logistics.riders', ['filter' => $filter]) }}">Clear search</a>@endif
        </form>
        <nav class="rider-filters" aria-label="Filter riders by status">
            @foreach(['all'=>'All riders','pending'=>'Pending','approved'=>'Approved','active'=>'Active','inactive'=>'Inactive','suspended'=>'Suspended','rejected'=>'Rejected'] as $key=>$label)
            <a href="{{ route('logistics.riders', ['filter' => $key, 'search' => $search]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$key] }}</span></a>
            @endforeach
        </nav>
        <div class="rider-results"><span>{{ $riders->total() }} {{ Str::plural('result', $riders->total()) }}@if($search !== '') for “{{ $search }}”@endif</span><span>Pending applications appear first</span></div>
        <div class="rider-table-wrap">
            <table class="rider-table">
                <caption class="rider-sr-only">Riders and their contact information, vehicle, status and available actions</caption>
                <thead><tr><th scope="col">Rider</th><th scope="col">Vehicle & area</th><th scope="col">Status</th><th scope="col">Performance</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                @forelse($riders as $rider)
                    @php($state = $rider['status'] === 'approved' ? ($rider['is_active'] ? 'active' : 'inactive') : $rider['status'])
                    <tr>
                        <td data-label="Rider"><div class="rider-person"><span class="rider-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($rider['name'], 0, 1)) }}</span><div><strong>{{ $rider['name'] }}</strong><a href="mailto:{{ $rider['email'] }}">{{ $rider['email'] }}</a>@if($rider['phone'])<a href="tel:{{ $rider['phone'] }}">{{ $rider['phone'] }}</a>@else<small>No phone provided</small>@endif</div></div></td>
                        <td data-label="Vehicle & area"><strong>{{ $rider['vehicle_type'] }} <span class="rider-plate">{{ $rider['plate_no'] }}</span></strong><small>{{ $rider['area'] }}</small></td>
                        <td data-label="Status"><span class="rider-badge rider-badge-{{ $state }}">{{ ucfirst($state) }}</span><small>Applied {{ $rider['applied_at'] }}</small>@if($rider['rejection_reason'])<details class="rider-reason"><summary>Rejection reason</summary><p>{{ $rider['rejection_reason'] }}</p></details>@endif</td>
                        <td data-label="Performance">@if($rider['status'] === 'approved')<strong>{{ $rider['rating'] !== null ? number_format($rider['rating'], 2).' / 5' : 'No rating yet' }}</strong><small>{{ $rider['deliveries_today'] }} deliveries today</small>@else<span class="rider-muted">Not available</span>@endif</td>
                        <td data-label="Actions"><div class="rider-actions">
                        @if($rider['status'] === 'pending')
                            <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}">@csrf<input type="hidden" name="status" value="approved"><button class="rider-button rider-primary" aria-label="Approve {{ $rider['name'] }}">Approve</button></form>
                            <button type="button" class="rider-button rider-danger" data-reject-url="{{ route('logistics.riders.status', $rider['id']) }}" data-rider-name="{{ $rider['name'] }}" aria-label="Reject {{ $rider['name'] }}">Reject</button>
                        @elseif($rider['status'] === 'approved' || $rider['status'] === 'suspended')
                            <div class="rider-action-dropdown">
                                <button type="button" class="rider-action-trigger rider-dots-btn" aria-expanded="false" aria-haspopup="true" aria-label="Actions for {{ $rider['name'] }}" title="Actions">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="1.5"></circle>
                                        <circle cx="12" cy="5" r="1.5"></circle>
                                        <circle cx="12" cy="19" r="1.5"></circle>
                                    </svg>
                                </button>
                                <div class="rider-dropdown-menu hidden" role="menu">
                                    @if($rider['status'] === 'suspended')
                                        <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}" role="none">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="rider-dropdown-item text-emerald-700" role="menuitem">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                <span>Unsuspend & Activate</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('logistics.riders.toggle', $rider['id']) }}" role="none">
                                            @csrf
                                            <input type="hidden" name="active" value="0">
                                            <button type="submit" class="rider-dropdown-item text-gray-700" role="menuitem">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                                <span>Deactivate</span>
                                            </button>
                                        </form>
                                    @else
                                        @if($rider['is_active'])
                                            <form method="POST" action="{{ route('logistics.riders.toggle', $rider['id']) }}" role="none">
                                                @csrf
                                                <input type="hidden" name="active" value="0">
                                                <button type="submit" class="rider-dropdown-item text-gray-700" role="menuitem">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                                    <span>Deactivate</span>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('logistics.riders.toggle', $rider['id']) }}" role="none">
                                                @csrf
                                                <input type="hidden" name="active" value="1">
                                                <button type="submit" class="rider-dropdown-item text-emerald-700 font-semibold" role="menuitem">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                    <span>Activate</span>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}" role="none" onsubmit="return confirm('Are you sure you want to suspend this rider?');">
                                            @csrf
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="rider-dropdown-item rider-dropdown-item-danger" role="menuitem">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <span>Suspend</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('logistics.riders.status', $rider['id']) }}">@csrf<input type="hidden" name="status" value="pending"><button class="rider-button" aria-label="Review again: {{ $rider['name'] }}">Review again</button></form>
                        @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="rider-empty"><h3>No riders found</h3><p>Try another search or status to find the rider you need.</p><a class="rider-button" href="{{ route('logistics.riders') }}">View all riders</a></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($riders->hasPages())<div class="rider-pagination">{{ $riders->links() }}</div>@endif
    </div>
</section>
<dialog id="rider-reject-dialog" class="rider-dialog" aria-labelledby="rider-reject-title" aria-describedby="rider-reject-name">
    <form id="rider-reject-form" method="POST">@csrf<input type="hidden" name="status" value="rejected">
        <span class="rider-eyebrow">APPLICATION REVIEW</span><h2 id="rider-reject-title">Reject application?</h2><p id="rider-reject-name"></p>
        <label for="rider-reject-reason">Reason for rejection <span>(optional)</span></label><textarea id="rider-reject-reason" name="reason" rows="4" maxlength="1000" placeholder="Explain what needs to be corrected before reapplying."></textarea>
        <p class="rider-dialog-help">You can return this application to review from the Rejected tab.</p>
        <div class="rider-dialog-actions"><button type="button" class="rider-button" id="rider-reject-cancel">Cancel</button><button type="submit" class="rider-button rider-reject-confirm">Reject application</button></div>
    </form>
</dialog>
@endsection
@push('scripts')
<script src="{{ asset('js/logistics-riders.js') }}?v={{ filemtime(public_path('js/logistics-riders.js')) }}" defer></script>
@endpush
