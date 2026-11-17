<?php

namespace App\Services;

use App\Models\{Address, Seller, User};
use RuntimeException;

class CheckoutQuote
{
    public function build(User $buyer, array $items, Address $address): array
    {
        $stores = [];
        foreach ($items as $item) {
            $item['id'] = $item['product_id'] ?? null;
            $variant = app(ProductPricing::class)->variant($item);
            if (!$variant || !$variant->product->is_active || !$variant->product->isApproved()
                || !Seller::publiclyVisible()->whereKey($variant->product->seller_id)->exists()) {
                throw new RuntimeException('An item is no longer available. Refresh your cart.');
            }
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            if ($qty > $variant->stock) throw new RuntimeException('The available stock changed. Refresh your cart.');
            $id = $variant->product->seller_id;
            $stores[$id] = ($stores[$id] ?? 0) + $variant->sale_price_minor * $qty;
        }
        $shipping = collect(ShippingCalculator::forStores(array_keys($stores), $address))->map(fn ($q) => $q['fee_minor'])->all();
        $vouchers = app(VoucherService::class);
        $shopOptions = $vouchers->shopVouchersFor($buyer, array_keys($stores));
        $shop = $vouchers->lockShopSelection($buyer, null, $stores, $shipping, 'items', $shopOptions);
        $shopShipping = $vouchers->lockShopSelection($buyer, null, $stores, $shipping, 'shipping', $shopOptions);
        $shopDiscount = array_sum(array_column($shop, 'discount_minor'));
        $shopShippingDiscount = array_sum(array_column($shopShipping, 'discount_minor'));
        $options = $vouchers->options($buyer, array_sum($stores) - $shopDiscount, array_sum($shipping) - $shopShippingDiscount);
        $discount = ($vouchers->best($options, 'items')['discount_minor'] ?? 0)
            + ($vouchers->best($options, 'shipping')['discount_minor'] ?? 0) + $shopDiscount + $shopShippingDiscount;
        return ['subtotal_minor' => array_sum($stores), 'shipping_minor' => array_sum($shipping),
            'discount_minor' => $discount, 'total_minor' => max(0, array_sum($stores) + array_sum($shipping) - $discount),
            'wallet_balance_minor' => app(BuyerWallet::class)->balance($buyer->id)];
    }
}
