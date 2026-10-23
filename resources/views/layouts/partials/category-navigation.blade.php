@php
    $navigationCategories = isset($allCategories) && $allCategories->count()
        ? $allCategories : (new \App\Http\Controllers\HomeController())->getCategoriesWithMeta();
    $navigationMeta = (new \App\Http\Controllers\HomeController())->getCategoryMeta();
    $navigationDepartments = ['fashion' => 'Fashion', 'tech' => 'Electronics', 'home' => 'Home & Living', 'beauty' => 'Beauty', 'lifestyle' => 'Lifestyle'];
    $navigationGroups = $navigationCategories->groupBy(fn ($category) => $category->meta['department'] ?? $navigationMeta[$category->slug]['department'] ?? 'lifestyle');
@endphp
<nav class="shop-navigation" aria-label="Shop navigation" id="category-mega-menu-wrapper">
    <div class="shop-navigation-bar">
        <button type="button" id="all-categories-btn" class="shop-all-button" onclick="toggleCategoryMenu()" aria-expanded="false" aria-controls="category-mega-dropdown">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>All categories</span><span id="cat-chevron" aria-hidden="true">⌄</span>
        </button>
        <div class="shop-navigation-links">
            <a href="{{ route('home') }}" @if(request()->routeIs('home') && !request()->query()) aria-current="page" @endif>Home</a>
            @foreach($navigationDepartments as $department => $label)
                @if($navigationGroups->has($department))
                    <button type="button" data-shop-department="{{ $department }}" aria-controls="category-mega-dropdown" aria-expanded="false">{{ $label }} <span aria-hidden="true">⌄</span></button>
                @endif
            @endforeach
            <a class="shop-sale-link" href="{{ route('sale') }}" @if(request()->routeIs('sale')) aria-current="page" @endif>Sale</a>
        </div>
    </div>
    <div id="category-mega-dropdown" class="shop-category-panel hidden">
        <div class="shop-category-heading">
            <div><p class="shop-category-eyebrow">FIND YOUR NEXT FAVORITE</p><h2 id="shop-menu-title">Shop by department</h2></div>
            <div class="shop-category-tools">
                <label class="shop-category-search"><span class="sr-only">Find a category</span><input id="mega-category-search" type="search" placeholder="Find a category…" oninput="filterMegaMenu(this.value)"></label>
                <button type="button" class="shop-menu-close" aria-label="Close categories" onclick="closeCategoryMenu(true)">&times;</button>
            </div>
        </div>
        <div id="mega-categories-grid" class="shop-category-groups">
            @foreach($navigationGroups as $department => $group)
                <section class="shop-category-group" data-department="{{ $department }}">
                    <h3>{{ $navigationDepartments[$department] ?? 'More to explore' }}</h3>
                    @foreach($group as $cat)
                        <a href="{{ route('home', ['category' => $cat->slug]) }}" class="mega-cat-item" data-cat-name="{{ strtolower($cat->name) }}" data-category-slug="{{ $cat->slug }}">{{ $cat->name }}</a>
                    @endforeach
                </section>
            @endforeach
        </div>
        <p id="shop-category-empty" class="hidden" role="status">No categories found. Try another search.</p>
        <div class="shop-category-footer">
            <a href="{{ route('home') }}#daily-discover-section" onclick="if(window.filterFeaturedSection) return filterFeaturedSection('just-for-you', event);">Just for You &rarr;</a>
            <a href="{{ route('home') }}#browse-categories" onclick="closeCategoryMenu()">Explore all categories &rarr;</a>
        </div>
    </div>
</nav>
