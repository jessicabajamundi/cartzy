(() => {
    const STORAGE_KEY = 'cartzy_theme';

    function getTheme() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved === 'dark' || saved === 'light') return saved;
        } catch (_) {}
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.dataset.theme = theme;
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            btn.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });
    }

    applyTheme(getTheme());

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-theme-toggle]');
        if (!btn) return;
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch (_) {}
        applyTheme(next);
    });
})();
