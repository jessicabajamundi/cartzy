/**
 * Logistics sign-up in 7 short steps, so the sign-up modal never needs scrolling:
 *   1 Your company · 2 Contact person · 3 Hub location · 4 Hub address · 5 Documents
 *   6 Sign-in details · 7 Verify your email
 * Steps 1-5 are pages inside the form's first section (register.js "step-1"); steps 6 and 7 are
 * register.js's own sign-in and verify sections, so the email code is still sent by register.js.
 * Only active while the role is "logistics"; buyers and sellers keep the 3-step form.
 */
(function () {
    'use strict';

    var container = document.querySelector('.su-step-form');
    var form = document.getElementById('registerForm');
    if (!container || !form || typeof window.selectRole !== 'function') return;

    var STEPS = [
        ['Your company', "The delivery company you'll run on cartzy."],
        ['Contact person', 'Who sellers, riders and cartzy should contact at your hub.'],
        ['Hub location', 'Where your sorting hub is.'],
        ['Hub address', 'The street address of your hub.'],
        ['Documents', 'So cartzy can verify your company before approving it.'],
        ['Sign-in details', "The email and password you'll log in with."],
        ['Verify your email', 'Enter the 6-digit code we sent to your email.']
    ];
    var LAST_PAGE = 5; // steps 6 and 7 belong to register.js
    var MAX_FILE = 5 * 1024 * 1024;

    var page = 1;
    var active = false;
    var busy = false;
    var progress = document.getElementById('logisticsProgress');
    var nextButton = container.querySelector('[data-lg-next]');
    var backButton = container.querySelector('[data-lg-back]');
    var step2 = document.getElementById('step-2');
    var step3 = document.getElementById('step-3');

    function $(id) { return document.getElementById(id); }
    function shown(el) { return el && el.style.display !== 'none'; }

    /** An error line under a field; created when the markup doesn't have one (e.g. company name). */
    function setError(id, message, focus) {
        var input = $(id);
        if (!input) return !message;
        var error = $(id + '_error');
        if (!error && message) {
            error = document.createElement('p');
            error.id = id + '_error';
            error.className = 'seller-field-error';
            error.setAttribute('role', 'alert');
            (input.closest('.input-group') || input).insertAdjacentElement('afterend', error);
        }
        if (error) {
            error.textContent = message || '';
            error.hidden = !message;
        }
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        input.style.borderColor = message ? '#ef4444' : '';
        if (message && focus) input.focus();
        return !message;
    }

    function lgInputs() {
        return container.querySelectorAll('.lg-only input, .lg-only select');
    }

    /** Logistics fields only count (and are only sent) while signing up as logistics. */
    function setActive(on) {
        active = on;
        container.classList.toggle('lg-steps', on);
        lgInputs().forEach(function (input) {
            if (!on) {
                if (input.dataset.lgWasDisabled === undefined) input.dataset.lgWasDisabled = input.disabled ? '1' : '';
                input.disabled = true;
            } else if (input.dataset.lgWasDisabled !== undefined) {
                input.disabled = input.dataset.lgWasDisabled === '1';
                delete input.dataset.lgWasDisabled;
            }
        });
        render();
    }

    function overallStep() {
        if (shown(step3)) return 7;
        if (shown(step2)) return 6;
        return page;
    }

    function render() {
        container.querySelectorAll('[data-lg-page]').forEach(function (el) {
            el.classList.toggle('is-lg-current', Number(el.dataset.lgPage) === page);
        });
        if (!active || !progress) return;
        var step = overallStep();
        progress.querySelector('[data-lg-number]').textContent = step;
        progress.querySelector('[data-lg-title]').textContent = STEPS[step - 1][0];
        progress.querySelector('[data-lg-hint]').textContent = STEPS[step - 1][1];
        progress.querySelector('[data-lg-fill]').style.width = (step / STEPS.length * 100) + '%';
        progress.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', step);
        nextButton.querySelector('[data-lg-next-label]').textContent = page === LAST_PAGE ? 'Continue to sign-in' : 'Continue';
    }

    function scrollTop() {
        var panel = form.closest('.lm-panel');
        if (panel) panel.scrollTop = 0;
    }

    function focusPage() {
        var first = container.querySelector('[data-lg-page="' + page + '"] input:not([type="hidden"]):not([disabled]), [data-lg-page="' + page + '"] select:not([disabled])');
        if (first) first.focus({ preventScroll: true });
    }

    function checkFile(id) {
        var input = $(id);
        var file = input.files && input.files[0];
        var message = '';
        if (!file) message = 'Please choose a file.';
        else if (file.size > MAX_FILE) message = 'File must not exceed 5 MB.';
        else if (!/\.(jpe?g|png|pdf)$/i.test(file.name)) message = 'Choose a JPG, PNG or PDF file.';
        return setError(id, message);
    }

    /** Checks the fields of one page; focuses the first problem. */
    async function validatePage(p) {
        var ok = true;
        if (p === 1) {
            var company = $('business_name');
            company.value = company.value.trim();
            ok = setError('business_name', company.value ? '' : 'Please enter your company name.', true);
        } else if (p === 2) {
            ok = window.validateRegistrationNames(true);
            if (ok) {
                var phone = $('phone');
                ok = phone.value.trim()
                    ? window.validateRegistrationPhone(true) && await window.checkPhoneAvailability(true)
                    : setError('phone', 'Please enter a mobile number for your hub.', true);
            }
        } else if (p === 3) {
            [['hub_region', 'region'], ['hub_province', 'province'], ['hub_city', 'city or municipality'], ['hub_barangay', 'barangay']].forEach(function (pair) {
                var select = $(pair[0]);
                var fine = setError(pair[0], select.value && !select.disabled ? '' : 'Please choose a ' + pair[1] + '.');
                if (!fine && ok) select.focus();
                ok = ok && fine;
            });
        } else if (p === 4) {
            var street = $('hub_street_address');
            street.value = street.value.trim();
            var postal = $('hub_postal_code');
            var streetOk = setError('hub_street_address', street.value ? '' : 'Please enter the street address.', true);
            var postalOk = setError('hub_postal_code', /^\d{4}$/.test(postal.value.trim()) ? '' : 'Enter the 4-digit postal code.', streetOk);
            ok = streetOk && postalOk;
        } else if (p === 5) {
            var idOk = checkFile('lg_id_photo');
            var permitOk = checkFile('lg_dti_permit');
            ok = idOk && permitOk;
            if (!idOk) $('lg_id_photo').focus(); else if (!permitOk) $('lg_dti_permit').focus();
        }
        return ok;
    }

    async function next() {
        if (busy) return;
        busy = true;
        nextButton.disabled = true;
        try {
            if (!await validatePage(page)) return;
            if (page < LAST_PAGE) {
                page++;
                render();
                scrollTop();
                focusPage();
            } else if (typeof window.nextStep === 'function') {
                await window.nextStep(2); // register.js: on to sign-in details (step 6)
            }
        } finally {
            busy = false;
            nextButton.disabled = false;
        }
    }

    function back() {
        if (page > 1) {
            page--;
            render();
            scrollTop();
            focusPage();
        } else {
            var roles = document.querySelector('[data-su-back]'); // step 1: back to "Sign up as..."
            if (roles) roles.click();
        }
    }

    nextButton.addEventListener('click', next);
    backButton.addEventListener('click', back);

    // Enter in a field moves to the next step instead of submitting the form early.
    form.addEventListener('keydown', function (event) {
        if (!active || event.key !== 'Enter' || shown(step2) || shown(step3)) return;
        if (event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') return;
        event.preventDefault();
        next();
    });

    // Clear an error as soon as the field is fixed.
    lgInputs().forEach(function (input) {
        input.addEventListener(input.tagName === 'SELECT' || input.type === 'file' ? 'change' : 'input', function () {
            if (input.getAttribute('aria-invalid') === 'true') setError(input.id, '');
        });
    });
    $('business_name').addEventListener('input', function () { setError('business_name', ''); });

    // Steps 6 and 7 are shown by register.js; keep the progress bar in step.
    var watch = new MutationObserver(render);
    [step2, step3].forEach(function (el) { if (el) watch.observe(el, { attributes: true, attributeFilter: ['style'] }); });

    // Follow the chosen role: register.js and the role picker both call selectRole().
    var selectRole = window.selectRole;
    window.selectRole = function (role) {
        selectRole(role);
        setActive(role === 'logistics');
    };

    // After a failed submit, open the first logistics page that shows an error.
    var role = ($('role_input') || {}).value;
    if (role === 'logistics') {
        var errorPage = Array.prototype.find.call(container.querySelectorAll('[data-lg-page]'), function (el) {
            return Array.prototype.some.call(el.querySelectorAll('.seller-field-error, [role="alert"]'), function (error) {
                return !error.hidden && error.textContent.trim();
            });
        });
        if (errorPage) page = Number(errorPage.dataset.lgPage);
    }
    setActive(role === 'logistics');
})();
