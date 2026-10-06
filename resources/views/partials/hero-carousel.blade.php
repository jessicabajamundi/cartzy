@php
    $heroSlides = [
        [
            'key' => 'home',
            'label' => 'Home & living',
            'title' => 'Good finds.',
            'accent' => 'Great everyday living.',
            'description' => 'Make room for the things you love. Discover home essentials, fresh styles, and everyday favorites, all on Cartzy.',
            'cta' => 'Explore the marketplace',
            'href' => '#daily-discover-section',
            'alt' => 'An oak lounge chair with a soft ivory cushion beside a walnut table and ceramic vase',
        ],
        [
            'key' => 'tech',
            'label' => 'Tech & audio',
            'title' => 'Tune into more.',
            'accent' => 'Make it your moment.',
            'description' => 'From your morning playlist to your evening wind-down, find tech that fits the way you live.',
            'cta' => 'Discover tech & audio',
            'href' => route('category.show', ['slug' => 'electronics']),
            'alt' => 'Charcoal wireless headphones and lavender earbuds on a pale stone display',
        ],
        [
            'key' => 'style',
            'label' => 'Everyday style',
            'title' => 'Go your own way.',
            'accent' => 'Find your everyday fit.',
            'description' => 'Easy layers, fresh kicks, and finishing touches. Meet the pieces that make every day feel a little more you.',
            'cta' => 'Find your next pair',
            'href' => '#shoes',
            'alt' => 'Cream sneakers, a folded beige shirt, and a muted lavender crossbody bag',
        ],
        [
            'key' => 'beauty',
            'label' => 'Beauty & self-care',
            'title' => 'A little me-time.',
            'accent' => 'A softer kind of glow.',
            'description' => 'Slow down and make space for yourself. Discover beauty essentials for your everyday rituals.',
            'cta' => 'Explore beauty',
            'alt' => 'Blush skincare bottles with rose-gold details and a lavender cosmetic pouch',
            'href' => route('category.show', ['slug' => 'beauty-health']),
        ],
        [
            'key' => 'jewelry',
            'label' => 'Jewelry & accessories',
            'title' => 'Little details.',
            'accent' => 'Your kind of sparkle.',
            'description' => 'The finishing touches that feel like you. Find jewelry and accessories for everyday moments.',
            'cta' => 'Discover accessories',
            'alt' => 'Rose-gold hoops, a delicate necklace, and a watch on an ivory and blush display',
            'href' => route('category.show', ['slug' => 'jewelry-accessories']),
        ],
        [
            'key' => 'coffee',
            'label' => 'Coffee & home',
            'title' => 'Stay a little longer.',
            'accent' => 'Savor the everyday.',
            'description' => 'From your first cup to your favorite corner, discover thoughtful upgrades for a home you love.',
            'cta' => 'Shop home appliances',
            'alt' => 'An ivory espresso machine with rose-gold details beside a ceramic cappuccino cup',
            'href' => route('category.show', ['slug' => 'appliances']),
        ],
        [
            'key' => 'travel',
            'label' => 'Bags & travel',
            'title' => 'Pack a little joy.',
            'accent' => 'Go somewhere new.',
            'description' => 'Weekend escapes or everyday adventures. Find bags and travel essentials ready to go with you.',
            'cta' => 'Explore bags & travel',
            'alt' => 'A blush cabin suitcase, taupe weekender bag, and ivory travel organizer',
            'href' => route('category.show', ['slug' => 'bags-luggage']),
        ],
        [
            'key' => 'fitness',
            'label' => 'Movement & wellness',
            'title' => 'Move at your pace.',
            'accent' => 'Feel a little better.',
            'description' => 'A morning stretch, a fresh start, a moment for you. Find essentials for the way you like to move.',
            'cta' => 'Find your fitness essentials',
            'alt' => 'A lavender yoga mat, blush dumbbells, and an ivory water bottle with a rose-gold cap',
            'href' => route('category.show', ['slug' => 'sports-outdoors']),
        ],
    ];
@endphp

<section class="market-hero" data-market-hero role="region" aria-roledescription="carousel" aria-label="Featured collections">
    <h1 class="market-hero__sr-only">Discover your everyday favorites at Cartzy</h1>
    <div class="market-hero__slides" id="market-hero-slides" aria-live="off">
        @foreach ($heroSlides as $slide)
            <article class="market-hero__slide {{ $loop->first ? 'is-active' : '' }}"
                     data-hero-slide data-hero-label="{{ $slide['label'] }}" role="group" aria-roledescription="slide"
                     aria-label="{{ $loop->iteration }} of {{ count($heroSlides) }}: {{ $slide['label'] }}"
                     @if (!$loop->first) inert aria-hidden="true" @endif>
                <img class="market-hero__image" src="{{ asset('images/banners/cartzy-' . $slide['key'] . '.jpg') }}"
                     width="2172" height="724" alt="{{ $slide['alt'] }}" draggable="false"
                     @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                <div class="market-hero__copy">
                    <p class="market-hero__eyebrow">{{ $slide['label'] }} <span class="market-hero__edit">CARTZY</span></p>
                    <h2 class="market-hero__title">{{ $slide['title'] }}<span>{{ $slide['accent'] }}</span></h2>
                    <p class="market-hero__description">{{ $slide['description'] }}</p>
                    <div class="market-hero__actions">
                        <a class="market-hero__cta" href="{{ $slide['href'] }}">{{ $slide['cta'] }}<span aria-hidden="true">&nearr;</span></a>
                        <a class="market-hero__secondary" href="#flash-sale">Explore today's deals <span aria-hidden="true">&rarr;</span></a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="market-hero__footer" data-hero-controls hidden>
        <p class="market-hero__caption"><span class="market-hero__caption-mark" aria-hidden="true"></span><span data-hero-current-label>{{ $heroSlides[0]['label'] }}</span></p>
        <div class="market-hero__navigation">
            <span class="market-hero__count" data-hero-count aria-hidden="true">01 / {{ str_pad(count($heroSlides), 2, '0', STR_PAD_LEFT) }}</span>
            <button class="market-hero__arrow" type="button" data-hero-prev aria-label="Previous collection" aria-controls="market-hero-slides">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m14 6-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="market-hero__dots" role="group" aria-label="Choose a collection">
                @foreach ($heroSlides as $slide)
                    <button class="market-hero__dot" type="button" data-hero-dot="{{ $loop->index }}"
                            aria-label="Show {{ $slide['label'] }}" aria-controls="market-hero-slides"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"><span></span></button>
                @endforeach
            </div>
            <button class="market-hero__arrow" type="button" data-hero-next aria-label="Next collection" aria-controls="market-hero-slides">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m10 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
    <p class="market-hero__sr-only" data-hero-status role="status" aria-live="polite" aria-atomic="true"></p>
</section>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hero-carousel.css') }}">
@endpush
