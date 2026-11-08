@extends('layouts.seller')
@section('page_title', 'Order Details #' . $order->order->reference)

@section('content')
<div class="waybill-page">
    {{-- Top Action Bar (Hidden during Print) --}}
    <div class="page-heading print-hide">
        <div class="heading-left">
            <a href="{{ route('seller.orders') }}" class="back-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Back to Orders</span>
            </a>
            <span class="order-eyebrow">ORDER MANAGEMENT</span>
            <h1>Order details & shipping</h1>
            <p class="subtitle">Reference: <strong>{{ $order->order->reference }}</strong> · Placed on {{ $order->created_at->format('M d, Y · h:i A') }}</p>
        </div>
        <div class="heading-actions">
            <button class="button" onclick="window.print()" type="button" title="Print Airway Bill (Waybill)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print waybill
            </button>
        </div>
    </div>

    @if(request()->boolean('print'))<script>window.addEventListener('load', () => window.print());</script>@endif

    {{-- Delivery Status & Rider Verification Banner (Hidden during Print) --}}
    <div class="print-hide status-banner-wrapper">
        @if(in_array($order->status, ['delivered', 'completed']) || ($order->shipment && $order->shipment->status === 'delivered'))
            <div class="delivery-proof-card delivered">
                <div class="proof-header">
                    <div class="proof-icon-box success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="proof-meta">
                        <span class="proof-badge success">DELIVERY VERIFIED · RIDER SCANNED</span>
                        <h2>Product Successfully Delivered to Buyer</h2>
                        <p class="proof-timestamp">
                            Delivered on <strong>{{ $order->delivered_at ? $order->delivered_at->copy()->timezone('Asia/Manila')->format('F d, Y · h:i A') : now()->timezone('Asia/Manila')->format('F d, Y · h:i A') }}</strong>
                        </p>
                    </div>
                </div>

                <div class="proof-details-grid">
                    <div class="proof-col">
                        <span class="col-label">Delivering Courier & Rider</span>
                        <p class="col-value"><strong>{{ $order->shipment?->rider?->user?->name ?? 'Assigned Logistics Rider' }}</strong></p>
                        <p class="col-sub">{{ $order->shipment?->logisticsProvider?->name ?? 'Express Partner Hub' }} · {{ $order->shipment?->rider?->user?->phone ?? 'Contact on file' }}</p>
                    </div>
                    <div class="proof-col">
                        <span class="col-label">Scanned Waybill / Tracking Code</span>
                        <p class="col-value"><span class="code-pill">{{ $order->shipment?->tracking_code ?? 'CTZ-PARCEL' }}</span></p>
                        <p class="col-sub">100% Optical Barcode Verified</p>
                    </div>
                    <div class="proof-col">
                        <span class="col-label">Delivery Note & Recipient Confirmation</span>
                        @php
                            $delEvent = $order->shipment?->deliveryEvents?->firstWhere('status', 'delivered');
                        @endphp
                        <p class="col-value">{{ $delEvent?->note ?? 'Delivered and confirmed via rider optical barcode scan.' }}</p>
                        <p class="col-sub">Status: Package received & inspected by consignee</p>
                    </div>
                </div>
            </div>
        @elseif($order->status === 'shipped' || ($order->shipment && in_array($order->shipment->status, ['in_transit', 'out_for_delivery'])))
            <div class="delivery-proof-card in-transit">
                <div class="proof-header">
                    <div class="proof-icon-box warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </div>
                    <div class="proof-meta">
                        <span class="proof-badge warning">{{ strtoupper(str_replace('_', ' ', $order->shipment?->status ?? 'IN TRANSIT')) }}</span>
                        <h2>{{ $order->shipment?->status === 'out_for_delivery' ? 'Your parcel is out for delivery' : 'Your parcel is on its way' }}</h2>
                        <p class="proof-timestamp">
                            Assigned Rider: <strong>{{ $order->shipment?->rider?->user?->name ?? 'Awaiting Rider Scan' }}</strong> ({{ $order->shipment?->logisticsProvider?->name ?? 'Partner Hub' }})
                        </p>
                    </div>
                </div>
                <div class="proof-notice">
                    Delivery confirmation will appear here once the rider scans the parcel at the buyer's address.
                </div>
            </div>
        @else
            <div class="delivery-proof-card pending">
                <div class="proof-header">
                    <div class="proof-icon-box neutral">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <div class="proof-meta">
                        <span class="proof-badge neutral">READY TO SHIP / AWAITING PICKUP</span>
                        <h2>Waybill Generated · Ready for Rider Collection</h2>
                        <p class="proof-timestamp">Tracking Code: <strong>{{ $order->shipment?->tracking_code }}</strong></p>
                    </div>
                </div>
                <div class="proof-notice">
                    Print the shipping label below and securely paste it on top of your packaged item. The courier rider will scan this barcode during pickup and final delivery.
                </div>
            </div>
        @endif
    </div>

    <dl class="order-overview print-hide" aria-label="Order summary">
        <div><dt>Tracking number</dt><dd class="tracking-reference">{{ $order->shipment?->tracking_code ?? 'Awaiting assignment' }}</dd></div>
        <div><dt>Delivery partner</dt><dd>{{ $order->shipment?->logisticsProvider?->name ?? 'Awaiting assignment' }}</dd></div>
        <div><dt>Payment method</dt><dd>{{ $order->order->payment_method === 'cod' ? 'Cash on delivery' : 'Online payment' }}</dd></div>
        <div><dt>Order total <span> / {{ $order->items->sum('quantity') }} items</span></dt><dd class="overview-total">&#8369;{{ number_format(($order->subtotal_minor + $order->shipping_fee_minor) / 100, 2) }}</dd></div>
    </dl>

    <div class="order-detail-grid">
    {{-- Interactive Tracking Timeline (Hidden during Print) --}}
    @if(isset($tracking) && !empty($tracking['steps']))
    <div class="panel print-hide tracking-timeline-panel">
        <div class="panel-header">
            <h3>Delivery timeline</h3>
            <span class="last-updated">Latest update: {{ $tracking['last_update'] ?? 'Recent' }}</span>
        </div>
        <div class="timeline-steps-container">
            @foreach($tracking['steps'] as $step)
                <div class="timeline-step {{ !empty($step['done']) ? 'completed' : 'pending' }} {{ !empty($step['current']) ? 'current' : '' }}" @if(!empty($step['current'])) aria-current="step" @endif>
                    <div class="timeline-indicator">
                        <div class="timeline-dot">
                            @if(!empty($step['done']))
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endif
                        </div>
                        <div class="timeline-bar"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">{{ $step['label'] }} @if(!empty($step['current']))<span class="current-step-label">Current</span>@endif</div>
                        <div class="timeline-time">{{ $step['time'] }}</div>
                        <div class="timeline-desc">{{ $step['detail'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @include('partials.waybill-label')
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/waybill-label.css') }}?v={{ filemtime(public_path('css/waybill-label.css')) }}">
<link rel="stylesheet" href="{{ asset('css/seller-waybill.css') }}?v={{ filemtime(public_path('css/seller-waybill.css')) }}">
<style>
/* Waybill & Order Details Custom Styles */
.waybill-page {
    max-width: 960px;
    margin: 0 auto;
    padding-bottom: 60px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    text-decoration: none;
    margin-bottom: 8px;
    transition: color 0.15s;
}
.back-link:hover {
    color: #111827;
}

.status-banner-wrapper {
    margin-bottom: 24px;
}

.delivery-proof-card {
    border-radius: 16px;
    padding: 24px;
    border: 1px solid;
    margin-bottom: 20px;
}

.delivery-proof-card.delivered {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #14532d;
}

.delivery-proof-card.in-transit {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1e3a8a;
}

.delivery-proof-card.pending {
    background: #f9fafb;
    border-color: #e5e7eb;
    color: #374151;
}

.proof-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.proof-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.proof-icon-box.success { background: #22c55e; color: #ffffff; }
.proof-icon-box.warning { background: #3b82f6; color: #ffffff; }
.proof-icon-box.neutral { background: #6b7280; color: #ffffff; }

.proof-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 3px 8px;
    border-radius: 6px;
    margin-bottom: 4px;
}
.proof-badge.success { background: #dcfce7; color: #15803d; }
.proof-badge.warning { background: #dbeafe; color: #1d4ed8; }
.proof-badge.neutral { background: #e5e7eb; color: #374151; }

.proof-meta h2 {
    font-size: 18px;
    font-weight: 800;
    margin: 2px 0 4px 0;
}
.proof-timestamp {
    font-size: 13px;
    opacity: 0.85;
    margin: 0;
}

.proof-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed rgba(0, 0, 0, 0.12);
}

.proof-col .col-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.75;
    margin-bottom: 4px;
}
.proof-col .col-value {
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 2px 0;
}
.proof-col .col-sub {
    font-size: 12px;
    opacity: 0.8;
    margin: 0;
}

.code-pill {
    font-family: monospace;
    background: rgba(0, 0, 0, 0.06);
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 800;
}

.proof-notice {
    font-size: 13px;
    line-height: 1.5;
    opacity: 0.9;
}

/* Tracking Timeline */
.tracking-timeline-panel {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 28px;
}
.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
    padding-bottom: 12px;
}
.panel-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #111827;
}
.panel-header .last-updated {
    font-size: 12px;
    color: #6b7280;
}

.timeline-steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 8px;
}
.timeline-step {
    position: relative;
    padding: 8px 4px;
}
.timeline-indicator {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}
.timeline-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    z-index: 2;
    flex-shrink: 0;
}
.timeline-step.completed .timeline-dot {
    background: #10b981;
}
.timeline-step.current .timeline-dot {
    background: #2563eb;
    box-shadow: 0 0 0 4px #dbeafe;
}
.timeline-bar {
    flex: 1;
    height: 3px;
    background: #e5e7eb;
    margin-left: -2px;
}
.timeline-step.completed .timeline-bar {
    background: #10b981;
}
.timeline-step:last-child .timeline-bar {
    display: none;
}
.timeline-title {
    font-size: 12px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}
.timeline-time {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 4px;
}
.timeline-desc {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* PRINT MEDIA STYLES: Only Print Waybill */
@media print {
    body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .print-hide, nav, header, aside, .sidebar, footer {
        display: none !important;
    }
    .waybill-page {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .waybill-printable-card {
        border: 2px solid #000000 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 12px !important;
        page-break-inside: avoid;
    }
    .awb-barcode-container svg {
        width: 320px !important;
        height: 75px !important;
    }
}
</style>
@endsection
