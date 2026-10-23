(() => {
    const dialog = document.getElementById('restock-dialog');
    if (!dialog) return;
    dialog.querySelectorAll('[data-restock-close]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });
    // Clicking the dimmed backdrop also closes the reminder.
    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    document.querySelectorAll('[data-restock-open]').forEach(button => {
        button.addEventListener('click', () => dialog.showModal());
    });
    // Opens by itself only on the first seller page after signing in (decided by the server).
    if (dialog.dataset.restockAutoopen === 'true') dialog.showModal();
})();
