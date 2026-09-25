/**
 * Cartzy Admin - Chat Quick Messages
 */

function setQuickMessage(text) {
    const input = document.getElementById('chatMessageInput');
    if (input) {
        input.value = text;
        input.focus();
    }
}
