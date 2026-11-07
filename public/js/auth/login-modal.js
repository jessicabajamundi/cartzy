/**
 * Cartzy login modal.
 * - Storefront: clicking any link to the buyer login page opens the modal instead.
 *   Other scripts can call window.cartzyLogin.open().
 * - Standalone /login pages: the modal is already open; it only wires up the form.
 */
(function () {
    'use strict';

    var overlay = document.getElementById('login-modal');
    if (!overlay) return;

    var dialog = overlay.querySelector('.lm-dialog');
    var form = overlay.querySelector('#lm-form');
    var standalone = overlay.classList.contains('is-standalone');
    var FLAG = 'cartzy-login-modal';
    var lastFocus = null;
    var closeTimer = null;

    function focusables() {
        return Array.prototype.filter.call(
            dialog.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled])'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    function open() {
        if (standalone || overlay.classList.contains('is-open')) return;
        clearTimeout(closeTimer);
        lastFocus = document.activeElement;
        overlay.hidden = false;
        document.documentElement.classList.add('lm-locked');
        // Next frame so the entrance transition runs.
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                overlay.classList.add('is-open');
                var email = overlay.querySelector('#lm-email');
                var target = email && email.value ? overlay.querySelector('#lm-password') : email;
                if (target) target.focus({ preventScroll: true });
            });
        });
    }

    function close() {
        if (standalone || !overlay.classList.contains('is-open')) return;
        overlay.classList.remove('is-open');
        document.documentElement.classList.remove('lm-locked');
        closeTimer = setTimeout(function () { overlay.hidden = true; }, 300);
        if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }

    window.cartzyLogin = { open: open, close: close };

    // Close controls (backdrop + X button). In standalone mode the X is a link to the shop.
    overlay.addEventListener('click', function (event) {
        if (event.target.closest('[data-lm-close]')) close();
    });

    document.addEventListener('keydown', function (event) {
        if (!overlay.classList.contains('is-open')) return;
        if (event.key === 'Escape' && !standalone) {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab') {
            var items = focusables();
            if (!items.length) return;
            var first = items[0];
            var last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });

    // Show / hide password.
    var eye = overlay.querySelector('[data-lm-toggle-password]');
    if (eye) {
        eye.addEventListener('click', function () {
            var input = overlay.querySelector('#lm-password');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.setAttribute('aria-pressed', show ? 'true' : 'false');
            eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            eye.querySelector('svg').innerHTML = show
                ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        });
    }

    // Remember that the attempt came from the modal so a failed login reopens it on the page we return to.
    if (form) {
        form.addEventListener('submit', function () {
            if (!standalone) {
                try { sessionStorage.setItem(FLAG, '1'); } catch (e) { /* storage blocked */ }
            }
            var button = form.querySelector('.lm-submit');
            if (button) setTimeout(function () { button.disabled = true; }, 0);
        });
    }

    if (standalone) return;

    // Intercept links to the buyer login page.
    var loginPath = new URL(overlay.dataset.loginUrl, window.location.href).pathname.replace(/\/$/, '');
    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('data-lm-skip') || overlay.contains(link) || link.target === '_blank') return;
        var url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin || url.pathname.replace(/\/$/, '') !== loginPath) return;
        event.preventDefault();
        open();
    });

    // Reopen after a failed login submitted from the modal.
    var cameFromModal = false;
    try {
        cameFromModal = sessionStorage.getItem(FLAG) === '1';
        sessionStorage.removeItem(FLAG);
    } catch (e) { /* storage blocked */ }
    if (cameFromModal && overlay.dataset.hasErrors === 'true') {
        open();
    } else {
        // Errors on this page belong to some other form, not the login.
        var alert = overlay.querySelector('.lm-alert-error');
        if (alert) alert.remove();
        Array.prototype.forEach.call(overlay.querySelectorAll('.is-invalid'), function (el) { el.classList.remove('is-invalid'); });
    }
})();
