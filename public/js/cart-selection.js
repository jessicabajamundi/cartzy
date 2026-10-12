(() => {
    const form = document.getElementById('cart-checkout-form');
    if (!form) return;

    const boxes = [...document.querySelectorAll('.cart-item-checkbox')];
    const selectAll = document.getElementById('cart-select-all');
    const button = document.getElementById('cart-checkout-button');
    const money = value => '\u20b1' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let saveQueue = Promise.resolve();
    function saveSelection() {
        const selected = boxes.filter(box => box.checked).map(box => box.value);
        saveQueue = saveQueue.then(async () => {
            const response = await fetch(form.dataset.selectionUrl, {
                method: 'POST', keepalive: true,
                headers: {
                    'Content-Type': 'application/json', Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ selected_items: selected }),
            });
            if (!response.ok) throw new Error('Selection save failed');
        }).catch(() => {
            window.showToast?.('Could not save your selection. Please try again.', 'error');
        });
    }

    function update() {
        const selected = boxes.filter(box => box.checked);
        const quantity = selected.reduce((sum, box) => sum + Number(box.dataset.quantity), 0);
        const subtotal = selected.reduce((sum, box) => sum + Math.round(Number(box.dataset.price) * 100) * Number(box.dataset.quantity), 0) / 100;
        const shipping = selected.length ? 60 : 0;
        const discount = subtotal >= 1000 ? 50 : 0;
        selectAll.checked = selected.length === boxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
        button.disabled = selected.length === 0;
        document.getElementById('cart-selected-count').textContent = `${quantity} ${quantity === 1 ? 'item' : 'items'} selected`;
        document.getElementById('cart-selected-subtotal').textContent = money(subtotal);
        document.getElementById('cart-selected-shipping').textContent = money(shipping);
        document.getElementById('cart-selected-discount').textContent = '-' + money(discount);
        document.getElementById('cart-discount-row').style.display = discount ? 'flex' : 'none';
        document.getElementById('cart-selected-total').textContent = money(subtotal + shipping - discount);
        document.getElementById('cart-selection-hint').textContent = selected.length ? 'Only selected items will be checked out.' : 'Select at least one item to continue.';
    }

    selectAll.addEventListener('change', () => {
        boxes.forEach(box => { box.checked = selectAll.checked; });
        update();
        saveSelection();
    });
    boxes.forEach(box => box.addEventListener('change', () => { update(); saveSelection(); }));
    form.addEventListener('submit', event => {
        if (!boxes.some(box => box.checked)) event.preventDefault();
    });
    window.addEventListener('pageshow', update);
    update();
})();
