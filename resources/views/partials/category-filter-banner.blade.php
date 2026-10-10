<section id="category-results-banner" class="category-filter-banner hidden" aria-label="Selected category">
    <div class="category-filter-banner__main">
        <span class="category-filter-banner__icon" aria-hidden="true">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13l-7 7a2 2 0 0 1-2.8 0L3 12.8V4a1 1 0 0 1 1-1h8.8l7.2 7.2a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1"/></svg>
        </span>
        <div class="category-filter-banner__copy">
            <p class="category-filter-banner__eyebrow">YOUR SELECTED CATEGORY</p>
            <div class="category-filter-banner__selection" aria-live="polite" aria-atomic="true">
                <h3 id="category-banner-name"></h3>
                <span class="category-filter-banner__count">Products found <strong id="category-banner-count">0</strong></span>
            </div>
        </div>
    </div>
    <button type="button" onclick="clearCategoryFilter()" class="category-filter-banner__clear">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
        Clear filter <span class="category-filter-banner__button-note">&amp; explore all</span>
    </button>
</section>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/category-filter-banner.css') }}?v={{ filemtime(public_path('css/category-filter-banner.css')) }}">
@endpush
