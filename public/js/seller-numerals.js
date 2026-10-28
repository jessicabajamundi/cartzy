// Draws every ₱ in the seller workspace in Bodoni (see seller-numerals.css): each ₱ text character is
// wrapped in <span class="seller-peso"><span>₱</span></span>. Text added later (popups, filters) is
// wrapped too. Form fields, dropdown options, scripts and SVG can't hold markup and are left as they are.
(() => {
    const PESO = '₱';
    const SKIP = 'script, style, textarea, option, select, title, svg, code, pre, [contenteditable], .seller-peso';

    const wrapText = node => {
        const parts = node.nodeValue.split(PESO);
        if (parts.length < 2 || node.parentElement?.closest(SKIP)) return;
        const fragment = document.createDocumentFragment();
        parts.forEach((part, index) => {
            if (index > 0) {
                const peso = document.createElement('span');
                peso.className = 'seller-peso';
                const glyph = document.createElement('span');
                glyph.textContent = PESO;
                peso.append(glyph);
                fragment.append(peso);
            }
            if (part) fragment.append(part);
        });
        const parent = node.parentElement;
        node.replaceWith(fragment);
        // Bold text gets slightly heavier peso bars (see .seller-peso.is-bold).
        if (parent && parseInt(getComputedStyle(parent).fontWeight, 10) >= 600) {
            parent.querySelectorAll(':scope > .seller-peso').forEach(peso => peso.classList.add('is-bold'));
        }
    };

    const scan = root => {
        if (root.nodeType === Node.TEXT_NODE) return wrapText(root);
        if (root.nodeType !== Node.ELEMENT_NODE || root.matches(SKIP)) return;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: node => node.nodeValue.includes(PESO) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT,
        });
        const found = [];
        while (walker.nextNode()) found.push(walker.currentNode);
        found.forEach(wrapText);
    };

    scan(document.body);
    new MutationObserver(mutations => mutations.forEach(mutation => {
        if (mutation.type === 'characterData') return wrapText(mutation.target);
        mutation.addedNodes.forEach(scan);
    })).observe(document.body, { childList: true, subtree: true, characterData: true });
})();
