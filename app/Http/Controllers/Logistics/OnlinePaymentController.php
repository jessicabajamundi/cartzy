<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\SellerOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OnlinePaymentController extends Controller
{
    private function orders(Request $request): Builder
    {
        $user = $request->user();
        abort_unless($user->role === 'logistics' && $user->status === 'active' && !$user->is_suspended, 403);
        $hub = $user->logisticsProvider;
        abort_unless($hub && $hub->status === 'approved', 403);

        return SellerOrder::whereHas('order', fn ($q) => $q->where('payment_method', '!=', 'cod'))
            ->where(function ($q) use ($hub) {
                $q->where('logistics_provider_id', $hub->id)
                    ->orWhereHas('shipment', fn ($s) => $s->where('logistics_provider_id', $hub->id)
                        ->orWhere('origin_hub_id', $hub->id)->orWhere('destination_hub_id', $hub->id))
                    ->orWhereHas('reports.returnCase', fn ($r) => $r->where('logistics_provider_id', $hub->id));
            });
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'payment_rejected', 'partially_refunded', 'refunded'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $orders = $this->orders($request)
            ->with(['seller', 'shipment', 'order.payments.verifier', 'reports.returnCase.refund.recorder',
                'reports.returnCase.events.actor'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->whereHas('order', fn ($o) => $o->where('payment_status', $status)))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when(trim($filters['search'] ?? ''), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('order', fn ($o) => $o->where('reference', 'like', '%'.$search.'%'))
                        ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('shipment', fn ($s) => $s->where('tracking_code', 'like', '%'.$search.'%'))
                        ->orWhereHas('order.payments', fn ($p) => $p->where('provider_ref', 'like', '%'.$search.'%'));
                });
            })
            ->latest('id')->paginate(15)->withQueryString();

        return view('logistics.online-payments', compact('orders', 'filters'));
    }

    public function receipt(Request $request, int $sellerOrder, int $payment)
    {
        $parcel = $this->orders($request)->findOrFail($sellerOrder);
        $record = $parcel->order->payments()->findOrFail($payment);
        $path = $record->receipt_path;
        abort_unless($path && str_starts_with($path, 'payment_receipts/') && !str_contains($path, '..'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }
}
