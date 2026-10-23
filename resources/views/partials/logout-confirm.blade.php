{{-- Shared sign-out confirmation. Included once per layout; it intercepts every form that posts to the logout route. --}}
@auth
<dialog class="logout-confirm" id="logout-confirm" aria-labelledby="logout-confirm-title" aria-describedby="logout-confirm-text">
    <h2 id="logout-confirm-title">Sign out?</h2>
    <p id="logout-confirm-text">You're signed in as <strong>{{ auth()->user()->name }}</strong>. You'll need to log in again to continue.</p>
    <div class="logout-confirm-actions">
        <button type="button" class="logout-confirm-cancel" data-logout-cancel>Stay signed in</button>
        <button type="button" class="logout-confirm-submit" data-logout-confirm>Sign out</button>
    </div>
</dialog>
<style>
    .logout-confirm { width: min(380px, calc(100vw - 32px)); margin: auto; padding: 28px 24px 22px; border: 0; border-radius: 18px; color: #282133; background: #fff; box-shadow: 0 24px 60px rgba(40, 33, 51, .28); text-align: center; font-family: inherit; }
    .logout-confirm::backdrop { background: rgba(40, 33, 51, .45); backdrop-filter: blur(2px); }
    .logout-confirm[open] { animation: logout-confirm-in .16s ease-out; }
    @keyframes logout-confirm-in { from { opacity: 0; transform: translateY(8px) scale(.98); } }
    .logout-confirm h2 { margin: 0 0 6px; font-size: 18px; font-weight: 800; }
    .logout-confirm p { margin: 0 0 20px; color: #6b6178; font-size: 14px; line-height: 1.5; }
    .logout-confirm p strong { color: #3e354c; }
    .logout-confirm-actions { display: flex; gap: 10px; }
    .logout-confirm-actions button { flex: 1; min-height: 44px; padding: 10px 14px; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: background-color .15s, border-color .15s; }
    .logout-confirm-cancel { border: 1px solid #e1dde7; color: #3e354c; background: #fff; }
    .logout-confirm-cancel:hover { background: #f6f2f8; }
    .logout-confirm-submit { border: 1px solid #b42318; color: #fff; background: #b42318; }
    .logout-confirm-submit:hover { background: #912018; }
    .logout-confirm-submit:disabled { opacity: .7; cursor: wait; }
    .logout-confirm-actions button:focus-visible { outline: 2px solid #574362; outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { .logout-confirm[open] { animation: none; } }
</style>
<script>
(() => {
    const dialog = document.getElementById('logout-confirm');
    if (!dialog || typeof dialog.showModal !== 'function') return; // very old browsers: sign out without a prompt
    const logoutPath = @json(route('logout', [], false));
    const confirmButton = dialog.querySelector('[data-logout-confirm]');
    let pendingForm = null;

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || new URL(form.action).pathname !== logoutPath) return;
        event.preventDefault();
        pendingForm = form;
        confirmButton.disabled = false;
        confirmButton.textContent = 'Sign out';
        dialog.showModal();
        dialog.querySelector('[data-logout-cancel]').focus();
    }, true);

    confirmButton.addEventListener('click', () => {
        if (!pendingForm) return;
        confirmButton.disabled = true;
        confirmButton.textContent = 'Signing out…';
        pendingForm.submit(); // submit() skips the listener above
    });
    dialog.querySelector('[data-logout-cancel]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); }); // backdrop click
    dialog.addEventListener('close', () => { pendingForm = null; });
})();
</script>
@endauth
