export function previewPrice(priceMinor, type, rawValue) {
    if (!type) return { price: priceMinor, saving: 0, error: '' };
    const value = Number(rawValue);
    if (!Number.isFinite(value) || value < 0.01 || value > 99999999.99) {
        return { price: null, saving: 0, error: 'Enter a discount greater than zero.' };
    }
    if (Math.abs(value * 100 - Math.round(value * 100)) > 0.00001) {
        return { price: null, saving: 0, error: 'Use no more than two decimal places.' };
    }
    if (type === 'percentage' && value > 99.99) {
        return { price: null, saving: 0, error: 'Percentage must be between 0.01 and 99.99.' };
    }
    const amount = Math.round(value * 100);
    if (type === 'fixed' && amount >= priceMinor) {
        return { price: null, saving: 0, error: 'The amount must be less than every variation price.' };
    }
    const discount = type === 'percentage' ? Math.round(priceMinor * amount / 10000) : amount;
    const price = Math.max(1, priceMinor - discount);
    return { price, saving: priceMinor - price, error: '' };
}

export function scheduleError(type, start, end, now = Date.now()) {
    if (!type || !end) return '';
    // datetime-local values are always interpreted as Philippine time, not the browser timezone.
    const endsAt = Date.parse(`${end}+08:00`);
    const startsAt = start ? Date.parse(`${start}+08:00`) : now;
    return !Number.isFinite(endsAt) || !Number.isFinite(startsAt) || endsAt <= now || endsAt <= startsAt
        ? 'End time must be in the future and later than the start time (PHT).'
        : '';
}

if (typeof document !== 'undefined') {
    document.querySelectorAll('[data-product-discount]').forEach(panel => {
        const editor = panel.closest('.sale-editor');
        const form = panel.closest('form');
        const value = panel.querySelector('[name="discount_value"]');
        const start = panel.querySelector('[name="discount_starts_at"]');
        const end = panel.querySelector('[name="discount_ends_at"]');
        const rows = [...editor.querySelectorAll('[data-price-minor]')];
        const money = minor => `₱${(minor / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const dateLabel = raw => new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
        }).format(new Date(`${raw}+08:00`)) + ' PHT';
        const update = () => {
            const type = panel.querySelector('[name="discount_type"]:checked').value;
            value.disabled = !type;
            value.required = Boolean(type);
            value.max = type === 'percentage' ? '99.99' : '99999999.99';
            start.disabled = end.disabled = !type;
            panel.querySelector('[data-discount-label]').textContent = type === 'percentage' ? 'Percentage off (%)' : 'Amount off (₱)';
            panel.querySelector('.sale-presets').hidden = type !== 'percentage';
            panel.querySelectorAll('[data-sale-preset]').forEach(button => {
                button.setAttribute('aria-pressed', String(type === 'percentage' && Number(button.dataset.salePreset) === Number(value.value)));
            });
            const previews = rows.map(row => previewPrice(Number(row.dataset.priceMinor), type, value.value));
            const error = previews.find(preview => preview.error)?.error || '';
            value.setCustomValidity(error);
            value.setAttribute('aria-invalid', String(Boolean(error)));
            panel.querySelector('#sale-value-error').textContent = error;
            rows.forEach((row, index) => {
                const preview = previews[index];
                row.classList.toggle('has-discount', !error && preview.saving > 0);
                row.querySelector('[data-preview-price]').textContent = error ? '—' : money(preview.price);
                row.querySelector('[data-preview-saving]').textContent = error ? 'Check discount' : preview.saving > 0 ? `Save ${money(preview.saving)}` : 'Regular price';
            });
            editor.querySelector('[data-offer-summary]').textContent = !type ? 'Regular pricing · No discount' : error ? 'Enter a valid discount to preview' : type === 'percentage' ? `${Number(value.value)}% off every item` : `${money(Math.round(Number(value.value) * 100))} off every item`;
            const timingError = scheduleError(type, start.value, end.value);
            end.setCustomValidity(timingError);
            end.setAttribute('aria-invalid', String(Boolean(timingError)));
            panel.querySelector('[data-schedule-error]').textContent = timingError;
            editor.querySelector('[data-preview-start]').textContent = !type ? 'No sale scheduled' : start.value ? dateLabel(start.value) : 'Immediately after saving';
            editor.querySelector('[data-preview-end]').textContent = !type ? '—' : end.value ? dateLabel(end.value) : 'No end date';
        };
        panel.addEventListener('input', update);
        panel.addEventListener('change', update);
        panel.querySelectorAll('[data-sale-preset]').forEach(button => {
            button.addEventListener('click', () => { value.value = button.dataset.salePreset; update(); });
        });
        form.addEventListener('submit', event => {
            update();
            if (!form.reportValidity()) event.preventDefault();
        });
        update();
    });
}
