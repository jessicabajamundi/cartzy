<!-- Trust & Value Proposition Banner -->
<div class="bg-white border-t border-b border-gray-200 py-12 mt-20 w-full">
    <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-left">
            
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-900 flex items-center justify-center shrink-0 border border-gray-200 text-2xl">
                    🛡️
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900">100% Authentic Guarantee</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Guaranteed authentic items or double refund</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-900 flex items-center justify-center shrink-0 border border-gray-200 text-2xl">
                    🔄
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900">7-Day Free Returns</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Hassle-free return & fast refund process</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-900 flex items-center justify-center shrink-0 border border-gray-200 text-2xl">
                    🚚
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900">Free Shipping Nationwide</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Enjoy free delivery vouchers with ₱0 min spend</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-900 flex items-center justify-center shrink-0 border border-gray-200 text-2xl">
                    🪙
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900">Cashback & Rewards</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Earn reward points on every completed order</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Main Footer Directory -->
<footer class="bg-white text-gray-600 text-sm pt-14 pb-10 border-t border-gray-200 w-full">
    <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10 pb-12 border-b border-gray-200">

            <!-- Column 1: Cartzy Logo -->
            <div class="col-span-2 md:col-span-1">
                <a href="/" class="flex items-center gap-2 mb-6">
                    <img src="{{ asset('images/logo-transparent.png') }}?v={{ filemtime(public_path('images/logo-transparent.png')) }}" alt="cartzy" class="h-16 w-auto object-contain">
                </a>
                <p class="text-gray-500 text-xs leading-relaxed">
                    Shop the best electronics, fashion, and home essentials. Your trusted online marketplace in the Philippines.
                </p>
            </div>

            <!-- Column 2: SHOP -->
            <div>
                <h5 class="font-bold text-gray-900 uppercase tracking-[0.15em] mb-6 text-base">SHOP</h5>
                <ul class="space-y-4 text-[15px]">
                    <li><a href="#" class="text-gray-600 hover:text-black transition">All products</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-black transition">Electronics</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-black transition">Home & Living</a></li>
                </ul>
            </div>

            <!-- Column 3: DISCOVER -->
            <div>
                <h5 class="font-bold text-gray-900 uppercase tracking-[0.15em] mb-6 text-base">DISCOVER</h5>
                <ul class="space-y-4 text-[15px]">
                    <li><a href="#" class="text-gray-600 hover:text-black transition">New arrivals</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-black transition">Fashion</a></li>
                </ul>
            </div>

            <!-- Column 3: ACCOUNT -->
            <div>
                <h5 class="font-bold text-gray-900 uppercase tracking-[0.15em] mb-6 text-base">ACCOUNT</h5>
                <ul class="space-y-4 text-[15px]">
                    <li><a href="{{ route('login') }}" class="text-gray-600 hover:text-black transition">Log in</a></li>
                    <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-black transition">Create account</a></li>
                </ul>
            </div>

            <!-- Column 4: PARTNERS -->
            <div>
                <h5 class="font-bold text-gray-900 uppercase tracking-[0.15em] mb-6 text-base">PARTNERS</h5>
                <ul class="space-y-4 text-[15px]">
                    <li><a href="{{ route('register', ['role' => 'seller']) }}" class="text-gray-600 hover:text-black transition">Sell on cartzy</a></li>
                    <li><a href="{{ route('register', ['role' => 'courier']) }}" class="text-gray-600 hover:text-black transition">Become a courier</a></li>
                </ul>
            </div>

        </div>

        <div class="pt-6 flex flex-col md:flex-row items-center justify-between text-gray-500 gap-4 text-xs">
            <div>
                © 2026 cartzy. All Rights Reserved.
            </div>
        </div>

    </div>
</footer>
