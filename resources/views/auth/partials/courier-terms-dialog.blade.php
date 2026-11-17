@php($courierPolicy = json_decode(file_get_contents(resource_path('legal/courier-terms.json')), true, 512, JSON_THROW_ON_ERROR))
<dialog id="courierTermsDialog" class="logistics-terms-dialog" aria-labelledby="courierTermsModalTitle">
    <div class="logistics-terms-modal-card">
        <div class="logistics-terms-modal-header">
            <div class="logistics-terms-modal-head-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg>
                <h3 id="courierTermsModalTitle" class="logistics-terms-modal-heading">Courier Terms &amp; Privacy Policy</h3>
            </div>
            <div class="logistics-terms-modal-head-actions">
                <button type="button" class="logistics-terms-close-btn" id="closeCourierTermsBtn" aria-label="Close modal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
            </div>
        </div>
        <div id="courierTermsModalBody" class="logistics-terms-modal-body" tabindex="0" role="region" aria-label="Courier Terms and Conditions and Privacy Policy document">
            <div class="logistics-terms-content">
                <h4 style="text-align:center;font-weight:800">CARTZY</h4>
                <p style="text-align:center;font-weight:700;margin-bottom:16px">TERMS &amp; CONDITIONS AND PRIVACY POLICY</p>
                @foreach($courierPolicy['blocks'] as $block)
                    @if($block['type'] === 'banner')
                        <h4 class="terms-section-banner">{{ $block['text'] }}</h4>
                    @elseif($block['type'] === 'heading')
                        <h5>{{ $block['text'] }}</h5>
                    @elseif($block['type'] === 'list')
                        <ul>@foreach($block['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    @else
                        <p>{{ $block['text'] }}</p>
                    @endif
                @endforeach
                <div class="logistics-terms-embedded-agreement">
                    <label class="logistics-terms-checkbox-label" for="modal_courier_terms_agreed" style="margin:0;width:100%">
                        <input type="checkbox" id="modal_courier_terms_agreed">
                        <span>{{ $courierPolicy['agreement'] }} <span style="color:var(--dm-red-ink, #ef4444)">*</span></span>
                    </label>
                    <div class="logistics-terms-modal-actions" style="margin-top:14px"><button type="button" class="btn-terms-agree" id="agreeCourierTermsBtn">I Agree</button></div>
                </div>
            </div>
        </div>
    </div>
</dialog>
<script src="{{ asset('js/auth/courier-terms.js') }}" defer></script>
