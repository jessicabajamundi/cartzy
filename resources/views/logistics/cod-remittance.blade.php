@extends('layouts.logistics')

@section('title', 'COD Remittance & 7-Day Escrow Desk')

@section('content')
<div class="logistics-content" style="padding: 24px 32px; max-width: 1400px; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b6477; margin-bottom: 4px;">
                <span>Logistics Hub</span>
                <span>/</span>
                <span>Financials</span>
                <span>/</span>
                <span style="color: #302b3d; font-weight: 600;">COD Remittance & Escrow</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: #1e1b26; margin: 0;">COD Remittance & 7-Day Escrow Desk</h1>
            <p style="font-size: 13px; color: #6b6477; margin: 4px 0 0;">
                Rider-collected COD cash is held for <strong>7 days</strong> before release to sellers. During this window, buyers can request refunds with seller approval.
            </p>
        </div>
        @if($stats['ready_to_remit_count'] > 0)
            <div>
                <button type="button" onclick="document.getElementById('batchRemitModal').classList.remove('hidden')" class="btn-gradient-master" style="padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; border: 0; color: white; background: #6f6382;">
                    <span>Remit All Eligible ({{ $stats['ready_to_remit_count'] }})</span>
                </button>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div style="padding: 12px 18px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div style="padding: 12px 18px; border-radius: 10px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: 13px; margin-bottom: 20px;">
            {{ session('info') }}
        </div>
    @endif

    {{-- Policy Guide Card --}}
    <div style="background: linear-gradient(135deg, #fdf4ff 0%, #f5f3ff 100%); border: 1px solid #e9d5ff; border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #7c3aed; color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                ⚖️
            </div>
            <div style="font-size: 13px; color: #4c1d95; line-height: 1.5;">
                <strong>Cartzy 7-Day Payment & Refund Flow:</strong><br>
                • <strong>Online Payment (GCash):</strong> Buyer pays seller directly.<br>
                • <strong>Cash on Delivery (COD):</strong> Rider collects cash → Remits to Logistics Hub → Logistics holds for <strong>7 days</strong>.<br>
                • <strong>Buyer Refunds:</strong> If buyer requests within 7 days and <em>Seller Approves</em>, Logistics disburses the refund from held COD funds. After 7 days, refunds are locked and funds are remitted to the seller.
            </div>
        </div>
        <a href="{{ route('logistics.returns') }}" style="font-size: 12px; font-weight: 700; color: #7c3aed; text-decoration: none; padding: 8px 14px; border: 1px solid #c4b5fd; border-radius: 8px; background: white; white-space: nowrap;">
            View Return Cases ({{ $stats['refund_pending_count'] }}) →
        </a>
    </div>

    {{-- Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: white; border: 1px solid #e5e1eb; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #6b6477; letter-spacing: 0.5px;">Total COD Collected</div>
            <div style="font-size: 22px; font-weight: 800; color: #1e1b26; margin-top: 6px;">₱{{ number_format($stats['total_cod_collected'] / 100, 2) }}</div>
            <div style="font-size: 11px; color: #6b6477; margin-top: 4px;">Delivered & cash received intact</div>
        </div>

        <div style="background: white; border: 1px solid #fed7aa; border-radius: 12px; padding: 18px 20px; background: #fffaf5;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #c2410c; letter-spacing: 0.5px;">Held in 7-Day Escrow</div>
            <div style="font-size: 22px; font-weight: 800; color: #ea580c; margin-top: 6px;">₱{{ number_format($stats['held_in_escrow'] / 100, 2) }}</div>
            <div style="font-size: 11px; color: #9a3412; margin-top: 4px;">{{ $stats['held_in_escrow_count'] }} parcel(s) within 7-day window</div>
        </div>

        <div style="background: white; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px 20px; background: #f0fdf4;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #15803d; letter-spacing: 0.5px;">Ready to Remit to Seller</div>
            <div style="font-size: 22px; font-weight: 800; color: #16a34a; margin-top: 6px;">₱{{ number_format($stats['ready_to_remit'] / 100, 2) }}</div>
            <div style="font-size: 11px; color: #166534; margin-top: 4px;">{{ $stats['ready_to_remit_count'] }} parcel(s) past 7 days</div>
        </div>

        <div style="background: white; border: 1px solid #e5e1eb; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; letter-spacing: 0.5px;">Remitted to Sellers</div>
            <div style="font-size: 22px; font-weight: 800; color: #334155; margin-top: 6px;">₱{{ number_format($stats['total_remitted'] / 100, 2) }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $stats['total_remitted_count'] }} completed payouts</div>
        </div>

        <div style="background: white; border: 1px solid #fbcfe8; border-radius: 12px; padding: 18px 20px; background: #fdf2f8;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #be185d; letter-spacing: 0.5px;">Pending COD Refunds</div>
            <div style="font-size: 22px; font-weight: 800; color: #db2777; margin-top: 6px;">{{ $stats['refund_pending_count'] }}</div>
            <div style="font-size: 11px; color: #9d174d; margin-top: 4px;">Seller approved · Hub to disburse</div>
        </div>
    </div>

    {{-- Pending Refunds Queue (If any seller approved refund cases need logistics cash disbursal) --}}
    @if($pendingRefundCases->isNotEmpty())
        <div style="background: white; border: 1px solid #f472b6; border-radius: 14px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(244,114,182,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #9d174d; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>💸</span> COD Refund Disbursal Queue (Seller Approved)
                    </h2>
                    <p style="font-size: 12px; color: #be185d; margin: 4px 0 0;">These returns were approved by the seller. Logistics holds the COD payment and can now send the refund to the buyer.</p>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #fce7f3; color: #9d174d; font-size: 11px; text-transform: uppercase;">
                            <th style="padding: 10px;">Case #</th>
                            <th style="padding: 10px;">Order / Tracking</th>
                            <th style="padding: 10px;">Seller Store</th>
                            <th style="padding: 10px;">Buyer / Contact</th>
                            <th style="padding: 10px;">Approved Refund</th>
                            <th style="padding: 10px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRefundCases as $case)
                            <tr style="border-bottom: 1px solid #fdf2f8;">
                                <td style="padding: 12px 10px; font-weight: 700;">#{{ $case->id }}</td>
                                <td style="padding: 12px 10px;">
                                    <strong>{{ $case->dispute->order->reference }}</strong><br>
                                    <small style="color: #6b6477;">{{ $case->dispute->sellerOrder->shipment?->tracking_code }}</small>
                                </td>
                                <td style="padding: 12px 10px;">{{ $case->dispute->sellerOrder->seller?->name }}</td>
                                <td style="padding: 12px 10px;">
                                    {{ $case->dispute->order->buyer?->name }}<br>
                                    <small style="color: #6b6477;">{{ $case->dispute->contact_phone }}</small>
                                </td>
                                <td style="padding: 12px 10px; font-weight: 700; color: #db2777;">
                                    ₱{{ number_format($case->amount_minor / 100, 2) }}
                                </td>
                                <td style="padding: 12px 10px; text-align: right;">
                                    <a href="{{ route('logistics.returns', ['case' => $case->id]) }}" style="display: inline-block; padding: 7px 14px; background: #db2777; color: white; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none;">
                                        Disburse Refund →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Main COD Parcels Table --}}
    <div style="background: white; border: 1px solid #e5e1eb; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- Search and filters --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('logistics.cod_remittances') }}" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ $tab === 'all' ? 'background: #6f6382; color: white;' : 'background: #f3f4f6; color: #4b5563;' }}">
                    All Delivered COD
                </a>
                <a href="{{ route('logistics.cod_remittances', ['tab' => 'ready']) }}" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ $tab === 'ready' ? 'background: #16a34a; color: white;' : 'background: #f0fdf4; color: #15803d;' }}">
                    Ready to Remit ({{ $stats['ready_to_remit_count'] }})
                </a>
                <a href="{{ route('logistics.cod_remittances', ['tab' => 'holding']) }}" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ $tab === 'holding' ? 'background: #ea580c; color: white;' : 'background: #fffaf5; color: #c2410c;' }}">
                    Held in Escrow ({{ $stats['held_in_escrow_count'] }})
                </a>
                <a href="{{ route('logistics.cod_remittances', ['tab' => 'remitted']) }}" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ $tab === 'remitted' ? 'background: #334155; color: white;' : 'background: #f8fafc; color: #475569;' }}">
                    Remitted ({{ $stats['total_remitted_count'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('logistics.cod_remittances') }}" style="display: flex; gap: 8px;">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search tracking, order, seller..." style="padding: 7px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; width: 220px;">
                <button type="submit" style="padding: 7px 12px; background: #6f6382; color: white; border: 0; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #f3f4f6; color: #6b6477; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 10px;">Parcel / Order</th>
                        <th style="padding: 12px 10px;">Delivered Date</th>
                        <th style="padding: 12px 10px;">Seller Store</th>
                        <th style="padding: 12px 10px;">Seller GCash / Payout</th>
                        <th style="padding: 12px 10px;">COD Amount</th>
                        <th style="padding: 12px 10px;">Escrow / Payout Status</th>
                        <th style="padding: 12px 10px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filteredShipments = $shipments->filter(function ($s) use ($tab) {
                            if ($tab === 'ready') return $s->isCodEligibleForRemittance();
                            if ($tab === 'holding') return $s->isCodHolding();
                            if ($tab === 'remitted') return $s->isCodRemitted();
                            return true;
                        });
                    @endphp

                    @forelse($filteredShipments as $shipment)
                        @php
                            $deliveredAt = $shipment->sellerOrder?->delivered_at;
                            $holdingDaysLeft = $shipment->codHoldingDaysRemaining();
                            $seller = $shipment->sellerOrder?->seller;
                            $order = $shipment->sellerOrder?->order;
                        @endphp
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 14px 10px;">
                                <strong style="color: #1e1b26;">{{ $shipment->tracking_code }}</strong><br>
                                <small style="color: #6b6477;">Order: {{ $order?->reference }}</small>
                            </td>
                            <td style="padding: 14px 10px;">
                                <div>{{ $deliveredAt?->timezone('Asia/Manila')->format('M j, Y') ?? 'N/A' }}</div>
                                <small style="color: #9ca3af;">{{ $deliveredAt ? $deliveredAt->diffForHumans() : '' }}</small>
                            </td>
                            <td style="padding: 14px 10px;">
                                <strong>{{ $seller?->name ?? 'Unknown Store' }}</strong><br>
                                <small style="color: #6b6477;">{{ $seller?->user?->name }}</small>
                            </td>
                            <td style="padding: 14px 10px;">
                                @if(!empty($seller?->gcash_number))
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 600;">
                                        GCash: {{ $seller->gcash_number }}
                                    </span>
                                    @if(!empty($seller?->gcash_name))
                                        <br><small style="color: #6b6477;">{{ $seller->gcash_name }}</small>
                                    @endif
                                @else
                                    <span style="color: #9ca3af; font-size: 11px;">Default Hub Cashier</span>
                                @endif
                            </td>
                            <td style="padding: 14px 10px; font-weight: 700; color: #1e1b26;">
                                ₱{{ number_format($shipment->cod_amount, 2) }}
                            </td>
                            <td style="padding: 14px 10px;">
                                @if($shipment->isCodRemitted())
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700;">
                                        ✓ Remitted ({{ strtoupper($shipment->cod_remittance_method ?? 'GCash') }})
                                    </span>
                                    <br><small style="color: #6b6477;">Ref: {{ $shipment->cod_remittance_ref }} · {{ $shipment->cod_remitted_at?->timezone('Asia/Manila')->format('M j, Y') }}</small>
                                @elseif($shipment->isCodHolding())
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; background: #fffaf5; color: #c2410c; border: 1px solid #fed7aa; font-size: 11px; font-weight: 700;">
                                        ⏳ Escrow: {{ $holdingDaysLeft }}d remaining
                                    </span>
                                    <br><small style="color: #9a3412;">Refund window open until {{ $shipment->codHoldingUntil()?->timezone('Asia/Manila')->format('M j, g:i A') }}</small>
                                @elseif($shipment->isCodEligibleForRemittance())
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 700;">
                                        ✓ 7 Days Passed · Ready
                                    </span>
                                    <br><small style="color: #166534;">Buyer refund period expired</small>
                                @else
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; background: #fef2f2; color: #b91c1c; font-size: 11px; font-weight: 700;">
                                        Dispute / Pending Review
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 10px; text-align: right;">
                                @if($shipment->isCodEligibleForRemittance())
                                    <button type="button" onclick="openRemitModal('{{ $shipment->id }}', '{{ $shipment->tracking_code }}', '{{ $seller?->name }}', '{{ number_format($shipment->cod_amount, 2) }}', '{{ $seller?->gcash_number }}')" style="padding: 6px 14px; background: #16a34a; color: white; border: 0; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                        Remit ₱{{ number_format($shipment->cod_amount, 2) }}
                                    </button>
                                @elseif($shipment->isCodHolding())
                                    <span style="font-size: 11px; color: #9ca3af; font-style: italic;">
                                        Locked ({{ $holdingDaysLeft }}d)
                                    </span>
                                @elseif($shipment->isCodRemitted())
                                    <span style="font-size: 12px; color: #059669; font-weight: 600;">
                                        Settled
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: #6b6477;">
                                No COD shipments found for this category.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $shipments->links() }}
        </div>
    </div>
</div>

{{-- Single Remittance Modal --}}
<div id="singleRemitModal" style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 16px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #1e1b26; margin: 0;">Remit COD Cash to Seller</h3>
            <button type="button" onclick="document.getElementById('singleRemitModal').style.display = 'none'" style="border: 0; background: transparent; font-size: 20px; cursor: pointer; color: #6b6477;">✕</button>
        </div>
        <p style="font-size: 13px; color: #6b6477; margin-bottom: 16px;">
            The 7-day refund escrow protection has passed without disputes. Confirm the remittance of collected COD funds to the seller.
        </p>
        <form id="singleRemitForm" method="POST" action="">
            @csrf
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; margin-bottom: 16px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: #6b6477;">Parcel:</span>
                    <strong id="modalTracking"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: #6b6477;">Seller Store:</span>
                    <strong id="modalSeller"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: #6b6477;">Remittance Amount:</span>
                    <strong id="modalAmount" style="color: #16a34a; font-size: 15px;"></strong>
                </div>
                <div id="modalGcashRow" style="display: flex; justify-content: space-between;">
                    <span style="color: #6b6477;">Registered GCash:</span>
                    <strong id="modalGcash" style="color: #1d4ed8;"></strong>
                </div>
            </div>

            <div style="display: grid; gap: 14px; margin-bottom: 20px; font-size: 13px;">
                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Payout Method
                    <select name="method" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="gcash">GCash Transfer</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cashier Counter / Cash</option>
                    </select>
                </label>

                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Transaction Reference / Receipt #
                    <input type="text" name="reference" placeholder="e.g. GCash Ref 100293847581 or Receipt #4810" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                </label>

                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Notes / Remarks (Optional)
                    <textarea name="notes" rows="2" placeholder="e.g. Remitted to registered seller GCash number" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('singleRemitModal').style.display = 'none'" style="padding: 9px 16px; border: 1px solid #d1d5db; background: white; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; color: #374151;">
                    Cancel
                </button>
                <button type="submit" style="padding: 9px 18px; border: 0; background: #16a34a; color: white; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Confirm & Remit to Seller
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Batch Remittance Modal --}}
<div id="batchRemitModal" class="hidden" style="position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 16px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #1e1b26; margin: 0;">Batch Remit Eligible COD Parcels</h3>
            <button type="button" onclick="document.getElementById('batchRemitModal').classList.add('hidden')" style="border: 0; background: transparent; font-size: 20px; cursor: pointer; color: #6b6477;">✕</button>
        </div>
        <p style="font-size: 13px; color: #6b6477; margin-bottom: 16px;">
            This will mark all <strong>{{ $stats['ready_to_remit_count'] }} eligible COD parcel(s)</strong> (totaling <strong>₱{{ number_format($stats['ready_to_remit'] / 100, 2) }}</strong>) as remitted to their respective sellers.
        </p>
        <form method="POST" action="{{ route('logistics.cod_remittances.batch') }}">
            @csrf
            <div style="display: grid; gap: 14px; margin-bottom: 20px; font-size: 13px;">
                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Batch Payout Method
                    <select name="method" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="gcash">GCash Bulk Disbursement</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cashier Hub Batch</option>
                    </select>
                </label>

                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Batch Reference / Batch #
                    <input type="text" name="reference" placeholder="e.g. BATCH-REM-{{ date('Ymd') }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                </label>

                <label style="display: grid; gap: 4px; font-weight: 600; color: #374151;">
                    Notes / Remarks
                    <textarea name="notes" rows="2" placeholder="e.g. Weekly scheduled COD payout batch" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;"></textarea>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('batchRemitModal').classList.add('hidden')" style="padding: 9px 16px; border: 1px solid #d1d5db; background: white; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; color: #374151;">
                    Cancel
                </button>
                <button type="submit" style="padding: 9px 18px; border: 0; background: #6f6382; color: white; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Confirm Batch Remittance
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRemitModal(id, tracking, seller, amount, gcash) {
    var form = document.getElementById('singleRemitForm');
    form.action = '/logistics/cod-remittances/' + id + '/remit';
    document.getElementById('modalTracking').innerText = tracking;
    document.getElementById('modalSeller').innerText = seller;
    document.getElementById('modalAmount').innerText = '₱' + amount;
    if (gcash) {
        document.getElementById('modalGcash').innerText = gcash;
        document.getElementById('modalGcashRow').style.display = 'flex';
    } else {
        document.getElementById('modalGcashRow').style.display = 'none';
    }
    document.getElementById('singleRemitModal').style.display = 'flex';
}
</script>
@endsection
