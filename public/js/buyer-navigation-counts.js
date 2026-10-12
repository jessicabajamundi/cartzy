(() => {
    const nav = document.querySelector('.buyer-top-nav');
    if (!nav) return;
    let revision = 0;
    window.updateBuyerNavCount = (key, value) => {
        const count = Number(value);
        if (!Number.isInteger(count) || count < 0) return;
        const badge = nav.querySelector(`[data-buyer-count="${key}"]`);
        const link = badge?.closest('[data-buyer-nav]');
        if (!link) return;
        revision++;
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.dataset.count = String(count);
        badge.classList.remove('hidden');
        const description = `${link.dataset.navLabel}: ${count} ${link.dataset.countLabel}`;
        link.title = description;
        link.setAttribute('aria-label', description);
    };

    if (!nav.dataset.countsUrl) return;
    let pending = false;
    let stopped = false;
    async function refresh() {
        if (pending || stopped || document.hidden) return;
        pending = true;
        const startedAt = revision;
        try {
            const response = await fetch(nav.dataset.countsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if ([401, 403].includes(response.status)) stopped = true;
            if (!response.ok) return;
            const data = await response.json();
            if (startedAt === revision) Object.entries(data.counts || {}).forEach(([key, value]) => window.updateBuyerNavCount(key, value));
        } catch (_) { /* Keep the last known counts if the connection is interrupted. */ }
        finally { pending = false; }
    }
    window.addEventListener('focus', refresh);
    window.addEventListener('pageshow', refresh);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.setInterval(refresh, 30000);
})();
