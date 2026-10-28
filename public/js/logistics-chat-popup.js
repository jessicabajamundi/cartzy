(() => {
    const bubble = document.getElementById('logistics-chat-bubble');
    const bubbleBtn = document.getElementById('logistics-chat-bubble-btn');
    const badge = document.getElementById('logistics-chat-bubble-badge');
    const popup = document.getElementById('logistics-chat-popup');
    if (!popup || !bubbleBtn) return;

    const title = document.getElementById('logistics-chat-title');
    const subtitle = document.getElementById('logistics-chat-subtitle');
    const back = document.getElementById('logistics-chat-back');
    const expand = document.getElementById('logistics-chat-expand');
    const minimize = document.getElementById('logistics-chat-minimize');
    const close = document.getElementById('logistics-chat-close');
    const list = document.getElementById('logistics-chat-list');
    const messages = document.getElementById('logistics-chat-messages');
    const compose = document.getElementById('logistics-chat-compose');
    const input = document.getElementById('logistics-chat-input');
    const error = document.getElementById('logistics-chat-error');

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const conversationsUrl = popup.dataset.conversationsUrl;
    const inboxUrl = popup.dataset.inboxUrl || '/logistics/chat';

    let sendUrl = null;
    let currentThreadUrl = null;
    let pollInterval = null;
    let clickTimer = null;
    let loadId = 0;

    const request = async (url, options = {}) => {
        let response;
        try {
            response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                ...options,
            });
        } catch (_) {
            throw new Error('Connection lost. Please check your internet and try again.');
        }

        if (!response.ok) {
            const errors = {
                401: 'Session ended. Please log in again.',
                419: 'Session expired. Please refresh the page.',
                429: 'Sending too quickly. Please wait a moment.',
            };
            let detail = '';
            try { detail = (await response.json()).message || ''; } catch (_) {}
            throw new Error(errors[response.status] || `Error (${response.status}${detail ? ': ' + detail : ''}). Please try again.`);
        }

        return response.json();
    };

    const showError = msg => {
        if (!error) return;
        error.textContent = msg || '';
        error.hidden = !msg;
    };

    const tickSvg = status => {
        if (status === 'sent') {
            return `<svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 8.5l3.5 3.5 7.5-7.5"/></svg>`;
        }
        return `<svg width="15" height="12" viewBox="0 0 20 16" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1.5 8.5l3.5 3.5 7.5-7.5"/><path d="M6.5 8.5l3.5 3.5 7.5-7.5"/></svg>`;
    };

    const renderStatus = (status = 'sent') => {
        const s = (status || 'sent').toLowerCase();
        const label = s === 'seen' ? 'Seen' : (s === 'delivered' ? 'Delivered' : 'Sent');
        const span = document.createElement('span');
        span.className = 'pd-msg-status is-' + s;
        span.innerHTML = `${tickSvg(s)} <span>${label}</span>`;
        return span;
    };

    const setUnreadBadge = count => {
        if (!badge) return;
        const total = Number(count) || 0;
        if (total <= 0) {
            badge.hidden = true;
            badge.textContent = '';
        } else {
            badge.hidden = false;
            badge.textContent = total > 99 ? '99+' : String(total);
        }
    };

    const addMessage = msg => {
        messages.querySelector('.pd-chat-empty')?.remove();
        if (msg.id) {
            const existing = messages.querySelector(`[data-msg-id="${msg.id}"]`);
            if (existing) {
                if (msg.mine) {
                    const small = existing.querySelector('small');
                    const statusEl = small?.querySelector('.pd-msg-status');
                    if (statusEl) statusEl.replaceWith(renderStatus(msg.status || 'sent'));
                }
                return existing;
            }
        }

        const item = document.createElement('div');
        item.className = 'pd-chat-message' + (msg.mine ? ' is-mine' : '');
        if (msg.id) item.dataset.msgId = msg.id;

        const bubbleP = document.createElement('p');
        bubbleP.textContent = msg.body || msg.text || '';

        const timeSmall = document.createElement('small');
        timeSmall.textContent = msg.time || '';
        if (msg.mine) {
            timeSmall.append(' · ', renderStatus(msg.status || 'sent'));
        }

        item.append(bubbleP, timeSmall);
        messages.append(item);
        messages.scrollTop = messages.scrollHeight;
        return item;
    };

    const stopPolling = () => {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    };

    const startPolling = url => {
        stopPolling();
        pollInterval = setInterval(async () => {
            if (popup.hidden || url !== currentThreadUrl) {
                return stopPolling();
            }
            try {
                const data = await request(url);
                if (url !== currentThreadUrl) return;
                if (Array.isArray(data.messages)) {
                    data.messages.forEach(addMessage);
                }
                if (data.unread_total !== undefined) {
                    setUnreadBadge(data.unread_total);
                }
            } catch (_) {}
        }, 3000);
    };

    const showList = async () => {
        const currentLoad = ++loadId;
        stopPolling();
        sendUrl = null;
        currentThreadUrl = null;
        try { sessionStorage.removeItem('logistics-chat-thread'); } catch (_) {}
        showError('');

        title.textContent = popup.dataset.title || 'Cartzy Logistics Chat';
        subtitle.textContent = popup.dataset.subtitle || 'Pickups, deliveries, and rider updates';
        expand.href = inboxUrl;
        back.hidden = true;
        list.hidden = false;
        messages.hidden = true;
        compose.hidden = true;

        list.innerHTML = '<p class="cw-empty">Loading conversations…</p>';

        try {
            const data = await request(conversationsUrl);
            if (currentLoad !== loadId) return;

            if (data.unread_total !== undefined) {
                setUnreadBadge(data.unread_total);
            }

            if (!Array.isArray(data.conversations) || data.conversations.length === 0) {
                list.innerHTML = `<p class="cw-empty">${popup.dataset.emptyText || 'No conversations yet.'}</p>`;
                return;
            }

            list.innerHTML = '';
            data.conversations.forEach(c => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'cw-item' + (c.unread > 0 ? ' is-unread' : '');
                item.setAttribute('aria-label', `${c.name}, ${c.subtitle}`);

                const avatar = document.createElement('span');
                avatar.className = 'cw-avatar';
                avatar.textContent = (c.name || 'C').charAt(0).toUpperCase();

                const body = document.createElement('span');
                body.className = 'cw-body';

                const row = document.createElement('span');
                row.className = 'cw-row';

                const nameStrong = document.createElement('strong');
                nameStrong.textContent = c.name;

                const timeEl = document.createElement('time');
                timeEl.textContent = c.time || '';

                row.append(nameStrong, timeEl);

                const sub = document.createElement('span');
                sub.className = 'cw-sub';
                sub.textContent = c.subtitle || '';

                const prev = document.createElement('span');
                prev.className = 'cw-preview';
                prev.textContent = c.preview || '';

                body.append(row, sub, prev);
                item.append(avatar, body);

                if (c.unread > 0) {
                    const unreadEl = document.createElement('span');
                    unreadEl.className = 'cw-unread';
                    unreadEl.textContent = c.unread > 99 ? '99+' : String(c.unread);
                    item.append(unreadEl);
                }

                item.addEventListener('click', () => openThread(c.thread_url));
                list.append(item);
            });
        } catch (e) {
            if (currentLoad !== loadId) return;
            list.innerHTML = `<p class="cw-empty">${e.message}</p>`;
        }
    };

    const openThread = async (threadUrl) => {
        const currentLoad = ++loadId;
        stopPolling();
        showError('');
        currentThreadUrl = threadUrl;
        try { sessionStorage.setItem('logistics-chat-thread', threadUrl); } catch (_) {}

        back.hidden = false;
        list.hidden = true;
        messages.hidden = false;
        compose.hidden = false;
        messages.innerHTML = '<p class="pd-chat-empty">Loading conversation…</p>';

        try {
            const data = await request(threadUrl);
            if (currentLoad !== loadId) return;

            title.textContent = data.title || 'Chat';
            subtitle.textContent = data.subtitle || '';
            sendUrl = data.send_url;
            expand.href = data.inbox_url || inboxUrl;

            if (data.input_placeholder) {
                input.placeholder = data.input_placeholder;
            } else {
                input.placeholder = 'Ask about this product…';
            }

            const emptyText = data.empty_text || popup.dataset.emptyText || 'Say hello! Ask the seller about sizes, stock, or shipping.';

            messages.innerHTML = '';
            if (Array.isArray(data.messages) && data.messages.length > 0) {
                data.messages.forEach(addMessage);
            } else {
                const emptyP = document.createElement('p');
                emptyP.className = 'pd-chat-empty';
                emptyP.textContent = emptyText;
                messages.append(emptyP);
            }

            if (data.unread_total !== undefined) {
                setUnreadBadge(data.unread_total);
            }

            input.focus();
            messages.scrollTop = messages.scrollHeight;
            startPolling(threadUrl);
        } catch (e) {
            if (currentLoad !== loadId) return;
            messages.innerHTML = '';
            showError(e.message);
        }
    };

    const openPopup = async () => {
        popup.hidden = false;
        bubble.hidden = true;
        bubbleBtn.setAttribute('aria-expanded', 'true');

        let savedThread = null;
        try { savedThread = sessionStorage.getItem('logistics-chat-thread'); } catch (_) {}

        if (savedThread) {
            openThread(savedThread);
        } else {
            // Fetch list, and if there are conversations, auto-open the first one (like Gaabor Store)
            // with back button enabled so the user instantly sees the exact screenshot design!
            try {
                const data = await request(conversationsUrl);
                if (Array.isArray(data.conversations) && data.conversations.length > 0) {
                    openThread(data.conversations[0].thread_url);
                } else {
                    showList();
                }
            } catch (_) {
                showList();
            }
        }
    };

    const minimizePopup = () => {
        stopPolling();
        popup.hidden = true;
        bubble.hidden = false;
        bubbleBtn.setAttribute('aria-expanded', 'false');
        bubbleBtn.focus();
    };

    const closePopup = () => {
        stopPolling();
        try { sessionStorage.removeItem('logistics-chat-thread'); } catch (_) {}
        popup.hidden = true;
        bubble.hidden = false;
        bubbleBtn.setAttribute('aria-expanded', 'false');
        bubbleBtn.focus();
    };

    bubbleBtn.addEventListener('click', event => {
        event.preventDefault();
        clearTimeout(clickTimer);
        if (event.detail >= 2) {
            window.location.href = inboxUrl;
            return;
        }
        if (event.detail === 0) {
            if (popup.hidden) openPopup(); else minimizePopup();
            return;
        }
        clickTimer = setTimeout(() => {
            if (popup.hidden) openPopup(); else minimizePopup();
        }, 220);
    });

    back.addEventListener('click', showList);
    minimize.addEventListener('click', minimizePopup);
    close.addEventListener('click', closePopup);
    popup.addEventListener('keydown', event => {
        if (event.key === 'Escape') minimizePopup();
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            compose.requestSubmit();
        }
    });

    compose.addEventListener('submit', async event => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text || !sendUrl) return;

        const sendBtn = compose.querySelector('button');
        sendBtn.disabled = true;
        showError('');

        try {
            const data = await request(sendUrl, {
                method: 'POST',
                body: JSON.stringify({ message: text }),
            });
            if (data.message) {
                addMessage(data.message);
            }
            input.value = '';
        } catch (e) {
            showError(e.message);
        } finally {
            sendBtn.disabled = false;
            input.focus();
            messages.scrollTop = messages.scrollHeight;
        }
    });
})();
