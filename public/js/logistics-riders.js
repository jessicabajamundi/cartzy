(() => {
    const actionDialog = document.getElementById('rider-action-dialog');
    const actionConfirm = document.getElementById('rider-action-confirm');
    const actionCancel = document.getElementById('rider-action-cancel');
    let pendingAction = null;
    let actionTrigger = null;

    document.querySelectorAll('[data-rider-toggle], [data-rider-confirm]').forEach(actionForm => {
        actionForm.addEventListener('submit', event => {
            const isDeactivation = actionForm.matches('[data-rider-toggle]') && actionForm.elements.active.value === '0';
            const isSuspension = actionForm.dataset.riderConfirm === 'suspend';
            if (!isDeactivation && !isSuspension) return;
            event.preventDefault();
            pendingAction = actionForm;
            actionTrigger = actionForm.closest('.rider-action-dropdown').querySelector('.rider-action-trigger');
            closeAllDropdowns();
            const name = actionForm.dataset.riderName;
            document.getElementById('rider-action-title').textContent = isSuspension ? 'Suspend this account?' : 'Deactivate this rider?';
            const riderName = document.createElement('strong');
            riderName.textContent = name;
            document.getElementById('rider-action-description').replaceChildren(
                document.createTextNode(isSuspension ? 'Are you sure you want to suspend ' : 'Are you sure you want to deactivate '),
                riderName,
                document.createTextNode(isSuspension
                    ? "'s account? The rider will be marked inactive and their account access will be restricted."
                    : '? The rider will be marked inactive in your hub.')
            );
            document.getElementById('rider-action-help').textContent = isSuspension
                ? 'Their records will be kept. You can restore access later using Unsuspend & Activate.'
                : actionForm.dataset.riderStatus === 'suspended'
                    ? 'This also lifts the current suspension and restores account access, while keeping the rider inactive. Their records will be kept.'
                    : 'Their account and delivery records will be kept. You can make this rider active again using Activate.';
            actionConfirm.textContent = isSuspension ? 'Yes, suspend' : 'Yes, deactivate';
            actionConfirm.disabled = false;
            actionDialog.showModal();
            actionCancel.focus();
        });
    });
    actionCancel?.addEventListener('click', () => actionDialog.close());
    actionDialog?.addEventListener('click', event => {
        const bounds = actionDialog.getBoundingClientRect();
        if (event.target === actionDialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) actionDialog.close();
    });
    actionDialog?.addEventListener('close', () => {
        pendingAction = null;
        actionTrigger?.focus();
    });
    actionConfirm?.addEventListener('click', () => {
        if (!pendingAction || actionConfirm.disabled) return;
        actionConfirm.disabled = true;
        actionConfirm.textContent = 'Updating...';
        HTMLFormElement.prototype.submit.call(pendingAction);
    });
    // ── Reject Dialog ───────────────────────────────────────────
    const dialog = document.getElementById('rider-reject-dialog');
    const form = document.getElementById('rider-reject-form');
    let trigger;
    document.querySelectorAll('[data-reject-url]').forEach(button => {
        button.addEventListener('click', () => {
            trigger = button;
            form.reset();
            form.action = button.dataset.rejectUrl;
            document.getElementById('rider-reject-name').textContent = `Application from ${button.dataset.riderName}`;
            dialog.showModal();
            document.getElementById('rider-reject-reason').focus();
        });
    });
    document.getElementById('rider-reject-cancel')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('close', () => trigger?.focus());

    // ── 3-Dots Action Dropdown ──────────────────────────────────
    function closeAllDropdowns() {
        document.querySelectorAll('.rider-dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
            const btn = menu.closest('.rider-action-dropdown')?.querySelector('.rider-action-trigger');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', (e) => {
        const triggerBtn = e.target.closest('.rider-action-trigger');

        if (triggerBtn) {
            e.stopPropagation();
            const parent = triggerBtn.closest('.rider-action-dropdown');
            const menu = parent ? parent.querySelector('.rider-dropdown-menu') : null;
            const isCurrentlyOpen = menu && !menu.classList.contains('hidden');

            closeAllDropdowns();

            if (!isCurrentlyOpen && menu) {
                menu.classList.remove('hidden');
                triggerBtn.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        // Clicking outside any dropdown closes all menus
        if (!e.target.closest('.rider-dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
})();
