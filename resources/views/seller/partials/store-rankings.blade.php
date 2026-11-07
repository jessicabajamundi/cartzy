{{-- Store and product leaderboard on the seller dashboard. Params: $rankings from StoreRankings::forSeller(). --}}
@php
    $myStore = $rankings['mine'] ?? collect($rankings['top'])->firstWhere('is_mine', true);
    $myRank = $myStore['rank'] ?? null;
@endphp
<section class="panel store-rankings" aria-labelledby="store-rankings-title">
    <div class="panel-header">
        <div>
            <h2 id="store-rankings-title">Your ranking on Cartzy</h2>
            <small>Last {{ \App\Services\StoreRankings::DAYS }} days, by items delivered across all shops</small>
        </div>
    </div>
    <div class="store-rankings-body">
        <div class="store-rankings-box">
            <div class="store-rankings-standing">
                @include('seller.partials.icon', ['name' => 'trophy', 'size' => 34])
                <div>
                    @if($myRank === 1)
                        <strong>Best shop on Cartzy</strong>
                        <small>Your shop has the most items delivered of all {{ number_format($rankings['total']) }} {{ Str::plural('shop', $rankings['total']) }}.</small>
                    @elseif($myRank)
                        <strong>#{{ number_format($myRank) }} on Cartzy</strong>
                        <small>Your shop is ranked {{ number_format($myRank) }} of {{ number_format($rankings['total']) }} {{ Str::plural('shop', $rankings['total']) }}.</small>
                    @else
                        <strong>Not ranked yet</strong>
                        <small>Deliver an order to join the ranking.</small>
                    @endif
                </div>
            </div>
            @if($rankings['top'])
                <ol class="store-ranking-list">
                    @foreach($rankings['top'] as $store)
                        @include('seller.partials.store-ranking-row', ['store' => $store])
                    @endforeach
                </ol>
            @else
                <p class="store-ranking-empty">No shop has delivered an order in the last {{ \App\Services\StoreRankings::DAYS }} days yet.</p>
            @endif
            @if($rankings['mine'])
                <p class="store-ranking-divider">Your store</p>
                <ul class="store-ranking-list">@include('seller.partials.store-ranking-row', ['store' => $rankings['mine']])</ul>
            @endif
        </div>
        <div class="store-rankings-products">
            <h3>Best-selling products</h3>
            @forelse($rankings['products'] as $product)
                <div class="product-ranking-row">
                    <div>
                        <a href="{{ $product['url'] }}">{{ $product['name'] }}</a>
                        <small>{{ number_format($product['sold']) }} sold</small>
                    </div>
                    <span class="product-ranking-pill {{ $product['rank'] === 1 ? 'is-best' : '' }}">{{ $product['rank'] === 1 ? 'Best seller on Cartzy' : '#'.number_format($product['rank']).' on Cartzy' }}</span>
                </div>
            @empty
                <p class="store-ranking-empty">Your best sellers will show here once your orders are delivered.</p>
            @endforelse
        </div>
    </div>
</section>
