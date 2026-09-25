/**
 * Cartzy Home Page Scripts
 * Handles live search, clear query, section toggling, and brand filtering
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

    function performSearch(query, shouldScroll = false) {
        query = (query || '').trim().toLowerCase();
        
        // Show or hide clear cross button
        if (searchClear) {
            if (query.length > 0) {
                searchClear.classList.remove('hidden');
            } else {
                searchClear.classList.add('hidden');
            }
        }

        const allCards = document.querySelectorAll('.product-card');
        let matchedCount = 0;
        let matchedInShoes = 0;
        let matchedInDaily = 0;

        if (!query) {
            // Restore everything
            allCards.forEach(card => card.classList.remove('hidden'));
            if (resultsBanner) resultsBanner.classList.add('hidden');
            if (noResults) {
                noResults.classList.add('hidden');
                noResults.classList.remove('flex');
            }
            if (shoesSection) shoesSection.style.display = '';
            if (dailySection) dailySection.style.display = '';
            return;
        }

        allCards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const brand = (card.getAttribute('data-brand') || '').toLowerCase();
            const category = (card.getAttribute('data-category') || '').toLowerCase();
            const cardText = (card.innerText || '').toLowerCase();

            const isMatch = name.includes(query) || 
                            brand.includes(query) || 
                            category.includes(query) || 
                            cardText.includes(query);

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

        // Update banner
        if (resultsBanner) {
            resultsBanner.classList.remove('hidden');
            if (queryDisplay) queryDisplay.textContent = query;
            if (countDisplay) countDisplay.textContent = matchedCount;
        }

        // Toggle sections based on matches
        if (shoesSection) {
            shoesSection.style.display = matchedInShoes > 0 ? '' : 'none';
        }
        if (dailySection) {
            dailySection.style.display = matchedInDaily > 0 ? '' : 'none';
        }

        // Show/hide no results box
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
            const target = resultsBanner || shoesSection;
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    window.clearSearch = function() {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        const url = new URL(window.location);
        url.searchParams.delete('q');
        window.history.replaceState({}, '', url.pathname);
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

    // Brand filter tab buttons inside Shoes & Sneakers section
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

    // Check if URL has q param on initial load
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q');
    if (initialQuery) {
        if (searchInput) searchInput.value = initialQuery;
        performSearch(initialQuery, true);
    }
});
