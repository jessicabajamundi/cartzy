@extends('layouts.app')

@section('title', 'Checkout | Cartzy')

@section('content')
<div class="bg-gray-100 min-h-[calc(100vh-140px)] py-6 sm:py-8 lg:py-10">
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-20 mx-auto max-w-7xl">

        {{-- Top Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
            <a href="{{ route('home') }}" class="hover:text-black">Home</a>
            <span>›</span>
            <span class="text-gray-900 font-bold">Secure Checkout</span>
        </div>

        {{-- Verification Approved Trust Banner --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 sm:p-5 mb-6 flex items-center justify-between gap-4 shadow-2xs">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                    ✓
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm font-black text-emerald-950">ID-Verified Buyer Checkout</h2>
                        <span class="text-[10px] font-extrabold bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded-full uppercase tracking-wider">
                            Verified Account
                        </span>
                    </div>
                    <p class="text-xs text-emerald-700 mt-0.5">
                        Your identity has been verified via <strong>{{ $user->id_type ?? 'Government ID' }}</strong>. Your purchase is protected with Buyer Guarantee.
                    </p>
                </div>
            </div>
            <a href="{{ route('account.index', ['tab' => 'profile']) }}" class="hidden sm:inline-flex text-xs font-bold text-emerald-800 hover:text-emerald-950 underline shrink-0">
                Manage ID
            </a>
        </div>

        <form action="{{ route('checkout.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            {{-- LEFT COLUMN: Delivery & Payment Details --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- 1. Delivery Address --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📍</span>
                            <h3 class="text-base font-extrabold text-gray-900">Delivery Address</h3>
                        </div>
                        <a href="{{ route('account.index', ['tab' => 'addresses']) }}" class="text-xs font-bold text-gray-600 hover:text-black underline">
                            Change Address
                        </a>
                    </div>

                    <div class="flex items-start gap-3 text-sm">
                        <div class="space-y-1">
                            <div class="font-black text-gray-900 flex items-center gap-2">
                                <span>{{ $user->name }}</span>
                                <span class="text-xs font-semibold text-gray-500">({{ $user->phone ?? 'No phone specified' }})</span>
                                <span class="text-[10px] font-bold bg-gray-100 text-gray-800 px-2 py-0.5 rounded uppercase">Default</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                {{ $user->address ?? '1011, Purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011' }}
                            </p>
                        </div>
                    </div>
                    <input type="hidden" name="delivery_address" value="{{ $user->address ?? '1011, Purok 4, Brgy. Masapang, Victoria, Laguna' }}">
                </div>

                {{-- 2. Ordered Products --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📦</span>
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
                            <span class="text-lg">💳</span>
                            <h3 class="text-base font-extrabold text-gray-900">Payment Method</h3>
                        </div>
                        <span class="text-xs text-emerald-600 font-bold flex items-center gap-1">
                            <span>🔒</span> 100% Encrypted
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-bold">
                        <label class="border-2 border-black rounded-2xl p-4 flex flex-col justify-between gap-3 cursor-pointer bg-gray-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-900">💵 Cash on Delivery</span>
                                <input type="radio" name="payment_method" value="COD" checked class="accent-black">
                            </div>
                            <span class="text-[11px] text-gray-500 font-normal">Pay in cash upon doorstep delivery</span>
                        </label>

                        <label class="border-2 border-gray-200 hover:border-gray-400 rounded-2xl p-4 flex flex-col justify-between gap-3 cursor-pointer transition">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-900">📱 GCash / Maya</span>
                                <input type="radio" name="payment_method" value="E-Wallet" class="accent-black">
                            </div>
                            <span class="text-[11px] text-gray-500 font-normal">Instant digital wallet QR payment</span>
                        </label>

                        <label class="border-2 border-gray-200 hover:border-gray-400 rounded-2xl p-4 flex flex-col justify-between gap-3 cursor-pointer transition">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-900">💳 Card / Debit</span>
                                <input type="radio" name="payment_method" value="Card" class="accent-black">
                            </div>
                            <span class="text-[11px] text-gray-500 font-normal">Visa, Mastercard, JCB</span>
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
                        By placing your order, you agree to Cartzy's Terms of Service and Verified Buyer Policies.
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection
