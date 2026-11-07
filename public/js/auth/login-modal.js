/**
 * Cartzy login modal, in two steps:
 *   1. "choose" — pick who you sign in as (Buyer, Seller, Logistics, Rider, Admin)
 *   2. "login"  — the sign-in form for that role (Riders are pointed to the mobile app)
 * - Storefront: links to /login open step 1; links to a role's login page open step 2 for that role.
 *   Other scripts can call window.cartzyLogin.open() or window.cartzyLogin.open('buyer').
 * - Standalone /login pages: already open; changing role also updates the address bar.
 */
(function () {
    'use strict';

    var overlay = document.getElementById('login-modal');
    if (!overlay) return;

    var form = overlay.querySelector('#lm-form');
    var standalone = overlay.classList.contains('is-standalone');
    var portals = JSON.parse(overlay.dataset.portals || '{}');
    var current = overlay.dataset.portal || 'buyer';
    var step = overlay.dataset.step || 'choose';
    var FLAG = 'cartzy-login-modal';
    var ROLE = 'cartzy-login-portal';
    var lastFocus = null;
    var closeTimer = null;

    function $(selector) { return overlay.querySelector(selector); }
    function stepEl(name) { return overlay.querySelector('[data-lm-step="' + name + '"]'); }

    function updateUrl() {
        if (!standalone || !window.history.replaceState) return;
        var key = step === 'login' && current !== 'courier' ? current : 'buyer';
        var data = portals[key];
        if (!data) return;
        window.history.replaceState(null, '', data.url);
        document.title = (key === 'buyer' ? 'Log In' : data.title + ' Log In') + ' | cartzy';
    }

    function showStep(name, options) {
        options = options || {};
        var changed = name !== step;
        step = name;
        ['choose', 'login'].forEach(function (n) {
            var el = stepEl(n);
            el.hidden = n !== name;
            el.classList.remove('is-entering-forward', 'is-entering-back');
        });
        var el = stepEl(name);
        if (changed && overlay.classList.contains('is-open')) {
            void el.offsetWidth; // restart the entrance animation
            el.classList.add(name === 'login' ? 'is-entering-forward' : 'is-entering-back');
        }
        updateUrl();
        if (options.focus !== false) focusStep();
    }

    function focusStep() {
        if (step === 'choose') {
            var role = stepEl('choose').querySelector('.lm-role[data-lm-role="' + current + '"]') || stepEl('choose').querySelector('.lm-role');
            if (role) role.focus({ preventScroll: true });
        } else if (current === 'courier') {
            var app = $('.lm-app-button');
            if (app) app.focus({ preventScroll: true });
        } else {
            var email = $('#lm-email');
            var target = email && email.value ? $('#lm-password') : email;
            if (target) target.focus({ preventScroll: true });
        }
    }

    function setRole(key, options) {
        options = options || {};
        var data = portals[key];
        if (!data) return;
        var changed = key !== current;
        current = key;

        var isApp = key === 'courier';
        $('[data-lm-section="app"]').hidden = !isApp;
        $('[data-lm-section="form"]').hidden = isApp;

        $('[data-lm-badge-icon]').innerHTML = data.icon;
        $('[data-lm-badge-label]').textContent = data.label;
        $('#lm-title').textContent = data.title;
        $('#lm-subtitle').textContent = data.subtitle;
        $('[data-lm-button]').textContent = data.button;
        $('[data-lm-portal-input]').value = key;
        $('[data-lm-forgot]').href = data.forgot;
        $('[data-lm-google]').href = data.google;
        $('[data-lm-register-row]').hidden = !data.register;
        if (data.register) $('[data-lm-register]').href = data.register;

        // An error from a previous attempt belongs to the role that was selected then.
        if (changed && !options.keepErrors) clearErrors();
    }

    function clearErrors() {
        var alert = $('.lm-alert-error');
        if (alert) alert.remove();
        Array.prototype.forEach.call(overlay.querySelectorAll('.is-invalid'), function (el) { el.classList.remove('is-invalid'); });
    }

    function focusables() {
        return Array.prototype.filter.call(
            stepEl(step).querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled])'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    function open(role) {
        if (role) setRole(role);
        showStep(role ? 'login' : 'choose', { focus: false });
        if (standalone) return focusStep();
        if (overlay.classList.contains('is-open')) return focusStep();
        clearTimeout(closeTimer);
        lastFocus = document.activeElement;
        overlay.hidden = false;
        document.documentElement.classList.add('lm-locked');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                overlay.classList.add('is-open');
                focusStep();
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

    overlay.addEventListener('click', function (event) {
        var roleTrigger = event.target.closest('[data-lm-role]');
        if (roleTrigger) {
            event.preventDefault();
            setRole(roleTrigger.dataset.lmRole);
            showStep('login');
            return;
        }
        if (event.target.closest('[data-lm-back]')) {
            clearErrors();
            showStep('choose');
            return;
        }
        if (event.target.closest('[data-lm-close]')) close();
    });

    // Arrow keys move through the role grid (left/right by one, up/down by one row).
    overlay.addEventListener('keydown', function (event) {
        var button = event.target.closest('.lm-role');
        var steps = { ArrowRight: 1, ArrowLeft: -1, ArrowDown: 2, ArrowUp: -2 };
        if (!button || !(event.key in steps || event.key === 'Home' || event.key === 'End')) return;
        event.preventDefault();
        var buttons = Array.prototype.slice.call(stepEl('choose').querySelectorAll('.lm-role'));
        var index = buttons.indexOf(button);
        var next = event.key === 'Home' ? 0
            : event.key === 'End' ? buttons.length - 1
            : Math.min(Math.max(index + steps[event.key], 0), buttons.length - 1);
        buttons[next].focus();
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

    var eye = $('[data-lm-toggle-password]');
    if (eye) {
        eye.addEventListener('click', function () {
            var input = $('#lm-password');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.setAttribute('aria-pressed', show ? 'true' : 'false');
            eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            eye.querySelector('svg').innerHTML = show
                ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        });
    }

    // Remember the attempt (and role) so a failed login reopens the form on the same role.
    if (form) {
        form.addEventListener('submit', function () {
            try {
                sessionStorage.setItem(ROLE, current);
                if (!standalone) sessionStorage.setItem(FLAG, '1');
            } catch (e) { /* storage blocked */ }
            var button = form.querySelector('.lm-submit');
            if (button) {
                button.classList.add('is-loading');
                setTimeout(function () { button.disabled = true; }, 0);
            }
        });
    }

    var storedRole = null;
    try {
        storedRole = sessionStorage.getItem(ROLE);
        sessionStorage.removeItem(ROLE);
    } catch (e) { /* storage blocked */ }

    if (standalone) {
        if (overlay.dataset.hasErrors === 'true' && storedRole && portals[storedRole]) {
            setRole(storedRole, { keepErrors: true });
            showStep('login', { focus: false });
        }
        return;
    }

    // Intercept links to the login pages: /login opens step 1, a role's page opens its form.
    function path(url) { return new URL(url, window.location.href).pathname.replace(/\/$/, ''); }
    var buyerPath = portals.buyer ? path(portals.buyer.url) : '/login';
    var rolesByPath = {};
    Object.keys(portals).forEach(function (key) {
        if (key !== 'courier' && key !== 'buyer') rolesByPath[path(portals[key].url)] = key;
    });

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('data-lm-skip') || overlay.contains(link) || link.target === '_blank') return;
        var url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        var linkPath = url.pathname.replace(/\/$/, '');
        if (linkPath === buyerPath) { event.preventDefault(); open(); }
        else if (rolesByPath[linkPath]) { event.preventDefault(); open(rolesByPath[linkPath]); }
    });

    var cameFromModal = false;
    try {
        cameFromModal = sessionStorage.getItem(FLAG) === '1';
        sessionStorage.removeItem(FLAG);
    } catch (e) { /* storage blocked */ }

    if (cameFromModal && overlay.dataset.hasErrors === 'true') {
        setRole(storedRole && portals[storedRole] ? storedRole : 'buyer', { keepErrors: true });
        open(current);
    } else {
        // Errors on this page belong to some other form, not the login.
        clearErrors();
    }
})();
