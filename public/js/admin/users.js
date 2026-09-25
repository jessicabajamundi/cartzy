/**
 * Cartzy Admin - Users Management Scripts
 * Handles user status change modal and detailed profile inspection modal
 */

function openStatusModal(id, name, currentStatus) {
    const nameEl = document.getElementById('statusUserName');
    if (nameEl) nameEl.innerText = name;
    
    const formEl = document.getElementById('statusForm');
    if (formEl) formEl.action = "/admin/users/" + id + "/status";
    
    const radio = document.querySelector(`input[name="status"][value="${currentStatus}"]`);
    if (radio) radio.checked = true;

    const modal = document.getElementById('statusModal');
    if (modal) modal.classList.remove('hidden');
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    if (modal) modal.classList.add('hidden');
}

function openProfileModal(user) {
    if (!user) return;
    
    const nameEl = document.getElementById('modalProfileName');
    if (nameEl) nameEl.innerText = user.name || '';
    
    const emailEl = document.getElementById('modalProfileEmail');
    if (emailEl) emailEl.innerText = user.email || '';
    
    const phoneEl = document.getElementById('modalProfilePhone');
    if (phoneEl) phoneEl.innerText = user.phone || 'N/A';
    
    const roleEl = document.getElementById('modalProfileRole');
    if (roleEl) roleEl.innerText = user.role || '';
    
    const statusEl = document.getElementById('modalProfileStatus');
    if (statusEl) statusEl.innerText = (user.status || '').toUpperCase();
    
    const ordersEl = document.getElementById('modalProfileOrders');
    if (ordersEl) ordersEl.innerText = user.orders_count || '0';
    
    const violationsEl = document.getElementById('modalProfileViolations');
    if (violationsEl) violationsEl.innerText = (user.violations || 0) + ' Strikes';
    
    const avatarEl = document.getElementById('modalProfileAvatar');
    if (avatarEl && user.name) {
        avatarEl.innerText = user.name.substring(0, 2).toUpperCase();
    }

    const modal = document.getElementById('profileModal');
    if (modal) modal.classList.remove('hidden');
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) modal.classList.add('hidden');
}
