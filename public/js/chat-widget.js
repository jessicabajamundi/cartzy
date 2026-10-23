(() => {
    const widget = document.getElementById('cw');
    const trigger = document.querySelector('.buyer-top-nav [data-buyer-nav="messages"]');
    if (!widget || !trigger) return;

    const list = document.getElementById('cw-list');
    const messages = document.getElementById('cw-messages');
    const compose = document.getElementById('cw-compose');
    const input = document.getElementById('cw-input');
    const error = document.getElementById('cw-error');
    const back = document.getElementById('cw-back');
    const expand = document.getElementById('cw-expand');
    const title = document.getElementById('cw-title');
    const subtitle = document.getElementById('cw-subtitle');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const typingUrl = widget.dataset.typingUrl || '/chat/typing';

    let sendUrl = null;
    let currentThreadUrl = null;
    let currentThread = null; // { type, id }
    let currentAvatar = null;
    let clickTimer = null;
    let loadId = 0;
    let pollInterval = null;
    let lastTypingSent = 0;
    let stopTypingTimer = null;
    let typingEl = null;

    trigger.setAttribute('aria-controls', 'cw');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.title = 'Messages: click to chat here, double-click to open the Messages page';

    const request = async (url, options = {}) => {
        let response;
        try {
            response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                ...options,
            });
        } catch (_) {
            throw new Error('Could not reach the server. Check your connection and try again.');
        }
        if (!response.ok) {
            const errors = { 401: 'Your session ended. Please log in again.', 403: 'Messages are only available to active buyer accounts.', 419: 'Your session expired. Please refresh the page.', 429: 'You are sending messages too quickly. Please wait a moment.' };
            let detail = '';
            try { detail = (await response.json()).message || ''; } catch (_) { /* not JSON */ }
            console.error('Chat request failed', response.status, url, detail);
            throw new Error(errors[response.status] || `Something went wrong (error ${response.status}${detail ? ': ' + detail : ''}). Please try again.`);
        }
        try {
            return await response.json();
        } catch (_) {
            console.error('Chat response was not JSON', response.status, response.url);
            throw new Error('Unexpected response from the server. Please refresh the page and try again.');
        }
    };

    const showError = text => { error.textContent = text; error.hidden = !text; };

    const note = (parent, text) => {
        const p = document.createElement('p');
        p.className = parent === list ? 'cw-empty' : 'pd-chat-empty';
        p.textContent = text;
        parent.replaceChildren(p);
        return p;
    };

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
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

    const ensureTypingEl = () => {
        if (!typingEl) {
            typingEl = el('div', 'pd-chat-typing');
            typingEl.id = 'cw-typing';
            typingEl.hidden = true;
            const bubble = el('div', 'pd-typing-bubble');
            bubble.append(el('span', 'pd-typing-dot'), el('span', 'pd-typing-dot'), el('span', 'pd-typing-dot'));
            const label = el('small', 'pd-typing-label', 'typing…');
            typingEl.append(bubble, label);
        }
        return typingEl;
    };

    const setTyping = (isTyping, name) => {
        ensureTypingEl();
        if (typingEl.parentNode !== messages) {
            messages.append(typingEl);
        }
        if (isTyping) {
            typingEl.querySelector('.pd-typing-label').textContent = `${name || 'Seller'} is typing…`;
            typingEl.hidden = false;
            messages.scrollTop = messages.scrollHeight;
        } else {
            typingEl.hidden = true;
        }
    };

    const addMessage = message => {
        messages.querySelector('.pd-chat-empty')?.remove();
        let item = message.id ? messages.querySelector(`[data-msg-id="${message.id}"]`) : null;
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

        item = el('div', 'pd-chat-message' + (message.mine ? ' is-mine' : ''));
        if (message.id) item.dataset.msgId = message.id;

        const small = el('small', '', message.time);
        if (message.mine) {
            small.append(' · ', renderStatus(message.status || 'sent'));
        }

        item.append(el('p', '', message.body), small);

        ensureTypingEl();
        if (typingEl && typingEl.parentNode === messages) {
            messages.insertBefore(item, typingEl);
        } else {
            messages.append(item);
        }
        messages.scrollTop = messages.scrollHeight;
        return item;
    };

    const setHeader = (heading, sub, href, inThread) => {
        title.textContent = heading;
        subtitle.textContent = sub;
        expand.href = href;
        back.hidden = !inThread;
        list.hidden = inThread;
        messages.hidden = !inThread;
        compose.hidden = !inThread;
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

    const startPolling = (threadUrl) => {
        stopPolling();
        pollInterval = setInterval(async () => {
            if (widget.hidden || back.hidden || !currentThreadUrl || threadUrl !== currentThreadUrl) {
                stopPolling();
                return;
            }
            try {
                const data = await request(threadUrl);
                if (threadUrl !== currentThreadUrl) return;
                if (data.typing) {
                    setTyping(data.typing.is_typing, data.typing.name);
                }
                if (Array.isArray(data.messages)) {
                    data.messages.forEach(addMessage);
                }
                if (data.unread_total !== null && data.unread_total !== undefined) {
                    window.updateBuyerNavCount?.('messages', data.unread_total);
                }
            } catch (_) {}
        }, 2500);
    };

    const showList = async () => {
        const id = ++loadId;
        stopPolling();
        sendUrl = null;
        currentThreadUrl = null;
        currentThread = null;
        showError('');
        setHeader('Messages', 'Your recent conversations', widget.dataset.inboxUrl, false);
        note(list, 'Loading conversations…');
        try {
            const data = await request(widget.dataset.conversationsUrl);
            if (id !== loadId) return;
            if (!data.conversations.length) {
                const empty = note(list, 'No conversations yet. Use “Message seller” on a product or the Chat bubble on a store page to start one. ');
                const link = el('a', '', 'Open Messages');
                link.href = widget.dataset.inboxUrl;
                empty.append(link);
                return;
            }
            list.replaceChildren(...data.conversations.map(conversation => {
                const item = el('button', 'cw-item' + (conversation.unread ? ' is-unread' : ''));
                item.type = 'button';
                item.setAttribute('aria-label', `${conversation.name}, ${conversation.subtitle}${conversation.unread ? `, ${conversation.unread} unread` : ''}`);
                const row = el('span', 'cw-row');
                row.append(el('strong', '', conversation.name));
                if (conversation.time) row.append(el('time', '', conversation.time));
                const body = el('span', 'cw-body');
                body.append(row, el('span', 'cw-sub', conversation.subtitle), el('span', 'cw-preview', conversation.preview || ''));
                item.append(el('span', 'cw-avatar', (conversation.name || 'S').charAt(0).toUpperCase()), body);
                if (conversation.unread) item.append(el('span', 'cw-unread', conversation.unread > 99 ? '99+' : String(conversation.unread)));
                item.addEventListener('click', () => openThread(conversation));
                return item;
            }));
        } catch (e) {
            if (id !== loadId) return;
            const retry = el('button', 'cw-retry', 'Try again');
            retry.type = 'button';
            retry.addEventListener('click', showList);
            note(list, e.message).after(retry);
        }
    };

    const openThread = async conversation => {
        const id = ++loadId;
        stopPolling();
        showError('');
        currentAvatar = conversation.avatar || null;
        currentThreadUrl = conversation.thread_url;

        const type = conversation.type || (conversation.subtitle && conversation.subtitle.startsWith('Order') ? 'order' : 'inquiry');
        const threadId = conversation.thread_id || (conversation.key ? parseInt(conversation.key.split('-')[1]) : 0);
        currentThread = { type, id: threadId };

        setHeader(conversation.name, conversation.subtitle, widget.dataset.inboxUrl, true);
        note(messages, 'Loading conversation…');
        try {
            const data = await request(conversation.thread_url);
            if (id !== loadId) return;
            sendUrl = data.send_url;
            currentAvatar = data.avatar || currentAvatar;
            currentThread = {
                type: data.type || type,
                id: data.thread_id || threadId,
            };
            setHeader(data.title, data.subtitle, data.inbox_url, true);
            note(messages, 'No messages yet. Say hello!');
            ensureTypingEl();
            messages.append(typingEl);
            data.messages.forEach(addMessage);
            if (data.typing) {
                setTyping(data.typing.is_typing, data.typing.name);
            }
            if (data.unread_total !== null && data.unread_total !== undefined) {
                window.updateBuyerNavCount?.('messages', data.unread_total);
            }
            input.focus();
            startPolling(conversation.thread_url);
        } catch (e) {
            if (id === loadId) { note(messages, ''); showError(e.message); }
        }
    };

    let docked = null; // dock bubble while minimized

    const focusInside = () => (back.hidden ? widget.querySelector('#cw-close') : input).focus();

    const setOpen = (open, { focusTrigger = true } = {}) => {
        if (open && docked) return restore();
        docked?.remove();
        docked = null;
        widget.hidden = !open;
        trigger.setAttribute('aria-expanded', String(open));
        if (open) {
            document.dispatchEvent(new CustomEvent('chat-popup:open', { detail: { source: 'header' } }));
            showList();
            focusInside();
        } else {
            stopPolling();
            if (focusTrigger) trigger.focus();
        }
    };

    // Minimize keeps the current list or conversation and leaves a bubble to reopen it.
    const minimize = () => {
        if (widget.hidden || docked) return;
        stopPolling();
        if (!window.ChatDock) return setOpen(false);
        widget.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        docked = window.ChatDock.add({
            label: title.textContent,
            avatar: currentAvatar,
            conversation: !back.hidden,
            onOpen: restore,
            onDismiss: () => { docked = null; trigger.focus(); },
        });
    };

    function restore() {
        docked?.remove();
        docked = null;
        widget.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        document.dispatchEvent(new CustomEvent('chat-popup:open', { detail: { source: 'header' } }));
        if (back.hidden) {
            showList();
        } else if (currentThreadUrl) {
            startPolling(currentThreadUrl);
        }
        focusInside();
    }

    // Single click opens chat popup; double click goes to full Messages page.
    trigger.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
        event.preventDefault();
        clearTimeout(clickTimer);
        if (event.detail >= 2) return window.location.assign(trigger.href);
        if (event.detail === 0) return setOpen(widget.hidden);
        clickTimer = setTimeout(() => setOpen(widget.hidden), 250);
    });

    document.addEventListener('chat-popup:open', event => {
        if (event.detail?.source !== 'header' && !widget.hidden) minimize();
    });

    back.addEventListener('click', showList);
    document.getElementById('cw-close').addEventListener('click', () => setOpen(false));
    document.getElementById('cw-minimize').addEventListener('click', minimize);
    widget.addEventListener('keydown', event => { if (event.key === 'Escape') setOpen(false); });

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
            const data = await request(sendUrl, { method: 'POST', body: JSON.stringify({ message: body }) });
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
