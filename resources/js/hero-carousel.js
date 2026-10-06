/** Cartzy editorial carousel. Keep public/js/hero-carousel.js in sync. */
(() => {
    function initHeroCarousel() {
        const hero = document.querySelector('[data-market-hero]');
        if (!hero || hero.dataset.initialized) return;
        const slides = [...hero.querySelectorAll('[data-hero-slide]')];
        if (slides.length < 2) return;
        hero.dataset.initialized = 'true';

        const dots = [...hero.querySelectorAll('[data-hero-dot]')];
        const status = hero.querySelector('[data-hero-status]');
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let index = 0;
        let paused = motion.matches;
        let hovered = false;
        let focusWithin = false;
        let inView = true;
        let timer;
        let touchStart;

        function schedule() {
            window.clearTimeout(timer);
            if (!paused && !hovered && !focusWithin && !document.hidden && inView) {
                timer = window.setTimeout(() => show(index + 1), 6500);
            }
        }

        function show(next, manual = false) {
            index = (next + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                const active = i === index;
                slide.classList.toggle('is-active', active);
                slide.inert = !active;
                slide.setAttribute('aria-hidden', String(!active));
                dots[i].setAttribute('aria-current', String(active));
            });
            hero.querySelector('[data-hero-count]').textContent = `${String(index + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
            hero.querySelector('[data-hero-current-label]').textContent = slides[index].dataset.heroLabel;
            // Warm up the next image while this collection is being viewed.
            slides[(index + 1) % slides.length].querySelector('img').loading = 'eager';
            if (manual) {
                paused = true;
                status.textContent = slides[index].getAttribute('aria-label');
            }
            schedule();
        }

        hero.querySelector('[data-hero-prev]').addEventListener('click', () => show(index - 1, true));
        hero.querySelector('[data-hero-next]').addEventListener('click', () => show(index + 1, true));
        dots.forEach((dot, i) => dot.addEventListener('click', () => show(i, true)));
        hero.addEventListener('mouseenter', () => { hovered = true; schedule(); });
        hero.addEventListener('mouseleave', () => { hovered = false; schedule(); });
        hero.addEventListener('focusin', () => { focusWithin = true; schedule(); });
        hero.addEventListener('focusout', (event) => {
            focusWithin = hero.contains(event.relatedTarget);
            schedule();
        });
        hero.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            if (!event.target.closest('[data-hero-controls]')) return;
            event.preventDefault();
            show(index + (event.key === 'ArrowRight' ? 1 : -1), true);
        });
        hero.addEventListener('touchstart', (event) => {
            touchStart = event.touches.length === 1
                ? { x: event.touches[0].clientX, y: event.touches[0].clientY }
                : null;
        }, { passive: true });
        hero.addEventListener('touchend', (event) => {
            if (!touchStart || event.touches.length) { touchStart = null; return; }
            const dx = event.changedTouches[0].clientX - touchStart.x;
            const dy = event.changedTouches[0].clientY - touchStart.y;
            touchStart = null;
            if (Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                show(index + (dx < 0 ? 1 : -1), true);
            }
        }, { passive: true });
        hero.addEventListener('touchcancel', () => { touchStart = null; });
        document.addEventListener('visibilitychange', schedule);
        motion.addEventListener('change', () => {
            if (motion.matches) paused = true;
            schedule();
        });
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([entry]) => {
                inView = entry.isIntersecting;
                schedule();
            }, { threshold: 0.2 }).observe(hero);
        }
        hero.querySelector('[data-hero-controls]').hidden = false;
        show(0);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCarousel, { once: true });
    } else {
        initHeroCarousel();
    }
})();
