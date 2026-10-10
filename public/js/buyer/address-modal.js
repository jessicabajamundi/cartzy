/**
 * Address Modal & PSGC (Philippine Standard Geographic Code) Cascading API Handler
 * Handles modal dialog lifecycle and cascading Region -> Province -> City -> Barangay dropdowns
 */
(() => {
    // -------------------------------------------------------------
    // PSGC Cascading Dropdown Logic
    // -------------------------------------------------------------
    const PSGC = 'https://psgc.gitlab.io/api';
    const psgcCache = new Map();

    const regionMap = {
        'NCR':   '130000000',
        'CAR':   '140000000',
        'I':     '010000000',
        'II':    '020000000',
        'III':   '030000000',
        'IV-A':  '040000000',
        'IV-B':  '170000000',
        'V':     '050000000',
        'VI':    '060000000',
        'VII':   '070000000',
        'VIII':  '080000000',
        'IX':    '090000000',
        'X':     '100000000',
        'XI':    '110000000',
        'XII':   '120000000',
        'XIII':  '160000000',
        'BARMM': '150000000'
    };

    const provinceToRegion = {
        'Metro Manila': 'NCR',
        'Abra': 'CAR', 'Apayao': 'CAR', 'Benguet': 'CAR', 'Ifugao': 'CAR', 'Kalinga': 'CAR', 'Mountain Province': 'CAR',
        'Ilocos Norte': 'I', 'Ilocos Sur': 'I', 'La Union': 'I', 'Pangasinan': 'I',
        'Batanes': 'II', 'Cagayan': 'II', 'Isabela': 'II', 'Nueva Vizcaya': 'II', 'Quirino': 'II',
        'Aurora': 'III', 'Bataan': 'III', 'Bulacan': 'III', 'Nueva Ecija': 'III', 'Pampanga': 'III', 'Tarlac': 'III', 'Zambales': 'III',
        'Batangas': 'IV-A', 'Cavite': 'IV-A', 'Laguna': 'IV-A', 'Quezon': 'IV-A', 'Rizal': 'IV-A',
        'Marinduque': 'IV-B', 'Occidental Mindoro': 'IV-B', 'Oriental Mindoro': 'IV-B', 'Palawan': 'IV-B', 'Romblon': 'IV-B',
        'Albay': 'V', 'Camarines Norte': 'V', 'Camarines Sur': 'V', 'Catanduanes': 'V', 'Masbate': 'V', 'Sorsogon': 'V',
        'Aklan': 'VI', 'Antique': 'VI', 'Capiz': 'VI', 'Guimaras': 'VI', 'Iloilo': 'VI', 'Negros Occidental': 'VI',
        'Bohol': 'VII', 'Cebu': 'VII', 'Negros Oriental': 'VII', 'Siquijor': 'VII',
        'Biliran': 'VIII', 'Eastern Samar': 'VIII', 'Leyte': 'VIII', 'Northern Samar': 'VIII', 'Samar': 'VIII', 'Western Samar': 'VIII', 'Southern Leyte': 'VIII',
        'Zamboanga del Norte': 'IX', 'Zamboanga del Sur': 'IX', 'Zamboanga Sibugay': 'IX',
        'Bukidnon': 'X', 'Camiguin': 'X', 'Lanao del Norte': 'X', 'Misamis Occidental': 'X', 'Misamis Oriental': 'X',
        'Davao de Oro': 'XI', 'Compostela Valley': 'XI', 'Davao del Norte': 'XI', 'Davao del Sur': 'XI', 'Davao Occidental': 'XI', 'Davao Oriental': 'XI',
        'Cotabato': 'XII', 'North Cotabato': 'XII', 'Sarangani': 'XII', 'South Cotabato': 'XII', 'Sultan Kudarat': 'XII',
        'Agusan del Norte': 'XIII', 'Agusan del Sur': 'XIII', 'Dinagat Islands': 'XIII', 'Surigao del Norte': 'XIII', 'Surigao del Sur': 'XIII',
        'Basilan': 'BARMM', 'Lanao del Sur': 'BARMM', 'Maguindanao': 'BARMM', 'Maguindanao del Norte': 'BARMM', 'Maguindanao del Sur': 'BARMM', 'Sulu': 'BARMM', 'Tawi-Tawi': 'BARMM'
    };

    async function fetchPsgc(url) {
        if (psgcCache.has(url)) return psgcCache.get(url);
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            psgcCache.set(url, data);
            return data;
        } catch (err) {
            console.error('PSGC API request failed:', url, err);
            return null;
        }
    }

    function populateSelect(select, items, placeholder, selectedValue = '') {
        if (!select) return;
        select.innerHTML = '';
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = placeholder;
        select.appendChild(defaultOpt);

        if (!items || !items.length) {
            select.disabled = false;
            return;
        }

        const sorted = [...items].sort((a, b) => (a.name || '').localeCompare(b.name || ''));
        let foundSelected = false;

        sorted.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.name;
            opt.textContent = item.name;
            opt.dataset.code = item.code;
            if (selectedValue && item.name.toLowerCase() === selectedValue.toLowerCase()) {
                opt.selected = true;
                foundSelected = true;
            }
            select.appendChild(opt);
        });

        // Ensure previously saved value is preserved even if slight name variation exists in API
        if (selectedValue && !foundSelected) {
            const fallbackOpt = document.createElement('option');
            fallbackOpt.value = selectedValue;
            fallbackOpt.textContent = selectedValue;
            fallbackOpt.selected = true;
            select.appendChild(fallbackOpt);
        }

        select.disabled = false;
    }

    function setLoading(select, placeholder = 'Loading...') {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    function initPsgcForm(form) {
        if (!form || form.dataset.psgcBound === 'true') return;
        form.dataset.psgcBound = 'true';

        const regionSel = form.querySelector('.psgc-region');
        const provSel   = form.querySelector('.psgc-province');
        const citySel   = form.querySelector('.psgc-city');
        const brgySel   = form.querySelector('.psgc-barangay');

        if (!regionSel || !provSel || !citySel || !brgySel) return;

        let initialRegion = regionSel.value || regionSel.dataset.initial || '';
        const initialProv = provSel.dataset.initial || provSel.value || '';
        const initialCity = citySel.dataset.initial || citySel.value || '';
        const initialBrgy = brgySel.dataset.initial || brgySel.value || '';

        if (!initialRegion && initialProv) {
            initialRegion = provinceToRegion[initialProv] || (initialProv.toLowerCase().includes('manila') ? 'NCR' : '');
            if (initialRegion) regionSel.value = initialRegion;
        }

        async function onRegionChange(targetProv = '', targetCity = '', targetBrgy = '') {
            const regionCode = regionSel.value;
            if (!regionCode) {
                populateSelect(provSel, [], 'Select Province');
                populateSelect(citySel, [], 'Select City / Municipality');
                populateSelect(brgySel, [], 'Select Barangay');
                provSel.disabled = true;
                citySel.disabled = true;
                brgySel.disabled = true;
                return;
            }

            if (regionCode === 'NCR') {
                provSel.innerHTML = '<option value="Metro Manila" selected>Metro Manila</option>';
                provSel.disabled = false;

                setLoading(citySel, 'Loading cities...');
                populateSelect(brgySel, [], 'Select Barangay');
                brgySel.disabled = true;

                const cities = await fetchPsgc(`${PSGC}/regions/130000000/cities-municipalities.json`);
                populateSelect(citySel, cities || [], 'Select City / Municipality', targetCity);

                if (targetCity && citySel.value) {
                    await onCityChange(targetBrgy);
                }
                return;
            }

            const psgcCode = regionMap[regionCode];
            if (!psgcCode) return;

            setLoading(provSel, 'Loading provinces...');
            populateSelect(citySel, [], 'Select City / Municipality');
            populateSelect(brgySel, [], 'Select Barangay');
            citySel.disabled = true;
            brgySel.disabled = true;

            const provinces = await fetchPsgc(`${PSGC}/regions/${psgcCode}/provinces.json`);
            populateSelect(provSel, provinces || [], 'Select Province', targetProv);

            if (targetProv && provSel.value) {
                await onProvinceChange(targetCity, targetBrgy);
            }
        }

        async function onProvinceChange(targetCity = '', targetBrgy = '') {
            const provName = provSel.value;
            const regionCode = regionSel.value;
            if (!provName || regionCode === 'NCR') return;

            setLoading(citySel, 'Loading cities...');
            populateSelect(brgySel, [], 'Select Barangay');
            brgySel.disabled = true;

            const psgcCode = regionMap[regionCode];
            const provinces = await fetchPsgc(`${PSGC}/regions/${psgcCode}/provinces.json`);
            const provObj = (provinces || []).find(p => p.name.toLowerCase() === provName.toLowerCase());

            if (!provObj) {
                populateSelect(citySel, [], 'Select City / Municipality');
                return;
            }

            const cities = await fetchPsgc(`${PSGC}/provinces/${provObj.code}/cities-municipalities.json`);
            populateSelect(citySel, cities || [], 'Select City / Municipality', targetCity);

            if (targetCity && citySel.value) {
                await onCityChange(targetBrgy);
            }
        }

        async function onCityChange(targetBrgy = '') {
            const cityName = citySel.value;
            const regionCode = regionSel.value;
            if (!cityName) {
                populateSelect(brgySel, [], 'Select Barangay');
                brgySel.disabled = true;
                return;
            }

            setLoading(brgySel, 'Loading barangays...');

            let cityObj = null;
            if (regionCode === 'NCR') {
                const cities = await fetchPsgc(`${PSGC}/regions/130000000/cities-municipalities.json`);
                cityObj = (cities || []).find(c => c.name.toLowerCase() === cityName.toLowerCase());
            } else {
                const psgcCode = regionMap[regionCode];
                const provName = provSel.value;
                const provinces = await fetchPsgc(`${PSGC}/regions/${psgcCode}/provinces.json`);
                const provObj = (provinces || []).find(p => p.name.toLowerCase() === provName.toLowerCase());
                if (provObj) {
                    const cities = await fetchPsgc(`${PSGC}/provinces/${provObj.code}/cities-municipalities.json`);
                    cityObj = (cities || []).find(c => c.name.toLowerCase() === cityName.toLowerCase());
                }
            }

            if (!cityObj) {
                populateSelect(brgySel, [], 'Select Barangay');
                return;
            }

            const barangays = await fetchPsgc(`${PSGC}/cities-municipalities/${cityObj.code}/barangays.json`);
            populateSelect(brgySel, barangays || [], 'Select Barangay', targetBrgy);
        }

        regionSel.addEventListener('change', () => onRegionChange());
        provSel.addEventListener('change', () => onProvinceChange());
        citySel.addEventListener('change', () => onCityChange());

        if (initialRegion) {
            onRegionChange(initialProv, initialCity, initialBrgy);
        } else {
            provSel.disabled = true;
            citySel.disabled = true;
            brgySel.disabled = true;
        }
    }

    // -------------------------------------------------------------
    // Modal Dialog Controls
    // -------------------------------------------------------------
    const modal = document.getElementById('newAddressModal');
    let opener;
    let previousOverflow;

    const open = (trigger) => {
        if (!modal || modal.open) return;
        opener = trigger || document.querySelector('[data-open-address-modal]');
        previousOverflow = document.body.style.overflow;
        modal.showModal();
        document.body.style.overflow = 'hidden';

        const form = modal.querySelector('.psgc-address-form');
        if (form) initPsgcForm(form);

        (modal.querySelector('[role="alert"]') || modal.querySelector('select'))?.focus();
    };

    if (modal) {
        document.querySelectorAll('[data-open-address-modal]').forEach(button => {
            button.addEventListener('click', () => open(button));
        });
        modal.querySelectorAll('[data-close-address-modal]').forEach(button => {
            button.addEventListener('click', () => modal.close());
        });
        modal.addEventListener('close', () => {
            document.body.style.overflow = previousOverflow;
            opener?.focus({ preventScroll: true });
        });

        const outside = event => {
            const rect = modal.getBoundingClientRect();
            return event.clientX < rect.left || event.clientX > rect.right
                || event.clientY < rect.top || event.clientY > rect.bottom;
        };
        let startedOutside = false;
        modal.addEventListener('pointerdown', event => {
            startedOutside = event.target === modal && outside(event);
        });
        modal.addEventListener('click', event => {
            if (startedOutside && event.target === modal && outside(event)) modal.close();
            startedOutside = false;
        });

        if (modal.dataset.autoOpen === 'true') open();
    }

    // Initialize all forms present on the page (e.g. details open or modal)
    document.querySelectorAll('.psgc-address-form').forEach(form => {
        initPsgcForm(form);
    });

    // Listen for dynamically opened edit forms
    document.querySelectorAll('details.settings-edit').forEach(details => {
        details.addEventListener('toggle', () => {
            if (details.open) {
                const form = details.querySelector('.psgc-address-form');
                if (form) initPsgcForm(form);
            }
        });
    });

    // Enforce numbers-only on Contact number & Postal code inputs
    const isNumericTarget = el => el && (el.matches('input[name="phone"]') || el.matches('input[name="postal_code"]'));

    document.addEventListener('keydown', e => {
        if (!isNumericTarget(e.target)) return;
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        const allowed = ['Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
        if (allowed.includes(e.key)) return;
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });

    document.addEventListener('input', e => {
        if (!isNumericTarget(e.target)) return;
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    document.addEventListener('paste', e => {
        if (!isNumericTarget(e.target)) return;
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const digits = text.replace(/\D/g, '');
        if (document.queryCommandSupported && document.queryCommandSupported('insertText')) {
            document.execCommand('insertText', false, digits);
        } else {
            e.target.value = digits;
        }
    });
})();
