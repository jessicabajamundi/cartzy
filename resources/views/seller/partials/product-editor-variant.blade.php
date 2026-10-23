{{-- One option row in the Add product variations list. Keep in sync with createShadeCard() in public/js/seller-inventory.js. --}}
<div class="shade-card pe-variant" data-shade-index="{{ $idx }}">
    <label class="pe-field pe-variant-name"><span class="pe-variant-label">Option</span>
        <input type="text" name="variants[{{ $idx }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="e.g. Medium / Black" class="shade-name-input" data-required-variant="true" maxlength="255" {{ $required ? 'required' : '' }}>
    </label>
    <label class="pe-field"><span class="pe-variant-label">Price</span>
        <span class="pe-money"><span>₱</span><input type="number" name="variants[{{ $idx }}][price]" min="0.01" max="99999999.99" step="0.01" value="{{ $v['price'] ?? '' }}" placeholder="0.00" data-required-variant="true" {{ $required ? 'required' : '' }}></span>
    </label>
    <label class="pe-field"><span class="pe-variant-label">Stock</span>
        <input type="number" name="variants[{{ $idx }}][stock]" min="0" max="1000000" value="{{ $v['stock'] ?? 0 }}" data-required-variant="true" {{ $required ? 'required' : '' }}>
    </label>
    <button type="button" class="remove-shade-btn pe-remove" title="Remove this option" aria-label="Remove option">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <label class="pe-field pe-option-photo-row">Color photo
                <span class="pe-variant-photo">
                    <span class="shade-photo-preview empty"><span class="preview-placeholder">No photo</span></span>
                    <input type="file" name="variants[{{ $idx }}][image]" accept="image/jpeg,image/png,image/webp" class="shade-file-input">
                </span>
            </label>
    <details class="pe-variant-extras">
        <summary>Swatch &amp; stock code (optional)</summary>
        <div>
            <label class="pe-field">SKU (optional)
                <input type="text" name="variants[{{ $idx }}][sku]" value="{{ $v['sku'] ?? '' }}" placeholder="Auto" maxlength="100">
            </label>

            <label class="pe-field">Color swatch
                <span class="pe-swatch">
                    <input type="color" name="variants[{{ $idx }}][color]" value="{{ $v['color'] ?? '#1E293B' }}" class="color-swatch-input">
                    <span class="swatch-hex-text">{{ strtoupper($v['color'] ?? '#1E293B') }}</span>
                </span>
            </label>
        </div>
    </details>
</div>
