// Color wheel for the Add product option builder.
// Hue = angle, intensity (saturation) = distance from center, brightness = slider.
// The chosen color is kept in #option-color-hex (as RRGGBB), which seller-inventory.js reads.
(() => {
    const root = document.querySelector('[data-color-wheel]');
    if (!root) return;

    const wheel = root.querySelector('.pe-wheel');
    const canvas = root.querySelector('.pe-wheel-canvas');
    const shade = root.querySelector('.pe-wheel-shade');
    const knob = root.querySelector('.pe-wheel-knob');
    const brightness = document.getElementById('option-color-brightness');
    const hexInput = document.getElementById('option-color-hex');
    const nameInput = document.getElementById('option-colors');
    const previewSwatch = root.querySelector('.pe-wheel-preview-swatch');
    const previewName = root.querySelector('.pe-wheel-preview-name');

    const NAMES = {
        Black: '#1F1F1F', Charcoal: '#36454F', Gray: '#9E9E9E', Silver: '#C0C0C0', White: '#F5F5F5', Ivory: '#FFFFF0',
        Cream: '#F3E5C0', Beige: '#E3D5B8', Khaki: '#C3B091', Tan: '#D2B48C', Brown: '#7A5536', Chocolate: '#4B2E1E',
        Maroon: '#800000', Burgundy: '#6D0E26', Red: '#C0392B', Coral: '#FF7F50', Orange: '#E67E22', Peach: '#FFCBA4',
        Mustard: '#D4A017', Gold: '#C9A227', Yellow: '#E6C13B', Lime: '#A4C639', Olive: '#708238', Green: '#4B8B52',
        Emerald: '#2E8B57', Mint: '#98D8B0', Teal: '#1F7A7A', Turquoise: '#40C4C4', 'Sky Blue': '#87CEEB', Blue: '#3B6DB3',
        Royal: '#2747A5', Navy: '#1F2F55', Lavender: '#C8B6E2', Lilac: '#B79FCF', Purple: '#7D55A8', Plum: '#5E2750',
        Magenta: '#C2185B', Pink: '#F4B6C2', 'Hot Pink': '#E8559A', Rose: '#D98C9C',
        Crimson: '#B91C1C', Scarlet: '#E3242B', Amber: '#F0A202', 'Leaf Green': '#5FA82E', Forest: '#2D5A27',
        Cyan: '#00B7D4', Cobalt: '#0047AB', Violet: '#8F3FD9',
    };

    // --- color math ---
    const clamp = (n, min, max) => Math.min(max, Math.max(min, n));
    const hsvToRgb = (h, s, v) => {
        const f = n => { const k = (n + h / 60) % 6; return v - v * s * Math.max(0, Math.min(k, 4 - k, 1)); };
        return [f(5), f(3), f(1)].map(x => Math.round(x * 255));
    };
    const rgbToHsv = (r, g, b) => {
        [r, g, b] = [r / 255, g / 255, b / 255];
        const max = Math.max(r, g, b), d = max - Math.min(r, g, b);
        let h = 0;
        if (d) h = max === r ? ((g - b) / d) % 6 : max === g ? (b - r) / d + 2 : (r - g) / d + 4;
        return [(h * 60 + 360) % 360, max ? d / max : 0, max];
    };
    const toHex = rgb => '#' + rgb.map(x => x.toString(16).padStart(2, '0')).join('').toUpperCase();
    const fromHex = hex => {
        const m = /^#?([0-9a-f]{6})$/i.exec(hex.trim());
        return m ? [0, 2, 4].map(i => parseInt(m[1].slice(i, i + 2), 16)) : null;
    };
    const nearestName = rgb => {
        let best = '', bestDistance = Infinity;
        for (const [name, hex] of Object.entries(NAMES)) {
            const [r, g, b] = fromHex(hex);
            const mean = (rgb[0] + r) / 2; // "redmean" weighting: closer to how eyes compare colors
            const distance = (2 + mean / 256) * (rgb[0] - r) ** 2 + 4 * (rgb[1] - g) ** 2 + (2 + (255 - mean) / 256) * (rgb[2] - b) ** 2;
            if (distance < bestDistance) { bestDistance = distance; best = name; }
        }
        return best;
    };

    // --- draw the hue/intensity disk once (brightness is a dark overlay on top) ---
    const ctx = canvas.getContext('2d');
    const size = canvas.width, radius = size / 2;
    const image = ctx.createImageData(size, size);
    for (let y = 0; y < size; y++) {
        for (let x = 0; x < size; x++) {
            const dx = x - radius + .5, dy = y - radius + .5, dist = Math.hypot(dx, dy);
            if (dist > radius) continue;
            const [r, g, b] = hsvToRgb((Math.atan2(dy, dx) * 180 / Math.PI + 360) % 360, dist / radius, 1);
            const i = (y * size + x) * 4;
            image.data.set([r, g, b, clamp((radius - dist) * 255, 0, 255)], i); // soft anti-aliased edge
        }
    }
    ctx.putImageData(image, 0, 0);

    // --- state ---
    let [hue, sat, val] = rgbToHsv(...fromHex('#3B6DB3')); // start on a medium blue so first drags give clear colors
    let autoName = ''; // last name we filled in, so we never overwrite a name the seller typed

    const render = ({ fromHexInput = false, userChange = true } = {}) => {
        const rgb = hsvToRgb(hue, sat, val);
        const hex = toHex(rgb);
        const angle = hue * Math.PI / 180;
        knob.style.left = 50 + Math.cos(angle) * sat * 50 + '%';
        knob.style.top = 50 + Math.sin(angle) * sat * 50 + '%';
        knob.style.background = hex;
        shade.style.opacity = String(1 - val);
        brightness.value = String(Math.round(val * 100));
        brightness.style.setProperty('--wheel-hue', toHex(hsvToRgb(hue, sat, 1)));
        if (!fromHexInput) hexInput.value = hex.slice(1); // the "#" is shown as a prefix
        hexInput.setCustomValidity('');
        previewSwatch.style.background = hex;
        const name = nearestName(rgb);
        previewName.textContent = `${name} · ${hex}`;
        knob.setAttribute('aria-valuenow', String(Math.round(hue)));
        knob.setAttribute('aria-valuetext', `${name}, ${hex}`);
        if (userChange && (!nameInput.value.trim() || nameInput.value === autoName)) {
            nameInput.value = autoName = name;
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    // --- dragging on the wheel ---
    const pickAt = event => {
        const rect = wheel.getBoundingClientRect();
        const dx = event.clientX - rect.left - rect.width / 2, dy = event.clientY - rect.top - rect.height / 2;
        hue = (Math.atan2(dy, dx) * 180 / Math.PI + 360) % 360;
        sat = clamp(Math.hypot(dx, dy) / (rect.width / 2), 0, 1);
        if (val < .3) val = 1; // picking a hue on a near-black wheel would be invisible
        render();
    };
    wheel.addEventListener('pointerdown', event => {
        event.preventDefault();
        wheel.setPointerCapture(event.pointerId);
        wheel.classList.add('is-dragging');
        knob.focus({ preventScroll: true });
        pickAt(event);
    });
    wheel.addEventListener('pointermove', event => { if (wheel.hasPointerCapture(event.pointerId)) pickAt(event); });
    const stopDrag = event => { if (wheel.hasPointerCapture(event.pointerId)) wheel.releasePointerCapture(event.pointerId); wheel.classList.remove('is-dragging'); };
    wheel.addEventListener('pointerup', stopDrag);
    wheel.addEventListener('pointercancel', stopDrag);

    // --- keyboard on the knob ---
    knob.addEventListener('keydown', event => {
        const step = event.shiftKey ? 15 : 5;
        const moves = {
            ArrowLeft: () => { hue = (hue - step + 360) % 360; },
            ArrowRight: () => { hue = (hue + step) % 360; },
            ArrowUp: () => { sat = clamp(sat + step / 100, 0, 1); },
            ArrowDown: () => { sat = clamp(sat - step / 100, 0, 1); },
        };
        if (!moves[event.key]) return;
        event.preventDefault();
        moves[event.key]();
        render();
    });

    brightness.addEventListener('input', () => { val = Number(brightness.value) / 100; render(); });

    // --- typing a color code ---
    hexInput.addEventListener('input', () => {
        const rgb = fromHex(hexInput.value);
        if (!rgb) return;
        [hue, sat, val] = rgbToHsv(...rgb);
        render({ fromHexInput: true });
    });
    hexInput.addEventListener('change', () => {
        const rgb = fromHex(hexInput.value);
        if (rgb) { hexInput.value = toHex(rgb).slice(1); return; }
        hexInput.setCustomValidity('Enter a 6-digit color code, e.g. 1E293B.');
        hexInput.reportValidity();
    });
    hexInput.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); hexInput.dispatchEvent(new Event('change')); } });

    nameInput.addEventListener('input', () => { if (nameInput.value !== autoName) autoName = ''; });

    render({ userChange: false });
})();
