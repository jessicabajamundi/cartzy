// Minimized chat windows become round bubbles in a dock at the bottom-right corner.
// The bubble shows the conversation's profile (seller logo, else their initial), or a message
// icon when no conversation is open.
// ChatDock.add({ label, avatar, conversation, onOpen, onDismiss }) returns { remove() }.
window.ChatDock = window.ChatDock || (() => {
    const ICON = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z"/></svg>';
    let dock = null;
    const ensureDock = () => {
        if (!dock) {
            dock = document.createElement('div');
            dock.className = 'chat-dock';
            dock.setAttribute('role', 'region');
            dock.setAttribute('aria-label', 'Minimized chats');
            document.body.append(dock);
        }
        return dock;
    };

    const fillFace = (face, { label, avatar, conversation }) => {
        face.replaceChildren();
        face.classList.remove('is-icon', 'is-initial', 'is-photo');
        if (conversation && avatar) {
            const img = document.createElement('img');
            img.src = avatar;
            img.alt = '';
            img.addEventListener('error', () => fillFace(face, { label, conversation })); // broken logo: fall back to the initial
            face.append(img);
            face.classList.add('is-photo');
        } else if (conversation && label) {
            face.textContent = label.trim().charAt(0).toUpperCase();
            face.classList.add('is-initial');
        } else {
            face.innerHTML = ICON;
            face.classList.add('is-icon');
        }
    };

    return {
        add({ label, avatar = null, conversation = false, onOpen, onDismiss }) {
            const item = document.createElement('div');
            item.className = 'chat-mini';
            const open = document.createElement('button');
            open.type = 'button';
            open.className = 'chat-mini-open';
            const name = conversation ? label : 'Messages';
            open.setAttribute('aria-label', `Reopen chat: ${name}`);
            open.title = name;
            fillFace(open, { label, avatar, conversation });
            const dismiss = document.createElement('button');
            dismiss.type = 'button';
            dismiss.className = 'chat-mini-close';
            dismiss.textContent = '×';
            dismiss.setAttribute('aria-label', `Close chat: ${name}`);
            dismiss.title = 'Close chat';
            item.append(open, dismiss);

            const handle = {
                remove() {
                    item.remove();
                    if (dock && !dock.children.length) { dock.remove(); dock = null; }
                },
            };
            open.addEventListener('click', () => { handle.remove(); onOpen?.(); });
            dismiss.addEventListener('click', () => { handle.remove(); onDismiss?.(); });
            ensureDock().append(item);
            open.focus();
            return handle;
        },
    };
})();
