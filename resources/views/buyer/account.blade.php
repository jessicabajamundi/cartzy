@extends('layouts.app')

@section('title', 'My Account | Cartzy')

@section('hide_footer', 'true')

@section('content')
{{-- Top Banner Bar (Balanced modern sizing) --}}
<div class="bg-[#18181b] text-white border-b border-zinc-800 w-full">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="flex items-center gap-4 sm:gap-5">
            <div class="relative w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center shrink-0">
                <svg class="w-9 h-9 sm:w-10 sm:h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span class="absolute -bottom-0.5 -right-0.5 bg-zinc-800 text-white rounded-full p-1 border border-zinc-700">
                    <svg class="w-3.5 h-3.5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Profile Settings</h1>
                <p class="text-xs sm:text-sm text-zinc-300 mt-1 font-medium">Keep your personal details, password, and delivery addresses up to date.</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-100/70 min-h-[calc(100vh-220px)] py-8 sm:py-10 w-full">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Toast Flash Notification --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm sm:text-base px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-emerald-600">✓</span>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-base font-bold cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 bg-amber-50 border border-amber-300 text-amber-900 text-sm sm:text-base px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="text-xl shrink-0">⚠️</span>
                    <div>
                        <div class="font-bold text-amber-950">Action Required</div>
                        <div class="text-xs sm:text-sm text-amber-800 mt-0.5">{{ session('warning') }}</div>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-900 text-base font-bold cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 text-sm sm:text-base px-5 py-3.5 rounded-2xl shadow-2xs">
                <div class="font-bold mb-1.5">Please fix the following issues:</div>
                <ul class="list-disc list-inside text-xs sm:text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            {{-- ========================================================================= --}}
            {{-- LEFT SIDEBAR NAVIGATION (Sticky, stays locked in view below the header) --}}
            {{-- ========================================================================= --}}
            <aside class="lg:col-span-4 xl:col-span-3 space-y-4 sticky top-[136px] self-start z-30">
                <div class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs overflow-hidden">
                    
                    {{-- User Profile Header --}}
                    <div class="p-5 sm:p-6 flex items-center gap-3.5 sm:gap-4">
                        <div class="relative group shrink-0">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full overflow-hidden bg-zinc-900 text-white flex items-center justify-center font-black text-lg sm:text-xl border border-gray-100 shadow-2xs">
                                @if(!empty($user->avatar) && file_exists(public_path('storage/' . $user->avatar)))
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <button type="button" onclick="document.getElementById('sidebarAvatarInput').click()" title="Change photo" class="absolute inset-0 rounded-full bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer text-sm">
                                📷
                            </button>
                            <form id="sidebarAvatarForm" action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="avatar" id="sidebarAvatarInput" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                            </form>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-extrabold text-base text-gray-900 leading-snug break-words">{{ $user->name ?? 'Buyer Account' }}</h3>
                            <p class="text-xs text-gray-500 truncate mt-0.5 font-medium">{{ $user->email ?? '' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    {{-- Navigation Menu (Smooth scroll to sequential sections on page) --}}
                    <div class="p-3 space-y-1" id="accountSidebarNav">
                        <a href="{{ $tab === 'purchases' ? route('account.index') . '#personal-details' : '#personal-details' }}" onclick="scrollToSection(event, 'personal-details')" class="account-nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition text-black bg-gray-100 font-extrabold" data-target="personal-details">
                            <svg class="w-5 h-5 shrink-0 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Personal details</span>
                        </a>

                        <a href="{{ $tab === 'purchases' ? route('account.index') . '#delivery-addresses' : '#delivery-addresses' }}" onclick="scrollToSection(event, 'delivery-addresses')" class="account-nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition text-gray-600 hover:text-black hover:bg-gray-50" data-target="delivery-addresses">
                            <svg class="w-5 h-5 shrink-0 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Delivery addresses</span>
                        </a>

                        <a href="{{ $tab === 'purchases' ? route('account.index') . '#password-settings' : '#password-settings' }}" onclick="scrollToSection(event, 'password-settings')" class="account-nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition text-gray-600 hover:text-black hover:bg-gray-50" data-target="password-settings">
                            <svg class="w-5 h-5 shrink-0 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Password</span>
                        </a>
                    </div>

                    {{-- Secondary link for Orders / Purchases --}}
                    <div class="border-t border-gray-100"></div>
                    <div class="p-3">
                        <a href="{{ route('account.index', ['tab' => 'purchases']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition {{ $tab === 'purchases' ? 'bg-gray-100 text-black font-extrabold' : 'text-gray-500 hover:text-black hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span>My Purchases</span>
                        </a>
                    </div>

                </div>
            </aside>

            {{-- ========================================================================= --}}
            {{-- MAIN CONTENT AREA --}}
            {{-- ========================================================================= --}}
            <main class="lg:col-span-8 xl:col-span-9 space-y-6 sm:space-y-8">

                {{-- VIEW: MY PURCHASES (When purchases tab is selected) --}}
                @if($tab === 'purchases')

                    <div class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs p-6 sm:p-8">
                        <div class="pb-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h1 class="text-xl sm:text-2xl font-black text-gray-900">My Purchases</h1>
                                <p class="text-xs sm:text-sm text-gray-500 mt-1">Track, manage and view status of all your store orders</p>
                            </div>
                        </div>

                        {{-- Purchases Sub-Tabs --}}
                        <div class="flex items-center gap-2 border-b border-gray-200 mt-4 overflow-x-auto text-xs font-bold text-gray-500">
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'all']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status', 'all') === 'all' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">All</a>
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'to_pay']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status') === 'to_pay' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">To Pay</a>
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'to_ship']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status') === 'to_ship' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">To Ship</a>
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'to_receive']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status') === 'to_receive' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">To Receive</a>
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'completed']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status') === 'completed' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">Completed</a>
                            <a href="{{ route('account.index', ['tab' => 'purchases', 'status' => 'cancelled']) }}" class="py-3 px-4 border-b-2 transition whitespace-nowrap {{ request('status') === 'cancelled' ? 'border-black text-black' : 'border-transparent hover:text-gray-900' }}">Cancelled</a>
                        </div>

                        {{-- Order Items Cards --}}
                        <div class="space-y-4 mt-6">
                            @forelse($orders as $order)
                                <div class="border border-gray-200 rounded-2xl p-5 hover:border-gray-300 transition bg-white shadow-2xs space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-gray-900">🏬 {{ $order['store_name'] }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ $order['store_badge'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-bold text-{{ $order['status_color'] }}-700 flex items-center gap-1">
                                                <span>●</span> {{ $order['status_label'] }}
                                            </span>
                                        </div>
                                    </div>

                                    @foreach($order['items'] as $item)
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $item['image'] }}" alt="Product" class="w-16 h-16 object-cover rounded-xl border border-gray-200 shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs font-bold text-gray-900 truncate">{{ $item['name'] }}</h4>
                                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $item['variation'] }}</p>
                                                <div class="text-[11px] text-gray-600 mt-1">Qty: {{ $item['quantity'] }}</div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-bold text-gray-900">₱{{ number_format($item['price'], 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                                        <div class="text-xs text-gray-500">
                                            Tracking: <span class="font-mono font-bold text-gray-900">{{ $order['tracking_no'] }}</span> ({{ $order['courier_name'] }})
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-xs">
                                                Order Total: <span class="text-sm font-black text-black">₱{{ number_format($order['total_amount'], 2) }}</span>
                                            </div>
                                            <button type="button" class="bg-black hover:bg-gray-800 text-white font-bold text-xs px-4 py-2 rounded-lg transition">
                                                Buy Again
                                            </button>
                                            <button type="button" class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold text-xs px-4 py-2 rounded-lg transition">
                                                Contact Seller
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-16 text-center text-gray-500 text-xs">
                                    No purchases or orders found.
                                </div>
                            @endforelse
                        </div>
                    </div>

                @else

                    {{-- ================================================================= --}}
                    {{-- 3 SEQUENTIAL SECTIONS (Personal details -> Addresses -> Password) --}}
                    {{-- ================================================================= --}}

                    {{-- 1. PERSONAL DETAILS SECTION --}}
                    <div id="personal-details" class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs p-6 sm:p-8 scroll-mt-24">
                        <div class="flex items-start justify-between gap-4 pb-5 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">ACCOUNT</p>
                                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Personal details</h2>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-bold shrink-0">
                                <span class="text-black font-black">✓</span> Verified Buyer
                            </div>
                        </div>

                        <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-6 pt-6">
                            @csrf

                            {{-- Row 1: Full name + Mobile number --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                <div>
                                    <label for="name" class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5">Full name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        value="{{ old('name', $user->name) }}"
                                        required
                                        class="w-full px-4 py-3 text-sm sm:text-base text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition bg-white shadow-2xs"
                                    >
                                </div>
                                <div>
                                    <label for="phone" class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5">Mobile number</label>
                                    <input
                                        type="tel"
                                        name="phone"
                                        id="phone"
                                        value="{{ old('phone', $user->phone) }}"
                                        placeholder="09XXXXXXXXX"
                                        maxlength="11"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        class="w-full px-4 py-3 text-sm sm:text-base text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition bg-white shadow-2xs"
                                    >
                                </div>
                            </div>

                            {{-- Row 2: Email address --}}
                            <div>
                                <label for="email" class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5">Email address</label>
                                <input
                                    type="email"
                                    id="email"
                                    value="{{ $user->email }}"
                                    readonly
                                    class="w-full px-4 py-3 text-sm sm:text-base text-gray-700 border border-gray-200 rounded-xl bg-gray-50/70 cursor-not-allowed focus:outline-none shadow-2xs"
                                >
                                <p class="text-xs text-gray-500 mt-1.5 font-medium">Managed by your connected Google account.</p>
                            </div>

                            {{-- Row 3: Birthday + Current age --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                <div>
                                    <label for="birthday" class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5">Birthday</label>
                                    <input
                                        type="date"
                                        name="birthday"
                                        id="birthday"
                                        value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}"
                                        max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                        onchange="updateAgeDisplay(this.value)"
                                        class="w-full px-4 py-3 text-sm sm:text-base text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition bg-white shadow-2xs"
                                    >
                                    <p class="text-xs text-gray-500 mt-1.5 font-medium">Your birthday is used to keep your age accurate.</p>
                                </div>
                                <div>
                                    <label for="ageDisplay" class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5">Current age</label>
                                    <input
                                        type="text"
                                        id="ageDisplay"
                                        readonly
                                        value="{{ !empty($user->age) ? $user->age . ' years old' : (!empty($user->birthday) ? \Carbon\Carbon::parse($user->birthday)->age . ' years old' : '') }}"
                                        placeholder="Calculated automatically"
                                        class="w-full px-4 py-3 text-sm sm:text-base text-gray-700 border border-gray-200 rounded-xl bg-gray-50/70 cursor-not-allowed focus:outline-none shadow-2xs"
                                    >
                                    <p class="text-xs text-gray-500 mt-1.5 font-medium">Calculated automatically.</p>
                                </div>
                            </div>

                            {{-- Save Profile Button --}}
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    class="bg-black hover:bg-zinc-800 text-white font-bold text-xs sm:text-sm px-8 py-3 rounded-xl uppercase tracking-wider shadow-sm transition active:scale-95 cursor-pointer"
                                >
                                    SAVE PROFILE
                                </button>
                            </div>
                        </form>
                    </div>


                    {{-- 2. BUYER ADDRESSES SECTION --}}
                    <div id="delivery-addresses" class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs p-6 sm:p-8 scroll-mt-24">
                        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">DELIVERY</p>
                                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Buyer addresses</h2>
                                <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">Save an address by type, then choose your default delivery location.</p>
                            </div>
                            <button type="button" onclick="openAddressModal()" class="bg-black hover:bg-zinc-800 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 uppercase tracking-wider shrink-0 shadow-sm active:scale-95 cursor-pointer">
                                <span class="text-base leading-none font-bold">+</span>
                                <span>ADD ADDRESS</span>
                            </button>
                        </div>

                        {{-- 4 Quick-Add Buttons --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 my-5">
                            <button type="button" onclick="openAddressModal('Home')" class="bg-[#fafafa] hover:bg-gray-100 border border-gray-200/90 rounded-xl py-3 px-3 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold text-gray-800 hover:text-black transition shadow-2xs cursor-pointer">
                                <svg class="w-4 h-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                <span>Add Home</span>
                            </button>

                            <button type="button" onclick="openAddressModal('Work')" class="bg-[#fafafa] hover:bg-gray-100 border border-gray-200/90 rounded-xl py-3 px-3 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold text-gray-800 hover:text-black transition shadow-2xs cursor-pointer">
                                <svg class="w-4 h-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                                </svg>
                                <span>Add Work</span>
                            </button>

                            <button type="button" onclick="openAddressModal('School')" class="bg-[#fafafa] hover:bg-gray-100 border border-gray-200/90 rounded-xl py-3 px-3 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold text-gray-800 hover:text-black transition shadow-2xs cursor-pointer">
                                <svg class="w-4 h-4 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.973 10.973 0 00-.25 2.366c0 1.76.627 3.407 1.707 4.707A8.966 8.966 0 0010 18a8.966 8.966 0 003.293-2.876A7.042 7.042 0 0015 10.417c0-.82-.09-1.616-.25-2.366l2.644-1.132a1 1 0 000-1.84l-7-3zM10 16a6.98 6.98 0 01-2.55-1.503A5.045 5.045 0 016 10.417c0-.528.055-1.042.158-1.536l3.842 1.647V16zm2-5.472l3.842-1.647c.103.494.158 1.008.158 1.536 0 1.554-.606 2.973-1.604 4.08A6.98 6.98 0 0112 16v-5.472z"/>
                                </svg>
                                <span>Add School</span>
                            </button>

                            <button type="button" onclick="openAddressModal('Other')" class="bg-[#fafafa] hover:bg-gray-100 border border-gray-200/90 rounded-xl py-3 px-3 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold text-gray-800 hover:text-black transition shadow-2xs cursor-pointer">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Add Other</span>
                            </button>
                        </div>

                        {{-- Saved Address Cards List --}}
                        <div class="space-y-4">
                            @foreach($addresses as $addr)
                                @php
                                    $addrType = $addr['type'] ?? 'Home';
                                @endphp
                                <div class="bg-white rounded-2xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs hover:border-gray-300 transition">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-4 sm:gap-5 min-w-0">
                                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-[#f4ece1] flex items-center justify-center text-gray-800 shrink-0 mt-0.5">
                                                @if($addrType === 'Work')
                                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-amber-900" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                                                    </svg>
                                                @elseif($addrType === 'School')
                                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-amber-900" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.973 10.973 0 00-.25 2.366c0 1.76.627 3.407 1.707 4.707A8.966 8.966 0 0010 18a8.966 8.966 0 003.293-2.876A7.042 7.042 0 0015 10.417c0-.82-.09-1.616-.25-2.366l2.644-1.132a1 1 0 000-1.84l-7-3zM10 16a6.98 6.98 0 01-2.55-1.503A5.045 5.045 0 016 10.417c0-.528.055-1.042.158-1.536l3.842 1.647V16zm2-5.472l3.842-1.647c.103.494.158 1.008.158 1.536 0 1.554-.606 2.973-1.604 4.08A6.98 6.98 0 0112 16v-5.472z"/>
                                                    </svg>
                                                @elseif($addrType === 'Other')
                                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-amber-900" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2.5 mb-1">
                                                    <h3 class="font-black text-base sm:text-lg text-gray-900">{{ $addrType }}</h3>
                                                    @if(!empty($addr['is_default']))
                                                        <span class="bg-gray-100 text-gray-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Default</span>
                                                    @endif
                                                </div>
                                                <p class="font-bold text-sm text-gray-900 mb-0.5">{{ $addr['recipient_name'] }}</p>
                                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">{{ $addr['full_address'] }}</p>
                                                <p class="text-xs text-gray-500 mt-1 font-medium">{{ $addr['phone'] }}</p>
                                            </div>
                                        </div>

                                        <div class="shrink-0">
                                            <button type="button" onclick="openAddressModal('{{ $addrType }}')" class="rounded-xl border border-gray-200 hover:border-gray-400 px-3.5 py-2 text-xs font-bold text-gray-800 hover:bg-gray-50 transition flex items-center gap-1.5 uppercase shadow-2xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                                <span>EDIT</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if(empty($addresses))
                                <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-2xl">
                                    <div class="text-4xl mb-2">📍</div>
                                    <p class="font-bold text-gray-800 text-sm sm:text-base">No saved addresses yet</p>
                                    <p class="text-xs text-gray-500 mt-0.5 mb-4">Add a delivery address for faster checkout</p>
                                    <button type="button" onclick="openAddressModal()" class="bg-black hover:bg-zinc-800 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition inline-flex items-center gap-1.5 uppercase tracking-wider">
                                        <span>+</span> Add Address
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Bottom Link: MANAGE ALL SAVED ADDRESSES → --}}
                        <div class="mt-6">
                            <button type="button" onclick="openAddressModal()" class="rounded-xl border border-gray-200 hover:border-black px-5 py-2.5 text-xs font-bold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2 uppercase tracking-wider shadow-2xs cursor-pointer">
                                <span>MANAGE ALL SAVED ADDRESSES</span>
                                <span class="text-sm leading-none">→</span>
                            </button>
                        </div>
                    </div>


                    {{-- 3. PASSWORD SECTION --}}
                    <div id="password-settings" class="bg-white rounded-3xl border border-gray-200/90 shadow-2xs p-6 sm:p-8 scroll-mt-24">
                        <div class="pb-5 border-b border-gray-100">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">SECURITY</p>
                            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Password</h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">For your security, please do not share your password with anyone else.</p>
                        </div>

                        <div class="pt-6">
                            <form action="{{ route('account.password.update') }}" method="POST" class="space-y-5">
                                @csrf

                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5" for="current_password">Current Password</label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        id="current_password"
                                        required
                                        placeholder="Enter your current password"
                                        class="w-full px-4 py-3 text-sm text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5" for="new_password">New Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="new_password"
                                        required
                                        placeholder="Min. 6 characters"
                                        class="w-full px-4 py-3 text-sm text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 mb-1.5" for="new_password_confirmation">Confirm New Password</label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        id="new_password_confirmation"
                                        required
                                        placeholder="Re-enter your new password"
                                        class="w-full px-4 py-3 text-sm text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                                    >
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="bg-black hover:bg-zinc-800 text-white font-bold text-xs sm:text-sm px-8 py-3 rounded-xl uppercase tracking-wider shadow-sm transition active:scale-95 cursor-pointer">
                                        Confirm Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                @endif

            </main>

        </div>

    </div>
</div>

{{-- EDIT / ADD ADDRESS MODAL --}}
<div id="addressModal" style="display: none;" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 space-y-5 shadow-2xl border border-gray-200">
        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
            <h3 id="modalAddressTitle" class="font-black text-lg text-gray-900 tracking-tight">Delivery Address</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-black text-xl font-bold cursor-pointer p-1">✕</button>
        </div>

        <form action="{{ route('account.address.update') }}" method="POST" class="space-y-4 text-xs sm:text-sm">
            @csrf

            <div>
                <label class="block font-bold text-gray-800 mb-1.5">Address Type</label>
                <div class="grid grid-cols-4 gap-2">
                    <label class="flex items-center justify-center py-2.5 px-2 rounded-xl border border-gray-200 cursor-pointer text-center hover:bg-gray-50 text-xs sm:text-sm font-bold has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white transition shadow-2xs">
                        <input type="radio" name="address_type" value="Home" id="type_Home" checked class="hidden">
                        <span>Home</span>
                    </label>
                    <label class="flex items-center justify-center py-2.5 px-2 rounded-xl border border-gray-200 cursor-pointer text-center hover:bg-gray-50 text-xs sm:text-sm font-bold has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white transition shadow-2xs">
                        <input type="radio" name="address_type" value="Work" id="type_Work" class="hidden">
                        <span>Work</span>
                    </label>
                    <label class="flex items-center justify-center py-2.5 px-2 rounded-xl border border-gray-200 cursor-pointer text-center hover:bg-gray-50 text-xs sm:text-sm font-bold has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white transition shadow-2xs">
                        <input type="radio" name="address_type" value="School" id="type_School" class="hidden">
                        <span>School</span>
                    </label>
                    <label class="flex items-center justify-center py-2.5 px-2 rounded-xl border border-gray-200 cursor-pointer text-center hover:bg-gray-50 text-xs sm:text-sm font-bold has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white transition shadow-2xs">
                        <input type="radio" name="address_type" value="Other" id="type_Other" class="hidden">
                        <span>Other</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 mb-1" for="modal_street">House/Unit No. &amp; Street <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="street_address"
                    id="modal_street"
                    value="{{ old('street_address', $user->street_address) }}"
                    required
                    placeholder="e.g. 1011, Purok 4"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                >
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 mb-1" for="modal_region">Region <span class="text-rose-500">*</span></label>
                    <select name="region" id="modal_region" class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs" required>
                        <option value="NCR" {{ $user->region == 'NCR' ? 'selected' : '' }}>NCR – Metro Manila</option>
                        <option value="CAR" {{ $user->region == 'CAR' ? 'selected' : '' }}>CAR – Cordillera</option>
                        <option value="I" {{ $user->region == 'I' ? 'selected' : '' }}>Region I – Ilocos</option>
                        <option value="II" {{ $user->region == 'II' ? 'selected' : '' }}>Region II – Cagayan</option>
                        <option value="III" {{ $user->region == 'III' ? 'selected' : '' }}>Region III – Central Luzon</option>
                        <option value="IV-A" {{ $user->region == 'IV-A' || empty($user->region) ? 'selected' : '' }}>Region IV-A – CALABARZON</option>
                        <option value="IV-B" {{ $user->region == 'IV-B' ? 'selected' : '' }}>Region IV-B – MIMAROPA</option>
                        <option value="V" {{ $user->region == 'V' ? 'selected' : '' }}>Region V – Bicol</option>
                        <option value="VI" {{ $user->region == 'VI' ? 'selected' : '' }}>Region VI – Western Visayas</option>
                        <option value="VII" {{ $user->region == 'VII' ? 'selected' : '' }}>Region VII – Central Visayas</option>
                        <option value="VIII" {{ $user->region == 'VIII' ? 'selected' : '' }}>Region VIII – Eastern Visayas</option>
                        <option value="IX" {{ $user->region == 'IX' ? 'selected' : '' }}>Region IX – Zamboanga</option>
                        <option value="X" {{ $user->region == 'X' ? 'selected' : '' }}>Region X – Northern Mindanao</option>
                        <option value="XI" {{ $user->region == 'XI' ? 'selected' : '' }}>Region XI – Davao</option>
                        <option value="XII" {{ $user->region == 'XII' ? 'selected' : '' }}>Region XII – SOCCSKSARGEN</option>
                        <option value="XIII" {{ $user->region == 'XIII' ? 'selected' : '' }}>Region XIII – CARAGA</option>
                        <option value="BARMM" {{ $user->region == 'BARMM' ? 'selected' : '' }}>BARMM – Bangsamoro</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-800 mb-1" for="modal_province">Province</label>
                    <input
                        type="text"
                        name="province"
                        id="modal_province"
                        value="{{ old('province', $user->province ?? 'Laguna') }}"
                        placeholder="Province"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 mb-1" for="modal_city">City / Municipality</label>
                    <input
                        type="text"
                        name="city"
                        id="modal_city"
                        value="{{ old('city', $user->city ?? 'Victoria') }}"
                        placeholder="City"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-800 mb-1" for="modal_barangay">Barangay</label>
                    <input
                        type="text"
                        name="barangay"
                        id="modal_barangay"
                        value="{{ old('barangay', $user->barangay ?? 'Masapang') }}"
                        placeholder="Barangay"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                    >
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 mb-1" for="modal_postal_code">Postal Code</label>
                <input
                    type="text"
                    name="postal_code"
                    id="modal_postal_code"
                    value="{{ old('postal_code', $user->postal_code ?? '4011') }}"
                    maxlength="4"
                    inputmode="numeric"
                    placeholder="e.g. 4011"
                    class="w-36 px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-2xs"
                >
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAddressModal()" class="px-5 py-2.5 font-bold text-gray-600 hover:bg-gray-100 rounded-xl text-xs sm:text-sm cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2.5 font-bold bg-black text-white rounded-xl hover:bg-zinc-800 text-xs sm:text-sm shadow-sm cursor-pointer">Save Address</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateAgeDisplay(dateStr) {
        if (!dateStr) return;
        const today = new Date();
        const bday = new Date(dateStr);
        let age = today.getFullYear() - bday.getFullYear();
        const m = today.getMonth() - bday.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
        
        const input = document.getElementById('ageDisplay');
        if (input) {
            input.value = (age >= 0 ? age : 0) + ' years old';
        }
    }

    function scrollToSection(event, sectionId) {
        if (event) event.preventDefault();
        const el = document.getElementById(sectionId);
        if (el) {
            const headerOffset = 136;
            const elementPosition = el.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
            history.replaceState(null, '', '#' + sectionId);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bdayInput = document.getElementById('birthday');
        if (bdayInput && bdayInput.value) {
            updateAgeDisplay(bdayInput.value);
        }

        // Handle auto-scroll based on URL tab query or hash
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        const hash = window.location.hash;

        if (hash === '#delivery-addresses' || tabParam === 'addresses') {
            setTimeout(() => scrollToSection(null, 'delivery-addresses'), 150);
        } else if (hash === '#password-settings' || tabParam === 'password') {
            setTimeout(() => scrollToSection(null, 'password-settings'), 150);
        } else if (hash === '#personal-details' || tabParam === 'profile') {
            setTimeout(() => scrollToSection(null, 'personal-details'), 150);
        }

        // Scroll spy to update active state of sidebar links
        const sections = ['personal-details', 'delivery-addresses', 'password-settings'];
        const navItems = document.querySelectorAll('.account-nav-item');

        window.addEventListener('scroll', function() {
            let current = '';
            sections.forEach(id => {
                const section = document.getElementById(id);
                if (section) {
                    const rect = section.getBoundingClientRect();
                    if (rect.top <= 220 && rect.bottom >= 120) {
                        current = id;
                    }
                }
            });

            if (current) {
                navItems.forEach(item => {
                    if (item.getAttribute('data-target') === current) {
                        item.classList.add('bg-gray-100', 'text-black', 'font-extrabold');
                        item.classList.remove('text-gray-600');
                    } else {
                        item.classList.remove('bg-gray-100', 'text-black', 'font-extrabold');
                        item.classList.add('text-gray-600');
                    }
                });
            }
        });
    });

    function openAddressModal(type = '') {
        const modal = document.getElementById('addressModal');
        if (modal) modal.style.display = 'flex';
        const title = document.getElementById('modalAddressTitle');
        if (type) {
            const radio = document.getElementById('type_' + type);
            if (radio) radio.checked = true;
            if (title) title.innerText = 'Add ' + type + ' Address';
        } else {
            if (title) title.innerText = 'Delivery Address';
        }
    }
    function closeAddressModal() {
        const modal = document.getElementById('addressModal');
        if (modal) modal.style.display = 'none';
    }
</script>
@endpush
@endsection
