@extends('layouts.courier')

@section('title', 'Rider Account Management | Cartzy Courier')
@section('header_title', 'Account Management')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Profile Hero Card -->
    <div class="rider-card p-6 md:p-8 relative overflow-hidden bg-white border border-[#ECE7EF]">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
            <!-- Avatar with Active Indicator -->
            <div class="relative shrink-0">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl flex items-center justify-center font-heading text-2xl md:text-3xl font-bold text-white shadow-md" style="background: var(--gradient-master);">
                    {{ strtoupper(substr($courier->name, 0, 2)) }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white {{ ($rider->is_active ?? true) ? 'bg-emerald-500' : 'bg-gray-400' }}" title="{{ ($rider->is_active ?? true) ? 'Online' : 'Offline' }}">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                </span>
            </div>

            <!-- Basic Details -->
            <div class="flex-1 text-center md:text-left space-y-2.5">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">{{ $courier->name }}</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#F3EFF7] text-[#564B68] border border-[#E5DCEF] text-[10px] font-black uppercase tracking-wider">
                        {{ strtoupper(str_replace('_', ' ', $rider->courier_type ?? 'courier')) }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-black uppercase">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Fully Verified Courier
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold">
                        ★ {{ number_format($rider->rating ?? 4.95, 2) }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 flex flex-wrap items-center justify-center md:justify-start gap-4">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#564B68]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $courier->email }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#564B68]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $courier->phone ?? '0917-888-0000' }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#564B68]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $provider->name ?? 'Metro South Sorting & Fulfillment Hub' }}
                    </span>
                </div>

                <!-- Quick Working Status Toggle -->
                <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <form method="POST" action="{{ route('courier.account.toggle-status') }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-2 cursor-pointer {{ ($rider->is_active ?? true) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                            <span class="w-2 h-2 rounded-full {{ ($rider->is_active ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            <span>{{ ($rider->is_active ?? true) ? 'Status: ONLINE (Receiving Orders)' : 'Status: OFFLINE (Shift Paused)' }}</span>
                            <span class="text-[10px] text-gray-400 underline ml-1">Toggle</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column Details: Vehicle & Account Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Vehicle & License Credentials Card -->
        <div class="rider-card p-6 space-y-4 bg-white border border-[#ECE7EF]">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#ECE7EF]">
                <div class="w-9 h-9 rounded-xl bg-[#F3EFF7] text-[#564B68] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#1E1B2E]">Vehicle &amp; License Credentials</h3>
                    <p class="text-[11px] text-gray-500">Official transport registration on file</p>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500 font-medium">Vehicle Type:</span>
                    <span class="font-bold text-gray-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#564B68]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ $rider->vehicle_type ?? 'Motorcycle' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500 font-medium">Plate Number:</span>
                    <span class="font-mono font-bold text-xs text-[#564B68] bg-white px-2.5 py-1 rounded-md border border-[#ECE7EF] tracking-wider">
                        {{ $rider->plate_no ?? 'NQ-4812' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500 font-medium">Driver's License No:</span>
                    <span class="font-mono font-bold text-gray-900">
                        {{ $rider->license_no ?? 'D02-18-984712' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500 font-medium">License Expiration:</span>
                    <span class="font-semibold text-emerald-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $rider->license_expiry ? \Carbon\Carbon::parse($rider->license_expiry)->format('F d, Y') : 'Valid until 2028' }} (Active)
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500 font-medium">Service Coverage:</span>
                    <span class="font-semibold text-gray-800">
                        {{ $rider->area ?? 'Metro South (Laguna & NCR)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Vehicle Form -->
        <div class="rider-card p-6 space-y-4 bg-white border border-[#ECE7EF]">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#ECE7EF]">
                <div class="w-9 h-9 rounded-xl bg-[#FDF2F0] text-[#8C5A50] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#1E1B2E]">Update Information</h3>
                    <p class="text-[11px] text-gray-500">Modify phone number and vehicle details</p>
                </div>
            </div>

            <form method="POST" action="{{ route('courier.account.profile') }}" class="space-y-3.5">
                @csrf

                <!-- Contact Phone -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Mobile Contact Number</label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ old('phone', $courier->phone) }}" 
                        required 
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white transition"
                    >
                </div>

                <!-- Vehicle Type -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Vehicle Classification</label>
                    <select name="vehicle_type" required class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white transition">
                        @foreach(\App\Models\Rider::vehicleTypesFor($rider->courier_type) as $type)
                            <option value="{{ $type }}" {{ strcasecmp(str_replace('-', '', $rider->vehicle_type ?? ''), str_replace('-', '', $type)) === 0 ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plate Number -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Plate Number</label>
                    <input 
                        type="text" 
                        name="plate_no" 
                        value="{{ old('plate_no', $rider->plate_no) }}" 
                        placeholder="e.g. NQ-4812"
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white font-mono uppercase transition"
                    >
                </div>

                <!-- Assigned Area -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Preferred Delivery Area</label>
                    <input 
                        type="text" 
                        name="area" 
                        value="{{ old('area', $rider->area) }}" 
                        placeholder="e.g. Metro South (Laguna & NCR)"
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white transition"
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition shadow-xs cursor-pointer" style="background: var(--gradient-master);">
                        Save Account Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Logout & Security Section -->
    <div class="rider-card p-6 border border-rose-200 bg-rose-50/20 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-sm text-gray-900">Sign Out of Courier Rider Portal</h4>
            <p class="text-xs text-gray-500">Always sign out when finishing your shift or sharing devices.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-5 py-2 rounded-xl bg-white border border-rose-200 text-rose-700 font-bold text-xs hover:bg-rose-50 transition shadow-2xs flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Logout Session</span>
            </button>
        </form>
    </div>

</div>
@endsection
