@extends('layouts.app')

@section('title', 'Checkout | cartzy')

@section('content')
<div class="bg-gray-100 min-h-[calc(100vh-140px)] py-6 sm:py-8 lg:py-10">
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-20 mx-auto max-w-7xl">

        {{-- Top Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
            <a href="{{ route('home') }}" class="hover:text-black">Home</a>
            <span>›</span>
            <span class="text-gray-900 font-bold">Secure Checkout</span>
        </div>

        <form action="{{ route('checkout.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            {{-- LEFT COLUMN: Delivery & Payment Details --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- 1. Delivery Address --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h3 class="text-base font-extrabold text-gray-900">Delivery Address</h3>
                        </div>
                        <a href="{{ route('buyer.dashboard', ['tab' => 'addresses']) }}" class="text-xs font-bold text-gray-600 hover:text-black underline">
                            Change Address
                        </a>
                    </div>

                    <div class="flex items-start gap-3 text-sm">
                        <div class="space-y-1">
                            <div class="font-black text-gray-900 flex items-center gap-2">
                                <span>{{ $deliveryAddress?->recipient ?? $user->name }}</span>
                                <span class="text-xs font-semibold text-gray-500">({{ $deliveryAddress?->phone ?? $user->phone ?? 'No phone specified' }})</span>
                                @if(!empty($checkoutAddress))
                                    <span class="text-[10px] font-bold bg-gray-100 text-gray-800 px-2 py-0.5 rounded uppercase">Default</span>
                                @endif
                            </div>
                            @if(!empty($checkoutAddress))
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    {{ $checkoutAddress }}
                                </p>
                            @else
                                <p class="text-xs text-rose-600 font-semibold leading-relaxed flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>No delivery address set. Please click <strong>Change Address</strong> above to add your address.</span>
                                </p>
                            @endif
                        </div>
                    </div>
                    <input type="hidden" name="delivery_address" value="{{ $checkoutAddress ?? '' }}">
                </div>

                {{-- 2. Ordered Products --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <h3 class="text-base font-extrabold text-gray-900">Order Items ({{ count($cartItems) }})</h3>
                        </div>
                        <span class="text-xs text-gray-400 font-medium">Fulfilled by Verified Merchant</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($cartItems as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex items-center gap-4">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-gray-200 shrink-0">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1">{{ $item['name'] }}</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $item['variation'] }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">Qty: <strong>{{ $item['quantity'] }}</strong></span>
                                    <span class="text-sm font-black text-gray-900">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Payment Method --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 10h20M6 14h2"/></svg>
                            <h3 class="text-base font-extrabold text-gray-900">Payment Method</h3>
                        </div>
                        <span class="text-xs text-emerald-600 font-bold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>100% Encrypted</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs font-bold" id="payment-methods-grid">
                        <label class="payment-method-card relative border-2 border-black rounded-2xl p-4 sm:p-5 flex flex-col justify-between gap-3 cursor-pointer bg-gray-50 transition-all shadow-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-900 inline-flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </span>
                                    <span>Cash on Delivery (COD)</span>
                                </span>
                                <input type="radio" name="payment_method" value="COD" checked class="accent-black w-4 h-4 cursor-pointer">
                            </div>
                            <span class="text-[11px] text-gray-500 font-normal leading-relaxed">Pay in cash upon doorstep delivery</span>
                        </label>

                        <label class="payment-method-card relative border-2 border-gray-200 hover:border-gray-400 rounded-2xl p-4 sm:p-5 flex flex-col justify-between gap-3 cursor-pointer bg-white transition-all">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-900 inline-flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-[#007DFE] flex items-center justify-center font-black text-sm shrink-0 border border-blue-100">
                                        G
                                    </span>
                                    <span>GCash</span>
                                </span>
                                <input type="radio" name="payment_method" value="GCash" class="accent-black w-4 h-4 cursor-pointer">
                            </div>
                            <span class="text-[11px] text-gray-500 font-normal leading-relaxed">Fast and secure mobile e-wallet payment</span>
                        </label>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Order Summary & Place Order Button --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs space-y-5 sticky top-24">
                    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100">
                        Payment Summary
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Merchandise Subtotal</span>
                            <span class="font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Shipping Subtotal</span>
                            <span class="font-bold text-gray-900">₱{{ number_format($shippingFee, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-emerald-700">
                            <span>Shipping Voucher Discount</span>
                            <span class="font-bold">-₱{{ number_format($discount, 2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-black text-gray-900 block">Total Payment</span>
                                <span class="text-[10px] text-gray-400">VAT &amp; fees included</span>
                            </div>
                            <span class="text-2xl font-black text-gray-900">
                                ₱{{ number_format($total, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Place Order Action Button styled with A8A0B2 brand gradient --}}
                    <button
                        type="submit"
                        class="w-full py-4 px-6 text-white font-bold text-sm rounded-full shadow-md transition-all active:scale-98 flex items-center justify-center gap-2 hover:opacity-95 cursor-pointer"
                        style="background: linear-gradient(135deg, #91879E 0%, #6F6382 50%, #564B68 100%); box-shadow: 0 4px 16px rgba(111, 99, 130, 0.35);"
                    >
                        <span>Place Order</span>
                        <span>&rarr;</span>
                    </button>

                    <div class="text-[11px] text-gray-400 text-center leading-relaxed">
                        By placing your order, you agree to cartzy's Terms of Service and Verified Buyer Policies.
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="payment_method"]');

        function updatePaymentCards() {
            radios.forEach((radio) => {
                const card = radio.closest('.payment-method-card');
                if (!card) return;
                if (radio.checked) {
                    card.classList.remove('border-gray-200', 'bg-white', 'hover:border-gray-400');
                    card.classList.add('border-black', 'bg-gray-50', 'shadow-xs');
                } else {
                    card.classList.remove('border-black', 'bg-gray-50', 'shadow-xs');
                    card.classList.add('border-gray-200', 'bg-white', 'hover:border-gray-400');
                }
            });
        }

        radios.forEach((radio) => {
            radio.addEventListener('change', updatePaymentCards);
        });

        updatePaymentCards();
    });
</script>
@endsection
