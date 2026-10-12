<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SellerPortalController extends Controller
{
    private function shop(): ?Seller
    {
        return Auth::user()->seller;
    }

    private function ordersQuery()
    {
        return SellerOrder::where('seller_id', $this->shop()?->id ?? 0);
    }

    private function productsQuery()
    {
        return Product::where('seller_id', $this->shop()?->id ?? 0);
    }

    private function reviewsQuery()
    {
        return Review::whereHas('product', fn ($q) => $q->where('seller_id', $this->shop()?->id ?? 0));
    }

    private function page(string $name, array $data = [])
    {
        $shop = $this->shop();
        $stockAlertQuery = \App\Models\ProductVariant::query()
            ->where('is_active', true)->where('stock', '<=', 10)
            ->whereHas('product', fn ($query) => $query->where('seller_id', $shop?->id ?? 0)->where('is_active', true));

        return view('seller.'.$name, $data + [
            'shop' => $shop,
            'stockAlertCount' => (clone $stockAlertQuery)->count(),
            'stockAlerts' => $stockAlertQuery->with('product')->orderBy('stock')->orderBy('id')->limit(5)->get(),
        ]);
    }

    private function paidSales()
    {
        return $this->ordersQuery()->whereIn('status', ['delivered', 'completed'])->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));
    }

    public function dashboard(Request $request)
    {
        $request->validate(['period' => ['nullable', Rule::in(['7_days', '30_days', 'this_month'])]]);
        $period = $request->input('period', '7_days');
        $periodLabels = ['7_days' => 'Last 7 days', '30_days' => 'Last 30 days', 'this_month' => 'This month'];
        $periodLabel = $periodLabels[$period];
        $end = now()->endOfDay();
        $start = match ($period) {
            '30_days' => now()->subDays(29)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            default => now()->subDays(6)->startOfDay(),
        };
        $sales = $this->paidSales()->whereBetween('delivered_at', [$start, $end]);
        $stats = [
            'sales' => (int) (clone $sales)->sum('subtotal_minor'),
            'net' => (int) (clone $sales)->sum(DB::raw('subtotal_minor - commission_minor')),
            'orders' => $this->ordersQuery()->whereIn('status', ['pending', 'accepted', 'packed'])->count(),
            'products' => $this->productsQuery()->where('is_active', true)->count(),
            'low_stock' => $this->productsQuery()->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('stock', '<=', 10))->count(),
            'rating' => $this->reviewsQuery()->avg('rating'), 'reviews' => $this->reviewsQuery()->count(),
        ];
        $days = collect(\Carbon\CarbonPeriod::create($start, '1 day', $end));
        $daily = (clone $sales)->get()->groupBy(fn ($order) => $order->delivered_at->format('Y-m-d'));
        $chart = $days->map(fn ($day) => ['label' => $day->format($days->count() <= 7 ? 'D' : 'M j'), 'date' => $day->format('M j'), 'sales' => $daily->get($day->format('Y-m-d'), collect())->sum('subtotal_minor')]);
        $recentOrders = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment'])->latest()->limit(5)->get();
        $reportDates = ['from_date' => $start->format('Y-m-d'), 'to_date' => $end->format('Y-m-d')];
        $updatedAt = now()->timezone('Asia/Manila')->format('g:i A');

        return $this->page('dashboard', compact('stats', 'chart', 'recentOrders', 'period', 'periodLabels', 'periodLabel', 'reportDates', 'updatedAt'));
    }

    public function orders(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:150', 'status' => ['nullable', Rule::in(['all', 'cancellation_requested', 'to_prepare', 'pending', 'accepted', 'packed', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'])]]);
        $query = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment']);
        if ($request->status === 'cancellation_requested') {
            $query->where('cancellation_status', 'pending');
        } elseif ($request->status === 'to_prepare') {
            $query->whereIn('status', ['pending', 'accepted', 'packed']);
        } elseif ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('order', fn ($q) => $q->where('reference', 'like', '%'.$request->search.'%')->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', '%'.$request->search.'%')));
        }

        return $this->page('orders', ['orders' => $query->latest()->paginate(15)->withQueryString()]);
    }

    public function packOrder(Request $request, int $id)
    {
        DB::transaction(function () use ($id) {
            $order = $this->ordersQuery()->lockForUpdate()->findOrFail($id);
            abort_if($order->cancellation_status === 'pending', 422, 'Review the cancellation request before packing.');
            abort_unless(in_array($order->status, ['pending', 'accepted', 'packed']), 422, 'This order cannot be packed in its current status.');
            abort_if(in_array($order->order->payment_status, ['refunded', 'partially_refunded']), 422, 'A refunded order cannot be packed.');
            abort_if($order->order->payment_method !== 'cod' && $order->order->payment_status !== 'paid', 422, 'Payment must be received before packing.');
            $order->update(['status' => 'ready_to_ship']);
            $order->shipment?->deliveryEvents()->firstOrCreate(['status' => 'packed', 'attempt' => 1], [
                'user_id' => Auth::id(),
                'note' => 'The seller packed the parcel and marked it ready for pickup.',
                'occurred_at' => now(),
            ]);
        });

        return back()->with('success', 'Order packed and ready for pickup.');
    }

    public function printWaybill(int $id)
    {
        $order = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment.logisticsProvider', 'seller.pickupAddress'])->findOrFail($id);

        return $this->page('waybill', compact('order'));
    }

    public function reviewCancellation(Request $request, int $id)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])]]);
        DB::transaction(function () use ($id, $data) {
            $sellerOrder = $this->ordersQuery()->findOrFail($id);
            $order = $sellerOrder->order()->lockForUpdate()->firstOrFail();
            $sellerOrder = $this->ordersQuery()->lockForUpdate()->findOrFail($id);
            $sellerOrder->setRelation('shipment', $sellerOrder->shipment()->lockForUpdate()->first());
            abort_unless($sellerOrder->cancellation_status === 'pending', 422, 'There is no pending cancellation request.');
            if ($data['decision'] === 'approved') {
                abort_unless($sellerOrder->canBeCancelledByBuyer(), 422, 'This order has already been collected or completed. Cancellation cannot be approved.');
                $note = 'Cancellation reason: '.$sellerOrder->cancellation_reason;
                $sellerOrder->update([
                    'status' => 'cancelled',
                    'note' => trim(($sellerOrder->note ? $sellerOrder->note."\n" : '').$note),
                ]);
                if ($sellerOrder->shipment) {
                    $sellerOrder->shipment->update(['cod_amount_minor' => 0]);
                    $sellerOrder->shipment->deliveryEvents()->create([
                        'status' => 'cancelled', 'attempt' => 1, 'user_id' => Auth::id(),
                        'note' => 'Cancellation approved by seller. '.$note, 'occurred_at' => now(),
                    ]);
                }
                if (!$order->sellerOrders()->where('status', '!=', 'cancelled')->exists()) {
                    $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);
                } elseif ($order->payment_status === 'pending') {
                    $remaining = $order->sellerOrders()->where('status', '!=', 'cancelled')->get()
                        ->sum(fn ($row) => $row->subtotal_minor + $row->shipping_fee_minor);
                    $order->payments()->where('status', 'pending')->update(['amount_minor' => $remaining]);
                }
            }
            $sellerOrder->update(['cancellation_status' => $data['decision'], 'cancellation_reviewed_at' => now()]);
            $message = $data['decision'] === 'approved'
                ? "Your cancellation for {$order->reference} was approved.".($order->payment_status === 'paid' ? ' Contact the seller to arrange your refund.' : '')
                : "Your cancellation for {$order->reference} was declined. Your order remains active.";
            \App\Services\NotificationService::notify($order->buyer_id, 'cancellation_'.$data['decision'], 'Cancellation '.$data['decision'], $message, route('buyer.dashboard', ['tab' => 'orders']));
        });
        return back()->with('success', 'Cancellation request '.$data['decision'].'.');
    }

    public function courier()
    {
        return $this->page('courier', [
            'orders' => $this->ordersQuery()->whereIn('status', ['ready_to_ship', 'shipped'])->with(['order.buyer', 'shipment.logisticsProvider', 'shipment.rider.user'])->latest()->paginate(15),
            'providers' => LogisticsProvider::where('status', 'approved')->orderBy('name')->get(),
        ]);
    }

    public function schedulePickup(Request $request, int $id)
    {
        $data = $request->validate(['logistics_provider_id' => ['required', 'integer', Rule::exists('logistics_providers', 'id')->where('status', 'approved')], 'pickup_date' => 'required|date_format:Y-m-d|after_or_equal:today', 'notes' => 'nullable|string|max:1000']);
        DB::transaction(function () use ($id, $data) {
            $order = $this->ordersQuery()->lockForUpdate()->findOrFail($id);
            abort_unless($order->status === 'ready_to_ship', 422, 'Pack the order before requesting pickup.');
            abort_if($order->cancellation_status === 'pending', 422, 'Review the cancellation request before scheduling pickup.');
            $shipment = $order->shipment;
            abort_if($shipment && $shipment->status !== 'unassigned', 422, 'This shipment has already been assigned to a courier.');
            $order->update(['logistics_provider_id' => $data['logistics_provider_id'], 'note' => 'Requested pickup: '.$data['pickup_date'].(! empty($data['notes']) ? "\n".$data['notes'] : '')]);
            $shipment = Shipment::updateOrCreate(['seller_order_id' => $order->id], [
                'logistics_provider_id' => $data['logistics_provider_id'], 'tracking_code' => $shipment?->tracking_code ?? 'CTZ-'.Str::upper(Str::random(12)),
                'status' => 'unassigned', 'fee_minor' => $order->shipping_fee_minor,
                'cod_amount_minor' => $shipment?->cod_amount_minor ?? ($order->order->payment_method === 'cod' ? $order->subtotal_minor + $order->shipping_fee_minor : 0),
            ]);
            $shipment->deliveryEvents()->create([
                'status' => 'pickup_requested', 'user_id' => Auth::id(),
                'attempt' => ((int) $shipment->deliveryEvents()->where('status', 'pickup_requested')->max('attempt')) + 1,
                'note' => 'Seller requested pickup on '.$data['pickup_date'].'. Awaiting courier confirmation.',
                'occurred_at' => now(),
            ]);
        });

        return back()->with('success', 'Pickup request saved. Awaiting courier assignment and collection.');
    }

    public function deliveries()
    {
        return $this->page('deliveries', ['orders' => $this->ordersQuery()->whereIn('status', ['delivered', 'completed'])->with(['order.buyer', 'shipment'])->latest('delivered_at')->paginate(15)]);
    }

    public function feedback(Request $request)
    {
        $request->validate(['rating' => 'nullable|integer|between:1,5']);
        $query = $this->reviewsQuery()->with(['user', 'product']);
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        return $this->page('feedback', ['reviews' => $query->latest()->paginate(12)->withQueryString(), 'rating' => $this->reviewsQuery()->avg('rating'), 'total' => $this->reviewsQuery()->count()]);
    }

    public function replyFeedback(Request $request, int $id)
    {
        $data = $request->validate(['reply' => 'required|string|max:2000']);
        $this->reviewsQuery()->findOrFail($id)->forceFill(['seller_reply' => $data['reply'], 'replied_at' => now()])->save();

        return back()->with('success', 'Your review response has been saved.');
    }

    public function inventory(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:150', 'tab' => ['nullable', Rule::in(['all', 'active', 'low_stock', 'out_of_stock', 'archived'])]]);
        $base = $this->productsQuery();
        $stockStats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'low_stock' => (clone $base)->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('stock', '>', 0)->where('stock', '<=', 10))->count(),
            'out_of_stock' => (clone $base)->where('is_active', true)->where(function ($q) {
                $q->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
            })->count(),
            'archived' => (clone $base)->where('is_active', false)->count(),
            'total_units' => (int) \App\Models\ProductVariant::whereHas('product', fn ($q) => $q->where('seller_id', $this->shop()?->id ?? 0)->where('is_active', true))->where('is_active', true)->sum('stock'),
        ];

        $query = $this->productsQuery()->with(['variants', 'category', 'coverImage']);
        if ($request->tab === 'active' || $request->tab === 'archived') {
            $query->where('is_active', $request->tab === 'active');
        }
        if ($request->tab === 'low_stock') {
            $query->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('stock', '>', 0)->where('stock', '<=', 10));
        }
        if ($request->tab === 'out_of_stock') {
            $query->where('is_active', true)->where(function ($q) {
                $q->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
            });
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$request->search.'%')));
        }

        return $this->page($request->routeIs('seller.products') ? 'products' : 'inventory', [
            'products' => $query->latest()->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'stockStats' => $stockStats,
        ]);
    }

    public function addProduct(Request $request)
    {
        $user = Auth::user();
        $shop = $this->shop();
        abort_unless($shop, 422, 'Save your store profile first.');

        if ($user->status !== 'active' || !$shop->isApproved()) {
            return back()->withErrors([
                'name' => 'Your seller account is currently pending administrator approval. You cannot sell or add products until approved by the admin.',
            ]);
        }

        $hasVariants = $request->boolean('has_variants') && $request->has('variants') && is_array($request->input('variants'));

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
        ];

        if ($hasVariants) {
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.name'] = 'required|string|max:255';
            $rules['variants.*.sku'] = 'required|string|max:100|distinct|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0.01|max:99999999.99';
            $rules['variants.*.stock'] = 'required|integer|min:0|max:1000000';
            $rules['variants.*.color'] = 'nullable|string|max:50';
            $rules['variants.*.image'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240';
        } else {
            $rules['sku'] = 'required|string|max:100|unique:product_variants,sku';
            $rules['price'] = 'required|numeric|min:0.01|max:99999999.99';
            $rules['stock'] = 'required|integer|min:0|max:1000000';
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $shop, $request, $hasVariants) {
            $product = $shop->products()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'],
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(10)),
                'is_active' => true
            ]);

            $firstVariantImage = null;

            if ($hasVariants && !empty($data['variants'])) {
                foreach ($data['variants'] as $idx => $v) {
                    $variantImagePath = null;
                    if ($request->hasFile("variants.{$idx}.image") && $request->file("variants.{$idx}.image")->isValid()) {
                        $variantImagePath = $request->file("variants.{$idx}.image")->store('products/variants', 'public');
                        if (!$firstVariantImage) {
                            $firstVariantImage = $variantImagePath;
                        }
                    }

                    $options = !empty($v['color']) ? ['color' => $v['color']] : null;

                    $product->variants()->create([
                        'name' => $v['name'],
                        'sku' => $v['sku'],
                        'price_minor' => (int) round($v['price'] * 100),
                        'stock' => (int) $v['stock'],
                        'image_path' => $variantImagePath,
                        'options' => $options,
                        'is_active' => true,
                    ]);
                }
            } else {
                $product->variants()->create([
                    'sku' => $data['sku'],
                    'name' => 'Standard',
                    'price_minor' => (int) round($data['price'] * 100),
                    'stock' => (int) $data['stock'],
                    'is_active' => true,
                ]);
            }

            $position = 0;

            if ($request->hasFile('image')) {
                $product->images()->create([
                    'path' => $request->file('image')->store('products', 'public'),
                    'position' => $position++,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imgFile) {
                    if ($position >= 10) break;
                    if ($imgFile->isValid()) {
                        $product->images()->create([
                            'path' => $imgFile->store('products', 'public'),
                            'position' => $position++,
                        ]);
                    }
                }
            }

            if ($position === 0 && $firstVariantImage) {
                $product->images()->create(['path' => $firstVariantImage, 'position' => 0]);
            }
            \App\Services\NotificationService::notify(Auth::id(), 'product_update', 'Product added', $product->name.' was added to your catalog.', route('seller.products', ['search' => $product->name]));
        });

        return back()->with('success', 'Product added to your inventory.');
    }

    public function updateProduct(Request $request, int $id)
    {
        $user = Auth::user();
        $shop = $this->shop();
        if ($user->status !== 'active' || !$shop?->isApproved()) {
            return back()->withErrors([
                'name' => 'Your seller account is currently pending administrator approval. Product changes are locked until approved.',
            ]);
        }

        $product = $this->productsQuery()->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|integer|distinct',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'required|numeric|min:0.01|max:99999999.99',
            'variants.*.stock' => $request->routeIs('seller.products.update') ? 'exclude' : 'required|integer|min:0|max:1000000',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'new_variants' => 'nullable|array',
            'new_variants.*.name' => 'required_with:new_variants|string|max:255',
            'new_variants.*.sku' => 'required_with:new_variants|string|max:100|distinct|unique:product_variants,sku',
            'new_variants.*.price' => 'required_with:new_variants|numeric|min:0.01|max:99999999.99',
            'new_variants.*.stock' => 'required_with:new_variants|integer|min:0|max:1000000',
            'new_variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);
        DB::transaction(function () use ($product, $data, $request) {
            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
            ]);
            foreach ($data['variants'] as $idx => $variant) {
                $v = $product->variants()->lockForUpdate()->findOrFail($variant['id']);
                $updateData = [
                    'price_minor' => (int) round($variant['price'] * 100),
                ];
                if (array_key_exists('stock', $variant)) $updateData['stock'] = (int) $variant['stock'];
                if (!empty($variant['name'])) {
                    $updateData['name'] = $variant['name'];
                }
                if ($request->hasFile("variants.{$idx}.image") && $request->file("variants.{$idx}.image")->isValid()) {
                    $updateData['image_path'] = $request->file("variants.{$idx}.image")->store('products/variants', 'public');
                }
                $v->update($updateData);
            }

            if (!empty($data['new_variants'])) {
                foreach ($data['new_variants'] as $nIdx => $newV) {
                    if (empty($newV['name']) || empty($newV['sku'])) continue;
                    $newImgPath = null;
                    if ($request->hasFile("new_variants.{$nIdx}.image") && $request->file("new_variants.{$nIdx}.image")->isValid()) {
                        $newImgPath = $request->file("new_variants.{$nIdx}.image")->store('products/variants', 'public');
                    }
                    $product->variants()->create([
                        'name' => $newV['name'],
                        'sku' => $newV['sku'],
                        'price_minor' => (int) round($newV['price'] * 100),
                        'stock' => (int) $newV['stock'],
                        'image_path' => $newImgPath,
                        'is_active' => true,
                    ]);
                }
            }

            if ($request->hasFile('images')) {
                $currPos = (int) $product->images()->max('position') + 1;
                $currCount = $product->images()->count();
                foreach ($request->file('images') as $imgFile) {
                    if ($currCount >= 10) break;
                    if ($imgFile->isValid()) {
                        $product->images()->create([
                            'path' => $imgFile->store('products', 'public'),
                            'position' => $currPos++,
                        ]);
                        $currCount++;
                    }
                }
            }
        });

        \App\Services\NotificationService::notify(Auth::id(), 'product_update', 'Product updated', $product->name.' details or variations were updated.', route('seller.products', ['search' => $product->name]));

        return back()->with('success', 'Product changes saved.');
    }

    public function updateStock(Request $request, int $id)
    {
        abort_unless(Auth::user()->status === 'active' && $this->shop()?->isApproved(), 403, 'An approved store is required to update stock.');
        $product = $this->productsQuery()->findOrFail($id);
        $data = $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|integer|distinct',
            'variants.*.stock' => 'required|integer|min:0|max:1000000',
            'variants.*.previous_stock' => 'required|integer|min:0',
        ]);
        DB::transaction(function () use ($product, $data) {
            foreach (collect($data['variants'])->sortBy('id') as $row) {
                $variant = $product->variants()->lockForUpdate()->findOrFail($row['id']);
                if ((int) $variant->stock !== (int) $row['previous_stock']) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['stock' => 'Stock changed since this page was opened. Refresh the page and try again.']);
                }
                $variant->update(['stock' => $row['stock']]);
            }
        });
        \App\Services\NotificationService::notify(Auth::id(), 'product_update', 'Stock updated', $product->name.' stock quantities were updated.', route('seller.inventory', ['search' => $product->name]));
        return back()->with('success', 'Stock quantities updated.');
    }

    public function toggleArchiveProduct(int $id)
    {
        $user = Auth::user();
        $shop = $this->shop();
        if ($user->status !== 'active' || !$shop?->isApproved()) {
            return back()->withErrors([
                'name' => 'Your seller account is currently pending administrator approval.',
            ]);
        }

        DB::transaction(function () use ($id) {
            $product = $this->productsQuery()->lockForUpdate()->findOrFail($id);
            $product->update(['is_active' => ! $product->is_active]);
            \App\Services\NotificationService::notify(Auth::id(), 'product_update', 'Product visibility updated', $product->name.($product->is_active ? ' is now active.' : ' was archived.'), route('seller.products', ['tab' => $product->is_active ? 'active' : 'archived', 'search' => $product->name]));
        });

        return back()->with('success', 'Product visibility updated.');
    }

    public function reports(Request $request)
    {
        $data = $request->validate(['from_date' => 'nullable|date_format:Y-m-d', 'to_date' => 'nullable|date_format:Y-m-d|after_or_equal:from_date', 'export' => 'nullable|in:csv']);
        $from = $data['from_date'] ?? now()->subDays(29)->format('Y-m-d');
        $to = $data['to_date'] ?? now()->format('Y-m-d');
        abort_if($to < $from, 422, 'End date must be on or after the start date.');
        $query = $this->paidSales()->whereDate('delivered_at', '>=', $from)->whereDate('delivered_at', '<=', $to)->with('order');
        $summary = ['gross' => (int) (clone $query)->sum('subtotal_minor'), 'commission' => (int) (clone $query)->sum('commission_minor'), 'count' => (clone $query)->count()];
        if ($request->export === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Order', 'Delivered', 'Gross (PHP)', 'Commission (PHP)', 'Net sales (PHP)']);
                foreach ($query->lazyById(200) as $order) {
                    $reference = preg_match('/^[=+@\-\t\r]/', $order->order->reference) ? "'".$order->order->reference : $order->order->reference;
                    fputcsv($file, [$reference, $order->delivered_at->format('Y-m-d'), $order->subtotal_minor / 100, $order->commission_minor / 100, ($order->subtotal_minor - $order->commission_minor) / 100]);
                }
                fclose($file);
            }, 'seller-sales-'.$from.'-'.$to.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return $this->page('reports', ['orders' => $query->latest('delivered_at')->paginate(20)->withQueryString(), 'summary' => $summary, 'from' => $from, 'to' => $to]);
    }

    public function account()
    {
        return $this->page('account');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'store_name'       => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'phone'            => ['nullable', 'string', 'regex:/\A[0-9]+\z/', 'max:11'],
            'line_of_business' => 'nullable|string|max:150',
            'name'             => 'nullable|string|max:255',
            'first_name'       => 'nullable|string|max:120',
            'last_name'        => 'nullable|string|max:120',
            'middle_initial'   => 'nullable|string|max:50',
            'sex'              => 'nullable|string|in:Male,Female,Prefer not to say',
            'birthday'         => ['nullable', 'bail', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYearsNoOverflow(18)->toDateString()],
            'id_type'          => 'nullable|string|max:100',
            'id_number'        => 'nullable|string|max:100',
            'id_photo'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'dti_permit'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'birthday.date_format' => 'Please enter a valid birthday.',
            'birthday.before_or_equal' => 'You must be at least 18 years old.',
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = Auth::user();
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $shop = $user->seller()->first();

            // Auto-calculate age if birthday updated
            $age = $user->age;
            if (!empty($data['birthday'])) {
                $age = max(0, (int) \Carbon\Carbon::parse($data['birthday'])->age);
            }

            // Name combination: preserve exactly as typed (including ñ/Ñ and multibyte characters)
            $fullName = $user->name;
            if (!empty($data['name'])) {
                $fullName = trim($data['name']);
            } elseif (!empty($data['first_name']) || !empty($data['last_name'])) {
                $combined = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
                if ($combined) {
                    $fullName = $combined;
                }
            }

            $userUpdate = [
                'name'             => $fullName,
                'middle_initial'   => array_key_exists('middle_initial', $data) ? $data['middle_initial'] : $user->middle_initial,
                'sex'              => array_key_exists('sex', $data) ? $data['sex'] : $user->sex,
                'birthday'         => array_key_exists('birthday', $data) ? $data['birthday'] : $user->birthday,
                'age'              => $age,
                'phone'            => $data['phone'] ?? $user->phone,
                'business_name'    => $data['store_name'],
                'line_of_business' => $data['line_of_business'] ?? $user->line_of_business,
            ];

            if (!empty($data['id_type'])) {
                $userUpdate['id_type'] = $data['id_type'];
            }
            if (isset($data['id_number'])) {
                $userUpdate['id_number'] = $data['id_number'];
            }

            if ($request->hasFile('id_photo') && $request->file('id_photo')->isValid()) {
                $userUpdate['id_photo'] = $request->file('id_photo')->store('id_documents', 'public');
                $userUpdate['id_status'] = 'pending';
                $userUpdate['id_rejection_reason'] = null;
            }

            if ($request->hasFile('dti_permit') && $request->file('dti_permit')->isValid()) {
                $userUpdate['dti_permit'] = $request->file('dti_permit')->store('dti_permits', 'public');
            }

            $user->update($userUpdate);

            if ($shop) {
                $shop->update([
                    'name'        => $data['store_name'],
                    'description' => $data['description'] ?? null,
                ]);
            } else {
                $user->seller()->create([
                    'name'        => $data['store_name'],
                    'description' => $data['description'] ?? null,
                    'slug'        => Str::slug($data['store_name']) . '-' . Str::lower(Str::random(10)),
                    'status'      => $user->status === 'active' && $user->isIdVerified() ? 'approved' : 'pending',
                ]);
            }
        });

        \App\Console\Commands\ExportDatabaseSql::exportSqlFile();

        return back()->with('success', 'Store profile and account details saved.');
    }

    public function updateAddress(Request $request)
    {
        $shop = $this->shop();
        abort_unless($shop, 422, 'Save your store profile first.');
        $data = $request->validate([
            'recipient'   => 'required|string|max:255',
            'phone'       => ['required', 'string', 'regex:/\A[0-9]+\z/', 'max:11'],
            'line1'       => 'required|string|max:500',
            'barangay'    => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'province'    => 'required|string|max:255',
            'postal_code' => ['required', 'string', 'regex:/\A[0-9]{4}\z/'],
            'region'      => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($shop, $data) {
            $user = Auth::user();
            $address = $user->addresses()->updateOrCreate(
                ['id' => $shop->pickup_address_id],
                [
                    'recipient'   => $data['recipient'],
                    'phone'       => $data['phone'],
                    'line1'       => $data['line1'],
                    'barangay'    => $data['barangay'],
                    'city'        => $data['city'],
                    'province'    => $data['province'],
                    'postal_code' => $data['postal_code'],
                    'label'       => 'Shop pickup',
                ]
            );
            $shop->update(['pickup_address_id' => $address->id]);

            $user->update([
                'street_address' => $data['line1'],
                'region'         => $data['region'] ?? $user->region,
                'province'       => $data['province'],
                'city'           => $data['city'],
                'barangay'       => $data['barangay'],
                'postal_code'    => $data['postal_code'],
                'address'        => $address->formatted_address,
            ]);
        });

        \App\Console\Commands\ExportDatabaseSql::exportSqlFile();

        return back()->with('success', 'Pickup address saved.');
    }
}
