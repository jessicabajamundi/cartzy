// Lets a floating chat bubble be dragged anywhere, and closed by dropping it on the target at the
// bottom of the page. A plain click still works as before; a drag never counts as a click.
// DragDiscard.attach(element, { key, onDiscard }): key remembers the spot for this browser tab.
window.DragDiscard = window.DragDiscard || (() => {
    const MOVE_THRESHOLD = 6;
    const DISCARD_RADIUS = 75;
    const EDGE = 10;
    let target = null;

    const ensureTarget = () => {
        if (!target) {
            target = document.createElement('div');
            target.className = 'drag-discard';
            target.setAttribute('aria-hidden', 'true');
            target.innerHTML = '<span class="drag-discard-circle"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span class="drag-discard-label"></span>';
            document.body.append(target);
        }
        return target;
    };

    const setLabel = (near) => {
        target.classList.toggle('is-active', near);
        target.querySelector('.drag-discard-label').textContent = near ? 'Release to close' : 'Drag here to close';
    };

    const readSpot = (key) => {
        try {
            const spot = JSON.parse(sessionStorage.getItem(key) || 'null');
            return typeof spot?.left === 'number' && typeof spot?.top === 'number' ? spot : null;
        } catch (_) {
            return null;
        }
    };

    const writeSpot = (key, spot) => {
        try {
            if (spot) sessionStorage.setItem(key, JSON.stringify(spot));
            else sessionStorage.removeItem(key);
        } catch (_) { /* Private windows can block storage; dragging still works. */ }
    };

    const attach = (element, { key, onDiscard }) => {
        let spot = readSpot(key);
        let drag = null;
        let justDragged = false;

        const place = () => {
            if (!spot) return;
            // A hidden bubble has no size yet, so assume a labelled bubble's width.
            const left = Math.max(EDGE, Math.min(spot.left, innerWidth - (element.offsetWidth || 140) - EDGE));
            const top = Math.max(EDGE, Math.min(spot.top, innerHeight - (element.offsetHeight || 60) - EDGE));
            Object.assign(element.style, { position: 'fixed', left: `${left}px`, top: `${top}px`, right: 'auto', bottom: 'auto', margin: '0' });
        };

        const backToCorner = () => {
            spot = null;
            writeSpot(key, null);
            ['position', 'left', 'top', 'right', 'bottom', 'margin', 'transform', 'opacity'].forEach((prop) => { element.style[prop] = ''; });
        };

        // Measured from where the target settles, so a quick drag counts before it finishes sliding in.
        const isNearTarget = (box) => {
            const goalX = innerWidth / 2;
            const goalY = innerHeight - parseFloat(getComputedStyle(target).bottom) - target.offsetHeight / 2;
            return Math.hypot(box.left + box.width / 2 - goalX, box.top + box.height / 2 - goalY) < DISCARD_RADIUS;
        };

        element.classList.add('is-draggable');
        place();

        element.addEventListener('pointerdown', (event) => {
            if ((event.pointerType === 'mouse' && event.button !== 0) || event.target.closest('[data-no-drag]')) return;
            const box = element.getBoundingClientRect();
            drag = { x: event.clientX, y: event.clientY, left: box.left, top: box.top, moved: false, pointerId: event.pointerId };
        });

        element.addEventListener('pointermove', (event) => {
            if (!drag || event.pointerId !== drag.pointerId) return;
            const dx = event.clientX - drag.x, dy = event.clientY - drag.y;
            if (!drag.moved) {
                if (Math.hypot(dx, dy) <= MOVE_THRESHOLD) return;
                drag.moved = true;
                try { element.setPointerCapture(event.pointerId); } catch (_) { /* Still follows the pointer over the bubble. */ }
                element.classList.add('is-dragging');
                ensureTarget();
                setLabel(false);
                void target.offsetWidth; // Lets the target slide in instead of appearing.
                target.classList.add('is-visible');
            }
            spot = { left: drag.left + dx, top: drag.top + dy };
            place();
            const near = isNearTarget(element.getBoundingClientRect());
            setLabel(near);
            element.style.transform = near ? 'scale(.75)' : '';
            element.style.opacity = near ? '.75' : '';
        });

        const finish = (event, canDiscard) => {
            if (!drag || event.pointerId !== drag.pointerId) return;
            const { moved, pointerId } = drag;
            drag = null;
            if (!moved) return;

            try { element.releasePointerCapture(pointerId); } catch (_) { /* Capture already ended. */ }
            element.classList.remove('is-dragging');
            const discard = canDiscard && target.classList.contains('is-active');
            target.classList.remove('is-visible', 'is-active');
            // The browser sends one click right after a drag ends; swallow only that one.
            justDragged = true;
            setTimeout(() => { justDragged = false; }, 50);

            if (discard) {
                backToCorner();
                onDiscard?.();
                return;
            }
            element.style.transform = '';
            element.style.opacity = '';
            writeSpot(key, spot);
        };
        element.addEventListener('pointerup', (event) => finish(event, true));
        element.addEventListener('pointercancel', (event) => finish(event, false));

        element.addEventListener('click', (event) => {
            if (!justDragged) return;
            justDragged = false;
            event.preventDefault();
            event.stopImmediatePropagation();
        }, true);
        element.addEventListener('dragstart', (event) => event.preventDefault());
        window.addEventListener('resize', place);
    };

    // The store page's Chat bubble. It comes back when the page is opened again.
    document.querySelectorAll('.chat-bubble-form').forEach((form) => {
        attach(form, { key: 'chat-bubble-spot:store', onDiscard: () => { form.hidden = true; } });
    });

    return { attach };
})();
