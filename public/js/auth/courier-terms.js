(() => {
    const dialog = document.getElementById('courierTermsDialog');
    const field = document.getElementById('courier_terms_agreed');
    const modalField = document.getElementById('modal_courier_terms_agreed');
    const opener = document.getElementById('openCourierTermsBtn');
    const body = document.getElementById('courierTermsModalBody');
    if (!dialog || !field) return;
    function sync() {
        modalField.checked = field.checked;
        document.getElementById('courierTermsAgreedBadge').style.display = field.checked ? 'inline-flex' : 'none';
        if (field.checked) {
            document.getElementById('courier_terms_error').hidden = true;
            opener.removeAttribute('aria-invalid');
        }
    }
    window.validateCourierTerms = (focus = false) => {
        sync();
        if (field.checked) return true;
        const error = document.getElementById('courier_terms_error');
        error.textContent = 'You must open and agree to the Cartzy Courier Terms & Conditions and Privacy Policy to proceed.';
        error.hidden = false;
        opener.setAttribute('aria-invalid', 'true');
        if (focus) { opener.focus(); opener.scrollIntoView({behavior: 'smooth', block: 'nearest'}); }
        return false;
    };
    const close = () => typeof dialog.close === 'function' ? dialog.close() : dialog.removeAttribute('open');
    opener.addEventListener('click', () => {
        sync();
        if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
        if (!field.checked) body.scrollTop = 0;
    });
    document.getElementById('closeCourierTermsBtn').addEventListener('click', close);
    modalField.addEventListener('change', () => { field.checked = modalField.checked; sync(); });
    document.getElementById('agreeCourierTermsBtn').addEventListener('click', () => { field.checked = true; sync(); close(); });
    let outside = false;
    const isOutside = event => {
        const box = dialog.getBoundingClientRect();
        return event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom;
    };
    dialog.addEventListener('pointerdown', event => { outside = event.target === dialog && isOutside(event); });
    dialog.addEventListener('click', event => { if (outside && event.target === dialog && isOutside(event)) close(); });
    document.getElementById('riderDetailsForm')?.addEventListener('submit', event => {
        if (!window.validateCourierTerms(true)) { event.preventDefault(); window.goToRiderStep(2); }
    });
    sync();
})();
