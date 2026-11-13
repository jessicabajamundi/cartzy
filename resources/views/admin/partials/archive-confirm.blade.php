<dialog id="archive-confirm" aria-labelledby="archive-confirm-title" aria-describedby="archive-confirm-message archive-confirm-note">
    <div class="archive-confirm-content">
        <button type="button" class="archive-confirm-close" data-archive-cancel aria-label="Close confirmation">&times;</button>
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
    #archive-confirm {
        width: min(480px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--line, #e2e8f0);
        border-radius: 20px;
        background: var(--surface, #fff);
        color: var(--ink, #1f2937);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.28);
        overflow: hidden;
    }
    #archive-confirm::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(4px);
    }
    #archive-confirm[open] {
        animation: user-modal-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .archive-confirm-content {
        position: relative;
        padding: 32px 30px 28px;
        text-align: left;
    }
    .archive-confirm-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f8fafc;
        color: #64748b;
        font-size: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all 0.15s ease;
    }
    .archive-confirm-close:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: scale(1.05);
    }
    .archive-confirm-symbol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        margin-bottom: 18px;
        border-radius: 14px;
        background: #f0eaf6;
        color: var(--accent);
        font-size: 24px;
        font-weight: 700;
    }
    #archive-confirm[data-action="delete"] .archive-confirm-symbol {
        background: #fef2f2;
        color: #dc2626;
    }
    #archive-confirm-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }
    #archive-confirm-message {
        overflow-wrap: anywhere;
        font-size: 13.5px;
        color: #4b5563;
        line-height: 1.55;
    }
    #archive-confirm-note {
        margin-top: 10px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }
    .archive-confirm-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 26px;
    }
    .archive-confirm-actions .button {
        min-height: 42px;
        padding: 0 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
    }
    #archive-confirm[data-action="delete"] #archive-confirm-submit {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    #archive-confirm[data-action="delete"] #archive-confirm-submit:hover {
        background: #b91c1c;
    }
    @media (max-width: 480px) {
        .archive-confirm-content { padding: 26px 20px 22px; }
        .archive-confirm-actions .button { flex: 1 1 auto; }
    }
</style>
@endpush
