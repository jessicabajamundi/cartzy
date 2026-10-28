@extends('layouts.seller')
@section('page_title', 'Manage sale')
@section('content')
<link rel="stylesheet" href="{{ asset('css/seller-product-editor.css') }}?v={{ filemtime(public_path('css/seller-product-editor.css')) }}">
<link rel="stylesheet" href="{{ asset('css/seller-sale.css') }}?v={{ filemtime(public_path('css/seller-sale.css')) }}">
<div class="product-editor sale-editor">
    <a class="editor-back" href="{{ route('seller.products') }}">&larr; Back to products</a>
    <div class="page-heading editor-heading">
        <div><div class="eyebrow">PRODUCT PROMOTIONS</div><h1>Set up sale</h1><p class="subtitle">Build your offer. Preview the price. Make it live.</p></div>
        <span class="badge {{ $product->sale_is_active ? 'active' : '' }}">{{ $product->sale_status }}</span>
    </div>
    @if($product->discount_type)
        <p class="notice">{{ $product->sale_status }} &middot; Starts: {{ $product->discount_starts_at ? $product->discount_starts_at->setTimezone('Asia/Manila')->format('M j, Y g:i A').' PHT' : 'Immediately' }} &middot; Ends: {{ $product->discount_ends_at ? $product->discount_ends_at->setTimezone('Asia/Manila')->format('M j, Y g:i A').' PHT' : 'No end date' }}</p>
    @endif
    <div class="editor-layout">
        <section class="editor-card">
            <div class="sale-product-heading">
                @if($product->coverImage)
                    <img class="sale-product-image" src="{{ str_starts_with($product->coverImage->path, 'http') || str_starts_with($product->coverImage->path, '/') ? $product->coverImage->path : Storage::disk('public')->url($product->coverImage->path) }}" alt="">
                @else
                    <span class="sale-product-icon">@include('seller.partials.icon', ['name' => 'tag', 'size' => 24])</span>
                @endif
                <div><span class="eyebrow">YOUR PRODUCT</span><h2>{{ $product->name }}</h2><p>{{ $product->variants->count() }} variation(s) &middot; Prices in pesos (₱)</p></div>
            </div>
            @if(!$product->is_active)<p class="notice">This product is archived. Its sale will apply when you restore the listing.</p>@endif
            @if(auth()->user()->status === 'active' && $shop?->isApproved())
            <form method="POST" action="{{ route('seller.products.sale.save', $product) }}">
                @csrf
                <input type="hidden" name="editing_product" value="{{ $product->id }}">
                @include('seller.partials.product-discount', ['discountProduct' => $product])
                <div class="editor-actions">
                    <span class="sale-save-note">Changes apply after saving.</span>
                    <div><a class="button secondary" href="{{ route('seller.products') }}">Cancel</a>
                    <button class="button" type="submit">Save sale settings</button></div>
                </div>
            </form>
            @else
                <p class="notice">An approved, active seller account is required to manage sales.</p>
            @endif
        </section>
        <aside class="sale-preview" aria-labelledby="sale-preview-title">
            <div class="sale-preview-header"><span class="eyebrow">BEFORE YOU SAVE</span><span class="sale-draft">Draft preview</span></div>
            <h2 id="sale-preview-title">Your offer at a glance</h2>
            <p class="sale-preview-caption">Prices below apply during the sale.</p>
            <div class="sale-preview-offer" data-offer-summary aria-live="polite">Choose a discount to preview your offer.</div>
            <div class="sale-preview-variants">
                @foreach($product->variants as $variant)
                    <div class="sale-preview-variant" data-price-minor="{{ $variant->price_minor }}">
                        <div><strong>{{ $variant->name }}</strong><small>{{ $variant->sku }}</small></div>
                        <div class="sale-preview-amounts"><span class="sale-regular-price">₱{{ number_format($variant->price, 2) }}</span><strong data-preview-price>₱{{ number_format($variant->price, 2) }}</strong><small data-preview-saving>Regular price</small></div>
                    </div>
                @endforeach
            </div>
            <dl class="sale-preview-schedule"><div><dt>Starts</dt><dd data-preview-start>Immediately</dd></div><div><dt>Ends</dt><dd data-preview-end>No end date</dd></div></dl>
            <p class="sale-preview-footnote">Preview only. Save to apply your changes. Shipping is excluded; existing orders keep their original prices.</p>
        </aside>
    </div>
    <section class="panel sale-prices">
        <div class="panel-header"><div><h2>Current saved prices</h2><small>Prices per item, before shipping</small></div></div>
        <div class="table-wrap"><table><thead><tr><th>Variation / SKU</th><th>Regular price</th><th>Current selling price</th></tr></thead><tbody>
        @foreach($product->variants as $variant)
            <tr><td>{{ $variant->name }}<small>{{ $variant->sku }}</small></td><td>₱{{ number_format($variant->price, 2) }}</td><td><strong>₱{{ number_format($variant->sale_price_minor / 100, 2) }}</strong></td></tr>
        @endforeach
        </tbody></table></div>
    </section>
</div>
@endsection
