(() => {
    function init() {
        const bubbleWrap = document.getElementById('logistics-chat-bubble-wrap') || document.querySelector('.logistics-chat-bubble-form');
        const bubbleBtn = document.getElementById('logistics-chat-bubble-btn') || bubbleWrap?.querySelector('.chat-bubble');
        const bubbleCancelBtn = document.getElementById('logistics-bubble-cancel-btn') || bubbleWrap?.querySelector('.logistics-bubble-cancel-btn');
        const badge = document.getElementById('logistics-chat-bubble-badge') || bubbleWrap?.querySelector('.chat-bubble-badge');
        const launchers = document.querySelectorAll('[data-chat-launch="logistics-chat"], #logistics-sidebar-chat-launch');

        let popup = document.getElementById('logistics-chat-popup');
        if (!popup) {
            popup = document.createElement('section');
            popup.className = 'pd-chat cw';
            popup.id = 'logistics-chat-popup';
            popup.role = 'dialog';
            popup.setAttribute('aria-labelledby', 'logistics-chat-title');
            popup.dataset.conversationsUrl = '/logistics/chat-popup/conversations';
            popup.dataset.inboxUrl = '/logistics/chat';
            popup.dataset.title = 'Cartzy Logistics Chat';
            popup.dataset.subtitle = 'Pickups, deliveries, and rider updates';
            popup.dataset.emptyText = 'Say hello! Ask the seller about sizes, stock, or shipping.';
            popup.hidden = true;
            popup.innerHTML = `
                <header class="pd-chat-head">
                    <button class="pd-chat-icon" type="button" id="logistics-chat-back" aria-label="Back to conversations" title="Back" hidden>&larr;</button>
                    <div>
                        <strong id="logistics-chat-title">Cartzy Logistics Chat</strong>
                        <small id="logistics-chat-subtitle">Pickups, deliveries, and rider updates</small>
                    </div>
                    <a class="pd-chat-icon" id="logistics-chat-expand" href="/logistics/chat" aria-label="Open full chat page" title="Open full chat">&#x2197;</a>
                    <button class="pd-chat-icon pd-chat-minimize" type="button" id="logistics-chat-minimize" aria-label="Minimize to bubble" title="Minimize">&minus;</button>
                    <button class="pd-chat-icon" type="button" id="logistics-chat-close" aria-label="Close chat" title="Close">&times;</button>
                </header>
                <div class="cw-list" id="logistics-chat-list" aria-live="polite"></div>
                <div class="pd-chat-messages" id="logistics-chat-messages" aria-live="polite" hidden></div>
                <form class="pd-chat-compose" id="logistics-chat-compose" hidden>
                    <label class="pd-chat-sr" for="logistics-chat-input">Message</label>
                    <textarea id="logistics-chat-input" name="message" rows="1" maxlength="5000" placeholder="Ask about this product…" required></textarea>
                    <button type="submit" aria-label="Send message"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7z M22 2 11 13"/></svg></button>
                </form>
                <p class="pd-chat-error" id="logistics-chat-error" role="alert" hidden></p>
            `;
            document.body.appendChild(popup);
        }

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
        const conversationsUrl = popup.dataset.conversationsUrl || '/logistics/chat-popup/conversations';
        const inboxUrl = popup.dataset.inboxUrl || '/logistics/chat';

        let sendUrl = null;
        let currentThreadUrl = null;
        let pollInterval = null;
        let bubbleClickTimer = null;
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

        const refreshBadge = async () => {
            if (!bubbleWrap || bubbleWrap.hidden || document.hidden) return;
            try {
                const data = await request(conversationsUrl);
                if (data.unread_total !== undefined) {
                    setUnreadBadge(data.unread_total);
                }
            } catch (_) {}
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

        // Open chat popup (hides floating bubble)
        const openPopup = async () => {
            popup.hidden = false;
            if (bubbleWrap) bubbleWrap.hidden = true;
            if (bubbleBtn) bubbleBtn.setAttribute('aria-expanded', 'true');
            try { sessionStorage.setItem('logistics-chat-state', 'open'); } catch (_) {}

            let savedThread = null;
            try { savedThread = sessionStorage.getItem('logistics-chat-thread'); } catch (_) {}

            if (savedThread) {
                openThread(savedThread);
            } else {
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

        // Minimize popup to floating side bubble
        const showBubbleOnly = () => {
            stopPolling();
            popup.hidden = true;
            if (bubbleWrap) {
                bubbleWrap.hidden = false;
                bubbleWrap.style.display = '';
            }
            if (bubbleBtn) {
                bubbleBtn.setAttribute('aria-expanded', 'false');
                bubbleBtn.focus();
            }
            try { sessionStorage.setItem('logistics-chat-state', 'bubble'); } catch (_) {}
            refreshBadge();
        };

        // Completely hide/cancel both popup and bubble
        const hideAll = () => {
            stopPolling();
            popup.hidden = true;
            if (bubbleWrap) {
                bubbleWrap.hidden = true;
            }
            if (bubbleBtn) {
                bubbleBtn.setAttribute('aria-expanded', 'false');
            }
            try {
                sessionStorage.removeItem('logistics-chat-state');
                sessionStorage.removeItem('logistics-chat-thread');
            } catch (_) {}
            launchers[0]?.focus();
        };

        // Initialize state on page load
        let savedState = null;
        try { savedState = sessionStorage.getItem('logistics-chat-state'); } catch (_) {}

        if (savedState === 'open') {
            openPopup();
        } else if (savedState === 'bubble') {
            showBubbleOnly();
        } else {
            // Default: completely hidden until clicked in sidebar
            hideAll();
        }

        // Bind Sidebar Launchers ([data-chat-launch="logistics-chat"])
        launchers.forEach(link => {
            link.setAttribute('aria-controls', 'logistics-chat-popup');
            link.title = 'Click to chat here, double-click to open Messages';
            let clickTimer = null;

            link.addEventListener('click', event => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;

                // Double click: open full inbox page
                if (event.detail >= 2) {
                    return window.location.assign(link.href || inboxUrl);
                }

                // If currently on /logistics/chat, let standard navigation happen
                if (window.location.pathname === '/logistics/chat') {
                    return;
                }

                event.preventDefault();
                clearTimeout(clickTimer);

                const toggle = () => {
                    if (popup.hidden) {
                        openPopup();
                    } else {
                        showBubbleOnly();
                    }
                };

                if (event.detail === 0) return toggle();
                clickTimer = setTimeout(toggle, 200);
            });
        });

        // Floating side bubble click (reopens popup or double-click to inbox)
        if (bubbleBtn) {
            bubbleBtn.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                clearTimeout(bubbleClickTimer);

                if (event.detail >= 2) {
                    window.location.href = inboxUrl;
                    return;
                }
                if (event.detail === 0) {
                    if (popup.hidden) openPopup(); else showBubbleOnly();
                    return;
                }
                bubbleClickTimer = setTimeout(() => {
                    if (popup.hidden) openPopup(); else showBubbleOnly();
                }, 180);
            });
        }

        // Floating side bubble cancel "X" button (completely cancels and hides the side bubble)
        if (bubbleCancelBtn) {
            bubbleCancelBtn.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                hideAll();
            });
        }

        // Header controls
        if (back) back.addEventListener('click', showList);
        if (minimize) minimize.addEventListener('click', showBubbleOnly);
        if (close) close.addEventListener('click', hideAll);

        popup.addEventListener('keydown', event => {
            if (event.key === 'Escape') showBubbleOnly();
        });

        if (input) {
            input.addEventListener('keydown', event => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    compose.requestSubmit();
                }
            });
        }

        if (compose) {
            compose.onsubmit = async event => {
                event.preventDefault();
                const text = input.value.trim();
                if (!text || !sendUrl) return;

                const sendBtn = compose.querySelector('button');
                if (sendBtn) sendBtn.disabled = true;
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
                    if (sendBtn) sendBtn.disabled = false;
                    input.focus();
                    messages.scrollTop = messages.scrollHeight;
                }
            };
        }

        // Periodic badge refresh every 30 seconds
        setInterval(refreshBadge, 30000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
