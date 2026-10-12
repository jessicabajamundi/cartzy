document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.getElementById('shop-all-dropdown');
    if (!dropdown) return;
    const trigger = dropdown.querySelector('summary');
    let closeTimer;
    let restoringFocus = false;
    const cancelClose = () => clearTimeout(closeTimer);
    dropdown.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'mouse') return;
        cancelClose();
        dropdown.open = true;
    });
    dropdown.addEventListener('pointerleave', event => {
        if (event.pointerType !== 'mouse') return;
        closeTimer = setTimeout(() => {
            if (!dropdown.contains(document.activeElement)) dropdown.open = false;
        }, 180);
    });
    dropdown.addEventListener('focusin', event => {
        cancelClose();
        // Keyboard focus opens the list; touch/click uses native details behavior.
        if (!restoringFocus && event.target.matches(':focus-visible')) dropdown.open = true;
    });
    dropdown.addEventListener('focusout', event => {
        if (!dropdown.contains(event.relatedTarget)) dropdown.open = false;
    });
    document.addEventListener('pointerdown', event => {
        if (!dropdown.contains(event.target)) dropdown.open = false;
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || !dropdown.open) return;
        cancelClose();
        if (dropdown.contains(document.activeElement)) {
            restoringFocus = true;
            trigger.focus();
            restoringFocus = false;
        }
        dropdown.open = false;
    });
});
