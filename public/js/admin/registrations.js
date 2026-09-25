/**
 * Cartzy Admin - Seller Registrations Management Scripts
 * Handles applicant rejection modal and document preview modal
 */

function openRejectModal(id, name, email) {
    const nameEl = document.getElementById('rejectApplicantName');
    if (nameEl) nameEl.innerText = name;

    const emailEl = document.getElementById('rejectApplicantEmail');
    if (emailEl) emailEl.innerText = email;

    const formEl = document.getElementById('rejectForm');
    if (formEl) formEl.action = "/admin/registrations/" + id + "/status";

    const modal = document.getElementById('rejectModal');
    if (modal) modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    if (modal) modal.classList.add('hidden');
}

function openDocPreview(docName, ownerName, photoUrl) {
    const docNameEl = document.getElementById('previewDocName');
    if (docNameEl) docNameEl.innerText = docName;

    const docOwnerEl = document.getElementById('previewDocOwner');
    if (docOwnerEl) docOwnerEl.innerText = 'Applicant: ' + ownerName;
    
    const imgContainer = document.getElementById('previewDocImageContainer');
    const img = document.getElementById('previewDocImage');
    const placeholder = document.getElementById('previewDocPlaceholder');
    
    if (photoUrl && (photoUrl.endsWith('.jpg') || photoUrl.endsWith('.jpeg') || photoUrl.endsWith('.png') || photoUrl.startsWith('http') || photoUrl.startsWith('/'))) {
        if (img) img.src = photoUrl;
        if (imgContainer) imgContainer.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    } else {
        if (img) img.src = '';
        if (imgContainer) imgContainer.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
    }

    const modal = document.getElementById('docPreviewModal');
    if (modal) modal.classList.remove('hidden');
}

function closeDocPreview() {
    const modal = document.getElementById('docPreviewModal');
    if (modal) modal.classList.add('hidden');
}
