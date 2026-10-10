(() => {
    const modal = document.getElementById('add-product');
    const trigger = document.getElementById('add-product-trigger');
    if (!modal || !trigger) return;

    const openModal = () => {
        if (modal.open) return;
        modal.showModal();
        document.body.classList.add('product-modal-open');
        (modal.querySelector('.notice.error') || modal.querySelector('[name="name"]') || modal.querySelector('[data-close-product]'))?.focus();
    };

    const errors = modal.querySelector('.notice.error');
    if (errors) errors.tabIndex = -1;

    trigger.addEventListener('click', openModal);
    modal.querySelectorAll('[data-close-product]').forEach(button => {
        button.addEventListener('click', () => modal.close());
    });
    modal.addEventListener('close', () => {
        document.body.classList.remove('product-modal-open');
        trigger.focus({ preventScroll: true });
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
})();
