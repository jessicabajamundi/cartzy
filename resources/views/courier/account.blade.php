@extends('layouts.courier')

@section('title', 'Rider Account Management | Cartzy Courier')
@section('header_title', 'Account Management')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Hero Card -->
    <div class="rider-card p-6 md:p-8 relative overflow-hidden" style="background: linear-gradient(135deg, #FAF8FB 0%, #FFFFFF 100%);">
        <!-- Background decorative watermark -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 opacity-5 pointer-events-none text-[#564B68]">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
            </svg>
        </div>

        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
            <!-- Avatar -->
            <div class="relative">
                <div class="w-24 h-24 rounded-3xl flex items-center justify-center font-heading text-3xl font-bold text-white shadow-md" style="background: var(--gradient-master);">
                    {{ strtoupper(substr($courier->name, 0, 2)) }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white {{ $rider->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}" title="{{ $rider->is_active ? 'Online' : 'Offline' }}">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                </span>
            </div>

            <!-- Basic Details -->
            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <h2 class="font-heading text-2xl font-bold text-gray-900">{{ $courier->name }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-black uppercase">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Fully Verified Courier
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                        ★ {{ number_format($rider->rating ?? 4.95, 2) }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $courier->email }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $courier->phone ?? '0917-888-0000' }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $provider->name ?? 'Cartzy Express Logistics Hub' }}
                    </span>
                </p>

                <!-- Quick Working Status Toggle -->
                <div class="pt-3 flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <form method="POST" action="{{ route('courier.account.toggle-status') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $rider->is_active ? 'bg-emerald-600 text-white shadow-xs hover:bg-emerald-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            <span class="w-2.5 h-2.5 rounded-full {{ $rider->is_active ? 'bg-white animate-ping' : 'bg-gray-500' }}"></span>
                            <span>{{ $rider->is_active ? 'Status: ONLINE (Receiving Orders)' : 'Status: OFFLINE (Shift Paused)' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column Details: Vehicle & Account Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Vehicle & License Credentials Card -->
        <div class="rider-card p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#ECE7EF]">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#8C5A50] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#564B68]">Vehicle & License Credentials</h3>
                    <p class="text-[11px] text-gray-500">Official transport registration on file</p>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500">Vehicle Type:</span>
                    <span class="font-bold text-gray-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#8C5A50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ $rider->vehicle_type ?? 'Motorcycle' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500">Plate Number:</span>
                    <span class="font-mono font-bold text-sm text-[#564B68] bg-white px-2.5 py-1 rounded-md border border-[#ECE7EF]">
                        {{ $rider->plate_no ?? 'NQ-4812' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500">Driver's License No:</span>
                    <span class="font-mono font-bold text-gray-900">
                        {{ $rider->license_no ?? 'D02-18-984712' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500">License Expiration:</span>
                    <span class="font-semibold text-emerald-700">
                        {{ $rider->license_expiry ? \Carbon\Carbon::parse($rider->license_expiry)->format('F d, Y') : 'Valid until 2028' }} (Active)
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF9FC] border border-[#ECE7EF]">
                    <span class="text-gray-500">Service Coverage:</span>
                    <span class="font-semibold text-gray-800">
                        {{ $rider->area ?? 'Metro South (Laguna & NCR)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Vehicle Form -->
        <div class="rider-card p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-[#ECE7EF]">
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-[#564B68] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#564B68]">Update Information</h3>
                    <p class="text-[11px] text-gray-500">Modify phone number and vehicle details</p>
                </div>
            </div>

            <form method="POST" action="{{ route('courier.account.profile') }}" class="space-y-4">
                @csrf

                <!-- Contact Phone -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Contact Number</label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ old('phone', $courier->phone) }}" 
                        required 
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white"
                    >
                </div>

                <!-- Vehicle Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Vehicle Classification</label>
                    <select name="vehicle_type" required class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white">
                        @foreach(['Motorcycle', 'Bicycle', 'E-bike', 'Tricycle', 'Car', 'Van', 'Truck'] as $type)
                            <option value="{{ $type }}" {{ ($rider->vehicle_type ?? 'Motorcycle') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plate Number -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Plate Number</label>
                    <input 
                        type="text" 
                        name="plate_no" 
                        value="{{ old('plate_no', $rider->plate_no) }}" 
                        placeholder="e.g. NQ-4812"
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white font-mono uppercase"
                    >
                </div>

                <!-- Assigned Area -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Preferred Delivery Area</label>
                    <input 
                        type="text" 
                        name="area" 
                        value="{{ old('area', $rider->area) }}" 
                        placeholder="e.g. Metro South (Laguna & NCR)"
                        class="w-full px-3.5 py-2 text-xs bg-[#FAF9FC] border border-[#ECE7EF] rounded-xl focus:outline-hidden focus:border-[#564B68] focus:bg-white"
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rider-btn-primary py-2.5 rounded-xl text-xs font-bold text-white shadow-xs">
                        Save Account Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Logout & Security Section -->
    <div class="rider-card p-6 border-rose-200 bg-rose-50/30 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-sm text-gray-900">Sign Out of Courier Rider Portal</h4>
            <p class="text-xs text-gray-500">Always sign out when finishing your shift or sharing devices.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-700 font-bold text-xs hover:bg-rose-50 transition shadow-2xs flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Logout Session</span>
            </button>
        </form>
    </div>

</div>
@endsection
