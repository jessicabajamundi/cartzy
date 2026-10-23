@extends('layouts.app')
@section('title', $product->name.' | cartzy')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/product-details.css') }}?v={{ filemtime(public_path('css/product-details.css')) }}">
<link rel="stylesheet" href="{{ asset('css/chat-popup.css') }}?v={{ filemtime(public_path('css/chat-popup.css')) }}">
@endpush
@section('content')
<div class="pd-page">
    <nav class="pd-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('sale') }}">Shop offers</a><span>/</span><span aria-current="page">{{ $product->name }}</span></nav>
    @if(session('success'))<div class="pd-notice" role="status">{{ session('success') }} <a href="{{ route('cart.index') }}">View cart &rarr;</a></div>@endif
    @if($errors->any())<div class="pd-notice pd-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="pd-layout">
        <section class="pd-gallery" aria-label="Product photos">
            <div class="pd-main-photo">
                @if($photos->isNotEmpty())
                    <img id="pd-main-image" @if($selected && empty($selected['images'])) hidden @endif src="{{ $selected['image'] ?? $photos->first() }}" alt="{{ $product->name }}" fetchpriority="high">
                    <button type="button" class="pd-zoom-open" id="pd-zoom-open" aria-haspopup="dialog" aria-controls="pd-viewer" aria-label="View photo full screen" title="View full screen">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.5 4.5M11 8.5v5M8.5 11h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                @else
                    <div class="pd-placeholder">No product photo yet</div>
                @endif
                @if($product->sale_is_active)<span class="pd-sale-badge">Special offer</span>@endif
                @if($photos->count() > 1)
                    <div class="pd-photo-navigation" aria-label="Product photo navigation">
                        <button type="button" data-photo-step="-1" aria-label="Previous product photo" aria-controls="pd-main-image">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 5-7 7 7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" data-photo-step="1" aria-label="Next product photo" aria-controls="pd-main-image">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m10 5 7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                @endif
            </div>
            @if($photos->count() > 1)
            <div class="pd-thumbnails" aria-label="Choose a product photo">
                @foreach($photos as $photo)<button type="button" data-photo="{{ $photo }}" @if($selected && !in_array($photo, $selected['images'])) hidden @endif aria-label="Show product photo {{ $loop->iteration }}" aria-pressed="{{ $photo === ($selected['image'] ?? $photos->first()) ? 'true' : 'false' }}"><img src="{{ $photo }}" alt="" loading="lazy"></button>@endforeach
            </div>
            @endif
            <p class="pd-caption">@if($photos->isNotEmpty())<span class="pd-caption-hover">Hover over the photo to zoom &middot; </span>Click the photo to view it full screen @else Explore the photos, then choose the option that fits you. @endif</p>
        </section>
        @if($photos->isNotEmpty())
        <dialog class="pd-viewer" id="pd-viewer" aria-label="{{ $product->name }} photos">
            <div class="pd-viewer-bar">
                <span class="pd-viewer-count" id="pd-viewer-count" aria-live="polite"></span>
                <div class="pd-viewer-tools">
                    <button type="button" data-viewer-zoom="-1" aria-label="Zoom out" title="Zoom out"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.5 4.5M8.5 11h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
                    <span class="pd-viewer-level" id="pd-viewer-level">100%</span>
                    <button type="button" data-viewer-zoom="1" aria-label="Zoom in" title="Zoom in"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.5 4.5M11 8.5v5M8.5 11h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
                    <button type="button" class="pd-viewer-close" id="pd-viewer-close" aria-label="Close photo viewer" title="Close (Esc)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
                </div>
            </div>
            <div class="pd-viewer-stage" id="pd-viewer-stage">
                <img id="pd-viewer-image" src="" alt="{{ $product->name }}" draggable="false">
                @if($photos->count() > 1)
                <button type="button" class="pd-viewer-step is-prev" data-viewer-step="-1" aria-label="Previous photo"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 5-7 7 7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <button type="button" class="pd-viewer-step is-next" data-viewer-step="1" aria-label="Next photo"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m10 5 7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                @endif
            </div>
            <p class="pd-viewer-hint">Click or tap the photo to zoom &middot; Drag to move around &middot; Scroll or pinch to zoom more</p>
            @if($photos->count() > 1)
            <div class="pd-viewer-thumbs">
                @foreach($photos as $photo)<button type="button" data-viewer-photo="{{ $loop->index }}" @if($selected && !in_array($photo, $selected['images'])) hidden @endif aria-label="Show photo {{ $loop->iteration }}"><img src="{{ $photo }}" alt="" loading="lazy"></button>@endforeach
            </div>
            @endif
        </dialog>
        @endif
        <section class="pd-summary" aria-labelledby="product-title">
            <p class="pd-eyebrow">{{ $product->category?->name ?? 'THE GOOD FINDS' }}</p>
            <h1 id="product-title">{{ $product->name }}</h1>
            <ul class="pd-stats" aria-label="Ratings and sales">
                <li><a href="#reviews">@if($rating)<span class="pd-stats-score">{{ number_format($rating, 1) }}</span><span class="pd-stars is-small" aria-label="{{ number_format($rating, 1) }} out of 5 stars"><span style="width: {{ $rating / 5 * 100 }}%"></span></span>@else<span class="pd-stats-muted">No ratings yet</span>@endif</a></li>
                <li><a href="#reviews"><strong>{{ number_format($reviews->total()) }}</strong> {{ \Illuminate\Support\Str::plural('review', $reviews->total()) }}</a></li>
                <li><strong>{{ number_format($soldCount) }}</strong> sold</li>
            </ul>
            @if($selected)
            <div class="pd-price-box" aria-live="polite">
                <div class="pd-price-line">
                    <strong id="pd-price">₱{{ number_format($selected['price'] / 100, 2) }}</strong>
                    <del id="pd-regular" @if($selected['price'] >= $selected['regular']) hidden @endif>₱{{ number_format($selected['regular'] / 100, 2) }}</del>
                    <span class="pd-discount" id="pd-discount" @if($selected['price'] >= $selected['regular']) hidden @endif>-{{ $selected['regular'] > 0 ? round(($selected['regular'] - $selected['price']) / $selected['regular'] * 100) : 0 }}%</span>
                </div>
                <p id="pd-saving" @if($selected['price'] >= $selected['regular']) hidden @endif>You save ₱{{ number_format(($selected['regular'] - $selected['price']) / 100, 2) }}</p>
                @if($product->sale_is_active && $product->discount_ends_at)<small>Offer ends {{ $product->discount_ends_at->setTimezone('Asia/Manila')->format('M j, Y · g:i A') }} PHT</small>@endif
            </div>
            @endif
            {{-- Shipping estimate, like Shopee/Lazada: priced from this store to the buyer's default address. --}}
            <div class="pd-shipping">
                <span class="pd-shipping-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h11v12H3zM14 9h4l3 4v4h-7M7 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4M17 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/></svg></span>
                <div>
                    @if($shippingEstimate)
                        <p><span class="pd-shipping-label">Ships to</span> <strong>{{ $shippingEstimate['city'] ? $shippingEstimate['city'].', ' : '' }}{{ $shippingEstimate['province'] }}</strong></p>
                        <p class="pd-shipping-fee">Shipping fee <strong>₱{{ number_format($shippingEstimate['fee_minor'] / 100, 2) }}</strong> &middot; {{ $shippingEstimate['label'] }}</p>
                    @else
                        <p><span class="pd-shipping-label">Shipping from</span> <strong>₱{{ number_format($shippingFromMinor / 100, 2) }}</strong></p>
                        <p class="pd-shipping-fee">The fee depends on your address. @auth<a href="{{ route('buyer.dashboard', ['tab' => 'addresses']) }}">Add your address</a> to see the exact fee.@else<a href="{{ route('login') }}">Log in</a> to see the fee for your address.@endauth</p>
                    @endif
                    @if($storeFreeShippingMin !== null)
                        <p class="pd-shipping-free">🚚 Free shipping from this store {{ $storeFreeShippingMin > 0 ? 'on orders ₱'.number_format($storeFreeShippingMin / 100, 0).'+' : 'on any order' }}</p>
                    @endif
                </div>
            </div>

            @if($selected)
            @php
                $colors = $variants->pluck('color')->filter()->unique()->values();
                $sizes = $variants->pluck('size')->filter()->unique()->values();
                $sizeInStock = fn ($size) => $variants->contains(fn ($v) => $v['color'] === $selected['color'] && $v['size'] === $size && $v['stock'] > 0);
            @endphp
            <form method="POST" action="{{ route('cart.add') }}" id="pd-cart-form">
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="name" value="{{ $product->name }}">
                <input type="hidden" name="price" id="pd-price-input" value="{{ $selected['price'] / 100 }}">
                <input type="hidden" name="variant_id" id="pd-variant-input" value="{{ $selected['id'] }}">
                @if($variants->count() > 1)
                <div class="pd-variations">
                    @if($colors->isNotEmpty())
                    <div class="pd-variation" role="group" aria-labelledby="pd-color-label">
                        <p class="pd-variation-label" id="pd-color-label">{{ $sizes->isEmpty() ? 'Variation' : 'Color' }}</p>
                        <div class="pd-chips">
                        @foreach($colors as $color)
                            @php
                                $colorVariants = $variants->where('color', $color);
                                $colorImage = $colorVariants->pluck('image')->filter()->first();
                                $colorSoldOut = $colorVariants->every(fn ($v) => $v['stock'] < 1);
                            @endphp
                            <button type="button" class="pd-chip" data-pick="color" data-value="{{ $color }}" aria-pressed="{{ $selected['color'] === $color ? 'true' : 'false' }}" @disabled($colorSoldOut)>
                                @if($colorImage)<img src="{{ $colorImage }}" alt="" loading="lazy">@endif{{ $color }}@if($colorSoldOut)<small>Sold out</small>@endif
                            </button>
                        @endforeach
                        </div>
                    </div>
                    @endif
                    @if($sizes->isNotEmpty())
                    <div class="pd-variation" role="group" aria-labelledby="pd-size-label">
                        <p class="pd-variation-label" id="pd-size-label">Size</p>
                        <div class="pd-chips">
                        @foreach($sizes as $size)
                            <button type="button" class="pd-chip" data-pick="size" data-value="{{ $size }}" aria-pressed="{{ $selected['size'] === $size ? 'true' : 'false' }}" @disabled(!$sizeInStock($size))>{{ $size }}</button>
                        @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                <p class="pd-stock" role="status">
                    @if($selected['stock'] > 0)
                        <span class="pd-stock-name">Selected: <strong id="pd-selected-name">{{ $selected['name'] }}</strong></span><span id="pd-stock">{{ $selected['stock'] }} in stock</span>
                    @else
                        <span id="pd-stock">This product is currently sold out.</span>
                    @endif
                </p>
                <div class="pd-buy-row">
                    <div class="pd-quantity-field">
                        <label for="pd-quantity">Quantity</label>
                        <div class="pd-quantity-control">
                            <button type="button" data-quantity-step="-1" aria-label="Decrease quantity" aria-controls="pd-quantity" disabled>&minus;</button>
                            <input type="number" id="pd-quantity" name="quantity" min="1" max="{{ max(1, $selected['stock']) }}" step="1" inputmode="numeric" value="1" required @disabled($selected['stock'] < 1)>
                            <button type="button" data-quantity-step="1" aria-label="Increase quantity" aria-controls="pd-quantity" @disabled($selected['stock'] <= 1)>+</button>
                        </div>
                    </div>
                    <button class="pd-button" id="pd-add" type="submit" @disabled($selected['stock'] < 1)>{{ $selected['stock'] > 0 ? 'Add to cart' : 'Sold out' }}</button>
                </div>
                @if(auth()->check() && !auth()->user()->isBuyer())
                    <p class="pd-notice">Purchases require a separate buyer account. <a href="{{ route('buyer.required') }}">How to shop with a buyer account</a></p>
                @else
                    <p class="pd-muted">Shipping fees and available payment methods are shown at checkout. Stock is confirmed when you order.</p>
                @endif
            </form>
            @else
                <div class="pd-notice">No options are available for purchase right now.</div>
            @endif
        </section>
    </div>
    @php
        $specColors = $variants->pluck('color')->filter()->unique()->values();
        $specSizes = $variants->pluck('size')->filter()->unique()->values();
        $description = trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r\n", "\n", (string) $product->description)));
    @endphp
    <div class="pd-seller-row">
        <a class="pd-store" href="{{ route('store.show', $product->seller->slug) }}">
            <span class="pd-store-avatar" aria-hidden="true">@if($product->seller->logoUrl())<img src="{{ $product->seller->logoUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($product->seller->name, 0, 1)) }}@endif</span>
            <span class="pd-store-text"><small>Sold by</small><strong>{{ $product->seller->name }}</strong><span class="pd-store-visit">Visit store &rarr;</span></span>
        </a>
        @guest
            <a class="pd-message-button" href="{{ route('login') }}">@include('seller.partials.icon', ['name' => 'chat', 'size' => 16])Log in to message seller</a>
        @else
            @if(auth()->user()->isBuyer() && auth()->user()->status === 'active')
                <form method="POST" action="{{ route('products.inquire', $product->id) }}" id="pd-message-form">@csrf<button class="pd-message-button" type="submit" title="Click to chat here, double-click to open Messages" aria-controls="pd-chat" aria-expanded="false" aria-label="Message {{ $product->seller->name }} about {{ $product->name }}">@include('seller.partials.icon', ['name' => 'chat', 'size' => 16])Message seller</button></form>
            @endif
        @endguest
    </div>
    @include('partials.shop-vouchers', ['shopName' => $product->seller->name, 'compact' => true])
    <nav class="pd-section-links" aria-label="Product information">
        <a href="#details">Specifications</a>
        <a href="#description">Description</a>
        <a href="#reviews">Reviews <span>{{ $reviews->total() }}</span></a>
        <a href="#comments">Comments <span>{{ $comments->total() }}</span></a>
    </nav>
    <section class="pd-panel pd-info" aria-label="Product information">
        <div class="pd-info-block" id="details">
            <h2 class="pd-info-title">Specifications</h2>
            <dl class="pd-specs">
                <div><dt>Category</dt><dd>{{ $product->category?->name ?? 'Uncategorized' }}</dd></div>
                <div><dt>Store</dt><dd><a href="{{ route('store.show', $product->seller->slug) }}">{{ $product->seller->name }}</a></dd></div>
                @if($product->gender)<div><dt>For</dt><dd>{{ ucfirst($product->gender) }}</dd></div>@endif
                @if($specColors->isNotEmpty())
                    <div><dt>{{ \Illuminate\Support\Str::plural('Color', $specColors->count()) }}</dt><dd><ul class="pd-spec-tags">@foreach($specColors as $color)<li>{{ $color }}</li>@endforeach</ul></dd></div>
                @endif
                @if($specSizes->isNotEmpty())
                    <div><dt>{{ \Illuminate\Support\Str::plural('Size', $specSizes->count()) }}</dt><dd><ul class="pd-spec-tags">@foreach($specSizes as $size)<li>{{ $size }}</li>@endforeach</ul></dd></div>
                @endif
            </dl>
        </div>
        <div class="pd-info-block" id="description">
            <h2 class="pd-info-title">Description</h2>
            @if($description !== '')
                <div class="pd-description-wrap" data-description>
                    <p class="pd-description">{{ $description }}</p>
                </div>
                <button type="button" class="pd-description-toggle" data-description-toggle aria-expanded="false" hidden>Show more</button>
            @else
                <p class="pd-empty">The seller hasn't added a description yet. Ask a question in the comments below.</p>
            @endif
        </div>
    </section>
    <section class="pd-panel pd-feedback" aria-labelledby="feedback-title">
        <div class="pd-feedback-head">
            <h2 id="feedback-title" class="pd-info-title">Reviews &amp; comments</h2>
            <div class="pd-tabs" role="tablist" aria-label="Reviews and comments" hidden>
                <button type="button" role="tab" id="tab-reviews" aria-controls="reviews" aria-selected="true">Reviews <span>{{ $reviews->total() }}</span></button>
                <button type="button" role="tab" id="tab-comments" aria-controls="comments" aria-selected="false" tabindex="-1">Comments <span>{{ $comments->total() }}</span></button>
            </div>
        </div>

        <div class="pd-tabpanel" id="reviews" role="tabpanel" aria-labelledby="tab-reviews">
            <div class="pd-rating-summary">
                <div class="pd-rating-score">
                    <strong>{{ number_format($rating ?? 0, 1) }}</strong><span>out of 5</span>
                    <span class="pd-stars" aria-label="{{ number_format($rating ?? 0, 1) }} out of 5 stars"><span style="width: {{ ($rating ?? 0) / 5 * 100 }}%"></span></span>
                    <small>{{ $rating ? number_format($reviews->total()).' '.\Illuminate\Support\Str::plural('rating', $reviews->total()) : 'No ratings yet' }}</small>
                </div>
                <ul class="pd-rating-bars" aria-label="Ratings by star">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php $count = (int) ($starCounts[$star] ?? 0); @endphp
                        <li><span>{{ $star }} &#9733;</span><span class="pd-rating-bar" aria-hidden="true"><span style="width: {{ $reviews->total() ? $count / $reviews->total() * 100 : 0 }}%"></span></span><span class="pd-rating-count">{{ $count }}</span></li>
                    @endforeach
                </ul>
                <p class="pd-rating-cta">Bought this item?<a href="{{ route('buyer.dashboard') }}">Rate it from your orders</a></p>
            </div>
            @forelse($reviews as $review)
                <article class="pd-comment">
                    <span class="pd-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user?->name ?? 'B', 0, 1)) }}</span>
                    <div>
                        <div class="pd-comment-head"><strong>{{ $review->user?->name ?? 'Buyer' }}</strong><span class="pd-stars is-small" aria-label="{{ $review->rating }} out of 5 stars"><span style="width: {{ $review->rating * 20 }}%"></span></span><time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('M j, Y') }}</time></div>
                        @if($review->comment)<p>{{ $review->comment }}</p>@endif
                        @if($review->seller_reply)<blockquote><strong>Seller reply</strong> {{ $review->seller_reply }}</blockquote>@endif
                    </div>
                </article>
            @empty
                <p class="pd-empty">No reviews yet. Ratings from buyers will appear here.</p>
            @endforelse
            {{ $reviews->links() }}
        </div>

        <div class="pd-tabpanel" id="comments" role="tabpanel" aria-labelledby="tab-comments">
            @if($canComment['post'])
                <form class="pd-comment-form" action="{{ route('products.comments.store', $product->id) }}" method="POST">@csrf
                    <label class="pd-chat-sr" for="comment-body">Your comment</label>
                    <textarea id="comment-body" name="body" rows="1" maxlength="2000" required placeholder="Ask about fit, color, or details…" aria-describedby="comment-help">{{ old('body') }}</textarea>
                    <div><small id="comment-help">Comments are public. Keep contact and order details private.</small><button class="pd-button" type="submit">Post</button></div>
                </form>
            @elseif(!auth()->check())<p class="pd-empty"><a href="{{ route('login') }}">Log in</a> with a buyer account to leave a comment.</p>
            @elseif($canComment['isSeller'])<p class="pd-muted">You sell this product. Reply to buyers' questions below.</p>
            @else<p class="pd-empty">An active buyer account is needed to post a comment.</p>@endif
            @forelse($comments as $comment)
                @include('partials.product-comment', ['comment' => $comment, 'isReply' => false])
            @empty
                <p class="pd-empty">No comments yet. Start the conversation.</p>
            @endforelse
            {{ $comments->links() }}
        </div>
    </section>
    @if(auth()->check() && auth()->user()->isBuyer() && auth()->user()->status === 'active')
    <section class="pd-chat" id="pd-chat" role="dialog" aria-labelledby="pd-chat-title" data-avatar="{{ $product->seller->logoUrl() }}" hidden>
        <header class="pd-chat-head">
            <div><strong id="pd-chat-title">{{ $product->seller->name }}</strong><small>About: {{ $product->name }}</small></div>
            <a class="pd-chat-icon" id="pd-chat-expand" href="{{ route('buyer.messages') }}" aria-label="Open in Messages" title="Open in Messages">&#x2197;</a>
            <button class="pd-chat-icon pd-chat-minimize" type="button" id="pd-chat-minimize" aria-label="Minimize chat" title="Minimize">&minus;</button>
            <button class="pd-chat-icon" type="button" id="pd-chat-close" aria-label="Close chat" title="Close">&times;</button>
        </header>
        <div class="pd-chat-messages" id="pd-chat-messages" aria-live="polite"></div>
        <form class="pd-chat-compose" id="pd-chat-compose">
            <label class="pd-chat-sr" for="pd-chat-input">Message</label>
            <textarea id="pd-chat-input" name="message" rows="1" maxlength="5000" placeholder="Ask about this product…" required></textarea>
            <button type="submit" aria-label="Send message">@include('seller.partials.icon', ['name' => 'send', 'size' => 18])</button>
        </form>
        <p class="pd-chat-error" id="pd-chat-error" role="alert" hidden></p>
    </section>
    @endif
</div>
@endsection
@push('scripts')
<script>window.productOptions = {{ Illuminate\Support\Js::from($variants) }};</script>
<script src="{{ asset('js/product-details.js') }}?v={{ filemtime(public_path('js/product-details.js')) }}" defer></script>
<script src="{{ asset('js/product-chat.js') }}?v={{ filemtime(public_path('js/product-chat.js')) }}" defer></script>
@endpush
