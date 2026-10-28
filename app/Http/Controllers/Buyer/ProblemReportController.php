<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\{Address, AdminDispute, SellerOrder};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Buyer "Report a problem" on one store's part of an order. Reports become cases on the admin Disputes page.
 */
class ProblemReportController extends Controller
{
    private function sellerOrder(int $id): SellerOrder
    {
        return SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', Auth::id()))
            ->with(['order', 'seller', 'items.product.coverImage'])->findOrFail($id);
    }

    public function create(int $id)
    {
        $sellerOrder = $this->sellerOrder($id);
        if ($open = $sellerOrder->openReport()) {
            return redirect()->route('buyer.dashboard', ['tab' => 'orders'])
                ->with('success', "You already reported a problem on this order (case #{$open->id}). Our team is reviewing it.");
        }
        abort_unless($sellerOrder->canBeReported(), 403, 'This order can no longer be reported.');
        $defaultAddress = Address::where('user_id', Auth::id())->where('is_default', true)->first();

        return view('buyer.report-problem', [
            'sellerOrder' => $sellerOrder,
            'phone' => $defaultAddress?->phone ?? Auth::user()->phone,
        ]);
    }

    public function store(Request $request, int $id)
    {
        $dispute = $this->createReport($request, $id);

        return redirect()->route('buyer.dashboard', ['tab' => 'orders'])
            ->with('success', "Report sent (case #{$dispute->id}). Our team will review it and update you in Notifications.");
    }

    /** Validates and opens the case; shared with the mobile API. */
    public function createReport(Request $request, int $id): AdminDispute
    {
        $sellerOrder = $this->sellerOrder($id);
        abort_unless($sellerOrder->canBeReported(), 403, 'This order can no longer be reported.');
        $needsPhotos = AdminDispute::CATEGORIES[$request->input('category')][1] ?? false;

        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(AdminDispute::CATEGORIES))],
            'items' => ['nullable', 'array'],
            'items.*' => ['integer', Rule::in($sellerOrder->items->pluck('id')->all())],
            'requested_resolution' => ['required', Rule::in(array_keys(AdminDispute::RESOLUTIONS))],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'photos' => [$needsPhotos ? 'required' : 'nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'contact_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
        ], [
            'category.required' => 'Choose what went wrong.',
            'photos.required' => 'Please add at least one photo so we can check the problem.',
            'photos.max' => 'You can add up to 4 photos.',
            'photos.*.max' => 'Each photo must be 5 MB or smaller.',
            'description.min' => 'Please describe the problem in at least 20 characters.',
        ]);

        return DB::transaction(function () use ($sellerOrder, $data, $request) {
            // Re-check under lock so double submits cannot open two cases.
            SellerOrder::whereKey($sellerOrder->id)->lockForUpdate()->first();
            if ($sellerOrder->openReport()) {
                throw ValidationException::withMessages(['category' => 'You already have an open report for this order.']);
            }
            $items = $sellerOrder->items->whereIn('id', $data['items'] ?? [])
                ->map(fn ($item) => ['name' => $item->product_name, 'variation' => $item->variant_name, 'qty' => $item->quantity])->values()->all();
            $dispute = AdminDispute::create([
                'order_id' => $sellerOrder->order_id,
                'seller_order_id' => $sellerOrder->id,
                'reporter_id' => Auth::id(),
                'category' => $data['category'],
                'requested_resolution' => $data['requested_resolution'],
                'subject' => AdminDispute::CATEGORIES[$data['category']][0],
                'description' => $data['description'],
                'items' => $items,
                'contact_phone' => $data['contact_phone'],
                'status' => 'open',
            ]);
            $dispute->update(['evidence' => collect($request->file('photos', []))
                ->map(fn ($photo) => $photo->store('dispute-evidence/'.$dispute->id, 'local'))->all()]);

            $reference = $sellerOrder->order->reference;
            if ($sellerOrder->seller?->user_id) {
                NotificationService::notify($sellerOrder->seller->user_id, 'dispute', 'Buyer reported a problem',
                    "A buyer reported \"{$dispute->subject}\" on order {$reference}. Cartzy will review it and may contact you.",
                    route('seller.orders'));
            }
            NotificationService::notify(Auth::id(), 'dispute', 'Problem report received',
                "We received your report for order {$reference} (case #{$dispute->id}). We'll update you here once it's reviewed.",
                route('buyer.dashboard', ['tab' => 'orders']));

            return $dispute;
        });
    }

    /** Photo proof is private: only admins and the buyer who reported it can see it. */
    public function evidence(AdminDispute $dispute, int $index)
    {
        abort_unless(Auth::user()->isAdmin() || $dispute->reporter_id === Auth::id(), 403);
        $path = $dispute->evidence[$index] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
