@php($wallet = app(\App\Services\BuyerWallet::class)->summary($user))
<section class="acct-card" id="acct-wallet" data-acct-section="wallet" data-wallet-url="{{ route('buyer.wallet') }}">
    <header class="acct-head"><div><p class="acct-eyebrow">Refund credit</p><h2 class="acct-title">Cartzy Wallet</h2><p>Use refunded credit for any product on Cartzy. No cash-out or top-ups.</p></div></header>
    <div class="wallet-body">
        <p>Available balance</p>
        <strong class="wallet-balance" data-wallet-balance>₱{{ number_format($wallet['balance_minor'] / 100, 2) }}</strong>
        <p data-wallet-pending>Pending approved refunds: ₱{{ number_format($wallet['pending_refunds_minor'] / 100, 2) }}. Pending amounts cannot be spent yet.</p>
        <h3 class="wallet-history-title">Recent transactions</h3>
        <p>Latest 50 entries · Philippine time</p>
        <div data-wallet-history>
            @forelse($wallet['transactions'] as $entry)
                <article class="wallet-entry">
                    <strong>{{ $entry['description'] }}</strong>
                    <span>{{ $entry['amount_minor'] > 0 ? '+' : '−' }}₱{{ number_format(abs($entry['amount_minor']) / 100, 2) }}</span>
                    <p>{{ $entry['order_reference'] }} · {{ $entry['created_at'] }}</p>
                </article>
            @empty
                <p>Your completed refunds and wallet payments will appear here.</p>
            @endforelse
        </div>
        <a href="{{ route('returns.index') }}">View pending refund requests</a>
    </div>
</section>
<script src="{{ asset('js/buyer/wallet.js') }}" defer></script>
