@php
    $heroSlides = \App\Models\HeroSlide::where('is_active', true)
        ->orderBy('position')
        ->orderBy('id')
        ->get();
@endphp

@if ($heroSlides->isNotEmpty())

<section class="market-hero" data-market-hero role="region" aria-roledescription="carousel" aria-label="Featured collections">
    <h1 class="market-hero__sr-only">Discover your everyday favorites at Cartzy</h1>
    <div class="market-hero__slides" id="market-hero-slides" aria-live="off">
        @foreach ($heroSlides as $slide)
            @php
                $isHalloween = str_contains(strtolower($slide['image_path'] . ' ' . $slide['label']), 'halloween');
            @endphp
            <article class="market-hero__slide {{ $isHalloween ? 'market-hero__slide--halloween' : '' }} {{ $loop->first ? 'is-active' : '' }}"
                     data-hero-slide data-hero-label="{{ $slide['label'] }}" role="group" aria-roledescription="slide"
                     aria-label="{{ $loop->iteration }} of {{ count($heroSlides) }}: {{ $slide['label'] }}"
                     @if (!$loop->first) inert aria-hidden="true" @endif>
                <img class="market-hero__image" src="{{ asset($slide['image_path']) }}"
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
    <div class="market-hero__controls" data-hero-controls hidden>
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
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hero-carousel.css') }}?v={{ filemtime(public_path('css/hero-carousel.css')) }}">
@endpush
