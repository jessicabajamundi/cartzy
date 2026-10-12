(() => {
    function initWishlist() {
        const section = document.getElementById('daily-discover-section');
        if (!section || section.dataset.wishlistReady === 'true') return;
        section.dataset.wishlistReady = 'true';

        const status = document.getElementById('wishlist-status');
        const notify = (message, type = 'success') => {
            if (status) {
                status.textContent = message;
                status.classList.remove('sr-only');
                status.classList.toggle('text-red-700', type === 'error');
            }
            window.showToast?.(message, type);
        };

        section.addEventListener('submit', async (event) => {
            const form = event.target.closest('.discovery-wishlist-form');
            if (!form) return;
            event.preventDefault();
            const button = form.querySelector('.discovery-wishlist');
            if (!button || button.disabled) return;
            if (section.dataset.authenticated !== 'true') {
                window.location.assign(section.dataset.loginUrl);
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            try {
                const product = Object.fromEntries(new FormData(form));
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': product._token,
                    },
                    body: JSON.stringify(product),
                });
                if (response.status === 401) {
                    window.location.assign(section.dataset.loginUrl);
                    return;
                }
                if (response.status === 419) {
                    notify('Your session has expired. Refresh the page and try saving again.', 'error');
                    return;
                }
                if (!response.ok) throw new Error('Wishlist request failed');
                const result = await response.json();
                if (!result.success || typeof result.wished !== 'boolean') throw new Error('Invalid wishlist response');
                window.updateBuyerNavCount?.('wishlist', result.count);

                button.setAttribute('aria-pressed', String(result.wished));
                button.setAttribute('aria-label', `${result.wished ? 'Remove from wishlist' : 'Add to wishlist'}: ${product.name}`);
                button.title = result.wished ? 'Remove from wishlist' : 'Save to wishlist';
                notify(result.wished ? 'Saved to your wishlist.' : 'Removed from your wishlist.');
            } catch {
                notify('Could not update your wishlist. Please try again.', 'error');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWishlist, { once: true });
    } else {
        initWishlist();
    }
})();
