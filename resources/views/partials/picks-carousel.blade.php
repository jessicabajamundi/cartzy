@php
    // Editorial selection reviewed 7 October 2026; category discovery, not live inventory.
    // Trend sources: https://jisulife.com/ and https://www.anker.com/collections/best-seller
    // New spotlights intentionally have no unverified prices, ratings or stock claims.
    $pickGroups = [
        ['key' => 'editors', 'label' => "Editor's Pick", 'note' => 'Details that make the difference', 'items' => [
            ['name' => 'Minimalist watches', 'category' => 'Watches & accessories', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=720&h=720&fit=crop&q=85', 'alt' => 'White minimalist watch on a grey background', 'href' => route('category.show', ['slug' => 'jewelry-accessories']), 'description' => 'Clean dials and everyday straps that go with almost anything.', 'cta' => 'Browse watches'],
            ['name' => 'Everyday running shoes', 'category' => 'Footwear', 'image' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=720&h=720&fit=crop&q=85', 'alt' => 'Black running shoe on a light background', 'href' => '#shoes', 'description' => 'Sport-inspired pairs for your daily rotation.', 'cta' => 'Browse sneakers'],
        ]],
        ['key' => 'staff', 'label' => 'Staff Pick', 'note' => 'Useful finds, worth a look', 'items' => [
            ['name' => 'Over-ear headphones', 'category' => 'Audio & electronics', 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=720&h=720&fit=crop&q=85', 'alt' => 'Black over-ear headphones on a yellow background', 'href' => route('category.show', ['slug' => 'electronics']), 'description' => 'For playlists, focused afternoons and the journey home.', 'cta' => 'Explore audio'],
            ['name' => 'Instant-print cameras', 'category' => 'Cameras & photography', 'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=720&h=720&fit=crop&q=85', 'alt' => 'Retro instant camera', 'href' => route('category.show', ['slug' => 'electronics']), 'description' => 'A little keepsake from weekends, catch-ups and celebrations.', 'cta' => 'Explore cameras'],
        ]],
        ['key' => 'trending', 'label' => 'Trending', 'note' => 'On the radar right now', 'items' => [
            ['name' => 'Pocket-sized cooling', 'category' => 'Spotlight · Jisulife Pro1 S', 'image' => 'https://jisulife.com/cdn/shop/files/dark_grey-handheld_fan-FHP01S.webp?v=1780996028&width=720', 'alt' => 'Dark grey Jisulife Pro1 S handheld fan', 'href' => route('category.show', ['slug' => 'appliances']), 'description' => 'Rechargeable handheld fans for commutes and days out.', 'cta' => 'Browse portable fans', 'contain' => true],
            ['name' => 'Magnetic power banks', 'category' => 'Spotlight · Anker MagGo', 'image' => 'https://cdn.shopify.com/s/files/1/0493/9834/9974/files/A1664H11_Richimage_US_TD01_V2.png?v=1766390722&width=720', 'alt' => 'Anker MagGo 10K Slim magnetic power bank', 'href' => route('category.show', ['slug' => 'cell-phones-accessories']), 'description' => 'Compact charging that earns a spot in your everyday bag.', 'cta' => 'Browse power banks', 'contain' => true],
        ]],
        ['key' => 'value', 'label' => 'Best Value', 'note' => 'More use out of the everyday', 'items' => [
            ['name' => 'One charger, more devices', 'category' => 'Spotlight · Anker Nano', 'image' => 'https://cdn.shopify.com/s/files/1/0493/9834/9974/files/Frame_2147226467.png?v=1763623150&width=720', 'alt' => 'Anker Nano 70W three-port charger', 'href' => route('category.show', ['slug' => 'cell-phones-accessories']), 'description' => 'Multi-port USB-C chargers to simplify your desk and travel kit.', 'cta' => 'Browse chargers', 'contain' => true],
            ['name' => 'Skincare starter sets', 'category' => 'Beauty & self-care', 'image' => 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=720&h=720&fit=crop&q=85', 'alt' => 'Beauty and skincare products', 'href' => route('category.show', ['slug' => 'beauty-health']), 'description' => 'Explore everyday beauty essentials in a single set.', 'cta' => 'Browse beauty sets'],
        ]],
    ];
@endphp

<section id="flash-sale" class="picks" data-picks-board aria-labelledby="picks-title">
    <header class="picks__header">
        <div><h2 id="picks-title" class="picks__title">Today's picks<span>.</span></h2><p class="picks__intro">Four ways to find your next favourite.</p></div>
        <div class="picks__header-actions">
            <button class="picks__rotation" type="button" data-picks-pause hidden aria-label="Pause automatic product rotation"><span data-picks-pause-icon aria-hidden="true">Ⅱ</span><span data-picks-pause-label>Pause</span></button>
            <a class="picks__browse" href="#daily-discover-section">View all products <span aria-hidden="true">↗</span></a>
        </div>
    </header>
    <div class="picks__grid">
        @foreach ($pickGroups as $group)
            <section class="pick-lane pick-lane--{{ $group['key'] }}" data-pick-lane data-pick-interval="{{ 6500 + $loop->index * 1100 }}" role="region" aria-roledescription="carousel" aria-labelledby="pick-{{ $group['key'] }}-title">
                <header class="pick-lane__header"><h3 id="pick-{{ $group['key'] }}-title"><span aria-hidden="true"></span>{{ $group['label'] }}</h3><p>{{ $group['note'] }}</p></header>
                <div class="pick-lane__viewport">
                    <div class="pick-lane__track" id="pick-{{ $group['key'] }}-slides" data-pick-track aria-live="off">
                        @foreach ($group['items'] as $item)
                            <div class="pick-lane__slide" data-pick-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($group['items']) }}: {{ $item['name'] }}" @if (!$loop->first) inert aria-hidden="true" @endif>
                                <a class="pick-lane__product" href="{{ $item['href'] }}">
                                    <div class="pick-lane__media {{ !empty($item['contain']) ? 'pick-lane__media--contain' : '' }}"><img src="{{ $item['image'] }}" alt="{{ $item['alt'] }}" width="720" height="720" loading="lazy" decoding="async" draggable="false"></div>
                                    <div class="pick-lane__copy"><p class="pick-lane__category">{{ $item['category'] }}</p><h4>{{ $item['name'] }}</h4><p class="pick-lane__description">{{ $item['description'] }}</p><span class="pick-lane__link">{{ $item['cta'] }}<span aria-hidden="true">↗</span></span></div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="pick-lane__controls" data-pick-controls hidden>
                    <span class="pick-lane__count" data-pick-count aria-hidden="true">01 / {{ str_pad(count($group['items']), 2, '0', STR_PAD_LEFT) }}</span>
                    <div><button type="button" data-pick-prev aria-label="Previous {{ $group['label'] }} product" aria-controls="pick-{{ $group['key'] }}-slides"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg></button><button type="button" data-pick-next aria-label="Next {{ $group['label'] }} product" aria-controls="pick-{{ $group['key'] }}-slides"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg></button></div>
                </div>
                <p class="picks__sr-only" data-pick-status role="status" aria-live="polite" aria-atomic="true"></p>
            </section>
        @endforeach
    </div>
</section>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/picks-carousel.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/picks-carousel.js') }}" defer></script>
@endpush
