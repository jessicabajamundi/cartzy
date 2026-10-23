@extends('layouts.app')
@section('title', 'Flash Sale | cartzy')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/flash-sale.css') }}?v={{ filemtime(public_path('css/flash-sale.css')) }}">
<link rel="stylesheet" href="{{ asset('css/catalog-pagination.css') }}?v={{ filemtime(public_path('css/catalog-pagination.css')) }}">
@endpush
@section('content')
<div class="flash-page">
    <nav class="flash-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">Sale</span></nav>
    <header class="flash-hero">
        <div><p class="flash-eyebrow">THE GOOD FINDS. FOR LESS.</p><h1>Flash <em>Sale</em><span class="flash-spark" aria-hidden="true">✦</span></h1><p class="flash-intro">A little less to spend. A little more to love.<br>Discover live offers from our sellers, all in one place.</p></div>
        <div class="flash-hero-note"><span aria-hidden="true">%</span><p>Same favorites.<br><strong>Better prices.</strong></p><small>Active offers &middot; Updated by sellers</small></div>
    </header>
    @if(session('success'))<p class="flash-notice" role="status">{{ session('success') }} <a href="{{ route('cart.index') }}">View cart &rarr;</a></p>@endif
    @if($errors->any())<div class="flash-notice" role="alert">{{ $errors->first() }}</div>@endif
    <p id="wishlist-status" class="sr-only" role="status" aria-live="polite"></p>
    <div class="flash-grid" id="sale-products" data-wishlist-refs="{{ json_encode($wishlistRefs) }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}" data-login-url="{{ route('login') }}" data-per-page="{{ $products->perPage() }}" data-current-page="{{ $products->currentPage() }}">
    @forelse($products as $product)
        @php
            $product->variants->each(fn ($item) => $item->setRelation('product', $product));
            $variant = $product->variants->sortBy('sale_price_minor')->first();
            $discountPercent = round(($variant->price_minor - $variant->sale_price_minor) / $variant->price_minor * 100);
            $totalStock = (int) $product->variants->sum('stock');
            $isLowStock = $totalStock > 0 && $totalStock <= $product->stock_alert_threshold;
            $sold = (int) ($product->sold_quantity ?? 0);
        @endphp
        <article class="flash-card">
            <div class="flash-card-image">
                @if($variant->image_url || $product->coverImage)<img src="{{ $variant->resolved_image_url }}" alt="{{ $product->name }}" loading="lazy">@else<span class="flash-image-placeholder" aria-hidden="true">@include('seller.partials.icon', ['name' => 'bag', 'size' => 60])</span>@endif
                <span class="flash-seller-badge"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M9.2 1 3.5 9h4l-1 6 6-8.5H8.4z" fill="currentColor"/></svg>Flash Sale</span>
                <span class="flash-offer-label" data-discount-percent>-{{ $discountPercent < 1 ? '<1' : min(99, $discountPercent) }}%</span>
                <p class="flash-expiry"><svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 4.5V8l2.3 1.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>{{ $product->discount_ends_at ? 'Ends '.$product->discount_ends_at->setTimezone('Asia/Manila')->format('M j, g:i A') : 'Limited-time offer' }}</p>
            </div>
            <div class="flash-card-body">
                <div class="flash-info">
                    <a class="flash-store" href="{{ route('store.show', $product->seller->slug) }}">{{ $product->seller->name }}</a>
                    <h3><a class="flash-product-link" href="{{ route('products.show', $product->id) }}" aria-label="{{ $product->name }} — view details">{{ $product->name }}</a></h3>
                    @if(\App\Models\Product::qualifiesForFreeShipping($variant->sale_price_minor, $product->seller_id))<span class="flash-free-shipping">Free Shipping</span>@endif
                </div>
                <div class="flash-pricing">
                    <div class="flash-card-price">
                        <strong data-sale-price><span class="flash-currency">₱</span>{{ number_format($variant->sale_price_minor / 100, 2) }}</strong>
                        <del data-regular-price>₱{{ number_format($variant->price, 2) }}</del>
                    </div>
                    <div class="flash-rating">
                        <span class="flash-score"><span aria-hidden="true">&#9733;</span> {{ $product->ratingLabel() }}</span>
                        <span class="flash-dot" aria-hidden="true"></span>
                        <span>{{ $sold >= 1000 ? round($sold / 1000, 1).'k' : number_format($sold) }} sold</span>
                    </div>
                </div>
                <div class="flash-availability">
                    <span class="flash-stock {{ $totalStock < 1 ? 'is-out' : ($isLowStock ? 'is-low' : '') }}"><span class="flash-stock-dot" aria-hidden="true"></span>@if($totalStock < 1)Out of stock @elseif($isLowStock)Only {{ number_format($totalStock) }} left @else{{ number_format($totalStock) }} in stock @endif</span>
                </div>
                <div class="flash-actions">
                @include('partials.discovery-wishlist', ['product' => ['ref' => 'variant_'.$variant->id, 'name' => $product->name, 'price' => $variant->sale_price_minor / 100, 'image' => $variant->resolved_image_url, 'variation' => $variant->name]])
                <form method="POST" action="{{ route('cart.add') }}" class="flash-cart-form" id="sale-cart-{{ $product->id }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}"><input type="hidden" name="name" value="{{ $product->name }}"><input type="hidden" name="price" value="{{ $variant->sale_price_minor / 100 }}"><input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                    <button type="submit"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3h2l3 12h11l3-8H6M8 15l-1 3h13"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/></svg>Add to Cart</button>
                </form>
                </div>
            </div>
        </article>
    @empty
        <div class="flash-empty"><span aria-hidden="true">✦</span><h2>Fresh offers are on their way.</h2><p>No active sale products right now. Come back soon for more good finds.</p><a href="{{ route('home') }}">Browse all products &rarr;</a></div>
    @endforelse
    </div>
    @if($products->total() > 0)
    <div class="flash-pagination">
        <p class="flash-page-summary">Showing {{ $products->firstItem() ?? 0 }}&ndash;{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</p>
        <nav class="flash-page-controls" aria-label="Sale pagination">
            @if($products->onFirstPage())
                <span class="is-disabled" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
            @else
                <a href="{{ $products->previousPageUrl() }}#sale-products" aria-label="Previous page">&lsaquo;</a>
            @endif
            @php($previousPage = 0)
            @for($page = 1; $page <= $products->lastPage(); $page++)
                @if($products->lastPage() <= 5 || $page === 1 || $page === $products->lastPage() || abs($page - $products->currentPage()) <= 1)
                    @if($previousPage && $page - $previousPage > 1)<span class="flash-page-gap" aria-hidden="true">&hellip;</span>@endif
                    @if($page === $products->currentPage())
                        <span aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                    @else
                        <a href="{{ $products->url($page) }}#sale-products" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                    @php($previousPage = $page)
                @endif
            @endfor
            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}#sale-products" aria-label="Next page">&rsaquo;</a>
            @else
                <span class="is-disabled" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
            @endif
        </nav>
    </div>
    @endif
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/discovery-wishlist.js') }}?v={{ filemtime(public_path('js/discovery-wishlist.js')) }}"></script>
<script>
// Keep five rows at each grid breakpoint while retaining server pagination.
const saleGrid = document.getElementById('sale-products');
function syncSalePageSize() {
    const columns = getComputedStyle(saleGrid).gridTemplateColumns.split(/\s+/).filter(Boolean).length;
    const perPage = columns * 5;
    const previousSize = Number(saleGrid.dataset.perPage);
    if (perPage === previousSize || ![10, 15, 20, 25].includes(perPage)) return;
    const url = new URL(window.location.href);
    const firstIndex = (Number(saleGrid.dataset.currentPage) - 1) * previousSize;
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', Math.floor(firstIndex / perPage) + 1);
    window.location.replace(url.toString());
}
syncSalePageSize();
let saleResizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(saleResizeTimer);
    saleResizeTimer = setTimeout(syncSalePageSize, 200);
});
</script>
@endpush
