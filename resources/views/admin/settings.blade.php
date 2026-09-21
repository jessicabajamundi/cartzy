@extends('layouts.admin')

@section('title', 'Platform Settings & Policies | cartzy')
@section('page_title', 'Manage Platform Settings')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Post Platform Announcement Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <span>📢</span> Post Platform Announcement / Banner
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Broadcast important operational updates, flash campaigns, or rider notices to users.</p>
            </div>
            <span class="text-xs bg-indigo-50 text-indigo-700 font-bold px-3 py-1 rounded-xl">Live Platform Feed</span>
        </div>

        <form action="{{ route('admin.settings.announcement') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Announcement Headline:</label>
                    <input type="text" name="title" required placeholder="e.g. ⚡ System Maintenance Schedule or Payday Flash Sale" class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Audience:</label>
                    <select name="target" class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600 bg-white">
                        <option value="all">🌐 All Users (Buyers, Sellers, Couriers)</option>
                        <option value="sellers">🏬 Merchants & Sellers Only</option>
                        <option value="couriers">🛵 Express Couriers / Riders Only</option>
                        <option value="buyers">🛍️ Buyers Only</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Announcement Body Content:</label>
                <textarea name="content" rows="3" required placeholder="Write detailed notice to be displayed on top notice headers..." class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                    <span>🚀 Publish Live Announcement</span>
                </button>
            </div>
        </form>

        <!-- Current Published Announcements -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="text-[11px] font-black uppercase text-slate-400 tracking-wider">Active Published Platform Announcements:</div>
            <div class="space-y-2">
                @foreach($announcements as $ann)
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900">{{ $ann['title'] }}</span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                                Target: {{ $ann['target'] }}
                            </span>
                            <span class="text-[10px] text-slate-400">&bull; {{ $ann['created_at'] }}</span>
                        </div>
                        <p class="text-slate-600 text-[11px]">{{ $ann['content'] }}</p>
                    </div>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full">
                        ✓ Active on Platform
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Platform Policies & Commission Configuration -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-6">
        <div class="pb-3 border-b border-slate-100">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span>📜</span> Platform Policies, Rules & Commission Engine
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Configure operational thresholds, platform terms, and merchant compliance guidelines.</p>
        </div>

        <form action="{{ route('admin.settings.policies') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <!-- Commission Rate -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Platform Commission Rate (%):</label>
                    <div class="flex items-center gap-2 mt-2">
                        <input type="number" readonly value="10" class="w-20 text-sm font-black p-2.5 bg-slate-200 text-slate-900 rounded-lg border border-slate-300 text-center">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded">Fixed 10% Standard</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Platform fee automatically applied to each completed order.</p>
                </div>

                <!-- Auto Payout Days -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Auto Payout Holding (Days):</label>
                    <input type="number" name="auto_payout_days" value="{{ $policies['auto_payout_days'] }}" class="w-full text-xs font-bold p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-indigo-600 mt-2">
                    <p class="text-[10px] text-slate-400 mt-1">Number of days before order escrow funds disburse to merchant.</p>
                </div>

                <!-- Dispute Window -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Dispute Return Window (Days):</label>
                    <input type="number" name="max_dispute_window_days" value="{{ $policies['max_dispute_window_days'] }}" class="w-full text-xs font-bold p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-indigo-600 mt-2">
                    <p class="text-[10px] text-slate-400 mt-1">Allowed period for buyer to file complaint after delivery.</p>
                </div>

            </div>

            <!-- Text Policies -->
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Platform Terms of Service & Merchant Agreement:</label>
                    <textarea name="terms_of_service" rows="3" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">{{ $policies['terms_of_service'] }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Seller Category & Product Compliance Guidelines:</label>
                    <textarea name="seller_guidelines" rows="3" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">{{ $policies['seller_guidelines'] }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Customer Dispute & 3-Way Refund Policy:</label>
                    <textarea name="dispute_policy" rows="3" class="w-full p-3 border border-slate-300 rounded-xl focus:outline-none focus:border-indigo-600">{{ $policies['dispute_policy'] }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition">
                    Save Platform Policies
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
