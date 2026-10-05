/**
 * Cartzy Home Page Scripts
 * Handles live search, clear query, 22-category database integration,
 * category filtering, header mega menu, and department tabs.
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('header-search-input');
    const searchForm = document.getElementById('header-search-form');
    const searchClear = document.getElementById('header-search-clear');
    const resultsBanner = document.getElementById('search-results-banner');
    const noResults = document.getElementById('no-search-results');
    const queryDisplay = document.getElementById('search-query-display');
    const countDisplay = document.getElementById('search-count-display');
    const shoesSection = document.getElementById('shoes');
    const dailySection = document.getElementById('daily-discover-section');

    const categoryBanner = document.getElementById('category-results-banner');
    const categoryBannerName = document.getElementById('category-banner-name');
    const categoryBannerCount = document.getElementById('category-banner-count');

    let currentCategory = 'all';
    let currentSearch = '';

    // ─── Header Mega Menu ──────────────────────────────────────────────────
    window.toggleCategoryMenu = function() {
        const dropdown = document.getElementById('category-mega-dropdown');
        const chevron = document.getElementById('cat-chevron');
        if (!dropdown) return;

        const isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            const input = document.getElementById('mega-category-search');
            if (input) setTimeout(() => input.focus(), 100);
        } else {
            dropdown.classList.add('hidden');
            if (chevron) chevron.style.transform = '';
        }
    };

    window.closeCategoryMenu = function() {
        const dropdown = document.getElementById('category-mega-dropdown');
        const chevron = document.getElementById('cat-chevron');
        if (dropdown) dropdown.classList.add('hidden');
        if (chevron) chevron.style.transform = '';
    };

    window.filterFeaturedSection = function(type) {
        if (window.closeCategoryMenu) window.closeCategoryMenu();
        if (type === 'just-for-you') {
            if (window.clearCategoryFilter) window.clearCategoryFilter();
            const el = document.getElementById('daily-discover-section');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else if (type === 'new-in') {
            const el = document.getElementById('shoes');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else if (type === 'sale') {
            const el = document.getElementById('flash-sale') || document.querySelector('section:has(#flash-sale-timer)');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ─── Horizontal Category Bar Scrolling ─────────────────────────────────
    window.scrollHeaderCategories = function(direction) {
        const el = document.getElementById('header-categories-scroll');
        if (!el) return;
        const scrollDistance = 220;
        el.scrollBy({
            left: direction === 'left' ? -scrollDistance : scrollDistance,
            behavior: 'smooth'
        });
    };

    const headerCatScroll = document.getElementById('header-categories-scroll');
    if (headerCatScroll) {
        headerCatScroll.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                headerCatScroll.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    }

    // Close mega menu on outside click
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('category-mega-menu-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            window.closeCategoryMenu();
        }
    });

    // Close mega menu on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeCategoryMenu();
        }
    });

    // Instant filter within Mega Menu search
    window.filterMegaMenu = function(query) {
        query = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.mega-cat-item');
        items.forEach(item => {
            const name = (item.getAttribute('data-cat-name') || '').toLowerCase();
            if (!query || name.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    };

    // ─── Search Functionality ──────────────────────────────────────────────
    function performSearch(query, shouldScroll = false) {
        currentSearch = (query || '').trim().toLowerCase();
        
        if (searchClear) {
            if (currentSearch.length > 0) {
                searchClear.classList.remove('hidden');
            } else {
                searchClear.classList.add('hidden');
            }
        }

        applyFilters(shouldScroll);
    }

    // ─── Unified Product Filtering (Category + Search + Brand) ─────────────
    function applyFilters(shouldScroll = false) {
        const allCards = document.querySelectorAll('.product-card');
        let matchedCount = 0;
        let matchedInShoes = 0;
        let matchedInDaily = 0;

        allCards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const brand = (card.getAttribute('data-brand') || '').toLowerCase();
            const categories = (card.getAttribute('data-category') || '').toLowerCase();
            const cardText = (card.innerText || '').toLowerCase();

            // Search filter
            const matchesSearch = !currentSearch || 
                name.includes(currentSearch) || 
                brand.includes(currentSearch) || 
                categories.includes(currentSearch) || 
                cardText.includes(currentSearch);

            // Category filter
            let matchesCategory = true;
            if (currentCategory && currentCategory !== 'all') {
                const catList = categories.split(/\s+/);
                matchesCategory = catList.includes(currentCategory) || categories.includes(currentCategory);
            }

            const isMatch = matchesSearch && matchesCategory;

            if (isMatch) {
                card.classList.remove('hidden');
                matchedCount++;
                if (shoesSection && shoesSection.contains(card)) {
                    matchedInShoes++;
                } else if (dailySection && dailySection.contains(card)) {
                    matchedInDaily++;
                }
            } else {
                card.classList.add('hidden');
            }
        });

        // Search Banner
        if (resultsBanner) {
            if (currentSearch) {
                resultsBanner.classList.remove('hidden');
                if (queryDisplay) queryDisplay.textContent = currentSearch;
                if (countDisplay) countDisplay.textContent = matchedCount;
            } else {
                resultsBanner.classList.add('hidden');
            }
        }

        // Category Banner
        if (categoryBanner) {
            if (currentCategory && currentCategory !== 'all') {
                categoryBanner.classList.remove('hidden');
                if (categoryBannerCount) categoryBannerCount.textContent = matchedCount;
            } else {
                categoryBanner.classList.add('hidden');
            }
        }

        // Section visibility
        if (shoesSection) {
            shoesSection.style.display = (matchedInShoes > 0 || (!currentSearch && currentCategory === 'all')) ? '' : 'none';
        }
        if (dailySection) {
            dailySection.style.display = (matchedInDaily > 0 || matchedCount > 0) ? '' : 'none';
        }

        // No Results
        if (noResults) {
            if (matchedCount === 0) {
                noResults.classList.remove('hidden');
                noResults.classList.add('flex');
            } else {
                noResults.classList.add('hidden');
                noResults.classList.remove('flex');
            }
        }

        if (shouldScroll) {
            const target = categoryBanner && !categoryBanner.classList.contains('hidden') 
                ? categoryBanner 
                : (resultsBanner || shoesSection || dailySection);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    // ─── Filter By Category ────────────────────────────────────────────────
    window.filterByCategory = function(slug, name, shouldScroll = true) {
        currentCategory = slug;

        // Update Category Showcase Cards styling
        document.querySelectorAll('.browse-category-card').forEach(card => {
            const cardSlug = card.getAttribute('data-category-slug');
            const indicator = card.querySelector('.cat-active-indicator');
            const ring = card.querySelector('.cat-ring-box');

            if (cardSlug === slug) {
                card.classList.add('border-[#6F6382]', 'bg-[#FAF9FB]', 'shadow-md');
                card.classList.remove('border-gray-200');
                if (ring) ring.classList.add('ring-2', 'ring-[#6F6382]', 'ring-offset-2');
                if (indicator) indicator.classList.remove('hidden');
            } else {
                card.classList.remove('border-[#6F6382]', 'bg-[#FAF9FB]', 'shadow-md');
                card.classList.add('border-gray-200');
                if (ring) ring.classList.remove('ring-2', 'ring-[#6F6382]', 'ring-offset-2');
                if (indicator) indicator.classList.add('hidden');
            }
        });

        // Update Header Navigation pills
        document.querySelectorAll('.header-nav-pill').forEach(pill => {
            pill.classList.remove('bg-[#6F6382]', 'text-white', 'font-bold');
            pill.classList.add('text-gray-600');
        });
        const activeNavPill = document.getElementById('header-nav-cat-' + slug);
        if (activeNavPill) {
            activeNavPill.classList.add('bg-[#6F6382]', 'text-white', 'font-bold');
            activeNavPill.classList.remove('text-gray-600');
        }

        // Update Category Filter Tab pills above products
        document.querySelectorAll('.catalog-cat-pill').forEach(pill => {
            pill.classList.remove('bg-[#6F6382]', 'text-white', 'font-bold');
            pill.classList.add('bg-white', 'text-gray-700');
        });
        const activeCatalogPill = document.getElementById('catalog-pill-' + slug);
        if (activeCatalogPill) {
            activeCatalogPill.classList.add('bg-[#6F6382]', 'text-white', 'font-bold');
            activeCatalogPill.classList.remove('bg-white', 'text-gray-700');
        }

        // Update category banner display
        if (categoryBannerName) {
            categoryBannerName.textContent = name || slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Update URL
        const url = new URL(window.location);
        if (slug && slug !== 'all') {
            url.searchParams.set('category', slug);
        } else {
            url.searchParams.delete('category');
        }
        window.history.replaceState({}, '', url.toString());

        applyFilters(shouldScroll);
    };

    window.clearCategoryFilter = function() {
        window.filterByCategory('all', 'All Categories', false);
    };

    // ─── Department Tabs within Categories Showcase ────────────────────────
    window.filterDepartmentTabs = function(dept) {
        document.querySelectorAll('.dept-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#6F6382]', 'text-white', 'shadow-xs');
            btn.classList.add('bg-[#F1EFF5]', 'text-gray-600', 'hover:bg-[#E1DDE7]');
        });

        const activeBtn = document.getElementById('dept-tab-' + dept);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#6F6382]', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('bg-[#F1EFF5]', 'text-gray-600', 'hover:bg-[#E1DDE7]');
        }

        const cards = document.querySelectorAll('.browse-category-card');
        cards.forEach(card => {
            const cardDept = card.getAttribute('data-department');
            if (dept === 'all' || cardDept === dept) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    };

    // ─── Search within Category Cards ──────────────────────────────────────
    window.searchCategoryCards = function(query) {
        query = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.browse-category-card');
        cards.forEach(card => {
            const name = (card.getAttribute('data-category-name') || '').toLowerCase();
            if (!query || name.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    };

    // ─── Toggle All vs Compact View for Categories ─────────────────────────
    let isAllCategoriesExpanded = false;
    window.toggleAllCategoriesView = function() {
        const extraCards = document.querySelectorAll('.category-card-extra');
        const btn = document.getElementById('toggle-all-cats-btn');
        const btnText = document.getElementById('toggle-all-cats-text');
        const btnIcon = document.getElementById('toggle-all-cats-icon');

        isAllCategoriesExpanded = !isAllCategoriesExpanded;

        extraCards.forEach(card => {
            if (isAllCategoriesExpanded) {
                card.classList.remove('hidden');
                card.style.display = '';
            } else {
                card.classList.add('hidden');
            }
        });

        if (btnText) {
            btnText.textContent = isAllCategoriesExpanded ? 'Show Less' : 'Explore All 22 Categories';
        }
        if (btnIcon) {
            btnIcon.style.transform = isAllCategoriesExpanded ? 'rotate(180deg)' : '';
        }
    };

    // ─── Clear Search ──────────────────────────────────────────────────────
    window.clearSearch = function() {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        const url = new URL(window.location);
        url.searchParams.delete('q');
        window.history.replaceState({}, '', url.toString());
        performSearch('', false);
    };

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            window.clearSearch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            performSearch(this.value, false);
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (window.location.pathname === '/' || window.location.pathname === '') {
                e.preventDefault();
                const q = searchInput ? searchInput.value : '';
                performSearch(q, true);
                const url = new URL(window.location);
                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }
                window.history.replaceState({}, '', url.toString());
            }
        });
    }

    // ─── Brand filter tab buttons inside Shoes & Sneakers section ──────────
    window.filterShoeBrand = function(brand) {
        document.querySelectorAll('.brand-tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-black', 'shadow-2xs');
            btn.classList.add('text-gray-600');
        });
        const activeBtn = document.getElementById('tab-brand-' + brand);
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'text-black', 'shadow-2xs');
            activeBtn.classList.remove('text-gray-600');
        }

        const shoeCards = document.querySelectorAll('#shoes-grid .product-card');
        shoeCards.forEach(card => {
            const cardBrand = card.getAttribute('data-brand');
            if (brand === 'all' || cardBrand === brand) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    };

    // ─── Initialize from URL Parameters ────────────────────────────────────
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q');
    const initialCategory = urlParams.get('category') || urlParams.get('cat');

    if (initialQuery) {
        if (searchInput) searchInput.value = initialQuery;
        performSearch(initialQuery, true);
    }

    if (initialCategory) {
        window.filterByCategory(initialCategory, '', true);
    }
});
