/** Independent product carousels. Keep public/js/picks-carousel.js in sync. */
(() => {
    function init() {
        const board = document.querySelector('[data-picks-board]');
        if (!board || board.dataset.initialized) return;
        board.dataset.initialized = 'true';
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const pause = board.querySelector('[data-picks-pause]');
        let paused = motion.matches;
        const schedulers = [];

        function updatePause() {
            pause.setAttribute('aria-label', paused ? 'Resume automatic product rotation' : 'Pause automatic product rotation');
            board.querySelector('[data-picks-pause-icon]').textContent = paused ? '▷' : 'Ⅱ';
            board.querySelector('[data-picks-pause-label]').textContent = paused ? 'Resume' : 'Pause';
            schedulers.forEach(schedule => schedule());
        }

        board.querySelectorAll('[data-pick-lane]').forEach(lane => {
            const slides = [...lane.querySelectorAll('[data-pick-slide]')];
            if (slides.length < 2) return;
            const track = lane.querySelector('[data-pick-track]');
            const controls = lane.querySelector('[data-pick-controls]');
            const nextButton = lane.querySelector('[data-pick-next]');
            let index = 0, hovered = false, focused = false, manual = false;
            let visible = !('IntersectionObserver' in window);
            let timer, touch;

            function schedule() {
                window.clearTimeout(timer);
                if (!paused && !manual && !hovered && !focused && visible && !document.hidden) {
                    timer = window.setTimeout(() => show(index + 1), Number(lane.dataset.pickInterval) || 7000);
                }
            }
            function warmImages() {
                slides[index].querySelector('img').loading = 'eager';
                slides[(index + 1) % slides.length].querySelector('img').loading = 'eager';
            }
            function show(next, userInitiated = false) {
                index = (next + slides.length) % slides.length;
                track.style.transform = `translateX(-${index * 100}%)`;
                slides.forEach((slide, i) => {
                    slide.inert = i !== index;
                    slide.setAttribute('aria-hidden', String(i !== index));
                });
                lane.querySelector('[data-pick-count]').textContent = `${String(index + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
                if (visible) warmImages();
                if (userInitiated) {
                    manual = true;
                    lane.querySelector('[data-pick-status]').textContent = slides[index].getAttribute('aria-label');
                }
                schedule();
            }
            lane.querySelector('[data-pick-prev]').addEventListener('click', () => show(index - 1, true));
            nextButton.addEventListener('click', () => show(index + 1, true));
            controls.addEventListener('keydown', event => {
                if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
                event.preventDefault();
                show(index + (event.key === 'ArrowRight' ? 1 : -1), true);
            });
            lane.addEventListener('mouseenter', () => { hovered = true; schedule(); });
            lane.addEventListener('mouseleave', () => { hovered = false; manual = false; schedule(); });
            lane.addEventListener('focusin', () => { focused = true; schedule(); });
            lane.addEventListener('focusout', event => {
                focused = lane.contains(event.relatedTarget);
                if (!focused) manual = false;
                schedule();
            });
            lane.addEventListener('touchstart', event => {
                touch = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null;
            }, { passive: true });
            lane.addEventListener('touchend', event => {
                if (!touch || event.touches.length) { touch = null; return; }
                const dx = event.changedTouches[0].clientX - touch.x;
                const dy = event.changedTouches[0].clientY - touch.y;
                touch = null;
                if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                    if (slides[index].contains(document.activeElement)) nextButton.focus({ preventScroll: true });
                    show(index + (dx < 0 ? 1 : -1), true);
                }
            }, { passive: true });
            lane.addEventListener('touchcancel', () => { touch = null; });
            pause.addEventListener('click', () => { manual = false; });
            if ('IntersectionObserver' in window) {
                new IntersectionObserver(([entry]) => {
                    visible = entry.isIntersecting;
                    if (visible) warmImages();
                    schedule();
                }, { threshold: 0.2 }).observe(lane);
            }
            schedulers.push(schedule);
            controls.hidden = false;
            show(0);
        });
        pause.addEventListener('click', () => { paused = !paused; updatePause(); });
        motion.addEventListener('change', () => {
            if (motion.matches) paused = true;
            updatePause();
        });
        document.addEventListener('visibilitychange', () => schedulers.forEach(schedule => schedule()));
        pause.hidden = false;
        updatePause();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
