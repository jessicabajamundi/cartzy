<?php

namespace App\Services;

use App\Models\ProductVariant;
use RuntimeException;

class ProductPricing
{
    public function variant(array $item): ?ProductVariant
    {
        $query = ProductVariant::with('product.seller.user');
        if (empty($item['variant_id']) && preg_match('/^variant_(\d+)$/', (string) ($item['id'] ?? ''), $match)) {
            $item['variant_id'] = (int) $match[1];
        }
        if (!empty($item['variant_id'])) {
            $variant = $query->find($item['variant_id']);
            if (!$variant) throw new RuntimeException('This product variation is no longer available.');
        } else {
            $variant = (clone $query)->where('sku', 'CAT-'.($item['id'] ?? '').'-'.substr(md5($item['variation'] ?? 'Standard'), 0, 6))->first();
            if (!$variant && !empty($item['id']) && is_numeric($item['id'])) {
                $variant = (clone $query)->where('product_id', (int) $item['id'])
                    ->where('is_active', true)
                    ->when(!empty($item['variation']), fn ($q) => $q->where('name', $item['variation']))
                    ->first()
                    ?: (clone $query)->where('product_id', (int) $item['id'])->where('is_active', true)->first();
            }
        }
        if ($variant && (!$variant->is_active || !$variant->product?->is_active || !$variant->product->seller?->isApproved()
            || $variant->product->seller->user?->status !== 'active')) {
            throw new RuntimeException('This product is no longer available. Please remove it from your cart.');
        }
        return $variant;
    }
}
