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
        return view('seller.'.$name, $data + ['shop' => $this->shop()]);
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
        $request->validate(['search' => 'nullable|string|max:150', 'status' => ['nullable', Rule::in(['all', 'to_prepare', 'pending', 'accepted', 'packed', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled'])]]);
        $query = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment']);
        if ($request->status === 'to_prepare') {
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
            abort_unless(in_array($order->status, ['pending', 'accepted', 'packed']), 422, 'This order cannot be packed in its current status.');
            abort_if(in_array($order->order->payment_status, ['refunded', 'partially_refunded']), 422, 'A refunded order cannot be packed.');
            abort_if($order->order->payment_method !== 'cod' && $order->order->payment_status !== 'paid', 422, 'Payment must be received before packing.');
            $order->update(['status' => 'ready_to_ship']);
        });

        return back()->with('success', 'Order packed and ready for pickup.');
    }

    public function printWaybill(int $id)
    {
        $order = $this->ordersQuery()->with(['order.buyer', 'items', 'shipment.logisticsProvider', 'seller.pickupAddress'])->findOrFail($id);

        return $this->page('waybill', compact('order'));
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
            $shipment = $order->shipment;
            abort_if($shipment && $shipment->status !== 'unassigned', 422, 'This shipment has already been assigned to a courier.');
            $order->update(['logistics_provider_id' => $data['logistics_provider_id'], 'note' => 'Requested pickup: '.$data['pickup_date'].(! empty($data['notes']) ? "\n".$data['notes'] : '')]);
            Shipment::updateOrCreate(['seller_order_id' => $order->id], [
                'logistics_provider_id' => $data['logistics_provider_id'], 'tracking_code' => $shipment?->tracking_code ?? 'CTZ-'.Str::upper(Str::random(12)),
                'status' => 'unassigned', 'fee_minor' => $order->shipping_fee_minor,
                'cod_amount_minor' => $shipment?->cod_amount_minor ?? ($order->order->payment_method === 'cod' ? $order->subtotal_minor + $order->shipping_fee_minor : 0),
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
            $query->where('is_active', true)->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('stock', '<=', 10));
        }
        if ($request->tab === 'out_of_stock') {
            $query->where('is_active', true)->where(function ($q) {
                $q->whereDoesntHave('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
            });
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$request->search.'%')));
        }

        return $this->page('inventory', [
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

        $data = $request->validate([
            'name' => 'required|string|max:255', 'description' => 'nullable|string|max:5000',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'gender' => ['nullable', 'string', Rule::in(['unisex', 'men', 'women', 'kids'])],
            'badge' => ['nullable', 'string', 'max:60'],
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'price' => 'required|numeric|min:0.01|max:99999999.99', 'stock' => 'required|integer|min:0|max:1000000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        DB::transaction(function () use ($data, $shop, $request) {
            $product = $shop->products()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'],
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(10)),
                'is_active' => true
            ]);
            $product->variants()->create(['sku' => $data['sku'], 'name' => 'Standard', 'price_minor' => (int) round($data['price'] * 100), 'stock' => $data['stock'], 'is_active' => true]);
            if ($request->hasFile('image')) {
                $product->images()->create(['path' => $request->file('image')->store('products', 'public'), 'position' => 0]);
            }
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
            'variants.*.price' => 'required|numeric|min:0.01|max:99999999.99',
            'variants.*.stock' => 'required|integer|min:0|max:1000000'
        ]);
        DB::transaction(function () use ($product, $data) {
            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'gender' => $data['gender'] ?? 'unisex',
                'badge' => !empty($data['badge']) ? $data['badge'] : null,
            ]);
            foreach ($data['variants'] as $variant) {
                $product->variants()->lockForUpdate()->findOrFail($variant['id'])->update(['price_minor' => (int) round($variant['price'] * 100), 'stock' => $variant['stock']]);
            }
        });

        return back()->with('success', 'Product and stock levels updated.');
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
            'phone'            => 'nullable|string|max:30',
            'line_of_business' => 'nullable|string|max:150',
            'first_name'       => 'nullable|string|max:120',
            'last_name'        => 'nullable|string|max:120',
            'middle_initial'   => 'nullable|string|max:5',
            'sex'              => 'nullable|string|in:Male,Female,Prefer not to say',
            'birthday'         => 'nullable|date|before:today',
            'id_type'          => 'nullable|string|max:100',
            'id_number'        => 'nullable|string|max:100',
            'id_photo'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'dti_permit'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
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

            // Name combination if first_name / last_name submitted
            $fullName = $user->name;
            if (!empty($data['first_name']) || !empty($data['last_name'])) {
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
            'phone'       => 'required|string|max:30',
            'line1'       => 'required|string|max:500',
            'barangay'    => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'province'    => 'required|string|max:255',
            'postal_code' => 'required|string|max:15',
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
