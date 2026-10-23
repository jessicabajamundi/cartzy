(() => {
    const form = document.getElementById('sv-form');
    if (!form) return;
    const field = name => form.elements[name];
    const checked = name => form.querySelector(`[name="${name}"]:checked`)?.value;
    const num = name => Number(field(name)?.value) || 0;
    const peso = value => '₱' + Number(value).toLocaleString('en-PH', { maximumFractionDigits: 2 });
    const set = (selector, text) => document.querySelectorAll(selector).forEach(el => { el.textContent = text; });
    const date = value => value ? new Date(value + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

    function render() {
        const type = checked('discount_type') || 'fixed';
        const audience = checked('audience') || 'public';
        form.querySelectorAll('[data-type-field]').forEach(el => { el.hidden = el.dataset.typeField !== type; });
        form.querySelectorAll('[data-audience-field]').forEach(el => { el.hidden = el.dataset.audienceField !== audience; });

        const amount = num('amount'), percent = num('percent'), cap = num('max_discount'), minSpend = num('min_spend'), shipCap = num('shipping_cap');
        const headline = type === 'percent' ? `${percent || 0}% OFF`
            : type === 'free_shipping' ? (shipCap ? `${peso(shipCap)} OFF SHIPPING` : 'FREE SHIPPING')
            : `${peso(amount)} OFF`;
        const terms = [minSpend > 0 ? `Min. spend ${peso(minSpend)}` : 'No min. spend'];
        if (type === 'percent' && cap) terms.push(`Up to ${peso(cap)}`);
        if (field('ends_at')?.value) terms.push('Until ' + date(field('ends_at').value));
        set('[data-preview-headline]', headline);
        set('[data-preview-audience]', audience === 'code' ? (field('code').value.trim().toUpperCase() || 'CODE') : (type === 'free_shipping' ? 'SHIPPING' : 'SHOP VOUCHER'));
        document.querySelector('.sv-ticket')?.classList.toggle('is-shipping', type === 'free_shipping');
        set('[data-preview-name]', field('name').value.trim() || 'Your voucher name');
        set('[data-preview-terms]', terms.join(' · '));
        set('[data-preview-dates]', `${date(field('starts_at')?.value)} – ${date(field('ends_at')?.value)}`);

        // A worked example makes the deal concrete: a cart that just meets the minimum spend.
        const basket = Math.max(minSpend, type === 'fixed' ? amount * 2 : 500);
        if (type === 'free_shipping') {
            set('[data-preview-example]', `${peso(basket)} of your items → ${shipCap ? `up to ${peso(shipCap)} off your parcel's shipping` : 'your parcel ships free'}`);
        } else {
            const off = type === 'percent' ? Math.min(basket * percent / 100, cap || Infinity) : Math.min(amount, basket);
            set('[data-preview-example]', basket ? `${peso(basket)} of your items → buyer pays ${peso(Math.max(0, basket - off))}` : '—');
        }

        const hint = form.querySelector('[data-min-spend-hint]');
        if (hint) {
            const lowSpend = type === 'fixed' && amount > 0 && minSpend < amount * 2;
            hint.textContent = lowSpend
                ? `Set at least ${peso(amount * 2)} (twice the discount) so each order stays profitable.`
                : "Counts only your items in the buyer's cart.";
            hint.classList.toggle('is-warning', lowSpend);
        }
    }

    form.addEventListener('input', render);
    form.addEventListener('change', render);

    form.querySelectorAll('[data-run-days]').forEach(button => button.addEventListener('click', () => {
        const start = field('starts_at').value ? new Date(field('starts_at').value + 'T00:00:00') : new Date();
        start.setDate(start.getDate() + Number(button.dataset.runDays));
        const pad = n => String(n).padStart(2, '0');
        field('ends_at').value = `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`;
        render();
    }));

    form.querySelector('[data-generate-code]')?.addEventListener('click', () => {
        const base = (field('name').value || 'SHOP').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8) || 'SHOP';
        field('code').value = base + Math.floor(100 + Math.random() * 900);
        render();
    });

    field('code')?.addEventListener('input', event => {
        event.target.value = event.target.value.toUpperCase().replace(/\s+/g, '');
    });

    render();
})();
