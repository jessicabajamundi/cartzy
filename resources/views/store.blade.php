@extends('layouts.app')
@section('title', $seller->name.' | cartzy')
@section('meta_description', \Illuminate\Support\Str::limit($seller->description ?: 'Shop products from '.$seller->name.' on cartzy.', 155))
@push('styles')
<link rel="stylesheet" href="{{ asset('css/store.css') }}?v={{ filemtime(public_path('css/store.css')) }}">
<link rel="stylesheet" href="{{ asset('css/chat-popup.css') }}?v={{ filemtime(public_path('css/chat-popup.css')) }}">
@endpush
@section('content')
@php
    $initial = mb_strtoupper(mb_substr($seller->name, 0, 1));
    $location = $seller->pickupAddress ? $seller->pickupAddress->city.', '.$seller->pickupAddress->province : null;
    $tabUrl = fn ($name) => route('store.show', ['slug' => $seller->slug, 'tab' => $name === 'products' ? null : $name]);
@endphp
<div class="st-page">
    <nav class="st-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Stores</span><span>/</span><span aria-current="page">{{ $seller->name }}</span></nav>
    @if(session('success'))<p class="st-notice" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<div class="st-notice st-error" role="alert">{{ $errors->first() }}</div>@endif

    <header class="st-hero" @if($seller->bannerUrl()) style="--st-banner: url('{{ $seller->bannerUrl() }}')" @endif>
        <div class="st-identity">
            @if($seller->logoUrl())
                <img class="st-logo" src="{{ $seller->logoUrl() }}" alt="{{ $seller->name }} logo">
            @else
                <span class="st-logo" aria-hidden="true">{{ $initial }}</span>
            @endif
            <div class="st-name">
                <h1>{{ $seller->name }}</h1>
                <p><span class="st-verified">@include('seller.partials.icon', ['name' => 'shield', 'size' => 14]) Verified seller</span>@if($location)<span>Ships from {{ $location }}</span>@endif</p>
            </div>
        </div>
        <div class="st-actions">
            @if($canFollow)
                <form method="POST" action="{{ route('store.follow', $seller->slug) }}" id="st-follow-form">@csrf
                    <button type="submit" class="st-button {{ $isFollowing ? 'is-following' : '' }}" id="st-follow" aria-pressed="{{ $isFollowing ? 'true' : 'false' }}">{{ $isFollowing ? 'Following' : '+ Follow' }}</button>
                </form>
            @elseif(!auth()->check())
                <a class="st-button" href="{{ route('login') }}">+ Follow</a>
            @elseif($isOwner)
                <a class="st-button st-button-ghost" href="{{ route('seller.account') }}">Edit store profile</a>
            @endif
            <button type="button" class="st-button st-button-ghost" id="st-share" data-url="{{ route('store.show', $seller->slug) }}">Share</button>
        </div>
    </header>

    <dl class="st-stats">
        <div><dt>Followers</dt><dd id="st-followers" data-count="{{ $stats['followers'] }}">{{ number_format($stats['followers']) }}</dd></div>
        <div><dt>Rating</dt><dd>@if($stats['rating']){{ number_format($stats['rating'], 1) }} <small>/ 5 ({{ number_format($stats['reviews']) }})</small>@else<small>No ratings yet</small>@endif</dd></div>
        <div><dt>Products</dt><dd>{{ number_format($stats['products']) }}</dd></div>
        <div><dt>Items sold</dt><dd>{{ number_format($stats['sold']) }}</dd></div>
        <div><dt>Joined</dt><dd>{{ $seller->created_at?->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE) ?? '—' }} <small>ago</small></dd></div>
    </dl>

    @include('partials.shop-vouchers', ['shopName' => $seller->name])

    <nav class="st-tabs" aria-label="Store sections">
        @foreach(['products' => 'Products ('.$stats['products'].')', 'reviews' => 'Reviews ('.$stats['reviews'].')', 'about' => 'About'] as $name => $label)
            <a href="{{ $tabUrl($name) }}" @if($tab === $name) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if($tab === 'products')
    <section id="store-products" aria-label="Products from {{ $seller->name }}">
        <form class="st-filters" method="GET" action="{{ route('store.show', $seller->slug) }}" role="search">
            <label class="st-search"><span class="st-sr">Search this store</span>
                @include('seller.partials.icon', ['name' => 'search', 'size' => 16])
                <input type="search" name="q" value="{{ $search }}" placeholder="Search in {{ $seller->name }}" maxlength="100">
            </label>
            @if($categories->count() > 0)
            <label><span class="st-sr">Category</span>
                <select name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $category)<option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>{{ $category->name }}</option>@endforeach
                </select>
            </label>
            @endif
            <label><span class="st-sr">Sort by</span>
                <select name="sort" onchange="this.form.submit()">
                    @foreach(\App\Http\Controllers\StoreController::SORTS as $value => $label)<option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>@endforeach
                </select>
            </label>
            <button type="submit" class="st-button">Search</button>
        </form>
        @if($search !== '' || $categorySlug !== '')
            <p class="st-muted">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }}@if($search !== '') for “{{ $search }}”@endif. <a href="{{ route('store.show', $seller->slug) }}">Clear filters</a></p>
        @endif
        <p id="wishlist-status" class="st-sr" role="status" aria-live="polite"></p>
        <div class="st-grid" id="store-products-grid" data-wishlist-refs="{{ json_encode($wishlistRefs) }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}" data-login-url="{{ route('login') }}">
        @forelse($products as $product)
            @php
                $product->variants->each(fn ($item) => $item->setRelation('product', $product));
                $variant = $product->variants->sortBy('sale_price_minor')->first();
                $onSale = $variant && $variant->sale_price_minor < $variant->price_minor;
                $totalStock = (int) $product->variants->sum('stock');
                $inStock = $totalStock > 0;
            @endphp
            <article class="st-card">
                <div class="st-card-image">
                    @if($variant && ($variant->image_url || $product->coverImage))<img src="{{ $variant->resolved_image_url }}" alt="{{ $product->name }}" loading="lazy">@else<span class="st-placeholder" aria-hidden="true">@include('seller.partials.icon', ['name' => 'bag', 'size' => 48])</span>@endif
                    @if($onSale)<span class="st-badge">Sale</span>@elseif($product->badge)<span class="st-badge is-soft">{{ $product->badge }}</span>@endif
                    @if(!$inStock)<span class="st-soldout">Sold out</span>@endif
                    @if($variant)@include('partials.discovery-wishlist', ['product' => ['ref' => 'variant_'.$variant->id, 'name' => $product->name, 'price' => $variant->sale_price_minor / 100, 'image' => $variant->resolved_image_url, 'variation' => $variant->name]])@endif
                </div>
                <div class="st-card-body">
                    <h3><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h3>
                    @if($variant)
                    <p class="st-price"><strong>₱{{ number_format($variant->sale_price_minor / 100, 2) }}</strong>@if($onSale)<del>₱{{ number_format($variant->price_minor / 100, 2) }}</del>@endif</p>
                    @endif
                    @if(\App\Models\Product::qualifiesForFreeShipping($variant?->sale_price_minor, $product->seller_id))<p class="st-free-shipping">Free Shipping</p>@endif
                    <p class="st-meta"><span><span aria-hidden="true" class="st-star">&#9733;</span> {{ $product->ratingLabel() }}</span></p>
                    <div class="st-stock-row">
                        <p class="st-stock {{ !$inStock ? 'is-out' : '' }}">@if($inStock){{ number_format($totalStock) }} in stock @else Out of stock @endif</p>
                        <span class="st-sold">{{ number_format($product->sold_quantity ?? 0) }} sold</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="st-empty">
                @if($search !== '' || $categorySlug !== '')
                    <h2>No matching products</h2><p>Try a different search or category.</p><a href="{{ route('store.show', $seller->slug) }}">Show all products</a>
                @else
                    <h2>No products yet</h2><p>{{ $seller->name }} hasn't listed anything yet. Follow the store to keep it handy.</p>
                @endif
            </div>
        @endforelse
        </div>
        @include('partials.store-pagination', ['paginator' => $products, 'label' => 'Product pages'])
    </section>
    @elseif($tab === 'reviews')
    <section id="store-reviews" class="st-panel" aria-labelledby="st-reviews-title">
        <h2 id="st-reviews-title">Store ratings &amp; reviews</h2>
        <div class="st-rating-overview">
            <div class="st-rating-score">
                @if($stats['rating'])
                    <strong>{{ number_format($stats['rating'], 1) }}</strong><span class="st-stars" aria-label="{{ number_format($stats['rating'], 1) }} out of 5 stars"><span style="width: {{ $stats['rating'] / 5 * 100 }}%"></span></span>
                    <small>{{ number_format($stats['reviews']) }} {{ \Illuminate\Support\Str::plural('review', $stats['reviews']) }} across all products</small>
                @else
                    <strong>—</strong><small>No ratings yet</small>
                @endif
            </div>
            <ul class="st-breakdown" aria-label="Rating breakdown">
                @foreach($stats['breakdown'] as $star => $count)
                    <li><span>{{ $star }} &#9733;</span><span class="st-bar"><span style="width: {{ $stats['reviews'] ? $count / $stats['reviews'] * 100 : 0 }}%"></span></span><span>{{ $count }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="st-chips" role="group" aria-label="Filter reviews by rating">
            <a href="{{ route('store.show', ['slug' => $seller->slug, 'tab' => 'reviews']) }}#store-reviews" @if(!$ratingFilter) aria-current="true" @endif>All</a>
            @foreach(range(5, 1) as $star)
                <a href="{{ route('store.show', ['slug' => $seller->slug, 'tab' => 'reviews', 'rating' => $star]) }}#store-reviews" @if($ratingFilter === $star) aria-current="true" @endif>{{ $star }} &#9733; ({{ $stats['breakdown'][$star] }})</a>
            @endforeach
        </div>
        @forelse($reviews as $review)
            <article class="st-review">
                <span class="st-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user?->name ?? 'B', 0, 1)) }}</span>
                <div>
                    <div class="st-review-head"><strong>{{ $review->user?->name ?? 'Buyer' }}</strong><span class="st-stars is-small" aria-label="{{ $review->rating }} out of 5 stars"><span style="width: {{ $review->rating * 20 }}%"></span></span><time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('M j, Y') }}</time></div>
                    @if($review->product)<p class="st-muted">Product: @if($review->product->is_active && !$review->product->trashed())<a href="{{ route('products.show', $review->product_id) }}">{{ $review->product->name }}</a>@else{{ $review->product->name }}@endif</p>@endif
                    @if($review->comment)<p>{{ $review->comment }}</p>@endif
                    @if($review->seller_reply)<blockquote><strong>Seller reply</strong> {{ $review->seller_reply }}</blockquote>@endif
                </div>
            </article>
        @empty
            <p class="st-empty-line">{{ $ratingFilter ? 'No '.$ratingFilter.'-star reviews yet.' : 'No reviews yet. Ratings from buyers will appear here.' }}</p>
        @endforelse
        @include('partials.store-pagination', ['paginator' => $reviews, 'label' => 'Review pages'])
    </section>
    @else
    <section class="st-panel" aria-labelledby="st-about-title">
        <h2 id="st-about-title">About {{ $seller->name }}</h2>
        <p class="st-description">{{ $seller->description ?: 'This seller has not added a store description yet.' }}</p>
        <dl class="st-about">
            <div><dt>Member since</dt><dd>{{ $seller->created_at?->format('F Y') ?? '—' }}</dd></div>
            @if($location)<div><dt>Ships from</dt><dd>{{ $location }}</dd></div>@endif
            <div><dt>Followers</dt><dd>{{ number_format($stats['followers']) }}</dd></div>
            <div><dt>Store rating</dt><dd>{{ $stats['rating'] ? number_format($stats['rating'], 1).' / 5 from '.number_format($stats['reviews']).' '.\Illuminate\Support\Str::plural('review', $stats['reviews']) : 'No ratings yet' }}</dd></div>
            <div><dt>Active listings</dt><dd>{{ number_format($stats['products']) }}</dd></div>
            <div><dt>Items sold</dt><dd>{{ number_format($stats['sold']) }}</dd></div>
        </dl>
        <p class="st-muted">Have a question? Use the <strong>Chat</strong> bubble to message {{ $seller->name }}, or <strong>Message seller</strong> on a product page to ask about a specific item.</p>
    </section>
    @endif

    @guest
        <div class="chat-bubble-form"><a class="chat-bubble" href="{{ route('login') }}" aria-label="Log in to chat with {{ $seller->name }}">@include('seller.partials.icon', ['name' => 'chat-round', 'size' => 22])<span>Chat</span></a></div>
    @else
        @if($canChat)
        <form method="POST" action="{{ route('store.message', $seller->slug) }}" id="pd-message-form" class="chat-bubble-form">@csrf
            <button type="submit" class="chat-bubble" title="Click to chat here, double-click to open Messages" aria-controls="pd-chat" aria-expanded="false" aria-label="Chat with {{ $seller->name }}">@include('seller.partials.icon', ['name' => 'chat-round', 'size' => 22])<span>Chat</span></button>
        </form>
        <section class="pd-chat" id="pd-chat" role="dialog" aria-labelledby="pd-chat-title" data-avatar="{{ $seller->logoUrl() }}" data-empty-text="Say hello! Ask {{ $seller->name }} about products, stock, or shipping." hidden>
            <header class="pd-chat-head">
                <div><strong id="pd-chat-title">{{ $seller->name }}</strong><small>Store chat</small></div>
                <a class="pd-chat-icon" id="pd-chat-expand" href="{{ route('buyer.messages') }}" aria-label="Open in Messages" title="Open in Messages">&#x2197;</a>
                <button class="pd-chat-icon pd-chat-minimize" type="button" id="pd-chat-minimize" aria-label="Minimize chat" title="Minimize">&minus;</button>
                <button class="pd-chat-icon" type="button" id="pd-chat-close" aria-label="Close chat" title="Close">&times;</button>
            </header>
            <div class="pd-chat-messages" id="pd-chat-messages" aria-live="polite"></div>
            <form class="pd-chat-compose" id="pd-chat-compose">
                <label class="pd-chat-sr" for="pd-chat-input">Message</label>
                <textarea id="pd-chat-input" name="message" rows="1" maxlength="5000" placeholder="Write a message…" required></textarea>
                <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
            </form>
            <p class="pd-chat-error" id="pd-chat-error" role="alert" hidden></p>
        </section>
        @endif
    @endguest
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/discovery-wishlist.js') }}?v={{ filemtime(public_path('js/discovery-wishlist.js')) }}"></script>
<script src="{{ asset('js/store.js') }}?v={{ filemtime(public_path('js/store.js')) }}" defer></script>
<script src="{{ asset('js/product-chat.js') }}?v={{ filemtime(public_path('js/product-chat.js')) }}" defer></script>
@endpush
