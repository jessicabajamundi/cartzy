(() => {
    function initMultiCategoryComponent(root = document) {
        const container = root.querySelector('#pe-multi-category-component');
        if (!container || container.dataset.initialized === 'true') return;
        container.dataset.initialized = 'true';

        const triggerBtn = container.querySelector('#pe-cat-trigger-btn');
        const triggerText = container.querySelector('#pe-cat-trigger-text');
        const dropdown = container.querySelector('#pe-cat-dropdown');
        const searchInput = container.querySelector('#pe-cat-search-input');
        const searchClear = container.querySelector('#pe-cat-search-clear');
        const countBadge = container.querySelector('#pe-cat-count-badge');
        const clearBtn = container.querySelector('#pe-cat-clear-btn');
        const doneBtn = container.querySelector('#pe-cat-done-btn');
        const emptyMsg = container.querySelector('#pe-cat-empty-msg');
        const pillsBar = container.querySelector('#pe-cat-pills-bar');
        const primaryInput = container.querySelector('#product-category');
        const hiddenInputsContainer = container.querySelector('#pe-cat-hidden-inputs');

        const optionLabels = Array.from(container.querySelectorAll('.pe-cat-option'));
        const checkboxes = Array.from(container.querySelectorAll('.pe-cat-checkbox'));
        const quickChips = Array.from(container.querySelectorAll('.pe-cat-quick-chip'));

        // Internal ordered list of selected category objects: { id, name, slug }
        let selectedList = [];

        // Initialize from checked checkboxes on load
        checkboxes.forEach(cb => {
            if (cb.checked) {
                selectedList.push({
                    id: String(cb.value),
                    name: cb.dataset.categoryName || '',
                    slug: cb.dataset.categorySlug || ''
                });
            }
        });

        function syncUI() {
            const count = selectedList.length;

            // Update primary hidden input and datasets
            const primary = selectedList[0] || null;
            if (primaryInput) {
                primaryInput.value = primary ? primary.id : '';
                primaryInput.dataset.selectedSlugs = selectedList.map(c => c.slug).join(' ');
                primaryInput.dataset.selectedNames = selectedList.map(c => c.name).join(' ');
            }

            // Sync hidden inputs for category_ids[]
            if (hiddenInputsContainer) {
                hiddenInputsContainer.innerHTML = selectedList.map(cat => 
                    `<input type="hidden" name="category_ids[]" value="${cat.id}">`
                ).join('');
            }

            // Update count badge & clear button
            if (countBadge) {
                if (count > 0) {
                    countBadge.style.display = 'inline-flex';
                    countBadge.textContent = `${count} selected`;
                } else {
                    countBadge.style.display = 'none';
                }
            }
            if (clearBtn) {
                clearBtn.style.display = count > 0 ? 'inline-block' : 'none';
            }

            if (count > 0) {
                triggerBtn?.classList.remove('is-invalid');
                const hint = container.querySelector('#category-options-help');
                if (hint && hint.style.color === 'rgb(178, 67, 85)') {
                    hint.style.color = '';
                    hint.style.fontWeight = '';
                    hint.innerHTML = 'Select multiple categories so buyers can easily discover your product across sections. The first selected is your <strong>Primary category</strong>.';
                }
            }

            // Update trigger text
            if (triggerText) {
                if (count === 0) {
                    triggerText.textContent = 'Select categories (multiple allowed)...';
                } else if (count === 1) {
                    triggerText.textContent = `${selectedList[0].name} (Primary)`;
                } else {
                    const firstTwo = selectedList.slice(0, 2).map(c => c.name).join(', ');
                    triggerText.textContent = `${count} categories: ${firstTwo}${count > 2 ? '...' : ''}`;
                }
            }

            // Update quick-pick chips active state
            const selectedIdsSet = new Set(selectedList.map(c => String(c.id)));
            quickChips.forEach(chip => {
                const qId = String(chip.dataset.quickCatId);
                chip.classList.toggle('is-active', selectedIdsSet.has(qId));
            });

            // Update checkboxes and option selection styling
            checkboxes.forEach(cb => {
                const isSel = selectedIdsSet.has(String(cb.value));
                cb.checked = isSel;
                cb.closest('.pe-cat-option')?.classList.toggle('is-selected', isSel);
            });

            // Update pills bar
            if (pillsBar) {
                pillsBar.innerHTML = selectedList.map((cat, idx) => {
                    const isPrimary = idx === 0;
                    return `
                        <div class="pe-cat-pill${isPrimary ? ' is-primary' : ''}" data-cat-id="${cat.id}">
                            <span class="pe-cat-pill-title">${escapeHtml(cat.name)}</span>
                            ${isPrimary ? '<span class="pe-cat-primary-tag" title="Primary category for storefront listing">Primary ★</span>' : ''}
                            <button type="button" class="pe-cat-pill-remove" data-remove-cat-id="${cat.id}" title="Remove ${escapeHtml(cat.name)}">&times;</button>
                        </div>
                    `;
                }).join('');

                // Attach remove event to pills
                pillsBar.querySelectorAll('[data-remove-cat-id]').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        const idToRemove = String(btn.dataset.removeCatId);
                        removeCategory(idToRemove);
                    });
                });

                // Clicking a non-primary pill promotes it to Primary!
                pillsBar.querySelectorAll('.pe-cat-pill').forEach(pill => {
                    pill.addEventListener('click', (e) => {
                        if (e.target.closest('.pe-cat-pill-remove')) return;
                        const idToPromote = String(pill.dataset.catId);
                        promoteToPrimary(idToPromote);
                    });
                });
            }

            // Dispatch change event on primary input so inventory / product scripts react
            if (primaryInput) {
                primaryInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function toggleCategory(cat) {
            const idx = selectedList.findIndex(c => String(c.id) === String(cat.id));
            if (idx > -1) {
                selectedList.splice(idx, 1);
            } else {
                selectedList.push({
                    id: String(cat.id),
                    name: cat.name,
                    slug: cat.slug || ''
                });
            }
            syncUI();
        }

        function removeCategory(id) {
            const idx = selectedList.findIndex(c => String(c.id) === String(id));
            if (idx > -1) {
                selectedList.splice(idx, 1);
                syncUI();
            }
        }

        function promoteToPrimary(id) {
            const idx = selectedList.findIndex(c => String(c.id) === String(id));
            if (idx > 0) {
                const item = selectedList.splice(idx, 1)[0];
                selectedList.unshift(item);
                syncUI();
            }
        }

        // Quick-pick chips click
        quickChips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                e.preventDefault();
                toggleCategory({
                    id: chip.dataset.quickCatId,
                    name: chip.dataset.catName,
                    slug: chip.dataset.catSlug
                });
            });
        });

        // Checkbox change inside dropdown
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                toggleCategory({
                    id: cb.value,
                    name: cb.dataset.categoryName,
                    slug: cb.dataset.categorySlug
                });
            });
        });

        // Clear all button
        clearBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            selectedList = [];
            syncUI();
        });

        // Dropdown open/close
        const openDropdown = () => {
            if (!dropdown) return;
            dropdown.hidden = false;
            triggerBtn?.setAttribute('aria-expanded', 'true');
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                searchInput.focus({ preventScroll: true });
            }
            dropdown.scrollTop = 0;
            const optionsList = container.querySelector('#pe-cat-options-list');
            if (optionsList) optionsList.scrollTop = 0;
        };

        const closeDropdown = () => {
            if (!dropdown) return;
            dropdown.hidden = true;
            triggerBtn?.setAttribute('aria-expanded', 'false');
        };

        triggerBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            if (dropdown && dropdown.hidden) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        doneBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            closeDropdown();
            triggerBtn?.focus();
        });

        // Live Search Filtering inside dropdown
        function filterOptions(query) {
            const q = (query || '').toLowerCase().trim();
            let visibleCount = 0;

            optionLabels.forEach(opt => {
                const name = (opt.dataset.catName || '').toLowerCase();
                const matches = !q || name.includes(q);
                opt.style.display = matches ? 'flex' : 'none';
                if (matches) visibleCount++;
            });

            if (emptyMsg) {
                emptyMsg.hidden = visibleCount > 0;
            }
            if (searchClear) {
                searchClear.hidden = !q;
            }
        }

        searchInput?.addEventListener('input', (e) => {
            filterOptions(e.target.value);
        });

        searchClear?.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                searchInput.focus();
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });

        // Close on Escape
        container.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdown && !dropdown.hidden) {
                closeDropdown();
                triggerBtn?.focus();
            }
        });

        // Form submit validation
        const form = container.closest('form');
        form?.addEventListener('submit', (e) => {
            if (selectedList.length === 0) {
                e.preventDefault();
                triggerBtn?.focus();
                triggerBtn?.classList.add('is-invalid');
                openDropdown();
                const hint = container.querySelector('#category-options-help');
                if (hint) {
                    hint.style.color = '#b24355';
                    hint.style.fontWeight = '600';
                    hint.textContent = 'Please choose at least one category for this product.';
                }
            }
        });

        // Initial render
        syncUI();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initMultiCategoryComponent());
    } else {
        initMultiCategoryComponent();
    }

    // Expose for dynamic modal opens or re-inits
    window.initMultiCategoryComponent = initMultiCategoryComponent;
})();
