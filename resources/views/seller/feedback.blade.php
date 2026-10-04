@extends('layouts.seller')

@section('title', 'Customer Feedback & Reviews | Seller Centre')
@section('page_title', 'Customer Reviews & Feedback')

@section('content')
<div class="space-y-6">

    <!-- Store Rating Summary Hero -->
    <div class="bg-gradient-to-r from-amber-950 via-slate-900 to-[#2D2438] rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-wrap items-center justify-between gap-6 border border-amber-900/40">
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 bg-white/10 rounded-3xl flex flex-col items-center justify-center border border-white/20 shrink-0">
                <span class="text-4xl font-black text-amber-400">4.9</span>
                <span class="text-xs text-amber-300 font-bold">★★★★★</span>
                <span class="text-[9px] text-slate-300 mt-0.5">out of 5.0</span>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold font-heading">Shop Reputation & Customer Satisfaction</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-lg leading-relaxed">
                    Maintain top-tier ratings by answering customer questions promptly and posting polite seller responses to reviews.
                </p>
                <div class="flex items-center gap-4 text-xs font-bold text-amber-300 mt-3">
                    <span>99.2% Positive Feedback</span>
                    <span>•</span>
                    <span>142 Total Verified Reviews</span>
                </div>
            </div>
        </div>

        <div class="bg-white/10 p-4 rounded-2xl border border-white/10 text-xs space-y-1.5 min-w-[200px]">
            <div class="flex justify-between items-center text-slate-300">
                <span>5 Stars:</span>
                <span class="font-bold text-white">{{ $ratingCounts['5'] }} reviews (85%)</span>
            </div>
            <div class="flex justify-between items-center text-slate-300">
                <span>4 Stars:</span>
                <span class="font-bold text-white">{{ $ratingCounts['4'] }} reviews (12%)</span>
            </div>
            <div class="flex justify-between items-center text-slate-300">
                <span>3 Stars:</span>
                <span class="font-bold text-white">{{ $ratingCounts['3'] }} reviews (3%)</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>1-2 Stars:</span>
                <span class="font-bold text-white">0 reviews (0%)</span>
            </div>
        </div>
    </div>

    <!-- Rating Filters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs font-bold">
        <a href="{{ route('seller.feedback', ['rating' => 'all']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $ratingFilter === 'all' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            All Reviews
        </a>
        <a href="{{ route('seller.feedback', ['rating' => '5']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter == '5' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span>5 Stars ({{ $ratingCounts['5'] }})</span>
        </a>
        <a href="{{ route('seller.feedback', ['rating' => '4']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter == '4' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span>4 Stars ({{ $ratingCounts['4'] }})</span>
        </a>
        <a href="{{ route('seller.feedback', ['rating' => '3']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter == '3' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span>3 Stars ({{ $ratingCounts['3'] }})</span>
        </a>
    </div>

    <!-- Customer Reviews List -->
    <div class="space-y-4">
        @forelse($filteredReviews as $review)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 hover:border-[#6F6382] transition">
                
                <!-- Reviewer Header -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                            @if(!empty($review['buyer_avatar']) && (str_starts_with($review['buyer_avatar'], 'http') || str_starts_with($review['buyer_avatar'], '/')))
                                <img src="{{ $review['buyer_avatar'] }}" alt="" class="w-full h-full rounded-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $review['buyer_name'] }}</h3>
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200">
                                    ✓ Verified Purchase
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Reviewed {{ $review['date'] }} • Item: <strong class="text-slate-700">{{ $review['product_name'] }}</strong> ({{ $review['product_variant'] }})
                            </div>
                        </div>
                    </div>

                    <!-- Stars -->
                    <div class="flex items-center gap-1 text-amber-400 text-lg">
                        @for($i = 0; $i < $review['rating']; $i++)
                            <span>★</span>
                        @endfor
                        @for($i = $review['rating']; $i < 5; $i++)
                            <span class="text-slate-300">★</span>
                        @endfor
                    </div>
                </div>

                <!-- Customer Comment -->
                <div class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    "{{ $review['comment'] }}"
                </div>

                <!-- Seller Response Thread -->
                @if(!empty($review['seller_reply']))
                    <div class="ml-6 pl-4 border-l-2 border-[#6F6382] bg-purple-50/50 p-3.5 rounded-r-2xl space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-[#564B68]">Seller Response:</span>
                            <span class="text-[10px] text-slate-400">{{ $review['replied_at'] ?? 'Recently' }}</span>
                        </div>
                        <p class="text-slate-700 leading-relaxed">{{ $review['seller_reply'] }}</p>
                    </div>
                @else
                    <!-- Reply Box for Seller -->
                    <form action="{{ route('seller.feedback.reply', $review['id']) }}" method="POST" class="ml-6 pl-4 border-l-2 border-slate-200 space-y-2 m-0">
                        @csrf
                        <div class="flex items-center gap-2">
                            <input type="text" name="reply" required placeholder="Type your public reply to this customer..." class="flex-1 bg-white border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                            <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition">
                                Reply to Review
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-amber-400 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">No Reviews in this Star Category</h3>
                <p class="text-xs text-slate-500 mt-1">There are currently no reviews with {{ $ratingFilter }} stars.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
