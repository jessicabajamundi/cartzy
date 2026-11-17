@extends($layout)
@section('title', 'Returns & refunds | Cartzy')
@section('page_title', 'Returns & refunds')
@section('page_description', 'Review requests, track returned parcels, and follow refund payments.')
@section('content')
<div class="returns-workspace">
    <header class="return-heading"><h1>Returns & refunds</h1><p>Follow each request from review to return delivery and refund.</p>
        @if(auth()->user()->role === 'buyer')<a href="{{ route('buyer.dashboard', ['tab' => 'orders']) }}">Back to orders · Start a request with “Report problem”</a>@endif
        @if(request('case'))<a href="{{ route('returns.index') }}">View all cases</a>@endif
    </header>
    @if(session('success'))<p class="return-notice" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<div class="return-notice" role="alert"><strong>Please check:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @foreach($cases as $case)
        @php
            $role = auth()->user()->role;
            $isAdmin = $role === 'admin';
            $report = $case->dispute;
            $labels = ['requested' => 'Awaiting seller review', 'awaiting_return' => 'Approved — arrange return', 'scheduled' => 'Return scheduled',
                'in_transit' => 'Returning to seller', 'received' => 'Received — awaiting inspection', 'inspection_failed' => 'Inspection rejected',
                'disputed' => 'Awaiting admin review', 'refund_pending' => 'Approved — awaiting refund payment', 'refunded' => 'Refund payment recorded', 'rejected' => 'Request rejected'];
            $actions = [];
            if (($role === 'seller' || $isAdmin) && ($case->status === 'requested' || ($isAdmin && $case->status === 'disputed'))) $actions = ['approve' => 'Approve request', 'reject' => 'Reject request'];
            if ($role === 'buyer' && $case->status === 'awaiting_return') $actions = ['schedule' => 'Arrange return pickup or drop-off'];
            if ($role === 'buyer' && in_array($case->status, ['rejected', 'inspection_failed'])) $actions = ['appeal' => 'Ask admin to review'];
            if (($role === 'logistics' || $isAdmin) && $case->status === 'scheduled') $actions = ['dispatch' => 'Assign return rider', 'collect' => $case->handover_method === 'pickup' ? 'Confirm parcel collected' : 'Confirm drop-off received at hub'];
            if (($role === 'seller' || $isAdmin) && $case->status === 'in_transit') $actions = ['receive' => 'Confirm returned parcel received'];
            if (($role === 'seller' || $isAdmin) && $case->status === 'received') $actions = ['inspect' => 'Record inspection'];
            if (($isAdmin || $case->canLogisticsDisburse(auth()->user())) && $case->status === 'refund_pending') $actions['refund'] = 'Credit buyer Cartzy Wallet';
        @endphp
        <article class="return-card" id="case-{{ $case->id }}">
            <header><div><h2>Case #{{ $case->id }} · {{ $report->order->reference }}</h2><p>{{ $report->sellerOrder->seller->name }} · {{ \App\Models\AdminDispute::RESOLUTIONS[$report->requested_resolution] }}</p></div><strong class="return-status">{{ $labels[$case->status] ?? $case->status }}</strong></header>
            <p><strong>{{ $report->subject }}</strong><br>{{ $report->description }}</p>
            @if($report->items)<ul>@foreach($report->items as $item)<li>{{ $item['name'] }} · {{ $item['variation'] ?? '' }} · Qty {{ $item['qty'] }}</li>@endforeach</ul>@endif
            @if($role !== 'logistics')
                @foreach($report->evidence ?? [] as $index => $path)<a class="return-link" href="{{ route('disputes.evidence', [$report->id, $index]) }}" target="_blank" rel="noopener">View buyer photo {{ $index + 1 }}</a>@endforeach
            @endif
            @if($case->amount_minor)<p><strong>Approved refund: PHP {{ number_format($case->amount_minor / 100, 2) }}</strong></p>@endif
            @php($originalPayment = $report->order->payments->sortByDesc('id')->first())
            <div class="return-address"><strong>Original payment</strong><p>{{ $report->order->payment_method === 'cod' ? 'Cash on delivery' : 'Online / direct GCash' }} &middot; {{ ucfirst(str_replace('_', ' ', $report->order->payment_status)) }}<br>Reference: {{ $originalPayment?->provider_ref ?: 'No online reference' }}<br>Verified by: {{ $originalPayment?->verifier?->name ?? 'No manual verification recorded' }} @if($originalPayment?->verified_at) &middot; {{ $originalPayment->verified_at->timezone('Asia/Manila')->format('M j, Y g:i A') }} @endif</p>
            @if($role === 'logistics' && $report->order->payment_method !== 'cod')<p>Online funds were sent directly to the seller. This hub tracks the return; administration records the completed refund after payment evidence is checked.</p>@endif</div>
            @if($case->review_notes)<p>Review: {{ $case->review_notes }}</p>@endif
            @if($case->return_address)<p class="return-address"><strong>Return to seller</strong><br>{{ $case->return_address }}</p>@endif
            @if($case->tracking_code)
                <div class="return-address"><strong>{{ $case->tracking_code }}</strong><p>{{ ucfirst($case->handover_method) }} · {{ $case->scheduled_date?->format('M j, Y') }} · {{ $case->logisticsProvider?->name }}</p>
                    @if($case->handover_method === 'pickup')<p>Pickup address: {{ $case->pickup_address }}</p>
                    @else<p>Drop-off hub: {{ \App\Services\HubRecommender::location($case->logisticsProvider) ?? 'Contact the hub for directions before travelling.' }}</p>@endif
                    <p>Buyer contact: {{ $report->contact_phone }}</p>
                    @if($case->rider)<p>Return rider: {{ $case->rider->user?->name }}</p>@endif
                </div>
            @endif
            @if($case->inspection_notes)<p>Inspection: {{ $case->inspection_notes }}</p>@endif
            @if($case->status === 'refund_pending')
                <p class="return-notice">The refund has been approved. It becomes spendable when an authorized administrator or handling COD hub completes the Cartzy Wallet credit. Pending amounts are not part of the available wallet balance.</p>
            @endif
            @if($case->refund)
                <p class="return-notice">PHP {{ number_format($case->refund->amount_minor / 100, 2) }} {{ $case->refund->method === 'cartzy_wallet' ? 'credited to Cartzy Wallet' : 'paid via '.strtoupper($case->refund->method) }}. Reference: {{ $case->refund->reference }} · {{ $case->refund->paid_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}.@if($case->refund->method !== 'cartzy_wallet')<br><a href="{{ route('returns.proof', $case->id) }}">Download payment receipt</a>@endif</p>
            @endif
            <div class="return-actions">@foreach($actions as $action => $label)
                <details><summary>{{ $label }}</summary>@include('returns.form')</details>
            @endforeach</div>
            <details class="return-history"><summary>Case history</summary><ol>
                @foreach($case->events as $event)<li><strong>{{ ucfirst(str_replace('_', ' ', $event->status)) }}</strong> · {{ $event->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }} · {{ $event->actor?->name ?? 'Former account' }}<p>{{ $event->notes }}</p></li>@endforeach
            </ol></details>
        </article>
    @endforeach
    @if($cases->isEmpty())<div class="return-card"><h2>No return or refund requests</h2><p>Requests will appear here when a buyer asks for a refund or a return through Report problem.</p></div>@endif
    {{ $cases->links() }}
</div>
<style>
.returns-workspace{max-width:1100px;margin:24px auto;padding:0 20px;color:var(--dm-ink, #302b3d);font-size:14px}.return-heading{margin-bottom:24px}.return-heading h1{font-size:28px}.return-heading p,.return-card header p{color:var(--dm-muted, #6b6477)}.return-card{background:var(--dm-surface, #fff);border:1px solid var(--dm-border, #e5e1eb);border-radius:16px;padding:24px;margin-bottom:20px}.return-card header{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}.return-card h2{font-size:18px;margin:0}.return-card p{margin:10px 0;white-space:pre-line;overflow-wrap:anywhere}.return-status{font-size:12px;background:var(--dm-elevated, #f0edf7);color:var(--dm-muted, #635078);padding:8px 12px;border-radius:20px;align-self:flex-start}.return-address,.return-notice{background:var(--dm-elevated, #f7f5fa);border:1px solid var(--dm-border, #e5e1eb);border-radius:10px;padding:14px}.returns-workspace a{color:var(--dm-muted, #655184);text-decoration:underline}.return-link{margin-right:12px}.return-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}.return-actions details{flex:1 1 280px;border:1px solid var(--dm-border, #e5e1eb);border-radius:10px;padding:12px}.returns-workspace summary{cursor:pointer;font-weight:600;color:var(--dm-muted, #655184)}.return-form{display:grid;gap:12px;margin-top:16px}.return-form label{display:grid;gap:6px;color:var(--dm-ink, #4f475e);font-size:13px}.return-form input,.return-form select,.return-form textarea{width:100%;min-height:40px;padding:9px;border:1px solid var(--dm-border, #d7d1e2);border-radius:8px;background:var(--dm-surface, #fff);color:var(--dm-ink, #302b3d);font:inherit}.return-form .return-check{display:flex;align-items:flex-start;gap:8px}.return-check input{width:18px;min-height:18px;flex-shrink:0}.return-button{background:#6f6382;color:white;border:0;padding:11px 16px;border-radius:9px;cursor:pointer;font:inherit;justify-self:start}.return-button:hover{background:#574b69}.return-button:focus-visible{outline:3px solid #a697bf;outline-offset:3px}.return-history{margin-top:20px}.return-history li{padding:8px 0}.return-history p{margin:4px 0}.return-form small{color:var(--dm-muted, #6b6477)}
</style>
@endsection
