{{-- Printable waybills for the logistics hub: one label per printed page. Opens the print dialog on load. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $labels->count() === 1 ? 'Waybill '.$labels->first()['order']->shipment->tracking_code : $labels->count().' waybills' }} | cartzy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/waybill-label.css') }}?v={{ filemtime(public_path('css/waybill-label.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/seller-waybill.css') }}?v={{ filemtime(public_path('css/seller-waybill.css')) }}">
    <style>
        body { margin: 0; background: #f6f3f8; font-family: 'Manrope', system-ui, sans-serif; color: #30283e; }
        .waybill-bar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 24px; background: #fff; border-bottom: 1px solid #e6e3eb; }
        .waybill-bar h1 { margin: 0; font-size: 16px; font-weight: 700; }
        .waybill-bar p { margin: 2px 0 0; font-size: 12px; color: #7b7089; }
        .waybill-bar-actions { display: flex; gap: 10px; }
        .waybill-bar a, .waybill-bar button { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font: 700 12px 'Manrope', system-ui, sans-serif; text-decoration: none; cursor: pointer; }
        .waybill-bar a { border: 1px solid #dcd2e2; background: #fff; color: #6f6382; }
        .waybill-bar button { border: 1px solid #59466e; background: #59466e; color: #fff; }
        .waybill-bar button:hover { background: #443354; }
        .waybill-bar a:focus-visible, .waybill-bar button:focus-visible { outline: 3px solid #a892c1; outline-offset: 3px; }
        .waybill-page.waybill-sheets { max-width: 860px !important; margin: 0 auto; padding: 24px 16px 60px; }
        .waybill-sheet + .waybill-sheet { margin-top: 24px; }
        @media print {
            @page { margin: 10mm; }
            body { background: #fff; }
            .print-hide { display: none !important; }
            .waybill-page.waybill-sheets { max-width: none !important; padding: 0; }
            .waybill-sheet + .waybill-sheet { margin-top: 0; }
            .waybill-sheet { break-after: page; page-break-after: always; }
            .waybill-sheet:last-child { break-after: auto; page-break-after: auto; }
        }
        @media (max-width: 560px) {
            .waybill-bar { flex-direction: column; align-items: stretch; }
            .waybill-bar-actions > * { flex: 1; justify-content: center; }
        }
    </style>
</head>
<body>
    <header class="waybill-bar print-hide">
        <div>
            <h1>{{ $labels->count() === 1 ? 'Waybill' : $labels->count().' waybills' }}</h1>
            <p>Each waybill prints on its own page. Stick it on top of the parcel.</p>
        </div>
        <div class="waybill-bar-actions">
            <a href="{{ $back }}">← Back</a>
            <button type="button" onclick="window.print()">Print {{ $labels->count() === 1 ? 'waybill' : 'all '.$labels->count() }}</button>
        </div>
    </header>

    <main class="waybill-page waybill-sheets">
        @foreach($labels as $label)
            <div class="waybill-sheet">
                @include('partials.waybill-label', $label + ['preview' => false])
            </div>
        @endforeach
    </main>

    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
