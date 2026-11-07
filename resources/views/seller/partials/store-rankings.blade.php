{{-- Store leaderboard on the seller dashboard. Params: $rankings from StoreRankings::forSeller(). --}}
<section class="panel store-rankings" aria-labelledby="store-rankings-title">
    <div class="panel-header">
        <div>
            <h2 id="store-rankings-title">Store rankings</h2>
            <small>Ranked by items delivered in the last {{ \App\Services\StoreRankings::DAYS }} days</small>
        </div>
        @include('seller.partials.icon', ['name' => 'trending'])
    </div>
    <div class="panel-body">
        @if($rankings['top'])
            <ol class="store-ranking-list">
                @foreach($rankings['top'] as $store)
                    @include('seller.partials.store-ranking-row', ['store' => $store])
                @endforeach
            </ol>
        @else
            <p class="store-ranking-empty">No store has delivered an order in the last {{ \App\Services\StoreRankings::DAYS }} days yet. Your store could be the first!</p>
        @endif
        @if($rankings['mine'])
            <p class="store-ranking-divider">Your store</p>
            <ul class="store-ranking-list">@include('seller.partials.store-ranking-row', ['store' => $rankings['mine']])</ul>
        @endif
    </div>
</section>
