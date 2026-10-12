@php
    $shopCategories = $categories ?? (new \App\Http\Controllers\HomeController())->getCategoriesWithMeta();
@endphp
<details class="shop-all-dropdown" id="shop-all-dropdown">
    <summary aria-controls="shop-all-panel">Collection</summary>
    <div id="shop-all-panel" class="shop-all-panel">
        <ul class="shop-all-list" aria-label="Product categories">
            @foreach($shopCategories as $category)
                <li><a href="{{ route('home', ['category' => $category->slug]) }}#daily-discover-section">{{ $category->name }}</a></li>
            @endforeach
        </ul>
    </div>
</details>
