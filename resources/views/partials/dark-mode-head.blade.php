<script>
(() => {
    try {
        const saved = localStorage.getItem('cartzy_theme');
        const theme = (saved === 'dark' || saved === 'light')
            ? saved
            : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.dataset.theme = theme;
    } catch (_) {}
})();
</script>
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}?v={{ filemtime(public_path('css/dark-mode.css')) }}">
<script defer src="{{ asset('js/dark-mode.js') }}?v={{ filemtime(public_path('js/dark-mode.js')) }}"></script>
