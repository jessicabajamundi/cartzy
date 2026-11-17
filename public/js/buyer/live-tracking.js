(() => {
    const panel = document.querySelector('[data-tracking-url]');
    if (!panel) return;
    let inFlight = false;
    const rendered = {};
    async function refresh() {
        if (inFlight || document.hidden) return;
        inFlight = true;
        try {
            const response = await fetch(panel.dataset.trackingUrl, {
                headers: { Accept: 'application/json' }, cache: 'no-store',
            });
            if (!response.ok) return;
            const fragments = await response.json();
            for (const name of ['summary', 'timeline']) {
                const target = panel.querySelector(`[data-tracking-fragment="${name}"]`);
                if (!target || rendered[name] === fragments[name]) continue;
                const expanded = target.querySelector('details')?.open;
                target.innerHTML = fragments[name];
                rendered[name] = fragments[name];
                if (expanded) target.querySelector('details')?.setAttribute('open', '');
            }
        } catch (_) {
            // Preserve the latest confirmed information during connection loss.
        } finally {
            inFlight = false;
        }
    }
    const timer = setInterval(refresh, 3000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh();
    });
    window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
})();
