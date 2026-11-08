/**
 * Cozy peeks at the login and sign-up modals: every so often her head rises over an edge of the open dialog
 * (top, left or right), her paws grip the edge and her eyes follow what you are doing.
 * After a few seconds she ducks back down, then pops up somewhere else. Clicking her makes her duck.
 * Skipped entirely for people who prefer reduced motion.
 */
(function () {
    'use strict';

    var overlay = document.getElementById('login-modal') || document.getElementById('signup-modal');
    if (!overlay) return;
    var peek = overlay.querySelector('[data-lm-peek]');
    var paws = overlay.querySelector('[data-lm-peek-paws]');
    var pupils = overlay.querySelector('[data-lm-peek-pupils]');
    if (!peek || !paws || !pupils) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var FIRST_DELAY = 2500;          // after the modal opens
    var SHOW_FOR = [6000, 9000];     // how long she stays up
    var HIDE_FOR = [4000, 8000];     // how long she stays down before the next peek
    var SIDE_ROOM = 60;              // px needed beside the dialog to peek from the left/right

    // Where along each edge she may appear (fractions of the edge), clear of the corners and the buttons.
    var EDGES = {
        top: { rotate: 0, from: 0.2, to: 0.68 },
        left: { rotate: -90, from: 0.3, to: 0.78 },
        right: { rotate: 90, from: 0.3, to: 0.78 }
    };

    var spot = null;
    var lastEdge = null;
    var timer = null;
    var frame = null;
    var target = null;

    function rand(range) { return range[0] + Math.random() * (range[1] - range[0]); }
    function dialog() { return overlay.querySelector('.lm-dialog:not([hidden])'); }
    function isOpen() { return !overlay.hidden && overlay.classList.contains('is-open'); }

    function edgesWithRoom(rect) {
        var names = ['top'];
        if (rect.left >= SIDE_ROOM) names.push('left');
        if (window.innerWidth - rect.right >= SIDE_ROOM) names.push('right');
        return names;
    }

    function pickSpot() {
        var d = dialog();
        if (!d) return false;
        var names = edgesWithRoom(d.getBoundingClientRect());
        var fresh = names.filter(function (n) { return n !== lastEdge; });
        var edge = (fresh.length ? fresh : names)[Math.floor(Math.random() * (fresh.length || names.length))];
        lastEdge = edge;
        spot = { edge: edge, at: EDGES[edge].from + Math.random() * (EDGES[edge].to - EDGES[edge].from) };
        return true;
    }

    // Keep her on the chosen point of the edge, even while the dialog moves or changes size.
    function place() {
        var d = dialog();
        if (!d || !spot) return false;
        var r = d.getBoundingClientRect();
        var o = overlay.getBoundingClientRect();
        var x = spot.edge === 'top' ? r.left + r.width * spot.at : spot.edge === 'left' ? r.left : r.right;
        var y = spot.edge === 'top' ? r.top : r.top + r.height * spot.at;
        spot.x = x;
        spot.y = y;
        [peek, paws].forEach(function (el) {
            el.style.left = (x - o.left) + 'px';
            el.style.top = (y - o.top) + 'px';
            el.style.setProperty('--peek-rotate', EDGES[spot.edge].rotate + 'deg');
        });
        return true;
    }

    // Turn her pupils toward the pointer (or whatever has keyboard focus).
    function look() {
        if (!target || !spot) return;
        var angle = EDGES[spot.edge].rotate * Math.PI / 180;
        var vx = target.x - spot.x;
        var vy = target.y - spot.y;
        var lx = vx * Math.cos(angle) + vy * Math.sin(angle);
        var ly = -vx * Math.sin(angle) + vy * Math.cos(angle);
        var len = Math.sqrt(lx * lx + ly * ly) || 1;
        pupils.style.transform = 'translate(' + (lx / len * 6).toFixed(2) + 'px,' + (ly / len * 5).toFixed(2) + 'px)';
    }

    function loop() {
        place();
        look();
        frame = window.requestAnimationFrame(loop);
    }

    function stopLoop() {
        if (frame) window.cancelAnimationFrame(frame);
        frame = null;
    }

    function schedule(fn, ms) {
        window.clearTimeout(timer);
        timer = window.setTimeout(fn, ms);
    }

    function peekOut() {
        if (!isOpen() || document.hidden || !pickSpot() || !place()) {
            schedule(peekOut, 2000);
            return;
        }
        peek.classList.remove('is-ducking');
        peek.classList.add('is-ready');
        paws.classList.add('is-ready');
        void peek.offsetWidth; // let the new position settle before she rises
        peek.classList.add('is-peeking');
        paws.classList.add('is-peeking');
        stopLoop();
        loop();
        schedule(duck, rand(SHOW_FOR));
    }

    function duck() {
        peek.classList.remove('is-peeking');
        paws.classList.remove('is-peeking');
        peek.classList.add('is-ducking');
        window.setTimeout(function () {
            if (peek.classList.contains('is-peeking')) return;
            stopLoop();
            peek.classList.remove('is-ready', 'is-ducking');
            paws.classList.remove('is-ready');
        }, 450);
        schedule(peekOut, rand(HIDE_FOR));
    }

    function reset() {
        window.clearTimeout(timer);
        stopLoop();
        peek.classList.remove('is-ready', 'is-peeking', 'is-ducking');
        paws.classList.remove('is-ready', 'is-peeking');
    }

    var wasOpen = false;
    function sync() {
        var open = isOpen();
        if (open === wasOpen) return;
        wasOpen = open;
        reset();
        if (open) schedule(peekOut, FIRST_DELAY);
    }

    overlay.addEventListener('pointermove', function (event) {
        target = { x: event.clientX, y: event.clientY };
    });
    overlay.addEventListener('focusin', function (event) {
        var r = event.target.getBoundingClientRect();
        target = { x: r.left + r.width / 2, y: r.top + r.height / 2 };
    });
    peek.addEventListener('click', function () {
        if (peek.classList.contains('is-peeking')) duck();
    });

    new MutationObserver(sync).observe(overlay, { attributes: true, attributeFilter: ['class', 'hidden'] });
    sync();
})();
