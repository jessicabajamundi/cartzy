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
            const countBadge = card.querySelector('[data-wallet-count]');
            if (countBadge) countBadge.textContent = String(wallet.transactions.length);
            history.replaceChildren();
            for (const entry of wallet.transactions) {
                const article = document.createElement('article');
                article.className = 'wallet-entry';
                const main = document.createElement('div');
                main.className = 'wallet-entry-main';
                const heading = document.createElement('strong');
                heading.textContent = entry.description;
                const detail = document.createElement('p');
                detail.textContent = `${entry.order_reference || ''} · ${entry.created_at}`;
                main.append(heading, detail);

                const amount = document.createElement('span');
                const isCredit = entry.amount_minor > 0;
                amount.className = `wallet-entry-amount ${isCredit ? 'is-credit' : 'is-debit'}`;
                amount.textContent = `${isCredit ? '+' : '−'}${money(Math.abs(entry.amount_minor))}`;

                article.append(main, amount);
                history.append(article);
            }
            if (!wallet.transactions.length) {
                const empty = document.createElement('div');
                empty.className = 'wallet-empty';
                const title = document.createElement('strong');
                title.textContent = 'No transactions yet';
                const sub = document.createElement('p');
                sub.textContent = 'Your completed refunds and wallet payments will appear here.';
                empty.append(title, sub);
                history.append(empty);
            }
        } catch (_) { /* Keep the last confirmed balance if offline. */ }
        finally { busy = false; }
    }
    const timer = setInterval(refresh, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => clearInterval(timer), {once: true});
})();
