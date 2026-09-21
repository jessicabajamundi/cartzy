@extends('layouts.app')

@section('title', 'Seller Centre | cartzy')

@section('content')
<div class="w-full max-w-[1920px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 py-8 space-y-6">

    <!-- Store Profile Header -->
    <div class="bg-black rounded-2xl p-6 text-white shadow-md flex flex-wrap items-center justify-between gap-4 border border-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white text-black rounded-2xl flex items-center justify-center text-3xl font-extrabold shadow-sm">
                🏬
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black">{{ $seller->name }} Store</h1>
                    <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">Verified Seller</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-300 mt-1">
                    <span>⭐ <strong>{{ $stats['rating'] }}</strong> / 5.0 Rating</span>
                    <span>•</span>
                    <span>📦 <strong>{{ $stats['total_products'] }}</strong> Active Products</span>
                    <span>•</span>
                    <span>⚡ Response Rate: <strong>98%</strong></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="#" class="bg-white text-black hover:bg-gray-100 text-xs font-bold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                <span>➕ Add New Product</span>
            </a>
        </div>
    </div>

    <!-- To-Do List -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs">
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">To-Do List</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            
            <a href="#" class="p-4 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 hover:border-black transition group">
                <div class="text-3xl font-black text-gray-900 group-hover:scale-110 transition-transform">{{ $stats['to_ship'] }}</div>
                <div class="text-xs font-bold text-gray-800 mt-1">To Ship (Pack Orders)</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Ready for courier pickup</div>
            </a>

            <a href="#" class="p-4 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 hover:border-black transition group">
                <div class="text-3xl font-black text-gray-900 group-hover:scale-110 transition-transform">{{ $stats['shipping'] }}</div>
                <div class="text-xs font-bold text-gray-800 mt-1">In Transit</div>
                <div class="text-[10px] text-gray-500 mt-0.5">With courier riders</div>
            </a>

            <a href="#" class="p-4 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 hover:border-black transition group">
                <div class="text-3xl font-black text-gray-900 group-hover:scale-110 transition-transform">{{ $stats['completed'] }}</div>
                <div class="text-xs font-bold text-gray-800 mt-1">Completed Orders</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Escrow released to wallet</div>
            </a>

            <a href="#" class="p-4 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 hover:border-black transition group">
                <div class="text-3xl font-black text-gray-700 group-hover:scale-110 transition-transform">0</div>
                <div class="text-xs font-bold text-gray-800 mt-1">Return / Refund</div>
                <div class="text-[10px] text-gray-500 mt-0.5">No disputes pending</div>
            </a>

        </div>
    </div>

    <!-- Store Financials & Quick Tools -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Seller Wallet Balance -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Seller Balance (My Income)</span>
                <div class="text-3xl font-black text-gray-900 mt-2">₱{{ number_format($stats['wallet_balance'], 2) }}</div>
                <p class="text-xs text-gray-600 font-semibold mt-1">Available for auto-withdrawal to bank</p>
            </div>
            <div class="pt-6">
                <button class="w-full bg-black hover:bg-gray-800 text-white text-xs font-bold py-3 rounded-lg shadow-sm transition">
                    Withdraw Funds
                </button>
            </div>
        </div>

        <!-- Marketing Tools -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs space-y-3">
            <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider">Marketing Centre</h3>
            <div class="space-y-2 text-xs">
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>🎟️ Create Shop Vouchers (Discounts / Rewards)</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>⚡ Nominate Items for Flash Deals</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>📦 Bundle Deals & Add-on Offers</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Order Operations -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs space-y-3">
            <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider">Shop Management</h3>
            <div class="space-y-2 text-xs">
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>📄 Mass Order Shipping & Airway Bill (AWB)</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>💬 Store Web Chat (Customer Inquiries)</span>
                    <span class="bg-black text-white text-[10px] font-bold px-2 py-0.5 rounded-full">2 Unread</span>
                </a>
                <a href="#" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-black transition font-medium text-gray-800">
                    <span>⚙️ Shop Customization & Banner Settings</span>
                    <span class="text-gray-400">&rarr;</span>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
