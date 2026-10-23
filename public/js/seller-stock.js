(() => {
    // "Edit" opens the stock editor under its product row.
    const toggles = document.querySelectorAll('[data-editor-toggle]');
    function setOpen(toggle, open) {
        toggle.setAttribute('aria-expanded', String(open));
        document.getElementById(toggle.getAttribute('aria-controls')).hidden = !open;
    }
    toggles.forEach(toggle => toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        setOpen(toggle, open);
        if (open) document.getElementById(toggle.getAttribute('aria-controls')).querySelector('input[type="number"]:not(:disabled)')?.focus();
    }));
    // A search that finds one product (e.g. a "Restock" link) opens it straight away.
    if (toggles.length === 1 && new URLSearchParams(location.search).get('search')) setOpen(toggles[0], true);

    const forms = document.querySelectorAll('[data-stock-form]');
    if (!forms.length) return;
    const labels = { ok: 'In stock', low: 'Low stock', out: 'Out of stock', inactive: 'Hidden' };
    const statusFor = (value, active, limit) => !active ? 'inactive' : value === 0 ? 'out' : value <= limit ? 'low' : 'ok';
    let submitting = false;

    function updateRow(row) {
        const input = row.querySelector('input[type="number"]');
        const original = Number(input.dataset.original);
        const value = input.value === '' ? original : Math.max(0, Math.floor(Number(input.value) || 0));
        const diff = value - original;
        row.classList.toggle('is-changed', diff !== 0);

        const diffLabel = row.querySelector('[data-stock-diff]');
        diffLabel.textContent = diff === 0 ? '' : (diff > 0 ? `+${diff}` : `−${Math.abs(diff)}`);
        diffLabel.className = 'inv-diff' + (diff > 0 ? ' is-up' : diff < 0 ? ' is-down' : '');

        const status = statusFor(value, row.dataset.active === '1', Number(row.dataset.limit ?? 10));
        const pill = row.querySelector('[data-stock-status]');
        pill.className = `inv-pill is-${status}`;
        pill.textContent = labels[status];
        row.querySelector('[data-step="-1"]').disabled = input.disabled || value <= 0;
        return diff !== 0;
    }

    function updateForm(form) {
        const changed = [...form.querySelectorAll('[data-stock-row]')].filter(updateRow).length;
        const bar = form.querySelector('[data-stock-savebar]');
        if (!bar) return;
        bar.hidden = changed === 0;
        bar.querySelector('[data-stock-summary]').textContent = `${changed} ${changed === 1 ? 'change' : 'changes'} not saved yet`;
    }

    forms.forEach(form => {
        form.addEventListener('click', event => {
            const step = event.target.closest('[data-step]');
            if (step) {
                const input = step.parentElement.querySelector('input');
                input.value = Math.min(1000000, Math.max(0, (Number(input.value) || 0) + Number(step.dataset.step)));
                updateForm(form);
            }
            if (event.target.closest('[data-stock-cancel]')) {
                form.querySelectorAll('input[data-original]').forEach(input => { input.value = input.dataset.original; });
                updateForm(form);
            }
        });
        form.addEventListener('input', () => updateForm(form));
        // Leave the field empty while typing, but never save an empty value.
        form.addEventListener('focusout', event => {
            const input = event.target.closest('input[data-original]');
            if (input && input.value === '') { input.value = input.dataset.original; updateForm(form); }
        });
        form.addEventListener('submit', () => {
            submitting = true;
            const save = form.querySelector('button[type="submit"]');
            if (save) { save.disabled = true; save.textContent = 'Saving…'; }
        });
        updateForm(form);
    });

    window.addEventListener('beforeunload', event => {
        if (!submitting && document.querySelector('.inv-row.is-changed')) event.preventDefault();
    });
})();
