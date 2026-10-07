<div class="activity-list">
@forelse($activity as $event)
<div class="activity-item"><span class="activity-dot"></span><div><strong>{{ $event->action }}</strong><p>{{ $event->description }}</p><small>{{ \Carbon\Carbon::parse($event->created_at)->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</small></div></div>
@empty<div class="empty"><strong>No activity yet</strong><p>Admin actions will appear here once recorded.</p></div>@endforelse
</div>
