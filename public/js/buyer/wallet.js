(() => {
    const card = document.querySelector('[data-wallet-url]');
    if (!card) return;
    const money = n => '₱' + (n / 100).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    let busy = false;
    async function refresh() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const response = await fetch(card.dataset.walletUrl, {headers: {Accept: 'application/json'}, cache: 'no-store'});
            if (!response.ok) return;
            const wallet = await response.json();
            card.querySelector('[data-wallet-balance]').textContent = money(wallet.balance_minor);
            card.querySelector('[data-wallet-pending]').textContent = `Pending approved refunds: ${money(wallet.pending_refunds_minor)}. Pending amounts cannot be spent yet.`;
            const history = card.querySelector('[data-wallet-history]');
            history.replaceChildren();
            for (const entry of wallet.transactions) {
                const article = document.createElement('article');
                article.className = 'wallet-entry';
                const heading = document.createElement('strong');
                heading.textContent = entry.description;
                const amount = document.createElement('span');
                amount.textContent = ` ${entry.amount_minor > 0 ? '+' : '−'}${money(Math.abs(entry.amount_minor))}`;
                const detail = document.createElement('p');
                detail.textContent = `${entry.order_reference || ''} · ${entry.created_at}`;
                article.append(heading, amount, detail);
                history.append(article);
            }
            if (!wallet.transactions.length) history.textContent = 'Your completed refunds and wallet payments will appear here.';
        } catch (_) { /* Keep the last confirmed balance if offline. */ }
        finally { busy = false; }
    }
    const timer = setInterval(refresh, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => clearInterval(timer), {once: true});
})();
