(() => {
    // Gallery size includes retained photos and pending replacements.
    const removedContainer = document.getElementById('pe-removed-images-container');
    const grid = document.querySelector('.pe-photos-grid');
    const bulkInput = document.querySelector('[data-bulk-photos-input]');
    const totalLabel = document.querySelector('[data-photo-total]');
    const maxBytes = 10 * 1024 * 1024;
    const cards = () => Array.from(grid?.querySelectorAll('[data-slot-card]') || []);
    const templateCard = cards()[0]?.cloneNode(true);
    let nextSlot = Math.max(-1, ...cards().map(card => Number(card.dataset.slotCard))) + 1;
    const pending = new Map();
    const objectUrls = new Map();
    const bytesFor = card => pending.get(card)?.size ?? Number(card.dataset.imageBytes || 0);
    const totalBytes = () => cards().reduce((sum, card) => sum + bytesFor(card), 0);
    const countPhotos = () => cards().filter(card => card.classList.contains('has-image')).length;
    const validFile = file => ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
    const tooLarge = () => alert('All product photos combined must be 10 MB or smaller. Remove photos or choose smaller images.');

    function updateTotal() {
        if (totalLabel) totalLabel.textContent = countPhotos() + ' photos · ' + (totalBytes() / (1024 * 1024)).toFixed(2) + ' / 10 MB';
    }

    function setInput(input, file) {
        const transfer = new DataTransfer();
        if (file) transfer.items.add(file);
        input.files = transfer.files;
    }

    function preview(card, file) {
        if (objectUrls.has(card)) URL.revokeObjectURL(objectUrls.get(card));
        const url = URL.createObjectURL(file);
        objectUrls.set(card, url);
        pending.set(card, file);
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Product photo preview';
        card.querySelector('[data-preview-box]').replaceChildren(img);
        card.classList.remove('is-empty');
        card.classList.add('has-image');
        card.querySelector('[data-remove-slot]').style.display = 'flex';
        card.querySelector('[data-action-text]').textContent = 'Change';
        updateTotal();
    }

    function bindCard(card) {
        const input = card.querySelector('[data-slot-input]');
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) { setInput(input, pending.get(card)); return; }
            if (!validFile(file) || totalBytes() - bytesFor(card) + file.size > maxBytes) {
                if (!validFile(file)) alert('Choose JPG, PNG or WebP images.');
                else tooLarge();
                setInput(input, pending.get(card));
                return;
            }
            preview(card, file);
        });
        card.querySelector('[data-remove-slot]').addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            if (card.dataset.imageId && removedContainer) {
                const removed = document.createElement('input');
                removed.type = 'hidden';
                removed.name = 'remove_image_ids[]';
                removed.value = card.dataset.imageId;
                removedContainer.appendChild(removed);
            }
            if (objectUrls.has(card)) URL.revokeObjectURL(objectUrls.get(card));
            objectUrls.delete(card);
            pending.delete(card);
            card.remove();
            updateTotal();
        });
    }
    cards().forEach(bindCard);

    bulkInput?.addEventListener('change', () => {
        const files = Array.from(bulkInput.files || []);
        bulkInput.value = '';
        if (!files.length) return;
        if (files.some(file => !validFile(file))) {
            alert('Choose JPG, PNG or WebP images.');
            return;
        }
        if (totalBytes() + files.reduce((sum, file) => sum + file.size, 0) > maxBytes) {
            tooLarge();
            return;
        }
        files.forEach(file => {
            const card = templateCard.cloneNode(true);
            const slot = nextSlot++;
            card.dataset.slotCard = slot;
            card.dataset.imageId = '';
            card.dataset.imageBytes = '0';
            card.querySelectorAll('[data-slot-input], [data-remove-slot], [data-preview-box], [data-action-text]').forEach(el => {
                ['data-slot-input', 'data-remove-slot', 'data-preview-box', 'data-action-text'].forEach(attr => {
                    if (el.hasAttribute(attr)) el.setAttribute(attr, slot);
                });
            });
            const input = card.querySelector('[data-slot-input]');
            input.id = 'pe_photo_input_' + slot;
            input.name = 'photos[' + slot + ']';
            input.removeAttribute('data-cover-input');
            card.querySelector('.pe-photo-slot-action').htmlFor = input.id;
            const box = card.querySelector('[data-preview-box]');
            box.removeAttribute('onclick');
            box.addEventListener('click', () => input.click());
            const badge = card.querySelector('.pe-photo-badge');
            badge.classList.remove('is-cover');
            badge.textContent = 'New photo';
            setInput(input, file);
            grid.appendChild(card);
            bindCard(card);
            preview(card, file);
        });
    });
    grid?.closest('form')?.addEventListener('submit', event => {
        if (totalBytes() > maxBytes) {
            event.preventDefault();
            tooLarge();
        }
        const maxFiles = Number(bulkInput?.dataset.maxFiles) || 0;
        if (maxFiles && pending.size > maxFiles) {
            event.preventDefault();
            alert('The server accepts up to ' + maxFiles + ' uploaded files per save. Save fewer new photos first, then add the rest. Existing photos do not count toward this upload limit.');
        }
    });
    updateTotal();

    const optionsForm = document.querySelector('[data-options-form]');
    if (!optionsForm) return;

    const list = optionsForm.querySelector('[data-color-group-list]');
    const template = optionsForm.querySelector('[data-color-group-template]');
    let nextGroupIndex = (list?.querySelectorAll('[data-color-group]')?.length || 0) + 1;

    const ADULT_US_SIZES = ['US 6', 'US 6.5', 'US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'];
    const KIDS_US_SIZES = ['US 10C', 'US 10.5C', 'US 11C', 'US 11.5C', 'US 12C', 'US 12.5C', 'US 13C', 'US 1Y', 'US 1.5Y', 'US 2Y', 'US 2.5Y', 'US 3Y', 'US 3.5Y', 'US 4Y', 'US 4.5Y', 'US 5Y', 'US 5.5Y', 'US 6Y', 'US 7Y'];

    const quickSizeBar = document.querySelector('[data-quick-size-bar]');
    const globalChipsContainer = document.querySelector('[data-quick-global-chips]');
    let currentSizeMode = quickSizeBar?.getAttribute('data-active-mode') || 'adult';

    function setGlobalSizeMode(mode) {
        currentSizeMode = mode === 'kids' ? 'kids' : 'adult';
        if (quickSizeBar) quickSizeBar.setAttribute('data-active-mode', currentSizeMode);

        // Update toggle buttons on the top bar
        quickSizeBar?.querySelectorAll('[data-set-size-mode]').forEach(btn => {
            btn.classList.toggle('is-active', btn.getAttribute('data-set-size-mode') === currentSizeMode);
        });

        // Re-render global chips on top bar
        const sizesToRender = currentSizeMode === 'kids' ? KIDS_US_SIZES : ADULT_US_SIZES;
        if (globalChipsContainer) {
            globalChipsContainer.innerHTML = sizesToRender.map(s => 
                `<button type="button" class="pe-quick-size-btn" data-quick-global-size="${escapeHtml(s)}">+ ${escapeHtml(s)}</button>`
            ).join('');
        }

        // Update all color groups' mini chips
        list?.querySelectorAll('[data-color-group]').forEach(groupEl => {
            renderGroupMiniChips(groupEl, currentSizeMode);
        });

        // Update placeholder in all color groups
        const placeholder = currentSizeMode === 'kids' ? 'e.g. US 11C, US 12C, US 13C, US 1Y, US 2Y, US 3Y' : 'e.g. US 7, US 7.5, US 8, US 8.5, US 9';
        list?.querySelectorAll('[data-sizes-input]').forEach(inp => {
            inp.placeholder = placeholder;
        });
    }

    function renderGroupMiniChips(groupEl, mode) {
        if (!groupEl) return;
        const chipsListEl = groupEl.querySelector('[data-mini-chips-list]');
        if (!chipsListEl) return;
        const sizesInput = groupEl.querySelector('[data-sizes-input]');
        const currentSizes = parseSizes(sizesInput?.value || '');
        const targetMode = mode || currentSizeMode;
        const sizesToRender = targetMode === 'kids' ? KIDS_US_SIZES : ADULT_US_SIZES;

        // Update group toggle buttons
        groupEl.querySelectorAll('[data-group-size-toggle]').forEach(btn => {
            btn.classList.toggle('is-active', btn.getAttribute('data-group-size-toggle') === targetMode);
        });

        chipsListEl.innerHTML = sizesToRender.map(s => {
            const isSel = currentSizes.includes(s) ? ' is-selected' : '';
            return `<button type="button" class="pe-size-chip-btn${isSel}" data-toggle-size="${escapeHtml(s)}">${escapeHtml(s)}</button>`;
        }).join('');
    }

    // Auto-detect when Category or Gender dropdown changes
    const categorySelect = document.getElementById('pe-edit-category-select');
    const genderSelect = document.getElementById('pe-edit-gender-select');

    function checkCategoryAndGender() {
        const selectedOpt = categorySelect?.selectedOptions?.[0];
        const slug = (selectedOpt?.getAttribute('data-category-slug') || '').toLowerCase();
        const catName = (selectedOpt?.textContent || '').toLowerCase();
        const gender = (genderSelect?.value || '').toLowerCase();

        if (slug.includes('kids') || catName.includes('kid') || ['kids', 'boys', 'girls', 'all_kids'].includes(gender)) {
            setGlobalSizeMode('kids');
        } else if (slug.includes('shoes') || slug.includes('sneakers') || slug.includes('footwear') || ['men', 'women', 'unisex'].includes(gender)) {
            setGlobalSizeMode('adult');
        }
    }

    categorySelect?.addEventListener('change', checkCategoryAndGender);
    genderSelect?.addEventListener('change', checkCategoryAndGender);

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[m]));
    }

    function parseSizes(str) {
        if (!str) return [];
        return str.split(',')
            .map(s => s.trim())
            .filter(Boolean)
            .filter((item, index, self) => self.indexOf(item) === index);
    }

    function updateGroupHeader(groupEl) {
        if (!groupEl) return;
        const colorInput = groupEl.querySelector('[data-sync-color]');
        const color = (colorInput?.value || '').trim();
        const previewLabel = groupEl.querySelector('[data-preview-color-label]');
        const swatch = groupEl.querySelector('[data-swatch]');
        const stockPill = groupEl.querySelector('[data-stock-pill]');

        if (previewLabel) {
            previewLabel.textContent = color || 'Color Option';
        }
        if (swatch && !swatch.style.background) {
            swatch.textContent = (color ? color.charAt(0) : '+').toUpperCase();
        }

        // Calculate total stock in this group for the pill badge
        let total = 0;
        groupEl.querySelectorAll('[data-field="stock"]').forEach(inp => {
            total += parseInt(inp.value, 10) || 0;
        });
        if (stockPill) {
            stockPill.className = `pe-pill is-${total === 0 ? 'out' : (total <= 10 ? 'low' : 'ok')}`;
            stockPill.textContent = total === 0 ? 'Sold out' : (total <= 10 ? 'Low' : 'In stock');
        }
    }

    function rebuildStockGrid(groupEl) {
        if (!groupEl) return;
        const gIndex = groupEl.getAttribute('data-group-index') || '0';
        const sizesInput = groupEl.querySelector('[data-sizes-input]');
        const gridEl = groupEl.querySelector('[data-size-stocks-grid]');
        if (!gridEl) return;

        const currentSizes = parseSizes(sizesInput?.value || '');

        // Gather existing stock values and hidden IDs from current DOM rows
        const existingData = new Map();
        gridEl.querySelectorAll('[data-size-row]').forEach(row => {
            const sizeKey = row.getAttribute('data-size-row');
            const stockVal = row.querySelector('[data-field="stock"]')?.value || '0';
            const idVal = row.querySelector('[data-field="id"]')?.value || '';
            const prevVal = row.querySelector('[data-field="prev"]')?.value || '0';
            existingData.set(sizeKey, { stock: stockVal, id: idVal, prev: prevVal });
        });

        // Also check if single stock row existed
        const singleStockInput = gridEl.querySelector('[data-single-stock-row] [data-field="stock"]');
        const singleId = gridEl.querySelector('[data-single-stock-row] [data-field="id"]')?.value || '';
        const singlePrev = gridEl.querySelector('[data-single-stock-row] [data-field="prev"]')?.value || '0';
        const defaultStockVal = singleStockInput ? singleStockInput.value : '0';

        if (currentSizes.length > 0) {
            let rowsHtml = '';
            currentSizes.forEach((size, sIndex) => {
                let saved = existingData.get(size);
                if (!saved) {
                    // If first size and had a single variant ID, reuse it
                    if (sIndex === 0 && singleId) {
                        saved = { stock: defaultStockVal, id: singleId, prev: singlePrev };
                    } else {
                        saved = { stock: '0', id: '', prev: '0' };
                    }
                }
                rowsHtml += `
                    <div class="pe-size-stock-row" data-size-row="${escapeHtml(size)}">
                        <span class="pe-size-stock-badge">${escapeHtml(size)}</span>
                        <input type="hidden" name="color_groups[${gIndex}][sizes][${sIndex}][size]" value="${escapeHtml(size)}" data-field="size">
                        <input type="hidden" name="color_groups[${gIndex}][sizes][${sIndex}][id]" value="${escapeHtml(saved.id)}" data-field="id">
                        <input type="hidden" name="color_groups[${gIndex}][sizes][${sIndex}][previous_stock]" value="${escapeHtml(saved.prev)}" data-field="prev">
                        <div class="pe-size-stock-input-wrap">
                            <input type="number" class="pe-size-stock-input" name="color_groups[${gIndex}][sizes][${sIndex}][stock]" value="${escapeHtml(saved.stock)}" min="0" max="1000000" required data-field="stock">
                            <span class="pe-size-stock-sublabel">stock</span>
                        </div>
                    </div>
                `;
            });
            gridEl.innerHTML = rowsHtml;
        } else {
            // Render single stock row when no specific sizes are selected
            gridEl.innerHTML = `
                <div class="pe-single-stock-row" data-single-stock-row>
                    <input type="hidden" name="color_groups[${gIndex}][sizes][0][size]" value="" data-field="size">
                    <input type="hidden" name="color_groups[${gIndex}][sizes][0][id]" value="${escapeHtml(singleId)}" data-field="id">
                    <input type="hidden" name="color_groups[${gIndex}][sizes][0][previous_stock]" value="${escapeHtml(singlePrev)}" data-field="prev">
                    <div class="pe-size-stock-input-wrap is-full">
                        <label class="pe-input-label">Stock quantity</label>
                        <input type="number" class="pe-option-input" name="color_groups[${gIndex}][sizes][0][stock]" value="${escapeHtml(defaultStockVal)}" min="0" max="1000000" required data-field="stock">
                    </div>
                </div>
            `;
        }

        // Highlight matching chips inside this group
        groupEl.querySelectorAll('[data-toggle-size]').forEach(btn => {
            const sVal = btn.getAttribute('data-toggle-size');
            btn.classList.toggle('is-selected', currentSizes.includes(sVal));
        });

        updateGroupHeader(groupEl);
    }

    function toggleSizeInGroup(groupEl, sizeToToggle) {
        if (!groupEl) return;
        const sizesInput = groupEl.querySelector('[data-sizes-input]');
        if (!sizesInput) return;
        let sizes = parseSizes(sizesInput.value);
        const idx = sizes.indexOf(sizeToToggle);
        if (idx >= 0) {
            sizes.splice(idx, 1);
        } else {
            sizes.push(sizeToToggle);
        }
        sizesInput.value = sizes.join(', ');
        rebuildStockGrid(groupEl);
    }

    function applyBatchStock(groupEl) {
        if (!groupEl) return;
        const batchInput = groupEl.querySelector('[data-batch-stock-input]');
        const val = batchInput?.value;
        if (val === '' || val === null || isNaN(val)) return;
        const numVal = Math.max(0, parseInt(val, 10));
        groupEl.querySelectorAll('[data-field="stock"]').forEach(inp => {
            inp.value = numVal;
        });
        updateGroupHeader(groupEl);
        // Visual feedback on batch button
        const btn = groupEl.querySelector('[data-batch-apply-stock]');
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = '✓ Done';
            setTimeout(() => { btn.textContent = originalText; }, 900);
        }
    }

    function addNewColorGroup() {
        if (!template || !list) return;
        const gIdx = nextGroupIndex++;
        const lastPriceInput = list.querySelector('[data-color-group]:last-child [data-sync-price]');
        const defaultPrice = lastPriceInput?.value || '';

        const html = template.innerHTML
            .replaceAll('__g__', String(gIdx))
            .replaceAll('__price__', escapeHtml(defaultPrice));

        list.insertAdjacentHTML('beforeend', html);
        const newGroup = list.lastElementChild;
        renderGroupMiniChips(newGroup, currentSizeMode);
        rebuildStockGrid(newGroup);
        newGroup.querySelector('[data-sync-color]')?.focus();
    }

    function getActiveOrLastGroup() {
        const active = document.activeElement?.closest('[data-color-group]');
        if (active) return active;
        const groups = list?.querySelectorAll('[data-color-group]');
        return groups?.length ? groups[groups.length - 1] : null;
    }

    // Input delegation
    optionsForm.addEventListener('input', event => {
        const groupEl = event.target.closest('[data-color-group]');
        if (!groupEl) return;

        if (event.target.matches('[data-sync-color]')) {
            updateGroupHeader(groupEl);
        } else if (event.target.matches('[data-sizes-input]')) {
            rebuildStockGrid(groupEl);
        } else if (event.target.matches('[data-field="stock"]')) {
            updateGroupHeader(groupEl);
        }
    });

    // Keydown delegation: Enter in batch stock input triggers apply
    optionsForm.addEventListener('keydown', event => {
        if (event.key === 'Enter' && event.target.matches('[data-batch-stock-input]')) {
            event.preventDefault();
            const groupEl = event.target.closest('[data-color-group]');
            applyBatchStock(groupEl);
        }
    });

    // Click delegation: quick chips, batch apply, add color, remove
    optionsForm.addEventListener('click', event => {
        // Global quick size mode toggle (Adult vs Kids)
        const sizeModeBtn = event.target.closest('[data-set-size-mode]');
        if (sizeModeBtn) {
            event.preventDefault();
            const mode = sizeModeBtn.getAttribute('data-set-size-mode');
            setGlobalSizeMode(mode);
            return;
        }

        // Color group mini size mode toggle (Adult vs Kids)
        const groupToggleBtn = event.target.closest('[data-group-size-toggle]');
        if (groupToggleBtn) {
            event.preventDefault();
            const groupEl = groupToggleBtn.closest('[data-color-group]');
            const mode = groupToggleBtn.getAttribute('data-group-size-toggle');
            renderGroupMiniChips(groupEl, mode);
            return;
        }

        // Toggle size inside a color group
        const toggleSizeBtn = event.target.closest('[data-toggle-size]');
        if (toggleSizeBtn) {
            event.preventDefault();
            const groupEl = toggleSizeBtn.closest('[data-color-group]');
            const sizeVal = toggleSizeBtn.getAttribute('data-toggle-size');
            toggleSizeInGroup(groupEl, sizeVal);
            return;
        }

        // Global quick size bar at top of card
        const globalSizeBtn = event.target.closest('[data-quick-global-size]');
        if (globalSizeBtn) {
            event.preventDefault();
            const groupEl = getActiveOrLastGroup();
            if (groupEl) {
                const sizeVal = globalSizeBtn.getAttribute('data-quick-global-size');
                toggleSizeInGroup(groupEl, sizeVal);
            }
            return;
        }

        // Batch apply stock
        const batchApplyBtn = event.target.closest('[data-batch-apply-stock]');
        if (batchApplyBtn) {
            event.preventDefault();
            const groupEl = batchApplyBtn.closest('[data-color-group]');
            applyBatchStock(groupEl);
            return;
        }

        // Add another colorway
        if (event.target.closest('[data-color-group-add]')) {
            event.preventDefault();
            addNewColorGroup();
            return;
        }

        // Cancel newly added group
        if (event.target.closest('[data-group-cancel]')) {
            event.preventDefault();
            event.target.closest('[data-color-group]')?.remove();
            return;
        }
    });

    // Removal handling for existing color groups
    optionsForm.addEventListener('change', event => {
        const remove = event.target.closest('[data-group-remove]');
        if (!remove) return;
        const groupEl = remove.closest('[data-color-group]');
        if (!groupEl) return;
        groupEl.classList.toggle('is-removed', remove.checked);
        groupEl.querySelectorAll('input[type="number"], .pe-option-input').forEach(input => {
            input.required = !remove.checked;
        });
    });
    optionsForm.querySelectorAll('[data-group-remove]:checked').forEach(box => {
        box.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Confirm before deleting; disable buttons while saving.
    document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    }));
    document.querySelectorAll('#product-details-form, [data-options-form]').forEach(form => form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        const button = form.querySelector('button[type="submit"]');
        if (button) { button.disabled = true; button.textContent = 'Saving…'; }
    }));
})();
