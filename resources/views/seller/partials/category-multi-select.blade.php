@php
    $selectedIds = collect(old('category_ids', $selectedCategoryIds ?? (old('category_id') ? [old('category_id')] : [])))
        ->map(fn($id) => (int)$id)
        ->unique()
        ->values()
        ->all();

    if (empty($selectedIds) && !empty($defaultCategoryId)) {
        $selectedIds = [(int) $defaultCategoryId];
    }
    $primaryId = $selectedIds[0] ?? null;

@endphp

<div class="pe-field pe-span-2 pe-category-multi-field" id="pe-multi-category-component">
    <div class="pe-cat-header-row">
        <label class="pe-cat-label" for="pe-cat-trigger-btn">
            Categories *
            <span class="pe-cat-count-badge" id="pe-cat-count-badge" @if(empty($selectedIds)) style="display:none;" @endif>
                {{ count($selectedIds) }} selected
            </span>
        </label>
        <button type="button" class="pe-cat-clear-btn" id="pe-cat-clear-btn" @if(empty($selectedIds)) style="display:none;" @endif>
            Clear all
        </button>
    </div>

    <!-- Dropdown Selector Button & Popover -->
    <div class="pe-cat-select-box" id="pe-cat-select-box">
        <button type="button" class="pe-cat-trigger" id="pe-cat-trigger-btn" aria-haspopup="listbox" aria-expanded="false">
            <span class="pe-cat-trigger-text" id="pe-cat-trigger-text">
                @if(empty($selectedIds))
                    Select categories (multiple allowed)...
                @else
                    {{ count($selectedIds) }} {{ count($selectedIds) === 1 ? 'category' : 'categories' }} selected
                @endif
            </span>
            <svg class="pe-cat-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        <div class="pe-cat-dropdown" id="pe-cat-dropdown" hidden>
            <div class="pe-cat-search-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" class="pe-cat-search-input" id="pe-cat-search-input" placeholder="Search categories (e.g. Shoes, Baby, Gadgets)..." autocomplete="off">
                <button type="button" class="pe-cat-search-clear" id="pe-cat-search-clear" hidden title="Clear search">&times;</button>
            </div>

            <div class="pe-cat-list" id="pe-cat-options-list" role="listbox" aria-multiselectable="true">
                @foreach($categories as $category)
                    @php($isSel = in_array($category->id, $selectedIds))
                    <label class="pe-cat-option{{ $isSel ? ' is-selected' : '' }}" 
                           data-cat-id="{{ $category->id }}" 
                           data-cat-slug="{{ $category->slug }}" 
                           data-cat-name="{{ $category->name }}">
                        <input type="checkbox" 
                               class="pe-cat-checkbox" 
                               value="{{ $category->id }}" 
                               data-category-slug="{{ $category->slug }}" 
                               data-category-name="{{ $category->name }}" 
                               @checked($isSel)>
                        <span class="pe-cat-checkbox-custom">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                        </span>
                        <span class="pe-cat-option-name">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="pe-cat-empty-msg" id="pe-cat-empty-msg" hidden>
                No matching categories found
            </div>

            <div class="pe-cat-dropdown-footer">
                <span class="pe-cat-footer-hint">Tip: Check all relevant categories</span>
                <button type="button" class="button small pe-cat-done-btn" id="pe-cat-done-btn">Done</button>
            </div>
        </div>
    </div>

    <!-- Selected Category Badges / Pills -->
    <div class="pe-cat-pills-bar" id="pe-cat-pills-bar">
        @foreach($selectedIds as $index => $catId)
            @php($cat = $categories->firstWhere('id', $catId))
            @if($cat)
                <div class="pe-cat-pill{{ $index === 0 ? ' is-primary' : '' }}" data-cat-id="{{ $cat->id }}">
                    <span class="pe-cat-pill-title">{{ $cat->name }}</span>
                    @if($index === 0)
                        <span class="pe-cat-primary-tag" title="Primary category for storefront listing">Primary ★</span>
                    @endif
                    <button type="button" class="pe-cat-pill-remove" data-remove-cat-id="{{ $cat->id }}" title="Remove {{ $cat->name }}">&times;</button>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Hidden Inputs For Form Transmission -->
    <input type="hidden" name="category_id" id="product-category" value="{{ $primaryId }}">
    <div id="pe-cat-hidden-inputs">
        @foreach($selectedIds as $catId)
            <input type="hidden" name="category_ids[]" value="{{ $catId }}">
        @endforeach
    </div>

    <small class="pe-hint" id="category-options-help">
        Choose one or more. The first is your <strong>primary category</strong>.
    </small>
</div>
<script src="{{ asset('js/seller-multi-category.js') }}?v={{ filemtime(public_path('js/seller-multi-category.js')) }}"></script>
