(() => {
    const form = document.getElementById('st-follow-form');
    const button = document.getElementById('st-follow');
    const counter = document.getElementById('st-followers');

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;
        button.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value },
            });
            if (!response.ok) throw new Error('Request failed');
            const data = await response.json();
            button.textContent = data.following ? 'Following' : '+ Follow';
            button.classList.toggle('is-following', data.following);
            button.setAttribute('aria-pressed', String(data.following));
            if (counter) counter.textContent = Number(data.followers).toLocaleString();
            window.showToast?.(data.following ? 'You are now following this store.' : 'You unfollowed this store.', 'success');
        } catch {
            form.submit();
        } finally {
            button.disabled = false;
        }
    });

    const share = document.getElementById('st-share');
    share?.addEventListener('click', async () => {
        const url = share.dataset.url;
        try {
            if (navigator.share) {
                await navigator.share({ title: document.title, url });
            } else {
                await navigator.clipboard.writeText(url);
                window.showToast?.('Store link copied.', 'success');
                share.textContent = 'Link copied';
            }
        } catch { /* share sheet dismissed */ }
    });
})();
