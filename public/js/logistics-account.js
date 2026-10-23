(() => {
    const form = document.getElementById('logistics-account-form');
    if (!form) return;
    const mobile = form.elements.phone;
    if (mobile) {
        mobile.addEventListener('beforeinput', event => {
            if (!event.data || event.inputType !== 'insertText') return;
            const start = mobile.selectionStart ?? mobile.value.length;
            const end = mobile.selectionEnd ?? start;
            const next = mobile.value.slice(0, start) + event.data + mobile.value.slice(end);
            if (!/^\+?[0-9]*$/.test(next) || next.length > (next.startsWith('+') ? 13 : 11)) {
                event.preventDefault();
            }
        });
        mobile.addEventListener('input', () => {
            const start = mobile.selectionStart;
            const original = mobile.value;
            const prefix = original.startsWith('+') ? '+' : '';
            mobile.value = prefix + original.replace(/[^0-9]/g, '').slice(0, prefix ? 12 : 11);
            if (start !== null && original !== mobile.value) {
                const caret = Math.min(start, mobile.value.length);
                mobile.setSelectionRange(caret, caret);
            }
        });
    }
    const status = document.getElementById('account-save-status');
    let dirty = false;
    const markDirty = () => {
        dirty = true;
        status.textContent = 'You have unsaved changes.';
    };
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    window.addEventListener('beforeunload', event => {
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });
    const password = form.elements.password;
    const confirmation = form.elements.password_confirmation;
    const validatePasswords = () => confirmation.setCustomValidity(
        confirmation.value !== password.value ? 'Your passwords do not match.' : ''
    );
    [password, confirmation].forEach(input => {
        input.addEventListener('input', validatePasswords);
        const wrapper = document.createElement('span');
        wrapper.className = 'account-password-control';
        input.before(wrapper);
        wrapper.append(input);
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.textContent = 'Show';
        toggle.setAttribute('aria-label', `Show ${input === password ? 'new password' : 'password confirmation'}`);
        toggle.setAttribute('aria-pressed', 'false');
        toggle.addEventListener('click', () => {
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            toggle.textContent = reveal ? 'Hide' : 'Show';
            toggle.setAttribute('aria-label', `${reveal ? 'Hide' : 'Show'} ${input === password ? 'new password' : 'password confirmation'}`);
            toggle.setAttribute('aria-pressed', String(reveal));
        });
        wrapper.append(toggle);
    });
    form.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            input.setCustomValidity(file && file.size > 5 * 1024 * 1024 ? 'Choose a file smaller than 5 MB.' : '');
            input.reportValidity();
        });
    });
    const errors = JSON.parse(form.dataset.errors || '{}');
    Object.entries(errors).forEach(([name, messages]) => {
        const field = form.elements.namedItem(name);
        if (!field) return;
        const error = document.createElement('span');
        error.className = 'account-field-error';
        error.id = `account-error-${name}`;
        error.textContent = messages[0];
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', error.id);
        field.closest('label').append(error);
    });
    form.querySelector('[aria-invalid="true"]')?.focus();
    form.addEventListener('submit', () => {
        dirty = false;
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.textContent = 'Saving…';
        status.textContent = 'Saving your account details…';
    });
    const birthday = document.getElementById('logistics-birthday');
    const age = document.getElementById('logistics-age');
    if (!birthday || !age) return;
    const updateAge = () => {
        const birth = new Date(`${birthday.value}T00:00:00`);
        const today = new Date();
        if (!birthday.value || Number.isNaN(birth.getTime()) || birth > today) {
            age.value = '';
            return;
        }
        let years = today.getFullYear() - birth.getFullYear();
        if (today.getMonth() < birth.getMonth() ||
            (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) years--;
        age.value = years;
    };
    birthday.addEventListener('input', updateAge);
    updateAge();
})();
