@php
    // The option builder separates color and size with a spaced slash.
    // Keep slashes inside colorway names intact.
    $variantGroups = $product->variants->groupBy(function ($variant) {
        $separator = strrpos($variant->name, ' / ');
        return $separator === false ? '' : substr($variant->name, 0, $separator);
    });
@endphp
<button type="button" class="catalog-variants-trigger" popovertarget="catalog-variants-{{ $product->id }}" aria-label="View options &amp; stock for {{ $product->name }}">
    {{ $product->variants->count() }} variants <span aria-hidden="true">⌄</span>
</button>
<div class="catalog-variants" id="catalog-variants-{{ $product->id }}" popover aria-labelledby="catalog-variants-title-{{ $product->id }}">
    <div class="catalog-variants-heading">
        <div>
            <strong id="catalog-variants-title-{{ $product->id }}">{{ $product->name }}</strong>
            <span>{{ $product->variants->count() }} options &middot; Stock by variant</span>
        </div>
        <button type="button" popovertarget="catalog-variants-{{ $product->id }}" popovertargetaction="hide" aria-label="Close variants">&times;</button>
    </div>
    <div class="catalog-variants-body">
        <p>Select an option to manage its stock.</p>
        @foreach($variantGroups as $groupName => $groupVariants)
            <div class="catalog-variant-group">
                <h4>{{ $groupName !== '' ? $groupName : 'Options' }}</h4>
                <div class="catalog-variant-options">
                    @foreach($groupVariants as $variant)
                        @php($optionName = $groupName !== '' ? substr($variant->name, strrpos($variant->name, ' / ') + 3) : $variant->name)
                        <a class="catalog-variant-option {{ $variant->stock <= 0 ? 'is-empty' : ($variant->stock <= $product->stock_alert_threshold ? 'is-low' : '') }}"
                           href="{{ route('seller.inventory', ['search' => $variant->sku ?: $product->name]) }}"
                           aria-label="{{ $variant->name }}: {{ $variant->stock }} in stock. Manage stock."
                           title="{{ $variant->name }} · PHP {{ number_format($variant->price, 2) }}">
                            @if($variant->image_url)
                                <img src="{{ $variant->image_url }}" alt="" loading="lazy">
                            @endif
                            <strong>{{ $optionName }}</strong>
                            <span>{{ $variant->stock > 0 ? $variant->stock.' left' : 'Out of stock' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
