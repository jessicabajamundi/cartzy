<script>
    (function() {
        // Prevent browser Back/Forward cache (bfcache) from restoring stale authenticated session pages
        window.addEventListener('pageshow', function(event) {
            var navEntry = (window.performance && window.performance.getEntriesByType)
                ? window.performance.getEntriesByType('navigation')[0]
                : null;
            var isBackForward = event.persisted ||
                (navEntry && navEntry.type === 'back_forward') ||
                (window.performance && window.performance.navigation && window.performance.navigation.type === 2);

            if (isBackForward) {
                window.location.reload();
            }
        });
    })();
</script>
