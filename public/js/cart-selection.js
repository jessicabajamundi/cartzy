(() => {
    const form = document.getElementById('cart-checkout-form');
    if (!form) return;

    let boxes = [...document.querySelectorAll('.cart-item-checkbox')];
    const selectAll = document.getElementById('cart-select-all');
    const button = document.getElementById('cart-checkout-button');
    const money = value => '\u20b1' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let saveQueue = Promise.resolve();
    function saveSelection() {
        const selected = boxes.filter(box => box.checked).map(box => box.value);
        saveQueue = saveQueue.then(async () => {
            const response = await fetch(form.dataset.selectionUrl, {
                method: 'POST', keepalive: true,
                headers: {
                    'Content-Type': 'application/json', Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ selected_items: selected }),
            });
            if (!response.ok) throw new Error('Selection save failed');
        }).catch(() => {
            window.showToast?.('Could not save your selection. Please try again.', 'error');
        });
    }

    function updateTotalItemsBadge() {
        const total = boxes.reduce((sum, box) => sum + Number(box.dataset.quantity || 0), 0);
        const badge = document.getElementById('cart-items-count-badge');
        if (badge) {
            badge.textContent = `${total} ${total === 1 ? 'item' : 'items'}`;
        }
    }

    function update() {
        boxes = [...document.querySelectorAll('.cart-item-checkbox')];
        const selected = boxes.filter(box => box.checked);
        const quantity = selected.reduce((sum, box) => sum + Number(box.dataset.quantity), 0);
        const subtotal = selected.reduce((sum, box) => sum + Math.round(Number(box.dataset.price) * 100) * Number(box.dataset.quantity), 0) / 100;
        const shipping = selected.length ? 60 : 0;
        const discount = subtotal >= 1000 ? 50 : 0;
        if (selectAll) {
            selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
        }
        if (button) {
            button.disabled = selected.length === 0;
        }
        const selCountEl = document.getElementById('cart-selected-count');
        if (selCountEl) selCountEl.textContent = `${quantity} ${quantity === 1 ? 'item' : 'items'} selected`;

        const subtotalEl = document.getElementById('cart-selected-subtotal');
        if (subtotalEl) subtotalEl.textContent = money(subtotal);

        const shippingEl = document.getElementById('cart-selected-shipping');
        if (shippingEl) shippingEl.textContent = money(shipping);

        const discountEl = document.getElementById('cart-selected-discount');
        if (discountEl) discountEl.textContent = '-' + money(discount);

        const discountRowEl = document.getElementById('cart-discount-row');
        if (discountRowEl) discountRowEl.style.display = discount ? 'flex' : 'none';

        const totalEl = document.getElementById('cart-selected-total');
        if (totalEl) totalEl.textContent = money(subtotal + shipping - discount);

        const hintEl = document.getElementById('cart-selection-hint');
        if (hintEl) hintEl.textContent = selected.length ? 'Only selected items will be checked out.' : 'Select at least one item to continue.';

        updateTotalItemsBadge();
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            boxes.forEach(box => { box.checked = selectAll.checked; });
            update();
            saveSelection();
        });
    }

    boxes.forEach(box => {
        box.addEventListener('change', () => {
            update();
            saveSelection();
        });
    });

    form.addEventListener('submit', event => {
        if (!boxes.some(box => box.checked)) event.preventDefault();
    });

    // ─── Seamless Quantity Adjustments (AJAX - No Page Reload) ───
    const qtyDebounceTimers = {};

    document.querySelectorAll('.cart-qty-form').forEach(qtyForm => {
        const row = qtyForm.closest('[id^="cart-row-"]');
        if (!row) return;

        const cartKeyInput = qtyForm.querySelector('input[name="cart_key"]');
        const cartKey = cartKeyInput ? cartKeyInput.value : '';
        const minusBtn = qtyForm.querySelector('.cart-qty-minus');
        const plusBtn = qtyForm.querySelector('.cart-qty-plus');
        const valSpan = qtyForm.querySelector('.cart-qty-val');
        const checkbox = row.querySelector('.cart-item-checkbox');
        const rowTotalEl = row.querySelector('.cart-row-total');
        const price = Number(checkbox?.dataset.price || 0);

        qtyForm.addEventListener('submit', e => {
            e.preventDefault();
        });

        function adjustQuantity(delta) {
            const currentQty = Number(valSpan.textContent.trim()) || 1;
            const targetQty = currentQty + delta;
            if (targetQty < 1 || targetQty > 99) return;

            const oldQty = currentQty;

            // Instant optimistic update
            valSpan.textContent = targetQty;
            if (checkbox) checkbox.dataset.quantity = targetQty;
            if (minusBtn) {
                minusBtn.disabled = targetQty <= 1;
                minusBtn.value = Math.max(1, targetQty - 1);
            }
            if (plusBtn) {
                plusBtn.disabled = targetQty >= 99;
                plusBtn.value = Math.min(99, targetQty + 1);
            }
            if (rowTotalEl) {
                rowTotalEl.textContent = money(price * targetQty);
            }

            // Immediately update Order Summary & Items Count
            update();

            // Subtle indicator that it's saving
            valSpan.style.opacity = '0.5';

            // Debounce request to avoid spamming server
            clearTimeout(qtyDebounceTimers[cartKey]);
            qtyDebounceTimers[cartKey] = setTimeout(async () => {
                try {
                    const response = await fetch(qtyForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            cart_key: cartKey,
                            quantity: targetQty,
                        }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Could not update quantity');
                    }

                    // Sync header badge and cart preview popover
                    if (typeof window.updateCartBadge === 'function') {
                        window.updateCartBadge(data.cartCount);
                    }
                    if (typeof window.updateCartPopover === 'function') {
                        window.updateCartPopover(data.cart, data.cartCount);
                    }
                } catch (err) {
                    // Revert to old quantity if failed
                    valSpan.textContent = oldQty;
                    if (checkbox) checkbox.dataset.quantity = oldQty;
                    if (minusBtn) {
                        minusBtn.disabled = oldQty <= 1;
                        minusBtn.value = Math.max(1, oldQty - 1);
                    }
                    if (plusBtn) {
                        plusBtn.disabled = oldQty >= 99;
                        plusBtn.value = Math.min(99, oldQty + 1);
                    }
                    if (rowTotalEl) {
                        rowTotalEl.textContent = money(price * oldQty);
                    }
                    update();
                    window.showToast?.(err.message || 'Could not update quantity', 'error');
                } finally {
                    valSpan.style.opacity = '1';
                }
            }, 250);
        }

        if (minusBtn) {
            minusBtn.addEventListener('click', e => {
                e.preventDefault();
                adjustQuantity(-1);
            });
        }
        if (plusBtn) {
            plusBtn.addEventListener('click', e => {
                e.preventDefault();
                adjustQuantity(1);
            });
        }
    });

    // ─── Seamless Item Removal (AJAX - Smooth row exit) ───
    document.querySelectorAll('.cart-remove-form').forEach(removeForm => {
        removeForm.addEventListener('submit', async e => {
            e.preventDefault();
            const row = removeForm.closest('[id^="cart-row-"]');
            if (!row) return;

            const cartKeyInput = removeForm.querySelector('input[name="cart_key"]');
            const cartKey = cartKeyInput ? cartKeyInput.value : '';

            // If it's the last item in the cart, full submit/reload is appropriate to show empty cart view
            const remainingRows = document.querySelectorAll('[id^="cart-row-"]');
            if (remainingRows.length <= 1) {
                removeForm.submit();
                return;
            }

            // Visual feedback - fade out row
            row.style.transition = 'all 0.25s ease';
            row.style.opacity = '0.3';
            row.style.pointerEvents = 'none';

            try {
                const response = await fetch(removeForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ cart_key: cartKey }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Could not remove item');
                }

                // Animate removal and update totals
                row.remove();
                boxes = [...document.querySelectorAll('.cart-item-checkbox')];
                update();

                if (typeof window.updateCartBadge === 'function') {
                    window.updateCartBadge(data.cartCount);
                }
                if (typeof window.updateCartPopover === 'function') {
                    window.updateCartPopover(data.cart, data.cartCount);
                }
                window.showToast?.('Item removed from cart.', 'success');
            } catch (err) {
                row.style.opacity = '1';
                row.style.pointerEvents = '';
                window.showToast?.(err.message || 'Could not remove item', 'error');
            }
        });
    });

    window.addEventListener('pageshow', update);
    update();
})();
