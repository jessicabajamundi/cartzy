// Shared marketplace navigation for home and buyer account pages.
document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('category-mega-dropdown');
    const allButton = document.getElementById('all-categories-btn');
    const search = document.getElementById('mega-category-search');
    const departments = document.querySelectorAll('[data-shop-department]');
    let selectedDepartment = '';
    let opener = allButton;

    window.filterMegaMenu = function (query) {
        const term = (query || '').trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('.shop-category-group').forEach(group => {
            let matches = 0;
            group.querySelectorAll('.mega-cat-item').forEach(item => {
                const visible = (!selectedDepartment || group.dataset.department === selectedDepartment)
                    && (!term || item.dataset.catName.includes(term));
                item.hidden = !visible;
                item.style.display = visible ? '' : 'none';
                if (visible) matches++;
            });
            group.hidden = matches === 0;
            group.style.display = matches ? '' : 'none';
            visibleCount += matches;
        });
        document.getElementById('shop-category-empty')?.classList.toggle('hidden', visibleCount > 0);
    };

    function openMenu(department, trigger) {
        if (!dropdown) return;
        selectedDepartment = department;
        opener = trigger;
        if (search) search.value = '';
        window.filterMegaMenu('');
        dropdown.classList.remove('hidden');
        allButton?.setAttribute('aria-expanded', String(!department));
        departments.forEach(button => button.setAttribute('aria-expanded', String(button.dataset.shopDepartment === department)));
        const chevron = document.getElementById('cat-chevron');
        if (chevron) chevron.style.transform = !department ? 'rotate(180deg)' : '';
        document.getElementById('shop-menu-title').textContent = department ? trigger.textContent.replace('⌄', '').trim() : 'Shop by department';
        search?.focus();
    }

    window.closeCategoryMenu = function (restoreFocus = false) {
        if (!dropdown || dropdown.classList.contains('hidden')) return;
        dropdown.classList.add('hidden');
        allButton?.setAttribute('aria-expanded', 'false');
        departments.forEach(button => button.setAttribute('aria-expanded', 'false'));
        const chevron = document.getElementById('cat-chevron');
        if (chevron) chevron.style.transform = '';
        if (restoreFocus) opener?.focus();
    };

    window.toggleCategoryMenu = function () {
        if (dropdown && !dropdown.classList.contains('hidden') && !selectedDepartment) window.closeCategoryMenu();
        else openMenu('', allButton);
    };
    departments.forEach(button => button.addEventListener('click', () => {
        if (!dropdown.classList.contains('hidden') && selectedDepartment === button.dataset.shopDepartment) window.closeCategoryMenu();
        else openMenu(button.dataset.shopDepartment, button);
    }));
    const modified = event => event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0;
    document.querySelectorAll('.mega-cat-item[data-category-slug]').forEach(link => link.addEventListener('click', event => {
        if (modified(event) || !window.filterByCategory) return;
        event.preventDefault();
        window.filterByCategory(link.dataset.categorySlug, link.textContent.trim());
        window.closeCategoryMenu(true);
    }));
    window.filterFeaturedSection = function (type, event) {
        if (event && modified(event)) return true;
        const sections = { 'just-for-you': 'daily-discover-section', 'new-in': 'daily-discover-section', sale: 'flash-sale' };
        const target = document.getElementById(sections[type]);
        if (!target) return true;
        window.closeCategoryMenu();
        if (type === 'just-for-you' && window.clearAllProductFilters) window.clearAllProductFilters();
        target.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        return false;
    };
    document.addEventListener('click', event => {
        if (!document.getElementById('category-mega-menu-wrapper')?.contains(event.target)) window.closeCategoryMenu();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') window.closeCategoryMenu(true);
    });
    document.addEventListener('focusin', event => {
        if (!document.getElementById('category-mega-menu-wrapper')?.contains(event.target)) window.closeCategoryMenu();
    });
});
