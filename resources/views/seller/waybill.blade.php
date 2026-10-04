<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airway Bill (AWB) #{{ $order['tracking_number'] ?? $order['id'] }} | cartzy</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .waybill-container {
                box-shadow: none !important;
                border: 2px solid black !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        .barcode-line {
            display: inline-block;
            background: #000;
            height: 52px;
            margin: 0 1.5px;
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans antialiased text-black">

    <!-- Top Action Bar -->
    <div class="max-w-2xl mx-auto mb-4 flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('seller.orders') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-300 shadow-xs">
                &larr; Back to Seller Orders
            </a>
            <span class="text-xs text-slate-500 font-medium">Standard 4x6" E-Commerce Waybill / Airway Bill</span>
        </div>
        <button onclick="window.print()" class="bg-[#2D2438] hover:bg-black text-white text-xs font-black px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Waybill (AWB)</span>
        </button>
    </div>

    <!-- Printable Waybill Card (Standard Philippine Logistics AWB Layout) -->
    <div class="waybill-container max-w-2xl mx-auto bg-white border-2 border-black p-6 rounded-xl shadow-xl space-y-4">
        
        <!-- Header: Courier & Routing Code -->
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-3">
                <div class="border border-black px-3 py-1 font-black text-xl tracking-wider uppercase bg-black text-white rounded">
                    cartzy EXPRESS
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Standard Delivery</div>
                    <div class="text-xs font-black">PH-NCR-TAG-04</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black font-mono tracking-wider">NCR-BGC</div>
                <div class="text-[10px] font-bold uppercase">Zone Hub 1634</div>
            </div>
        </div>

        <!-- Barcode Area -->
        <div class="text-center py-2 border-b-2 border-black">
            <div class="flex justify-center items-center h-14">
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-4"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-4"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-4"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-4"></span>
                <span class="barcode-line w-2"></span>
                <span class="barcode-line w-1"></span>
                <span class="barcode-line w-3"></span>
                <span class="barcode-line w-1"></span>
            </div>
            <div class="font-mono text-sm font-black tracking-widest mt-1">
                {{ $order['tracking_number'] ?? 'CTZ-PH-9042101' }}
            </div>
            <div class="text-[10px] text-slate-600 font-bold">Order Reference: #{{ $order['id'] }}</div>
        </div>

        <!-- 2-Column Addresses: Recipient & Sender -->
        <div class="grid grid-cols-2 gap-4 border-b-2 border-black pb-3 text-xs">
            
            <!-- Recipient (Buyer) -->
            <div class="border-r-2 border-black pr-3">
                <span class="font-black uppercase tracking-wider block text-[10px] bg-black text-white px-2 py-0.5 rounded w-fit mb-1">
                    CONSIGNEE (RECIPIENT)
                </span>
                <div class="font-extrabold text-sm">{{ $order['buyer_name'] }}</div>
                <div class="font-bold text-slate-700">{{ $order['buyer_phone'] }}</div>
                <div class="text-[11px] leading-tight text-slate-800 mt-1 font-medium">
                    {{ $order['shipping_address'] }}
                </div>
            </div>

            <!-- Sender (Seller Store) -->
            <div class="pl-2">
                <span class="font-black uppercase tracking-wider block text-[10px] bg-slate-200 text-black px-2 py-0.5 rounded w-fit mb-1">
                    SHIPPER (SELLER)
                </span>
                <div class="font-extrabold text-sm">TechZone Gadgets Store</div>
                <div class="font-bold text-slate-700">+63 917 555 8899</div>
                <div class="text-[11px] leading-tight text-slate-800 mt-1 font-medium">
                    Unit 402, High Street Plaza, 28th St., Fort Bonifacio, Taguig City, Metro Manila (1634)
                </div>
            </div>

        </div>

        <!-- Package & Payment Details -->
        <div class="grid grid-cols-3 gap-2 border-b-2 border-black pb-3 text-xs text-center">
            <div class="border-r border-slate-300">
                <div class="text-[10px] font-bold uppercase text-slate-500">Payment Method</div>
                <div class="font-black text-sm uppercase mt-0.5">
                    {{ $order['payment_status'] === 'cod_pending' ? 'CASH ON DELIVERY' : 'PREPAID' }}
                </div>
            </div>
            <div class="border-r border-slate-300">
                <div class="text-[10px] font-bold uppercase text-slate-500">Collect Amount</div>
                <div class="font-black text-sm mt-0.5 {{ $order['payment_status'] === 'cod_pending' ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ $order['payment_status'] === 'cod_pending' ? '₱' . number_format($order['total_amount'], 2) : '₱0.00 (PAID)' }}
                </div>
            </div>
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-500">Weight & Size</div>
                <div class="font-black text-sm mt-0.5">0.65 KG (Small Pouch)</div>
            </div>
        </div>

        <!-- Item Manifest Table -->
        <div class="border-b-2 border-black pb-3 text-xs">
            <div class="font-black uppercase text-[10px] tracking-wider mb-1 text-slate-600">Goods Description</div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-300 text-[10px] font-bold uppercase text-slate-500">
                        <th class="py-1">Item Description</th>
                        <th class="py-1 text-center">Variant</th>
                        <th class="py-1 text-right">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($order['items'] as $item)
                        <tr>
                            <td class="py-1.5 font-bold">{{ $item['name'] }}</td>
                            <td class="py-1.5 text-center text-slate-600">{{ $item['variant'] }}</td>
                            <td class="py-1.5 text-right font-black">{{ $item['qty'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer: Rider Proof of Delivery Signature Box -->
        <div class="grid grid-cols-2 gap-4 pt-1 text-xs">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase">Dispatch Notice</div>
                <p class="text-[10px] text-slate-600 mt-1 leading-tight">
                    By accepting this parcel, courier confirms package sealed with intact security tape. Handle electronic fragile contents with care.
                </p>
                <div class="text-[10px] font-mono text-slate-400 mt-2">Print Date: {{ now()->format('M d, Y h:i A') }}</div>
            </div>
            <div class="border border-black p-2 rounded text-center flex flex-col justify-between h-20">
                <span class="text-[9px] uppercase font-bold text-slate-500">Recipient Signature / Proof of Delivery</span>
                <div class="border-t border-dashed border-black pt-0.5 text-[9px] text-slate-500">Sign over Printed Name</div>
            </div>
        </div>

    </div>

</body>
</html>
