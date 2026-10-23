(() => {
    const modal = document.getElementById('add-product');
    const trigger = document.getElementById('add-product-trigger');
    if (!modal) return;

    const openModal = () => {
        if (typeof modal.showModal !== 'function') return;
        if (modal.open) return;
        modal.showModal();
        document.body.classList.add('product-modal-open');
        (modal.querySelector('.notice.error') || modal.querySelector('[name="name"]') || modal.querySelector('[data-close-product]'))?.focus();
    };

    const errors = modal.querySelector('.notice.error');
    if (errors) errors.tabIndex = -1;

    if (trigger) {
        trigger.addEventListener('click', openModal);
    }
    modal.querySelectorAll('[data-close-product]').forEach(button => {
        button.addEventListener('click', () => modal.close());
    });
    modal.addEventListener('close', () => {
        document.body.classList.remove('product-modal-open');
        if (trigger) trigger.focus({ preventScroll: true });
    });

    // Require both ends of the click outside so dragging from a field won't close it.
    const isOutside = event => {
        const rect = modal.getBoundingClientRect();
        return event.clientX < rect.left || event.clientX > rect.right
            || event.clientY < rect.top || event.clientY > rect.bottom;
    };
    let startedOutside = false;
    modal.addEventListener('pointerdown', event => {
        startedOutside = event.target === modal && isOutside(event);
    });
    modal.addEventListener('click', event => {
        if (startedOutside && event.target === modal && isOutside(event)) modal.close();
        startedOutside = false;
    });

    if (modal.dataset.autoOpen === 'true') openModal();

    // ==========================================
    // VARIATIONS & SHADES BUILDER LOGIC
    // ==========================================
    const variantsToggle = document.getElementById('enable-variants-toggle');
    const singleFieldsGroup = document.getElementById('single-variant-fields');
    const multipleFieldsGroup = document.getElementById('multiple-variants-fields');
    const singlePriceInput = document.getElementById('single-price-input');
    let selectedGalleryFiles = []; // product gallery photos
    const singleStockInput = document.getElementById('single-stock-input');
    const shadesContainer = document.getElementById('shades-container');
    const feedback = document.getElementById('variant-feedback');
    const announce = message => { if (feedback) feedback.textContent = message; };
    const escapeAttribute = value => String(value).replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character]));

    // Clothing uses both fields; electronics and bags use color without clothing sizes.
    const categorySelect = document.getElementById('product-category');
    const primarySizes = document.querySelector('[name="primary_size"]');
    const sizeStocks = document.getElementById('primary-size-stocks');
    const sizeRows = document.getElementById('primary-size-stock-rows');
    const savedSizeStocks = new Map();
    const rememberSizeStocks = () => {
        sizeRows?.querySelectorAll('.pe-size-price-row').forEach(row => {
            const size = row.querySelector('input[type="hidden"]')?.value;
            if (size) savedSizeStocks.set(size.toLowerCase(), {
                price: row.querySelector('[data-size-price]').value,
                stock: row.querySelector('[data-size-stock]').value,
            });
        });
    };
    const syncSizeStocks = () => {
        if (!primarySizes || !sizeRows || !sizeStocks) return;
        rememberSizeStocks();
        const sizes = [...new Map(primarySizes.value.split(',').map(size => size.trim()).filter(Boolean)
            .map(size => [size.toLowerCase(), size])).values()];
        primarySizes.setCustomValidity(sizes.length > 50 ? 'Add up to 50 sizes per color.' : sizes.some(size => size.length > 100) ? 'Each size must be 100 characters or fewer.' : '');
        const multiple = !primarySizes.disabled && sizes.length > 0;
        if (singleStockInput) { singleStockInput.disabled = multiple; singleStockInput.required = !multiple; }
        if (singlePriceInput) { singlePriceInput.disabled = multiple; singlePriceInput.required = !multiple; }
        sizeStocks.hidden = !multiple;
        ['primary-stock-field', 'primary-price-field'].forEach(id => {
            const field = document.getElementById(id);
            if (field) field.hidden = multiple;
        });
        sizeRows.replaceChildren();
        if (!multiple) return;
        sizes.slice(0, 50).forEach((size, index) => {
            const saved = savedSizeStocks.get(size.toLowerCase());
            const row = document.createElement('div');
            row.className = 'pe-size-price-row';
            const title = document.createElement('strong');
            title.textContent = size;
            const name = document.createElement('input');
            name.type = 'hidden';
            name.name = `primary_size_stocks[${index}][size]`;
            name.value = size;
            row.append(title, name);
            ['price', 'stock'].forEach(kind => {
                const label = document.createElement('label');
                label.className = 'pe-field';
                const caption = document.createElement('span');
                caption.className = 'pe-size-field-label';
                caption.textContent = kind === 'price' ? 'Price (PHP)' : 'Stock';
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `primary_size_stocks[${index}][${kind}]`;
                input.setAttribute(`data-size-${kind}`, '');
                input.setAttribute('aria-label', `${size} ${kind}`);
                input.min = kind === 'price' ? '0.01' : '0';
                input.max = kind === 'price' ? '99999999.99' : '1000000';
                input.step = kind === 'price' ? '0.01' : '1';
                input.required = true;
                input.value = saved?.[kind] ?? (kind === 'price' ? singlePriceInput?.value || '' : singleStockInput?.value || '0');
                label.appendChild(caption);
                if (kind === 'price') {
                    const money = document.createElement('span');
                    money.className = 'pe-money';
                    const currency = document.createElement('span');
                    currency.textContent = '₱';
                    currency.setAttribute('aria-hidden', 'true');
                    money.append(currency, input);
                    label.appendChild(money);
                } else {
                    label.appendChild(input);
                }
                row.appendChild(label);
            });
            sizeRows.appendChild(row);
        });
    };
    primarySizes?.addEventListener('input', syncSizeStocks);
    const ADULT_US_SIZES = ['US 6', 'US 6.5', 'US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'];
    const KIDS_US_SIZES = ['US 10C', 'US 10.5C', 'US 11C', 'US 11.5C', 'US 12C', 'US 12.5C', 'US 13C', 'US 1Y', 'US 1.5Y', 'US 2Y', 'US 2.5Y', 'US 3Y', 'US 3.5Y', 'US 4Y', 'US 4.5Y', 'US 5Y', 'US 5.5Y', 'US 6Y', 'US 7Y'];
    let editorSizeMode = 'adult';

    const genderSelect = document.querySelector('select[name="gender"]');

    const updateEditorSizeChips = (mode) => {
        editorSizeMode = mode === 'kids' ? 'kids' : 'adult';
        const sizesToRender = editorSizeMode === 'kids' ? KIDS_US_SIZES : ADULT_US_SIZES;
        const shoePlaceholder = editorSizeMode === 'kids' 
            ? 'e.g. US 11C, US 12C, US 13C, US 1Y, US 2Y, US 3Y' 
            : 'e.g. US 7, US 7.5, US 8, US 8.5, US 9, US 10';

        const size = document.querySelector('[name="primary_size"]');
        const builderSizesEl = document.getElementById('option-sizes');
        if (size) size.placeholder = shoePlaceholder;
        if (builderSizesEl) builderSizesEl.placeholder = shoePlaceholder;

        document.querySelectorAll('.pe-size-chips-wrap').forEach(wrap => {
            wrap.querySelectorAll('[data-editor-size-toggle]').forEach(btn => {
                btn.classList.toggle('is-active', btn.dataset.editorSizeToggle === editorSizeMode);
            });

            const container = wrap.querySelector('[data-chips-list-container]');
            if (container) {
                const target = wrap.dataset.sizeChipsFor;
                const input = target === 'primary_size'
                    ? document.querySelector('[name="primary_size"]')
                    : document.getElementById(target);
                const current = (input?.value || '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean);

                container.innerHTML = sizesToRender.map(s => {
                    const isSel = current.includes(s.toLowerCase()) ? ' is-selected' : '';
                    return `<button type="button" class="pe-size-chip${isSel}" data-size="${s}">${s}</button>`;
                }).join('');

                container.querySelectorAll('.pe-size-chip').forEach(chip => {
                    chip.addEventListener('click', () => {
                        const sizeVal = chip.dataset.size;
                        const currList = (input?.value || '').split(',').map(s => s.trim()).filter(Boolean);
                        const idx = currList.findIndex(s => s.toLowerCase() === sizeVal.toLowerCase());
                        if (idx > -1) {
                            currList.splice(idx, 1);
                        } else {
                            currList.push(sizeVal);
                        }
                        if (input) {
                            input.value = currList.join(', ');
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                });
            }
        });
    };

    const syncPrimaryFields = () => {
        const slugs = (categorySelect?.dataset?.selectedSlugs || '').toLowerCase();
        const names = (categorySelect?.dataset?.selectedNames || '').toLowerCase();
        const selectedOpt = categorySelect?.selectedOptions?.[0];
        const combinedSlugs = slugs || (selectedOpt?.dataset.categorySlug || '').toLowerCase();
        const combinedNames = names || (selectedOpt?.textContent || '').toLowerCase();
        const gender = (genderSelect?.value || '').toLowerCase();

        const isKids = combinedSlugs.includes('kids') || combinedNames.includes('kid') || ['kids', 'boys', 'girls', 'all_kids'].includes(gender);
        const isFootwearOrKids = !combinedSlugs || /(shoes|sneakers|footwear|kids|baby)/.test(combinedSlugs) || isKids;

        const sized = /(^|-|\s)(clothing|apparel|fashion|beachwear|kids|curve|shoes|sneakers|footwear|underwear|sleepwear|baby|maternity|textiles)(-|\s|$)/.test(combinedSlugs);
        const colored = sized || /(^|-|\s)(bags|luggage|jewelry|accessories|electronics|phones|audio|wearables|beauty|home-living|sports)(-|\s|$)/.test(combinedSlugs);
        document.querySelectorAll('[data-primary-field]').forEach(field => {
            const visible = Boolean(categorySelect?.value) && (field.dataset.primaryField === 'size' ? sized : colored);
            field.hidden = !visible;
            field.querySelector('input').disabled = !visible;
        });

        document.querySelectorAll('.pe-size-chips-wrap').forEach(wrap => {
            wrap.style.display = isFootwearOrKids ? 'flex' : 'none';
        });

        if (isKids) {
            updateEditorSizeChips('kids');
        } else if (/(shoes|sneakers|footwear)/.test(combinedSlugs)) {
            updateEditorSizeChips('adult');
        }

        syncSizeStocks();
    };
    categorySelect?.addEventListener('change', syncPrimaryFields);
    genderSelect?.addEventListener('change', syncPrimaryFields);

    const updateVariantModes = () => {
        const hasVariants = Boolean(variantsToggle && variantsToggle.checked);
        variantsToggle?.setAttribute('aria-expanded', String(hasVariants));
        const hasPrimary = Boolean(document.querySelector('input[name="primary_variant"]'));
        singleFieldsGroup?.querySelectorAll('input').forEach(input => { input.disabled = hasVariants && !hasPrimary; });
        if (hasPrimary) syncPrimaryFields();
        multipleFieldsGroup?.querySelectorAll('input').forEach(input => { input.disabled = !hasVariants; });

        if (singleFieldsGroup) {
            singleFieldsGroup.style.display = hasVariants && !hasPrimary ? 'none' : '';
        }
        if (multipleFieldsGroup) {
            multipleFieldsGroup.style.display = hasVariants ? '' : 'none';
        }

        // Toggle required attributes on single inputs (SKU is optional; the server makes one when blank)
        if (singlePriceInput) singlePriceInput.required = !singlePriceInput.disabled && (hasPrimary || !hasVariants);
        if (singleStockInput) singleStockInput.required = !singleStockInput.disabled && (hasPrimary || !hasVariants);

        // Toggle required attributes on shade inputs
        if (shadesContainer) {
            const shadeInputs = shadesContainer.querySelectorAll('input[data-required-variant="true"]');
            shadeInputs.forEach(input => {
                input.required = hasVariants;
            });
        }
    };

    if (variantsToggle) {
        variantsToggle.addEventListener('change', updateVariantModes);
        updateVariantModes();
    }

    // Helper: Wire up live preview for a shade card
    const wireUpShadeCard = (card) => {
        card.addEventListener('input', () => { card.dataset.edited = 'true'; });
        card.addEventListener('change', () => { card.dataset.edited = 'true'; });
        const fileInput = card.querySelector('.shade-file-input');
        const previewBox = card.querySelector('.shade-photo-preview');
        const colorInput = card.querySelector('.color-swatch-input');
        const hexText = card.querySelector('.swatch-hex-text');
        const removeBtn = card.querySelector('.remove-shade-btn');

        if (fileInput && previewBox) {
            fileInput.addEventListener('change', () => {
                const file = fileInput.files && fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewBox.classList.remove('empty');
                        previewBox.innerHTML = `<img src="${e.target.result}" alt="Shade preview">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.classList.add('empty');
                    previewBox.innerHTML = `<span class="preview-placeholder">No photo</span>`;
                }
            });
        }

        if (colorInput && hexText) {
            colorInput.addEventListener('input', () => {
                hexText.textContent = colorInput.value.toUpperCase();
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                card.remove();
                reindexShades();
                cleanupPhotoGroups();
                (shadesContainer.querySelector('.shade-name-input') || document.getElementById('option-colors'))?.focus();
                announce('Option removed.');
            });
        }
    };

    // Helper: Reindex all shade cards so form array indices are contiguous (variants[0], variants[1], ...)
    const reindexShades = () => {
        if (!shadesContainer) return;
        const cards = shadesContainer.querySelectorAll('.shade-card');
        const count = document.getElementById('variant-count');
        if (count) count.textContent = `${cards.length} ${cards.length === 1 ? 'option' : 'options'}`;
        const empty = document.getElementById('options-empty');
        if (empty) empty.hidden = cards.length > 0;
        cards.forEach((card, index) => {
            card.dataset.shadeIndex = index;
            const badge = card.querySelector('.shade-num-badge');
            if (badge) badge.textContent = `Option #${index + 1}`;
            card.querySelector('.remove-shade-btn').disabled = false;

            card.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('variants[')) {
                    const updatedName = name.replace(/variants\[\d+\]/, `variants[${index}]`);
                    input.setAttribute('name', updatedName);
                }
            });
        });
    };

    // Helper: Create a new shade / option card element
    const createShadeCard = (data = {}) => {
        const index = shadesContainer ? shadesContainer.querySelectorAll('.shade-card').length : 0;
        const name = escapeAttribute(data.name || '');
        const sku = escapeAttribute(data.sku || '');
        const price = escapeAttribute(data.price !== undefined ? data.price : (singlePriceInput ? singlePriceInput.value : ''));
        const stock = escapeAttribute(data.stock !== undefined ? data.stock : '0');
        const color = data.color || '#1E293B';

        const card = document.createElement('div');
        card.className = 'shade-card pe-variant';
        card.dataset.shadeIndex = index;
        card.innerHTML = `
            <label class="pe-field pe-variant-name"><span class="pe-variant-label">Option</span>
                <input type="text" name="variants[${index}][name]" value="${name}" placeholder="e.g. Medium / Black" class="shade-name-input" data-required-variant="true" required maxlength="255">
            </label>
            <label class="pe-field"><span class="pe-variant-label">Price</span>
                <span class="pe-money"><span>₱</span><input type="number" name="variants[${index}][price]" min="0.01" max="99999999.99" step="0.01" value="${price}" placeholder="0.00" data-required-variant="true" required></span>
            </label>
            <label class="pe-field"><span class="pe-variant-label">Stock</span>
                <input type="number" name="variants[${index}][stock]" min="0" max="1000000" value="${stock}" data-required-variant="true" required>
            </label>
            <button type="button" class="remove-shade-btn pe-remove" title="Remove this option" aria-label="Remove option">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <label class="pe-field pe-option-photo-row">Color photo
                        <span class="pe-variant-photo">
                            <span class="shade-photo-preview empty"><span class="preview-placeholder">No photo</span></span>
                            <input type="file" name="variants[${index}][image]" accept="image/jpeg,image/png,image/webp" class="shade-file-input">
                        </span>
                    </label>
    <details class="pe-variant-extras">
                <summary>Swatch &amp; stock code (optional)</summary>
                <div>
            <label class="pe-field">SKU (optional)
                <input type="text" name="variants[${index}][sku]" value="${sku}" placeholder="Auto" maxlength="100">
            </label>

                    <label class="pe-field">Color swatch
                        <span class="pe-swatch">
                            <input type="color" name="variants[${index}][color]" value="${color}" class="color-swatch-input">
                            <span class="swatch-hex-text">${color.toUpperCase()}</span>
                        </span>
                    </label>
                </div>
            </details>
        `;

        // Shared photos of this color: remember the group and preview the main photo with a count.
        if (data.photoGroup) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `variants[${index}][photo_group]`;
            hidden.value = data.photoGroup.id;
            hidden.dataset.photoGroup = '';
            card.appendChild(hidden);
            const preview = card.querySelector('.shade-photo-preview');
            const count = data.photoGroup.urls.length;
            preview.classList.remove('empty');
            preview.innerHTML = `<img src="${data.photoGroup.urls[0]}" alt="Main photo">${count > 1 ? `<span class="shade-photo-count">+${count - 1}</span>` : ''}`;
            preview.title = `${count} ${count === 1 ? 'photo' : 'photos'} shared with the other sizes of this color`;
        }

        wireUpShadeCard(card);
        return card;
    };

    // Wire up existing initial shade cards
    if (shadesContainer) {
        shadesContainer.querySelectorAll('.shade-card').forEach(wireUpShadeCard);
        reindexShades();
    }

    const builderColors = document.getElementById('option-colors');
    const colorChips = Array.from(document.querySelectorAll('[data-option-color]'));
    const syncColorChips = () => {
        const selected = builderColors?.value.trim().toLowerCase();
        colorChips.forEach(chip => chip.setAttribute('aria-pressed', String(chip.dataset.optionColor.toLowerCase() === selected)));
    };
    colorChips.forEach(chip => chip.addEventListener('click', () => {
        builderColors.value = chip.dataset.optionColor;
        builderColors.dispatchEvent(new Event('input', { bubbles: true }));
    }));
    builderColors?.addEventListener('input', syncColorChips);
    syncColorChips();

    const initSizeChips = () => {
        updateEditorSizeChips(editorSizeMode);

        document.addEventListener('click', event => {
            const toggleBtn = event.target.closest('[data-editor-size-toggle]');
            if (toggleBtn) {
                event.preventDefault();
                const mode = toggleBtn.dataset.editorSizeToggle;
                updateEditorSizeChips(mode);
            }
        });

        // Keep chip highlights in sync with input values
        ['primary_size', 'option-sizes'].forEach(target => {
            const input = target === 'primary_size'
                ? document.querySelector('[name="primary_size"]')
                : document.getElementById(target);
            if (!input) return;

            const syncChips = () => {
                const current = (input.value || '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                const wrap = document.querySelector(`[data-size-chips-for="${target}"]`);
                wrap?.querySelectorAll('.pe-size-chip').forEach(chip => {
                    const val = chip.dataset.size.toLowerCase();
                    chip.classList.toggle('is-selected', current.includes(val));
                });
            };

            input.addEventListener('input', syncChips);
            syncChips();
        });
    };
    initSizeChips();

    const builderSizes = document.getElementById('option-sizes');
    const builderPrice = document.getElementById('option-price');
    const builderStock = document.getElementById('option-stock');
    // Photos of the color being added: as many as the seller likes, each JPG/PNG/WebP up to 10 MB.
    const OPTION_PHOTO_MAX_BYTES = 10 * 1024 * 1024;
    const OPTION_PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const builderPhoto = document.getElementById('option-photo');
    const photoGrid = document.getElementById('option-photo-grid');
    const photoCount = document.getElementById('option-photo-count');
    const photoStore = document.getElementById('option-photo-store');
    const formatMb = bytes => (bytes / (1024 * 1024)).toFixed(bytes < 1024 * 1024 ? 2 : 1) + ' MB';
    let builderFiles = []; // [{ file, url }]
    // Uploaded once per color: group id => { input, urls }. Option rows point to their group.
    const photoGroups = new Map();

    const renderBuilderPhotos = () => {
        if (!photoGrid) return;
        photoGrid.querySelectorAll('.pe-color-photo-thumb').forEach(el => el.remove());
        const addTile = photoGrid.querySelector('.pe-color-photo-add');
        builderFiles.forEach(({ file, url }, index) => {
            const figure = document.createElement('figure');
            figure.className = 'pe-color-photo-thumb';
            const img = document.createElement('img');
            img.src = url; img.alt = `Photo ${index + 1}: ${file.name}`;
            const remove = document.createElement('button');
            remove.type = 'button'; remove.className = 'pe-color-photo-remove';
            remove.setAttribute('aria-label', `Remove photo ${index + 1}`); remove.title = 'Remove photo';
            remove.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
            remove.addEventListener('click', () => {
                URL.revokeObjectURL(url);
                builderFiles.splice(index, 1);
                renderBuilderPhotos();
                builderPhoto?.focus();
            });
            figure.append(img, remove);
            if (index === 0) figure.insertAdjacentHTML('beforeend', '<span class="pe-color-photo-main">Main</span>');
            photoGrid.insertBefore(figure, addTile);
        });
        const label = photoGrid.querySelector('[data-add-photo-label]');
        if (label) label.textContent = builderFiles.length ? 'Add more' : 'Add photos';
        if (photoCount) {
            const total = builderFiles.reduce((sum, { file }) => sum + file.size, 0);
            photoCount.textContent = builderFiles.length
                ? `${builderFiles.length} ${builderFiles.length === 1 ? 'photo' : 'photos'} · ${formatMb(total)}`
                : 'No photos yet';
        }
    };

    builderPhoto?.addEventListener('change', () => {
        const skipped = [];
        Array.from(builderPhoto.files).forEach(file => {
            if (!OPTION_PHOTO_TYPES.includes(file.type)) { skipped.push(`${file.name} (not JPG, PNG or WebP)`); return; }
            if (file.size > OPTION_PHOTO_MAX_BYTES) { skipped.push(`${file.name} (${formatMb(file.size)}, over 10 MB)`); return; }
            if (builderFiles.some(item => item.file.name === file.name && item.file.size === file.size)) return; // same photo picked twice
            builderFiles.push({ file, url: URL.createObjectURL(file) });
        });
        builderPhoto.value = '';
        renderBuilderPhotos();
        announce(skipped.length ? `Skipped: ${skipped.join(', ')}. Each photo must be JPG, PNG or WebP, up to 10 MB.` : `${builderFiles.length} photo(s) ready for this color.`);
    });

    // Drop the uploaded photos of a color once no option row uses them anymore.
    const cleanupPhotoGroups = () => {
        photoGroups.forEach((group, id) => {
            if (shadesContainer?.querySelector(`input[data-photo-group][value="${id}"]`)) return;
            group.urls.forEach(url => URL.revokeObjectURL(url));
            group.input.remove();
            photoGroups.delete(id);
        });
    };
    renderBuilderPhotos();
    const parseOptions = input => Array.from(new Map((input?.value || '').split(',')
        .map(value => value.trim()).filter(Boolean).map(value => [value.toLowerCase(), value])).values());
    const normalizeOption = value => value.split('/').map(part => part.trim().toLowerCase()).join(' / ');
    const primaryOptionNames = () => {
        const colorInput = document.querySelector('[name="primary_color"]');
        const color = colorInput && !colorInput.disabled ? colorInput.value.trim() : '';
        const sizes = primarySizes && !primarySizes.disabled ? parseOptions(primarySizes) : [];
        return (sizes.length ? sizes : ['']).map(size => normalizeOption([color, size].filter(Boolean).join(' / ') || 'Standard'));
    };
    const addOptions = () => {
        const color = builderColors.value.trim();
        const sizes = parseOptions(builderSizes);
        if (!color && !sizes.length) {
            announce('Enter a color, style, or size first.'); builderColors.focus(); return false;
        }
        if (color.includes(',')) {
            announce('Add one color at a time. You can include several sizes for that color.'); builderColors.focus(); return false;
        }
        if (sizes.length > 50 || sizes.some(size => size.length > 100)) {
            announce('Use up to 50 sizes, with at most 100 characters per size.'); builderSizes.focus(); return false;
        }
        if (![builderColors, builderSizes, builderPrice, builderStock].every(input => input.reportValidity())) return false;
        const price = builderPrice.value || (singlePriceInput?.disabled ? sizeRows?.querySelector('[data-size-price]')?.value : singlePriceInput?.value);
        if (!price || Number(price) < 0.01 || Number(price) > 99999999.99) {
            announce('Enter a valid price above or in this section first.'); builderPrice.focus(); return false;
        }
        const primaryColor = document.querySelector('[name="primary_color"]');
        const chosenColor = color || (primaryColor && !primaryColor.disabled ? primaryColor.value.trim() : '');
        const wheelHex = document.getElementById('option-color-hex')?.value.trim().replace(/^#/, '');
        const swatch = (/^[0-9a-f]{6}$/i.test(wheelHex || '') ? '#' + wheelHex.toUpperCase() : null)
            || colorChips.find(chip => chip.dataset.optionColor.toLowerCase() === chosenColor.toLowerCase())?.dataset.hex || '#1E293B';
        const names = (sizes.length ? sizes : ['']).map(size => [chosenColor, size].filter(Boolean).join(' / '));
        const existing = new Set([...primaryOptionNames(), ...Array.from(shadesContainer.querySelectorAll('.shade-name-input'), input => normalizeOption(input.value))]);
        let added = 0;
        const newCards = [];
        // Upload this color's photos once, shared by every size row created below.
        let photoGroup = null;
        if (builderFiles.length && names.some(name => !existing.has(normalizeOption(name)))) {
            try {
                const id = 'g' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
                const input = document.createElement('input');
                input.type = 'file'; input.multiple = true; input.name = `option_photos[${id}][]`;
                const transfer = new DataTransfer();
                builderFiles.forEach(({ file }) => transfer.items.add(file));
                input.files = transfer.files;
                photoStore.appendChild(input);
                photoGroup = { id, urls: builderFiles.map(item => item.url), input };
                photoGroups.set(id, photoGroup);
            } catch (error) {
                announce('Your browser could not attach the photos. Please try again using a current browser.');
                return false;
            }
        }
        names.forEach(name => {
            if (existing.has(normalizeOption(name))) return;
            const card = createShadeCard({ name, price, stock: builderStock.value || '0', color: swatch, photoGroup });
            newCards.push(card);
            existing.add(normalizeOption(name)); added++;
        });
        newCards.forEach(card => shadesContainer.appendChild(card));
        reindexShades();
        updateVariantModes();
        if (!added) {
            announce('These options already exist. Edit their price or stock in the product form instead.'); return false;
        }
        builderColors.value = ''; builderSizes.value = ''; builderPrice.value = ''; builderStock.value = '0';
        syncColorChips();
        builderSizes.dispatchEvent(new Event('input', { bubbles: true }));
        // The photo URLs now belong to the uploaded group, so clear the builder without revoking them.
        builderFiles = [];
        renderBuilderPhotos();
        announce(added + ' option(s) added' + (photoGroup ? ` with ${photoGroup.urls.length} photo(s)` : '') + '. Review their price and stock below.');
        builderColors.focus();
        return true;
    };
    document.getElementById('generate-product-options')?.addEventListener('click', addOptions);
    [builderColors, builderSizes, builderPrice, builderStock].forEach(input => input?.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); addOptions(); }
    }));

    // Product gallery: any number of photos within a combined 10 MB budget.
    const galleryMaxBytes = 10 * 1024 * 1024;
    const photosInput = document.getElementById('product-images-input');
    const photosPicker = document.getElementById('product-images-picker');
    const photosDropzone = document.getElementById('photos-dropzone');
    const photosCounterBadge = document.getElementById('photos-counter-badge');
    const photosCounterText = document.getElementById('photos-counter-text');
    const previewGrid = document.getElementById('product-photos-preview-grid');


    function syncInputFiles() {
        if (!photosInput) return;
        try {
            const dt = new DataTransfer();
            selectedGalleryFiles.forEach(file => dt.items.add(file));
            photosInput.files = dt.files;
        } catch (err) {
            console.error('DataTransfer sync error:', err);
        }
    }

    function isImageFile(file) {
        if (!file) return false;
        if (file.type && file.type.startsWith('image/')) return true;
        return /\.(jpe?g|png|webp|gif|bmp|svg|avif|jfif)$/i.test(file.name || '');
    }

    function addFiles(incomingFiles) {
        const fileList = Array.from(incomingFiles || []);
        if (fileList.length === 0) return;

        const validImages = fileList.filter(isImageFile);
        if (validImages.length === 0) {
            alert('Please select valid image files (JPG, PNG, WebP, AVIF, GIF).');
            return;
        }

        const nextFiles = [...selectedGalleryFiles];
        validImages.forEach(file => {
            const isExactDuplicate = nextFiles.some(f => f === file || (f.name === file.name && f.size === file.size && f.lastModified === file.lastModified));
            if (!isExactDuplicate) {
                nextFiles.push(file);
            }
        });

        if (nextFiles.reduce((sum, file) => sum + file.size, 0) > galleryMaxBytes) {
            alert('All product photos combined must be 10 MB or smaller. Choose smaller photos or remove some selected photos.');
            syncInputFiles();
            return;
        }

        selectedGalleryFiles = nextFiles;
        syncInputFiles();
        renderPhotosPreview();
    }

    function removeFile(index) {
        selectedGalleryFiles.splice(index, 1);
        syncInputFiles();
        renderPhotosPreview();
    }

    function triggerPhotoPicker() {
        if (photosPicker) {
            photosPicker.value = '';
            photosPicker.click();
        } else if (photosInput) {
            photosInput.click();
        }
    }

    if (photosPicker) {
        photosPicker.addEventListener('change', () => {
            addFiles(photosPicker.files);
            photosPicker.value = '';
        });
    }

    if (photosDropzone) {
        photosDropzone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                triggerPhotoPicker();
            }
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            photosDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                photosDropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'dragend'].forEach((eventName) => {
            photosDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                photosDropzone.classList.remove('dragover');
            });
        });

        photosDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            photosDropzone.classList.remove('dragover');

            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                addFiles(e.dataTransfer.files);
            }
        });
    }

    if (photosInput && !photosPicker) {
        photosInput.addEventListener('change', () => {
            addFiles(photosInput.files);
        });
    }

    function renderPhotosPreview() {
        if (!previewGrid) return;

        const totalMb = selectedGalleryFiles.reduce((sum, file) => sum + file.size, 0) / (1024 * 1024);
        const counter = `${selectedGalleryFiles.length} photos · ${totalMb.toFixed(2)} / 10 MB`;
        if (photosCounterText) {
            photosCounterText.textContent = counter;
        } else if (photosCounterBadge) {
            photosCounterBadge.textContent = counter;
        }

        if (photosCounterBadge) {
            photosCounterBadge.classList.toggle('has-photos', selectedGalleryFiles.length > 0);
        }

        if (selectedGalleryFiles.length === 0) {
            previewGrid.innerHTML = '';
            previewGrid.style.display = 'none';
            return;
        }

        previewGrid.innerHTML = '';
        previewGrid.style.display = 'grid';

        const coverIcon = '<svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: -1px; margin-right: 2px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

        selectedGalleryFiles.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = `photo-preview-item ${idx === 0 ? 'is-cover' : ''}`;
            item.innerHTML = `
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f8fafc; font-size:11px; color:#94a3b8;">Loading...</div>
                ${idx === 0 ? `<span class="photo-badge-cover">${coverIcon}Cover</span>` : `<span class="photo-badge-num">#${idx + 1}</span>`}
                <button type="button" class="photo-remove-btn" data-index="${idx}" title="Remove photo #${idx + 1}">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            `;
            previewGrid.appendChild(item);

            const removeBtn = item.querySelector('.photo-remove-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    removeFile(idx);
                });
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const imgPlaceholder = item.querySelector('div');
                if (imgPlaceholder) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = `Gallery photo ${idx + 1}`;
                    item.replaceChild(img, imgPlaceholder);
                }
            };
            reader.readAsDataURL(file);
        });

        // Keep adding photos while there is space in the gallery budget.
        if (totalMb < 10) {
            const addTile = document.createElement('label');
            addTile.htmlFor = 'product-images-picker';
            addTile.className = 'photo-add-more-tile';
            addTile.title = 'Add another photo';
            addTile.setAttribute('role', 'button');
            addTile.setAttribute('tabindex', '0');
            addTile.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add photo</span>
            `;
            addTile.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    triggerPhotoPicker();
                }
            });
            previewGrid.appendChild(addTile);
        }
    }

    const addProductForm = document.getElementById('add-product-form');
    addProductForm?.addEventListener('submit', event => {
        syncInputFiles();
        if (variantsToggle?.checked) {
            if (builderColors.value.trim() || builderSizes.value.trim() || builderPrice.value || Number(builderStock.value) !== 0 || builderFiles.length) {
                event.preventDefault();
                announce('Click Add to options to include your entry before creating the product.');
                builderColors.focus();
                return;
            }
            const names = new Set(primaryOptionNames());
            for (const input of shadesContainer.querySelectorAll('.shade-name-input')) {
                const name = normalizeOption(input.value);
                if (names.has(name)) {
                    event.preventDefault(); announce('This option is already listed. Change its name or remove the duplicate.'); input.focus(); return;
                }
                names.add(name);
            }
        }
        // The server accepts a limited number of files and megabytes per save; explain it instead of failing silently.
        const files = Array.from(addProductForm.querySelectorAll('input[type="file"][name]:not(:disabled)')).flatMap(input => Array.from(input.files || []));
        const maxFiles = Number(addProductForm.dataset.maxFiles) || 0;
        const maxBytes = Number(addProductForm.dataset.maxUploadBytes) || 0;
        const totalBytes = files.reduce((sum, file) => sum + file.size, 0);
        if ((maxFiles && files.length > maxFiles) || (maxBytes && totalBytes > maxBytes * 0.95)) {
            event.preventDefault();
            announce(`Too many photos to save at once: ${files.length} photos, ${formatMb(totalBytes)}. `
                + `Each save can include up to ${maxFiles} photos and ${formatMb(maxBytes)} in total. `
                + 'Remove a few photos now and add the rest after creating the product.');
            document.getElementById('variant-feedback')?.scrollIntoView({ block: 'center' });
            return;
        }
        const create = addProductForm.querySelector('[data-create-product]');
        if (create) { create.disabled = true; create.textContent = 'Creating…'; }
    });
    if (addProductForm) {
        addProductForm.addEventListener('reset', () => {
            selectedGalleryFiles = [];
            syncInputFiles();
            setTimeout(() => {
                if (photosCounterText) photosCounterText.textContent = '0 photos · 0.00 / 10 MB';
                if (photosCounterBadge) photosCounterBadge.classList.remove('has-photos');
                if (previewGrid) {
                    previewGrid.innerHTML = '';
                    previewGrid.style.display = 'none';
                }
            }, 0);
        });
    }
})();
