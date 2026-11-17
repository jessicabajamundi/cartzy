(() => {
    const section = document.getElementById('delete-account-section');
    const form = section?.querySelector('[data-delete-account-form]');
    const dialog = document.getElementById('delete-account-confirmation');
    if (!form || !dialog) return;
    const password = form.querySelector('input[name="password"]');
    const toggle = form.querySelector('[data-delete-password-toggle]');
    const submit = form.querySelector('[type="submit"]');
    const confirm = dialog.querySelector('[data-delete-confirm]');
    let confirmed = false;
    let submitting = false;
    function showPassword(visible) {
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-label', visible ? 'Hide Cartzy password' : 'Show Cartzy password');
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.querySelector('[data-eye-slash]').style.display = visible ? 'block' : 'none';
    }
    toggle.addEventListener('click', () => showPassword(password.type === 'password'));
    form.dataset.confirmationReady = 'true';
    form.addEventListener('submit', event => {
        if (submitting) { event.preventDefault(); return; }
        if (confirmed) {
            submitting = true;
            submit.disabled = true;
            confirm.disabled = true;
            confirm.textContent = 'Deleting account…';
            return;
        }
        event.preventDefault();
        if (!form.reportValidity()) return;
        showPassword(false);
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else if (window.confirm('Are you sure you want to permanently delete your Cartzy account? This action cannot be undone.')) {
            confirmed = true;
            form.requestSubmit();
        }
    });
    dialog.querySelector('[data-delete-cancel]').addEventListener('click', () => { confirmed = false; dialog.close(); });
    dialog.addEventListener('close', () => { if (!submitting) confirmed = false; });
    confirm.addEventListener('click', () => {
        if (submitting) return;
        confirmed = true;
        // Submit synchronously before the dialog's close event resets state.
        form.requestSubmit();
        dialog.close();
    });
    // A browser back navigation must not restore a disabled deletion form.
    window.addEventListener('pageshow', () => {
        confirmed = false; submitting = false;
        submit.disabled = false; confirm.disabled = false;
        confirm.textContent = 'Yes, delete my account';
        showPassword(false);
    });
})();
