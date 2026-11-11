/**
 * Cartzy Global Cart & Toast Notification System
 * Available on all store pages
 */

// ─── Toast Notification ────────────────────────────────────────────────────
window.dismissToast = function() {
    const toast = document.getElementById('cart-toast');
    if (!toast) return;
    clearTimeout(window._toastTimer);
    toast.style.transform = 'translateY(120px)';
    toast.style.opacity = '0';
    toast.style.pointerEvents = 'none';
};

window.showToast = function(message, type = 'success') {
    let toast = document.getElementById('cart-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cart-toast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;transform:translateY(120px);opacity:0;transition:all 0.35s cubic-bezier(.4,0,.2,1);pointer-events:none;';
        document.body.appendChild(toast);
    }
    if (!document.getElementById('cart-toast-style')) {
        const style = document.createElement('style');
        style.id = 'cart-toast-style';
        style.textContent = `
            @keyframes toastProgress {
                from { width: 100%; }
                to { width: 0%; }
            }
        `;
        document.head.appendChild(style);
    }
    const bg = type === 'success' ? '#111827' : '#dc2626';
    const icon = type === 'success'
        ? '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
        : '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    toast.innerHTML = `<div style="position:relative;overflow:hidden;background:${bg};color:#fff;border-radius:12px;padding:12px 14px 14px 18px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 8px 32px rgba(0,0,0,0.28);min-width:250px;max-width:380px;pointer-events:auto"><div style="display:flex;align-items:center;gap:10px;flex:1">${icon}<span>${message}</span></div><button type="button" onclick="window.dismissToast()" style="background:transparent;border:none;color:rgba(255,255,255,0.7);cursor:pointer;padding:4px;margin-left:4px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;flex-shrink:0;transition:all 0.15s" onmouseover="this.style.color='#fff';this.style.backgroundColor='rgba(255,255,255,0.15)'" onmouseout="this.style.color='rgba(255,255,255,0.7)';this.style.backgroundColor='transparent'" aria-label="Close notification"><svg style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button><div style="position:absolute;bottom:0;left:0;height:3px;background:rgba(255,255,255,0.75);animation:toastProgress 5s linear forwards;"></div></div>`;
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
    toast.style.pointerEvents = 'auto';
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        window.dismissToast();
    }, 5000);
};

// ─── Update Cart Badge ─────────────────────────────────────────────────────
window.updateCartBadge = function(count) {
    window.updateBuyerNavCount?.('cart', count);
    document.querySelectorAll('.cart-badge-count').forEach(badge => {
        if (badge.hasAttribute('data-buyer-count')) return;
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    });
};

// ─── Rebuild Cart Popover HTML ─────────────────────────────────────────────
window.updateCartPopover = function(cartItems, cartCount) {
    const box = document.getElementById('cart-popover-box');
    if (!box) return;

    if (!cartItems || cartItems.length === 0) {
        box.innerHTML = `
            <div class="p-8 text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-gray-100 text-gray-800 rounded-full flex items-center justify-center text-2xl mb-2">🛒</div>
                <h4 class="text-sm font-bold text-gray-900">Your Cart is Empty</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-xs">No items added to your cart yet.</p>
                <a href="/" class="mt-4 bg-black hover:bg-gray-800 text-white text-xs font-semibold px-5 py-2 rounded-md shadow-xs transition">Shop Now</a>
            </div>`;
        return;
    }

    const itemsHtml = cartItems.map(item => `
        <div class="p-3 hover:bg-gray-50 flex items-center gap-3 transition">
            <img src="${item.image || 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=100&h=100&fit=crop&q=80'}"
                 alt="Item" class="w-12 h-12 object-cover rounded border border-gray-200 shrink-0">
            <div class="flex-1 min-w-0">
                <h4 class="text-xs font-medium text-gray-900 truncate">${item.name}</h4>
                <p class="text-[11px] text-gray-500">Qty: ${item.quantity} · ${item.variation || 'Standard'}</p>
                <span class="text-xs font-bold text-black">₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
            </div>
        </div>`).join('');

    const label = cartCount === 1 ? 'item' : 'items';
    box.innerHTML = `
        <div class="p-3 bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
            Cart (${cartCount} ${label})
        </div>
        <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">${itemsHtml}</div>
        <div class="p-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-2">
            <a href="/cart" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-900 text-xs font-semibold px-3 py-2 rounded transition border border-gray-200">View Cart</a>
            <a href="/checkout" class="flex-1 text-center bg-black hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded transition flex items-center justify-center gap-1"><span>Checkout</span><span>&rarr;</span></a>
        </div>`;
};

// ─── Add to Cart ───────────────────────────────────────────────────────────
window.addToCart = function(id, name, price, image, variation) {
    const isLoggedIn = (typeof window.isUserLoggedIn !== 'undefined')
        ? Boolean(window.isUserLoggedIn)
        : (document.querySelector('meta[name="user-logged-in"]')?.getAttribute('content') === '1');

    const registerUrl = window.registerUrl || document.querySelector('meta[name="register-url"]')?.getAttribute('content') || '/register?from=cart';

    // If user is not logged in / has no account, redirect directly to sign-up
    if (!isLoggedIn) {
        window.location.href = registerUrl;
        return;
    }

    // Remember which button was pressed so the product photo can fly from its card.
    const source = window._lastClickTarget;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    return fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id, name, price, image, variation, quantity: 1 })
    })
    .then(async res => {
        if (res.status === 401) {
            const errData = await res.json().catch(() => ({}));
            window.location.href = errData.redirect || registerUrl;
            return null;
        }
        return res.json();
    })
    .then(data => {
        if (!data) return;
        if (data.success) {
            window.flyToHeader?.('cart', source, image);
            showToast('✓ Added: ' + (name.length > 28 ? name.substring(0, 28) + '…' : name), 'success');
            updateCartBadge(data.cartCount);
            updateCartPopover(data.cart, data.cartCount);
            return true;
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong. Please try again.', 'error');
    });
};

// ─── Fly-to-header animation (cart & wishlist) ─────────────────────────────
// A small copy of the product photo (cart) or a heart (wishlist) jumps in an arc
// from the clicked button into the header icon, which then bounces to say "got it".
(() => {
    document.addEventListener('click', e => { window._lastClickTarget = e.target; }, true);

    const style = document.createElement('style');
    style.textContent = `
        .fly-to-header { position:fixed; z-index:100000; pointer-events:none; border-radius:9999px; overflow:hidden;
            background:#fff center/cover no-repeat; border:2px solid #fff; box-shadow:0 10px 28px rgba(40,33,51,.32); will-change:transform,opacity; }
        .fly-to-header.is-heart { display:grid; place-items:center; color:#b0485f; background:#fff5f7; border-color:#efc9d1; }
        .fly-to-header.is-heart svg { width:58%; height:58%; fill:currentColor; }
        .fly-bump { animation:flyBump .6s cubic-bezier(.28,.84,.42,1); }
        @keyframes flyBump {
            0%   { transform:translateY(0) scale(1); }
            25%  { transform:translateY(-9px) scale(1.18,.9); }
            45%  { transform:translateY(0) scale(.92,1.1); }
            65%  { transform:translateY(-4px) scale(1.05); }
            100% { transform:translateY(0) scale(1); }
        }
        .fly-bump-wishlist { color:#b0485f !important; }
        @media (prefers-reduced-motion: reduce) { .fly-bump { animation:none; } }
    `;
    document.head.appendChild(style);

    const visible = el => { const r = el.getBoundingClientRect(); return r.width > 0 && r.height > 0 && r.bottom > 0 && r.top < innerHeight; };

    function headerIcon(kind) {
        const label = kind === 'cart' ? 'Cart' : 'Wishlist';
        const candidates = [
            ...document.querySelectorAll(`[data-buyer-nav="${kind}"] .buyer-top-icon`),
            ...document.querySelectorAll(`header a[aria-label^="${label}"], header a[title^="${label}"]`),
        ];
        return candidates.find(visible) || null;
    }

    // Cart: prefer the product photo near the clicked button. Wishlist: start at the heart itself.
    function startRect(source, kind) {
        let node = kind === 'cart' && source instanceof Element ? source : null;
        for (let depth = 0; node && depth < 7; depth++, node = node.parentElement) {
            const img = node.tagName === 'IMG' ? node : node.querySelector?.('img');
            if (img && visible(img)) return img.getBoundingClientRect();
        }
        const btn = source instanceof Element ? source.closest('button, a') || source : null;
        return btn && visible(btn) ? btn.getBoundingClientRect() : null;
    }

    function bump(target, kind) {
        const link = target.closest('a, summary') || target;
        link.classList.remove('fly-bump');
        void link.offsetWidth; // restart the animation
        link.classList.add('fly-bump');
        if (kind === 'wishlist') link.classList.add('fly-bump-wishlist');
        setTimeout(() => link.classList.remove('fly-bump', 'fly-bump-wishlist'), 650);
    }

    window.flyToHeader = function (kind, source, imageUrl) {
        const target = headerIcon(kind);
        if (!target) return;
        const from = startRect(source, kind);
        const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!from || reduceMotion || !Element.prototype.animate) return bump(target, kind);

        const to = target.getBoundingClientRect();
        const size = Math.max(36, Math.min(72, from.width, from.height));
        const flyer = document.createElement('div');
        flyer.className = 'fly-to-header' + (kind === 'wishlist' || !imageUrl ? ' is-heart' : '');
        if (kind === 'wishlist' || !imageUrl) {
            flyer.innerHTML = kind === 'wishlist'
                ? '<svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>'
                : '<svg viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>';
        } else {
            flyer.style.backgroundImage = `url("${String(imageUrl).replace(/"/g, '%22')}")`;
        }
        Object.assign(flyer.style, { width: size + 'px', height: size + 'px', left: '0px', top: '0px' });
        document.body.appendChild(flyer);

        // Arc path: hop up above both points, then drop into the icon.
        const x0 = from.left + from.width / 2 - size / 2, y0 = from.top + from.height / 2 - size / 2;
        const x1 = to.left + to.width / 2 - size / 2,     y1 = to.top + to.height / 2 - size / 2;
        const peak = Math.min(y0, y1) - Math.max(70, Math.abs(x1 - x0) * 0.15);
        const cx = (x0 + x1) / 2, cy = peak;
        const endScale = 18 / size;
        const frames = [];
        for (let i = 0; i <= 24; i++) {
            const t = i / 24;
            const x = (1 - t) ** 2 * x0 + 2 * (1 - t) * t * cx + t ** 2 * x1;
            const y = (1 - t) ** 2 * y0 + 2 * (1 - t) * t * cy + t ** 2 * y1;
            // Quick squash on take-off, then shrink as it lands in the icon.
            const s = t < 0.12 ? 1 + Math.sin(t / 0.12 * Math.PI) * 0.15 : 1 + (endScale - 1) * ((t - 0.12) / 0.88) ** 1.4;
            frames.push({ transform: `translate(${x}px, ${y}px) scale(${s}) rotate(${t * (kind === 'wishlist' ? -20 : 25)}deg)`, opacity: t > 0.9 ? 1 - (t - 0.9) * 6 : 1 });
        }
        const anim = flyer.animate(frames, { duration: 780, easing: 'cubic-bezier(.45,.05,.4,1)', fill: 'forwards' });
        anim.onfinish = anim.oncancel = () => { flyer.remove(); bump(target, kind); };
    };
})();
