<?php

namespace App\Services;

use App\Models\{Order, SellerOrder, User, WalletTransaction};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BuyerWallet
{
    public function balance(int $buyerId): int
    {
        return (int) WalletTransaction::where('user_id', $buyerId)->sum('amount_minor');
    }

    public function summary(User $buyer): array
    {
        abort_unless($buyer->isBuyer(), 403);
        $pending = \App\Models\ReturnCase::where('status', 'refund_pending')
            ->whereHas('dispute', fn ($q) => $q->where('reporter_id', $buyer->id))->sum('amount_minor');
        return [
            'balance_minor' => $this->balance($buyer->id), 'pending_refunds_minor' => (int) $pending,
            'transactions' => WalletTransaction::where('user_id', $buyer->id)->with('order')->latest('id')->limit(50)->get()
                ->map(fn ($entry) => ['id' => $entry->id, 'type' => $entry->type, 'amount_minor' => $entry->amount_minor,
                    'description' => $entry->description, 'order_reference' => $entry->order?->reference,
                    'created_at' => $entry->created_at->timezone('Asia/Manila')->format('M d, Y · h:i A')])->all(),
        ];
    }

    // Every mutation locks the buyer row, including the first credit. The unique
    // source key prevents retried refunds or checkout requests being applied twice.
    public function record(User $buyer, int $amount, string $key, string $type, string $description, ?Order $order = null, ?SellerOrder $parcel = null): WalletTransaction
    {
        return DB::transaction(function () use ($buyer, $amount, $key, $type, $description, $order, $parcel) {
            User::whereKey($buyer->id)->lockForUpdate()->firstOrFail();
            if ($existing = WalletTransaction::where('key', $key)->first()) return $existing;
            if (!$buyer->isBuyer() || $amount === 0 || $this->balance($buyer->id) + $amount < 0) {
                throw new RuntimeException('Your wallet balance is insufficient. Refresh checkout and try again.');
            }
            $entry = WalletTransaction::create(['user_id' => $buyer->id, 'amount_minor' => $amount,
                'key' => $key, 'type' => $type, 'description' => $description,
                'order_id' => $order?->id, 'seller_order_id' => $parcel?->id]);
            if ($amount > 0) NotificationService::notify($buyer->id, 'refund', 'Cartzy Wallet credited',
                'PHP '.number_format($amount / 100, 2).' added to your wallet. '.$description,
                route('buyer.dashboard', ['tab' => 'settings', 'section' => 'wallet']));
            return $entry;
        }, 3);
    }

    public function restoreCancelled(SellerOrder $parcel): void
    {
        DB::transaction(function () use ($parcel) {
            $parcel = SellerOrder::whereKey($parcel->id)->lockForUpdate()->firstOrFail();
            $amount = $parcel->wallet_paid_minor - $parcel->wallet_refunded_minor;
            if ($parcel->status !== 'cancelled' || $amount <= 0) return;
            $order = $parcel->order;
            $this->record($order->buyer, $amount, 'cancel:'.$parcel->id, 'cancellation_refund',
                'Wallet payment returned after cancellation', $order, $parcel);
            $parcel->update(['wallet_refunded_minor' => $parcel->wallet_paid_minor]);
        }, 3);
    }
}
