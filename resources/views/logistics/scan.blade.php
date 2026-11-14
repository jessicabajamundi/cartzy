@extends('layouts.logistics')

@section('title', 'Scan Parcel Waybill | Logistics Hub | cartzy')
@section('page_eyebrow', 'PARCEL OPERATIONS / LIVE WAYBILL TRACKER')
@section('page_title', 'Scan Parcel')
@section('page_description', "Use your device's camera to scan parcel waybills for real-time tracking of current status, rider dispatch, and exact whereabouts.")

@push('styles')
<style>
    .scan-layout {
        display: grid;
        grid-template-columns: 460px 1fr;
        gap: 24px;
        align-items: start;
        margin-top: 16px;
    }
    @media (max-width: 1024px) {
        .scan-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Scanner Card */
    .scanner-card {
        background: #ffffff;
        border: 1px solid #e5dfeb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(86, 75, 104, 0.05);
    }
    .scanner-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .scanner-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e1b26;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .camera-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .camera-status-pill.active {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    .camera-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #9ca3af;
    }
    .camera-status-pill.active .camera-status-dot {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.75; }
    }

    /* Viewfinder View */
    .viewfinder-wrapper {
        position: relative;
        background: #0f0d14;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.4);
    }
    #camera-reader {
        width: 100% !important;
        height: 100% !important;
    }
    #camera-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 16px;
    }
    .viewfinder-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .viewfinder-target {
        width: 72%;
        height: 72%;
        border: 2px dashed rgba(255, 255, 255, 0.4);
        border-radius: 14px;
        position: relative;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
    }
    .target-corner {
        position: absolute;
        width: 18px;
        height: 18px;
        border-color: #a78bfa;
        border-style: solid;
    }
    .corner-tl { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-top-left-radius: 8px; }
    .corner-tr { top: -2px; right: -2px; border-width: 3px 3px 0 0; border-top-right-radius: 8px; }
    .corner-bl { bottom: -2px; left: -2px; border-width: 0 0 3px 3px; border-bottom-left-radius: 8px; }
    .corner-br { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-bottom-right-radius: 8px; }

    .laser-scan-line {
        position: absolute;
        width: 100%;
        height: 2.5px;
        background: linear-gradient(90deg, transparent, #c084fc, #a855f7, #c084fc, transparent);
        box-shadow: 0 0 10px #c084fc, 0 0 4px #fff;
        top: 0;
        animation: laserScan 2.2s ease-in-out infinite alternate;
        display: none;
    }
    .viewfinder-wrapper.is-scanning .laser-scan-line {
        display: block;
    }
    @keyframes laserScan {
        0% { top: 6%; opacity: 0.9; }
        100% { top: 92%; opacity: 0.9; }
    }

    .viewfinder-idle-banner {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #14121a;
        color: #e5e1ea;
        padding: 24px;
        text-align: center;
        gap: 12px;
        z-index: 2;
    }
    .viewfinder-idle-banner.hidden {
        display: none !important;
    }

    /* Controls Bar */
    .scanner-controls {
        display: flex;
        gap: 8px;
        margin-top: 14px;
    }
    .scanner-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .scanner-btn.primary {
        background: #564b68;
        color: #ffffff;
    }
    .scanner-btn.primary:hover {
        background: #463c55;
    }
    .scanner-btn.secondary {
        background: #f8f6fa;
        color: #564b68;
        border-color: #e1dde7;
    }
    .scanner-btn.secondary:hover {
        background: #eeeaf3;
    }

    /* Manual Entry Form */
    .manual-entry-box {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #e8e3ed;
    }
    .manual-entry-box label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: #554b62;
        margin-bottom: 6px;
    }
    .manual-input-row {
        display: flex;
        gap: 8px;
    }
    .manual-input-row input {
        flex: 1;
        padding: 9px 14px;
        font-size: 12.5px;
        font-family: monospace;
        text-transform: uppercase;
        border: 1px solid #dcd7e2;
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s;
    }
    .manual-input-row input:focus {
        border-color: #564b68;
    }

    /* Recent Scans Chips */
    .recent-scans {
        margin-top: 16px;
    }
    .recent-scans-label {
        font-size: 11px;
        font-weight: 700;
        color: #8a7e96;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }
    .recent-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .recent-chip {
        padding: 4px 10px;
        background: #faf8fb;
        border: 1px solid #ece7ef;
        border-radius: 8px;
        font-size: 11px;
        font-family: monospace;
        font-weight: 700;
        color: #564b68;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
    }
    .recent-chip:hover {
        background: #f1ecf5;
        border-color: #d1cad7;
    }

    /* Result Monitor Display */
    .monitor-card {
        background: #ffffff;
        border: 1px solid #e5dfeb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(86, 75, 104, 0.05);
        min-height: 480px;
    }
    .empty-state-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 420px;
        text-align: center;
        padding: 32px 16px;
        color: #8c7f99;
    }
    .empty-state-icon {
        width: 72px;
        height: 72px;
        border-radius: 24px;
        background: #f7f4f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: #6f6382;
    }

    /* Scanned Parcel Content */
    .parcel-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 16px;
        border-bottom: 1px solid #eeeaf3;
    }
    .tracking-badge {
        font-family: monospace;
        font-size: 16px;
        font-weight: 800;
        color: #1e1b26;
        letter-spacing: 0.5px;
    }
    .order-ref {
        font-size: 12px;
        color: #7b7089;
        font-weight: 600;
    }

    /* Whereabouts Hero Banner */
    .whereabouts-banner {
        margin: 16px 0;
        padding: 16px 18px;
        border-radius: 16px;
        background: linear-gradient(135deg, #f8f6fc 0%, #fefcfd 100%);
        border: 1px solid #e4dcee;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .whereabouts-pin {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #564b68;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(86, 75, 104, 0.2);
    }
    .whereabouts-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6f6382;
        margin-bottom: 2px;
    }
    .whereabouts-text {
        font-size: 13.5px;
        font-weight: 700;
        color: #1e1b26;
        line-height: 1.45;
    }

    /* 4-Step Route Progress */
    .route-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 20px 0;
        position: relative;
    }
    .route-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 6px;
        flex: 1;
        position: relative;
        z-index: 2;
    }
    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #dcd7e2;
        color: #8c7f99;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .route-step.active .step-circle {
        border-color: #564b68;
        background: #564b68;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(86, 75, 104, 0.15);
    }
    .route-step.completed .step-circle {
        border-color: #10b981;
        background: #10b981;
        color: #ffffff;
    }
    .step-label {
        font-size: 10.5px;
        font-weight: 700;
        color: #6d627b;
    }
    .step-sub {
        font-size: 9.5px;
        color: #9c91a8;
    }
    .route-stepper::before {
        content: '';
        position: absolute;
        top: 16px;
        left: 12%;
        right: 12%;
        height: 2px;
        background: #e6e0ec;
        z-index: 1;
    }

    /* Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
        margin: 18px 0;
    }
    .detail-tile {
        padding: 14px;
        background: #faf9fb;
        border: 1px solid #eeeaf3;
        border-radius: 14px;
    }
    .detail-tile-header {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #7b7089;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .detail-tile-body {
        font-size: 12.5px;
        color: #272330;
    }
    .detail-tile-body strong {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #1e1b26;
        margin-bottom: 2px;
    }

    /* Timeline Section */
    .timeline-wrap {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #eeeaf3;
    }
    .timeline-title {
        font-size: 13px;
        font-weight: 800;
        color: #1e1b26;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .timeline-list {
        position: relative;
        padding-left: 20px;
    }
    .timeline-list::before {
        content: '';
        position: absolute;
        top: 6px;
        bottom: 6px;
        left: 6px;
        width: 2px;
        background: #e6e0ec;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 14px;
    }
    .timeline-dot {
        position: absolute;
        left: -20px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #564b68;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #564b68;
    }
    .timeline-content {
        font-size: 12px;
        color: #3b3544;
    }
    .timeline-meta {
        font-size: 10.5px;
        color: #8c7f99;
        margin-top: 2px;
    }

    /* Actions Footer */
    .parcel-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #eeeaf3;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn.primary {
        background: #564b68;
        color: #ffffff;
        border: none;
    }
    .action-btn.primary:hover {
        background: #463c55;
    }
    .action-btn.secondary {
        background: #f8f6fa;
        color: #564b68;
        border: 1px solid #dcd7e2;
    }
    .action-btn.secondary:hover {
        background: #eeeaf3;
    }
    .action-btn.success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .action-btn.success:hover {
        background: #d1fae5;
    }

    /* Status Badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .status-pill.out_for_delivery, .status-pill.in_transit {
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .status-pill.delivered {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .status-pill.at_hub, .status-pill.received {
        background: #f3eff7;
        color: #564b68;
        border: 1px solid #e5dcef;
    }
    .status-pill.failed {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .status-pill.returned {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>
@endpush

@section('content')
<div class="scan-layout">

    <!-- Left Column: Camera Scanner & Input -->
    <div class="scanner-card">
        <div class="scanner-header">
            <h2 class="scanner-title">
                @include('admin.partials.icon', ['name' => 'camera'])
                <span>Optical Waybill Scanner</span>
            </h2>
            <div id="camera-status" class="camera-status-pill">
                <span class="camera-status-dot"></span>
                <span id="camera-status-label">Camera Standby</span>
            </div>
        </div>

        <!-- Camera Viewfinder -->
        <div class="viewfinder-wrapper" id="viewfinder-box">
            <div id="camera-reader"></div>

            <div class="viewfinder-overlay">
                <div class="viewfinder-target">
                    <span class="target-corner corner-tl"></span>
                    <span class="target-corner corner-tr"></span>
                    <span class="target-corner corner-bl"></span>
                    <span class="target-corner corner-br"></span>
                    <div class="laser-scan-line"></div>
                </div>
            </div>

            <!-- Standby Overlay -->
            <div id="viewfinder-standby" class="viewfinder-idle-banner">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    📷
                </div>
                <div>
                    <h3 style="margin: 0 0 4px; font-size: 14px; font-weight: 700;">Camera Scanner Ready</h3>
                    <p style="margin: 0; font-size: 12px; color: #a49baa; max-width: 260px;">Tap Start Camera below to activate your device's lens for real-time waybill scanning.</p>
                </div>
                <button type="button" class="scanner-btn primary" id="btn-start-camera" style="margin-top: 4px;">
                    Start Camera Scanner
                </button>
            </div>
        </div>

        <!-- Scanner Controls -->
        <div class="scanner-controls">
            <button type="button" class="scanner-btn primary" id="btn-toggle-camera">
                <span id="btn-camera-icon">▶</span>
                <span id="btn-camera-text">Start Camera</span>
            </button>
            <button type="button" class="scanner-btn secondary" id="btn-switch-camera" title="Switch between front and back camera">
                ↻ Switch Lens
            </button>
            <label class="scanner-btn secondary" style="cursor: pointer;" title="Upload or choose a photo of the waybill">
                📁 Upload Photo
                <input type="file" id="waybill-file-input" accept="image/*" capture="environment" hidden>
            </label>
        </div>

        <!-- Manual Tracking Search Form -->
        <div class="manual-entry-box">
            <label for="manual-code-input">Or Type Tracking Number / Order Reference</label>
            <form id="manual-lookup-form" class="manual-input-row" onsubmit="event.preventDefault(); triggerLookup();">
                <input type="text" id="manual-code-input" placeholder="e.g. CTZ-SHP-202611... or reference" value="{{ $code }}">
                <button type="submit" class="scanner-btn primary" style="flex: 0 0 auto; padding: 9px 16px;">
                    Lookup
                </button>
            </form>
        </div>

        <!-- Recent Waybills Tray -->
        @if(!empty($recentParcels) && count($recentParcels) > 0)
            <div class="recent-scans">
                <span class="recent-scans-label">Recent Hub Shipments (Quick Tap)</span>
                <div class="recent-chips">
                    @foreach($recentParcels as $rp)
                        <button type="button" class="recent-chip" onclick="searchCode('{{ $rp['tracking_code'] }}')">
                            {{ $rp['tracking_code'] }} ({{ Str::limit($rp['recipient'], 10) }})
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Live Status & Whereabouts Card -->
    <div class="monitor-card" id="monitor-card">

        <!-- Loading State -->
        <div id="lookup-loading" style="display: none; height: 100%; min-height: 420px; align-items: center; justify-content: center; flex-direction: column; gap: 14px; text-align: center;">
            <div style="width: 40px; height: 40px; border: 3px solid #f3eff7; border-top-color: #564b68; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <p style="font-size: 13px; font-weight: 700; color: #564b68; margin: 0;">Scanning & locating parcel whereabouts...</p>
        </div>

        <!-- Empty State -->
        <div id="monitor-empty-state" class="empty-state-wrap" style="{{ $initialParcel ? 'display: none;' : '' }}">
            <div class="empty-state-icon">
                @include('admin.partials.icon', ['name' => 'scan'])
            </div>
            <h3 style="font-size: 16px; font-weight: 800; color: #1e1b26; margin: 0 0 6px;">No Parcel Scanned Yet</h3>
            <p style="font-size: 13px; color: #7b7089; max-width: 320px; line-height: 1.5; margin: 0;">
                Point your camera at a waybill's barcode or QR code, upload a photo, or enter the tracking number on the left to inspect its live status and whereabouts.
            </p>
        </div>

        <!-- Populated State -->
        <div id="monitor-result-content" style="{{ $initialParcel ? '' : 'display: none;' }}">
            @if($initialParcel)
                @include('logistics.partials.scanned-parcel-details', ['parcel' => $initialParcel])
            @endif
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    let html5QrCode = null;
    let isCameraRunning = false;
    let currentCameraFacing = 'environment';
    let isLookingUp = false;

    const standbyOverlay = document.getElementById('viewfinder-standby');
    const viewfinderBox = document.getElementById('viewfinder-box');
    const cameraStatusPill = document.getElementById('camera-status');
    const cameraStatusLabel = document.getElementById('camera-status-label');
    const toggleBtn = document.getElementById('btn-toggle-camera');
    const toggleBtnText = document.getElementById('btn-camera-text');
    const toggleBtnIcon = document.getElementById('btn-camera-icon');
    const switchBtn = document.getElementById('btn-switch-camera');
    const fileInput = document.getElementById('waybill-file-input');
    const manualInput = document.getElementById('manual-code-input');
    const resultContent = document.getElementById('monitor-result-content');
    const emptyState = document.getElementById('monitor-empty-state');
    const loadingState = document.getElementById('lookup-loading');

    // Pleasant audio beep upon successful scan
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (_) {}
    }

    async function startCamera() {
        if (isCameraRunning) return;
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("camera-reader");
            }
            standbyOverlay.classList.add('hidden');
            viewfinderBox.classList.add('is-scanning');
            cameraStatusPill.classList.add('active');
            cameraStatusLabel.textContent = 'Camera Live';
            toggleBtnText.textContent = 'Pause Camera';
            toggleBtnIcon.textContent = '⏸';

            const config = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.333334
            };

            await html5QrCode.start(
                { facingMode: currentCameraFacing },
                config,
                (decodedText) => {
                    handleScanSuccess(decodedText);
                },
                (_) => {}
            );
            isCameraRunning = true;
        } catch (err) {
            console.error('Camera start error:', err);
            stopCamera();
            alert('Could not access device camera. Please verify camera permissions in your browser.');
        }
    }

    async function stopCamera() {
        if (html5QrCode && isCameraRunning) {
            try {
                await html5QrCode.stop();
            } catch (_) {}
        }
        isCameraRunning = false;
        standbyOverlay.classList.remove('hidden');
        viewfinderBox.classList.remove('is-scanning');
        cameraStatusPill.classList.remove('active');
        cameraStatusLabel.textContent = 'Camera Standby';
        toggleBtnText.textContent = 'Start Camera';
        toggleBtnIcon.textContent = '▶';
    }

    async function toggleCamera() {
        if (isCameraRunning) {
            await stopCamera();
        } else {
            await startCamera();
        }
    }

    async function switchCamera() {
        currentCameraFacing = (currentCameraFacing === 'environment') ? 'user' : 'environment';
        if (isCameraRunning) {
            await stopCamera();
            await startCamera();
        }
    }

    function handleScanSuccess(code) {
        if (isLookingUp || !code) return;
        playBeep();
        if (manualInput) manualInput.value = code;
        lookupCode(code);
    }

    // Lookup parcel via AJAX
    async function lookupCode(code) {
        if (!code || isLookingUp) return;
        isLookingUp = true;

        emptyState.style.display = 'none';
        resultContent.style.display = 'none';
        loadingState.style.display = 'flex';

        try {
            const url = '{{ route("logistics.scan.lookup") }}?code=' + encodeURIComponent(code);
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();

            loadingState.style.display = 'none';

            if (!data.success || !data.parcel) {
                alert(data.message || 'Parcel not found.');
                emptyState.style.display = 'flex';
                return;
            }

            renderParcelDetails(data.parcel);
            resultContent.style.display = 'block';

            // Update URL without page reload
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('code', code);
            window.history.replaceState({}, '', newUrl);
        } catch (err) {
            console.error('Lookup error:', err);
            loadingState.style.display = 'none';
            emptyState.style.display = 'flex';
            alert('Failed to connect to logistics hub. Please try again.');
        } finally {
            isLookingUp = false;
        }
    }

    window.triggerLookup = function() {
        const val = manualInput.value.trim();
        if (!val) {
            alert('Please enter a tracking number.');
            return;
        }
        lookupCode(val);
    };

    window.searchCode = function(code) {
        if (manualInput) manualInput.value = code;
        lookupCode(code);
    };

    // Render Scanned Details HTML
    function renderParcelDetails(p) {
        let riderHtml = '<p style="color:#8c7f99;font-size:12px;margin:0;">No rider assigned yet</p>';
        if (p.assigned_rider) {
            riderHtml = `
                <strong>${p.assigned_rider.name} (★ ${p.assigned_rider.rating})</strong>
                <span style="display:block;color:#6f6382;font-size:11.5px;">${p.assigned_rider.vehicle} · ${p.assigned_rider.plate_no}</span>
                <span style="display:block;color:#8c7f99;font-size:11.5px;">📞 ${p.assigned_rider.phone}</span>
            `;
        }

        // Stepper calculations
        const s = p.delivery_status;
        const step1 = true;
        const step2 = ['at_hub', 'assigned', 'in_transit', 'out_for_delivery', 'delivered'].includes(s);
        const step3 = ['assigned', 'in_transit', 'out_for_delivery', 'delivered'].includes(s);
        const step4 = s === 'delivered';

        let eventsHtml = '';
        if (p.events && p.events.length) {
            p.events.forEach(ev => {
                eventsHtml += `
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <strong>${ev.status_label}</strong> - ${ev.note || ''}
                            <div class="timeline-meta">${ev.occurred_at} · by ${ev.user || 'Hub Staff'}</div>
                        </div>
                    </div>
                `;
            });
        }

        resultContent.innerHTML = `
            <div class="parcel-header">
                <div>
                    <div class="tracking-badge">${p.tracking_code}</div>
                    <div class="order-ref">Order Ref: <strong>${p.order_reference}</strong> · Area: <strong style="color:#564b68;">${p.delivery_area}</strong></div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="status-pill ${p.delivery_status}">${p.status_label}</span>
                </div>
            </div>

            <!-- Whereabouts Live Banner -->
            <div class="whereabouts-banner">
                <div class="whereabouts-pin">📍</div>
                <div>
                    <div class="whereabouts-title">Current Whereabouts & Physical Stage</div>
                    <div class="whereabouts-text">${p.whereabouts}</div>
                </div>
            </div>

            <!-- Route Stepper -->
            <div class="route-stepper">
                <div class="route-step ${step1 ? 'completed' : ''}">
                    <div class="step-circle">${step1 ? '✓' : '1'}</div>
                    <span class="step-label">Picked Up</span>
                    <span class="step-sub">From Seller</span>
                </div>
                <div class="route-step ${step2 ? (step3 ? 'completed' : 'active') : ''}">
                    <div class="step-circle">${step2 && step3 ? '✓' : '2'}</div>
                    <span class="step-label">Sorting Hub</span>
                    <span class="step-sub">Metro South</span>
                </div>
                <div class="route-step ${step3 ? (step4 ? 'completed' : 'active') : ''}">
                    <div class="step-circle">${step3 && step4 ? '✓' : '3'}</div>
                    <span class="step-label">Rider Out</span>
                    <span class="step-sub">En Route</span>
                </div>
                <div class="route-step ${step4 ? 'completed' : ''}">
                    <div class="step-circle">${step4 ? '✓' : '4'}</div>
                    <span class="step-label">Delivered</span>
                    <span class="step-sub">Completed</span>
                </div>
            </div>

            <!-- Key Details Grid -->
            <div class="details-grid">
                <div class="detail-tile">
                    <div class="detail-tile-header">📦 Package Information</div>
                    <div class="detail-tile-body">
                        <strong>${p.items}</strong>
                        <span>Weight: ${p.weight_kg} kg · Payment: <strong style="display:inline;color:${p.payment_method === 'COD' ? '#b45309' : '#059669'};">${p.payment_method} ${p.payment_method === 'COD' ? '₱' + p.cod_amount.toFixed(2) : ''}</strong></span>
                    </div>
                </div>

                <div class="detail-tile">
                    <div class="detail-tile-header">🛵 Delivery Rider</div>
                    <div class="detail-tile-body">
                        ${riderHtml}
                    </div>
                </div>

                <div class="detail-tile">
                    <div class="detail-tile-header">🏠 Recipient (Destination)</div>
                    <div class="detail-tile-body">
                        <strong>${p.recipient_name}</strong>
                        <span>${p.recipient_address}</span>
                        ${p.recipient_phone ? `<span style="display:block;margin-top:2px;"><a href="tel:${p.recipient_phone}" style="color:#564b68;font-weight:700;text-decoration:none;">📞 ${p.recipient_phone}</a></span>` : ''}
                    </div>
                </div>

                <div class="detail-tile">
                    <div class="detail-tile-header">🏪 Store Sender (Origin)</div>
                    <div class="detail-tile-body">
                        <strong>${p.seller_name}</strong>
                        <span>${p.seller_address}</span>
                        ${p.seller_phone ? `<span style="display:block;margin-top:2px;color:#8c7f99;">📞 ${p.seller_phone}</span>` : ''}
                    </div>
                </div>
            </div>

            <!-- Timeline Audit Trail -->
            <div class="timeline-wrap">
                <div class="timeline-title">
                    ⏱ Parcel Journey & Audit Events
                </div>
                <div class="timeline-list">
                    ${eventsHtml}
                </div>
            </div>

            <!-- Hub Actions -->
            <div class="parcel-actions">
                <a href="${p.monitoring_url}" class="action-btn primary">
                    Open in Delivery Monitoring →
                </a>
                <a href="${p.print_url}" target="_blank" class="action-btn secondary">
                    🖨 Print Waybill Label
                </a>
                ${p.chat_url ? `
                    <a href="${p.chat_url}" class="action-btn success">
                        💬 Message Assigned Rider
                    </a>
                ` : ''}
            </div>
        `;
    }

    // Attach File Upload Scanner
    if (fileInput) {
        fileInput.addEventListener('change', async (e) => {
            if (!e.target.files || e.target.files.length === 0) return;
            const file = e.target.files[0];
            try {
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("camera-reader");
                }
                const result = await html5QrCode.scanFile(file, true);
                if (result) {
                    handleScanSuccess(result);
                }
            } catch (err) {
                alert('No readable QR code or barcode found in the uploaded image. Please try a clearer photo or enter the code manually.');
            }
        });
    }

    // Button event listeners
    if (toggleBtn) toggleBtn.addEventListener('click', toggleCamera);
    document.getElementById('btn-start-camera')?.addEventListener('click', startCamera);
    if (switchBtn) switchBtn.addEventListener('click', switchCamera);

    // If an initial code was present, start by looking it up
    @if(!empty($code))
        lookupCode('{{ $code }}');
    @endif
})();
</script>
<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endpush
