/** Shared admin navigation and confirmation behavior. */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-close]');
    function setOpen(open) {
        sidebar?.classList.toggle('is-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        if (overlay) overlay.hidden = !open;
        if (open) sidebar?.querySelector('a')?.focus();
    }
    toggle?.addEventListener('click', () => setOpen(!sidebar?.classList.contains('is-open')));
    overlay?.addEventListener('click', () => { setOpen(false); toggle?.focus(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            setOpen(false);
            toggle?.focus();
        }
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) setOpen(false);
    });
    // Pop-up dialogs: a button with data-dialog-open="<dialog id>" opens it; data-dialog-close,
    // Esc, or a click on the dimmed backdrop closes it.
    document.querySelectorAll('[data-dialog-open]').forEach((button) => {
        const dialog = document.getElementById(button.dataset.dialogOpen);
        if (!dialog || typeof dialog.showModal !== 'function') return;
        button.addEventListener('click', () => dialog.showModal());
        dialog.addEventListener('close', () => button.focus());
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        dialog.querySelectorAll('[data-dialog-close]').forEach((close) => close.addEventListener('click', () => dialog.close()));
    });

    // Registration review: the rejection reason only matters when rejecting.
    document.querySelectorAll('[data-review-decision]').forEach((select) => {
        const reason = select.closest('form').querySelector('[data-review-reason]');
        const sync = () => { if (reason) reason.hidden = select.value !== 'rejected'; };
        select.addEventListener('change', sync);
        sync();
    });

    const archiveDialog = document.getElementById('archive-confirm');
    const archiveSubmit = document.getElementById('archive-confirm-submit');
    let pendingArchive = null;
    let confirmedArchive = null;
    archiveDialog?.querySelectorAll('[data-archive-cancel]').forEach((button) => {
        button.addEventListener('click', () => archiveDialog.close());
    });
    archiveDialog?.addEventListener('close', () => {
        const trigger = pendingArchive?.submitter;
        pendingArchive = null;
        trigger?.focus();
    });
    archiveSubmit?.addEventListener('click', () => {
        if (!pendingArchive || archiveSubmit.disabled) return;
        const { form, submitter } = pendingArchive;
        archiveSubmit.disabled = true;
        confirmedArchive = form;
        try {
            form.requestSubmit(submitter || undefined);
        } finally {
            confirmedArchive = null;
            archiveDialog.close();
        }
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (confirmedArchive === form) return;
            if (form.dataset.archiveAction && typeof archiveDialog?.showModal === 'function') {
                event.preventDefault();
                if (archiveDialog.open) return;
                pendingArchive = { form, submitter: event.submitter };
                const deleting = form.dataset.archiveAction === 'delete';
                archiveDialog.dataset.action = form.dataset.archiveAction;
                document.getElementById('archive-confirm-title').textContent = deleting
                    ? 'Permanently delete this listing?' : 'Restore this listing?';
                document.getElementById('archive-confirm-message').textContent = deleting
                    ? `Are you sure you want to permanently delete “${form.dataset.productName}”?`
                    : `Are you sure you want to restore “${form.dataset.productName}”?`;
                document.getElementById('archive-confirm-note').textContent = deleting
                    ? 'This action cannot be undone. You will no longer be able to restore this listing.'
                    : 'This listing will become active again and visible on the marketplace.';
                archiveSubmit.textContent = deleting ? 'Yes, delete permanently' : 'Yes, restore listing';
                archiveSubmit.disabled = false;
                archiveDialog.showModal();
                archiveDialog.querySelector('[autofocus]').focus();
                return;
            }
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
