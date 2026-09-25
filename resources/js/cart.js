/**
 * Cartzy Global Cart & Toast Notification System
 * Available on all store pages
 */

// ─── Toast Notification ────────────────────────────────────────────────────
window.showToast = function(message, type = 'success') {
    let toast = document.getElementById('cart-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cart-toast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;transform:translateY(120px);opacity:0;transition:all 0.35s cubic-bezier(.4,0,.2,1);pointer-events:none;';
        document.body.appendChild(toast);
    }
    const bg = type === 'success' ? '#111827' : '#dc2626';
    const icon = type === 'success'
        ? '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
        : '<svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    toast.innerHTML = `<div style="background:${bg};color:#fff;border-radius:12px;padding:12px 18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 32px rgba(0,0,0,0.28);min-width:240px;max-width:360px">${icon}<span>${message}</span></div>`;
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        toast.style.transform = 'translateY(120px)';
        toast.style.opacity = '0';
    }, 3000);
};

// ─── Update Cart Badge ─────────────────────────────────────────────────────
window.updateCartBadge = function(count) {
    document.querySelectorAll('.cart-badge-count').forEach(badge => {
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id, name, price, image, variation, quantity: 1 })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✓ Added: ' + (name.length > 28 ? name.substring(0, 28) + '…' : name), 'success');
            updateCartBadge(data.cartCount);
            updateCartPopover(data.cart, data.cartCount);
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
        }
    })
    .catch(() => {
        showToast('Something went wrong. Please try again.', 'error');
    });
};
