(() => {
    const root = document.querySelector('[data-notifications-url]');
    if (!root) return;
    const items = document.querySelector('[data-notification-items]');
    const status = document.querySelector('[data-notification-status]');
    let busy = false;
    let saving = false;
    let confirming = false;

    let revision = 0;
    const say = message => { if (status) status.textContent = message; };
    function syncFilters(data) {
        if (!data) return;
        document.querySelectorAll('[data-filter-count]').forEach(label => {
            label.textContent = data.filterCounts[label.dataset.filterCount] || '';
        });
        const wrapper = document.querySelector('[data-mark-all-wrapper]');
        if (wrapper) wrapper.hidden = !data.unread;
    }
    function markRead(card) {
        card.classList.remove('unread');
        card.classList.add('is-read');
        card.querySelector('[data-notification-read]')?.remove();
    }

    async function post(url, method = 'POST') {
        const response = await fetch(url, {
            method, keepalive: true,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        });
        if (!response.ok) throw new Error('Unable to save');
    }

    async function refresh(force = false) {
        if (busy || saving || confirming || document.hidden) return;
        busy = true;
        const startedAt = revision;
        try {
            const response = await fetch(root.dataset.notificationsUrl + (items ? window.location.search : ''), { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) throw new Error('Unable to refresh');
            const data = await response.json();
            if (saving || startedAt !== revision) return;
            document.querySelectorAll('[data-notification-count]').forEach(badge => {
                const total = Math.max(0, Number(data.count) || 0);
                badge.textContent = total || '';
                badge.hidden = total === 0;
            });
            const menuOpen = items?.querySelector('[data-menu-toggle][aria-expanded="true"]');
            if (items && (force || (!menuOpen && !items.contains(document.activeElement))) && items.innerHTML !== data.html) items.innerHTML = data.html;
            syncFilters(data);
            say('Updates automatically');
        } catch {
            say('Offline — retrying shortly');
        } finally { busy = false; }
    }

    // ⋮ menu: open/close, close on outside click or Escape.
    function closeMenus(except) {
        items?.querySelectorAll('[data-menu-toggle][aria-expanded="true"]').forEach(toggle => {
            if (toggle === except) return;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.nextElementSibling.hidden = true;
        });
    }
    items?.addEventListener('click', event => {
        const toggle = event.target.closest('[data-menu-toggle]');
        if (!toggle) return;
        const list = toggle.nextElementSibling;
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        closeMenus(toggle);
        toggle.setAttribute('aria-expanded', String(open));
        list.hidden = !open;
        if (open) {
            list.classList.toggle('open-up', toggle.getBoundingClientRect().bottom + list.offsetHeight + 16 > window.innerHeight);
            list.querySelector('button')?.focus();
        }
    });
    document.addEventListener('click', event => { if (!event.target.closest('.sn-menu')) closeMenus(); });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const open = items?.querySelector('[data-menu-toggle][aria-expanded="true"]');
        closeMenus();
        open?.focus();
    });

    // Confirm modal for Archive and Delete. Resolves true only when the seller presses the confirm button.
    const confirmDialog = document.getElementById('sn-confirm');
    function askToConfirm(action) {
        if (!confirmDialog) return Promise.resolve(window.confirm(action.dataset.confirmTitle));
        const { confirm: kind, confirmTitle, confirmText, confirmOk, confirmItem } = action.dataset;
        confirmDialog.dataset.kind = kind;
        confirmDialog.querySelector('[data-confirm-title]').textContent = confirmTitle;
        confirmDialog.querySelector('[data-confirm-text]').textContent = confirmText;
        const preview = confirmDialog.querySelector('[data-confirm-preview]');
        preview.textContent = confirmItem || '';
        preview.hidden = !confirmItem;
        const ok = confirmDialog.querySelector('[data-confirm-ok]');
        ok.textContent = confirmOk;
        confirming = true;
        confirmDialog.showModal();
        // Delete is destructive, so start on Cancel; Archive is safe, so start on the action.
        (kind === 'delete' ? confirmDialog.querySelector('[data-confirm-cancel]') : ok).focus();
        return new Promise(resolve => {
            const finish = result => {
                ok.removeEventListener('click', onOk);
                confirmDialog.removeEventListener('close', onClose);
                confirmDialog.removeEventListener('click', onBackdrop);
                confirming = false;
                if (confirmDialog.open) confirmDialog.close();
                resolve(result);
            };
            const onOk = () => finish(true);
            const onClose = () => finish(false);
            const onBackdrop = event => { if (event.target === confirmDialog) finish(false); };
            ok.addEventListener('click', onOk);
            confirmDialog.addEventListener('close', onClose);
            confirmDialog.addEventListener('click', onBackdrop);
        });
    }
    confirmDialog?.querySelector('[data-confirm-cancel]').addEventListener('click', () => confirmDialog.close());

    // Archive / Unarchive / Delete from the ⋮ menu.
    items?.addEventListener('click', async event => {
        const action = event.target.closest('[data-notification-action]');
        if (!action || saving || confirming) return;
        const toggle = action.closest('.sn-menu')?.querySelector('[data-menu-toggle]');
        closeMenus();
        if (action.dataset.confirm && !(await askToConfirm(action))) {
            toggle?.focus();
            return;
        }
        saving = true;
        revision++;
        action.disabled = true;
        try {
            await post(action.dataset.notificationAction, action.dataset.method);
            action.closest('.sn-item')?.remove();
            say(action.dataset.done);
        } catch {
            action.disabled = false;
            say('Something went wrong. Try again.');
            return;
        } finally { saving = false; }
        await refresh(true);
    });

    // Opening a notification (View) or clicking its unread dot marks it as read.
    items?.addEventListener('click', async event => {
        const button = event.target.closest('[data-notification-read]');
        const link = event.target.closest('a.sn-view');
        const card = (button || link)?.closest('.sn-item');
        const readButton = button || card?.querySelector('[data-notification-read]');
        if (!readButton || saving) return;
        saving = true;
        revision++;
        readButton.disabled = true;
        try {
            await post(readButton.dataset.notificationRead);
            if (button && document.activeElement === button) card.querySelector('a')?.focus();
            markRead(card);
            say('Marked as read');
        } catch {
            readButton.disabled = false;
            say('Could not mark as read. Try again.');
            return;
        } finally { saving = false; }
        await refresh(true);
    });

    const markAllForm = document.querySelector('#mark-all-read-btn')?.closest('form');
    markAllForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (saving) return;
        saving = true;
        revision++;
        const button = markAllForm.querySelector('button');
        button.disabled = true;
        try {
            await post(markAllForm.action);
            items?.querySelectorAll('.sn-item.unread').forEach(markRead);
            document.querySelector('[data-notification-filter="all"]')?.focus();
            say('All caught up');
        } catch {
            say('Could not mark all as read. Try again.');
            return;
        } finally { saving = false; button.disabled = false; }
        await refresh(true);
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    setInterval(refresh, 15000);
})();
