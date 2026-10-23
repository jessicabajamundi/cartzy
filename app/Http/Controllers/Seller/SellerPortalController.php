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
            ->where('is_active', true)
            ->atStockAlertLevel()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $shop?->id ?? 0)->where('is_active', true));

        $stockAlertCount = (clone $stockAlertQuery)->count();

        return view('seller.'.$name, $data + [
            'shop' => $shop,
            'stockAlertCount' => $stockAlertCount,
            'stockOutCount' => (clone $stockAlertQuery)->where('stock', '<=', 0)->count(),
            'stockAlerts' => $stockAlertQuery->with('product.coverImage')->orderBy('stock')->orderBy('id')->limit(5)->get(),
            // Set at login and used up here, so the reminder opens by itself only once per sign-in.
            'restockPrompt' => (bool) session()->pull('seller_restock_prompt') && $stockAlertCount > 0,
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
            'net' => (int) (clone $sales)->sum(DB::raw('subtotal_minor - commission_minor - seller_voucher_discount_minor - seller_shipping_discount_minor')),
            'orders' => $this->ordersQuery()->whereIn('status', ['pending', 'accepted', 'packed'])->count(),
            'products' => $this->productsQuery()->where('is_active', true)->count(),
            'low_stock' => $this->productsQuery()->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->atStockAlertLevel())->count(),
            'rating' => $this->reviewsQuery()->avg('rating'), 'reviews' => $this->reviewsQuery()->count(),
            'cancellation_requests' => $this->ordersQuery()->where('cancellation_status', 'pending')->count(),
            'ready_for_pickup' => $this->ordersQuery()->where('status', 'ready_to_ship')->count(),
            'unread_messages' => \App\Models\SellerMessage::whereIn('seller_order_id', $this->ordersQuery()->select('id'))
                ->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->count()
                + \App\Models\ProductInquiryMessage::whereHas('inquiry', fn ($q) => $q->where('seller_id', $request->user()->seller?->id ?? 0))
                    ->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->count(),
        ];
        $days = collect(\Carbon\CarbonPeriod::create($start, '1 day', $end));
        $daily = (clone $sales)->get()->groupBy(fn ($order) => $order->delivered_at->format('Y-m-d'));
        $chart = $days->map(fn ($day) => ['label' => $day->format($days->count() <= 7 ? 'D' : 'M j'), 'date' => $day->format('M j'), 'sales' => $daily->get($day->format('Y-m-d'), collect())->sum('subtotal_minor')]);
        $recentOrders = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment'])->latest()->limit(5)->get();
        $reportDates = ['from_date' => $start->format('Y-m-d'), 'to_date' => $end->format('Y-m-d')];
        $updatedAt = now()->timezone('Asia/Manila')->format('M j, Y \a\t g:i A');

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

            $shipment = $order->shipment;
            if (!$shipment) {
                $defaultProvider = LogisticsProvider::where('status', 'approved')->first() ?? LogisticsProvider::first();
                $shipment = $order->shipment()->create([
                    'logistics_provider_id' => $order->logistics_provider_id ?? $defaultProvider?->id ?? 1,
                    'tracking_code' => 'CTZ-'.Str::upper(Str::random(12)),
                    'status' => 'unassigned',
                    'fee_minor' => $order->courierFeeMinor(),
                    'cod_amount_minor' => $order->order->payment_method === 'cod' ? $order->buyerDueMinor() : 0,
                ]);
            }

            $shipment->deliveryEvents()->firstOrCreate(['status' => 'packed', 'attempt' => 1], [
                'user_id' => Auth::id(),
                'note' => 'The seller packed the parcel and marked it ready for pickup.',
                'occurred_at' => now(),
            ]);
        });

        return back()->with('success', 'Order packed and ready for pickup.');
    }

    public function printWaybill(int $id)
    {
        $order = $this->ordersQuery()->with([
            'order.buyer',
            'items',
            'shipment.logisticsProvider',
            'shipment.rider.user',
            'shipment.deliveryEvents.user',
            'seller.pickupAddress',
        ])->findOrFail($id);

        if (!$order->shipment) {
            $defaultProvider = LogisticsProvider::where('status', 'approved')->first() ?? LogisticsProvider::first();
            $order->shipment()->create([
                'logistics_provider_id' => $order->logistics_provider_id ?? $defaultProvider?->id ?? 1,
                'tracking_code' => 'CTZ-'.Str::upper(Str::random(12)),
                'status' => 'unassigned',
                'fee_minor' => $order->courierFeeMinor(),
                'cod_amount_minor' => $order->order->payment_method === 'cod' ? $order->buyerDueMinor() : 0,
            ]);
            $order->load(['shipment.logisticsProvider', 'shipment.rider.user', 'shipment.deliveryEvents.user']);
        }

        $trackingService = new \App\Services\OrderTracking();
        $tracking = $trackingService->build($order);

        $trackingCode = $order->shipment->tracking_code ?? 'CTZ-'.Str::upper(Str::random(12));
        $barcodeSvg = \App\Services\BarcodeService::generateBarcodeSvg($trackingCode, 320, 75);
        $qrSvg = \App\Services\BarcodeService::generateQrSvg($trackingCode, 100);

        return $this->page('waybill', compact('order', 'tracking', 'barcodeSvg', 'qrSvg'));
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
                    app(\App\Services\VoucherService::class)->reverseIfCancelled($order);
                } elseif ($order->payment_status === 'pending') {
                    $remaining = $order->sellerOrders()->where('status', '!=', 'cancelled')->get()
                        ->sum(fn ($row) => $row->buyerDueMinor());
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

    public function courier(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(['ready_to_ship', 'shipped'])]]);
        return $this->page('courier', [
            'orders' => $this->ordersQuery()->whereIn('status', ['ready_to_ship', 'shipped'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->with(['order.buyer', 'shipment.logisticsProvider', 'shipment.rider.user'])->latest()->paginate(15)->withQueryString(),
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
                'status' => 'unassigned', 'fee_minor' => $order->courierFeeMinor(),
                'cod_amount_minor' => $shipment?->cod_amount_minor ?? ($order->order->payment_method === 'cod' ? $order->buyerDueMinor() : 0),
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
        $request->validate([
            'search' => 'nullable|string|max:150',
            'tab' => ['nullable', Rule::in(['all', 'active', 'in_stock', 'low_stock', 'out_of_stock', 'archived'])],
            'category' => 'nullable|integer',
        ]);
        $base = $this->productsQuery();
        // A product is "low" if any live variation is between 1 and its low-stock limit, "out" if none has stock, otherwise "in stock".
        $lowStock = fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0)->atStockAlertLevel());
        $outOfStock = fn ($q) => $q->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
        $inStock = fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0))
            ->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0)->atStockAlertLevel());
        $stockStats = [
            'total' => (clone $base)->where('is_active', true)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'in_stock' => $inStock((clone $base)->where('is_active', true))->count(),
            'low_stock' => $lowStock((clone $base)->where('is_active', true))->count(),
            'out_of_stock' => $outOfStock((clone $base)->where('is_active', true))->count(),
            'archived' => (clone $base)->where('is_active', false)->count(),
            'total_units' => (int) \App\Models\ProductVariant::whereHas('product', fn ($q) => $q->where('seller_id', $this->shop()?->id ?? 0)->where('is_active', true))->where('is_active', true)->sum('stock'),
        ];

        $query = $this->productsQuery()->with(['variants', 'category', 'coverImage']);
        $query->where('is_active', $request->tab !== 'archived');
        match ($request->tab) {
            'in_stock' => $inStock($query),
            'low_stock' => $lowStock($query),
            'out_of_stock' => $outOfStock($query),
            default => null,
        };
        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$request->search.'%')));
        }

        return $this->page($request->routeIs('seller.products') ? 'products' : 'inventory', [
            'products' => $query->latest()->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'stockStats' => $stockStats,
            'inventoryCategories' => Category::whereIn('id', (clone $base)->select('category_id'))->orderBy('name')->get(['id', 'name']),
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

        // SKU is optional on the form: fill blanks with a code like "NIKE-7QK2ZD".
        $skuPrefix = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('name')), 0, 4)) ?: 'PROD';
        $makeSku = function () use ($skuPrefix) {
            do {
                $sku = $skuPrefix.'-'.Str::upper(Str::random(6));
            } while (\App\Models\ProductVariant::where('sku', $sku)->exists());
            return $sku;
        };
        if ($hasVariants) {
            $request->merge(['variants' => collect($request->input('variants'))
                ->map(fn ($variant) => is_array($variant) && blank($variant['sku'] ?? null) ? ['sku' => $makeSku()] + $variant : $variant)
                ->all()]);
        }
        if ((!$hasVariants || $request->boolean('primary_variant')) && blank($request->input('sku'))) {
            $request->merge(['sku' => $makeSku()]);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('is_active', true)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids', 'boys', 'girls', 'all_kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'images' => ['nullable', 'array', function ($attribute, $files, $fail) {
                if (is_array($files) && collect($files)->sum(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile && $file->isValid() ? $file->getSize() : 0) > 10 * 1024 * 1024) {
                    $fail('All product photos combined must be 10 MB or smaller.');
                }
            }],
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
        ];

        if ($hasVariants) {
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.name'] = 'required|string|max:255';
            $rules['variants.*.sku'] = 'required|string|max:100|distinct|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0.01|max:99999999.99';
            $rules['variants.*.stock'] = 'required|integer|min:0|max:1000000';
            $rules['variants.*.color'] = 'nullable|string|max:50';
            $rules['variants.*.image'] = 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240';
            // Photos of one color/style, uploaded once and shared by every size added with it.
            $rules['variants.*.photo_group'] = ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'];
            $rules['option_photos'] = 'nullable|array';
            $rules['option_photos.*'] = 'array';
            $rules['option_photos.*.*'] = 'image|mimes:jpg,jpeg,png,webp|max:10240';
        }
        if (!$hasVariants || $request->boolean('primary_variant')) {
            $rules['sku'] = 'required|string|max:100|unique:product_variants,sku';
            $rules['price'] = $request->boolean('primary_variant')
                ? 'required_without:primary_size_stocks|nullable|numeric|min:0.01|max:99999999.99'
                : 'required|numeric|min:0.01|max:99999999.99';
            $rules['stock'] = $request->boolean('primary_variant')
                ? 'required_without:primary_size_stocks|nullable|integer|min:0|max:1000000'
                : 'required|integer|min:0|max:1000000';
        }

        $rules['primary_variant'] = 'sometimes|boolean';
        $rules['primary_color'] = 'nullable|string|max:100';
        $rules['primary_size'] = 'nullable|string|max:1000';
        $rules['primary_size_stocks'] = 'nullable|array|max:50';
        $rules['primary_size_stocks.*.size'] = 'required|string|max:100|distinct:ignore_case';
        $rules['primary_size_stocks.*.stock'] = 'required|integer|min:0|max:1000000';
        $rules['primary_size_stocks.*.price'] = 'required_without:price|nullable|numeric|min:0.01|max:99999999.99';
        $rules['colors'] = 'nullable|string|max:500';
        $rules['low_stock_threshold'] = 'nullable|integer|min:0|max:100000';
        $rules['weight_grams'] = 'nullable|integer|min:0|max:1000000';
        $data = $request->validate($rules, [
            'option_photos.*.*.max' => 'Each color photo must be 10 MB or smaller. Please choose a smaller photo.',
            'option_photos.*.*.image' => 'Color photos must be JPG, PNG or WebP images.',
            'option_photos.*.*.mimes' => 'Color photos must be JPG, PNG or WebP images.',
            'variants.*.image.max' => 'Each color photo must be 10 MB or smaller. Please choose a smaller photo.',
        ]);

        $categoryIds = collect($request->input('category_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty() && !empty($data['category_id'])) {
            $categoryIds = collect([(int) $data['category_id']]);
        }

        if ($categoryIds->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'category_ids' => 'Please select at least one category.',
            ]);
        }

        $primaryCategoryId = (int) $categoryIds->first();

        $primarySizes = collect(explode(',', $data['primary_size'] ?? ''))
            ->map(fn ($size) => trim($size))->filter(fn ($size) => $size !== '')
            ->unique(fn ($size) => Str::lower($size))->values();
        if ($primarySizes->count() > 50 || $primarySizes->contains(fn ($size) => mb_strlen($size) > 100)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'primary_size' => 'Enter up to 50 sizes, with at most 100 characters per size.',
            ]);
        }
        $sizeStocks = collect($data['primary_size_stocks'] ?? [])->keyBy(fn ($row) => Str::lower(trim($row['size'])));
        if (!empty($data['primary_variant']) && $sizeStocks->isNotEmpty()
            && ($sizeStocks->count() !== $primarySizes->count() || $primarySizes->contains(fn ($size) => !$sizeStocks->has(Str::lower($size))))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'primary_size' => 'Enter the available stock for each of your selected sizes.',
            ]);
        }
        $weight = (int) ($data['weight_grams'] ?? 0);

        if ($hasVariants) {
            $normalizeOption = fn ($name) => Str::lower(implode(' / ', array_map('trim', explode('/', $name))));
            $optionNames = collect();
            if (!empty($data['primary_variant'])) {
                foreach ($primarySizes->isEmpty() ? [''] : $primarySizes as $size) {
                    $name = implode(' / ', array_filter([trim($data['primary_color'] ?? ''), $size], fn ($value) => $value !== '')) ?: 'Standard';
                    $optionNames->push($normalizeOption($name));
                }
            }
            foreach ($data['variants'] as $index => $variant) {
                $name = $normalizeOption($variant['name']);
                if ($optionNames->contains($name)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "variants.$index.name" => 'This option is already listed. Remove it or use a different color or size.',
                    ]);
                }
                if (!empty($data['primary_variant']) && $variant['sku'] === $data['sku']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "variants.$index.sku" => 'Use a different stock code from the first product option.',
                    ]);
                }
                $optionNames->push($name);
            }
        }

        // "Black, White, Olive" → one variation per color, each starting with the price and stock entered.
        $colors = $hasVariants ? collect() : collect(explode(',', (string) ($data['colors'] ?? '')))
            ->map(fn ($color) => Str::limit(trim($color), 50, ''))
            ->filter()
            ->unique(fn ($color) => Str::lower($color))
            ->take(20)
            ->values();

        DB::transaction(function () use ($data, $shop, $request, $hasVariants, $colors, $weight, $primarySizes, $sizeStocks, $makeSku, $categoryIds, $primaryCategoryId) {
            $product = $shop->products()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $primaryCategoryId,
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(10)),
                'is_active' => true,
                'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? Product::MIN_LOW_STOCK_THRESHOLD),
            ]);

            $product->categories()->sync($categoryIds->all());

            $firstVariantImage = null;

            if (!empty($data['primary_variant'])) {
                foreach ($primarySizes->isEmpty() ? [''] : $primarySizes as $sizeIndex => $size) {
                    $primaryOptions = array_filter([
                        'color_name' => trim($data['primary_color'] ?? ''),
                        'size' => $size,
                    ], fn ($value) => $value !== '');
                    $product->variants()->create([
                        'sku' => $sizeIndex === 0 ? $data['sku'] : $makeSku(),
                        'name' => implode(' / ', $primaryOptions) ?: 'Standard',
                        'options' => $primaryOptions ?: null,
                        'price_minor' => (int) round(($sizeStocks->get(Str::lower($size))['price'] ?? $data['price']) * 100),
                        'stock' => (int) ($sizeStocks->get(Str::lower($size))['stock'] ?? $data['stock'] ?? 0),
                        'weight_grams' => $weight,
                        'is_active' => true,
                    ]);
                }
            }

            if ($hasVariants && !empty($data['variants'])) {
                // Store each color's photos once; all sizes of that color share them.
                $groupPaths = [];
                foreach ((array) $request->file('option_photos', []) as $group => $files) {
                    if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/', (string) $group)) continue;
                    foreach ((array) $files as $file) {
                        if ($file && $file->isValid()) $groupPaths[$group][] = $file->store('products/variants', 'public');
                    }
                }
                foreach ($data['variants'] as $idx => $v) {
                    $photos = [];
                    if ($request->hasFile("variants.{$idx}.image") && $request->file("variants.{$idx}.image")->isValid()) {
                        $photos[] = $request->file("variants.{$idx}.image")->store('products/variants', 'public');
                    }
                    $photos = array_values(array_unique([...$photos, ...($groupPaths[$v['photo_group'] ?? ''] ?? [])]));
                    $variantImagePath = $photos[0] ?? null;
                    if ($variantImagePath && !$firstVariantImage) {
                        $firstVariantImage = $variantImagePath;
                    }

                    $options = !empty($v['color']) ? ['color' => $v['color']] : null;

                    $product->variants()->create([
                        'name' => $v['name'],
                        'sku' => $v['sku'],
                        'price_minor' => (int) round($v['price'] * 100),
                        'stock' => (int) $v['stock'],
                        'image_path' => $variantImagePath,
                        'gallery' => $photos ?: null,
                        'options' => $options,
                        'weight_grams' => $weight,
                        'is_active' => true,
                    ]);
                }
            } elseif ($colors->isNotEmpty() && empty($data['primary_variant'])) {
                $swatches = ['black' => '#1F1F1F', 'white' => '#F5F5F5', 'gray' => '#9E9E9E', 'navy' => '#1F2F55', 'blue' => '#3B6DB3', 'red' => '#C0392B', 'green' => '#4B8B52', 'pink' => '#F4B6C2', 'beige' => '#E3D5B8', 'brown' => '#7A5536', 'yellow' => '#E6C13B', 'purple' => '#7D55A8'];
                foreach ($colors as $color) {
                    // Base SKU + color code, e.g. NIKE-001-BLACK; add a suffix if that is already taken.
                    $sku = Str::limit($data['sku'], 80, '').'-'.Str::upper(Str::limit(Str::slug($color, ''), 12, ''));
                    while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
                        $sku = Str::limit($data['sku'], 80, '').'-'.Str::upper(Str::random(4));
                    }
                    $hex = $swatches[Str::lower($color)] ?? null;
                    $product->variants()->create([
                        'sku' => $sku,
                        'name' => $color,
                        'price_minor' => (int) round($data['price'] * 100),
                        'stock' => (int) $data['stock'],
                        'options' => $hex ? ['color' => $hex] : null,
                        'weight_grams' => $weight,
                        'is_active' => true,
                    ]);
                }
            } elseif (empty($data['primary_variant'])) {
                $product->variants()->create([
                    'sku' => $data['sku'],
                    'name' => 'Standard',
                    'price_minor' => (int) round($data['price'] * 100),
                    'stock' => (int) $data['stock'],
                    'weight_grams' => $weight,
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

        return ($request->routeIs('seller.products.add') ? redirect()->route('seller.products') : back())
            ->with('success', 'Product added to your inventory.');
    }

    public function productSale(int $id)
    {
        $product = $this->productsQuery()->with('variants')->findOrFail($id);
        return $this->page('product-sale', compact('product'));
    }

    public function saveProductSale(Request $request, int $id)
    {
        abort_unless(Auth::user()->status === 'active' && $this->shop()?->isApproved(), 403);
        $data = $request->validate([
            'discount_type' => ['present', 'nullable', Rule::in(['percentage', 'fixed'])],
            'discount_value' => 'exclude_unless:discount_type,percentage,fixed|required|numeric|decimal:0,2|min:0.01|max:99999999.99',
            'discount_starts_at' => 'exclude_unless:discount_type,percentage,fixed|nullable|date_format:Y-m-d\\TH:i',
            'discount_ends_at' => 'exclude_unless:discount_type,percentage,fixed|nullable|date_format:Y-m-d\\TH:i',
        ]);
        foreach (['discount_starts_at', 'discount_ends_at'] as $field) {
            $data[$field] = !empty($data[$field])
                ? \Carbon\CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data[$field], 'Asia/Manila')->startOfMinute()->utc()
                : null;
        }
        if ($data['discount_ends_at'] && ($data['discount_ends_at']->lte(now())
            || ($data['discount_starts_at'] && $data['discount_ends_at']->lte($data['discount_starts_at'])))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_ends_at' => 'End time must be in the future and later than the start time (Philippine time).',
            ]);
        }
        DB::transaction(function () use ($id, $data) {
            $product = $this->productsQuery()->lockForUpdate()->findOrFail($id);
            $prices = $product->variants()->lockForUpdate()->get()->pluck('price')->all();
            $this->validateDiscount($data, $prices);
            $product->update([
                'discount_type' => $data['discount_type'] ?: null,
                'discount_value' => $data['discount_type'] ? $data['discount_value'] : 0,
                'discount_starts_at' => $data['discount_starts_at'],
                'discount_ends_at' => $data['discount_ends_at'],
            ]);
        });
        return redirect()->route('seller.products.sale', $id)
            ->with('success', $data['discount_type'] ? 'Sale settings saved. Discounts follow your schedule.' : 'Sale ended. Regular prices are restored.');
    }

    private function validateDiscount(array $data, array $prices): void
    {
        $type = $data['discount_type'] ?? null;
        $value = (float) ($data['discount_value'] ?? 0);
        if ($type === 'percentage' && $value > 99.99) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_value' => 'Percentage discount must be between 0.01 and 99.99.']);
        }
        if ($type === 'fixed' && $prices && (int) round($value * 100) >= (int) round(min($prices) * 100)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_value' => 'The fixed discount must be less than every variation price.']);
        }
    }

    /** Edit page for one product: details on the left, options / sale / delete on the right. */
    public function editProduct(int $id)
    {
        $product = $this->productsQuery()->with(['variants' => fn ($q) => $q->orderBy('id'), 'category', 'categories', 'coverImage', 'images'])->findOrFail($id);
        $orderedVariantIds = \App\Models\OrderItem::whereIn('product_variant_id', $product->variants->pluck('id'))->distinct()->pluck('product_variant_id')->all();

        return $this->page('product-edit', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'orderedVariantIds' => $orderedVariantIds,
        ]);
    }

    /** Saves the left-hand cards: details, price (single option), stock settings, photo and visibility. */
    public function saveProductDetails(Request $request, int $id)
    {
        abort_unless(Auth::user()->status === 'active' && $this->shop()?->isApproved(), 403, 'An approved store is required to edit products.');
        $product = $this->productsQuery()->with('variants')->findOrFail($id);
        $live = $product->variants->where('is_active', true)->values();
        $single = $live->count() === 1 ? $live->first() : null;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => $single ? ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($single->id)] : 'exclude',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('is_active', true)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids', 'boys', 'girls', 'all_kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'description' => 'nullable|string|max:5000',
            'price' => $single ? 'required|numeric|min:0.01|max:99999999.99' : 'exclude',
            'low_stock_threshold' => 'required|integer|min:0|max:100000',
            'weight_grams' => 'nullable|integer|min:0|max:1000000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'new_photos' => 'nullable|array',
            'new_photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer',
            'is_active' => 'required|boolean',
        ]);
        if ($single) {
            $this->validateDiscount(['discount_type' => $product->discount_type, 'discount_value' => $product->discount_value], [$data['price']]);
        }

        // Count the final gallery, excluding removed and replaced photos.
        $replacementFiles = $request->file('photos', []);
        if ($request->hasFile('image') && !isset($replacementFiles[0])) {
            $replacementFiles[0] = $request->file('image');
        }
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $galleryBytes = $product->images()->get()
            ->reject(fn ($image) => in_array($image->id, $data['remove_image_ids'] ?? []) || isset($replacementFiles[$image->position]))
            ->sum(fn ($image) => $disk->exists($image->path) ? $disk->size($image->path) : 0);
        foreach (array_merge($replacementFiles, $request->file('new_photos', [])) as $file) {
            $galleryBytes += $file ? $file->getSize() : 0;
        }
        if ($galleryBytes > 10 * 1024 * 1024) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'photos' => 'All product photos combined must be 10 MB or smaller. Remove photos or choose smaller images.',
            ]);
        }

        $categoryIds = collect($request->input('category_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty() && !empty($data['category_id'])) {
            $categoryIds = collect([(int) $data['category_id']]);
        }

        if ($categoryIds->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'category_ids' => 'Please select at least one category.',
            ]);
        }

        $primaryCategoryId = (int) $categoryIds->first();

        DB::transaction(function () use ($product, $data, $single, $request, $categoryIds, $primaryCategoryId) {
            $product->update([
                'name' => $data['name'],
                'category_id' => $primaryCategoryId,
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
                'description' => $data['description'] ?? null,
                'low_stock_threshold' => (int) $data['low_stock_threshold'],
                'is_active' => (bool) $data['is_active'],
            ]);
            $product->categories()->sync($categoryIds->all());
            if ($single) {
                $single->update(['sku' => $data['sku'], 'price_minor' => (int) round($data['price'] * 100)]);
            }
            if (array_key_exists('weight_grams', $data) && $data['weight_grams'] !== null) {
                $product->variants()->update(['weight_grams' => (int) $data['weight_grams']]);
            }

            // 1. Remove requested images
            if (!empty($data['remove_image_ids'])) {
                $toDelete = $product->images()->whereIn('id', $data['remove_image_ids'])->get();
                foreach ($toDelete as $delImg) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($delImg->path);
                    $delImg->delete();
                }
            }

            $existingImages = $product->images()->orderBy('position')->get()->values();

            // 2. Cover photo (Slot 0 or fallback 'image')
            $coverFile = $request->file('photos.0') ?? $request->file('image');
            if ($coverFile && $coverFile->isValid()) {
                $coverPath = $coverFile->store('products', 'public');
                $firstImg = $existingImages->firstWhere('position', 0);
                if ($firstImg) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($firstImg->path);
                    $firstImg->update(['path' => $coverPath, 'position' => 0]);
                } else {
                    $product->images()->create(['path' => $coverPath, 'position' => 0]);
                }
            }

            // 3. Replace or add any submitted gallery slot.
            foreach ($request->file('photos', []) as $slot => $file) {
                if ((int) $slot === 0) continue;
                if ($request->hasFile("photos.{$slot}") && $request->file("photos.{$slot}")->isValid()) {
                    $newPath = $request->file("photos.{$slot}")->store('products', 'public');
                    $existingAtSlot = $product->images()->where('position', $slot)->first();
                    if ($existingAtSlot) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existingAtSlot->path);
                        $existingAtSlot->update(['path' => $newPath]);
                    } else {
                        $product->images()->create(['path' => $newPath, 'position' => $slot]);
                    }
                }
            }

            // 4. Bulk uploaded extra photos
            if ($request->hasFile('new_photos')) {
                foreach ($request->file('new_photos') as $gFile) {
                    if ($gFile && $gFile->isValid()) {
                        $usedPositions = $product->images()->pluck('position')->all();
                        $nextPos = 0;
                        while (in_array($nextPos, $usedPositions)) {
                            $nextPos++;
                        }
                        $gPath = $gFile->store('products', 'public');
                        $product->images()->create(['path' => $gPath, 'position' => $nextPos]);
                    }
                }
            }

            // 5. Normalize positions: 0, 1, 2, ...
            $allImgs = $product->images()->orderBy('position')->orderBy('id')->get();
            foreach ($allImgs as $idx => $img) {
                if ($img->position !== $idx) {
                    $img->update(['position' => $idx]);
                }
            }
        });

        \App\Services\NotificationService::notify(Auth::id(), 'product_update', 'Product updated', $product->name.' details were updated.', route('seller.products.edit', $product->id));

        return redirect()->route('seller.products.edit', $product->id)->with('success', 'Product changes saved.');
    }

    /** Saves the "Colors & sizes" card: edit, add, and remove options. */
    public function saveProductOptions(Request $request, int $id)
    {
        abort_unless(Auth::user()->status === 'active' && $this->shop()?->isApproved(), 403, 'An approved store is required to edit products.');
        $product = $this->productsQuery()->with('variants')->findOrFail($id);
        if ($request->has('color_groups')) {
            $data = $request->validate([
                'color_groups' => 'required|array',
                'color_groups.*.color' => 'nullable|string|max:100',
                'color_groups.*.price' => 'required|numeric|min:0.01|max:99999999.99',
                'color_groups.*.sizes_text' => 'nullable|string|max:1000',
                'color_groups.*.sizes' => 'nullable|array',
                'color_groups.*.sizes.*.size' => 'nullable|string|max:50',
                'color_groups.*.sizes.*.stock' => 'required|integer|min:0|max:1000000',
                'color_groups.*.sizes.*.id' => 'nullable|integer',
                'color_groups.*.sizes.*.previous_stock' => 'nullable|integer|min:0',
                'color_groups.*.remove' => 'nullable|boolean',
            ]);

            $swatches = ['black' => '#1F1F1F', 'white' => '#F5F5F5', 'gray' => '#9E9E9E', 'navy' => '#1F2F55', 'blue' => '#3B6DB3', 'red' => '#C0392B', 'green' => '#4B8B52', 'pink' => '#F4B6C2', 'beige' => '#E3D5B8', 'brown' => '#7A5536', 'yellow' => '#E6C13B', 'purple' => '#7D55A8'];

            $allPrices = [];
            $hasAnyActive = false;

            foreach ($data['color_groups'] as $group) {
                if (empty($group['remove'])) {
                    $hasAnyActive = true;
                    $allPrices[] = (float) $group['price'];
                }
            }

            if (!$hasAnyActive) {
                throw \Illuminate\Validation\ValidationException::withMessages(['color_groups' => 'Keep at least one option. To stop selling this product, set it to Hidden instead.']);
            }

            $this->validateDiscount(
                ['discount_type' => $product->discount_type, 'discount_value' => $product->discount_value],
                $allPrices
            );

            $existingVariants = $product->variants;
            $orderedVariantIds = \App\Models\OrderItem::whereIn('product_variant_id', $existingVariants->pluck('id'))->distinct()->pluck('product_variant_id')->all();
            $prefix = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $product->name), 0, 4)) ?: 'PROD';
            $weight = (int) ($existingVariants->first()?->weight_grams ?? 0);

            DB::transaction(function () use ($product, $data, $existingVariants, $orderedVariantIds, $prefix, $weight, $swatches) {
                $processedVariantIds = [];

                foreach ($data['color_groups'] as $group) {
                    $color = trim((string) ($group['color'] ?? ''));
                    $price = (float) $group['price'];
                    $isRemoved = !empty($group['remove']);
                    $hex = $swatches[Str::lower($color)] ?? null;

                    $sizes = $group['sizes'] ?? [];
                    if (empty($sizes)) {
                        $sizes = [['size' => '', 'stock' => 0, 'id' => null]];
                    }

                    foreach ($sizes as $s) {
                        $size = trim((string) ($s['size'] ?? ''));
                        $stock = (int) ($s['stock'] ?? 0);
                        $vId = !empty($s['id']) ? (int) $s['id'] : null;

                        if ($isRemoved) {
                            if ($vId && ($variant = $existingVariants->firstWhere('id', $vId))) {
                                $processedVariantIds[] = $vId;
                                in_array($vId, $orderedVariantIds) ? $variant->update(['is_active' => false]) : $variant->delete();
                            }
                            continue;
                        }

                        if ($color !== '' && $size !== '') {
                            $name = "{$color} / {$size}";
                        } elseif ($color !== '') {
                            $name = $color;
                        } elseif ($size !== '') {
                            $name = $size;
                        } else {
                            $name = 'Standard';
                        }

                        $options = array_filter([
                            'color_name' => $color,
                            'size' => $size,
                            'color' => $hex,
                        ], fn ($val) => $val !== '' && $val !== null);

                        if ($vId && ($variant = $existingVariants->firstWhere('id', $vId))) {
                            $processedVariantIds[] = $vId;
                            $variant->update([
                                'name' => $name,
                                'options' => !empty($options) ? $options : null,
                                'price_minor' => (int) round($price * 100),
                                'stock' => $stock,
                                'is_active' => true,
                            ]);
                        } else {
                            do {
                                $sku = $prefix . '-' . Str::upper(Str::random(6));
                            } while (\App\Models\ProductVariant::where('sku', $sku)->exists());

                            $created = $product->variants()->create([
                                'name' => $name,
                                'sku' => $sku,
                                'price_minor' => (int) round($price * 100),
                                'stock' => $stock,
                                'weight_grams' => $weight,
                                'options' => !empty($options) ? $options : null,
                                'is_active' => true,
                            ]);
                            $processedVariantIds[] = $created->id;
                        }
                    }
                }

                // Any active existing variant that wasn't in processedVariantIds was removed
                foreach ($existingVariants as $variant) {
                    if ($variant->is_active && !in_array($variant->id, $processedVariantIds)) {
                        in_array($variant->id, $orderedVariantIds) ? $variant->update(['is_active' => false]) : $variant->delete();
                    }
                }
            });

            return redirect()->route('seller.products.edit', $product->id)->with('success', 'Colors & sizes saved.');
        }

        $data = $request->validate([
            'options' => 'nullable|array',
            'options.*.id' => 'required|integer|distinct',
            'options.*.name' => 'nullable|string|max:255',
            'options.*.color' => 'nullable|string|max:100',
            'options.*.size' => 'nullable|string|max:100',
            'options.*.price' => 'required|numeric|min:0.01|max:99999999.99',
            'options.*.stock' => 'required|integer|min:0|max:1000000',
            'options.*.previous_stock' => 'required|integer|min:0',
            'options.*.remove' => 'nullable|boolean',
            'new_options' => 'nullable|array',
            'new_options.*.name' => 'nullable|string|max:255',
            'new_options.*.color' => 'nullable|string|max:100',
            'new_options.*.size' => 'nullable|string|max:100',
            'new_options.*.price' => 'nullable|numeric|min:0.01|max:99999999.99',
            'new_options.*.stock' => 'nullable|integer|min:0|max:1000000',
        ]);

        $makeVariantName = function ($row, $fallbackName = 'Standard') {
            $color = trim($row['color'] ?? '');
            $size = trim($row['size'] ?? '');
            if ($color !== '' && $size !== '') {
                return "{$color} / {$size}";
            } elseif ($color !== '') {
                return $color;
            } elseif ($size !== '') {
                return $size;
            }
            return trim($row['name'] ?? '') ?: $fallbackName;
        };

        $makeVariantOptions = function ($row, $existingOptions = null) {
            $color = trim($row['color'] ?? '');
            $size = trim($row['size'] ?? '');
            $swatches = ['black' => '#1F1F1F', 'white' => '#F5F5F5', 'gray' => '#9E9E9E', 'navy' => '#1F2F55', 'blue' => '#3B6DB3', 'red' => '#C0392B', 'green' => '#4B8B52', 'pink' => '#F4B6C2', 'beige' => '#E3D5B8', 'brown' => '#7A5536', 'yellow' => '#E6C13B', 'purple' => '#7D55A8'];
            $hex = $swatches[Str::lower($color)] ?? ($existingOptions['color'] ?? null);

            $opts = [];
            if ($color !== '') $opts['color_name'] = $color;
            if ($size !== '') $opts['size'] = $size;
            if ($hex) $opts['color'] = $hex;

            return !empty($opts) ? $opts : null;
        };

        $existing = collect($data['options'] ?? []);
        $added = collect($data['new_options'] ?? [])->filter(function ($row) use ($makeVariantName) {
            $name = $makeVariantName($row, '');
            return filled($name) || filled($row['price'] ?? null);
        });

        foreach ($existing as $row) {
            abort_unless($product->variants->firstWhere('id', $row['id']), 404);
        }
        $kept = $existing->reject(fn ($row) => !empty($row['remove']));
        if ($kept->isEmpty() && $added->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'Keep at least one option. To stop selling this product, set it to Hidden instead.']);
        }
        $this->validateDiscount(
            ['discount_type' => $product->discount_type, 'discount_value' => $product->discount_value],
            $kept->pluck('price')->merge($added->pluck('price'))->all()
        );

        $ordered = \App\Models\OrderItem::whereIn('product_variant_id', $existing->pluck('id'))->distinct()->pluck('product_variant_id')->all();
        $prefix = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $product->name), 0, 4)) ?: 'PROD';
        $weight = (int) ($product->variants->first()?->weight_grams ?? 0);

        DB::transaction(function () use ($product, $existing, $added, $ordered, $prefix, $weight, $makeVariantName, $makeVariantOptions) {
            foreach ($existing->sortBy('id') as $row) {
                $variant = $product->variants()->lockForUpdate()->findOrFail($row['id']);
                if (!empty($row['remove'])) {
                    // Options with past orders are hidden so order history stays intact.
                    in_array($variant->id, $ordered) ? $variant->update(['is_active' => false]) : $variant->delete();
                    continue;
                }
                if ((int) $variant->stock !== (int) $row['previous_stock']) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'Stock for "'.$variant->name.'" changed since this page was opened. Refresh the page and try again.']);
                }
                $vName = $makeVariantName($row, $variant->name);
                $vOptions = $makeVariantOptions($row, $variant->options);
                $variant->update([
                    'name' => $vName,
                    'options' => $vOptions,
                    'price_minor' => (int) round($row['price'] * 100),
                    'stock' => (int) $row['stock'],
                    'is_active' => true,
                ]);
            }
            foreach ($added as $row) {
                do {
                    $sku = $prefix.'-'.Str::upper(Str::random(6));
                } while (\App\Models\ProductVariant::where('sku', $sku)->exists());
                $vName = $makeVariantName($row, 'Standard');
                $vOptions = $makeVariantOptions($row);
                $product->variants()->create([
                    'name' => $vName,
                    'sku' => $sku,
                    'price_minor' => (int) round(($row['price'] ?? ($product->variants->first()?->price ?: 0.01)) * 100),
                    'stock' => (int) ($row['stock'] ?? 0),
                    'weight_grams' => $weight,
                    'options' => $vOptions,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('seller.products.edit', $product->id)->with('success', 'Colors & sizes saved.');
    }

    /** Removes the product from the shop. It is soft-deleted, so past orders keep their records. */
    public function deleteProduct(int $id)
    {
        abort_unless(Auth::user()->status === 'active' && $this->shop()?->isApproved(), 403, 'An approved store is required to delete products.');
        $product = $this->productsQuery()->findOrFail($id);
        $product->delete();

        return redirect()->route('seller.products')->with('success', '"'.$product->name.'" was deleted.');
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
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids', 'boys', 'girls', 'all_kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|integer|distinct',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'required|numeric|min:0.01|max:99999999.99',
            'variants.*.stock' => $request->routeIs('seller.products.update') ? 'exclude' : 'required|integer|min:0|max:1000000',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
            'new_variants' => 'nullable|array',
            'new_variants.*.name' => 'required_with:new_variants|string|max:255',
            'new_variants.*.sku' => 'required_with:new_variants|string|max:100|distinct|unique:product_variants,sku',
            'new_variants.*.price' => 'required_with:new_variants|numeric|min:0.01|max:99999999.99',
            'new_variants.*.stock' => 'required_with:new_variants|integer|min:0|max:1000000',
            'new_variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif,bmp,gif|max:10240',
        ]);
        $discount = [
            'discount_type' => $product->discount_type,
            'discount_value' => $product->discount_value,
        ];
        foreach ($data['variants'] as $entry) {
            $variant = $product->variants->firstWhere('id', $entry['id']);
            abort_unless($variant, 404);
        }
        $prices = $product->variants->map(function ($v) use ($data) {
            return collect($data['variants'])->firstWhere('id', $v->id)['price'] ?? $v->price;
        })->merge(array_column($data['new_variants'] ?? [], 'price'))->all();
        $this->validateDiscount($discount, $prices);
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
            \App\Models\AppNotification::where('user_id', Auth::id())
                ->whereNull('archived_at')
                ->where('type', 'product_update')
                ->where('title', 'Product visibility updated')
                ->update(['is_read' => true]);
        });

        return back()->with('success', 'Product visibility updated.');
    }

    public function deleteArchivedProduct(int $id)
    {
        if (Auth::user()->status !== 'active' || !$this->shop()?->isApproved()) {
            return back()->withErrors(['name' => 'Your seller account is currently pending administrator approval.']);
        }

        DB::transaction(function () use ($id) {
            $product = $this->productsQuery()->lockForUpdate()->findOrFail($id);
            abort_if($product->is_active, 422, 'Archive this product before deleting it.');
            // Soft deletion removes the listing while preserving historical order records.
            $product->delete();
        });

        return redirect()->route('seller.archive')->with('success', 'Product deleted from your archive.');
    }

    public function archive(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:150']);
        $base = $this->productsQuery()->where('is_active', false);

        $query = (clone $base)->with(['variants', 'category', 'coverImage']);
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$request->search.'%')));
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $archivedCount = (clone $base)->count();

        return $this->page('archive', compact('products', 'archivedCount'));
    }

    public function reports(Request $request)
    {
        $data = $request->validate(['from_date' => 'nullable|date_format:Y-m-d', 'to_date' => 'nullable|date_format:Y-m-d|after_or_equal:from_date', 'export' => 'nullable|in:csv']);
        $from = $data['from_date'] ?? now()->subDays(29)->format('Y-m-d');
        $to = $data['to_date'] ?? now()->format('Y-m-d');
        abort_if($to < $from, 422, 'End date must be on or after the start date.');
        $query = $this->paidSales()->whereDate('delivered_at', '>=', $from)->whereDate('delivered_at', '<=', $to)->with('order');
        $summary = ['gross' => (int) (clone $query)->sum('subtotal_minor'), 'commission' => (int) (clone $query)->sum('commission_minor'), 'shop_vouchers' => (int) (clone $query)->sum(DB::raw('seller_voucher_discount_minor + seller_shipping_discount_minor')), 'count' => (clone $query)->count()];
        if ($request->export === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Order', 'Delivered', 'Gross (PHP)', 'Commission (PHP)', 'Shop vouchers & free shipping (PHP)', 'Net sales (PHP)']);
                foreach ($query->lazyById(200) as $order) {
                    $reference = preg_match('/^[=+@\-\t\r]/', $order->order->reference) ? "'".$order->order->reference : $order->order->reference;
                    fputcsv($file, [$reference, $order->delivered_at->format('Y-m-d'), $order->subtotal_minor / 100, $order->commission_minor / 100, $order->sellerFundedMinor() / 100, $order->sellerNetMinor() / 100]);
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
            'phone'            => ['nullable', 'string', 'regex:/\A[0-9]+\z/', 'max:11', Rule::unique('users', 'phone')->ignore(Auth::id())],
            'line_of_business' => 'nullable|string|max:150',
            'name'             => ['nullable', 'string', 'max:255', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'first_name'       => ['nullable', 'string', 'max:120', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'last_name'        => ['nullable', 'string', 'max:120', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'middle_initial'   => 'nullable|string|max:50',
            'sex'              => 'nullable|string|in:Male,Female,Prefer not to say',
            'birthday'         => ['nullable', 'bail', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYearsNoOverflow(18)->toDateString()],
            'id_type'          => 'nullable|string|max:100',
            'id_number'        => 'nullable|string|max:100',
            'id_photo'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'dti_permit'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'phone.unique'     => 'This mobile number is already in use.',
            'name.regex'       => 'Special characters and numbers are not allowed.',
            'first_name.regex' => 'Special characters and numbers are not allowed.',
            'last_name.regex'  => 'Special characters and numbers are not allowed.',
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
