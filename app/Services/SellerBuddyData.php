<?php

namespace App\Services;

use App\Models\ProductInquiryMessage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerMessage;
use App\Models\SellerOrder;

/**
 * What Cozy may read about one seller's own shop. Read-only, and buyers' names,
 * addresses and phone numbers stay out: Cozy doesn't need them to help run a shop.
 */
class SellerBuddyData
{
    public function overview(Seller $shop): array
    {
        $orders = SellerOrder::where('seller_id', $shop->id);
        $products = $shop->products()->where('is_active', true);
        $paidSales = (clone $orders)->whereIn('status', ['delivered', 'completed'])
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
            ->where('delivered_at', '>=', now()->startOfMonth());
        $reviews = Review::whereHas('product', fn ($query) => $query->where('seller_id', $shop->id));
        $rating = (clone $reviews)->avg('rating');

        return [
            'shop_name' => $shop->name,
            'shop_status' => $shop->status,
            'orders_to_prepare' => (clone $orders)->whereIn('status', ['pending', 'accepted', 'packed'])->count(),
            'ready_for_pickup' => (clone $orders)->where('status', 'ready_to_ship')->count(),
            'in_delivery' => (clone $orders)->where('status', 'shipped')->count(),
            'cancellation_requests' => (clone $orders)->where('cancellation_status', 'pending')->count(),
            'unread_messages' => $this->unreadMessages($shop),
            'active_products' => (clone $products)->count(),
            'low_stock_products' => (clone $products)->whereHas('variants', fn ($query) => $query->where('is_active', true)->atStockAlertLevel())->count(),
            'out_of_stock_products' => (clone $products)->whereDoesntHave('variants', fn ($query) => $query->where('is_active', true)->where('stock', '>', 0))->count(),
            'sales_this_month' => (clone $paidSales)->sum('subtotal_minor') / 100,
            'average_rating' => $rating === null ? null : round((float) $rating, 1),
            'reviews' => (clone $reviews)->count(),
            'followers' => $shop->followers()->count(),
        ];
    }

    public function orders(Seller $shop, ?string $status = null): array
    {
        return SellerOrder::where('seller_id', $shop->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['order', 'items', 'shipment'])
            ->latest()->limit(10)->get()
            ->map(fn (SellerOrder $order) => [
                'reference' => $order->order->reference,
                'status' => $order->status,
                'delivery_status' => $order->shipment?->status,
                'tracking_code' => $order->shipment?->tracking_code,
                'date' => $order->created_at->timezone('Asia/Manila')->format('M j, Y g:i A'),
                'total' => $order->buyerDueMinor() / 100,
                'payment_method' => $order->order->payment_method,
                'payment_status' => $order->order->payment_status,
                'cancellation_requested' => $order->cancellation_status === 'pending',
                'cancellation_reason' => $order->cancellation_status === 'pending' ? $order->cancellation_reason : null,
                'items' => $order->items->map(fn ($item) => ['name' => $item->product_name, 'variation' => $item->variant_name, 'qty' => $item->quantity])->all(),
            ])->all();
    }

    public function lowStock(Seller $shop): array
    {
        return ProductVariant::query()
            ->where('is_active', true)
            ->atStockAlertLevel()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $shop->id)->where('is_active', true))
            ->with('product')
            ->orderBy('stock')->orderBy('id')->limit(15)->get()
            ->map(fn (ProductVariant $variant) => ['product' => $variant->product->name, 'variation' => $variant->name, 'stock' => $variant->stock])
            ->all();
    }

    private function unreadMessages(Seller $shop): int
    {
        return SellerMessage::whereIn('seller_order_id', SellerOrder::where('seller_id', $shop->id)->select('id'))
            ->where('sender_id', '!=', $shop->user_id)->whereNull('read_at')->count()
            + ProductInquiryMessage::whereHas('inquiry', fn ($query) => $query->where('seller_id', $shop->id))
                ->where('sender_id', '!=', $shop->user_id)->whereNull('read_at')->count();
    }
}
