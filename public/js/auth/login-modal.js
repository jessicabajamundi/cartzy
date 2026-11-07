/**
 * Cartzy login modal.
 * - People choose who they sign in as (Buyer, Seller, Logistics, Rider, Admin); the form updates in place.
 * - Storefront: clicking any link to a login page opens the modal on that role instead.
 *   Other scripts can call window.cartzyLogin.open('seller').
 * - Standalone /login pages: the modal is already open; switching role also updates the address bar.
 */
(function () {
    'use strict';

    var overlay = document.getElementById('login-modal');
    if (!overlay) return;

    var dialog = overlay.querySelector('.lm-dialog');
    var form = overlay.querySelector('#lm-form');
    var standalone = overlay.classList.contains('is-standalone');
    var portals = JSON.parse(overlay.dataset.portals || '{}');
    var current = overlay.dataset.portal || 'buyer';
    var FLAG = 'cartzy-login-modal';
    var ROLE = 'cartzy-login-portal';
    var lastFocus = null;
    var closeTimer = null;

    function $(selector) { return overlay.querySelector(selector); }
    function roleButtons() { return Array.prototype.slice.call(overlay.querySelectorAll('.lm-role')); }

    function setRole(key, options) {
        options = options || {};
        var data = portals[key];
        if (!data) return;
        var changed = key !== current;
        current = key;

        roleButtons().forEach(function (button) {
            var on = button.dataset.lmRole === key;
            button.setAttribute('aria-checked', on ? 'true' : 'false');
            button.tabIndex = on ? 0 : -1;
        });

        var isApp = key === 'courier';
        $('[data-lm-section="app"]').hidden = !isApp;
        $('[data-lm-section="form"]').hidden = isApp;

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

        if (standalone && changed && !isApp && window.history.replaceState) {
            window.history.replaceState(null, '', data.url);
            document.title = (key === 'buyer' ? 'Log In' : data.title + ' Log In') + ' | cartzy';
        }
    }

    function clearErrors() {
        var alert = $('.lm-alert-error');
        if (alert) alert.remove();
        Array.prototype.forEach.call(overlay.querySelectorAll('.is-invalid'), function (el) { el.classList.remove('is-invalid'); });
    }

    function focusables() {
        return Array.prototype.filter.call(
            dialog.querySelectorAll('a[href], button:not([disabled]):not([tabindex="-1"]), input:not([type="hidden"]):not([disabled])'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    function focusFirstField() {
        if (current === 'courier') return;
        var email = $('#lm-email');
        var target = email && email.value ? $('#lm-password') : email;
        if (target) target.focus({ preventScroll: true });
    }

    function open(role) {
        if (role) setRole(role);
        if (standalone || overlay.classList.contains('is-open')) return;
        clearTimeout(closeTimer);
        lastFocus = document.activeElement;
        overlay.hidden = false;
        document.documentElement.classList.add('lm-locked');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                overlay.classList.add('is-open');
                focusFirstField();
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

    window.cartzyLogin = { open: open, close: close, setRole: setRole };

    overlay.addEventListener('click', function (event) {
        var roleTrigger = event.target.closest('[data-lm-role]');
        if (roleTrigger) {
            event.preventDefault();
            setRole(roleTrigger.dataset.lmRole);
            var selected = overlay.querySelector('.lm-role[aria-checked="true"]');
            if (selected) selected.focus();
            return;
        }
        if (event.target.closest('[data-lm-close]')) close();
    });

    // Arrow keys move between roles, like a radio group.
    overlay.addEventListener('keydown', function (event) {
        var button = event.target.closest('.lm-role');
        if (!button) return;
        var keys = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 };
        if (!(event.key in keys) && event.key !== 'Home' && event.key !== 'End') return;
        event.preventDefault();
        var buttons = roleButtons();
        var index = buttons.indexOf(button);
        var next = event.key === 'Home' ? 0
            : event.key === 'End' ? buttons.length - 1
            : (index + keys[event.key] + buttons.length) % buttons.length;
        setRole(buttons[next].dataset.lmRole);
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

    // Remember the attempt (and role) so a failed login reopens the modal on the same role.
    if (form) {
        form.addEventListener('submit', function () {
            try {
                sessionStorage.setItem(ROLE, current);
                if (!standalone) sessionStorage.setItem(FLAG, '1');
            } catch (e) { /* storage blocked */ }
            var button = form.querySelector('.lm-submit');
            if (button) setTimeout(function () { button.disabled = true; }, 0);
        });
    }

    var storedRole = null;
    try {
        storedRole = sessionStorage.getItem(ROLE);
        sessionStorage.removeItem(ROLE);
    } catch (e) { /* storage blocked */ }

    if (standalone) {
        if (overlay.dataset.hasErrors === 'true' && storedRole) setRole(storedRole, { keepErrors: true });
        return;
    }

    // Intercept links to the login pages (except the Rider app page) and open the modal on that role.
    function path(url) { return new URL(url, window.location.href).pathname.replace(/\/$/, ''); }
    var rolesByPath = {};
    Object.keys(portals).forEach(function (key) {
        if (key !== 'courier') rolesByPath[path(portals[key].url)] = key;
    });

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('data-lm-skip') || overlay.contains(link) || link.target === '_blank') return;
        var url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        var role = rolesByPath[url.pathname.replace(/\/$/, '')];
        if (!role) return;
        event.preventDefault();
        open(role);
    });

    var cameFromModal = false;
    try {
        cameFromModal = sessionStorage.getItem(FLAG) === '1';
        sessionStorage.removeItem(FLAG);
    } catch (e) { /* storage blocked */ }

    if (cameFromModal && overlay.dataset.hasErrors === 'true') {
        if (storedRole) setRole(storedRole, { keepErrors: true });
        open();
    } else {
        // Errors on this page belong to some other form, not the login.
        clearErrors();
    }
})();
