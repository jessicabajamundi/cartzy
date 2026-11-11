{{-- Universal Confirmation Modal Dialog for Cartzy --}}
<dialog id="app-confirm-modal" class="app-confirm-modal" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message">
    <div class="app-confirm-card">
        <button type="button" class="app-confirm-close icon-button" data-app-confirm-cancel aria-label="Close dialog">&times;</button>
        <div id="app-confirm-badge" class="app-confirm-badge info">
            <svg id="app-confirm-icon-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <svg id="app-confirm-icon-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; display: none;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <svg id="app-confirm-icon-refresh" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; display: none;"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </div>
        <h2 id="app-confirm-title">Please Confirm</h2>
        <p id="app-confirm-message"></p>
        <p id="app-confirm-note" class="app-confirm-note" style="display: none;"></p>
        <div class="app-confirm-actions">
            <button type="button" class="button subtle" data-app-confirm-cancel autofocus>Cancel</button>
            <button type="button" class="button" id="app-confirm-submit">Confirm</button>
        </div>
    </div>
</dialog>

<style>
    .app-confirm-modal {
        width: min(450px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--line, #e2e8f0);
        border-radius: 20px;
        background: var(--surface, #fff);
        color: var(--ink, #1f2937);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.28);
        font-family: inherit;
    }
    .app-confirm-modal::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(3px);
    }
    .app-confirm-modal[open] {
        animation: app-confirm-pop .18s ease-out;
    }
    @keyframes app-confirm-pop {
        from { opacity: 0; transform: scale(0.96) translateY(6px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .app-confirm-card {
        position: relative;
        padding: 28px 24px 24px;
        text-align: center;
    }
    .app-confirm-close {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }
    .app-confirm-badge {
        display: inline-grid;
        place-items: center;
        width: 50px;
        height: 50px;
        margin-bottom: 16px;
        border-radius: 50%;
        background: #f0eaf6;
        color: var(--accent, #70558e);
    }
    .app-confirm-badge.danger {
        background: #fee2e2;
        color: #dc2626;
    }
    .app-confirm-badge.warning {
        background: #fef3c7;
        color: #b45309;
    }
    .app-confirm-badge.info {
        background: #f0eaf6;
        color: var(--accent, #70558e);
    }
    #app-confirm-title {
        font-size: 19px;
        font-weight: 700;
        margin: 0 0 8px;
        color: var(--ink, #1f2937);
    }
    #app-confirm-message {
        font-size: 13.5px;
        line-height: 1.55;
        color: var(--muted, #64748b);
        margin: 0 0 8px;
        overflow-wrap: anywhere;
    }
    .app-confirm-note {
        font-size: 12px;
        line-height: 1.5;
        color: #94a3b8;
        margin: 8px 0 0;
    }
    .app-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 22px;
    }
    .app-confirm-actions .button {
        flex: 1;
        min-height: 40px;
        font-size: 13px;
        font-weight: 600;
    }
    .app-confirm-actions #app-confirm-submit.danger {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    .app-confirm-actions #app-confirm-submit.danger:hover {
        background: #b91c1c;
    }
    @media (prefers-reduced-motion: reduce) {
        .app-confirm-modal[open] { animation: none; }
    }
</style>

<script>
(() => {
    const dialog = document.getElementById('app-confirm-modal');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const titleEl = document.getElementById('app-confirm-title');
    const msgEl = document.getElementById('app-confirm-message');
    const noteEl = document.getElementById('app-confirm-note');
    const badgeEl = document.getElementById('app-confirm-badge');
    const submitBtn = document.getElementById('app-confirm-submit');
    const iconInfo = document.getElementById('app-confirm-icon-info');
    const iconDanger = document.getElementById('app-confirm-icon-danger');
    const iconRefresh = document.getElementById('app-confirm-icon-refresh');

    let pendingForm = null;
    let pendingSubmitter = null;
    let confirmedForm = null;
    let pendingResolve = null;

    function resetIcons() {
        if (iconInfo) iconInfo.style.display = 'none';
        if (iconDanger) iconDanger.style.display = 'none';
        if (iconRefresh) iconRefresh.style.display = 'none';
    }

    function showConfirmModal(opts) {
        const msg = opts.message || 'Are you sure you want to proceed?';
        const isDestructive = opts.kind === 'delete' || opts.kind === 'danger' || /delete|remove|clear|destroy|force|discard/i.test(msg);
        const isRefresh = /reactivate|restore|reopen/i.test(msg);
        const title = opts.title || getSmartTitle(msg, opts.kind);

        titleEl.textContent = title;
        msgEl.textContent = msg;

        if (opts.note) {
            noteEl.textContent = opts.note;
            noteEl.style.display = 'block';
        } else {
            noteEl.textContent = '';
            noteEl.style.display = 'none';
        }

        resetIcons();
        submitBtn.className = 'button';

        if (isDestructive) {
            badgeEl.className = 'app-confirm-badge danger';
            if (iconDanger) iconDanger.style.display = 'block';
            submitBtn.classList.add('danger');
            submitBtn.textContent = opts.btnText || (opts.kind === 'delete' ? 'Yes, delete permanently' : 'Yes, continue');
        } else if (isRefresh) {
            badgeEl.className = 'app-confirm-badge info';
            if (iconRefresh) iconRefresh.style.display = 'block';
            submitBtn.textContent = opts.btnText || (/reactivate/i.test(msg) ? 'Yes, Reactivate' : 'Yes, Restore');
        } else {
            badgeEl.className = 'app-confirm-badge info';
            if (iconInfo) iconInfo.style.display = 'block';
            submitBtn.textContent = opts.btnText || 'Confirm';
        }

        submitBtn.disabled = false;
        dialog.showModal();
        dialog.querySelector('[data-app-confirm-cancel]')?.focus();
    }

    function getSmartTitle(msg, kind) {
        if (kind === 'delete' || /delete|force/i.test(msg)) return 'Delete Confirmation';
        if (/reactivate/i.test(msg)) return 'Reactivate Account';
        if (/restore/i.test(msg)) return 'Restore Item';
        if (/archive/i.test(msg)) return 'Archive Confirmation';
        if (/clear/i.test(msg)) return 'Clear Confirmation';
        if (/dispatch/i.test(msg)) return 'Dispatch Confirmation';
        return 'Please Confirm';
    }

    function handleClose(confirmed) {
        if (dialog.open) dialog.close();
        if (pendingResolve) {
            pendingResolve(confirmed);
            pendingResolve = null;
        }
    }

    dialog.querySelectorAll('[data-app-confirm-cancel]').forEach(btn => {
        btn.addEventListener('click', () => handleClose(false));
    });

    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) handleClose(false);
    });

    dialog.addEventListener('close', () => {
        if (pendingResolve) {
            pendingResolve(false);
            pendingResolve = null;
        }
    });

    submitBtn.addEventListener('click', () => {
        if (submitBtn.disabled) return;
        submitBtn.disabled = true;

        if (pendingForm) {
            confirmedForm = pendingForm;
            const sub = pendingSubmitter;
            const formToSubmit = pendingForm;
            pendingForm = null;
            pendingSubmitter = null;
            handleClose(true);
            try {
                formToSubmit.requestSubmit(sub || undefined);
            } finally {
                confirmedForm = null;
            }
        } else {
            handleClose(true);
        }
    });

    // Promise-based window.appConfirm
    window.appConfirm = function (message, options = {}) {
        return new Promise((resolve) => {
            pendingResolve = resolve;
            showConfirmModal({
                message: message,
                title: options.title,
                btnText: options.btnText,
                note: options.note,
                kind: options.kind
            });
        });
    };

    // Intercept form submissions with data-confirm or data-archive-action across the layout
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (confirmedForm === form) return;

        const confirmMsg = form.dataset.confirm || (
            form.dataset.archiveAction === 'delete'
                ? `Are you sure you want to permanently delete “${form.dataset.productName || 'this item'}”?`
                : (form.dataset.archiveAction ? `Are you sure you want to restore “${form.dataset.productName || 'this item'}”?` : null)
        );

        if (!confirmMsg) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        pendingForm = form;
        pendingSubmitter = event.submitter;

        showConfirmModal({
            message: confirmMsg,
            title: form.dataset.confirmTitle,
            btnText: form.dataset.confirmBtn,
            note: form.dataset.confirmNote || (
                form.dataset.archiveAction === 'delete'
                    ? 'This action cannot be undone. You will no longer be able to restore this listing.'
                    : (form.dataset.archiveAction ? 'This listing will become active again and visible on the marketplace.' : null)
            ),
            kind: form.dataset.archiveAction || form.dataset.confirmKind
        });
    }, true);
})();
</script>
