(() => {
    const options = window.productOptions || [];
    const image = document.getElementById('pd-main-image');
    const thumbs = document.querySelectorAll('[data-photo]');
    let photos = Array.from(thumbs, thumb => thumb.dataset.photo);
    if (!photos.length && image) photos = [image.getAttribute('src')];
    let syncViewerGallery = () => {};
    const showPhoto = url => {
        if (!image || !url || !photos.includes(url)) return;
        image.src = url;
        thumbs.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.photo === url)));
    };
    thumbs.forEach(button => button.addEventListener('click', () => showPhoto(button.dataset.photo)));
    document.querySelectorAll('[data-photo-step]').forEach(button => button.addEventListener('click', () => {
        if (!image || photos.length < 2) return;
        const current = photos.indexOf(image.getAttribute('src'));
        const next = (Math.max(0, current) + Number(button.dataset.photoStep) + photos.length) % photos.length;
        showPhoto(photos[next]);
        Array.from(thumbs).find(thumb => thumb.dataset.photo === photos[next])?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }));

    // Photo zoom: hover magnifies the main photo in place (mouse only); a click opens the full-screen viewer.
    const mainPhoto = image?.closest('.pd-main-photo');
    const viewer = document.getElementById('pd-viewer');
    if (mainPhoto && viewer) {
        const HOVER_SCALE = 2.2;
        mainPhoto.addEventListener('pointermove', event => {
            if (event.pointerType !== 'mouse' || event.target.closest('button')) {
                mainPhoto.classList.remove('is-hover-zoom');
                return;
            }
            const box = image.getBoundingClientRect();
            image.style.transformOrigin = `${(event.clientX - box.left) / box.width * 100}% ${(event.clientY - box.top) / box.height * 100}%`;
            image.style.setProperty('--pd-hover-scale', HOVER_SCALE);
            mainPhoto.classList.add('is-hover-zoom');
        });
        mainPhoto.addEventListener('pointerleave', () => mainPhoto.classList.remove('is-hover-zoom'));

        const stage = document.getElementById('pd-viewer-stage');
        const viewerImage = document.getElementById('pd-viewer-image');
        const viewerThumbs = viewer.querySelectorAll('[data-viewer-photo]');
        const allViewerPhotos = Array.from(viewerThumbs, thumb => thumb.querySelector('img').getAttribute('src'));
        const MIN = 1, MAX = 4, STEP = 1.5, TAP_SCALE = 2.5;
        const view = { index: 0, scale: 1, x: 0, y: 0 };
        let opener = null;

        const clamp = () => {
            const maxX = stage.clientWidth * (view.scale - 1) / 2;
            const maxY = stage.clientHeight * (view.scale - 1) / 2;
            view.x = Math.min(maxX, Math.max(-maxX, view.x));
            view.y = Math.min(maxY, Math.max(-maxY, view.y));
        };
        const render = () => {
            clamp();
            viewerImage.style.transform = `translate(${view.x}px, ${view.y}px) scale(${view.scale})`;
            stage.classList.toggle('is-zoomed', view.scale > 1);
            document.getElementById('pd-viewer-level').textContent = Math.round(view.scale * 100) + '%';
            viewer.querySelector('[data-viewer-zoom="-1"]').disabled = view.scale <= MIN;
            viewer.querySelector('[data-viewer-zoom="1"]').disabled = view.scale >= MAX;
        };
        // Zoom toward a point given relative to the stage centre, so the spot under the cursor stays put.
        const zoomTo = (scale, px = 0, py = 0) => {
            const next = Math.min(MAX, Math.max(MIN, scale));
            view.x = px - (px - view.x) * next / view.scale;
            view.y = py - (py - view.y) * next / view.scale;
            view.scale = next;
            if (next === MIN) view.x = view.y = 0;
            render();
        };
        const fromCentre = event => {
            const box = stage.getBoundingClientRect();
            return [event.clientX - box.left - box.width / 2, event.clientY - box.top - box.height / 2];
        };
        const go = index => {
            if (!photos.length) return;
            view.index = (index + photos.length) % photos.length;
            view.scale = 1; view.x = view.y = 0;
            viewerImage.src = photos[view.index];
            document.getElementById('pd-viewer-count').textContent = photos.length > 1 ? `${view.index + 1} / ${photos.length}` : '';
            viewerThumbs.forEach((thumb, i) => thumb.setAttribute('aria-current', String(allViewerPhotos[i] === photos[view.index])));
            Array.from(viewerThumbs).find((thumb, i) => allViewerPhotos[i] === photos[view.index])?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            showPhoto(photos[view.index]);
            render();
        };
        const open = () => {
            if (!photos.length) return;
            opener = document.activeElement;
            viewer.showModal();
            document.documentElement.classList.add('pd-viewer-open');
            go(Math.max(0, photos.indexOf(image.getAttribute('src'))));
        };
        image.addEventListener('click', open);
        document.getElementById('pd-zoom-open').addEventListener('click', open);
        document.getElementById('pd-viewer-close').addEventListener('click', () => viewer.close());
        viewer.addEventListener('close', () => {
            document.documentElement.classList.remove('pd-viewer-open');
            opener?.focus();
        });
        viewer.querySelectorAll('[data-viewer-step]').forEach(button => button.addEventListener('click', () => go(view.index + Number(button.dataset.viewerStep))));
        viewerThumbs.forEach((thumb, i) => thumb.addEventListener('click', () => {
            const index = photos.indexOf(allViewerPhotos[i]);
            if (index >= 0) go(index);
        }));
        syncViewerGallery = () => {
            viewerThumbs.forEach((thumb, i) => { thumb.hidden = !photos.includes(allViewerPhotos[i]); });
            viewer.querySelectorAll('[data-viewer-step]').forEach(button => { button.hidden = photos.length < 2; });
            if (viewer.open) {
                if (photos.length) go(0);
                else viewer.close();
            }
        };
        viewer.querySelectorAll('[data-viewer-zoom]').forEach(button => button.addEventListener('click', () => {
            zoomTo(Number(button.dataset.viewerZoom) > 0 ? view.scale * STEP : view.scale / STEP);
        }));
        viewer.addEventListener('keydown', event => {
            if (event.key === 'ArrowRight' && photos.length > 1) go(view.index + 1);
            else if (event.key === 'ArrowLeft' && photos.length > 1) go(view.index - 1);
            else if (event.key === '+' || event.key === '=') zoomTo(view.scale * STEP);
            else if (event.key === '-') zoomTo(view.scale / STEP);
            else return;
            event.preventDefault();
        });
        stage.addEventListener('wheel', event => {
            event.preventDefault();
            zoomTo(view.scale * (event.deltaY < 0 ? 1.2 : 1 / 1.2), ...fromCentre(event));
        }, { passive: false });

        // Drag to pan, pinch to zoom, click / tap to toggle zoom at that spot.
        const pointers = new Map();
        let drag = null, pinch = null;
        stage.addEventListener('pointerdown', event => {
            if (event.target.closest('button')) return;
            stage.setPointerCapture(event.pointerId);
            pointers.set(event.pointerId, [event.clientX, event.clientY]);
            if (pointers.size === 2) {
                const [a, b] = [...pointers.values()];
                pinch = { distance: Math.hypot(a[0] - b[0], a[1] - b[1]), scale: view.scale };
                drag = null;
            } else {
                drag = { startX: event.clientX, startY: event.clientY, x: view.x, y: view.y, moved: false };
            }
        });
        stage.addEventListener('pointermove', event => {
            if (!pointers.has(event.pointerId)) return;
            pointers.set(event.pointerId, [event.clientX, event.clientY]);
            if (pinch && pointers.size === 2) {
                const [a, b] = [...pointers.values()];
                const box = stage.getBoundingClientRect();
                zoomTo(pinch.scale * Math.hypot(a[0] - b[0], a[1] - b[1]) / pinch.distance,
                    (a[0] + b[0]) / 2 - box.left - box.width / 2, (a[1] + b[1]) / 2 - box.top - box.height / 2);
            } else if (drag) {
                const dx = event.clientX - drag.startX, dy = event.clientY - drag.startY;
                if (Math.hypot(dx, dy) > 5) drag.moved = true;
                if (drag.moved && view.scale > 1) {
                    view.x = drag.x + dx;
                    view.y = drag.y + dy;
                    render();
                }
            }
        });
        const release = event => {
            if (!pointers.delete(event.pointerId)) return;
            if (pinch) {
                if (pointers.size < 2) pinch = null;
                drag = null;
                return;
            }
            if (drag && !drag.moved && event.type === 'pointerup') {
                if (view.scale > 1) zoomTo(1);
                else zoomTo(TAP_SCALE, ...fromCentre(event));
            }
            drag = null;
        };
        stage.addEventListener('pointerup', release);
        stage.addEventListener('pointercancel', release);
        window.addEventListener('resize', () => viewer.open && render());
    }
    const money = value => '₱' + (value / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const quantityInput = document.getElementById('pd-quantity');
    const quantityButtons = document.querySelectorAll('[data-quantity-step]');
    const syncQuantity = (normalize = false) => {
        if (!quantityInput) return;
        const max = Math.max(1, Number(quantityInput.max));
        const value = Math.min(max, Math.max(1, Math.trunc(Number(quantityInput.value) || 1)));
        if (normalize) quantityInput.value = value;
        quantityButtons.forEach(button => {
            button.disabled = quantityInput.disabled || (Number(button.dataset.quantityStep) < 0 ? value <= 1 : value >= max);
        });
    };
    quantityButtons.forEach(button => button.addEventListener('click', () => {
        if (!quantityInput || quantityInput.disabled) return;
        syncQuantity(true);
        quantityInput.value = Number(quantityInput.value) + Number(button.dataset.quantityStep);
        syncQuantity(true);
        quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
    }));
    quantityInput?.addEventListener('input', () => syncQuantity());
    quantityInput?.addEventListener('change', () => syncQuantity(true));
    syncQuantity();
    // Color and size chips: each pick resolves to one variant, which drives price, stock and the cart form.
    const variantInput = document.getElementById('pd-variant-input');
    const chips = document.querySelectorAll('[data-pick]');
    const current = options.find(item => String(item.id) === variantInput?.value);
    const picked = { color: current?.color ?? '', size: current?.size ?? '' };
    const setGallery = option => {
        photos = [...new Set(option.images || (option.image ? [option.image] : []))];
        thumbs.forEach(thumb => { thumb.hidden = !photos.includes(thumb.dataset.photo); });
        document.querySelectorAll('[data-photo-step]').forEach(button => { button.hidden = photos.length < 2; });
        if (image) {
            image.hidden = !photos.length;
            let placeholder = image.parentElement.querySelector('[data-color-photo-empty]');
            if (!placeholder) {
                placeholder = document.createElement('div');
                placeholder.className = 'pd-placeholder';
                placeholder.dataset.colorPhotoEmpty = '';
                placeholder.textContent = 'No photo for this color yet';
                image.parentElement.appendChild(placeholder);
            }
            placeholder.hidden = photos.length > 0;
        }
        const zoom = document.getElementById('pd-zoom-open');
        if (zoom) zoom.hidden = !photos.length;
        showPhoto(photos[0]);
        syncViewerGallery();
    };
    if (current) setGallery(current);
    const applyOption = option => {
        variantInput.value = option.id;
        document.getElementById('pd-price').textContent = money(option.price);
        const regular = document.getElementById('pd-regular');
        regular.textContent = money(option.regular);
        regular.hidden = option.price >= option.regular;
        const discount = document.getElementById('pd-discount');
        discount.textContent = '-' + Math.round((option.regular - option.price) / option.regular * 100) + '%';
        discount.hidden = regular.hidden;
        const saving = document.getElementById('pd-saving');
        saving.textContent = 'You save ' + money(option.regular - option.price);
        saving.hidden = regular.hidden;
        document.getElementById('pd-price-input').value = option.price / 100;
        const name = document.getElementById('pd-selected-name');
        if (name) name.textContent = option.name;
        document.getElementById('pd-stock').textContent = option.stock + ' in stock';
        const quantity = document.getElementById('pd-quantity');
        quantity.max = option.stock;
        quantity.value = Math.min(Math.max(Number(quantity.value) || 1, 1), option.stock);
        quantity.disabled = option.stock < 1;
        syncQuantity(true);
        document.getElementById('pd-add').disabled = option.stock < 1;
        setGallery(option);
    };
    const syncChips = () => chips.forEach(chip => {
        chip.setAttribute('aria-pressed', String(picked[chip.dataset.pick] === chip.dataset.value));
        if (chip.dataset.pick === 'size') {
            chip.disabled = !options.some(item => item.color === picked.color && item.size === chip.dataset.value && item.stock > 0);
        }
    });
    chips.forEach(chip => chip.addEventListener('click', () => {
        picked[chip.dataset.pick] = chip.dataset.value;
        const inStock = item => item.color === picked.color && item.stock > 0;
        // Keep the size when this color has it in stock; otherwise move to the color's first available size.
        const option = options.find(item => inStock(item) && item.size === picked.size) || options.find(inStock);
        if (!option) return;
        picked.size = option.size;
        syncChips();
        applyOption(option);
    }));

    // Section menu: sit just below the sticky site header and highlight the section being read.
    const header = document.querySelector('body > header, header.sticky');
    const setHeaderOffset = () => document.documentElement.style.setProperty('--pd-header-offset', (header?.offsetHeight || 0) + 'px');
    setHeaderOffset();
    window.addEventListener('resize', setHeaderOffset);
    const sectionLinks = Array.from(document.querySelectorAll('.pd-section-links a'));
    const markSection = id => sectionLinks.forEach(link => link.setAttribute('aria-current', String(link.hash === '#' + id)));
    if ('IntersectionObserver' in window) {
        const spy = new IntersectionObserver(entries => entries.forEach(entry => entry.isIntersecting && markSection(entry.target.id)), { rootMargin: '-35% 0px -60% 0px' });
        sectionLinks.forEach(link => { const target = document.getElementById(link.hash.slice(1)); if (target) spy.observe(target); });
    }
    sectionLinks.forEach(link => link.addEventListener('click', () => markSection(link.hash.slice(1))));

    // Long descriptions start folded with a "Show more" button.
    const description = document.querySelector('[data-description]');
    const descriptionToggle = document.querySelector('[data-description-toggle]');
    if (description && descriptionToggle && description.scrollHeight > 320) {
        description.classList.add('is-clamped');
        descriptionToggle.hidden = false;
        descriptionToggle.addEventListener('click', () => {
            const open = description.classList.toggle('is-clamped') === false;
            descriptionToggle.setAttribute('aria-expanded', String(open));
            descriptionToggle.textContent = open ? 'Show less' : 'Show more';
            if (!open) description.scrollIntoView({ block: 'nearest' });
        });
    }

    // Reviews / comments tabs. Without JS both lists simply show one after the other.
    const tablist = document.querySelector('.pd-tabs');
    if (tablist) {
        const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));
        const select = (tab, focus) => {
            tabs.forEach(item => {
                const active = item === tab;
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
                document.getElementById(item.getAttribute('aria-controls')).hidden = !active;
            });
            if (focus) tab.focus();
        };
        // Opens the tab containing the hash target, e.g. #comments or #comment-12
        const fromHash = () => {
            const target = location.hash.length > 1 ? document.getElementById(location.hash.slice(1)) : null;
            const panel = target?.closest('[role="tabpanel"]');
            const tab = panel && tabs.find(item => item.getAttribute('aria-controls') === panel.id);
            if (!tab) return false;
            select(tab);
            target.scrollIntoView();
            return true;
        };
        tabs.forEach(tab => tab.addEventListener('click', () => select(tab)));
        tablist.addEventListener('keydown', event => {
            const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
            if (!step) return;
            const index = tabs.indexOf(document.activeElement);
            select(tabs[(index + step + tabs.length) % tabs.length], true);
        });
        window.addEventListener('hashchange', fromHash);
        tablist.hidden = false;
        if (!fromHash()) select(tabs[0]);
    }

    // Comment reactions: open the picker, then save the choice without reloading the page.
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const closePickers = except => document.querySelectorAll('.pd-react-picker:not([hidden])').forEach(picker => {
        if (picker === except) return;
        picker.hidden = true;
        picker.parentElement.querySelector('[data-react-toggle]').setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-react-toggle]');
        if (toggle) {
            const picker = toggle.parentElement.querySelector('.pd-react-picker');
            closePickers(picker);
            picker.hidden = !picker.hidden;
            toggle.setAttribute('aria-expanded', String(!picker.hidden));
            if (!picker.hidden) picker.querySelector('button[aria-pressed="true"], button').focus();
            return;
        }
        if (!event.target.closest('.pd-react-picker')) closePickers();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const open = document.querySelector('.pd-react-picker:not([hidden])');
        if (!open) return;
        closePickers();
        open.parentElement.querySelector('[data-react-toggle]').focus();
    });
    document.querySelectorAll('.pd-react-picker form').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const type = event.submitter?.value;
        const actions = form.closest('[data-reactions]');
        try {
            const response = await fetch(actions.dataset.reactUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ type }),
            });
            if (!response.ok) throw new Error();
            const data = await response.json();
            const label = data.mine ? `${data.emoji[data.mine]} ${data.mine[0].toUpperCase()}${data.mine.slice(1)}` : 'React';
            const toggle = actions.querySelector('[data-react-toggle]');
            toggle.querySelector('[data-react-mine]').textContent = label;
            toggle.classList.toggle('is-active', Boolean(data.mine));
            actions.querySelectorAll('.pd-react-picker button').forEach(button => button.setAttribute('aria-pressed', String(button.value === data.mine)));
            const summary = actions.querySelector('[data-react-summary]');
            summary.hidden = !data.total;
            summary.querySelector('[data-react-icons]').textContent = Object.keys(data.counts).slice(0, 3).map(key => data.emoji[key]).join('');
            summary.querySelector('[data-react-total]').textContent = data.total;
            closePickers();
            toggle.focus();
        } catch {
            form.submit(); // fall back to a normal page reload
        }
    }));

    // Replies: show the thread's reply box. Replying to a reply mentions that person.
    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-reply-to]');
        const cancel = event.target.closest('[data-reply-cancel]');
        if (cancel) { cancel.closest('form').hidden = true; return; }
        if (!toggle) return;
        const form = document.querySelector(`[data-reply-form="${toggle.dataset.replyTo}"]`);
        if (!form) return;
        form.hidden = false;
        const textarea = form.querySelector('textarea');
        if (toggle.closest('.is-reply') && !textarea.value.trim()) textarea.value = `@${toggle.dataset.replyName} `;
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    });
})();

// Wishlist heart beside Add to cart: saves or removes the currently selected variant
// (same "variant_<id>" refs as the store and sale pages) without leaving the page.
(() => {
    const heart = document.getElementById('pd-wishlist');
    const form = document.getElementById('pd-cart-form');
    if (!heart || !form) return;
    const variantInput = document.getElementById('pd-variant-input');
    const refs = new Set(JSON.parse(heart.dataset.refs || '[]'));
    const currentRef = () => 'variant_' + variantInput.value;

    const sync = () => {
        const saved = refs.has(currentRef());
        heart.setAttribute('aria-pressed', String(saved));
        heart.setAttribute('aria-label', saved ? 'Remove from wishlist' : 'Add to wishlist');
        heart.title = saved ? 'Remove from wishlist' : 'Save to wishlist';
    };
    sync();
    // Color/size chips switch the variant; refresh once product-details has applied the pick.
    form.addEventListener('click', event => {
        if (event.target.closest('[data-pick]')) setTimeout(sync);
    });

    heart.addEventListener('click', async () => {
        if (heart.dataset.authenticated !== 'true') return window.location.assign(heart.dataset.loginUrl);
        const ref = currentRef();
        heart.disabled = true;
        try {
            const response = await fetch(heart.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value },
                body: JSON.stringify({
                    ref,
                    name: heart.dataset.name,
                    price: document.getElementById('pd-price-input').value,
                    image: document.getElementById('pd-main-image')?.getAttribute('src') || '',
                    variation: document.getElementById('pd-selected-name')?.textContent.trim() || '',
                }),
            });
            if (response.status === 401) return window.location.assign(heart.dataset.loginUrl);
            if (response.status === 419) throw new Error('Your session has expired. Refresh the page and try again.');
            const result = response.ok ? await response.json() : null;
            if (!result?.success) throw new Error('Could not update your wishlist. Please try again.');
            if (result.wished) refs.add(ref); else refs.delete(ref);
            sync();
            heart.classList.remove('is-popping');
            void heart.offsetWidth; // restart the pop animation
            if (result.wished) heart.classList.add('is-popping');
            window.updateBuyerNavCount?.('wishlist', result.count);
            window.showToast?.(result.wished ? 'Saved to your wishlist.' : 'Removed from your wishlist.', 'success');
        } catch (error) {
            window.showToast?.(error.message, 'error');
        } finally {
            heart.disabled = false;
        }
    });
})();
