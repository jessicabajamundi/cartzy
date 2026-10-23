(() => {
    const form = document.getElementById('pd-message-form');
    const chat = document.getElementById('pd-chat');
    if (!form || !chat) return;

    const button = form.querySelector('button');
    const list = document.getElementById('pd-chat-messages');
    const compose = document.getElementById('pd-chat-compose');
    const input = document.getElementById('pd-chat-input');
    const error = document.getElementById('pd-chat-error');
    const expand = document.getElementById('pd-chat-expand');
    const token = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name="_token"]')?.value;
    const typingUrl = chat.dataset.typingUrl || '/chat/typing';

    let sendUrl = null;
    let clickTimer = null;
    let pollInterval = null;
    let lastTypingSent = 0;
    let stopTypingTimer = null;
    let currentThread = null; // { type: 'inquiry', id }
    let typingEl = null;

    const request = async (url, body) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(body || {}),
        });
        if (!response.ok) {
            const messages = { 419: 'Your session expired. Please refresh the page.', 429: 'You are sending messages too quickly. Please wait a moment.' };
            throw new Error(messages[response.status] || 'The message could not be sent. Please try again.');
        }
        return response.json();
    };

    const showError = message => { error.textContent = message; error.hidden = !message; };

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

    const ensureTypingEl = () => {
        if (!typingEl) {
            typingEl = document.createElement('div');
            typingEl.className = 'pd-chat-typing';
            typingEl.hidden = true;
            const bubble = document.createElement('div');
            bubble.className = 'pd-typing-bubble';
            bubble.append(document.createElement('span'), document.createElement('span'), document.createElement('span'));
            bubble.children[0].className = 'pd-typing-dot';
            bubble.children[1].className = 'pd-typing-dot';
            bubble.children[2].className = 'pd-typing-dot';
            const label = document.createElement('small');
            label.className = 'pd-typing-label';
            label.textContent = 'Seller is typing…';
            typingEl.append(bubble, label);
        }
        return typingEl;
    };

    const setTyping = (isTyping, name) => {
        ensureTypingEl();
        if (typingEl.parentNode !== list) {
            list.append(typingEl);
        }
        if (isTyping) {
            typingEl.querySelector('.pd-typing-label').textContent = `${name || 'Seller'} is typing…`;
            typingEl.hidden = false;
            list.scrollTop = list.scrollHeight;
        } else {
            typingEl.hidden = true;
        }
    };

    const addMessage = message => {
        list.querySelector('.pd-chat-empty')?.remove();
        let item = message.id ? list.querySelector(`[data-msg-id="${message.id}"]`) : null;
        if (item) {
            if (message.mine) {
                const small = item.querySelector('small');
                const statusEl = small?.querySelector('.pd-msg-status');
                if (statusEl) {
                    statusEl.replaceWith(renderStatus(message.status || 'sent'));
                }
            }
            return item;
        }

        item = document.createElement('div');
        item.className = 'pd-chat-message' + (message.mine ? ' is-mine' : '');
        if (message.id) item.dataset.msgId = message.id;

        const bubble = document.createElement('p');
        bubble.textContent = message.body;

        const time = document.createElement('small');
        time.textContent = message.time;
        if (message.mine) {
            time.append(' · ', renderStatus(message.status || 'sent'));
        }

        item.append(bubble, time);

        ensureTypingEl();
        if (typingEl && typingEl.parentNode === list) {
            list.insertBefore(item, typingEl);
        } else {
            list.append(item);
        }
        list.scrollTop = list.scrollHeight;
        return item;
    };

    const sendTypingPing = async (typing) => {
        if (!currentThread || !token) return;
        try {
            await fetch(typingUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ type: currentThread.type, id: currentThread.id, typing: Boolean(typing) }),
            });
        } catch (_) {}
    };

    const stopPolling = () => {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
        clearTimeout(stopTypingTimer);
        sendTypingPing(false);
        setTyping(false);
    };

    const startPolling = () => {
        stopPolling();
        pollInterval = setInterval(async () => {
            if (chat.hidden || !form.action) {
                stopPolling();
                return;
            }
            try {
                const data = await request(form.action);
                if (data.typing) {
                    setTyping(data.typing.is_typing, data.typing.name);
                }
                if (Array.isArray(data.messages)) {
                    data.messages.forEach(addMessage);
                }
            } catch (_) {}
        }, 2500);
    };

    let minimized = false;
    let docked = null;

    const setOpen = (open, { focusButton = true } = {}) => {
        minimized = false;
        docked?.remove();
        docked = null;
        form.hidden = false;
        chat.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
        if (open) {
            document.dispatchEvent(new CustomEvent('chat-popup:open', { detail: { source: 'page' } }));
            input.focus();
        } else {
            stopPolling();
            if (focusButton) button.focus();
        }
    };

    const minimize = () => {
        if (chat.hidden) return;
        stopPolling();
        minimized = true;
        chat.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        if (!window.ChatDock) return button.focus();
        form.hidden = button.classList.contains('chat-bubble');
        docked = window.ChatDock.add({
            label: document.getElementById('pd-chat-title').textContent,
            avatar: chat.dataset.avatar || null,
            conversation: true,
            onOpen: restore,
            onDismiss: () => { minimized = false; docked = null; form.hidden = false; button.focus(); },
        });
    };

    function restore() {
        docked?.remove();
        docked = null;
        minimized = false;
        form.hidden = false;
        chat.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        document.dispatchEvent(new CustomEvent('chat-popup:open', { detail: { source: 'page' } }));
        input.focus();
        list.scrollTop = list.scrollHeight;
        startPolling();
    }

    document.addEventListener('chat-popup:open', event => {
        if (event.detail?.source !== 'page' && !chat.hidden) minimize();
    });

    const openChat = async () => {
        if (minimized) return restore();
        if (!chat.hidden) return setOpen(false);
        setOpen(true);
        showError('');
        list.innerHTML = '<p class="pd-chat-empty">Loading conversation…</p>';
        try {
            const data = await request(form.action);
            sendUrl = data.send_url;
            expand.href = data.inbox_url;
            currentThread = {
                type: data.type || 'inquiry',
                id: data.thread_id || 0,
            };
            const empty = document.createElement('p');
            empty.className = 'pd-chat-empty';
            empty.textContent = chat.dataset.emptyText || 'Say hello! Ask the seller about sizes, stock, or shipping.';
            list.replaceChildren(empty);
            ensureTypingEl();
            list.append(typingEl);
            data.messages.forEach(addMessage);
            if (data.typing) {
                setTyping(data.typing.is_typing, data.typing.name);
            }
            startPolling();
        } catch (e) {
            list.innerHTML = '';
            showError(e.message);
        }
    };

    button.addEventListener('click', event => {
        event.preventDefault();
        clearTimeout(clickTimer);
        if (event.detail >= 2) return form.submit();
        if (event.detail === 0) return openChat();
        clickTimer = setTimeout(openChat, 250);
    });

    document.getElementById('pd-chat-close').addEventListener('click', () => setOpen(false));
    document.getElementById('pd-chat-minimize')?.addEventListener('click', minimize);
    chat.addEventListener('keydown', event => { if (event.key === 'Escape') setOpen(false); });

    input.addEventListener('input', () => {
        if (!currentThread) return;
        const now = Date.now();
        if (now - lastTypingSent > 1800) {
            lastTypingSent = now;
            sendTypingPing(true);
        }
        clearTimeout(stopTypingTimer);
        stopTypingTimer = setTimeout(() => {
            sendTypingPing(false);
        }, 2600);
    });

    input.addEventListener('blur', () => {
        clearTimeout(stopTypingTimer);
        sendTypingPing(false);
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            compose.requestSubmit();
        }
    });

    compose.addEventListener('submit', async event => {
        event.preventDefault();
        const body = input.value.trim();
        if (!body || !sendUrl) return;
        const send = compose.querySelector('button');
        send.disabled = true;
        showError('');
        clearTimeout(stopTypingTimer);
        sendTypingPing(false);

        try {
            const data = await request(sendUrl, { message: body });
            addMessage(data.message);
            input.value = '';
        } catch (e) {
            showError(e.message);
        } finally {
            send.disabled = false;
            input.focus();
        }
    });
})();
