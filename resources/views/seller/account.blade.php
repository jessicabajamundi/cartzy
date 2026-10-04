@extends('layouts.seller')

@section('title', 'Account Management | Seller Centre')
@section('page_title', 'Seller Account & Store Settings')

@section('content')
<div class="space-y-6">

    <!-- Store Profile & KYC Banner -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-3xl bg-[#6F6382] text-white flex items-center justify-center shadow-md shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-slate-900 font-heading">{{ $storeProfile['store_name'] }}</h1>
                    <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                        ✓ Verified Merchant
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Authorized Merchant Account • Member since January 2026</p>
                <div class="text-[11px] text-slate-400 mt-1">Slug: <span class="font-mono">cartzy.ph/shop/{{ $storeProfile['slug'] }}</span></div>
            </div>
        </div>

        <!-- Wallet Payout Quick Box -->
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs min-w-[220px]">
            <div class="text-slate-500 font-bold uppercase text-[10px]">Seller Wallet Balance</div>
            <div class="text-2xl font-black text-slate-900 mt-1">₱{{ number_format($payoutInfo['available_balance'], 2) }}</div>
            <button type="button" onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="mt-2.5 w-full bg-[#6F6382] hover:bg-[#564B68] text-white font-bold py-2 rounded-xl transition text-center text-xs">
                Withdraw Funds
            </button>
        </div>
    </div>

    <!-- 2 Columns: Store Info & Warehouse Address -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Column 1: Store Profile Settings -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#6F6382]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Store Profile & Operating Status</span>
                </h2>
                <p class="text-xs text-slate-500">Update your public storefront name, description, and vacation status</p>
            </div>

            <form action="{{ route('seller.account.profile') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Store Name *</label>
                    <input type="text" name="store_name" value="{{ $storeProfile['store_name'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Store URL Handle / Slug *</label>
                    <input type="text" name="slug" value="{{ $storeProfile['slug'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Contact Email *</label>
                        <input type="email" name="email" value="{{ $storeProfile['email'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mobile Phone *</label>
                        <input type="text" name="phone" value="{{ $storeProfile['phone'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Store Bio / Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">{{ $storeProfile['description'] }}</textarea>
                </div>

                <!-- Vacation / Holiday Mode Toggle -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div>
                        <div class="font-extrabold text-slate-900 text-xs">Holiday / Vacation Mode</div>
                        <div class="text-[11px] text-slate-500">Temporarily pause new incoming buyer orders while you are away</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="vacation_mode" value="1" {{ $storeProfile['vacation_mode'] ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6F6382]"></div>
                    </label>
                </div>

                <div class="pt-2 text-right">
                    <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-6 py-2.5 rounded-xl shadow-xs transition">
                        Save Store Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Column 2: Warehouse & Courier Pickup Address -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#6F6382]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Warehouse & Courier Pickup Address</span>
                </h2>
                <p class="text-xs text-slate-500">The address where courier fleet riders will arrive to collect scheduled parcels</p>
            </div>

            <form action="{{ route('seller.account.address') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Warehouse Contact Person *</label>
                        <input type="text" name="contact_person" value="{{ $pickupAddress['contact_person'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Contact Phone *</label>
                        <input type="text" name="phone" value="{{ $pickupAddress['phone'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unit / Building & Street Address *</label>
                    <input type="text" name="street" value="{{ $pickupAddress['street'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Barangay *</label>
                        <input type="text" name="barangay" value="{{ $pickupAddress['barangay'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">City / Municipality *</label>
                        <input type="text" name="city" value="{{ $pickupAddress['city'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Province *</label>
                        <input type="text" name="province" value="{{ $pickupAddress['province'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Postal Code *</label>
                        <input type="text" name="postal_code" value="{{ $pickupAddress['postal_code'] }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                </div>

                <div class="pt-2 text-right">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-xs transition">
                        Update Pickup Address
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Payout Information & Security -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Payout Bank / GCash Account -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#6F6382]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 10h20M6 14h2"/></svg>
                        <span>Bank & GCash Payout Destination</span>
                    </h2>
                    <p class="text-xs text-slate-500">Destination account for withdrawing your completed order revenues</p>
                </div>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    Auto-Withdrawal Ready
                </span>
            </div>

            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Payout Channel:</span>
                    <strong class="text-slate-900">{{ $payoutInfo['bank_name'] }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Account Name:</span>
                    <strong class="text-slate-900">{{ $payoutInfo['account_name'] }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Account / Mobile Number:</span>
                    <strong class="font-mono text-slate-900">{{ $payoutInfo['account_number'] }}</strong>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="w-full bg-[#6F6382] hover:bg-[#564B68] text-white text-xs font-bold py-2.5 rounded-xl shadow-xs transition">
                Request Payout Transfer
            </button>
        </div>

        <!-- Security: Change Password -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#6F6382]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Account Security</span>
                </h2>
                <p class="text-xs text-slate-500">Ensure your merchant credentials and payout authorizations remain secure</p>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Current Password</label>
                    <input type="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">New Password</label>
                        <input type="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Confirm Password</label>
                        <input type="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                    </div>
                </div>
                <div class="pt-2 text-right">
                    <button type="button" onclick="alert('Password updated successfully for merchant account.')" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2 rounded-xl transition">
                        Update Password
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900">Withdraw Seller Earnings</h3>
            <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('seller.account.withdraw') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-500 font-medium">Available for Withdrawal:</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">₱{{ number_format($payoutInfo['available_balance'], 2) }}</div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Transfer Destination</label>
                <div class="p-3 bg-slate-100 rounded-xl font-bold text-slate-800">
                    {{ $payoutInfo['bank_name'] }} • {{ $payoutInfo['account_number'] }}
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Withdrawal Amount (₱) *</label>
                <input 
                    type="number" 
                    step="0.01" 
                    name="amount" 
                    required 
                    max="{{ $payoutInfo['available_balance'] }}" 
                    value="10000" 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-base font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]"
                >
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">Cancel</button>
                <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition">Confirm Payout</button>
            </div>
        </form>
    </div>
</div>
@endsection
