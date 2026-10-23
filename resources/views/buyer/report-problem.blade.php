@extends('layouts.app')

@section('title', 'Report a problem | cartzy')

@php
    $problemTypes = \App\Models\AdminDispute::CATEGORIES;
    $photoCategories = collect($problemTypes)->filter(fn ($c) => $c[1])->keys()->all();
    $order = $sellerOrder->order;
@endphp

@section('content')
<div class="report-page">
    <div class="report-shell">
        <nav class="report-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span>
            <a href="{{ route('buyer.dashboard', ['tab' => 'orders']) }}">Orders</a><span aria-hidden="true">/</span><span aria-current="page">Report a problem</span>
        </nav>
        <header class="report-heading">
            <p class="report-eyebrow">WE'RE HERE TO HELP</p>
            <h1>Report a problem</h1>
            <p>Tell us what went wrong with order <strong>{{ $order->reference }}</strong> from <strong>{{ $sellerOrder->seller?->name ?? 'this store' }}</strong>. Cartzy will review your report and reply in your Notifications.</p>
        </header>

        @if($errors->any())
            <div class="report-alert" role="alert"><strong>Please check your report.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('orders.report.store', $sellerOrder->id) }}" enctype="multipart/form-data" class="report-form" id="report-form" data-photo-categories='@json($photoCategories)'>
            @csrf

            <section class="report-card" aria-labelledby="report-what">
                <h2 id="report-what"><span>1</span> What went wrong?</h2>
                <div class="report-options">
                    @foreach($problemTypes as $key => [$label, $needsPhoto])
                        <label class="report-option"><input type="radio" name="category" value="{{ $key }}" required @checked(old('category') === $key)><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </section>

            <section class="report-card" aria-labelledby="report-items">
                <h2 id="report-items"><span>2</span> Which items? <small>Optional</small></h2>
                <div class="report-items">
                    @foreach($sellerOrder->items as $item)
                        <label class="report-item">
                            <input type="checkbox" name="items[]" value="{{ $item->id }}" @checked(in_array($item->id, old('items', [])))>
                            <span><strong>{{ $item->product_name }}</strong><small>{{ $item->variant_name }} · x{{ $item->quantity }}</small></span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="report-card" aria-labelledby="report-details">
                <h2 id="report-details"><span>3</span> Tell us more</h2>
                <label class="report-field">What happened?
                    <textarea name="description" rows="5" minlength="20" maxlength="3000" required placeholder="e.g. The box was crushed and the screen is cracked. I noticed it when I opened the parcel.">{{ old('description') }}</textarea>
                </label>
                <fieldset class="report-field">
                    <legend>Photos <small id="photo-hint">Optional, up to 4 photos</small></legend>
                    <input type="file" name="photos[]" id="report-photos" accept="image/jpeg,image/png,image/webp" multiple>
                    <small>Clear photos of the item, packaging and shipping label help us resolve your report faster. JPG, PNG or WebP, up to 5 MB each.</small>
                </fieldset>
            </section>

            <section class="report-card" aria-labelledby="report-outcome">
                <h2 id="report-outcome"><span>4</span> What would you like us to do?</h2>
                <div class="report-options">
                    @foreach(\App\Models\AdminDispute::RESOLUTIONS as $key => $label)
                        <label class="report-option"><input type="radio" name="requested_resolution" value="{{ $key }}" required @checked(old('requested_resolution') === $key)><span>{{ $label }}</span></label>
                    @endforeach
                </div>
                <label class="report-field">Contact number
                    <input name="contact_phone" value="{{ old('contact_phone', $phone) }}" required maxlength="30" inputmode="tel">
                    <small>We'll only use this if we need more details about your report.</small>
                </label>
            </section>

            <div class="report-actions">
                <a href="{{ route('buyer.dashboard', ['tab' => 'orders']) }}">Cancel</a>
                <button type="submit">Send report</button>
            </div>
            <p class="report-note">Sending a report does not issue a refund by itself. Our team will review it with the seller and courier and let you know the outcome.</p>
        </form>
    </div>
</div>

<style>
.report-page{background:#F6F4F8;padding:32px 24px 56px;min-height:calc(100vh - 72px)}
.report-shell{width:100%;max-width:1040px;margin:0 auto}
.report-breadcrumb{display:flex;gap:8px;font-size:12px;color:#8c8396;margin-bottom:18px}.report-breadcrumb a{color:#6f6382}
.report-heading{margin-bottom:22px}.report-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.5px;color:#a56f60;margin:0}
.report-heading h1{font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(34px,4vw,46px);font-weight:700;color:#282133;margin:4px 0 8px;letter-spacing:-.03em}
.report-heading p{color:#6e6679;font-size:14px;line-height:1.7;margin:0}
.report-alert{padding:14px;border:1px solid #eccfc5;background:#fff7f3;color:#9f513d;border-radius:12px;font-size:13px;margin-bottom:18px}.report-alert ul{margin:5px 0 0;padding-left:18px}
.report-form{display:grid;gap:16px}
.report-card{background:#fff;border:1px solid #e7e3ec;border-radius:18px;padding:22px}
.report-card h2{display:flex;align-items:center;gap:10px;font-size:16px;font-weight:800;color:#282133;margin:0 0 16px}
.report-card h2>span{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#f3f0f7;color:#7d6b8e;font-size:12px}
.report-card h2 small,.report-field legend small{font-size:11px;font-weight:600;color:#9a90a4}
.report-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.report-option,.report-item{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid #e5e0eb;border-radius:12px;font-size:13px;color:#3e3548;cursor:pointer}
.report-option:has(input:checked),.report-item:has(input:checked){border-color:#8d7a9e;background:#faf8fc;box-shadow:inset 0 0 0 1px #8d7a9e}
.report-option input,.report-item input{width:17px;height:17px;accent-color:#6f6382;flex-shrink:0}
.report-items{display:grid;gap:8px}.report-item span{display:grid;gap:2px}.report-item small{font-size:11px;color:#8b8295}
.report-field{display:grid;gap:8px;font-size:13px;font-weight:700;color:#51455f;border:0;padding:0;margin:16px 0 0}
.report-card>.report-field:first-of-type{margin-top:0}
.report-field legend{padding:0;margin-bottom:8px}
.report-field textarea,.report-field input:not([type=file]){border:1px solid #e1dbe8;border-radius:12px;padding:11px 13px;font:500 14px 'Lato',sans-serif;color:#282133;width:100%;box-sizing:border-box}
.report-field textarea:focus,.report-field input:focus{outline:none;border-color:#8d7a9e;box-shadow:0 0 0 3px #6f638220}
.report-field input[type=file]{font-size:13px}
.report-field small{font-size:11px;font-weight:400;color:#8b8295;line-height:1.6}
.report-actions{display:flex;justify-content:flex-end;align-items:center;gap:16px}
.report-actions a{font-size:13px;font-weight:700;color:#6f6382}
.report-actions button{border:0;border-radius:12px;background:#6f6382;color:#fff;font:700 14px 'Lato',sans-serif;padding:14px 28px;cursor:pointer}
.report-actions button:hover{background:#564b68}.report-actions button:disabled{opacity:.6;cursor:wait}
.report-note{font-size:12px;color:#8c8396;text-align:right;margin:0}
@media(max-width:600px){.report-page{padding:20px 16px 40px}.report-options{grid-template-columns:1fr}.report-card{padding:18px}.report-actions{flex-direction:column-reverse;align-items:stretch;text-align:center}.report-note{text-align:center}}
</style>

<script>
(function () {
    const form = document.getElementById('report-form');
    const needPhotos = JSON.parse(form.dataset.photoCategories);
    const photos = document.getElementById('report-photos');
    const hint = document.getElementById('photo-hint');
    function syncPhotos() {
        const category = form.querySelector('input[name="category"]:checked')?.value;
        const required = needPhotos.includes(category);
        photos.required = required;
        hint.textContent = required ? 'Required, up to 4 photos' : 'Optional, up to 4 photos';
    }
    form.querySelectorAll('input[name="category"]').forEach(i => i.addEventListener('change', syncPhotos));
    photos.addEventListener('change', function () {
        this.setCustomValidity(this.files.length > 4 ? 'You can add up to 4 photos.' : '');
    });
    form.addEventListener('submit', function () {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Sending…';
    });
    syncPhotos();
})();
</script>
@endsection
