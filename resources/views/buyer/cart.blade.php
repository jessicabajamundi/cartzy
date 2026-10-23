@extends('layouts.app')

@section('title', 'Shopping Cart | cartzy')

@section('content')
@include('buyer.partials.commerce-style')
<div class="commerce-cart min-h-[calc(100vh-220px)] py-8 sm:py-10 w-full">
    <div class="commerce-page w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Page Header --}}
        <div class="commerce-heading">
            <div>
                <p class="commerce-eyebrow">YOUR NEXT FINDS</p>
                <div class="flex flex-wrap items-center gap-3"><h1>Shopping Cart</h1><span class="commerce-count" id="cart-items-count-badge">{{ array_sum(array_column($cart, 'quantity')) }} items</span></div>
                <p>Review your picks and make them yours.</p>
            </div>
            @if(count($cart) > 0)
                <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your entire cart?');">
                    @csrf
                    <button type="submit" class="text-xs sm:text-sm font-semibold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer">
                        Clear All Items
                    </button>
                </form>
            @endif
        </div>

        {{-- Toast / Alerts --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-emerald-600">✓</span>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-base font-bold cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('info') || $errors->any())
            <p class="mb-4 text-sm text-rose-600" role="alert">{{ session('info') ?: $errors->first() }}</p>
        @endif
        @if(count($cart) > 0)
            <form id="cart-checkout-form" action="{{ route('checkout') }}" method="GET" data-selection-url="{{ route('cart.selection') }}">
                <input type="hidden" name="selection" value="1">
            </form>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Left: Cart Items List --}}
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs overflow-hidden">
                        {{-- Table Header --}}
                        <div class="grid grid-cols-12 gap-4 p-4 sm:p-5 bg-gray-50/80 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <div class="col-span-6 flex items-center gap-3">
                                <input type="checkbox" id="cart-select-all" checked aria-label="Select all items" style="width:18px;height:18px;accent-color:#6F6382;cursor:pointer;flex-shrink:0">
                                <label for="cart-select-all" class="cursor-pointer">Select all</label>
                            </div>
                            <div class="col-span-2 text-center">Unit Price</div>
                            <div class="col-span-2 text-center">Quantity</div>
                            <div class="col-span-2 text-right">Total</div>
                        </div>

                        {{-- Items list --}}
                        <div class="divide-y divide-gray-100">
                            @foreach($cart as $key => $item)
                                <div class="p-4 sm:p-5 grid grid-cols-12 gap-4 items-center hover:bg-gray-50/50 transition" id="cart-row-{{ $key }}">
                                    {{-- Product Info --}}
                                    <div class="col-span-6 flex items-center gap-3 sm:gap-4">
                                        <input type="checkbox" name="selected_items[]" value="{{ $key }}" form="cart-checkout-form" class="cart-item-checkbox" @checked($item['selected'] ?? true) data-price="{{ $item['price'] }}" data-quantity="{{ $item['quantity'] }}" aria-label="Select {{ $item['name'] }}, {{ $item['variation'] }}" style="width:18px;height:18px;accent-color:#6F6382;cursor:pointer;flex-shrink:0">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-2xl border border-gray-100 shrink-0 bg-gray-50">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug">
                                                {{ $item['name'] }}
                                            </h3>
                                            <p class="text-[11px] text-gray-400 mt-1">Variation: <span class="font-medium text-gray-600">{{ $item['variation'] }}</span></p>
                                            
                                            {{-- Remove Button --}}
                                            <form action="{{ route('cart.remove') }}" method="POST" class="cart-remove-form mt-2 inline-block">
                                                @csrf
                                                <input type="hidden" name="cart_key" value="{{ $key }}">
                                                <button type="submit" class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Remove</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Unit Price --}}
                                    <div class="col-span-2 text-center text-xs sm:text-sm font-semibold text-gray-700">
                                        @if(($item['original_price'] ?? 0) > $item['price'])
                                            <small class="block text-gray-500"><s>PHP {{ number_format($item['original_price'], 2) }}</s> &middot; Sale</small>
                                        @endif
                                        ₱{{ number_format($item['price'], 2) }}
                                    </div>

                                    {{-- Quantity Selector --}}
                                    <div class="col-span-2 flex items-center justify-center">
                                        <form action="{{ route('cart.update') }}" method="POST" class="cart-qty-form flex items-center border border-gray-300 rounded-xl overflow-hidden bg-white shadow-2xs">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $key }}">
                                            <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="cart-qty-btn cart-qty-minus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold text-sm cursor-pointer transition disabled:opacity-30 disabled:cursor-not-allowed" data-action="decrease" @disabled($item['quantity'] <= 1) aria-label="Decrease quantity">
                                                -
                                            </button>
                                            <span class="cart-qty-val w-8 text-center text-xs font-bold text-gray-900 select-none transition-opacity duration-150">
                                                {{ $item['quantity'] }}
                                            </span>
                                            <button type="submit" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}" class="cart-qty-btn cart-qty-plus w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold text-sm cursor-pointer transition disabled:opacity-30 disabled:cursor-not-allowed" data-action="increase" @disabled($item['quantity'] >= 99) aria-label="Increase quantity">
                                                +
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Row Total --}}
                                    <div class="cart-row-total col-span-2 text-right text-sm sm:text-base font-black text-gray-900">
                                        ₱{{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Continue Shopping Link --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="/" class="text-xs sm:text-sm font-bold text-gray-700 hover:text-black hover:underline flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Order Summary --}}
                <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-32">
                    <div class="cart-summary bg-white rounded-3xl p-6 border border-gray-200/90 shadow-2xs space-y-5">
                        <h2 class="text-lg font-extrabold text-gray-900 pb-3 border-b border-gray-100">
                            Order Summary
                            <span id="cart-selected-count" class="block text-xs font-medium text-gray-500 mt-1" aria-live="polite"></span>
                        </h2>

                        <div class="space-y-3 text-xs sm:text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span id="cart-selected-subtotal" class="font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                            </div>

                            <div class="flex justify-between text-gray-600">
                                <span>Shipping Fee</span>
                                <span id="cart-selected-shipping" class="font-bold text-gray-900">₱{{ number_format($shippingFee, 2) }}</span>
                            </div>

                                <div id="cart-discount-row" class="flex justify-between text-emerald-600 font-bold" style="display:{{ $discount > 0 ? 'flex' : 'none' }}">
                                    <span>Voucher Discount</span>
                                    <span id="cart-selected-discount">-₱{{ number_format($discount, 2) }}</span>
                                </div>

                            <div class="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                                <span class="text-sm font-bold text-gray-900">Total Amount</span>
                                <span id="cart-selected-total" class="text-xl sm:text-2xl font-black text-gray-900">₱{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" form="cart-checkout-form" id="cart-checkout-button" class="disabled:opacity-50 disabled:cursor-not-allowed w-full bg-[#6F6382] hover:bg-[#564B68] text-white py-3.5 px-6 rounded-2xl font-extrabold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md shadow-[#6F6382]/25 transition-all duration-200 cursor-pointer active:scale-98">
                                <span>Proceed to Checkout</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                            <p id="cart-selection-hint" class="text-xs text-gray-500 text-center mt-2" aria-live="polite"></p>
                            <p class="text-[11px] text-gray-400 text-center mt-2.5 inline-flex items-center justify-center gap-1 w-full">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Secure checkout with 100% Buyer Protection</span>
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        @else
            @include('buyer.partials.commerce-empty', [
                'emptyPath' => 'M16 9V7a4 4 0 0 0-8 0v2M5 9h14l1 12H4L5 9ZM9 13v1m6-1v1',
                'eyebrow' => 'A LITTLE ROOM FOR SOMETHING GOOD',
                'title' => 'Your cart is waiting for you',
                'text' => 'Discover your next favorite. Add the products you love, then find them all right here when you are ready.',
                'cta' => 'Explore products',
                'href' => route('home'),
                'note' => 'Already saved something you love? Find it in your wishlist.'
            ])
            <div class="mt-4 text-center"><a href="{{ route('buyer.dashboard', ['tab' => 'wishlist']) }}" class="text-xs font-bold text-[#8C5A50] hover:underline">View my wishlist &rarr;</a></div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cart-selection.js') }}?v={{ filemtime(public_path('js/cart-selection.js')) }}" defer></script>
@endpush
