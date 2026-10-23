(() => {
    document.querySelectorAll('[data-pickup-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting) { event.preventDefault(); return; }
            form.dataset.submitting = 'true';
            const button = form.querySelector('button');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.querySelector('span').textContent = 'Confirming…';
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-pickup-confirm]').forEach(form => {
            delete form.dataset.submitting;
            const button = form.querySelector('button');
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.querySelector('span').textContent = 'Confirm pickup';
        });
    });
})();
