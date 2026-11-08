/**
 * Sign-up modal, in two steps (like the login modal):
 *   1. "choose": Sign up as Buyer, Seller, Logistics, Rider or Courier.
 *      Rider and Courier are links to the rider application; the others open step 2 here.
 *   2. "form": the sign-up form for that role. The role badge ("Switch") goes back to step 1.
 * Relies on window.selectRole() from js/auth/register.js to set the form up for a role.
 */
(function () {
    'use strict';

    var choose = document.querySelector('[data-su-step="choose"]');
    var form = document.querySelector('[data-su-step="form"]');
    if (!choose || !form) return;

    var roles = JSON.parse(choose.dataset.suRoles || '{}');
    var dialog = choose.closest('.lm-dialog');
    var panel = choose.closest('.lm-panel');
    var badgeIcon = form.querySelector('[data-su-badge-icon]');
    var badgeLabel = form.querySelector('[data-su-badge-label]');

    function setUrlRole(role) {
        if (!window.history.replaceState) return;
        var url = new URL(window.location.href);
        if (role) url.searchParams.set('role', role); else url.searchParams.delete('role');
        window.history.replaceState(null, '', url);
    }

    function show(step) {
        var target = step === 'form' ? form : choose;
        [choose, form].forEach(function (el) {
            el.hidden = el !== target;
            el.classList.remove('is-entering-forward', 'is-entering-back');
        });
        void target.offsetWidth; // restart the entrance animation
        target.classList.add(step === 'form' ? 'is-entering-forward' : 'is-entering-back');
        if (dialog) dialog.setAttribute('aria-labelledby', step === 'form' ? 'authTitle' : 'su-choose-title');
        if (panel) panel.scrollTop = 0;
    }

    choose.addEventListener('click', function (event) {
        var button = event.target.closest('[data-su-role]');
        if (!button) return;
        var role = button.dataset.suRole;
        if (typeof window.selectRole === 'function') window.selectRole(role);
        if (roles[role]) {
            badgeIcon.innerHTML = roles[role].icon;
            badgeLabel.textContent = roles[role].label;
        }
        setUrlRole(role);
        show('form');
        var first = document.getElementById('first_name');
        if (first) first.focus({ preventScroll: true });
    });

    form.querySelector('[data-su-back]').addEventListener('click', function () {
        var current = (document.getElementById('role_input') || {}).value;
        setUrlRole(null);
        show('choose');
        var button = choose.querySelector('[data-su-role="' + current + '"]') || choose.querySelector('.lm-role');
        if (button) button.focus({ preventScroll: true });
    });
})();
