(() => {
    const dialog = document.getElementById('cancelOrderDialog');
    if (!dialog) return;
    const form = document.getElementById('cancelOrderForm');
    const details = document.getElementById('cancelReasonDetails');
    const detailsContainer = document.getElementById('cancelOrderDetails');
    const confirmButton = document.getElementById('confirmCancellationButton');
    let submitting = false;

    function update() {
        const selected = form.querySelector('input[name="reason"]:checked');
        const other = selected?.value === 'Other reason';
        detailsContainer.hidden = !other;
        details.disabled = !other;
        details.required = other;
        confirmButton.disabled = submitting || !selected || (other && !details.value.trim());
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-cancel-order]');
        if (!trigger) return;
        form.reset();
        submitting = false;
        form.action = trigger.dataset.cancelOrder;
        document.getElementById('cancelOrderReference').textContent = trigger.dataset.orderReference;
        update();
        dialog.showModal();
    });
    document.getElementById('keepOrderButton').addEventListener('click', () => dialog.close());
    form.addEventListener('change', update);
    details.addEventListener('input', update);
    form.addEventListener('submit', (event) => {
        update();
        if (confirmButton.disabled || !form.reportValidity()) {
            event.preventDefault();
            return;
        }
        submitting = true;
        confirmButton.disabled = true;
    });
})();
