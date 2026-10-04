@extends('layouts.logistics')

@section('title', 'Account Management | Logistics Hub | cartzy')
@section('page_title', 'Account Management')
@section('page_subtitle', 'Manage your logistics hub profile and credentials')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Profile Card --}}
    <div class="bg-gradient-to-br from-[#0A2E4A] to-[#1A6FA8] rounded-2xl text-white p-6 mb-6 shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -top-6 text-8xl opacity-10 select-none">🏭</div>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-4xl shrink-0">🏭</div>
            <div>
                <div class="text-xl font-black">{{ $hub->business_name ?? $hub->name }}</div>
                <div class="text-blue-200 text-sm">{{ $hub->email }}</div>
                <div class="text-blue-200 text-xs mt-0.5">{{ $hub->phone ?? 'No phone number' }}</div>
                <span class="mt-2 inline-block text-[10px] font-black bg-emerald-500 text-white px-2.5 py-0.5 rounded-full uppercase tracking-wide">Logistics Hub — Active</span>
            </div>
        </div>
    </div>

    {{-- Update Form --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-black text-slate-800">Update Hub Information</h3>
            <p class="text-sm text-slate-500 mt-0.5">Update your hub name, contact details, and credentials</p>
        </div>

        <form method="POST" action="{{ route('logistics.account.update') }}" class="p-6 space-y-5">
            @csrf

            {{-- Hub Name --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Hub / Facility Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $hub->name) }}" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            </div>

            {{-- Business Name --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Business Name</label>
                <input type="text" name="business_name" value="{{ old('business_name', $hub->business_name) }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
                       placeholder="e.g. cartzy Express Logistics Hub - Metro South Facility">
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Contact Number</label>
                <input type="tel" name="phone" value="{{ old('phone', $hub->phone) }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
                       placeholder="09XXXXXXXXX" maxlength="11">
            </div>

            {{-- Email (read-only) --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">E-mail (Login)</label>
                <input type="email" value="{{ $hub->email }}" readonly
                       class="w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
            </div>

            <hr class="border-slate-100">

            {{-- Password --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">New Password <span class="text-slate-400 font-normal normal-case">(leave blank to keep current)</span></label>
                <input type="password" name="password"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
                       placeholder="Enter new password">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#1A6FA8] hover:bg-[#0F4C75] text-white font-black py-3 rounded-xl transition shadow-md">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Logout Section --}}
    <div class="mt-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="flex items-center justify-between">
            <div>
                <div class="font-black text-slate-800">Sign Out</div>
                <div class="text-xs text-slate-500 mt-0.5">Securely log out from the Logistics Hub portal</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition">
                    🚪 Logout
                </button>
            </form>
        </div>
    </div>

</div>

@endsection
