// The product video field. It keeps its size in data-video-bytes so the photo scripts can count it
// toward the shared budget, and they keep theirs in data-photo-bytes so this one can do the same.
(() => {
    const field = document.querySelector('[data-product-video]');
    if (!field) return;

    const input = field.querySelector('input[type="file"]');
    const dropzone = field.querySelector('[data-video-dropzone]');
    const preview = field.querySelector('[data-video-preview]');
    const player = preview.querySelector('video');
    const removeFlag = field.querySelector('[data-remove-video]');
    const budget = Number(field.dataset.budgetBytes);
    const budgetMb = budget / (1024 * 1024);
    const savedSrc = player.getAttribute('src');
    let objectUrl = null;

    const setBytes = (bytes) => {
        field.dataset.videoBytes = String(bytes);
        field.dispatchEvent(new CustomEvent('product-video-change', { bubbles: true }));
    };

    const show = (src) => {
        if (objectUrl && src !== objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        if (src) player.src = src;
        else { player.pause(); player.removeAttribute('src'); player.load(); }
        preview.hidden = !src;
        dropzone.hidden = !!src;
    };

    const clear = () => {
        input.value = '';
        if (removeFlag) removeFlag.value = savedSrc ? '1' : '0';
        show(null);
        setBytes(0);
    };

    const accept = (file) => {
        if (!file) return;
        if (!['video/mp4', 'video/webm'].includes(file.type)) {
            input.value = '';
            alert('Please choose an MP4 or WebM video.');
            return;
        }
        const photoBytes = Number(field.dataset.photoBytes || 0);
        if (file.size + photoBytes > budget) {
            input.value = '';
            alert(`Photos and video combined must be ${budgetMb} MB or smaller. Choose a shorter video or remove some photos.`);
            return;
        }
        if (input.files[0] !== file) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
        }
        if (removeFlag) removeFlag.value = '0';
        objectUrl = URL.createObjectURL(file);
        show(objectUrl);
        setBytes(file.size);
    };

    input.addEventListener('change', () => accept(input.files[0]));
    preview.querySelector('[data-video-remove]').addEventListener('click', clear);

    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
    });
    ['dragenter', 'dragover'].forEach((type) => dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        dropzone.classList.add('dragover');
    }));
    ['dragleave', 'drop'].forEach((type) => dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        dropzone.classList.remove('dragover');
    }));
    dropzone.addEventListener('drop', (event) => accept(event.dataTransfer.files[0]));

    field.closest('form')?.addEventListener('reset', () => setTimeout(clear, 0));
})();
