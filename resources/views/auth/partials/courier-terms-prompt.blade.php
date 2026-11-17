<div id="courierTermsWrapper" class="logistics-terms-prompt" style="margin: 14px 0 16px;">
    <input type="checkbox" id="courier_terms_agreed" name="terms_agreed" value="1" style="display: none;" @checked(old('terms_agreed'))>
    <div class="logistics-terms-prompt-row">
        <span>Read and agree to the</span>
        <button type="button" class="logistics-terms-link" id="openCourierTermsBtn" aria-haspopup="dialog" aria-controls="courierTermsDialog">Cartzy Courier Terms &amp; Conditions and Privacy Policy</button>
        <span style="color:var(--dm-red-ink, #ef4444);font-weight:700">*</span>
        <span id="courierTermsAgreedBadge" class="logistics-terms-agreed-pill" style="display:{{ old('terms_agreed') ? 'inline-flex' : 'none' }}">✓ Agreed</span>
    </div>
    <p id="courier_terms_error" role="alert" style="color:var(--dm-red-ink, #ef4444);font-size:12.5px;margin-top:5px" @unless($errors->has('terms_agreed')) hidden @endunless>{{ $errors->first('terms_agreed') ?: 'You must agree to the Cartzy Courier Terms & Conditions and Privacy Policy to proceed.' }}</p>
</div>
