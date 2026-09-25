/**
 * Cartzy Buyer Account Page Scripts
 * Handles age calculation, section scroll-spy, and address modal
 */

function updateAgeDisplay(dateStr) {
    if (!dateStr) return;
    const today = new Date();
    const bday = new Date(dateStr);
    let age = today.getFullYear() - bday.getFullYear();
    const m = today.getMonth() - bday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
    
    const input = document.getElementById('ageDisplay');
    if (input) {
        input.value = (age >= 0 ? age : 0) + ' years old';
    }
}

function scrollToSection(event, sectionId) {
    if (event) event.preventDefault();
    const el = document.getElementById(sectionId);
    if (el) {
        const headerOffset = 136;
        const elementPosition = el.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
        history.replaceState(null, '', '#' + sectionId);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const bdayInput = document.getElementById('birthday');
    if (bdayInput && bdayInput.value) {
        updateAgeDisplay(bdayInput.value);
    }

    // Handle auto-scroll based on URL tab query or hash
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const hash = window.location.hash;

    if (hash === '#delivery-addresses' || tabParam === 'addresses') {
        setTimeout(() => scrollToSection(null, 'delivery-addresses'), 150);
    } else if (hash === '#password-settings' || tabParam === 'password') {
        setTimeout(() => scrollToSection(null, 'password-settings'), 150);
    } else if (hash === '#personal-details' || tabParam === 'profile') {
        setTimeout(() => scrollToSection(null, 'personal-details'), 150);
    }

    // Scroll spy to update active state of sidebar links
    const sections = ['personal-details', 'delivery-addresses', 'password-settings'];
    const navItems = document.querySelectorAll('.account-nav-item');

    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(id => {
            const section = document.getElementById(id);
            if (section) {
                const rect = section.getBoundingClientRect();
                if (rect.top <= 220 && rect.bottom >= 120) {
                    current = id;
                }
            }
        });

        if (current) {
            navItems.forEach(item => {
                if (item.getAttribute('data-target') === current) {
                    item.classList.add('bg-gray-100', 'text-black', 'font-extrabold');
                    item.classList.remove('text-gray-600');
                } else {
                    item.classList.remove('bg-gray-100', 'text-black', 'font-extrabold');
                    item.classList.add('text-gray-600');
                }
            });
        }
    });
});

function openAddressModal(type = '') {
    const modal = document.getElementById('addressModal');
    if (modal) modal.style.display = 'flex';
    const title = document.getElementById('modalAddressTitle');
    if (type) {
        const radio = document.getElementById('type_' + type);
        if (radio) radio.checked = true;
        if (title) title.innerText = 'Add ' + type + ' Address';
    } else {
        if (title) title.innerText = 'Delivery Address';
    }
}

function closeAddressModal() {
    const modal = document.getElementById('addressModal');
    if (modal) modal.style.display = 'none';
}
