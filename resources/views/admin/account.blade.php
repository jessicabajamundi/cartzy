@extends('layouts.admin')

@section('title', 'Admin Account Management | cartzy')
@section('page_title', 'Admin Profile & Security Settings')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Profile Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-2xl shadow-md">
                    AD
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">{{ $adminUser->name }}</h2>
                    <div class="text-xs text-slate-500">{{ $adminUser->email }}</div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 mt-1 inline-block">
                        Root Super Administrator
                    </span>
                </div>
            </div>
            <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-xl border border-emerald-200">
                ✓ Full Privileges Active
            </span>
        </div>

        <form action="{{ route('admin.account.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Administrator Full Name:</label>
                    <input type="text" name="name" value="{{ $adminUser->name }}" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Admin Official Email:</label>
                    <input type="email" name="email" value="{{ $adminUser->email }}" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Security Phone Number:</label>
                    <input type="text" name="phone" value="{{ $adminUser->phone ?? '+63 917 123 4567' }}" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Permission Tier:</label>
                    <input type="text" readonly value="Super Administrator (Tier 1 Full Control)" class="w-full p-3 bg-slate-100 text-slate-600 border border-slate-200 rounded-xl">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Security & Password Change Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
                <span>🔐</span> Security & Password
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Ensure your Super Admin credentials remain secure with strong cryptographic password protection.</p>
        </div>

        <form action="{{ route('admin.account.update') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Current Password:</label>
                    <input type="password" placeholder="••••••••" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">New Password:</label>
                    <input type="password" placeholder="Min. 8 characters" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Confirm New Password:</label>
                    <input type="password" placeholder="••••••••" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Admin Activity Audit Log -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
        <div>
            <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
                <span>📋</span> Recent Admin Audit Log
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Immutable record of high-privilege administrative actions.</p>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            <div class="py-3 flex items-center justify-between">
                <div>
                    <strong class="text-slate-900">Approved KYC Application</strong>
                    <p class="text-slate-500 text-[11px]">Merchant GlowBeauty Shop approved for selling in Health & Beauty.</p>
                </div>
                <span class="text-[11px] text-slate-400">Feb 23, 2026 09:40 AM</span>
            </div>
            <div class="py-3 flex items-center justify-between">
                <div>
                    <strong class="text-slate-900">Dispute Ruling Issued</strong>
                    <p class="text-slate-500 text-[11px]">Issued ₱1,250 refund to Buyer on Dispute #DISP-303.</p>
                </div>
                <span class="text-[11px] text-slate-400">Feb 20, 2026 04:15 PM</span>
            </div>
            <div class="py-3 flex items-center justify-between">
                <div>
                    <strong class="text-slate-900">Platform Policy Updated</strong>
                    <p class="text-slate-500 text-[11px]">Adjusted return dispute window to 7 calendar days.</p>
                </div>
                <span class="text-[11px] text-slate-400">Feb 18, 2026 02:00 PM</span>
            </div>
        </div>
    </div>

</div>
@endsection
