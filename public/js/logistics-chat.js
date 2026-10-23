(() => {
    const search = document.getElementById('conversation-search');
    const contacts = [...document.querySelectorAll('[data-contact-role]')];
    const filters = [...document.querySelectorAll('[data-chat-filter]')];
    let role = 'all';
    const filterContacts = () => {
        const query = search.value.trim().toLocaleLowerCase();
        contacts.forEach(contact => {
            contact.hidden = (role !== 'all' && contact.dataset.contactRole !== role)
                || !contact.textContent.toLocaleLowerCase().includes(query);
        });
        document.getElementById('conversation-empty').hidden = contacts.some(contact => !contact.hidden);
    };
    search.addEventListener('input', filterContacts);
    filters.forEach(button => button.addEventListener('click', () => {
        role = button.dataset.chatFilter;
        filters.forEach(filter => filter.setAttribute('aria-pressed', String(filter === button)));
        filterContacts();
    }));

    const history = document.getElementById('chatMessages');
    history.scrollTop = history.scrollHeight;
    const form = document.getElementById('hub-chat-form');
    const message = document.getElementById('hub-chat-message');
    const send = document.getElementById('hub-chat-send');
    const toggle = document.getElementById('hub-chat-emoji-toggle');
    const panel = document.getElementById('hub-chat-emoji-panel');
    const feedback = document.getElementById('hub-chat-feedback');
    const updateSend = () => { send.disabled = !message.value.trim(); };
    const closePicker = (restoreFocus = false) => {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus();
    };
    const insertText = text => {
        const start = message.selectionStart;
        const end = message.selectionEnd;
        if (message.value.length - (end - start) + text.length > message.maxLength) {
            feedback.textContent = 'Your message is full. Remove some text before adding more.';
            feedback.hidden = false;
            return;
        }
        message.setRangeText(text, start, end, 'end');
        feedback.hidden = true;
        updateSend();
        message.focus();
    };
    const emojiSets = {
        faces: [['😀','Grinning happy'],['😃','Happy smile'],['😄','Smiling happy'],['😁','Beaming grin'],['😆','Laughing'],['😅','Sweat relieved'],['😂','Tears of joy laugh'],['🤣','Rolling laughing'],['😊','Smiling blush'],['🙂','Slight smile'],['😉','Wink'],['😍','Heart eyes love'],['🥰','Love hearts'],['😘','Kiss'],['😎','Cool sunglasses'],['🥳','Party celebration'],['🤔','Thinking'],['🤗','Hug'],['🤭','Giggle'],['🤫','Quiet hush'],['😴','Sleep'],['😢','Sad cry'],['😭','Crying'],['😮','Surprised'],['😬','Grimace'],['🙄','Eye roll'],['😡','Angry'],['🥺','Pleading'],['😇','Angel'],['🤩','Star struck'],['😋','Yummy'],['🙃','Upside down']],
        gestures: [['👍','Thumbs up like'],['👎','Thumbs down dislike'],['👋','Wave hello'],['👌','Okay'],['✌️','Peace'],['🤞','Fingers crossed'],['👏','Clap'],['🙌','Celebrate raised hands'],['🙏','Thank you please pray'],['🤝','Handshake agreed'],['💪','Strong flex'],['🫶','Heart hands love'],['👀','Eyes looking'],['🤙','Call me'],['👊','Fist bump'],['✋','Raised hand stop']],
        hearts: [['❤️','Red heart love'],['🧡','Orange heart'],['💛','Yellow heart'],['💚','Green heart'],['💙','Blue heart'],['💜','Purple heart'],['🤎','Brown heart'],['🖤','Black heart'],['🤍','White heart'],['💕','Two hearts'],['💖','Sparkling heart'],['💔','Broken heart'],['✨','Sparkles'],['⭐','Star'],['🎉','Party celebration'],['✅','Check done yes'],['❌','Cross no'],['💯','Hundred perfect'],['🔥','Fire'],['💬','Message chat']],
        objects: [['📦','Parcel package box'],['🛵','Scooter rider'],['🚚','Delivery truck'],['🚗','Car'],['🚲','Bicycle bike'],['🚀','Rocket'],['📍','Location pin'],['⏰','Time alarm clock'],['📞','Phone call'],['📷','Camera photo'],['🏠','Home house'],['🏪','Store shop'],['🎁','Gift'],['🌸','Flower'],['☀️','Sun'],['🌧️','Rain'],['☕','Coffee'],['🍔','Burger food'],['🎂','Cake birthday'],['🌈','Rainbow']]
    };
    const emojiEntries = Object.values(emojiSets).flat();
    const emojiSearch = document.getElementById('hub-emoji-search');
    const emojiGrid = document.getElementById('hub-emoji-grid');
    const categoryButtons = [...panel.querySelectorAll('[data-emoji-category]')];
    let category = 'faces';
    let recent = [];
    try {
        const saved = JSON.parse(localStorage.getItem('hub-chat-recent-emojis') || '[]');
        if (Array.isArray(saved)) recent = saved.filter(value => emojiEntries.some(([emoji]) => emoji === value)).slice(0, 24);
    } catch { /* The picker also works when browser storage is unavailable. */ }
    const renderEmojis = () => {
        const query = emojiSearch.value.trim().toLocaleLowerCase();
        const entries = query ? emojiEntries.filter(([emoji, label]) => label.toLowerCase().includes(query) || emoji.includes(query))
            : category === 'recent' ? recent.map(value => emojiEntries.find(([emoji]) => emoji === value)) : emojiSets[category];
        emojiGrid.replaceChildren();
        entries.forEach(([emoji, label]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.emoji = emoji;
            button.textContent = emoji;
            button.title = label;
            button.setAttribute('aria-label', label);
            emojiGrid.append(button);
        });
        document.getElementById('hub-emoji-section').textContent = query ? 'Search results' : categoryButtons.find(button => button.dataset.emojiCategory === category).title;
        const empty = document.getElementById('hub-emoji-empty');
        empty.hidden = entries.length > 0;
        empty.textContent = category === 'recent' && !query ? 'Emojis you use will appear here.' : 'No emojis found. Try another search.';
    };
    emojiSearch.addEventListener('input', renderEmojis);
    categoryButtons.forEach(button => button.addEventListener('click', () => {
        category = button.dataset.emojiCategory;
        emojiSearch.value = '';
        categoryButtons.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
        renderEmojis();
    }));
    toggle.addEventListener('click', () => {
        if (!panel.hidden) { closePicker(true); return; }
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        renderEmojis();
        emojiSearch.focus();
    });
    document.getElementById('hub-chat-emoji-close').addEventListener('click', () => closePicker(true));
    emojiGrid.addEventListener('click', event => {
        const button = event.target.closest('[data-emoji]');
        if (!button) return;
        insertText(button.dataset.emoji);
        recent = [button.dataset.emoji, ...recent.filter(value => value !== button.dataset.emoji)].slice(0, 24);
        try { localStorage.setItem('hub-chat-recent-emojis', JSON.stringify(recent)); } catch { /* Optional preference storage. */ }
        closePicker();
    });
    document.addEventListener('click', event => {
        if (!panel.hidden && !event.target.closest('.hub-chat-emoji-wrap')) closePicker();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) { event.preventDefault(); closePicker(true); }
    });
    document.querySelectorAll('[data-quick-reply]').forEach(button => button.addEventListener('click', () => {
        insertText((message.value && message.selectionStart > 0 && !/\s/.test(message.value[message.selectionStart - 1]) ? ' ' : '') + button.dataset.quickReply);
    }));
    message.addEventListener('input', updateSend);
    message.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            if (message.value.trim()) form.requestSubmit();
        }
    });
    let submitting = false;
    form.addEventListener('submit', event => {
        if (!message.value.trim() || submitting) { event.preventDefault(); return; }
        submitting = true;
        send.disabled = true;
        send.setAttribute('aria-busy', 'true');
        send.setAttribute('aria-label', 'Sending message');
    });
    window.addEventListener('pageshow', () => {
        submitting = false;
        send.removeAttribute('aria-busy');
        send.setAttribute('aria-label', 'Send message');
        updateSend();
    });
    updateSend();
})();
