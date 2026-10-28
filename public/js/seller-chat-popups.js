// Seller floating chats (Messages and Logistics). Each stays hidden until its sidebar link
// ([data-chat-launch="<id>"]) is clicked: a single click opens the popup, a double click opens the
// full page. Minimizing (−) leaves a lower-right bubble; closing (×) removes the chat entirely.
// Whether a bubble is showing is remembered for this browser tab, so it follows you across pages.
(() => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const storageKey = id => `seller-chat-bubble:${id}`;
    const remember = (id, visible) => {
        try { visible ? sessionStorage.setItem(storageKey(id), '1') : sessionStorage.removeItem(storageKey(id)); } catch (_) { /* optional */ }
    };
    const remembered = id => { try { return sessionStorage.getItem(storageKey(id)) === '1'; } catch (_) { return false; } };

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
            const errors = { 401: 'Your session ended. Please log in again.', 403: 'This chat is only available to seller accounts.', 404: 'This conversation is no longer available.', 419: 'Your session expired. Please refresh the page.', 422: 'Please write a message up to 5000 characters.', 429: 'You are sending messages too quickly. Please wait a moment.' };
            throw new Error(errors[response.status] || `Something went wrong (error ${response.status}). Please try again.`);
        }
        return response.json();
    };

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };

    const setup = widget => {
        const id = widget.id;
        const $ = suffix => document.getElementById(`${id}-${suffix}`);
        const list = $('list'), messages = $('messages'), compose = $('compose'), input = $('input');
        const error = $('error'), back = $('back'), expand = $('expand'), title = $('title'), subtitle = $('subtitle');
        const bubbleWrap = $('bubble-wrap'), bubble = $('bubble'), badge = $('badge');
        const launchers = document.querySelectorAll(`[data-chat-launch="${id}"]`);
        const { conversationsUrl, inboxUrl } = widget.dataset;

        let sendUrl = null, threadUrl = null, loadId = 0, pollInterval = null;

        const showError = text => { error.textContent = text; error.hidden = !text; };
        const note = (parent, text) => {
            const p = el('p', parent === list ? 'cw-empty' : 'pd-chat-empty', text);
            parent.replaceChildren(p);
            return p;
        };
        const setUnread = total => {
            const count = Number(total) || 0;
            badge.hidden = count <= 0;
            badge.textContent = count > 99 ? '99+' : String(count);
        };
        const renderStatus = status => el('span', 'pd-msg-status is-' + status, status === 'seen' ? '✓✓ Seen' : status === 'delivered' ? '✓✓ Delivered' : '✓ Sent');

        const addMessage = message => {
            messages.querySelector('.pd-chat-empty')?.remove();
            const existing = messages.querySelector(`[data-msg-id="${message.id}"]`);
            if (existing) {
                existing.querySelector('.pd-msg-status')?.replaceWith(renderStatus(message.status));
                return;
            }
            const item = el('div', 'pd-chat-message' + (message.mine ? ' is-mine' : ''));
            item.dataset.msgId = message.id;
            const small = el('small', '', message.time || '');
            if (message.mine) small.append(message.time ? ' · ' : '', renderStatus(message.status));
            item.append(el('p', '', message.body), small);
            messages.append(item);
            messages.scrollTop = messages.scrollHeight;
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

        const stopPolling = () => { clearInterval(pollInterval); pollInterval = null; };
        const startPolling = url => {
            stopPolling();
            pollInterval = setInterval(async () => {
                if (widget.hidden || url !== threadUrl) return stopPolling();
                try {
                    const data = await request(url);
                    if (url !== threadUrl) return;
                    data.messages.forEach(addMessage);
                    setUnread(data.unread_total);
                } catch (_) { /* keep polling quietly */ }
            }, 4000);
        };

        const showList = async () => {
            const load = ++loadId;
            stopPolling();
            sendUrl = threadUrl = null;
            showError('');
            setHeader(widget.dataset.title, widget.dataset.subtitle, inboxUrl, false);
            note(list, 'Loading conversations…');
            try {
                const data = await request(conversationsUrl);
                if (load !== loadId) return;
                setUnread(data.unread_total);
                if (!data.conversations.length) return void note(list, widget.dataset.emptyText);
                list.replaceChildren(...data.conversations.map(conversation => {
                    const item = el('button', 'cw-item' + (conversation.unread ? ' is-unread' : ''));
                    item.type = 'button';
                    item.setAttribute('aria-label', `${conversation.name}, ${conversation.subtitle}${conversation.unread ? `, ${conversation.unread} unread` : ''}`);
                    const row = el('span', 'cw-row');
                    row.append(el('strong', '', conversation.name));
                    if (conversation.time) row.append(el('time', '', conversation.time));
                    const body = el('span', 'cw-body');
                    body.append(row, el('span', 'cw-sub', conversation.subtitle), el('span', 'cw-preview', conversation.preview || ''));
                    item.append(el('span', 'cw-avatar', (conversation.name || '?').charAt(0).toUpperCase()), body);
                    if (conversation.unread) item.append(el('span', 'cw-unread', conversation.unread > 99 ? '99+' : String(conversation.unread)));
                    item.addEventListener('click', () => openThread(conversation));
                    return item;
                }));
            } catch (e) {
                if (load !== loadId) return;
                const retry = el('button', 'cw-retry', 'Try again');
                retry.type = 'button';
                retry.addEventListener('click', showList);
                note(list, e.message).after(retry);
            }
        };

        const openThread = async conversation => {
            const load = ++loadId;
            stopPolling();
            showError('');
            threadUrl = conversation.thread_url;
            setHeader(conversation.name, conversation.subtitle, inboxUrl, true);
            note(messages, 'Loading conversation…');
            try {
                const data = await request(conversation.thread_url);
                if (load !== loadId) return;
                sendUrl = data.send_url;
                setHeader(data.title, data.subtitle, data.inbox_url, true);
                note(messages, 'No messages yet. Say hello!');
                data.messages.forEach(addMessage);
                setUnread(data.unread_total);
                input.focus();
                startPolling(conversation.thread_url);
            } catch (e) {
                if (load === loadId) { note(messages, ''); showError(e.message); }
            }
        };

        const setExpanded = open => {
            bubble.setAttribute('aria-expanded', String(open));
            launchers.forEach(link => link.setAttribute('aria-expanded', String(open)));
        };

        // Popup open; bubble hidden while the popup covers its spot.
        const open = () => {
            document.dispatchEvent(new CustomEvent('chat-popup:open', { detail: { source: id } }));
            remember(id, true);
            widget.hidden = false;
            bubbleWrap.hidden = true;
            setExpanded(true);
            if (back.hidden) showList();
            else if (threadUrl) startPolling(threadUrl);
            (back.hidden ? $('close') : input).focus();
        };

        // Popup closed, bubble stays so the chat can be reopened.
        const toBubble = () => {
            stopPolling();
            widget.hidden = true;
            bubbleWrap.hidden = false;
            setExpanded(false);
            refreshBadge();
        };

        // Chat closed: nothing shows until the sidebar link is clicked again.
        const dismiss = () => {
            stopPolling();
            remember(id, false);
            widget.hidden = true;
            bubbleWrap.hidden = true;
            setExpanded(false);
            launchers[0]?.focus();
        };

        const refreshBadge = async () => {
            if (bubbleWrap.hidden || document.hidden) return;
            try { setUnread((await request(conversationsUrl)).unread_total); } catch (_) { /* badge is best effort */ }
        };

        launchers.forEach(link => {
            link.setAttribute('aria-controls', id);
            link.setAttribute('aria-expanded', 'false');
            link.title = 'Click to chat here, double-click to open the full page';
            let clickTimer = null;
            link.addEventListener('click', event => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
                event.preventDefault();
                clearTimeout(clickTimer);
                if (event.detail >= 2) return window.location.assign(link.href);
                document.getElementById('seller-sidebar')?.classList.remove('open');
                const toggle = () => (widget.hidden ? open() : toBubble());
                if (event.detail === 0) return toggle();
                clickTimer = setTimeout(toggle, 250);
            });
        });

        bubble.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
            event.preventDefault();
            open();
        });
        $('minimize').addEventListener('click', () => { toBubble(); bubble.focus(); });
        $('close').addEventListener('click', dismiss);
        back.addEventListener('click', showList);
        widget.addEventListener('keydown', event => { if (event.key === 'Escape') { toBubble(); bubble.focus(); } });
        document.addEventListener('chat-popup:open', event => {
            if (event.detail?.source !== id && !widget.hidden) toBubble();
        });

        input.addEventListener('keydown', event => {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
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

        if (remembered(id)) toBubble();
        setInterval(refreshBadge, 30000);
    };

    document.querySelectorAll('.seller-chat-popup').forEach(setup);
})();
