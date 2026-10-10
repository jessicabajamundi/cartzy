<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SellerOrder;
use App\Services\CatalogStores;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCatalogStores extends Command
{
    protected $signature = 'catalog:sync-stores';
    protected $description = 'Create catalog stores and repair unfulfilled, unpaid catalog orders with one destination store';

    public function handle(CatalogStores $catalog): int
    {
        $result = DB::transaction(function () use ($catalog) {
            $owners = [];
            foreach (config('catalog-stores') as $slug => $definition) {
                $store = $catalog->ensureStore($slug, $definition['name']);
                foreach ($definition['products'] as $id) {
                    $owners['catalog-' . $id] = $store;
                    Product::where('slug', 'catalog-' . $id)->update(['seller_id' => $store->id]);
                }
            }

            $updated = 0;
            $skipped = 0;
            foreach (SellerOrder::with(['items.product', 'order', 'shipment'])->lockForUpdate()->get() as $order) {
                $stores = $order->items->map(fn ($item) => $owners[$item->product?->slug] ?? null);
                if ($stores->isEmpty() || $stores->contains(null)) {
                    continue;
                }
                $ids = $stores->pluck('id')->unique();
                if ($ids->count() === 1 && $order->seller_id === $ids->first()) {
                    continue;
                }
                if ($ids->count() !== 1 || !in_array($order->status, ['pending', 'cancelled'], true)
                    || $order->order->payment_status !== 'pending'
                    || ($order->shipment && ($order->shipment->status !== 'unassigned' || $order->shipment->cod_collected))
                    || $order->messages()->exists()
                    || SellerOrder::where('order_id', $order->order_id)->where('seller_id', $ids->first())->whereKeyNot($order->id)->exists()) {
                    $skipped++;
                    continue;
                }
                $store = $stores->first();
                $order->update(['seller_id' => $store->id, 'commission_minor' => (int) round($order->subtotal_minor * $store->commission_bps / 10000)]);
                $updated++;
            }

            return compact('updated', 'skipped');
        });

        $this->info('Catalog stores synced. Orders corrected: ' . $result['updated'] . '. Orders needing manual review: ' . $result['skipped'] . '.');

        return self::SUCCESS;
    }
}
