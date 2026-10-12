(() => {
    const modal = document.getElementById('add-product');
    const trigger = document.getElementById('add-product-trigger');
    if (!modal) return;

    const openModal = () => {
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
    const singleSkuInput = document.getElementById('single-sku-input');
    const singlePriceInput = document.getElementById('single-price-input');
    const singleStockInput = document.getElementById('single-stock-input');
    const shadesContainer = document.getElementById('shades-container');
    const addShadeBtn = document.getElementById('add-shade-row-btn');
    const feedback = document.getElementById('variant-feedback');
    const announce = message => { if (feedback) feedback.textContent = message; };
    const escapeAttribute = value => String(value).replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character]));
    const skuPrefix = `V-${Date.now().toString(36).toUpperCase()}`;
    let skuSequence = 0;
    const nextSku = () => `${skuPrefix}-${++skuSequence}`;

    const updateVariantModes = () => {
        const hasVariants = Boolean(variantsToggle && variantsToggle.checked);
        variantsToggle?.setAttribute('aria-expanded', String(hasVariants));
        singleFieldsGroup?.querySelectorAll('input').forEach(input => { input.disabled = hasVariants; });
        multipleFieldsGroup?.querySelectorAll('input').forEach(input => { input.disabled = !hasVariants; });

        if (singleFieldsGroup) {
            singleFieldsGroup.style.display = hasVariants ? 'none' : '';
        }
        if (multipleFieldsGroup) {
            multipleFieldsGroup.style.display = hasVariants ? '' : 'none';
        }

        // Toggle required attributes on single inputs
        if (singleSkuInput) singleSkuInput.required = !hasVariants;
        if (singlePriceInput) singlePriceInput.required = !hasVariants;
        if (singleStockInput) singleStockInput.required = !hasVariants;

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
                const totalCards = shadesContainer ? shadesContainer.querySelectorAll('.shade-card').length : 0;
                if (totalCards <= 1) {
                    announce('Keep at least one option while variations are enabled.');
                    return;
                }
                card.remove();
                reindexShades();
                shadesContainer.querySelector('.shade-name-input')?.focus();
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
        cards.forEach((card, index) => {
            card.dataset.shadeIndex = index;
            const badge = card.querySelector('.shade-num-badge');
            if (badge) badge.textContent = `Option #${index + 1}`;
            card.querySelector('.remove-shade-btn').disabled = cards.length === 1;

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
        const sku = escapeAttribute(data.sku || nextSku());
        const price = escapeAttribute(data.price !== undefined ? data.price : (singlePriceInput ? singlePriceInput.value : ''));
        const stock = escapeAttribute(data.stock !== undefined ? data.stock : '0');
        const color = data.color || '#1E293B';

        const card = document.createElement('div');
        card.className = 'shade-card';
        card.dataset.shadeIndex = index;
        card.innerHTML = `
            <div class="shade-card-header">
                <div class="shade-title">
                    <span class="shade-num-badge">Option #${index + 1}</span>
                    <input type="text" name="variants[${index}][name]" value="${name}" aria-label="Option name" placeholder="Option name, e.g. Medium / Black" class="shade-name-input" data-required-variant="true" required maxlength="255">
                </div>
                <button type="button" class="icon-button remove-shade-btn" title="Remove this option" aria-label="Remove option">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="shade-card-body">
                <div class="shade-fields-grid">
                    <label>SKU
                        <input type="text" name="variants[${index}][sku]" value="${sku}" placeholder="e.g. VAR-0${index + 1}" data-required-variant="true" required maxlength="100">
                    </label>
                    <label>Price (PHP)
                        <input type="number" name="variants[${index}][price]" min="0.01" max="99999999.99" step="0.01" value="${price}" placeholder="0.00" data-required-variant="true" required>
                    </label>
                    <label>Stock
                        <input type="number" name="variants[${index}][stock]" min="0" max="1000000" value="${stock}" data-required-variant="true" required>
                    </label>
                </div>
                <details class="variant-extras"><summary>Photo &amp; color <span>Optional</span></summary><div class="shade-visual-row">
                    <label class="shade-photo-label">
                        <span class="label-text">Variation photo thumbnail <small class="muted">· Optional</small></span>
                        <div class="shade-photo-uploader">
                            <input type="file" name="variants[${index}][image]" accept="image/jpeg,image/png,image/webp" class="shade-file-input">
                            <div class="shade-photo-preview empty">
                                <span class="preview-placeholder">No photo</span>
                            </div>
                        </div>
                    </label>
                    <label class="shade-color-label">
                        <span class="label-text">Color swatch <small class="muted">· Optional</small></span>
                        <div class="swatch-picker-row">
                            <input type="color" name="variants[${index}][color]" value="${color}" class="color-swatch-input">
                            <span class="swatch-hex-text">${color.toUpperCase()}</span>
                        </div>
                    </label>
                </div></details>
            </div>
        `;

        wireUpShadeCard(card);
        return card;
    };

    // Wire up existing initial shade cards
    if (shadesContainer) {
        shadesContainer.querySelectorAll('.shade-card').forEach(wireUpShadeCard);
        reindexShades();
        shadesContainer.querySelectorAll('input[name$="[sku]"]').forEach(input => {
            if (!input.value) input.value = nextSku();
        });
    }

    document.getElementById('apply-variant-values')?.addEventListener('click', () => {
        const fields = ['price', 'stock'].map(key => [key, document.getElementById(`bulk-variant-${key}`)]);
        for (const [, input] of fields) {
            if (!input.reportValidity()) return;
        }
        const filled = fields.filter(([, input]) => input.value !== '');
        if (!filled.length) { announce('Enter a price or stock quantity first.'); return; }
        filled.forEach(([key, input]) => {
            shadesContainer.querySelectorAll(`input[name$="[${key}]"]`).forEach(target => { target.value = input.value; });
            input.value = '';
        });
        announce('Updated all options. You can still edit each option individually.');
    });

    // Add shade button click
    if (addShadeBtn && shadesContainer) {
        addShadeBtn.addEventListener('click', () => {
            const newCard = createShadeCard();
            shadesContainer.appendChild(newCard);
            reindexShades();
            updateVariantModes();
            newCard.querySelector('.shade-name-input')?.focus();
        });
    }

    // Preset buttons click handlers
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!shadesContainer) return;
            const preset = btn.dataset.preset;
            let presetItems = [];

            if (preset === 'shoes') {
                presetItems = [
                    { name: 'Size 38 (EU)', color: '#1E293B' },
                    { name: 'Size 39 (EU)', color: '#1E293B' },
                    { name: 'Size 40 (EU)', color: '#1E293B' },
                    { name: 'Size 41 (EU)', color: '#1E293B' },
                    { name: 'Size 42 (EU)', color: '#1E293B' },
                ];
            } else if (preset === 'sizes') {
                presetItems = [
                    { name: 'Small (S)', color: '#475569' },
                    { name: 'Medium (M)', color: '#475569' },
                    { name: 'Large (L)', color: '#475569' },
                    { name: 'Extra Large (XL)', color: '#475569' },
                    { name: '2XL', color: '#475569' },
                ];
            } else if (preset === 'colors') {
                presetItems = [
                    { name: 'Classic Black', color: '#1E293B' },
                    { name: 'Pure White', color: '#F8FAFC' },
                    { name: 'Heather Gray', color: '#94A3B8' },
                    { name: 'Beige / Nude', color: '#E2D4C9' },
                ];
            } else if (preset === 'lipstick') {
                presetItems = [
                    { name: 'Shade 01 - Peachy Nude', color: '#F4A5A5' },
                    { name: 'Shade 02 - Cherry Pop', color: '#C41E3A' },
                    { name: 'Shade 03 - Velvet Rose', color: '#D9534F' },
                    { name: 'Shade 04 - Berry Glaze', color: '#881B4C' },
                ];
            }

            if (presetItems.length > 0) {
                // Only discard untouched blank rows; presets never replace seller entries.
                shadesContainer.querySelectorAll('.shade-card').forEach(card => {
                    const value = key => card.querySelector(`input[name$="[${key}]"]`)?.value;
                    if (!card.dataset.edited && !value('name') && !value('price') && Number(value('stock')) === 0
                        && value('sku')?.startsWith(skuPrefix) && value('color') === '#1e293b'
                        && !card.querySelector('.shade-file-input')?.files.length) card.remove();
                });
                const existing = new Set(Array.from(shadesContainer.querySelectorAll('.shade-name-input'), input => input.value.trim().toLowerCase()));
                let added = 0;
                presetItems.forEach(item => {
                    if (existing.has(item.name.toLowerCase())) return;
                    const card = createShadeCard({ name: item.name, color: item.color, stock: '0' });
                    shadesContainer.appendChild(card);
                    added++;
                });
                reindexShades();
                updateVariantModes();
                announce(added ? `${added} options added. Set their prices and available stock below.` : 'These preset options are already in your list.');
            }
        });
    });

    // Auto-generate SKUs in shade rows if user types a product name or base SKU
    const productNameInput = modal.querySelector('input[name="name"]');
    if (productNameInput) {
        productNameInput.addEventListener('blur', () => {
            const cleanName = productNameInput.value.trim().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 4) || 'PROD';
            if (shadesContainer) {
                shadesContainer.querySelectorAll('.shade-card').forEach((card, idx) => {
                    const skuInput = card.querySelector('input[name*="[sku]"]');
                    if (skuInput && !skuInput.value) {
                        skuInput.value = `${cleanName}-V${idx + 1}`;
                    }
                });
            }
            if (singleSkuInput && !singleSkuInput.value) {
                singleSkuInput.value = `${cleanName}-001`;
            }
        });
    }

    // Professional Multi-Photo Product Gallery Uploader (Up to 10 photos)
    const photosInput = document.getElementById('product-images-input');
    const photosPicker = document.getElementById('product-images-picker');
    const photosDropzone = document.getElementById('photos-dropzone');
    const photosCounterBadge = document.getElementById('photos-counter-badge');
    const photosCounterText = document.getElementById('photos-counter-text');
    const previewGrid = document.getElementById('product-photos-preview-grid');

    let selectedGalleryFiles = [];

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

    function addFiles(incomingFiles) {
        const fileList = Array.from(incomingFiles || []);
        if (fileList.length === 0) return;

        const validImages = fileList.filter(f => f.type.startsWith('image/'));
        if (validImages.length === 0) {
            alert('Please select valid image files (JPG, PNG, WebP).');
            return;
        }

        const oversized = validImages.filter(f => f.size > 10 * 1024 * 1024);
        if (oversized.length > 0) {
            alert(`File size exceeds 10 MB limit: ${oversized.map(f => f.name).join(', ')}. Please select images under 10 MB each.`);
        }

        const acceptable = validImages.filter(f => f.size <= 10 * 1024 * 1024);
        let addedCount = 0;
        let limitReached = false;

        acceptable.forEach(file => {
            if (selectedGalleryFiles.length < 10) {
                const isDuplicate = selectedGalleryFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
                if (!isDuplicate) {
                    selectedGalleryFiles.push(file);
                    addedCount++;
                }
            } else {
                limitReached = true;
            }
        });

        if (limitReached || (acceptable.length > addedCount && selectedGalleryFiles.length >= 10)) {
            alert('You can upload up to 10 photos for the product gallery. You already have 10 photos selected.');
        }

        syncInputFiles();
        renderPhotosPreview();
    }

    function removeFile(index) {
        selectedGalleryFiles.splice(index, 1);
        syncInputFiles();
        renderPhotosPreview();
    }

    function triggerPhotoPicker() {
        if (selectedGalleryFiles.length >= 10) {
            alert('You have reached the maximum limit of 10 gallery photos.');
            return;
        }
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
        photosDropzone.addEventListener('click', (e) => {
            if (e.target.closest('#product-images-input') || e.target.closest('#product-images-picker')) return;
            triggerPhotoPicker();
        });

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

        if (photosCounterText) {
            photosCounterText.textContent = `${selectedGalleryFiles.length} / 10 photos`;
        } else if (photosCounterBadge) {
            photosCounterBadge.textContent = `${selectedGalleryFiles.length} / 10 photos`;
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

        // Add more photo tile if less than 10
        if (selectedGalleryFiles.length < 10) {
            const addTile = document.createElement('button');
            addTile.type = 'button';
            addTile.className = 'photo-add-more-tile';
            addTile.title = 'Add another photo';
            addTile.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add photo</span>
            `;
            addTile.addEventListener('click', (e) => {
                e.stopPropagation();
                triggerPhotoPicker();
            });
            previewGrid.appendChild(addTile);
        }
    }

    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('reset', () => {
            selectedGalleryFiles = [];
            syncInputFiles();
            setTimeout(() => {
                if (photosCounterText) photosCounterText.textContent = '0 / 10 photos';
                if (photosCounterBadge) photosCounterBadge.classList.remove('has-photos');
                if (previewGrid) {
                    previewGrid.innerHTML = '';
                    previewGrid.style.display = 'none';
                }
            }, 0);
        });
    }
})();
