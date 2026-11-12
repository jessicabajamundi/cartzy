<?php

namespace App\Services;

use App\Models\{AdminDispute, Order, RefundTransaction, ReturnCase, Rider, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnWorkflow
{
    public function open(AdminDispute $report): ?ReturnCase
    {
        if (!in_array($report->requested_resolution, ['refund', 'return_refund'], true)) return null;
        $case = ReturnCase::firstOrCreate(['admin_dispute_id' => $report->id]);
        if ($case->wasRecentlyCreated) {
            $case->events()->create(['user_id' => $report->reporter_id, 'status' => 'requested', 'notes' => 'Buyer requested seller review.']);
        }
        return $case;
    }

    public static function pesosToMinor(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    private function requireState(ReturnCase $case, array $states): void
    {
        if (!in_array($case->status, $states, true)) {
            throw ValidationException::withMessages(['action' => 'This case has changed or is not ready for that action. Refresh and check its status.']);
        }
    }

    private function paidOrder(ReturnCase $case, Order $order): void
    {
        $so = $case->dispute->sellerOrder;
        $eligible = in_array($order->payment_status, ['paid', 'partially_refunded'], true)
            && ($order->payment_method !== 'cod' || $so->shipment?->cod_collected);
        if (!$eligible || ($order->payment_status === 'partially_refunded' && !RefundTransaction::where('order_id', $order->id)->exists())) {
            throw ValidationException::withMessages(['amount' => 'No verified refundable payment is available for this parcel. Check payment and COD collection records first.']);
        }
    }

    private function checkAmount(ReturnCase $case, Order $order, int $amount): void
    {
        $so = $case->dispute->sellerOrder;
        // Approved cases reserve their amount; paid refunds remain counted. Locking the parent order
        // serializes approvals and payouts even when separate sellers share the same buyer payment.
        $reserved = ReturnCase::whereKeyNot($case->id)->whereNotIn('status', ['requested', 'rejected'])
            ->whereHas('dispute', fn ($q) => $q->where('order_id', $order->id));
        $orderRemaining = $order->total_minor - (clone $reserved)->sum('amount_minor');
        $storeRemaining = $so->buyerDueMinor() - (clone $reserved)
            ->whereHas('dispute', fn ($q) => $q->where('seller_order_id', $so->id))->sum('amount_minor');
        if ($amount <= 0 || $amount > min($orderRemaining, $storeRemaining)) {
            throw ValidationException::withMessages(['amount' => 'The refund exceeds the remaining amount paid for this store/order, including other approved refunds.']);
        }
    }

    public function act(User $actor, int $id, string $action, array $data): ReturnCase
    {
        abort_unless($actor->status === 'active' && !$actor->is_suspended, 403);
        $visible = ReturnCase::visibleTo($actor)->with('dispute')->findOrFail($id);

        return DB::transaction(function () use ($visible, $actor, $action, $data) {
            $order = Order::whereKey($visible->dispute->order_id)->lockForUpdate()->firstOrFail();
            $case = ReturnCase::visibleTo($actor)->lockForUpdate()->findOrFail($visible->id);
            $report = $case->dispute;
            $admin = $actor->isAdmin();
            $notes = trim($data['notes'] ?? '');

            switch ($action) {
                case 'approve':
                case 'reject':
                    abort_unless($admin || $actor->role === 'seller', 403);
                    $this->requireState($case, $admin ? ['requested', 'disputed'] : ['requested']);
                    if ($action === 'reject') {
                        $case->update(['status' => 'rejected', 'review_notes' => $notes]);
                        $report->update(['status' => 'resolved', 'admin_notes' => $notes, 'resolved_at' => now()]);
                        break;
                    }
                    $this->paidOrder($case, $order);
                    $amount = self::pesosToMinor($data['amount']);
                    $this->checkAmount($case, $order, $amount);
                    $needsReturn = $report->requested_resolution === 'return_refund' && !$case->received_at;
                    if ($needsReturn && empty(trim($data['return_address'] ?? ''))) {
                        throw ValidationException::withMessages(['return_address' => 'Provide the seller return address and handover instructions.']);
                    }
                    $case->update(['status' => $needsReturn ? 'awaiting_return' : 'refund_pending', 'amount_minor' => $amount,
                        'return_address' => $data['return_address'] ?? $case->return_address, 'review_notes' => $notes]);
                    $report->update(['status' => 'open', 'resolved_at' => null]);
                    break;
                case 'appeal':
                    abort_unless($actor->role === 'buyer', 403);
                    $this->requireState($case, ['rejected', 'inspection_failed']);
                    $case->update(['status' => 'disputed']);
                    $report->update(['status' => 'open', 'resolved_at' => null]);
                    break;
                case 'schedule':
                    abort_unless($actor->role === 'buyer', 403);
                    $this->requireState($case, ['awaiting_return']);
                    $case->update(['status' => 'scheduled', 'handover_method' => $data['handover_method'],
                        'scheduled_date' => $data['scheduled_date'], 'pickup_address' => $data['pickup_address'] ?? null,
                        'logistics_provider_id' => $data['logistics_provider_id'],
                        'tracking_code' => 'RET-'.str_pad((string) $case->id, 10, '0', STR_PAD_LEFT)]);
                    break;
                case 'dispatch':
                    abort_unless($admin || $actor->role === 'logistics', 403);
                    $this->requireState($case, ['scheduled']);
                    $rider = Rider::whereKey($data['rider_id'])->where('logistics_provider_id', $case->logistics_provider_id)
                        ->where('status', 'approved')->where('is_active', true)->where('online_until', '>', now())
                        ->whereHas('user', fn ($q) => $q->where('role', 'courier')->where('status', 'active')->where('is_suspended', false))
                        ->lockForUpdate()->first();
                    if (!$rider) throw ValidationException::withMessages(['rider_id' => 'Choose an online, active rider from the return hub.']);
                    $case->update(['rider_id' => $rider->id]);
                    break;
                case 'collect':
                    abort_unless($admin || $actor->role === 'logistics', 403);
                    $this->requireState($case, ['scheduled']);
                    if ($case->handover_method === 'pickup' && !$case->rider_id) {
                        throw ValidationException::withMessages(['rider_id' => 'Assign a pickup rider before confirming collection.']);
                    }
                    $case->update(['status' => 'in_transit']);
                    break;
                case 'receive':
                    abort_unless($admin || $actor->role === 'seller', 403);
                    $this->requireState($case, ['in_transit']);
                    $case->update(['status' => 'received', 'received_at' => now()]);
                    break;
                case 'inspect':
                    abort_unless($admin || $actor->role === 'seller', 403);
                    $this->requireState($case, ['received']);
                    $case->update(['status' => $data['result'] === 'accepted' ? 'refund_pending' : 'inspection_failed', 'inspection_notes' => $notes]);
                    break;
                case 'refund':
                    abort_unless($admin, 403);
                    $this->requireState($case, ['refund_pending']);
                    $this->paidOrder($case, $order);
                    $this->checkAmount($case, $order, $case->amount_minor);
                    RefundTransaction::create(['return_case_id' => $case->id, 'order_id' => $order->id,
                        'seller_order_id' => $report->seller_order_id, 'recorded_by' => $actor->id,
                        'amount_minor' => $case->amount_minor, 'method' => $data['method'],
                        'reference' => $data['reference'], 'proof_path' => $data['proof_path'], 'paid_at' => $data['paid_at']]);
                    $totalRefunded = RefundTransaction::where('order_id', $order->id)->sum('amount_minor');
                    $order->update(['payment_status' => $totalRefunded >= $order->total_minor ? 'refunded' : 'partially_refunded']);
                    $case->update(['status' => 'refunded']);
                    $report->update(['status' => 'resolved', 'resolved_at' => now(),
                        'admin_notes' => 'Refund payment recorded: PHP '.number_format($case->amount_minor / 100, 2).'. '.$notes]);
                    break;
                default:
                    abort(422, 'Unknown return action.');
            }

            $case->events()->create(['user_id' => $actor->id, 'status' => $case->status, 'notes' => ucfirst($action).': '.$notes]);
            $recipients = collect([$report->reporter_id, $report->sellerOrder->seller->user_id, $case->logisticsProvider?->user_id]);
            if (in_array($case->status, ['disputed', 'refund_pending'], true)) {
                $recipients = $recipients->merge(User::where('role', 'admin')->where('status', 'active')->pluck('id'));
            }
            foreach ($recipients->filter()->unique() as $userId) {
                NotificationService::notify($userId, 'return_update', 'Return / refund updated',
                    "Case #{$case->id}: ".str_replace('_', ' ', $case->status).'.', route('returns.index', ['case' => $case->id]));
            }
            return $case->fresh();
        }, 3);
    }
}
