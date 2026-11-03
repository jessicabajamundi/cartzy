// Cozy the teddy bear: floating shopping buddy for the storefront.
(() => {
    const root = document.getElementById('buddy');
    if (!root) return;

    const $ = (id) => document.getElementById(id);
    const panel = $('buddyPanel'), launcher = $('buddyLauncher'), log = $('buddyLog'), chips = $('buddyChips');
    const form = $('buddyForm'), input = $('buddyInput'), send = $('buddySend'), status = $('buddyStatus'), greeting = $('buddyGreeting');
    const discard = $('buddyDiscard'), discardLabel = $('buddyDiscardLabel'), restoreBtn = $('buddyRestore');
    const art = $('buddy-bear-art');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const name = root.dataset.userName;
    // Per account, so a chat about someone's orders doesn't reappear after they sign out.
    const storeKey = `cartzy-buddy-chat-${root.dataset.userId || 'guest'}`;
    const discardedKey = `cartzy-buddy-discarded-${root.dataset.userId || 'guest'}`;
    const posKey = `cartzy-buddy-pos-${root.dataset.userId || 'guest'}`;
    const peso = (amount) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: Number.isInteger(amount) ? 0 : 2 }).format(amount);
    const productMatch = location.pathname.match(/^\/products\/(\d+)/);
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

    const bears = [...root.querySelectorAll('[data-bear]')];
    bears.forEach((slot) => slot.appendChild(art.content.cloneNode(true)));

    // Check if Cozy was discarded in this session or restore custom position
    if (sessionStorage.getItem(discardedKey) === '1') {
        launcher.hidden = true;
        restoreBtn.hidden = false;
    } else {
        try {
            const savedPos = JSON.parse(sessionStorage.getItem(posKey) || 'null');
            if (savedPos && typeof savedPos.left === 'number' && typeof savedPos.top === 'number') {
                const maxX = Math.max(10, window.innerWidth - 100);
                const maxY = Math.max(10, window.innerHeight - 100);
                const x = Math.max(10, Math.min(maxX, savedPos.left));
                const y = Math.max(10, Math.min(maxY, savedPos.top));
                launcher.style.left = `${x}px`;
                launcher.style.top = `${y}px`;
                launcher.style.right = 'auto';
                launcher.style.bottom = 'auto';
            }
        } catch {}
    }

    let history = [];   // {role, content} sent to the server
    let transcript = []; // {role, text, products} shown on screen
    let busy = false;
    let moodTimer;

    const setMood = (mood, holdMs = 0) => {
        clearTimeout(moodTimer);
        bears.forEach((slot) => { slot.dataset.mood = mood; });
        if (holdMs) moodTimer = setTimeout(() => setMood('happy'), holdMs);
    };

    const playClass = (cls, ms) => {
        bears.forEach((slot) => slot.classList.add(cls));
        setTimeout(() => bears.forEach((slot) => slot.classList.remove(cls)), ms);
    };

    const hearts = (anchor, count = 5) => {
        if (reducedMotion) return;
        const box = anchor.getBoundingClientRect();
        for (let i = 0; i < count; i++) {
            const heart = document.createElement('span');
            heart.className = 'buddy-heart';
            heart.textContent = '♥';
            heart.style.left = `${box.left + box.width * Math.random()}px`;
            heart.style.top = `${box.top + box.height * .3}px`;
            heart.style.color = i % 2 ? '#A8A0B2' : '#D4A69A';
            heart.style.setProperty('--drift', `${(Math.random() - .5) * 50}px`);
            heart.style.animationDelay = `${i * 90}ms`;
            document.body.appendChild(heart);
            setTimeout(() => heart.remove(), 1700);
        }
    };

    // Cozy's eyes follow the pointer, and wander on their own when nothing is moving (always, on phones).
    const lookAt = (x, y) => {
        root.querySelectorAll('.buddy-pupil').forEach((pupil) => {
            pupil.style.transform = `translate(${x.toFixed(1)}px, ${y.toFixed(1)}px)`;
        });
    };
    if (!reducedMotion) {
        let frame = 0;
        let lastPointer = 0;
        window.addEventListener('pointermove', (event) => {
            lastPointer = Date.now();
            if (frame) return;
            frame = requestAnimationFrame(() => {
                frame = 0;
                const box = bears.find((slot) => slot.getBoundingClientRect().width && getComputedStyle(slot).visibility !== 'hidden')?.getBoundingClientRect();
                if (!box) return;
                const angle = Math.atan2(event.clientY - box.top - box.height * .4, event.clientX - box.left - box.width / 2);
                lookAt(Math.cos(angle) * 4, Math.sin(angle) * 4);
            });
        }, { passive: true });
        setInterval(() => {
            if (Date.now() - lastPointer < 3000) return;
            const angle = Math.random() * Math.PI * 2;
            const reach = Math.random() < .3 ? 0 : 4;
            lookAt(Math.cos(angle) * reach, Math.sin(angle) * reach);
        }, 1800);
    }

    const save = () => {
        try {
            sessionStorage.setItem(storeKey, JSON.stringify({ history, transcript }));
        } catch { /* Private windows can block storage; the chat still works. */ }
    };

    const scrollDown = () => { log.scrollTop = log.scrollHeight; };

    const addCards = (container, products) => {
        const row = document.createElement('div');
        row.className = 'buddy-cards';
        products.forEach((product) => {
            const card = document.createElement('a');
            card.className = 'buddy-card';
            card.href = product.url;

            const image = document.createElement('img');
            image.src = product.image || '/images/favicon.png';
            image.alt = '';
            image.loading = 'lazy';

            const body = document.createElement('span');
            body.className = 'buddy-card-body';
            const title = document.createElement('span');
            title.className = 'buddy-card-name';
            title.textContent = product.name;
            const price = document.createElement('span');
            price.className = 'buddy-card-price';
            price.textContent = product.price != null ? peso(product.price) : '';
            const meta = document.createElement('span');
            meta.className = 'buddy-card-meta';
            meta.textContent = product.in_stock === false ? 'Out of stock' : (product.rating ? `★ ${product.rating} · ${product.sold} sold` : `${product.sold} sold`);

            body.append(title, price, meta);
            card.append(image, body);
            row.appendChild(card);
        });
        container.classList.add('has-cards');
        container.appendChild(row);
    };

    const addBubble = (role, text, products = []) => {
        const message = document.createElement('div');
        message.className = `buddy-msg ${role === 'user' ? 'is-me' : 'is-bot'}`;
        const bubble = document.createElement('p');
        bubble.textContent = text;
        message.appendChild(bubble);
        if (products.length) addCards(message, products);
        log.appendChild(message);
        scrollDown();
        return bubble;
    };

    // Reveals Cozy's reply a few words at a time so it feels spoken.
    const speak = (text, products) => new Promise((resolve) => {
        const finish = (bubble) => {
            if (products.length) addCards(bubble.parentElement, products);
            scrollDown();
            resolve();
        };
        const words = text.split(/(\s+)/);
        if (reducedMotion || words.length < 5) {
            finish(addBubble('assistant', text));
            return;
        }
        const bubble = addBubble('assistant', '');
        let shown = 0;
        const timer = setInterval(() => {
            shown = Math.min(words.length, shown + 4);
            bubble.textContent = words.slice(0, shown).join('');
            scrollDown();
            if (shown >= words.length) {
                clearInterval(timer);
                finish(bubble);
            }
        }, 45);
    });

    const setChips = (options) => {
        chips.replaceChildren(...options.map((text) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'buddy-chip';
            chip.textContent = text;
            chip.addEventListener('click', () => submit(text));
            return chip;
        }));
    };

    const welcome = () => {
        const hello = name ? `Hi ${name}! I'm Cozy, your cuddly shopping buddy.` : "Hi hi! I'm Cozy, your cuddly shopping buddy.";
        const text = `${hello} Want to chat, hunt for something cute, or check on an order?`;
        addBubble('assistant', text);
        transcript = [{ role: 'assistant', text, products: [] }];
        history = [];
        setChips(productMatch
            ? ['Tell me about this item', 'Is this a good gift?', 'Show me similar items']
            : ['Help me find a gift', 'What’s new today?', 'Where’s my order?']);
    };

    const restore = () => {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storeKey) || 'null');
            if (saved?.transcript?.length) {
                history = saved.history;
                transcript = saved.transcript;
                transcript.forEach((m) => addBubble(m.role, m.text, m.products));
                return true;
            }
        } catch { /* Fall through to a fresh welcome. */ }
        return false;
    };

    const openPanel = () => {
        root.classList.add('is-open');
        panel.hidden = false;
        launcher.setAttribute('aria-expanded', 'true');
        greeting.hidden = true;
        if (!log.children.length && !restore()) welcome();
        playClass('is-waving', 2200);
        scrollDown();
        if (matchMedia('(min-width: 481px)').matches) input.focus();
    };

    const closePanel = () => {
        root.classList.remove('is-open');
        panel.hidden = true;
        launcher.setAttribute('aria-expanded', 'false');
        launcher.focus();
    };

    async function submit(text) {
        const message = text.trim();
        if (!message || busy) return;

        busy = true;
        send.disabled = true;
        input.value = '';
        input.style.height = '';
        setChips([]);
        addBubble('user', message);
        transcript.push({ role: 'user', text: message, products: [] });
        history.push({ role: 'user', content: message });

        setMood('thinking');
        status.textContent = 'Cozy is thinking...';
        const typing = document.createElement('div');
        typing.className = 'buddy-msg is-bot';
        typing.innerHTML = '<span class="buddy-typing" aria-label="Cozy is typing"><i></i><i></i><i></i></span>';
        log.appendChild(typing);
        scrollDown();

        let reply;
        try {
            const response = await fetch(root.dataset.chatUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ messages: history, product_id: productMatch ? Number(productMatch[1]) : null }),
            });
            if (response.status === 429) {
                reply = { reply: 'Whew, my paws need a little rest! Let\'s chat again in a minute.', mood: 'sad', suggestions: [], products: [], failed: true };
            } else if (!response.ok) {
                throw new Error(`Chat failed: ${response.status}`);
            } else {
                reply = await response.json();
            }
        } catch {
            reply = { reply: 'Oh no, I couldn\'t reach the shop just now. Can you try that again?', mood: 'sad', suggestions: [message], products: [], failed: true };
        }

        typing.remove();
        // A failed send stays out of the history, so retrying doesn't send it twice.
        if (reply.failed) {
            history.pop();
        } else {
            history.push({ role: 'assistant', content: reply.reply });
        }
        transcript.push({ role: 'assistant', text: reply.reply, products: reply.products ?? [] });
        save();

        setMood(reply.mood, reply.mood === 'sad' ? 4000 : 6000);
        status.textContent = 'Your Cartzy shopping buddy';
        if (reply.mood === 'love' || reply.mood === 'excited') {
            hearts(panel.querySelector('.buddy-head-bear'));
        }
        await speak(reply.reply, reply.products ?? []);
        setChips(reply.suggestions ?? []);
        scrollDown(); // The chips just took space from the log.

        busy = false;
        send.disabled = false;
        if (matchMedia('(min-width: 481px)').matches) input.focus();
    }

    let isPointerDown = false;
    let isDragging = false;
    let hasMoved = false;
    let startX = 0, startY = 0;
    let initialLeft = 0, initialTop = 0;
    let launcherWidth = 92, launcherHeight = 92;

    const onPointerDown = (e) => {
        if (e.button !== 0 && e.pointerType === 'mouse') return;
        isPointerDown = true;
        hasMoved = false;
        isDragging = false;
        startX = e.clientX;
        startY = e.clientY;

        const rect = launcher.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;
        launcherWidth = rect.width || 92;
        launcherHeight = rect.height || 92;

        try { launcher.setPointerCapture(e.pointerId); } catch {}
    };

    const onPointerMove = (e) => {
        if (!isPointerDown) return;
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;

        if (!hasMoved && Math.hypot(deltaX, deltaY) > 6) {
            hasMoved = true;
            isDragging = true;
            launcher.classList.add('is-dragging');
            if (greeting) greeting.hidden = true;
            if (discard) {
                discard.hidden = false;
                void discard.offsetWidth;
                discard.classList.add('is-visible');
            }
        }

        if (isDragging) {
            let newLeft = initialLeft + deltaX;
            let newTop = initialTop + deltaY;

            const maxLeft = Math.max(10, window.innerWidth - launcherWidth - 10);
            const maxTop = Math.max(10, window.innerHeight - launcherHeight - 10);
            newLeft = Math.max(10, Math.min(maxLeft, newLeft));
            newTop = Math.max(10, Math.min(maxTop, newTop));

            launcher.style.left = `${newLeft}px`;
            launcher.style.top = `${newTop}px`;
            launcher.style.right = 'auto';
            launcher.style.bottom = 'auto';

            if (discard) {
                const discardRect = discard.getBoundingClientRect();
                const discardCenterX = discardRect.left + discardRect.width / 2;
                const discardCenterY = discardRect.top + discardRect.height / 2;
                const bearCenterX = newLeft + launcherWidth / 2;
                const bearCenterY = newTop + launcherHeight / 2;
                const dist = Math.hypot(bearCenterX - discardCenterX, bearCenterY - discardCenterY);

                const isNear = dist < 75;
                if (isNear) {
                    discard.classList.add('is-active');
                    if (discardLabel) discardLabel.textContent = 'Release to discard';
                    setMood('sad');
                    launcher.style.transform = 'scale(0.75)';
                    launcher.style.opacity = '0.75';
                } else {
                    discard.classList.remove('is-active');
                    if (discardLabel) discardLabel.textContent = 'Drag here to discard';
                    setMood('happy');
                    launcher.style.transform = '';
                    launcher.style.opacity = '';
                }
            }
        }
    };

    const onPointerUp = (e) => {
        if (!isPointerDown) return;
        isPointerDown = false;
        try { launcher.releasePointerCapture(e.pointerId); } catch {}

        if (discard) {
            const wasActive = discard.classList.contains('is-active');
            discard.classList.remove('is-visible');
            discard.classList.remove('is-active');
            setTimeout(() => {
                if (!isDragging) discard.hidden = true;
            }, 240);

            if (wasActive) {
                launcher.classList.add('is-discarding');
                sessionStorage.setItem(discardedKey, '1');
                setTimeout(() => {
                    launcher.hidden = true;
                    launcher.classList.remove('is-discarding');
                    launcher.classList.remove('is-dragging');
                    restoreBtn.hidden = false;
                }, 240);
                return;
            }
        }

        if (isDragging) {
            launcher.classList.remove('is-dragging');
            launcher.style.transform = '';
            launcher.style.opacity = '';
            setMood('happy');
            const rect = launcher.getBoundingClientRect();
            sessionStorage.setItem(posKey, JSON.stringify({ left: rect.left, top: rect.top }));
        } else {
            openPanel();
        }
    };

    launcher.addEventListener('pointerdown', onPointerDown);
    launcher.addEventListener('pointermove', onPointerMove);
    launcher.addEventListener('pointerup', onPointerUp);
    launcher.addEventListener('pointercancel', onPointerUp);

    // Bring a discarded Cozy back to her usual corner.
    const bringBack = () => {
        sessionStorage.removeItem(discardedKey);
        sessionStorage.removeItem(posKey);
        ['left', 'top', 'right', 'bottom', 'transform', 'opacity'].forEach((prop) => { launcher.style[prop] = ''; });
        restoreBtn.hidden = true;
        launcher.hidden = false;
    };
    restoreBtn.addEventListener('click', () => {
        bringBack();
        launcher.focus();
        setMood('love', 1800);
        playClass('is-waving', 2200);
        hearts(launcher, 5);
    });

    // The Cozy icon in the header opens the chat, even after Cozy was discarded.
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-open-cozy]')) return;
        event.preventDefault();
        if (launcher.hidden) bringBack();
        openPanel();
    });

    $('buddyClose').addEventListener('click', closePanel);
    $('buddyReset').addEventListener('click', () => {
        if (busy) return;
        log.replaceChildren();
        welcome();
        save();
        setMood('wink', 2000);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) closePanel();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submit(input.value);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            submit(input.value);
        }
    });
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${input.scrollHeight + 3}px`;
    });

    // Patting the head bear makes Cozy giggle.
    panel.querySelector('.buddy-head-bear').addEventListener('click', (event) => {
        playClass('is-giggling', 500);
        setMood('love', 1800);
        hearts(event.currentTarget, 4);
    });

    // A quick hello per visit, once Cozy has popped in.
    try {
        if (!sessionStorage.getItem('cartzy-buddy-greeted')) {
            sessionStorage.setItem('cartzy-buddy-greeted', '1');
            setTimeout(() => {
                if (!panel.hidden) return;
                greeting.textContent = name ? `Hi ${name}! Need a shopping buddy?` : 'Psst! Need a shopping buddy?';
                greeting.hidden = false;
                playClass('is-waving', 2200);
                setTimeout(() => { greeting.hidden = true; }, 6000);
            }, 1800);
        }
    } catch { /* No greeting without storage. */ }
})();
