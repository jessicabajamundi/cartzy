@php
    $p = $parcel;
    $s = $p['delivery_status'] ?? 'pending';
    $step1 = true;
    $step2 = in_array($s, ['at_hub', 'assigned', 'in_transit', 'out_for_delivery', 'delivered']);
    $step3 = in_array($s, ['assigned', 'in_transit', 'out_for_delivery', 'delivered']);
    $step4 = ($s === 'delivered');
@endphp

<div class="parcel-header">
    <div>
        <div class="tracking-badge">{{ $p['tracking_code'] ?? '' }}</div>
        <div class="order-ref">Order Ref: <strong>{{ $p['order_reference'] ?? 'N/A' }}</strong> · Area: <strong style="color:#564b68;">{{ $p['delivery_area'] ?? 'General Area' }}</strong></div>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <span class="status-pill {{ $p['delivery_status'] ?? 'pending' }}">{{ $p['status_label'] ?? 'Pending' }}</span>
    </div>
</div>

<!-- Whereabouts Live Banner -->
<div class="whereabouts-banner">
    <div class="whereabouts-pin">📍</div>
    <div>
        <div class="whereabouts-title">Current Whereabouts & Physical Stage</div>
        <div class="whereabouts-text">{{ $p['whereabouts'] ?? 'Locating parcel...' }}</div>
    </div>
</div>

<!-- Route Stepper -->
<div class="route-stepper">
    <div class="route-step {{ $step1 ? 'completed' : '' }}">
        <div class="step-circle">{{ $step1 ? '✓' : '1' }}</div>
        <span class="step-label">Picked Up</span>
        <span class="step-sub">From Seller</span>
    </div>
    <div class="route-step {{ $step2 ? ($step3 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">{{ $step2 && $step3 ? '✓' : '2' }}</div>
        <span class="step-label">Sorting Hub</span>
        <span class="step-sub">Metro South</span>
    </div>
    <div class="route-step {{ $step3 ? ($step4 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">{{ $step3 && $step4 ? '✓' : '3' }}</div>
        <span class="step-label">Rider Out</span>
        <span class="step-sub">En Route</span>
    </div>
    <div class="route-step {{ $step4 ? 'completed' : '' }}">
        <div class="step-circle">{{ $step4 ? '✓' : '4' }}</div>
        <span class="step-label">Delivered</span>
        <span class="step-sub">Completed</span>
    </div>
</div>

<!-- Key Details Grid -->
<div class="details-grid">
    <div class="detail-tile">
        <div class="detail-tile-header">📦 Package Information</div>
        <div class="detail-tile-body">
            <strong>{{ $p['items'] ?? 'Items' }}</strong>
            <span>Weight: {{ $p['weight_kg'] ?? 1.0 }} kg · Payment: <strong style="display:inline;color:{{ ($p['payment_method'] ?? '') === 'COD' ? '#b45309' : '#059669' }};">{{ $p['payment_method'] ?? 'Prepaid' }} {{ ($p['payment_method'] ?? '') === 'COD' ? '₱' . number_format($p['cod_amount'] ?? 0, 2) : '' }}</strong></span>
        </div>
    </div>

    <div class="detail-tile">
        <div class="detail-tile-header">🛵 Delivery Rider</div>
        <div class="detail-tile-body">
            @if(!empty($p['assigned_rider']))
                <strong>{{ $p['assigned_rider']['name'] }} (★ {{ $p['assigned_rider']['rating'] }})</strong>
                <span style="display:block;color:#6f6382;font-size:11.5px;">{{ $p['assigned_rider']['vehicle'] }} · {{ $p['assigned_rider']['plate_no'] }}</span>
                <span style="display:block;color:#8c7f99;font-size:11.5px;">📞 {{ $p['assigned_rider']['phone'] }}</span>
            @else
                <p style="color:#8c7f99;font-size:12px;margin:0;">No rider assigned yet</p>
            @endif
        </div>
    </div>

    <div class="detail-tile">
        <div class="detail-tile-header">🏠 Recipient (Destination)</div>
        <div class="detail-tile-body">
            <strong>{{ $p['recipient_name'] ?? 'Recipient' }}</strong>
            <span>{{ $p['recipient_address'] ?? 'Address' }}</span>
            @if(!empty($p['recipient_phone']) && $p['recipient_phone'] !== 'N/A')
                <span style="display:block;margin-top:2px;">
                    <a href="tel:{{ $p['recipient_phone'] }}" style="color:#564b68;font-weight:700;text-decoration:none;">📞 {{ $p['recipient_phone'] }}</a>
                </span>
            @endif
        </div>
    </div>

    <div class="detail-tile">
        <div class="detail-tile-header">🏪 Store Sender (Origin)</div>
        <div class="detail-tile-body">
            <strong>{{ $p['seller_name'] ?? 'Store' }}</strong>
            <span>{{ $p['seller_address'] ?? 'Store Address' }}</span>
            @if(!empty($p['seller_phone']) && $p['seller_phone'] !== 'N/A')
                <span style="display:block;margin-top:2px;color:#8c7f99;">📞 {{ $p['seller_phone'] }}</span>
            @endif
        </div>
    </div>
</div>

<!-- Timeline Audit Trail -->
<div class="timeline-wrap">
    <div class="timeline-title">
        ⏱ Parcel Journey & Audit Events
    </div>
    <div class="timeline-list">
        @if(!empty($p['events']) && count($p['events']) > 0)
            @foreach($p['events'] as $ev)
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <strong>{{ $ev['status_label'] ?? '' }}</strong> - {{ $ev['note'] ?? '' }}
                        <div class="timeline-meta">{{ $ev['occurred_at'] ?? '' }} · by {{ $ev['user'] ?? 'Hub Staff' }}</div>
                    </div>
                </div>
            @endforeach
        @else
            <p style="color:#8c7f99;font-size:12.5px;margin:4px 0 0;">No audit events recorded yet.</p>
        @endif
    </div>
</div>

<!-- Hub Actions -->
<div class="parcel-actions">
    <a href="{{ $p['monitoring_url'] ?? '#' }}" class="action-btn primary">
        Open in Delivery Monitoring →
    </a>
    <a href="{{ $p['print_url'] ?? '#' }}" target="_blank" class="action-btn secondary">
        🖨 Print Waybill Label
    </a>
    @if(!empty($p['chat_url']))
        <a href="{{ $p['chat_url'] }}" class="action-btn success">
            💬 Message Assigned Rider
        </a>
    @endif
</div>
