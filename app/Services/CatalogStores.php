<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class CatalogStores
{
    public function storeFor(string $productId): Seller
    {
        foreach (config('catalog-stores') as $slug => $store) {
            if (in_array($productId, array_map('strval', $store['products']), true)) {
                return $this->ensureStore($slug, $store['name']);
            }
        }

        throw new RuntimeException('This product is no longer available in the storefront catalog. Please remove it from your cart.');
    }

    public function ensureStore(string $slug, string $name): Seller
    {
        // Dedicated catalog accounts must never reuse a registered merchant's account.
        $user = User::firstOrCreate(['email' => $slug . '@catalog.cartzy.invalid'], [
            'name' => $name, 'role' => 'seller', 'status' => 'active',
            'password' => Str::random(64),
        ]);

        return Seller::firstOrCreate(['user_id' => $user->id], [
            'name' => $name, 'slug' => 'catalog-' . $slug, 'status' => 'approved',
            'description' => 'Catalog store for ' . $name . '.', 'commission_bps' => 0,
        ]);
    }
}
