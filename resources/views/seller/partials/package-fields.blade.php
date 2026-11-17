<fieldset class="pe-card-body pe-grid" style="border:0">
    <legend>Packaged size &amp; weight</legend>
    <p class="pe-hint">Measure one fully packed unit, including packaging. Checkout is unavailable until these details are complete. Use the largest packaged variant dimensions.</p>
    @foreach(['length', 'width', 'height'] as $dimension)
        <label class="pe-field">Package {{ $dimension }} (cm) *
            <input type="number" name="package_{{ $dimension }}_cm" min="0.01" max="10000" step="0.01" required value="{{ old('package_'.$dimension.'_cm', isset($product) ? $product->{'package_'.$dimension.'_cm'} : '') }}">
        </label>
    @endforeach
</fieldset>
