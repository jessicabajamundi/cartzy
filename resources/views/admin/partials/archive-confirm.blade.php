<dialog id="archive-confirm" aria-labelledby="archive-confirm-title" aria-describedby="archive-confirm-message archive-confirm-note">
    <div class="archive-confirm-content">
        <button type="button" class="archive-confirm-close icon-button" data-archive-cancel aria-label="Close confirmation">&times;</button>
        <span class="archive-confirm-symbol" aria-hidden="true">!</span>
        <h2 id="archive-confirm-title">Permanently delete this listing?</h2>
        <p id="archive-confirm-message"></p>
        <p id="archive-confirm-note"></p>
        <div class="archive-confirm-actions">
            <button type="button" class="button subtle" data-archive-cancel autofocus>Cancel</button>
            <button type="button" class="button" id="archive-confirm-submit">Yes, delete permanently</button>
        </div>
    </div>
</dialog>

@push('styles')
<style>
    #archive-confirm { width: min(460px, calc(100vw - 32px)); max-height: calc(100dvh - 32px); padding: 0; border: 1px solid var(--line); border-radius: 20px; background: var(--surface, #fff); color: var(--ink); box-shadow: 0 24px 80px #28213333; }
    #archive-confirm::backdrop { background: #28213388; backdrop-filter: blur(3px); }
    .archive-confirm-content { position: relative; padding: 32px; }
    .archive-confirm-close { position: absolute; top: 12px; right: 12px; font-size: 24px; }
    .archive-confirm-symbol { display: grid; place-items: center; width: 48px; height: 48px; margin-bottom: 20px; border-radius: 50%; background: #f0eaf6; color: var(--accent); font-size: 26px; font-weight: 700; }
    #archive-confirm[data-action="delete"] .archive-confirm-symbol { background: #fce9e9; color: #b42332; }
    #archive-confirm-title { font-size: 21px; margin-bottom: 12px; }
    #archive-confirm-message { overflow-wrap: anywhere; }
    #archive-confirm-note { margin-top: 12px; color: var(--muted); font-size: 13px; }
    .archive-confirm-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; margin-top: 28px; }
    #archive-confirm[data-action="delete"] #archive-confirm-submit { background: #b42332; border-color: #b42332; color: #fff; }
    #archive-confirm[data-action="delete"] #archive-confirm-submit:hover { background: #941c29; }
    @media (max-width: 480px) { .archive-confirm-content { padding: 28px 20px 20px; } .archive-confirm-actions .button { flex: 1 1 auto; } }
</style>
@endpush
