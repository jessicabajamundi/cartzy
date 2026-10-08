@extends('layouts.admin')
@section('page_title', 'Complaints & Disputes')
@section('page_description', 'Record and review buyer and seller complaints, examine details, and coordinate resolution with buyers, sellers, and couriers.')
@section('content')
<section class="panel padded">
    <details>
        <summary class="text-link">Record a dispute / complaint</summary>
        <form class="form-grid spaced" method="POST" action="{{ route('admin.disputes.store') }}">
            @csrf
            <label>Order reference
                <input name="reference" value="{{ old('reference') }}" required maxlength="255" placeholder="Enter an existing order reference (e.g. ORD-...)">
            </label>
            <label>Subject / Claim
                <input name="subject" value="{{ old('subject') }}" maxlength="200" required placeholder="Reason for complaint (e.g. Damaged item, Wrong parcel, Delivery dispute)">
            </label>
            <label class="full">Complaint Details & Supporting Evidence Notes
                <textarea name="description" rows="4" maxlength="5000" required placeholder="Describe complaint findings, evidence reported, damage claims, or tracking logs">{{ old('description') }}</textarea>
            </label>
            <div><button class="button">Record dispute</button></div>
        </form>
    </details>
</section>

<section class="panel">
    <form class="filters" method="GET">
        <label>Status
            <select name="status">
                <option value="">All disputes</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
            </select>
        </label>
        <button class="button">Apply filter</button>
    </form>

    <div class="case-list">
    @forelse($disputes as $dispute)
        @php
            $order = isset($ordersMap) ? $ordersMap->get($dispute->order_id) : null;
        @endphp
        <article class="case">
            <div class="case-heading">
                <div>
                    <span class="eyebrow">CASE #{{ $dispute->id }} · {{ $dispute->reference }}</span>
                    <h2>{{ $dispute->subject }}</h2>
                    <p class="muted">{{ $dispute->buyer_name }} · {{ \Carbon\Carbon::parse($dispute->created_at)->format('M j, Y') }}</p>
                </div>
                <span class="badge {{ $dispute->status === 'resolved' ? 'good' : '' }}">{{ ucfirst($dispute->status) }}</span>
            </div>

            <div style="margin: 12px 0;">
                <strong style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--accent-hover);">Complaint Details:</strong>
                <p class="preserve-lines" style="margin-top: 4px;">{{ $dispute->description }}</p>
            </div>

            {{-- Coordinated Parties: Buyer, Seller, Courier --}}
            <div style="background: var(--canvas); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; margin: 16px 0;">
                <span class="eyebrow" style="margin-bottom: 8px; display: block;">Coordinate with Parties</span>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    {{-- Buyer Card --}}
                    <div style="flex: 1; min-width: 170px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 10px 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span class="badge" style="font-size: 9px;">Buyer</span>
                            @if($order?->buyer_id)
                                <a href="{{ route('admin.chat', ['contact' => $order->buyer_id]) }}" class="text-link" style="font-size: 11px;">Coordinate ↗</a>
                            @endif
                        </div>
                        <strong>{{ $dispute->buyer_name }}</strong>
                        <small class="muted">{{ $order?->buyer?->email ?? 'Buyer contact' }}</small>
                    </div>

                    {{-- Seller Card --}}
                    @if($order && $order->sellerOrders->isNotEmpty())
                        @foreach($order->sellerOrders as $so)
                            @if($so->seller)
                            <div style="flex: 1; min-width: 170px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 10px 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span class="badge" style="font-size: 9px;">Seller</span>
                                    @if($so->seller->user_id)
                                        <a href="{{ route('admin.chat', ['contact' => $so->seller->user_id]) }}" class="text-link" style="font-size: 11px;">Coordinate ↗</a>
                                    @endif
                                </div>
                                <strong>{{ $so->seller->name }}</strong>
                                <small class="muted">{{ $so->seller->user?->email ?? 'Store account' }}</small>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div style="flex: 1; min-width: 170px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 10px 12px;">
                            <span class="badge" style="font-size: 9px; margin-bottom: 4px; display: inline-block;">Seller</span>
                            <p class="muted" style="font-size: 11px;">Store assigned to order</p>
                        </div>
                    @endif

                    {{-- Courier Card --}}
                    @php
                        $foundCourier = false;
                    @endphp
                    @if($order && $order->sellerOrders->isNotEmpty())
                        @foreach($order->sellerOrders as $so)
                            @if($so->shipment)
                                @php $foundCourier = true; @endphp
                                <div style="flex: 1; min-width: 170px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 10px 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <span class="badge" style="font-size: 9px;">Courier</span>
                                        @if(optional($so->shipment->rider)->user_id)
                                            <a href="{{ route('admin.chat', ['contact' => $so->shipment->rider->user_id]) }}" class="text-link" style="font-size: 11px;">Coordinate ↗</a>
                                        @elseif(optional($so->shipment->logisticsProvider)->user_id)
                                            <a href="{{ route('admin.chat', ['contact' => $so->shipment->logisticsProvider->user_id]) }}" class="text-link" style="font-size: 11px;">Coordinate ↗</a>
                                        @endif
                                    </div>
                                    <strong>{{ optional($so->shipment->rider?->user)->name ?? optional($so->shipment->logisticsProvider)->name ?? 'Assigned Courier' }}</strong>
                                    <small class="muted">Tracking: {{ $so->shipment->tracking_code ?: 'In transit' }}</small>
                                </div>
                            @endif
                        @endforeach
                    @endif
                    @if(!$foundCourier)
                        <div style="flex: 1; min-width: 170px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: 10px 12px;">
                            <span class="badge" style="font-size: 9px; margin-bottom: 4px; display: inline-block;">Courier</span>
                            <p class="muted" style="font-size: 11px;">No courier dispatched yet</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($dispute->status === 'open')
                <details>
                    <summary class="text-link">Record resolution</summary>
                    <form class="form-stack spaced" action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST">
                        @csrf
                        <label>Resolution notes
                            <textarea name="notes" maxlength="5000" rows="3" required placeholder="Record terms of settlement, findings, or coordination outcomes"></textarea>
                        </label>
                        <small class="muted">This records the decision only. Payments and refunds must be handled separately.</small>
                        <button class="button">Mark resolved</button>
                    </form>
                </details>
            @else
                <div class="notice">
                    <strong>Resolution</strong>
                    <p class="preserve-lines">{{ $dispute->admin_notes }}</p>
                </div>
            @endif
        </article>
    @empty
        <div class="empty">
            <strong>No disputes recorded</strong>
            <p>Complaints linked to existing orders will appear here.</p>
        </div>
    @endforelse
    </div>
    @include('admin.partials.pagination', ['paginator' => $disputes])
</section>
@endsection
