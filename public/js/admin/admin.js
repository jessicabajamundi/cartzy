/**
 * Cartzy Admin Dashboard Layout Logic
 * Handles sidebar collapse, persistence, and auto-dismiss alerts
 */

function toggleSidebarCollapse() {
    const sidebar = document.getElementById('admin-sidebar');
    if (!sidebar) return;
    
    if (window.innerWidth < 1024) {
        sidebar.classList.toggle('-translate-x-full');
    } else {
        sidebar.classList.toggle('is-collapsed');
        const isMini = sidebar.classList.contains('is-collapsed');
        localStorage.setItem('admin_sidebar_mini', isMini ? '1' : '0');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth >= 1024 && localStorage.getItem('admin_sidebar_mini') === '1') {
        const sidebar = document.getElementById('admin-sidebar');
        if (sidebar) {
            sidebar.classList.add('is-collapsed');
        }
    }
});

function dismissAlert(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(() => el.remove(), 700);
    }
}

// Auto-dismiss alert popup after exactly 10 seconds (10,000ms)
setTimeout(() => {
    dismissAlert('flash-alert-success');
    dismissAlert('flash-alert-info');
}, 10000);
