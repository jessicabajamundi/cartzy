(() => {
    const root = document.querySelector('[data-notifications-url]');
    if (!root) return;
    const items = document.querySelector('[data-notification-items]');
    const count = root.querySelector('[data-notification-count]');
    const status = document.querySelector('[data-notification-status]');
    let busy = false;
    async function refresh() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const response = await fetch(root.dataset.notificationsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) throw new Error('Unable to load notifications');
            const data = await response.json();
            count.textContent = data.count;
            // Keep focused controls intact while the seller interacts with the panel.
            if (items && !items.contains(document.activeElement) && items.innerHTML !== data.html) items.innerHTML = data.html;
            if (status) status.textContent = 'Updates automatically every 15 seconds.';
        } catch {
            if (status) status.textContent = 'Unable to refresh. Retrying automatically.';
        } finally { busy = false; }
    }
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    items?.addEventListener('click', async event => {
        const button = event.target.closest('[data-notification-read]');
        if (!button || button.disabled) return;
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.notificationRead, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (!response.ok) throw new Error('Unable to mark as read');
            button.closest('article').classList.remove('unread');
            button.remove();
            await refresh();
        } catch {
            button.disabled = false;
            if (status) status.textContent = 'Could not mark as read. Please try again.';
        }
    });
    setInterval(refresh, 15000);
})();
