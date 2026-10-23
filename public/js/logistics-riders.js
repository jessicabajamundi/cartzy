(() => {
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
