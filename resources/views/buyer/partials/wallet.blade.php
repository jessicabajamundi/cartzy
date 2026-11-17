@php($wallet = app(\App\Services\BuyerWallet::class)->summary($user))
<section class="acct-card" id="acct-wallet" data-acct-section="wallet" data-wallet-url="{{ route('buyer.wallet') }}">
    <header class="acct-head">
        <div>
            <p class="acct-eyebrow">Refund credit</p>
            <h2 class="acct-title">Cartzy Wallet</h2>
            <p class="acct-lead">Use refunded credit for any product on Cartzy. No cash-out or top-ups.</p>
        </div>
        <span class="wallet-pill">Store Credit Only</span>
    </header>

    <div class="wallet-hero">
        <div class="wallet-hero-top">
            <div class="wallet-hero-label">
                <span class="wallet-icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>
                        <path d="M16 12h5v3h-5a1.5 1.5 0 0 1 0-3Z"/>
                        <path d="M3 7V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </span>
                <div>
                    <span class="wallet-kicker">Available Balance</span>
                    <strong class="wallet-balance" data-wallet-balance>₱{{ number_format($wallet['balance_minor'] / 100, 2) }}</strong>
                </div>
            </div>

            <div class="wallet-actions">
                <button type="button"
                        class="wallet-btn wallet-btn-secondary"
                        id="wallet-history-toggle"
                        aria-expanded="true"
                        aria-controls="wallet-history-panel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
                        <path d="M3 3v5h5"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                    <span>Transaction History</span>
                    <span class="wallet-count-badge" data-wallet-count>{{ count($wallet['transactions']) }}</span>
                </button>

                <a href="{{ route('returns.index') }}" class="wallet-btn wallet-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 14 4 9l5-5"/>
                        <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>
                    </svg>
                    <span>View Pending Refund Requests</span>
                </a>
            </div>
        </div>

        <div class="wallet-pending-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 8v4l2.5 1.5"/>
            </svg>
            <p data-wallet-pending>Pending approved refunds: ₱{{ number_format($wallet['pending_refunds_minor'] / 100, 2) }}. Pending amounts cannot be spent yet.</p>
        </div>
    </div>

    <div class="wallet-history-panel" id="wallet-history-panel">
        <div class="wallet-history-header">
            <div>
                <h3 class="wallet-history-title">Transaction History</h3>
                <p class="wallet-history-sub">Latest 50 entries · Philippine time</p>
            </div>
        </div>

        <div class="wallet-history-list" data-wallet-history>
            @forelse($wallet['transactions'] as $entry)
                @php($isCredit = $entry['amount_minor'] > 0)
                <article class="wallet-entry">
                    <div class="wallet-entry-main">
                        <strong>{{ $entry['description'] }}</strong>
                        <p>{{ $entry['order_reference'] }} · {{ $entry['created_at'] }}</p>
                    </div>
                    <span class="wallet-entry-amount {{ $isCredit ? 'is-credit' : 'is-debit' }}">
                        {{ $isCredit ? '+' : '−' }}₱{{ number_format(abs($entry['amount_minor']) / 100, 2) }}
                    </span>
                </article>
            @empty
                <div class="wallet-empty">
                    <span class="wallet-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 10h18"/>
                        </svg>
                    </span>
                    <strong>No transactions yet</strong>
                    <p>Your completed refunds and wallet payments will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
#acct-wallet .acct-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
#acct-wallet .wallet-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    background: rgba(212, 165, 184, 0.14);
    border: 1px solid rgba(212, 165, 184, 0.28);
    color: var(--acct-accent, #d8a7b8);
}
#acct-wallet .wallet-hero {
    margin-top: 16px;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.09);
    background: linear-gradient(135deg, rgba(216, 167, 184, 0.12), rgba(111, 99, 130, 0.10));
}
html:not([data-theme="dark"]) #acct-wallet .wallet-hero {
    border-color: rgba(86, 75, 104, 0.15);
    background: linear-gradient(135deg, #faf5f7, #f3eff6);
}
#acct-wallet .wallet-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
}
#acct-wallet .wallet-hero-label {
    display: flex;
    align-items: center;
    gap: 14px;
}
#acct-wallet .wallet-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(216, 167, 184, 0.18);
    color: #e4b8c7;
    flex-shrink: 0;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-icon-badge {
    background: #ffffff;
    color: #564b68;
    border: 1px solid rgba(86, 75, 104, 0.14);
}
#acct-wallet .wallet-icon-badge svg {
    width: 22px;
    height: 22px;
}
#acct-wallet .wallet-kicker {
    display: block;
    font-size: 12px;
    font-weight: 600;
    opacity: 0.78;
}
#acct-wallet .wallet-balance {
    display: block;
    font-size: 32px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.02em;
    margin-top: 2px;
    color: #e7bccb;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-balance {
    color: #261e30;
}
#acct-wallet .wallet-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
#acct-wallet .wallet-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.15s ease, opacity 0.15s ease, background-color 0.15s ease;
    border: 1px solid transparent;
}
#acct-wallet .wallet-btn:hover {
    transform: translateY(-1px);
}
#acct-wallet .wallet-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
#acct-wallet .wallet-btn-primary {
    background: #d8a7b8;
    color: #1f1726;
}
#acct-wallet .wallet-btn-primary:hover {
    opacity: 0.92;
}
#acct-wallet .wallet-btn-secondary {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.14);
    color: inherit;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-btn-secondary {
    background: #ffffff;
    border-color: rgba(86, 75, 104, 0.22);
    color: #261e30;
}
#acct-wallet .wallet-count-badge {
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    background: rgba(216, 167, 184, 0.22);
}
#acct-wallet .wallet-pending-banner {
    margin-top: 16px;
    padding: 11px 14px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0, 0, 0, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.07);
    font-size: 12.5px;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-pending-banner {
    background: rgba(255, 255, 255, 0.78);
    border-color: rgba(86, 75, 104, 0.12);
}
#acct-wallet .wallet-pending-banner svg {
    width: 16px;
    height: 16px;
    opacity: 0.78;
    flex-shrink: 0;
}
#acct-wallet .wallet-pending-banner p {
    margin: 0;
}
#acct-wallet .wallet-history-panel {
    margin-top: 18px;
    padding: 18px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.02);
}
html:not([data-theme="dark"]) #acct-wallet .wallet-history-panel {
    border-color: rgba(86, 75, 104, 0.12);
    background: #fcfbfc;
}
#acct-wallet .wallet-history-panel[hidden] {
    display: none;
}
#acct-wallet .wallet-history-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}
#acct-wallet .wallet-history-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
}
#acct-wallet .wallet-history-sub {
    margin: 2px 0 0;
    font-size: 12px;
    opacity: 0.7;
}
#acct-wallet .wallet-history-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
#acct-wallet .wallet-entry {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
}
html:not([data-theme="dark"]) #acct-wallet .wallet-entry {
    border-color: rgba(86, 75, 104, 0.12);
    background: #ffffff;
}
#acct-wallet .wallet-entry-main strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
}
#acct-wallet .wallet-entry-main p {
    margin: 3px 0 0;
    font-size: 11.5px;
    opacity: 0.7;
}
#acct-wallet .wallet-entry-amount {
    font-size: 13.5px;
    font-weight: 800;
    white-space: nowrap;
}
#acct-wallet .wallet-entry-amount.is-credit {
    color: #34d399;
}
#acct-wallet .wallet-entry-amount.is-debit {
    color: #fb7185;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-entry-amount.is-credit {
    color: #047857;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-entry-amount.is-debit {
    color: #be123c;
}
#acct-wallet .wallet-empty {
    padding: 24px 16px;
    text-align: center;
    border-radius: 12px;
    border: 1px dashed rgba(255, 255, 255, 0.12);
}
html:not([data-theme="dark"]) #acct-wallet .wallet-empty {
    border-color: rgba(86, 75, 104, 0.18);
}
#acct-wallet .wallet-empty-icon {
    width: 38px;
    height: 38px;
    margin: 0 auto 8px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(216, 167, 184, 0.14);
    color: #d8a7b8;
}
#acct-wallet .wallet-empty-icon svg {
    width: 18px;
    height: 18px;
}
#acct-wallet .wallet-empty strong {
    display: block;
    font-size: 13.5px;
}
#acct-wallet .wallet-empty p {
    margin: 4px 0 0;
    font-size: 12px;
    opacity: 0.72;
}
</style>

<script>
(() => {
    const toggle = document.getElementById('wallet-history-toggle');
    const panel = document.getElementById('wallet-history-panel');
    if (!toggle || !panel) return;
    toggle.addEventListener('click', () => {
        const isHidden = panel.hasAttribute('hidden');
        if (isHidden) {
            panel.removeAttribute('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            panel.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
<script src="{{ asset('js/buyer/wallet.js') }}" defer></script>
