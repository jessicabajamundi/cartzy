<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Buyer\ProblemReportController;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminDispute;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Services\BuyerOrders;
use App\Services\OrderTracking;
use Illuminate\Http\Request;

/**
 * Buyer "My Orders" for the mobile app: the same cards, rules and actions as the website dashboard.
 */
class OrderController extends Controller
{
    public function __construct(private BuyerOrders $orders) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $sellerOrders = $this->orders->sellerOrders($user->id);
        $reviews = Review::where('user_id', $user->id)->get()->keyBy('order_item_id');

        $cards = $sellerOrders->map(function ($so) use ($reviews) {
            $card = $this->orders->card($so);
            $report = $card['report'];
            $card['report'] = $report ? [
                'id' => $report->id,
                'status' => $report->status,
                'subject' => $report->subject,
                'admin_notes' => $report->admin_notes,
            ] : null;
            $card['items'] = array_map(fn ($item) => $item + [
                'rating' => $reviews->get($item['id'])?->rating,
                'comment' => $reviews->get($item['id'])?->comment,
            ], $card['items']);

            return $card;
        })->values();

        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first();

        return response()->json([
            'orders' => $cards,
            'counts' => $cards->countBy('status'),
            'cancellation_reasons' => SellerOrder::CANCELLATION_REASONS,
            'report_categories' => collect(AdminDispute::CATEGORIES)
                ->map(fn ($c, $key) => ['key' => $key, 'label' => $c[0], 'needs_photos' => $c[1]])->values(),
            'report_resolutions' => collect(AdminDispute::RESOLUTIONS)
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'contact_phone' => $defaultAddress?->phone ?? $user->phone,
        ]);
    }

    public function tracking(Request $request, int $id)
    {
        $so = $this->orders->sellerOrders($request->user()->id)->firstWhere('id', $id);
        abort_unless($so, 404, 'Order not found.');

        return response()->json(['tracking' => app(OrderTracking::class)->build($so)]);
    }

    public function cancel(Request $request, int $id)
    {
        $message = $this->orders->requestCancellation($id, $request->user()->id, $request->all());

        return response()->json(['message' => $message]);
    }

    public function pay(Request $request, string $reference)
    {
        $order = $this->orders->pay($reference, $request->user()->id);

        return response()->json(['message' => 'Payment recorded for '.$order->reference.'.']);
    }

    public function report(Request $request, int $id)
    {
        $dispute = app(ProblemReportController::class)->createReport($request, $id);

        return response()->json([
            'message' => "Report sent (case #{$dispute->id}). Our team will review it and update you in Notifications.",
            'case_id' => $dispute->id,
        ]);
    }

    /** Save a review for an item from a received order. */
    public function review(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Please choose a star rating.',
        ]);

        $item = OrderItem::with('sellerOrder.order')->findOrFail($data['order_item_id']);
        abort_unless($item->sellerOrder->order->buyer_id === $request->user()->id, 403);

        if (!in_array($item->sellerOrder->status, ['delivered', 'completed'], true)) {
            return response()->json(['message' => 'You can only review items you have received.'], 422);
        }

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'order_item_id' => $item->id],
            ['product_id' => $item->product_id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return response()->json(['message' => 'Thanks! Your review has been saved.']);
    }
}
