<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerPortalController extends Controller
{
    /**
     * Session-based mock data store for full interactive capability
     */
    private function getMockData(string $key, array $default): array
    {
        if (!session()->has('seller_' . $key)) {
            session(['seller_' . $key => $default]);
        }
        return session('seller_' . $key);
    }

    private function setMockData(string $key, array $data): void
    {
        session(['seller_' . $key => $data]);
    }

    /**
     * 1. Dashboard Overview (Stats, Charts, To-Do list, Recent Orders)
     */
    public function dashboard()
    {
        $seller = Auth::user();
        $products = $this->getProducts();
        $orders = $this->getOrders();

        $activeProductsCount = count(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'active'));
        $lowStockCount = count(array_filter($products, fn($p) => ($p['stock'] ?? 0) <= 10 && ($p['status'] ?? 'active') === 'active'));

        $toPackCount = count(array_filter($orders, fn($o) => in_array($o['status'], ['new', 'to_pack'])));
        $readyPickupCount = count(array_filter($orders, fn($o) => $o['status'] === 'ready_pickup'));
        $inTransitCount = count(array_filter($orders, fn($o) => $o['status'] === 'in_transit'));
        $completedCount = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
        $unpaidCount = count(array_filter($orders, fn($o) => ($o['payment_status'] ?? 'paid') === 'unpaid'));

        $grossSales = array_sum(array_map(fn($o) => in_array($o['status'], ['delivered', 'in_transit', 'ready_pickup', 'to_pack']) ? $o['total_amount'] : 0, $orders));
        $walletBalance = $this->getMockData('wallet_balance', ['balance' => 38450.00])['balance'];

        $stats = [
            'gross_sales' => $grossSales,
            'net_profit' => $grossSales * 0.90, // After 10% platform commission
            'wallet_balance' => $walletBalance,
            'to_pack' => $toPackCount,
            'ready_pickup' => $readyPickupCount,
            'in_transit' => $inTransitCount,
            'completed' => $completedCount,
            'unpaid' => $unpaidCount,
            'total_products' => $activeProductsCount,
            'low_stock' => $lowStockCount,
            'rating' => 4.9,
            'response_rate' => 98,
        ];

        // Sales Performance Chart Data (Last 7 Days)
        $chartData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'sales' => [12400, 18500, 14200, 22100, 19800, 28900, 31400],
            'orders' => [8, 12, 9, 15, 13, 20, 22],
        ];

        $recentOrders = array_slice($orders, 0, 5);

        // Notifications
        $notifications = [
            [
                'id' => 1,
                'title' => 'New Order #ORD-9042',
                'message' => 'Juan Dela Cruz ordered "Sony WH-1000XM5 Wireless Headphones". Please pack item.',
                'time' => '15 mins ago',
                'type' => 'order',
                'unread' => true,
                'link' => route('seller.orders', ['status' => 'new']),
            ],
            [
                'id' => 2,
                'title' => 'Courier Pickup Scheduled',
                'message' => 'Rider Arnel Gomez (cartzy Express) scheduled to pick up 3 parcels at 2:00 PM.',
                'time' => '1 hour ago',
                'type' => 'courier',
                'unread' => true,
                'link' => route('seller.courier'),
            ],
            [
                'id' => 3,
                'title' => 'Customer Confirmed Delivery',
                'message' => 'Elena Reyes confirmed receipt of Order #ORD-8821. ₱3,450.00 credited to wallet.',
                'time' => '3 hours ago',
                'type' => 'delivery',
                'unread' => false,
                'link' => route('seller.deliveries'),
            ],
            [
                'id' => 4,
                'title' => 'Low Stock Warning',
                'message' => 'Product "Logitech MX Master 3S Mouse" has only 4 units remaining.',
                'time' => 'Yesterday',
                'type' => 'inventory',
                'unread' => false,
                'link' => route('seller.inventory', ['tab' => 'low_stock']),
            ],
        ];

        return view('seller.dashboard', compact('seller', 'stats', 'chartData', 'recentOrders', 'notifications'));
    }

    /**
     * 2. Order Management: Orders & Notifications
     */
    public function orders(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $allOrders = $this->getOrders();

        if ($statusFilter !== 'all') {
            $filteredOrders = array_filter($allOrders, fn($o) => $o['status'] === $statusFilter);
        } else {
            $filteredOrders = $allOrders;
        }

        // Status counts for badge tabs
        $counts = [
            'all' => count($allOrders),
            'new' => count(array_filter($allOrders, fn($o) => $o['status'] === 'new')),
            'to_pack' => count(array_filter($allOrders, fn($o) => $o['status'] === 'to_pack')),
            'ready_pickup' => count(array_filter($allOrders, fn($o) => $o['status'] === 'ready_pickup')),
            'in_transit' => count(array_filter($allOrders, fn($o) => $o['status'] === 'in_transit')),
            'delivered' => count(array_filter($allOrders, fn($o) => $o['status'] === 'delivered')),
            'cancelled' => count(array_filter($allOrders, fn($o) => $o['status'] === 'cancelled')),
        ];

        return view('seller.orders', compact('filteredOrders', 'statusFilter', 'counts'));
    }

    /**
     * Prepare Order: Mark as Packed & Ready for Pickup
     */
    public function packOrder(Request $request, $id)
    {
        $orders = $this->getOrders();
        foreach ($orders as &$o) {
            if ($o['id'] == $id) {
                $o['status'] = 'ready_pickup';
                $o['packed_at'] = now()->format('M d, Y h:i A');
                $o['waybill_ready'] = true;
                break;
            }
        }
        $this->setMockData('orders', $orders);

        return back()->with('success', "Order #{$id} has been packed successfully! Airway Bill (AWB) waybill is ready to print.");
    }

    /**
     * Printable Waybill / Shipping Label (AWB) View
     */
    public function printWaybill($id)
    {
        $orders = $this->getOrders();
        $order = collect($orders)->firstWhere('id', $id) ?? $orders[0];

        return view('seller.waybill', compact('order'));
    }

    /**
     * 3. Courier Handover & Shipment Tracking
     */
    public function courier(Request $request)
    {
        $orders = $this->getOrders();
        // Orders ready for pickup or currently in transit
        $pickupReady = array_filter($orders, fn($o) => $o['status'] === 'ready_pickup');
        $inTransit = array_filter($orders, fn($o) => $o['status'] === 'in_transit');

        $courierPartners = [
            ['name' => 'cartzy Express Fleet', 'type' => 'Integrated Same-Day / Next-Day', 'badge' => 'Recommended', 'rating' => 4.9],
            ['name' => 'J&T Express Philippines', 'type' => 'Nationwide Standard Delivery', 'badge' => 'High Capacity', 'rating' => 4.8],
            ['name' => 'Flash Express PH', 'type' => 'Nationwide Express Shipping', 'badge' => 'Fast Dispatch', 'rating' => 4.7],
            ['name' => '2GO Logistics', 'type' => 'Sea & Air Heavy Cargo Cargo', 'badge' => 'Bulk Shipments', 'rating' => 4.6],
        ];

        return view('seller.courier', compact('pickupReady', 'inTransit', 'courierPartners'));
    }

    /**
     * Schedule Courier Pickup Action
     */
    public function schedulePickup(Request $request, $id)
    {
        $courierName = $request->input('courier_name', 'cartzy Express Fleet');
        $pickupSlot = $request->input('pickup_slot', 'Today, Afternoon (1:00 PM - 5:00 PM)');
        $riderNotes = $request->input('notes', 'Fragile electronic parcel. Please handle with care.');

        $orders = $this->getOrders();
        foreach ($orders as &$o) {
            if ($o['id'] == $id) {
                $o['status'] = 'in_transit';
                $o['courier_name'] = $courierName;
                $o['pickup_slot'] = $pickupSlot;
                $o['rider_name'] = 'Arnel Gomez (Express Rider)';
                $o['rider_phone'] = '+63 917 842 1928';
                $o['rider_plate'] = 'MC-8924-NCR';
                $o['dispatched_at'] = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('orders', $orders);

        return back()->with('success', "Pickup scheduled with {$courierName}! Order #{$id} is now handed over to the courier fleet.");
    }

    /**
     * 4. Confirm Delivery & Escrow Fund Release
     */
    public function deliveries()
    {
        $orders = $this->getOrders();
        $deliveredOrders = array_values(array_filter($orders, fn($o) => $o['status'] === 'delivered'));

        $totalDeliveredRevenue = array_sum(array_column($deliveredOrders, 'total_amount'));
        $totalNetEarnings = $totalDeliveredRevenue * 0.90; // 10% commission

        return view('seller.deliveries', compact('deliveredOrders', 'totalDeliveredRevenue', 'totalNetEarnings'));
    }

    /**
     * Simulate customer delivery confirmation
     */
    public function markDelivered(Request $request, $id)
    {
        $orders = $this->getOrders();
        $amount = 0;
        foreach ($orders as &$o) {
            if ($o['id'] == $id) {
                $o['status'] = 'delivered';
                $o['delivered_at'] = now()->format('M d, Y h:i A');
                $o['customer_confirmed'] = true;
                $amount = $o['total_amount'] * 0.90; // Net profit
                break;
            }
        }
        $this->setMockData('orders', $orders);

        // Credit to wallet
        $wallet = $this->getMockData('wallet_balance', ['balance' => 38450.00]);
        $wallet['balance'] += $amount;
        $this->setMockData('wallet_balance', $wallet);

        return back()->with('success', "Order #{$id} marked as delivered! Escrow payment of ₱" . number_format($amount, 2) . " has been successfully released to your Seller Wallet.");
    }

    /**
     * 5. Customer Feedback & Review Management
     */
    public function feedback(Request $request)
    {
        $ratingFilter = $request->query('rating', 'all');
        $reviews = $this->getReviews();

        if ($ratingFilter !== 'all') {
            $filteredReviews = array_filter($reviews, fn($r) => $r['rating'] == $ratingFilter);
        } else {
            $filteredReviews = $reviews;
        }

        $ratingCounts = [
            '5' => count(array_filter($reviews, fn($r) => $r['rating'] == 5)),
            '4' => count(array_filter($reviews, fn($r) => $r['rating'] == 4)),
            '3' => count(array_filter($reviews, fn($r) => $r['rating'] == 3)),
            '2' => count(array_filter($reviews, fn($r) => $r['rating'] == 2)),
            '1' => count(array_filter($reviews, fn($r) => $r['rating'] == 1)),
        ];

        return view('seller.feedback', compact('filteredReviews', 'ratingFilter', 'ratingCounts'));
    }

    /**
     * Post Seller Reply to Customer Feedback
     */
    public function replyFeedback(Request $request, $id)
    {
        $replyText = $request->input('reply');
        if (trim($replyText)) {
            $reviews = $this->getReviews();
            foreach ($reviews as &$r) {
                if ($r['id'] == $id) {
                    $r['seller_reply'] = $replyText;
                    $r['replied_at'] = now()->format('M d, Y');
                    break;
                }
            }
            $this->setMockData('reviews', $reviews);
        }

        return back()->with('success', 'Your seller response has been posted publicly to the buyer review.');
    }

    /**
     * 6. Manage Inventory (Products, Prices, Discounts, Vouchers, Stock levels)
     */
    public function inventory(Request $request)
    {
        $tab = $request->query('tab', 'all'); // 'all', 'active', 'low_stock', 'archived', 'vouchers'
        $products = $this->getProducts();
        $vouchers = $this->getVouchers();

        if ($tab === 'active') {
            $filteredProducts = array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'active');
        } elseif ($tab === 'low_stock') {
            $filteredProducts = array_filter($products, fn($p) => ($p['stock'] ?? 0) <= 10 && ($p['status'] ?? 'active') === 'active');
        } elseif ($tab === 'archived') {
            $filteredProducts = array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'archived');
        } else {
            $filteredProducts = $products;
        }

        $counts = [
            'all' => count($products),
            'active' => count(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'active')),
            'low_stock' => count(array_filter($products, fn($p) => ($p['stock'] ?? 0) <= 10 && ($p['status'] ?? 'active') === 'active')),
            'archived' => count(array_filter($products, fn($p) => ($p['status'] ?? 'active') === 'archived')),
            'vouchers' => count($vouchers),
        ];

        return view('seller.inventory', compact('filteredProducts', 'vouchers', 'tab', 'counts'));
    }

    /**
     * Add New Product
     */
    public function addProduct(Request $request)
    {
        $products = $this->getProducts();
        $newProduct = [
            'id' => count($products) + 101,
            'name' => $request->input('name', 'New Store Item'),
            'category' => $request->input('category', 'Electronics & Gadgets'),
            'sku' => $request->input('sku', 'SKU-' . strtoupper(substr(uniqid(), -5))),
            'price' => (float) $request->input('price', 999.00),
            'original_price' => (float) $request->input('original_price', $request->input('price', 999.00)),
            'stock' => (int) $request->input('stock', 25),
            'status' => 'active',
            'image' => $request->input('image') ?: 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&auto=format&fit=crop&q=80',
            'sales_count' => 0,
            'description' => $request->input('description', 'Authentic premium tech accessory with official brand warranty.'),
            'rating' => 5.0,
            'created_at' => now()->format('M d, Y'),
        ];

        array_unshift($products, $newProduct);
        $this->setMockData('products', $products);

        return back()->with('success', "Product '{$newProduct['name']}' has been added to your inventory successfully!");
    }

    /**
     * Update Product Details & Stock
     */
    public function updateProduct(Request $request, $id)
    {
        $products = $this->getProducts();
        foreach ($products as &$p) {
            if ($p['id'] == $id) {
                $p['name'] = $request->input('name', $p['name']);
                $p['price'] = (float) $request->input('price', $p['price']);
                $p['original_price'] = (float) $request->input('original_price', $p['original_price']);
                $p['stock'] = (int) $request->input('stock', $p['stock']);
                $p['category'] = $request->input('category', $p['category']);
                if ($request->filled('description')) {
                    $p['description'] = $request->input('description');
                }
                break;
            }
        }
        $this->setMockData('products', $products);

        return back()->with('success', "Product #{$id} has been updated successfully.");
    }

    /**
     * Archive or Unarchive Product
     */
    public function toggleArchiveProduct($id)
    {
        $products = $this->getProducts();
        $newStatus = 'active';
        foreach ($products as &$p) {
            if ($p['id'] == $id) {
                $p['status'] = ($p['status'] ?? 'active') === 'archived' ? 'active' : 'archived';
                $newStatus = $p['status'];
                break;
            }
        }
        $this->setMockData('products', $products);

        $msg = $newStatus === 'archived' ? "Product #{$id} has been moved to Archive." : "Product #{$id} restored to Active status.";
        return back()->with('success', $msg);
    }

    /**
     * Create Shop Voucher / Discount Promotion
     */
    public function addVoucher(Request $request)
    {
        $vouchers = $this->getVouchers();
        $newVoucher = [
            'id' => count($vouchers) + 1,
            'code' => strtoupper(trim($request->input('code', 'DISC100'))),
            'discount_type' => $request->input('discount_type', 'fixed'), // 'fixed' or 'percent'
            'discount_value' => (float) $request->input('discount_value', 100),
            'min_spend' => (float) $request->input('min_spend', 1000),
            'valid_until' => $request->input('valid_until', now()->addDays(30)->format('Y-m-d')),
            'usage_limit' => (int) $request->input('usage_limit', 50),
            'used_count' => 0,
            'status' => 'active',
        ];

        array_unshift($vouchers, $newVoucher);
        $this->setMockData('vouchers', $vouchers);

        return back()->with('success', "Shop Voucher '{$newVoucher['code']}' created successfully!");
    }

    /**
     * 7. Generate Report (Financial, Profit, Date Pickers, Sales Performance)
     */
    public function reports(Request $request)
    {
        // Date Pickers as required: "date picker as to from and to date"
        $fromDate = $request->query('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->query('to_date', now()->format('Y-m-d'));
        $preset = $request->query('preset', 'custom');

        // Filter sample transaction history based on dates
        $allTransactions = $this->getTransactionHistory();
        
        $filteredTransactions = array_filter($allTransactions, function ($t) use ($fromDate, $toDate) {
            $tDate = date('Y-m-d', strtotime($t['date']));
            return $tDate >= $fromDate && $tDate <= $toDate;
        });

        if (empty($filteredTransactions)) {
            $filteredTransactions = $allTransactions; // Graceful fallback
        }

        $grossSales = array_sum(array_column($filteredTransactions, 'order_amount'));
        $platformCommission = $grossSales * 0.10; // 10% Platform fee
        $netProfit = $grossSales - $platformCommission;
        $totalOrdersCount = count($filteredTransactions);
        $avgOrderValue = $totalOrdersCount > 0 ? $grossSales / $totalOrdersCount : 0;

        $reportSummary = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'preset' => $preset,
            'gross_sales' => $grossSales,
            'platform_commission' => $platformCommission,
            'net_profit' => $netProfit,
            'total_orders' => $totalOrdersCount,
            'avg_order_value' => $avgOrderValue,
            'shipping_subsidies' => 1250.00,
            'refund_deductions' => 0.00,
        ];

        $topProducts = [
            ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'category' => 'Audio', 'units' => 48, 'revenue' => 105600.00, 'commission' => 10560.00, 'net' => 95040.00],
            ['name' => 'Logitech MX Master 3S Wireless Mouse', 'category' => 'Accessories', 'units' => 35, 'revenue' => 20965.00, 'commission' => 2096.50, 'net' => 18868.50],
            ['name' => 'Apple iPad 10th Gen (Wi-Fi 64GB)', 'category' => 'Tablets', 'units' => 14, 'revenue' => 348600.00, 'commission' => 34860.00, 'net' => 313740.00],
            ['name' => 'Anker 737 Power Bank 24,000mAh', 'category' => 'Charging', 'units' => 29, 'revenue' => 31900.00, 'commission' => 3190.00, 'net' => 28710.00],
            ['name' => 'Keychron K2 V2 Wireless Keyboard', 'category' => 'Keyboards', 'units' => 22, 'revenue' => 19800.00, 'commission' => 1980.00, 'net' => 17820.00],
        ];

        return view('seller.reports', compact('reportSummary', 'filteredTransactions', 'topProducts'));
    }

    /**
     * 8. Chat / Messaging (Buyer Inquiries & Support)
     */
    public function chat(Request $request)
    {
        $activeContactId = (int) $request->query('contact', 1);

        $contacts = [
            [
                'id' => 1,
                'name' => 'Juan Dela Cruz',
                'role' => 'Buyer',
                'avatar' => '👨‍💼',
                'status' => 'online',
                'item_inquiry' => 'Sony WH-1000XM5 Wireless Headphones',
                'item_price' => '₱2,200.00',
                'last_message' => 'Hi seller! Is the black color available on hand for immediate dispatch?',
                'time' => '10:14 AM',
                'unread' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Elena Reyes',
                'role' => 'Buyer',
                'avatar' => '👩‍🦰',
                'status' => 'online',
                'item_inquiry' => 'Apple iPad 10th Gen',
                'item_price' => '₱24,900.00',
                'last_message' => 'Thank you! The package arrived in great condition with bubble wrap.',
                'time' => '08:45 AM',
                'unread' => 0,
            ],
            [
                'id' => 3,
                'name' => 'cartzy Support Specialist',
                'role' => 'Admin Support',
                'avatar' => '🛡️',
                'status' => 'online',
                'item_inquiry' => 'Seller Growth & Payday Campaign',
                'item_price' => 'Free Promotion',
                'last_message' => 'Congratulations! Your shop qualified for the Featured Merchant Payday Banner.',
                'time' => 'Yesterday',
                'unread' => 0,
            ],
            [
                'id' => 4,
                'name' => 'Carlos Mendoza',
                'role' => 'Buyer',
                'avatar' => '👨‍🦱',
                'status' => 'offline',
                'item_inquiry' => 'Logitech MX Master 3S',
                'item_price' => '₱5,990.00',
                'last_message' => 'Can you include an official BIR Sales Invoice receipt in the box?',
                'time' => '2 days ago',
                'unread' => 0,
            ],
        ];

        $currentContact = collect($contacts)->firstWhere('id', $activeContactId) ?? $contacts[0];

        $messages = $this->getMockData('chat_' . $activeContactId, [
            [
                'sender' => 'contact',
                'text' => $currentContact['last_message'],
                'time' => $currentContact['time'],
            ],
            [
                'sender' => 'seller',
                'text' => 'Hello! Yes po, all our items are 100% authentic, brand new, and ready to ship with official warranty.',
                'time' => 'Just now',
            ],
        ]);

        return view('seller.chat', compact('contacts', 'currentContact', 'messages'));
    }

    /**
     * Send Chat Message
     */
    public function sendMessage(Request $request, $contactId)
    {
        $text = $request->input('message');
        if (trim($text)) {
            $messages = $this->getMockData('chat_' . $contactId, []);
            $messages[] = [
                'sender' => 'seller',
                'text' => $text,
                'time' => now()->format('h:i A'),
            ];
            $this->setMockData('chat_' . $contactId, $messages);
        }

        return back()->with('success', 'Message sent to customer.');
    }

    /**
     * 9. Account Management (Store Profile, Pickup Address, Payout Bank)
     */
    public function account()
    {
        $seller = Auth::user();

        $storeProfile = $this->getMockData('profile', [
            'store_name' => 'TechZone Gadgets & Audio Store',
            'slug' => 'techzone-gadgets',
            'description' => 'Official authorized retailer of authentic audio gear, laptops, computer accessories, and consumer gadgets. 100% genuine with local warranty.',
            'email' => 'seller@marketstore.ph',
            'phone' => '+63 917 555 8899',
            'business_type' => 'Retail / Consumer Electronics',
            'vacation_mode' => false,
        ]);

        $pickupAddress = $this->getMockData('pickup_address', [
            'contact_person' => 'Maria Santos (Store Manager)',
            'phone' => '+63 917 555 8899',
            'street' => 'Unit 402, High Street Plaza, 28th St.',
            'barangay' => 'Fort Bonifacio',
            'city' => 'Taguig City',
            'province' => 'Metro Manila',
            'postal_code' => '1634',
        ]);

        $payoutInfo = $this->getMockData('payout_info', [
            'bank_name' => 'GCash / BDO Unibank',
            'account_name' => 'Maria Santos',
            'account_number' => '0917-555-8899',
            'available_balance' => $this->getMockData('wallet_balance', ['balance' => 38450.00])['balance'],
        ]);

        return view('seller.account', compact('seller', 'storeProfile', 'pickupAddress', 'payoutInfo'));
    }

    /**
     * Update Store Profile Information
     */
    public function updateProfile(Request $request)
    {
        $profile = [
            'store_name' => $request->input('store_name', 'TechZone Store'),
            'slug' => $request->input('slug', 'techzone-store'),
            'description' => $request->input('description', ''),
            'email' => $request->input('email', 'seller@marketstore.ph'),
            'phone' => $request->input('phone', '+63 917 555 8899'),
            'business_type' => $request->input('business_type', 'Electronics'),
            'vacation_mode' => $request->has('vacation_mode'),
        ];
        $this->setMockData('profile', $profile);

        return back()->with('success', 'Store Profile and Operating Status updated successfully!');
    }

    /**
     * Update Warehouse / Courier Pickup Address
     */
    public function updateAddress(Request $request)
    {
        $address = [
            'contact_person' => $request->input('contact_person'),
            'phone' => $request->input('phone'),
            'street' => $request->input('street'),
            'barangay' => $request->input('barangay'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'postal_code' => $request->input('postal_code'),
        ];
        $this->setMockData('pickup_address', $address);

        return back()->with('success', 'Pickup & Warehouse address updated for courier dispatches.');
    }

    /**
     * Request Wallet Withdrawal Payout
     */
    public function withdrawFunds(Request $request)
    {
        $amount = (float) $request->input('amount', 5000);
        $wallet = $this->getMockData('wallet_balance', ['balance' => 38450.00]);

        if ($amount <= 0 || $amount > $wallet['balance']) {
            return back()->withErrors(['amount' => 'Invalid withdrawal amount requested.']);
        }

        $wallet['balance'] -= $amount;
        $this->setMockData('wallet_balance', $wallet);

        return back()->with('success', "Withdrawal of ₱" . number_format($amount, 2) . " to your registered payout account has been submitted! Payout processed within 24 hours.");
    }

    // ==========================================
    // SEED / MOCK DATA DATASETS
    // ==========================================

    private function getProducts(): array
    {
        return $this->getMockData('products', [
            [
                'id' => 1,
                'name' => 'Sony WH-1000XM5 Wireless Noise-Cancelling Headphones',
                'category' => 'Electronics & Audio',
                'sku' => 'SNY-WH5-BLK',
                'price' => 2200.00,
                'original_price' => 2800.00,
                'stock' => 18,
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 84,
                'description' => 'Industry-leading noise cancellation, crystal clear hands-free calling, up to 30-hour battery life.',
                'rating' => 4.9,
            ],
            [
                'id' => 2,
                'name' => 'Logitech MX Master 3S Wireless Performance Mouse',
                'category' => 'Computer Accessories',
                'sku' => 'LOG-MX3S-GRY',
                'price' => 5990.00,
                'original_price' => 6490.00,
                'stock' => 4, // Low stock alert
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 42,
                'description' => '8K DPI any-surface tracking, quiet clicks, ergonomic palm support with MagSpeed electromagnetic scrolling.',
                'rating' => 4.8,
            ],
            [
                'id' => 3,
                'name' => 'Apple iPad 10th Generation (Wi-Fi, 64GB) - Silver',
                'category' => 'Tablets & Mobile',
                'sku' => 'APL-IPD10-SLV',
                'price' => 24900.00,
                'original_price' => 26900.00,
                'stock' => 12,
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 26,
                'description' => 'Striking 10.9-inch Liquid Retina display, A14 Bionic chip, 12MP Ultra Wide front camera.',
                'rating' => 5.0,
            ],
            [
                'id' => 4,
                'name' => 'Keychron K2 V2 Wireless Mechanical Keyboard',
                'category' => 'Computer Accessories',
                'sku' => 'KCH-K2-BRN',
                'price' => 4490.00,
                'original_price' => 4990.00,
                'stock' => 7, // Low stock alert
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 39,
                'description' => '75% compact layout, hot-swappable Gateron G Pro Brown switches with Mac & Windows layout support.',
                'rating' => 4.9,
            ],
            [
                'id' => 5,
                'name' => 'Anker 737 Power Bank (PowerCore 24K, 140W)',
                'category' => 'Mobile Accessories',
                'sku' => 'ANK-737-140W',
                'price' => 6490.00,
                'original_price' => 6990.00,
                'stock' => 15,
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1609592424368-45a73e654e99?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 61,
                'description' => 'Ultra-powerful 140W two-way fast charging with smart digital display and massive 24,000mAh capacity.',
                'rating' => 4.9,
            ],
            [
                'id' => 6,
                'name' => 'Sandisk Extreme Portable SSD 1TB (USB 3.2 Gen 2)',
                'category' => 'Storage',
                'sku' => 'SND-EXT-1TB',
                'price' => 5250.00,
                'original_price' => 5800.00,
                'stock' => 22,
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 53,
                'description' => 'Tough, rugged SSD with up to 1050MB/s read speeds, IP55 water and dust resistance.',
                'rating' => 4.7,
            ],
            [
                'id' => 7,
                'name' => 'Discontinued Audio Cable 3.5mm (Legacy Batch)',
                'category' => 'Accessories',
                'sku' => 'LEG-AUD-35',
                'price' => 150.00,
                'original_price' => 200.00,
                'stock' => 0,
                'status' => 'archived',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&auto=format&fit=crop&q=80',
                'sales_count' => 120,
                'description' => 'Archived stock item.',
                'rating' => 4.2,
            ],
        ]);
    }

    private function getOrders(): array
    {
        return $this->getMockData('orders', [
            [
                'id' => 'ORD-9042',
                'date' => 'Today, 02:40 PM',
                'buyer_name' => 'Juan Dela Cruz',
                'buyer_phone' => '+63 917 123 4567',
                'shipping_address' => 'Unit 12B, Tower 1, Avida Towers BGC, Taguig City, Metro Manila (1634)',
                'items' => [
                    ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'variant' => 'Midnight Black', 'qty' => 1, 'price' => 2200.00, 'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 2200.00,
                'shipping_fee' => 75.00,
                'voucher_discount' => 100.00,
                'total_amount' => 2175.00,
                'payment_method' => 'GCash E-Wallet',
                'payment_status' => 'paid',
                'status' => 'new', // Needs packing
                'courier_name' => 'cartzy Express',
                'tracking_number' => 'CTZ-PH-9042101',
                'waybill_ready' => false,
            ],
            [
                'id' => 'ORD-9038',
                'date' => 'Today, 11:20 AM',
                'buyer_name' => 'Samantha Mae Lim',
                'buyer_phone' => '+63 928 987 6543',
                'shipping_address' => 'House 45, Mahogany St., Valle Verde 3, Pasig City (1605)',
                'items' => [
                    ['name' => 'Logitech MX Master 3S Wireless Mouse', 'variant' => 'Pale Gray', 'qty' => 1, 'price' => 5990.00, 'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=200&auto=format&fit=crop&q=80'],
                    ['name' => 'Keychron K2 V2 Wireless Keyboard', 'variant' => 'Brown Switch / RGB', 'qty' => 1, 'price' => 4490.00, 'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 10480.00,
                'shipping_fee' => 0.00,
                'voucher_discount' => 500.00,
                'total_amount' => 9980.00,
                'payment_method' => 'Maya / Credit Card',
                'payment_status' => 'paid',
                'status' => 'to_pack', // In preparation
                'courier_name' => 'Flash Express PH',
                'tracking_number' => 'FLS-PH-9038822',
                'waybill_ready' => true,
            ],
            [
                'id' => 'ORD-9012',
                'date' => 'Yesterday, 04:15 PM',
                'buyer_name' => 'Robert Tan',
                'buyer_phone' => '+63 918 333 4455',
                'shipping_address' => 'Lot 8 Block 2, BF Homes, Parañaque City (1700)',
                'items' => [
                    ['name' => 'Anker 737 Power Bank 24,000mAh', 'variant' => 'Matte Black', 'qty' => 1, 'price' => 6490.00, 'image' => 'https://images.unsplash.com/photo-1609592424368-45a73e654e99?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 6490.00,
                'shipping_fee' => 85.00,
                'voucher_discount' => 0.00,
                'total_amount' => 6575.00,
                'payment_method' => 'Cash on Delivery (COD)',
                'payment_status' => 'cod_pending',
                'status' => 'ready_pickup', // Packed, waiting courier
                'courier_name' => 'J&T Express Philippines',
                'tracking_number' => 'JNT-PH-9012399',
                'waybill_ready' => true,
            ],
            [
                'id' => 'ORD-8995',
                'date' => 'Oct 01, 2026',
                'buyer_name' => 'Patricia Santos',
                'buyer_phone' => '+63 917 444 8811',
                'shipping_address' => '22 Orchid St., Capitol Hills, Quezon City (1126)',
                'items' => [
                    ['name' => 'Apple iPad 10th Generation (Wi-Fi, 64GB)', 'variant' => 'Silver', 'qty' => 1, 'price' => 24900.00, 'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 24900.00,
                'shipping_fee' => 0.00,
                'voucher_discount' => 1000.00,
                'total_amount' => 23900.00,
                'payment_method' => 'GCash E-Wallet',
                'payment_status' => 'paid',
                'status' => 'in_transit', // Handed over to rider
                'courier_name' => 'cartzy Express Fleet',
                'tracking_number' => 'CTZ-PH-8995001',
                'rider_name' => 'Arnel Gomez (Express Rider)',
                'rider_phone' => '+63 917 842 1928',
                'rider_plate' => 'MC-8924-NCR',
                'waybill_ready' => true,
            ],
            [
                'id' => 'ORD-8821',
                'date' => 'Sep 29, 2026',
                'buyer_name' => 'Elena Reyes',
                'buyer_phone' => '+63 920 777 9900',
                'shipping_address' => 'Apt 4B, Greenbelt Mansions, Legazpi Village, Makati City (1229)',
                'items' => [
                    ['name' => 'Sandisk Extreme Portable SSD 1TB', 'variant' => 'Standard Black/Orange', 'qty' => 1, 'price' => 5250.00, 'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 5250.00,
                'shipping_fee' => 50.00,
                'voucher_discount' => 150.00,
                'total_amount' => 5150.00,
                'payment_method' => 'Maya / Credit Card',
                'payment_status' => 'paid',
                'status' => 'delivered', // Confirmed received
                'delivered_at' => 'Sep 30, 2026 03:15 PM',
                'courier_name' => 'cartzy Express Fleet',
                'tracking_number' => 'CTZ-PH-8821094',
                'customer_confirmed' => true,
                'waybill_ready' => true,
            ],
            [
                'id' => 'ORD-8740',
                'date' => 'Sep 27, 2026',
                'buyer_name' => 'Mark Kenneth Cruz',
                'buyer_phone' => '+63 908 665 1122',
                'shipping_address' => '15 Rosal St., Ayala Alabang Village, Muntinlupa (1780)',
                'items' => [
                    ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'variant' => 'Silver / Platinum', 'qty' => 1, 'price' => 2200.00, 'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=200&auto=format&fit=crop&q=80']
                ],
                'subtotal' => 2200.00,
                'shipping_fee' => 60.00,
                'voucher_discount' => 0.00,
                'total_amount' => 2260.00,
                'payment_method' => 'GCash',
                'payment_status' => 'paid',
                'status' => 'delivered',
                'delivered_at' => 'Sep 28, 2026 01:45 PM',
                'courier_name' => 'J&T Express Philippines',
                'tracking_number' => 'JNT-PH-8740112',
                'customer_confirmed' => true,
                'waybill_ready' => true,
            ],
        ]);
    }

    private function getVouchers(): array
    {
        return $this->getMockData('vouchers', [
            [
                'id' => 1,
                'code' => 'TECHZONE100',
                'discount_type' => 'fixed',
                'discount_value' => 100.00,
                'min_spend' => 1000.00,
                'valid_until' => '2026-10-31',
                'usage_limit' => 100,
                'used_count' => 42,
                'status' => 'active',
            ],
            [
                'id' => 2,
                'code' => 'PAYDAY10',
                'discount_type' => 'percent',
                'discount_value' => 10.00,
                'min_spend' => 3000.00,
                'valid_until' => '2026-10-15',
                'usage_limit' => 50,
                'used_count' => 18,
                'status' => 'active',
            ],
            [
                'id' => 3,
                'code' => 'WELCOMEVIP',
                'discount_type' => 'fixed',
                'discount_value' => 250.00,
                'min_spend' => 5000.00,
                'valid_until' => '2026-11-30',
                'usage_limit' => 30,
                'used_count' => 9,
                'status' => 'active',
            ],
        ]);
    }

    private function getReviews(): array
    {
        return $this->getMockData('reviews', [
            [
                'id' => 1,
                'buyer_name' => 'Elena Reyes',
                'buyer_avatar' => '👩‍🦰',
                'product_name' => 'Sony WH-1000XM5 Wireless Headphones',
                'product_variant' => 'Midnight Black',
                'rating' => 5,
                'date' => 'Yesterday',
                'comment' => 'Napaka ganda ng sound quality! Super legit product, packed with thick layers of bubble wrap. Active noise cancellation is 10/10. Will definitely order again from this shop!',
                'seller_reply' => 'Salamat po nang marami Mam Elena! We are delighted that you love the sound quality. Enjoy your new headphones po!',
                'replied_at' => 'Yesterday',
                'verified_purchase' => true,
            ],
            [
                'id' => 2,
                'buyer_name' => 'Juan Dela Cruz',
                'buyer_avatar' => '👨‍💼',
                'product_name' => 'Logitech MX Master 3S Wireless Mouse',
                'product_variant' => 'Pale Gray',
                'rating' => 5,
                'date' => '3 days ago',
                'comment' => 'Very ergonomic for long office programming sessions. MagSpeed wheel is amazing. Fast courier handover as well, arrived within 24 hours in Taguig.',
                'seller_reply' => 'Thank you for trusting TechZone, Sir Juan! Glad the ergonomic shape helps with your daily workflow.',
                'replied_at' => '2 days ago',
                'verified_purchase' => true,
            ],
            [
                'id' => 3,
                'buyer_name' => 'Patricia Santos',
                'buyer_avatar' => '👩',
                'product_name' => 'Keychron K2 V2 Wireless Keyboard',
                'product_variant' => 'Brown Switch / RGB',
                'rating' => 4,
                'date' => 'Sep 26, 2026',
                'comment' => 'Keycaps and switches feel really satisfying. Connects smoothly to both my Mac and iPad. Minus 1 star only because J&T rider arrived late in the evening, but seller packed it promptly.',
                'seller_reply' => null, // Pending reply
                'replied_at' => null,
                'verified_purchase' => true,
            ],
            [
                'id' => 4,
                'buyer_name' => 'Carlos Mendoza',
                'buyer_avatar' => '👨‍🦱',
                'product_name' => 'Anker 737 Power Bank 24,000mAh',
                'product_variant' => 'Matte Black',
                'rating' => 5,
                'date' => 'Sep 20, 2026',
                'comment' => 'Charges my MacBook Pro at 140W full speed! The smart screen showing wattage input and output is super cool and handy.',
                'seller_reply' => 'Awesome to hear! That Anker 737 is truly a beast of a power bank. Thank you for shopping with us!',
                'replied_at' => 'Sep 21, 2026',
                'verified_purchase' => true,
            ],
        ]);
    }

    private function getTransactionHistory(): array
    {
        return [
            ['id' => 'TXN-9042', 'date' => '2026-10-02', 'order_id' => 'ORD-9042', 'customer' => 'Juan Dela Cruz', 'item' => 'Sony WH-1000XM5 Headphones', 'order_amount' => 2175.00, 'platform_fee' => 217.50, 'seller_net' => 1957.50, 'status' => 'settled'],
            ['id' => 'TXN-9038', 'date' => '2026-10-02', 'order_id' => 'ORD-9038', 'customer' => 'Samantha Mae Lim', 'item' => 'Logitech Mouse + Keychron K2', 'order_amount' => 9980.00, 'platform_fee' => 998.00, 'seller_net' => 8982.00, 'status' => 'settled'],
            ['id' => 'TXN-9012', 'date' => '2026-10-01', 'order_id' => 'ORD-9012', 'customer' => 'Robert Tan', 'item' => 'Anker 737 Power Bank 24K', 'order_amount' => 6575.00, 'platform_fee' => 657.50, 'seller_net' => 5917.50, 'status' => 'settled'],
            ['id' => 'TXN-8995', 'date' => '2026-10-01', 'order_id' => 'ORD-8995', 'customer' => 'Patricia Santos', 'item' => 'Apple iPad 10th Gen 64GB', 'order_amount' => 23900.00, 'platform_fee' => 2390.00, 'seller_net' => 21510.00, 'status' => 'settled'],
            ['id' => 'TXN-8821', 'date' => '2026-09-29', 'order_id' => 'ORD-8821', 'customer' => 'Elena Reyes', 'item' => 'Sandisk Extreme Portable SSD', 'order_amount' => 5150.00, 'platform_fee' => 515.00, 'seller_net' => 4635.00, 'status' => 'settled'],
            ['id' => 'TXN-8740', 'date' => '2026-09-27', 'order_id' => 'ORD-8740', 'customer' => 'Mark Kenneth Cruz', 'item' => 'Sony WH-1000XM5 Platinum', 'order_amount' => 2260.00, 'platform_fee' => 226.00, 'seller_net' => 2034.00, 'status' => 'settled'],
            ['id' => 'TXN-8690', 'date' => '2026-09-24', 'order_id' => 'ORD-8690', 'customer' => 'Danielle Uy', 'item' => 'Logitech MX Master 3S', 'order_amount' => 5990.00, 'platform_fee' => 599.00, 'seller_net' => 5391.00, 'status' => 'settled'],
            ['id' => 'TXN-8622', 'date' => '2026-09-20', 'order_id' => 'ORD-8622', 'customer' => 'Carlos Mendoza', 'item' => 'Anker 737 Power Bank', 'order_amount' => 6490.00, 'platform_fee' => 649.00, 'seller_net' => 5841.00, 'status' => 'settled'],
            ['id' => 'TXN-8510', 'date' => '2026-09-15', 'order_id' => 'ORD-8510', 'customer' => 'Jerome Ramos', 'item' => 'Keychron K2 V2 Keyboard', 'order_amount' => 4490.00, 'platform_fee' => 449.00, 'seller_net' => 4041.00, 'status' => 'settled'],
            ['id' => 'TXN-8401', 'date' => '2026-09-10', 'order_id' => 'ORD-8401', 'customer' => 'Bea Alonzo', 'item' => 'Apple iPad 10th Gen 64GB', 'order_amount' => 24900.00, 'platform_fee' => 2490.00, 'seller_net' => 22410.00, 'status' => 'settled'],
        ];
    }
}
