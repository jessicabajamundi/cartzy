@php
    $collections = [
        ['name' => "Women's Fashion", 'slug' => 'women-clothing', 'description' => 'Everyday looks, your way.', 'image' => 'photo-1490481651871-ab68de25d43d', 'alt' => 'A curated rail of clothing in soft neutral tones', 'tag' => 'Wear your mood'],
        ['name' => 'Home & Living', 'slug' => 'home-living', 'description' => 'Thoughtful finds for your space.', 'image' => 'photo-1616486338812-3dadae4b4ace', 'alt' => 'A warm, thoughtfully styled home interior', 'tag' => 'Make room for comfort'],
        ['name' => 'Tech & Gadgets', 'slug' => 'electronics', 'description' => 'Upgrade your everyday.', 'image' => 'photo-1496181133206-80ce9b88a853', 'alt' => 'A laptop on a clean wooden desk', 'tag' => 'Small upgrades, big possibilities'],
        ['name' => 'Beauty & Care', 'slug' => 'beauty-health', 'description' => 'Refresh your daily routine.', 'image' => 'photo-1596462502278-27bfdc403348', 'alt' => 'An assortment of beauty and makeup essentials', 'tag' => 'A little time for you'],
    ];
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop-categories.css') }}?v={{ filemtime(public_path('css/shop-categories.css')) }}">
@endpush
<section class="shop-collections" aria-labelledby="shop-collections-title">
    <div class="shop-collections__header">
        <div>
            <p class="shop-collections__eyebrow"><span aria-hidden="true"></span> Shop by category</p>
            <h2 id="shop-collections-title">Find your next <em>favorite.</em></h2>
            <p class="shop-collections__intro">Explore everyday essentials, style, and more.</p>
        </div>
        <button type="button" class="shop-collections__browse" id="browse-collections" aria-expanded="false" aria-controls="collection-directory">
            Browse all categories <span aria-hidden="true">↗</span>
        </button>
    </div>
    <div class="shop-collections__grid">
        @foreach($collections as $collection)
            <a class="collection-card" href="{{ route('home', ['category' => $collection['slug']]) }}" data-collection-slug="{{ $collection['slug'] }}" data-collection-name="{{ $collection['name'] }}">
                <div class="collection-card__image">
                    <img src="https://images.unsplash.com/{{ $collection['image'] }}?w=720&h=600&fit=crop&q=85" alt="{{ $collection['alt'] }}" width="720" height="600" loading="lazy" decoding="async">
                    <span class="collection-card__tag">{{ $collection['tag'] }}</span>
                </div>
                <div class="collection-card__body">
                    <div><h3>{{ $collection['name'] }}</h3><p>{{ $collection['description'] }}</p></div>
                    <span class="collection-card__arrow" aria-hidden="true">↗</span>
                </div>
            </a>
        @endforeach
    </div>
    <div id="collection-directory" class="shop-collections__directory" hidden>
        <h3>Explore all categories</h3>
        <div>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}" data-collection-slug="{{ $category->slug }}" data-collection-name="{{ $category->name }}">{{ $category->name }} <span aria-hidden="true">↗</span></a>
            @endforeach
        </div>
    </div>
</section>
@push('scripts')
<script>
document.getElementById('browse-collections').addEventListener('click', function () {
    const directory = document.getElementById('collection-directory');
    directory.hidden = !directory.hidden;
    this.setAttribute('aria-expanded', String(!directory.hidden));
});
document.querySelectorAll('[data-collection-slug]').forEach(link => {
    link.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || !window.filterByCategory) return;
        event.preventDefault();
        window.filterByCategory(link.dataset.collectionSlug, link.dataset.collectionName);
    });
});
</script>
@endpush
