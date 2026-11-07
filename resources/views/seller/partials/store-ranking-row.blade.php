<li class="store-ranking-row {{ $store['is_mine'] ? 'is-mine' : '' }}" @if($store['is_mine']) aria-current="true" @endif>
    <span class="store-ranking-place {{ $store['rank'] && $store['rank'] <= 3 ? 'is-top-'.$store['rank'] : '' }}">{{ $store['rank'] ?? '—' }}</span>
    <span class="store-ranking-logo" aria-hidden="true">
        @if($store['logo'])<img src="{{ $store['logo'] }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($store['name'], 0, 1)) }}@endif
    </span>
    <span class="store-ranking-copy">
        <a href="{{ $store['url'] }}">{{ $store['name'] }}</a>
        @if($store['is_mine'])<em>You</em>@endif
        <small>{{ $store['rating'] ? '★ '.number_format($store['rating'], 1) : 'No ratings yet' }}</small>
    </span>
    <span class="store-ranking-sold"><strong>{{ number_format($store['sold']) }}</strong> <small>{{ Str::plural('item', $store['sold']) }}</small></span>
</li>
