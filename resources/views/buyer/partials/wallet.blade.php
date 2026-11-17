@php($wallet = app(\App\Services\BuyerWallet::class)->summary($user))
@php($pendingRefundCases = \App\Models\ReturnCase::visibleTo($user)
    ->whereNotIn('status', ['refunded', 'rejected', 'cancelled', 'closed'])
    ->with(['dispute.order', 'dispute.sellerOrder.seller'])
    ->latest('updated_at')
    ->limit(25)
    ->get())

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
                        data-wallet-modal-open="wallet-history-modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
                        <path d="M3 3v5h5"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                    <span>Transaction History</span>
                    <span class="wallet-count-badge" data-wallet-count>{{ count($wallet['transactions']) }}</span>
                </button>

                <button type="button"
                        class="wallet-btn wallet-btn-primary"
                        data-wallet-modal-open="wallet-refunds-modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 14 4 9l5-5"/>
                        <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>
                    </svg>
                    <span>View Pending Refund Requests</span>
                    @if($pendingRefundCases->isNotEmpty())
                        <span class="wallet-count-badge">{{ $pendingRefundCases->count() }}</span>
                    @endif
                </button>
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

    {{-- ── Transaction History Modal Pop-up ── --}}
    <dialog id="wallet-history-modal" class="wallet-modal" aria-labelledby="wallet-history-modal-title">
        <div class="wallet-modal-card">
            <header class="wallet-modal-head">
                <div>
                    <span class="wallet-modal-eyebrow">Cartzy Wallet</span>
                    <h3 id="wallet-history-modal-title" class="wallet-modal-title">Transaction History</h3>
                    <p class="wallet-modal-sub">Latest 50 entries · Philippine time</p>
                </div>
                <button type="button" class="wallet-modal-close" data-wallet-modal-close aria-label="Close modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </header>

            <div class="wallet-modal-body">
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

            <footer class="wallet-modal-foot">
                <button type="button" class="wallet-btn wallet-btn-secondary" data-wallet-modal-close>Close</button>
            </footer>
        </div>
    </dialog>

    {{-- ── Pending Refund Requests Modal Pop-up ── --}}
    <dialog id="wallet-refunds-modal" class="wallet-modal" aria-labelledby="wallet-refunds-modal-title">
        <div class="wallet-modal-card">
            <header class="wallet-modal-head">
                <div>
                    <span class="wallet-modal-eyebrow">Returns &amp; Refunds</span>
                    <h3 id="wallet-refunds-modal-title" class="wallet-modal-title">Pending Refund Requests</h3>
                    <p class="wallet-modal-sub">Track active return and refund cases linked to your orders.</p>
                </div>
                <button type="button" class="wallet-modal-close" data-wallet-modal-close aria-label="Close modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </header>

            <div class="wallet-modal-body">
                @forelse($pendingRefundCases as $case)
                    @php($orderRef = $case->dispute?->order?->reference ?? ('Case #' . $case->id))
                    @php($shopName = $case->dispute?->sellerOrder?->seller?->name ?? 'Cartzy Seller')
                    <article class="wallet-entry">
                        <div class="wallet-entry-main">
                            <strong>{{ $orderRef }} · {{ $shopName }}</strong>
                            <p>{{ ucwords(str_replace('_', ' ', (string) $case->status)) }} · Updated {{ $case->updated_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}</p>
                        </div>
                        <span class="wallet-entry-amount is-credit">
                            ₱{{ number_format(($case->amount_minor ?? 0) / 100, 2) }}
                        </span>
                    </article>
                @empty
                    <div class="wallet-empty">
                        <span class="wallet-empty-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 14 4 9l5-5"/>
                                <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>
                            </svg>
                        </span>
                        <strong>No pending refund requests</strong>
                        <p>When you submit a return or refund request, its progress will appear here.</p>
                    </div>
                @endforelse
            </div>

            <footer class="wallet-modal-foot">
                <button type="button" class="wallet-btn wallet-btn-secondary" data-wallet-modal-close>Close</button>
                <a href="{{ route('returns.index') }}" class="wallet-btn wallet-btn-primary">Open Returns &amp; Refunds Page →</a>
            </footer>
        </div>
    </dialog>
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
    background: rgba(31, 23, 38, 0.14);
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

/* ── Modal Pop-ups ── */
#acct-wallet dialog.wallet-modal {
    border: none;
    padding: 0;
    margin: auto;
    width: min(560px, calc(100vw - 28px));
    max-height: calc(100vh - 40px);
    border-radius: 20px;
    background: transparent;
    color: #f3edf5;
    overflow: visible;
}
html:not([data-theme="dark"]) #acct-wallet dialog.wallet-modal {
    color: #261e30;
}
#acct-wallet dialog.wallet-modal::backdrop {
    background: rgba(15, 10, 20, 0.62);
    backdrop-filter: blur(6px);
}
#acct-wallet .wallet-modal-card {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 40px);
    border-radius: 20px;
    background: #1e1824;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.42);
    overflow: hidden;
    animation: walletModalIn 0.2s ease-out;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-modal-card {
    background: #ffffff;
    border-color: rgba(86, 75, 104, 0.16);
    box-shadow: 0 24px 60px rgba(38, 30, 48, 0.18);
}
@keyframes walletModalIn {
    from { opacity: 0; transform: translateY(8px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
#acct-wallet .wallet-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 22px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
html:not([data-theme="dark"]) #acct-wallet .wallet-modal-head {
    border-bottom-color: rgba(86, 75, 104, 0.12);
}
#acct-wallet .wallet-modal-eyebrow {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #d8a7b8;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-modal-eyebrow {
    color: #865561;
}
#acct-wallet .wallet-modal-title {
    margin: 4px 0 0;
    font-size: 19px;
    font-weight: 800;
}
#acct-wallet .wallet-modal-sub {
    margin: 3px 0 0;
    font-size: 12.5px;
    opacity: 0.72;
}
#acct-wallet .wallet-modal-close {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
    color: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-modal-close {
    border-color: rgba(86, 75, 104, 0.16);
    background: #f8f5f9;
}
#acct-wallet .wallet-modal-close svg {
    width: 16px;
    height: 16px;
}
#acct-wallet .wallet-modal-body {
    padding: 18px 22px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
#acct-wallet .wallet-modal-foot {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    flex-wrap: wrap;
}
html:not([data-theme="dark"]) #acct-wallet .wallet-modal-foot {
    border-top-color: rgba(86, 75, 104, 0.12);
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
    background: #faf9fb;
}
#acct-wallet .wallet-entry-main strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
}
#acct-wallet .wallet-entry-main p {
    margin: 3px 0 0;
    font-size: 11.5px;
    opacity: 0.72;
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
    padding: 26px 16px;
    text-align: center;
    border-radius: 12px;
    border: 1px dashed rgba(255, 255, 255, 0.14);
}
html:not([data-theme="dark"]) #acct-wallet .wallet-empty {
    border-color: rgba(86, 75, 104, 0.18);
}
#acct-wallet .wallet-empty-icon {
    width: 40px;
    height: 40px;
    margin: 0 auto 10px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(216, 167, 184, 0.16);
    color: #d8a7b8;
}
#acct-wallet .wallet-empty-icon svg {
    width: 19px;
    height: 19px;
}
#acct-wallet .wallet-empty strong {
    display: block;
    font-size: 14px;
}
#acct-wallet .wallet-empty p {
    margin: 4px 0 0;
    font-size: 12px;
    opacity: 0.74;
}
</style>

<script>
(() => {
    const root = document.getElementById('acct-wallet');
    if (!root) return;

    root.querySelectorAll('[data-wallet-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.walletModalOpen);
            if (modal && typeof modal.showModal === 'function' && !modal.open) {
                modal.showModal();
            }
        });
    });

    root.querySelectorAll('dialog.wallet-modal').forEach((modal) => {
        modal.querySelectorAll('[data-wallet-modal-close]').forEach((closeBtn) => {
            closeBtn.addEventListener('click', () => modal.close());
        });
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            const inside = e.clientX >= rect.left && e.clientX <= rect.right &&
                           e.clientY >= rect.top && e.clientY <= rect.bottom;
            if (!inside) modal.close();
        });
    });
})();
</script>
<script src="{{ asset('js/buyer/wallet.js') }}" defer></script>
