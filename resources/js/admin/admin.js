/** Shared admin navigation and confirmation behavior. */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-close]');
    function setOpen(open) {
        sidebar?.classList.toggle('is-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        if (overlay) overlay.hidden = !open;
        if (open) sidebar?.querySelector('a')?.focus();
    }
    toggle?.addEventListener('click', () => setOpen(!sidebar?.classList.contains('is-open')));
    overlay?.addEventListener('click', () => { setOpen(false); toggle?.focus(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            setOpen(false);
            toggle?.focus();
        }
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) setOpen(false);
    });
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
