{{--
    Printable waybill (airway bill) for one seller order. Used by the seller order page and the logistics hub.
    Params: $order (SellerOrder with order.buyer, items, seller.pickupAddress, shipment.logisticsProvider),
            $barcodeSvg, $qrSvg, optional $shop (defaults to the order's seller), optional $preview (show the screen heading).
--}}
@php $shop = $shop ?? $order->seller; @endphp
<div class="waybill-printable-card">
    @if($preview ?? true)<div class="waybill-preview-heading print-hide"><div><span class="order-eyebrow">SHIPPING DOCUMENT</span><h3>Waybill preview</h3></div><span class="print-ready">Ready to print</span></div>@endif
    {{-- Waybill Header --}}
    <div class="awb-header">
        <div class="awb-brand">
            <span class="awb-tag">EXPRESS LOGISTICS</span>
            <h2 class="awb-logo">CARTZY <span>EXPRESS</span></h2>
            <p class="awb-hub-name">{{ $order->shipment?->logisticsProvider?->name ?? 'Standard Domestic Delivery' }}</p>
        </div>
        <div class="awb-qr-area">
            {!! $qrSvg ?? '' !!}
            <span class="awb-routing">STD-DOM</span>
        </div>
    </div>

    {{-- Barcode Section --}}
    <div class="awb-barcode-block">
        <div class="awb-barcode-container">
            {!! $barcodeSvg ?? '' !!}
        </div>
        <div class="awb-tracking-text">
            TRACKING NO: <strong>{{ $order->shipment?->tracking_code ?? 'CTZ-PARCEL' }}</strong>
        </div>
    </div>

    {{-- Airway Bill Grid: Sender & Recipient --}}
    <div class="awb-parties-grid">
        <div class="awb-party-box sender">
            <div class="awb-party-header">
                <span class="awb-label">FROM (SELLER / SENDER)</span>
                <span class="awb-sublabel">Store Partner</span>
            </div>
            <div class="awb-party-body">
                <p class="awb-name"><strong>{{ $shop?->name ?? 'Store Partner' }}</strong></p>
                @if($shop?->pickupAddress)
                    <p class="awb-contact">{{ $shop->pickupAddress->recipient }} ({{ $shop->pickupAddress->phone }})</p>
                    <p class="awb-address">{{ $shop->pickupAddress->formatted_address }}</p>
                @else
                    <p class="awb-contact">{{ $order->seller?->user?->phone ?? 'Phone on record' }}</p>
                    <p class="awb-address">Shop pickup location registered on Cartzy Marketplace.</p>
                @endif
            </div>
        </div>

        <div class="awb-party-box recipient">
            <div class="awb-party-header">
                <span class="awb-label">SHIP TO (BUYER / CONSIGNEE)</span>
                <span class="awb-sublabel">Destination</span>
            </div>
            <div class="awb-party-body">
                <p class="awb-name"><strong>{{ $order->order->shipping_address['recipient'] ?? $order->order->buyer?->name }}</strong></p>
                <p class="awb-contact">Phone: {{ $order->order->shipping_address['phone'] ?? $order->order->buyer?->phone ?? 'N/A' }}</p>
                <p class="awb-address">
                    {{ $order->order->shipping_address['address'] ?? implode(', ', array_filter(Arr::only($order->order->shipping_address, ['line1', 'barangay', 'city', 'province', 'postal_code']))) }}
                </p>
            </div>
        </div>
    </div>

    {{-- COD / Payment Badge Box --}}
    <div class="awb-payment-strip">
        @if($order->order->payment_method === 'cod')
            <div class="awb-payment-box cod">
                <div class="payment-title">CASH ON DELIVERY (COD)</div>
                <div class="payment-amount">₱{{ number_format(($order->shipment?->cod_amount_minor ?? ($order->subtotal_minor + $order->shipping_fee_minor)) / 100, 2) }}</div>
                <div class="payment-hint">Rider: Collect exact cash from buyer before releasing package</div>
            </div>
        @else
            <div class="awb-payment-box prepaid">
                <div class="payment-title">PREPAID ORDER (PAID ONLINE)</div>
                <div class="payment-amount">₱0.00 TO COLLECT</div>
                <div class="payment-hint">Do NOT collect payment from buyer upon delivery</div>
            </div>
        @endif

        <div class="awb-order-meta-box">
            <div><span>Order Reference:</span> <strong>{{ $order->order->reference }}</strong></div>
            <div><span>Order Date:</span> <strong>{{ $order->created_at->format('M d, Y') }}</strong></div>
            <div><span>Total Items:</span> <strong>{{ $order->items->sum('quantity') }} pcs</strong></div>
        </div>
    </div>

    {{-- Package Contents Table --}}
    <div class="awb-items-table-wrap">
        <table class="awb-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item Description</th>
                    <th style="text-align:center; width: 15%;">Qty</th>
                    <th style="text-align:right; width: 20%;">Price</th>
                    <th style="text-align:right; width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if(!empty($item->variant_name))
                                <div class="item-variant">Variant: {{ $item->variant_name }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">₱{{ number_format($item->unit_price_minor / 100, 2) }}</td>
                        <td style="text-align:right;">₱{{ number_format(($item->unit_price_minor * $item->quantity) / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;">Item Subtotal:</td>
                    <td style="text-align:right;">₱{{ number_format($order->subtotal_minor / 100, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;">Shipping Fee:</td>
                    <td style="text-align:right;">₱{{ number_format($order->shipping_fee_minor / 100, 2) }}</td>
                </tr>
                <tr class="awb-grand-total">
                    <td colspan="3" style="text-align:right;">Grand Total:</td>
                    <td style="text-align:right;">₱{{ number_format(($order->subtotal_minor + $order->shipping_fee_minor) / 100, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Airway Bill Footer with Rider Instructions & Signature boxes --}}
    <div class="awb-footer-grid">
        <div class="awb-instructions">
            <strong>COURIER & RIDER INSTRUCTIONS:</strong>
            <ol>
                <li>Scan parcel barcode at pickup from seller warehouse.</li>
                <li>Scan upon arrival at sorting hub and dispatch out for delivery.</li>
                <li>Verify recipient ID and scan barcode as <strong>Delivered</strong> upon handover.</li>
            </ol>
        </div>
        <div class="awb-sign-boxes">
            <div class="sign-box">
                <div class="sign-line"></div>
                <span>Sender Signature</span>
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                <span>Recipient Signature</span>
            </div>
        </div>
    </div>
</div>
