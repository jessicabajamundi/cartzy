(() => {
    // 1. Modal Dialog open/close controller
    document.querySelectorAll('[data-open-modal]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-open-modal');
            const modal = document.getElementById(modalId);
            if (modal && typeof modal.showModal === 'function') {
                modal.showModal();
                // Autofocus search filter input if available
                const searchInput = modal.querySelector('.modal-rider-search');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 50);
                }
            }
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            const modalId = closeBtn.getAttribute('data-close-modal');
            const modal = document.getElementById(modalId);
            if (modal && typeof modal.close === 'function') {
                modal.close();
            }
        });
    });

    // Close modal on backdrop click
    document.querySelectorAll('dialog.pickup-rider-modal').forEach(dialog => {
        dialog.addEventListener('click', event => {
            const rect = dialog.getBoundingClientRect();
            const isInDialog = (
                rect.top <= event.clientY &&
                event.clientY <= rect.top + rect.height &&
                rect.left <= event.clientX &&
                event.clientX <= rect.left + rect.width
            );
            if (!isInDialog) {
                dialog.close();
            }
        });
    });

    // 2. Real-time Live Filter in Rider Selection Modals
    document.querySelectorAll('.modal-rider-search').forEach(input => {
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            const targetListId = input.getAttribute('data-filter-target');
            const list = document.getElementById(targetListId);
            if (!list) return;

            const items = list.querySelectorAll('.modal-rider-item');
            let visibleCount = 0;

            items.forEach(item => {
                const text = (item.getAttribute('data-rider-search') || item.textContent || '').toLowerCase();
                const matches = text.includes(query);
                item.style.display = matches ? 'flex' : 'none';
                if (matches) visibleCount++;
            });

            let emptyMessage = list.querySelector('.modal-filter-empty');
            if (!emptyMessage && items.length) {
                emptyMessage = document.createElement('p');
                emptyMessage.className = 'modal-filter-empty';
                emptyMessage.setAttribute('role', 'status');
                emptyMessage.textContent = 'No matching riders. Try another name, area or vehicle.';
                list.appendChild(emptyMessage);
            }
            if (emptyMessage) emptyMessage.hidden = visibleCount > 0;

            // Handle headings visibility if all items inside are hidden
            list.querySelectorAll('.rider-group-heading').forEach(heading => {
                let next = heading.nextElementSibling;
                let groupHasVisible = false;
                while (next && !next.classList.contains('rider-group-heading')) {
                    if (next.classList.contains('modal-rider-item') && next.style.display !== 'none') {
                        groupHasVisible = true;
                    }
                    next = next.nextElementSibling;
                }
                heading.style.display = groupHasVisible ? 'block' : 'none';
            });
        });
    });

    // 3. Form submit busy states
    document.querySelectorAll('[data-pickup-dispatch]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting) {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = 'true';
            const button = form.querySelector('button');
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                const span = button.querySelector('span');
                if (span) {
                    span.textContent = '⚡ Dispatching Nearest Rider…';
                }
            }
        });
    });

    document.querySelectorAll('[data-pickup-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting) {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = 'true';
            const button = form.querySelector('button');
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                const span = button.querySelector('span');
                if (span) {
                    span.textContent = 'Confirming…';
                }
            }
        });
    });

    // Reset button states on page restoration from bfcache
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-submitting]').forEach(form => {
            delete form.dataset.submitting;
            const button = form.querySelector('button');
            if (button) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        });
    });
})();
