(() => {
    const search = document.getElementById('assignment-search');
    const rows = [...document.querySelectorAll('[data-assignment-state]')];
    const filters = [...document.querySelectorAll('[data-assignment-filter]')];
    let status = 'all';
    const update = () => {
        const query = search.value.trim().toLocaleLowerCase();
        let count = 0;
        rows.forEach(row => {
            row.hidden = (status !== 'all' && row.dataset.assignmentState !== status) || !row.dataset.assignmentSearch.toLocaleLowerCase().includes(query);
            if (!row.hidden) count++;
        });
        document.getElementById('assignment-result-count').textContent = `${count} ${count === 1 ? 'parcel' : 'parcels'}`;
        document.getElementById('assignment-empty').hidden = count > 0;
        filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.assignmentFilter === status)));
    };
    search.addEventListener('input', update);
    filters.forEach(button => button.addEventListener('click', () => { status = button.dataset.assignmentFilter; update(); }));
    document.getElementById('assignment-clear').addEventListener('click', () => { search.value = ''; status = 'all'; update(); search.focus(); });
    document.querySelectorAll('.assignment-form').forEach(form => {
        const select = form.querySelector('select');
        const button = form.querySelector('button');
        const original = button.innerHTML;
        const updateButton = () => { button.disabled = !select.value || select.disabled; };
        select.addEventListener('change', updateButton);
        form.addEventListener('submit', event => {
            if (form.dataset.submitting || !select.value) { event.preventDefault(); return; }
            form.dataset.submitting = 'true'; button.disabled = true; button.textContent = 'Assigning…';
        });
        window.addEventListener('pageshow', () => { delete form.dataset.submitting; button.innerHTML = original; updateButton(); });
        updateButton();
    });
})();
