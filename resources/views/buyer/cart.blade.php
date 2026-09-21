@extends('layouts.app')

@section('title', 'Shopping Cart | Cartzy')

@section('content')
<div class="bg-gray-100/70 min-h-[calc(100vh-220px)] py-8 sm:py-10 w-full">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Page Header --}}
        <div class="flex items-center justify-between pb-6 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 bg-[#6F6382] rounded-full"></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Shopping Cart
                </h1>
                <span class="bg-[#6F6382] text-white text-xs font-bold px-2.5 py-0.5 rounded-full shadow-xs" id="cart-items-count-badge">
                    {{ array_sum(array_column($cart, 'quantity')) }} items
                </span>
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

        @if(count($cart) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Left: Cart Items List --}}
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs overflow-hidden">
                        {{-- Table Header --}}
                        <div class="grid grid-cols-12 gap-4 p-4 sm:p-5 bg-gray-50/80 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <div class="col-span-6">Product</div>
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
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-2xl border border-gray-100 shrink-0 bg-gray-50">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug">
                                                {{ $item['name'] }}
                                            </h3>
                                            <p class="text-[11px] text-gray-400 mt-1">Variation: <span class="font-medium text-gray-600">{{ $item['variation'] }}</span></p>
                                            
                                            {{-- Remove Button --}}
                                            <form action="{{ route('cart.remove') }}" method="POST" class="mt-2 inline-block">
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
                                        ₱{{ number_format($item['price'], 2) }}
                                    </div>

                                    {{-- Quantity Selector --}}
                                    <div class="col-span-2 flex items-center justify-center">
                                        <form action="{{ route('cart.update') }}" method="POST" class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-white shadow-2xs">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $key }}">
                                            <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold text-sm cursor-pointer transition">
                                                -
                                            </button>
                                            <span class="w-8 text-center text-xs font-bold text-gray-900 select-none">
                                                {{ $item['quantity'] }}
                                            </span>
                                            <button type="submit" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold text-sm cursor-pointer transition">
                                                +
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Row Total --}}
                                    <div class="col-span-2 text-right text-sm sm:text-base font-black text-gray-900">
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
                    <div class="bg-white rounded-3xl p-6 border border-gray-200/90 shadow-2xs space-y-5">
                        <h2 class="text-lg font-extrabold text-gray-900 pb-3 border-b border-gray-100">
                            Order Summary
                        </h2>

                        <div class="space-y-3 text-xs sm:text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                            </div>

                            <div class="flex justify-between text-gray-600">
                                <span>Shipping Fee</span>
                                <span class="font-bold text-gray-900">₱{{ number_format($shippingFee, 2) }}</span>
                            </div>

                            @if($discount > 0)
                                <div class="flex justify-between text-emerald-600 font-bold">
                                    <span>Voucher Discount</span>
                                    <span>-₱{{ number_format($discount, 2) }}</span>
                                </div>
                            @endif

                            <div class="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                                <span class="text-sm font-bold text-gray-900">Total Amount</span>
                                <span class="text-xl sm:text-2xl font-black text-gray-900">₱{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('checkout') }}" class="w-full bg-[#6F6382] hover:bg-[#564B68] text-white py-3.5 px-6 rounded-2xl font-extrabold text-sm sm:text-base flex items-center justify-center gap-2 shadow-md shadow-[#6F6382]/25 transition-all duration-200 cursor-pointer active:scale-98">
                                <span>Proceed to Checkout</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <p class="text-[11px] text-gray-400 text-center mt-2.5">
                                🔒 Secure checkout with 100% Buyer Protection
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        @else
            {{-- Empty Cart State --}}
            <div class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs p-12 text-center max-w-xl mx-auto my-8">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                    🛒
                </div>
                <h2 class="text-xl font-extrabold text-gray-900">Your Shopping Cart is Empty</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-2 max-w-sm mx-auto">
                    Looks like you haven't added any products to your cart yet. Explore our featured deals and popular categories!
                </p>
                <div class="mt-6">
                    <a href="/" class="inline-flex items-center gap-2 bg-black hover:bg-zinc-800 text-white font-bold text-sm px-8 py-3.5 rounded-2xl shadow-2xs transition">
                        <span>Start Shopping Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
