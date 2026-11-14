@extends('layouts.logistics')
@section('title', 'Hub Desk | Logistics Hub | cartzy')
@section('page_eyebrow', 'PARCEL OPERATIONS / HUB DESK')
@section('page_title', 'Hub Desk')
@section('page_description', 'Receive, sort by delivery area, and assign Last Mile couriers in one unified workspace.')
@section('page_subtitle', 'Receive, sort and assign parcels in one place')
@section('page_actions')
@if($processable > 0)
    <form method="POST" action="{{ route('logistics.hub.process-all') }}" class="hub-confirm" data-confirm="Receive, sort and assign every waiting parcel now?">@csrf
        <button type="submit" class="button hub-button primary large">⚡ Process all ({{ $processable }})</button>
    </form>
@endif
<a class="button subtle hub-button secondary" href="{{ route('logistics.monitoring') }}">Monitor deliveries @include('admin.partials.icon', ['name' => 'arrow'])</a>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/logistics-hub.css') }}?v={{ filemtime(public_path('css/logistics-hub.css')) }}">
<style>
@keyframes scanLaser {
    0% { top: 0%; opacity: 0.8; }
    50% { top: 98%; opacity: 1; }
    100% { top: 0%; opacity: 0.8; }
}
#camera-scanner-modal video {
    transform: none !important;
}
</style>
@endpush
@section('content')
@php
    $deliveryAreas = \App\Http\Controllers\Logistics\LogisticsPortalController::DELIVERY_AREAS;
    $steps = [
        'receive'  => ['Step 1', 'Receive', 'Parcels arriving at the hub', 'box'],
        'sort'     => ['Step 2', 'Sort', 'Address had no known area', 'file'],
        'assign'   => ['Step 3', 'Assign rider', 'Sorted, waiting for a Last Mile courier', 'users'],
        'assigned' => ['Step 4', 'Assigned', 'Waiting for rider pickup', 'truck'],
    ];
    $hints = [
        'receive'  => 'Mark parcels received as they arrive. Each one is sorted automatically when its address names a delivery area.',
        'sort'     => 'These addresses didn’t match a delivery area. Check the suggestion or choose the closest area.',
        'assign'   => 'Last Mile couriers deliver from the hub to the buyer. The best one for each parcel is already selected. Assign them all at once, or one at a time.',
        'assigned' => 'These parcels have a rider and are waiting to be picked up. Follow them in Delivery Monitoring.',
    ];
    $next = collect(['receive', 'sort', 'assign'])->first(fn ($key) => $key !== $tab && $counts[$key] > 0); // for the empty state
    $bulk = [
        'receive' => [$counts['receive'], 'logistics.parcels.receive-all', '✓ Receive all', 'Mark all :n waiting parcel(s) as received?'],
        'sort'    => [$sortSuggestable, 'logistics.sorting.sort-suggested', 'Sort all to suggested areas', 'Sort :n parcel(s) into the areas found in their addresses?'],
        'assign'  => [$riders ? $counts['assign'] : 0, 'logistics.assignments.auto-assign', 'Assign best riders to all', 'Assign the best-matching rider to all :n waiting parcel(s)?'],
    ][$tab] ?? null;
@endphp
<section class="hub-desk" aria-label="Hub desk">
    <nav class="stats-grid dashboard-stats hub-steps" aria-label="Parcel steps">
        @foreach($steps as $key => [$stepTag, $label, $caption, $icon])
            <a href="{{ route('logistics.hub', ['tab' => $key]) }}" class="stat-card hub-step {{ $counts[$key] > 0 && $key !== 'assigned' ? 'has-work' : '' }}" @if($tab === $key) aria-current="page" @endif>
                <div class="metric-heading">
                    <span class="metric-icon">@include('admin.partials.icon', ['name' => $icon])</span>
                    <span>{{ $stepTag }}</span>
                </div>
                <span>{{ $label }}</span>
                <strong aria-label="{{ $counts[$key] }} parcels">{{ $counts[$key] }}</strong>
                <small>{{ $caption }}</small>
            </a>
        @endforeach
    </nav>

    <div class="panel hub-panel">
        <div class="hub-toolbar">
            <p class="hub-hint">{{ $hints[$tab] }}</p>
            <div class="hub-toolbar-actions">
                @if($parcels->count())
                    {{-- Waybills for the parcels listed on this page, one per printed page --}}
                    <a class="hub-button secondary" target="_blank" rel="noopener"
                       href="{{ route('logistics.waybills', ['ids' => $parcels->pluck('id')->implode(',')]) }}">🖨 Print waybills <span class="hub-pill">{{ $parcels->count() }}</span></a>
                @endif
                @if($bulk && $bulk[0] > 0)
                    <form method="POST" action="{{ route($bulk[1]) }}" class="hub-confirm" data-confirm="{{ str_replace(':n', $bulk[0], $bulk[3]) }}">@csrf
                        <button type="submit" class="hub-button primary">{{ $bulk[2] }} <span class="hub-pill">{{ $bulk[0] }}</span></button>
                    </form>
                @endif
            </div>
        </div>
        @if($tab === 'assign' && !$riders)
            <p class="hub-notice">No online, active Last Mile couriers right now. Riders must time in or set their availability to online. Waiting parcels will be assigned when a rider comes online.</p>
        @endif

        {{-- Fast Parcel QR / Barcode Scanner for Hub Arrival --}}
        <div class="hub-scan-box" style="margin-bottom: 20px; padding: 16px 20px; background: #FAF9FC; border: 2px dashed #7C3AED; border-radius: 16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:#EDE9FE; display:flex; align-items:center; justify-content:center; color:#7C3AED; font-size:22px;">
                        📷
                    </div>
                    <div>
                        <strong style="display:block; font-size:15px; color:#1E1B4B;">Scan Parcel QR / Barcode</strong>
                        <span style="font-size:12.5px; color:#6B7280;">Scan waybill QR code or tracking code (e.g. CTZ-...) to receive parcel at sorting hub</span>
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" id="btn-open-camera-scanner" class="button" style="background:#5B21B6; color:#fff; border-radius:10px; font-weight:700; display:inline-flex; align-items:center; gap:6px; padding:10px 16px; cursor:pointer;">
                        <span>📷</span> Open Camera Scanner
                    </button>
                    <form method="POST" action="{{ route('logistics.parcels.scan') }}" style="display:flex; gap:8px;" id="hub-quick-scan-form">
                        @csrf
                        <input type="text" name="code" id="hub-scan-input" placeholder="Scan or enter CTZ-... / ORD-..." required autofocus
                            style="width:230px; padding:10px 14px; border:1.5px solid #C4B5FD; border-radius:10px; font-size:13.5px; font-weight:600; outline:none; background:#fff;">
                        <button type="submit" class="button primary" style="background:#7C3AED; border-color:#7C3AED; border-radius:10px; font-weight:700; white-space:nowrap; padding:10px 16px;">
                            ⚡ Receive
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Camera QR Scanner Modal --}}
        <div id="camera-scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.75); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
            <div style="background:#fff; border-radius:20px; width:100%; max-width:480px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.35);">
                <div style="padding:16px 20px; background:#1E1B4B; color:#fff; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:20px;">📷</span>
                        <strong style="font-size:16px;">Live Camera Parcel Scanner</strong>
                    </div>
                    <button type="button" id="btn-close-camera-scanner" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer; line-height:1;">✕</button>
                </div>
                
                <div style="padding:20px; text-align:center;">
                    <p style="font-size:13px; color:#4B5563; margin-bottom:14px;">Point camera at the QR code on the parcel's waybill label.</p>
                    
                    <!-- Video Scanner Viewport -->
                    <div style="position:relative; width:100%; height:300px; background:#0F172A; border-radius:14px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        <div id="qr-reader" style="width:100%; height:100%;"></div>
                        <video id="qr-camera-video" playsinline style="width:100%; height:100%; object-fit:cover; display:none;"></video>
                        <canvas id="qr-camera-canvas" style="display:none;"></canvas>
                        
                        <!-- Target reticle with animated laser -->
                        <div id="camera-reticle" style="position:absolute; width:220px; height:220px; border:2.5px solid #A78BFA; border-radius:16px; pointer-events:none; box-shadow:0 0 0 4000px rgba(0, 0, 0, 0.35);">
                            <div style="position:absolute; top:0; left:0; width:100%; height:2px; background:#7C3AED; box-shadow:0 0 10px #7C3AED; animation: scanLaser 2s infinite ease-in-out;"></div>
                        </div>
                    </div>

                    <div id="camera-scan-status" style="margin-top:14px; min-height:24px; font-size:13.5px; font-weight:700; color:#4B5563;">
                        Searching for parcel QR code...
                    </div>
                </div>

                <div style="padding:12px 20px 16px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:#6B7280;">Auto-submits on scan</span>
                    <button type="button" id="btn-stop-camera" class="button secondary" style="border-radius:10px;">Close Camera</button>
                </div>
            </div>
        </div>

        <form class="hub-search" method="GET" action="{{ route('logistics.hub') }}" role="search">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="hub-sr" for="hub-search">Find a parcel</label>
            <input type="search" id="hub-search" name="q" value="{{ $search }}" placeholder="Find by tracking number, seller, recipient or address">
            <button type="submit" class="hub-button secondary">Find</button>
            @if($search !== '')<a class="hub-link" href="{{ route('logistics.hub', ['tab' => $tab]) }}">Clear</a>@endif
        </form>

        <ul class="hub-list">
            @forelse($parcels as $parcel)
                <li class="hub-row">
                    <div class="hub-parcel">
                        <div class="hub-parcel-top">
                            <strong class="hub-code">{{ $parcel['tracking_code'] }}</strong>
                            @if($parcel['payment_method'] === 'COD')<span class="hub-tag cod">COD ₱{{ number_format($parcel['cod_amount'], 2) }}</span>@else<span class="hub-tag">Prepaid</span>@endif
                            @if($parcel['delivery_area'])<span class="hub-tag area">{{ $parcel['delivery_area'] }}</span>@endif
                            @if($tab === 'receive' && ($parcel['handover_method'] ?? 'pickup') === 'dropoff')<span class="hub-tag area">Seller drop-off</span>@endif
                        </div>
                        <p class="hub-recipient"><strong>{{ $parcel['recipient_name'] }}</strong> · {{ $parcel['recipient_address'] }}</p>
                        <small class="hub-meta">From {{ $parcel['seller_name'] }} · {{ $parcel['items'] }}</small>
                    </div>
                    <div class="hub-action">
                        @if($tab === 'receive')
                            <form method="POST" action="{{ route('logistics.parcels.receive', ['id' => $parcel['id']], false) }}" class="hub-once">@csrf
                                <button type="submit" class="hub-button primary">✓ Receive</button>
                            </form>
                            <small>{{ $parcel['suggested_area'] ? 'Will sort to '.$parcel['suggested_area'] : 'Will need an area' }}</small>
                        @elseif($tab === 'sort')
                            <form method="POST" action="{{ route('logistics.sorting.sort', ['id' => $parcel['id']], false) }}" class="hub-inline hub-once">@csrf
                                <label class="hub-sr" for="area-{{ $parcel['id'] }}">Delivery area for {{ $parcel['tracking_code'] }}</label>
                                <select id="area-{{ $parcel['id'] }}" name="area" required>
                                    <option value="">Choose an area</option>
                                    @foreach($deliveryAreas as $area)<option value="{{ $area }}" @selected($area === $parcel['suggested_area'])>{{ $area }}</option>@endforeach
                                </select>
                                <button type="submit" class="hub-button primary">Sort</button>
                            </form>
                        @elseif(in_array($tab, ['assign', 'assigned'], true))
                            @if($tab === 'assigned')
                                <span class="hub-assigned">{{ $parcel['assigned_rider_name'] ?: 'Rider' }}</span>
                                <small>Change rider before the parcel leaves the hub.</small>
                            @endif
                            <form method="POST" action="{{ route('logistics.assignments.assign', ['id' => $parcel['id']], false) }}" class="hub-inline hub-once">@csrf
                                <label class="hub-sr" for="rider-{{ $parcel['id'] }}">Rider for {{ $parcel['tracking_code'] }}</label>
                                <select id="rider-{{ $parcel['id'] }}" name="rider_id" required @disabled(!$riders)>
                                    <option value="">{{ $riders ? 'Choose an online Last Mile courier' : 'No online Last Mile couriers' }}</option>
                                    @foreach(collect($riders)->groupBy(fn ($r) => $r['home'] ?? 'No home address yet') as $home => $group)
                                        <optgroup label="🏠 {{ $home }}">
                                            @foreach($group as $rider)<option value="{{ $rider['id'] }}" @selected($rider['id'] === ($parcel['suggested_rider_id'] ?? null))>{{ $rider['id'] === ($parcel['suggested_rider_id'] ?? null) ? '★ ' : '' }}{{ $rider['name'] }} — {{ $rider['area'] }}{{ $rider['id'] === ($parcel['suggested_rider_id'] ?? null) ? ' (best)' : '' }}</option>@endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button type="submit" class="hub-button primary" @disabled(!$riders)>{{ $tab === 'assigned' ? 'Change rider' : 'Assign' }}</button>
                            </form>
                        @else
                            <span class="hub-assigned">✓ {{ $parcel['assigned_rider_name'] ?: 'Rider' }}</span>
                            <small>{{ $parcel['assigned_at'] ? 'Assigned '.$parcel['assigned_at'] : '' }}</small>
                            <a class="hub-link" href="{{ route('logistics.monitoring', ['search' => $parcel['tracking_code']]) }}">Track →</a>
                        @endif
                        <a class="hub-link" target="_blank" rel="noopener" href="{{ route('logistics.waybills', ['ids' => $parcel['id']]) }}"
                           aria-label="Print waybill for {{ $parcel['tracking_code'] }}">Print waybill</a>
                    </div>
                </li>
            @empty
                <li class="hub-empty">
                    <span aria-hidden="true">✓</span>
                    <h3>{{ $search !== '' ? 'No parcels match “'.$search.'”' : 'All clear here' }}</h3>
                    <p>{{ $search !== '' ? 'Try another tracking number or name.' : 'Nothing is waiting in this step.' }}</p>
                    @if($next && $search === '')<a class="hub-button secondary" href="{{ route('logistics.hub', ['tab' => $next]) }}">Go to {{ $steps[$next][1] }} ({{ $counts[$next] }}) →</a>@endif
                </li>
            @endforelse
        </ul>
        @if($parcels->hasPages())<div class="hub-pagination">{{ $parcels->links() }}</div>@endif
    </div>
</section>
@endsection
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// Bulk buttons ask once; every form disables its button while submitting so nothing is sent twice.
document.querySelectorAll('.hub-desk form[method="POST"]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        form.querySelector('button[type="submit"]').disabled = true;
    });
});
window.addEventListener('pageshow', () => document.querySelectorAll('.hub-desk form[data-submitting]').forEach(form => {
    delete form.dataset.submitting;
    form.querySelector('button[type="submit"]').disabled = false;
}));

// Live Camera QR Code Scanner Implementation
(function() {
    const modal = document.getElementById('camera-scanner-modal');
    const openBtn = document.getElementById('btn-open-camera-scanner');
    const closeBtn = document.getElementById('btn-close-camera-scanner');
    const stopBtn = document.getElementById('btn-stop-camera');
    const statusText = document.getElementById('camera-scan-status');
    const scanInput = document.getElementById('hub-scan-input');
    const scanForm = document.getElementById('hub-quick-scan-form');
    const reticle = document.getElementById('camera-reticle');

    let html5QrCode = null;
    let nativeStream = null;
    let detectorLoop = null;
    let isProcessing = false;

    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(920, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            osc.start();
            osc.stop(ctx.currentTime + 0.14);
        } catch (_) {}
    }

    function onCodeScanned(code) {
        if (isProcessing || !code) return;
        isProcessing = true;
        playBeep();

        if (statusText) {
            statusText.innerHTML = '<span style="color:#059669;">✓ Scanned: ' + code + '! Receiving parcel...</span>';
        }
        if (scanInput) {
            scanInput.value = code;
        }

        stopCamera();

        setTimeout(() => {
            modal.style.display = 'none';
            if (scanForm) {
                scanForm.submit();
            }
        }, 350);
    }

    async function startCamera() {
        isProcessing = false;
        modal.style.display = 'flex';
        statusText.textContent = 'Starting camera... Point at parcel QR code';
        statusText.style.color = '#4B5563';

        // Method 1: Check if Html5Qrcode is available
        if (typeof Html5Qrcode !== 'undefined') {
            try {
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("qr-reader");
                }
                const config = {
                    fps: 15,
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1.0,
                };
                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => onCodeScanned(decodedText),
                    () => {}
                );
                statusText.textContent = 'Camera active. Point at QR code on waybill';
                return;
            } catch (err) {
                console.warn('Html5Qrcode error, falling back to native mediaDevices:', err);
            }
        }

        // Method 2: Native fallback via getUserMedia and BarcodeDetector
        const video = document.getElementById('qr-camera-video');
        if (video && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            try {
                video.style.display = 'block';
                nativeStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                video.srcObject = nativeStream;
                await video.play();

                statusText.textContent = 'Camera active. Point at QR code on waybill';

                if ('BarcodeDetector' in window) {
                    const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13'] });
                    detectorLoop = setInterval(async () => {
                        if (isProcessing) return;
                        try {
                            const barcodes = await detector.detect(video);
                            if (barcodes.length > 0) {
                                onCodeScanned(barcodes[0].rawValue);
                            }
                        } catch (_) {}
                    }, 200);
                }
            } catch (err) {
                statusText.innerHTML = '<span style="color:#DC2626;">Camera access denied or unavailable. Please check permissions or enter code manually.</span>';
            }
        } else {
            statusText.innerHTML = '<span style="color:#DC2626;">Camera scanner not supported in this browser. Please enter code manually.</span>';
        }
    }

    function stopCamera() {
        if (detectorLoop) {
            clearInterval(detectorLoop);
            detectorLoop = null;
        }
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(() => {}).finally(() => {
                try { html5QrCode.clear(); } catch (_) {}
            });
        }
        if (nativeStream) {
            nativeStream.getTracks().forEach(track => track.stop());
            nativeStream = null;
        }
        const video = document.getElementById('qr-camera-video');
        if (video) {
            video.srcObject = null;
            video.style.display = 'none';
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', startCamera);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            stopCamera();
            modal.style.display = 'none';
        });
    }
    if (stopBtn) {
        stopBtn.addEventListener('click', () => {
            stopCamera();
            modal.style.display = 'none';
        });
    }
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            stopCamera();
            modal.style.display = 'none';
        }
    });
})();
</script>
@endpush
