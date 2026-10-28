<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\DeliveryEvent;
use App\Models\LogisticsMessage;
use App\Models\LogisticsProvider;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\SellerMessage;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public const RIDER_FEE_PER_DELIVERY = 65.00; // ₱65.00 base delivery commission per package

    private function getOrCreateRider(): Rider
    {
        $user = Auth::user();
        $rider = $user->rider;

        if (!$rider) {
            $provider = LogisticsProvider::where('status', 'approved')->first() ?? LogisticsProvider::first();
            $rider = Rider::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'logistics_provider_id' => $provider?->id ?? 1,
                    'vehicle_type'          => 'Motorcycle',
                    'plate_no'              => 'NQ-4812',
                    'status'                => 'approved',
                    'area'                  => 'Metro South Sector',
                    'rating'                => 4.95,
                    'deliveries_today'      => 0,
                    'is_active'             => true,
                ]
            );
        }

        return $rider;
    }

    /**
     * 1. Main Hub / Command Center Overview
     */
    public function index()
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        // Active delivery queue assigned to rider or available in area
        $activeShipments = Shipment::with([
                'sellerOrder.order.buyer',
                'sellerOrder.items',
                'sellerOrder.seller',
                'logisticsProvider'
            ])
            ->where(function ($q) use ($rider) {
                $q->where('rider_id', $rider->id)
                  ->orWhereNull('rider_id');
            })
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->latest()
            ->take(6)
            ->get();

        // Available pickup requests from sellers in sector
        $availablePickups = PickupRequest::with(['seller.pickupAddress', 'seller.user'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('pickup_date', '>=', today()->subDays(1))
            ->latest()
            ->take(4)
            ->get();

        // Shipments delivered today by this rider
        $deliveredToday = Shipment::with([
                'sellerOrder.order.buyer',
                'sellerOrder.items',
                'sellerOrder.seller'
            ])
            ->where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->latest('updated_at')
            ->get();

        $codCollectedToday = $deliveredToday
            ->where('cod_collected', true)
            ->sum(function ($s) {
                return $s->cod_amount_minor ?: $s->sellerOrder->buyerDueMinor();
            }) / 100;

        $stats = [
            'assigned_pickups'    => PickupRequest::where('status', 'pending')->count(),
            'out_for_delivery'    => $activeShipments->count(),
            'delivered_today'     => $deliveredToday->count(),
            'cod_collected_today' => $codCollectedToday,
            'earnings_today'      => $deliveredToday->count() * self::RIDER_FEE_PER_DELIVERY,
            'rider_rating'        => $rider->rating ?? 4.95,
            'is_online'           => (bool) $rider->is_active,
        ];

        return view('courier.dashboard', compact(
            'courier',
            'rider',
            'stats',
            'activeShipments',
            'availablePickups',
            'deliveredToday'
        ));
    }

    /**
     * 2. View Items for Pickup
     */
    public function pickups(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $statusFilter = $request->query('status', 'all');

        $query = PickupRequest::with(['seller.pickupAddress', 'seller.user', 'logisticsProvider'])
            ->where('logistics_provider_id', $rider->logistics_provider_id);

        if ($statusFilter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'confirmed') {
            $query->where('status', 'confirmed');
        }

        $pickups = $query->latest('pickup_date')->paginate(12)->withQueryString();

        // Ready to ship merchant shipments
        $readyShipments = Shipment::with(['sellerOrder.seller', 'sellerOrder.items', 'sellerOrder.order.buyer'])
            ->whereIn('status', ['unassigned', 'assigned'])
            ->where('logistics_provider_id', $rider->logistics_provider_id)
            ->latest()
            ->take(10)
            ->get();

        $pendingCount = PickupRequest::where('status', 'pending')->count();
        $confirmedCount = PickupRequest::where('status', 'confirmed')->count();

        return view('courier.pickups', compact(
            'courier',
            'rider',
            'pickups',
            'readyShipments',
            'statusFilter',
            'pendingCount',
            'confirmedCount'
        ));
    }

    /**
     * Accept & Confirm Pickup from Merchant Warehouse
     */
    public function acceptPickup(Request $request, int $id)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $pickup = PickupRequest::findOrFail($id);

        DB::transaction(function () use ($pickup, $rider, $courier) {
            $pickup->update([
                'rider_id'     => $rider->id,
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // If there are unassigned shipments for this seller, assign them to rider and mark as picked up
            $shipments = Shipment::whereHas('sellerOrder', fn ($q) => $q->where('seller_id', $pickup->seller_id))
                ->whereIn('status', ['unassigned', 'assigned'])
                ->get();

            foreach ($shipments as $shipment) {
                $shipment->update([
                    'rider_id' => $rider->id,
                    'status'   => 'picked_up',
                ]);

                $shipment->sellerOrder?->update(['status' => 'shipped']);

                DeliveryEvent::create([
                    'shipment_id' => $shipment->id,
                    'status'      => 'picked_up',
                    'attempt'     => 1,
                    'user_id'     => $courier->id,
                    'note'        => 'Picked up from seller warehouse (' . ($pickup->seller?->name ?? 'Merchant') . ') by rider ' . $courier->name . '.',
                    'occurred_at' => now(),
                ]);
            }

            // Notify merchant seller
            if ($pickup->seller?->user_id) {
                NotificationService::notify(
                    $pickup->seller->user_id,
                    'pickup_accepted',
                    'Rider Assigned for Pickup',
                    'Courier rider ' . $courier->name . ' has accepted your pickup request (' . $pickup->parcel_count . ' parcels). ETA: Within 30 minutes.',
                    route('seller.courier')
                );
            }
        });

        return back()->with('success', 'Merchant pickup accepted! Parcels have been collected and added to your active route.');
    }

    /**
     * 3. View Items for Delivery (Queue)
     */
    public function deliveries(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $search = trim($request->query('search', ''));
        $paymentFilter = $request->query('payment', 'all');

        $query = Shipment::with([
                'sellerOrder.order.buyer',
                'sellerOrder.items',
                'sellerOrder.seller',
                'logisticsProvider'
            ])
            ->where(function ($q) use ($rider) {
                $q->where('rider_id', $rider->id)
                  ->orWhereNull('rider_id');
            })
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhereHas('sellerOrder.order', fn ($o) => $o->where('reference', 'like', "%{$search}%"))
                  ->orWhereHas('sellerOrder.order.buyer', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        if ($paymentFilter === 'cod') {
            $query->whereHas('sellerOrder.order', fn ($o) => $o->where('payment_method', 'cod'));
        } elseif ($paymentFilter === 'prepaid') {
            $query->whereHas('sellerOrder.order', fn ($o) => $o->where('payment_method', '!=', 'cod'));
        }

        $deliveries = $query->latest()->paginate(15)->withQueryString();

        $totalActive = Shipment::where(function ($q) use ($rider) {
                $q->where('rider_id', $rider->id)->orWhereNull('rider_id');
            })
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->count();

        $totalCodToCollect = Shipment::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
            ->whereHas('sellerOrder.order', fn ($o) => $o->where('payment_method', 'cod'))
            ->sum('cod_amount_minor') / 100;

        return view('courier.deliveries', compact(
            'courier',
            'rider',
            'deliveries',
            'search',
            'paymentFilter',
            'totalActive',
            'totalCodToCollect'
        ));
    }

    /**
     * Barcode & QR Code Search
     */
    public function scan(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:100',
        ]);

        $code = trim($request->input('tracking_code'));

        $shipment = Shipment::where('tracking_code', $code)
            ->orWhereHas('sellerOrder.order', fn ($q) => $q->where('reference', $code))
            ->with([
                'sellerOrder.order.buyer',
                'sellerOrder.items',
                'sellerOrder.seller.pickupAddress',
                'logisticsProvider'
            ])
            ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'No parcel found matching tracking code or barcode: ' . $code,
            ], 404);
        }

        $sellerOrder = $shipment->sellerOrder;
        $order = $sellerOrder->order;
        $shippingAddress = $order->shipping_address ?? [];
        $recipientAddress = $shippingAddress['address'] ?? implode(', ', array_filter(Arr::only($shippingAddress, ['line1', 'barangay', 'city', 'province', 'postal_code'])));

        $codAmount = ($shipment->cod_amount_minor ?: $sellerOrder->buyerDueMinor()) / 100;

        return response()->json([
            'success' => true,
            'shipment' => [
                'id'                => $shipment->id,
                'tracking_code'     => $shipment->tracking_code,
                'status'            => $shipment->status,
                'order_reference'   => $order->reference,
                'recipient_name'    => $shippingAddress['recipient'] ?? $order->buyer?->name ?? 'Consignee',
                'recipient_phone'   => $shippingAddress['phone'] ?? $order->buyer?->phone ?? 'N/A',
                'recipient_address' => $recipientAddress,
                'payment_method'    => strtoupper($order->payment_method),
                'is_cod'            => $order->payment_method === 'cod',
                'cod_amount'        => number_format($codAmount, 2),
                'seller_name'       => $sellerOrder->seller?->name ?? 'Store Partner',
                'items_count'       => $sellerOrder->items->sum('quantity'),
                'items_summary'     => $sellerOrder->items->map(fn ($i) => $i->quantity . 'x ' . $i->product_name . ($i->variant_name ? ' (' . $i->variant_name . ')' : ''))->implode(', '),
                'is_delivered'      => $shipment->status === 'delivered',
            ],
        ]);
    }

    /**
     * Deliver Order (Actual process of delivery)
     */
    public function confirmDelivery(Request $request, int $id)
    {
        $request->validate([
            'recipient_name' => 'nullable|string|max:150',
            'note'           => 'nullable|string|max:500',
            'pod_photo'      => 'nullable|image|max:5120',
        ]);

        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $photoPath = null;
        if ($request->hasFile('pod_photo') && $request->file('pod_photo')->isValid()) {
            $photoPath = $request->file('pod_photo')->store('pod_proofs', 'public');
        }

        DB::transaction(function () use ($id, $request, $courier, $rider, $photoPath) {
            $shipment = Shipment::lockForUpdate()->findOrFail($id);
            $sellerOrder = $shipment->sellerOrder()->lockForUpdate()->firstOrFail();
            $order = $sellerOrder->order()->lockForUpdate()->firstOrFail();

            if ($shipment->status === 'delivered') {
                return;
            }

            // Update shipment
            $shipment->update([
                'status'        => 'delivered',
                'rider_id'      => $rider->id,
                'cod_collected' => $order->payment_method === 'cod',
                'updated_at'    => now(),
            ]);

            // Update seller order
            $sellerOrder->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);

            // If COD, mark payment as paid
            if ($order->payment_method === 'cod') {
                $order->update(['payment_status' => 'paid']);
                $order->payments()->where('status', 'pending')->update(['status' => 'completed']);
            }

            // Record delivery event
            $recipient = $request->filled('recipient_name')
                ? $request->input('recipient_name')
                : ($order->shipping_address['recipient'] ?? $order->buyer?->name ?? 'Consignee');

            $customNote = $request->input('note');
            $noteText = 'Package successfully delivered by rider ' . $courier->name . '. Received by: ' . $recipient . ($customNote ? ' (' . $customNote . ')' : '');

            $shipment->deliveryEvents()->create([
                'status'      => 'delivered',
                'attempt'     => ((int) $shipment->deliveryEvents()->where('status', 'delivered')->max('attempt')) + 1,
                'user_id'     => $courier->id,
                'note'        => $noteText,
                'photo_path'  => $photoPath,
                'occurred_at' => now(),
            ]);

            $rider->increment('deliveries_today');

            // Send notification to seller
            if ($sellerOrder->seller?->user_id) {
                NotificationService::notify(
                    $sellerOrder->seller->user_id,
                    'order_delivered',
                    'Order Delivered (' . $order->reference . ')',
                    'Parcel ' . $shipment->tracking_code . ' was delivered to ' . $recipient . ' by rider ' . $courier->name . '.',
                    route('seller.orders.waybill', $sellerOrder->id)
                );
            }

            // Send notification to buyer
            if ($order->buyer_id) {
                NotificationService::notify(
                    $order->buyer_id,
                    'order_delivered',
                    'Package Delivered! (' . $order->reference . ')',
                    'Your Cartzy order ' . $order->reference . ' was successfully delivered by rider ' . $courier->name . '. Thank you!',
                    route('buyer.dashboard', ['tab' => 'orders'])
                );
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Delivery successfully confirmed! Customer and merchant have been notified.',
            ]);
        }

        return back()->with('success', 'Parcel successfully marked as DELIVERED! Customer and seller have been notified.');
    }

    /**
     * 4. Profit & Earnings Dashboard / Page
     */
    public function earnings(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        // All completed shipments by this rider
        $completedShipments = Shipment::with(['sellerOrder.order', 'sellerOrder.seller'])
            ->where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->latest('updated_at')
            ->get();

        $deliveredTodayCount = $completedShipments
            ->filter(fn ($s) => $s->updated_at && $s->updated_at->isToday())
            ->count();

        $deliveredWeekCount = $completedShipments
            ->filter(fn ($s) => $s->updated_at && $s->updated_at->greaterThanOrEqualTo(now()->subDays(7)))
            ->count();

        $deliveredMonthCount = $completedShipments
            ->filter(fn ($s) => $s->updated_at && $s->updated_at->greaterThanOrEqualTo(now()->startOfMonth()))
            ->count();

        $totalEarningsToday = $deliveredTodayCount * self::RIDER_FEE_PER_DELIVERY;
        $totalEarningsWeek  = $deliveredWeekCount * self::RIDER_FEE_PER_DELIVERY;
        $totalEarningsMonth = $deliveredMonthCount * self::RIDER_FEE_PER_DELIVERY;
        $totalEarningsAll   = $completedShipments->count() * self::RIDER_FEE_PER_DELIVERY;

        // COD cash collections
        $codCollectedToday = $completedShipments
            ->filter(fn ($s) => $s->updated_at && $s->updated_at->isToday() && $s->cod_collected)
            ->sum(function ($s) {
                return $s->cod_amount_minor ?: $s->sellerOrder->buyerDueMinor();
            }) / 100;

        $codCollectedTotal = $completedShipments
            ->filter(fn ($s) => $s->cod_collected)
            ->sum(function ($s) {
                return $s->cod_amount_minor ?: $s->sellerOrder->buyerDueMinor();
            }) / 100;

        // 7-Day Chart Data
        $chartLabels = [];
        $chartEarnings = [];
        $chartDeliveries = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $count = $completedShipments
                ->filter(fn ($s) => $s->updated_at && $s->updated_at->toDateString() === $day->toDateString())
                ->count();

            $chartLabels[] = $day->format('D, M d');
            $chartDeliveries[] = $count;
            $chartEarnings[] = $count * self::RIDER_FEE_PER_DELIVERY;
        }

        $recentPayouts = $completedShipments->take(15);

        return view('courier.earnings', compact(
            'courier',
            'rider',
            'deliveredTodayCount',
            'deliveredWeekCount',
            'deliveredMonthCount',
            'totalEarningsToday',
            'totalEarningsWeek',
            'totalEarningsMonth',
            'totalEarningsAll',
            'codCollectedToday',
            'codCollectedTotal',
            'chartLabels',
            'chartEarnings',
            'chartDeliveries',
            'recentPayouts'
        ));
    }

    /**
     * Record Remittance of COD Cash to Hub
     */
    public function recordRemittance(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes'  => 'nullable|string|max:255',
        ]);

        $amount = (float) $request->input('amount');
        return back()->with('success', 'COD Remittance of ₱' . number_format($amount, 2) . ' submitted for verification at the logistics sorting hub.');
    }

    /**
     * 5. View Delivery History
     */
    public function history(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $search = trim($request->query('search', ''));
        $dateFilter = $request->query('date', 'all');

        $query = Shipment::with([
                'sellerOrder.order.buyer',
                'sellerOrder.items',
                'sellerOrder.seller',
                'deliveryEvents'
            ])
            ->where('rider_id', $rider->id)
            ->where('status', 'delivered');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhereHas('sellerOrder.order', fn ($o) => $o->where('reference', 'like', "%{$search}%"))
                  ->orWhereHas('sellerOrder.order.buyer', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dateFilter === 'today') {
            $query->whereDate('updated_at', today());
        } elseif ($dateFilter === 'week') {
            $query->whereDate('updated_at', '>=', now()->subDays(7));
        } elseif ($dateFilter === 'month') {
            $query->whereDate('updated_at', '>=', now()->startOfMonth());
        }

        $deliveries = $query->latest('updated_at')->paginate(15)->withQueryString();

        $stats = [
            'total_delivered'   => Shipment::where('rider_id', $rider->id)->where('status', 'delivered')->count(),
            'total_cod_revenue' => Shipment::where('rider_id', $rider->id)->where('status', 'delivered')->where('cod_collected', true)->sum('cod_amount_minor') / 100,
        ];

        return view('courier.history', compact('courier', 'rider', 'deliveries', 'search', 'dateFilter', 'stats'));
    }

    /**
     * 6. Chat / Messaging
     */
    public function chat(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        // Hub messages
        $hubMessages = LogisticsMessage::where('contact_user_id', $courier->id)
            ->latest()
            ->take(20)
            ->get();

        // Active orders associated with this rider for order-level chat
        $orders = SellerOrder::with(['order.buyer', 'seller', 'latestMessage'])
            ->whereHas('shipment', function ($q) use ($rider) {
                $q->where('rider_id', $rider->id);
            })
            ->latest()
            ->take(10)
            ->get();

        $selectedOrderId = $request->query('order_id', $orders->first()?->id);
        $activeOrder = $orders->firstWhere('id', $selectedOrderId) ?? $orders->first();

        $messages = collect();
        if ($activeOrder) {
            $messages = SellerMessage::where('seller_order_id', $activeOrder->id)
                ->with('sender')
                ->oldest()
                ->get();

            // Mark received messages as read
            SellerMessage::where('seller_order_id', $activeOrder->id)
                ->where('sender_id', '!=', $courier->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('courier.chat', compact('courier', 'rider', 'hubMessages', 'orders', 'activeOrder', 'messages'));
    }

    /**
     * Send Chat Message
     */
    public function sendMessage(Request $request, int $contactId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'type'    => 'nullable|string|in:order,hub',
        ]);

        $courier = Auth::user();
        $rider = $this->getOrCreateRider();
        $type = $request->input('type', 'order');

        if ($type === 'hub') {
            LogisticsMessage::create([
                'logistics_provider_id' => $rider->logistics_provider_id,
                'contact_user_id'       => $courier->id,
                'sender_type'           => 'contact',
                'message'               => trim($request->input('message')),
            ]);

            return back()->with('success', 'Message sent to Logistics Hub Dispatcher!');
        }

        $sellerOrder = SellerOrder::findOrFail($contactId);

        SellerMessage::create([
            'seller_order_id' => $sellerOrder->id,
            'sender_id'       => $courier->id,
            'body'            => trim($request->input('message')),
        ]);

        return back()->with('success', 'Message sent!');
    }

    /**
     * 7. Account Management
     */
    public function account(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();
        $provider = $rider->logisticsProvider;

        return view('courier.account', compact('courier', 'rider', 'provider'));
    }

    /**
     * Update Profile & Vehicle Info
     */
    public function updateProfile(Request $request)
    {
        $courier = Auth::user();
        $rider = $this->getOrCreateRider();

        $validated = $request->validate([
            'phone'        => 'required|string|max:20',
            'vehicle_type' => 'required|string|in:Motorcycle,Bicycle,E-bike,Tricycle,Car,Van,Truck',
            'plate_no'     => 'nullable|string|max:15',
            'area'         => 'nullable|string|max:100',
        ]);

        $courier->update(['phone' => $validated['phone']]);

        $rider->update([
            'vehicle_type' => $validated['vehicle_type'],
            'plate_no'     => strtoupper(trim($validated['plate_no'] ?? $rider->plate_no)),
            'area'         => $validated['area'] ?? $rider->area,
        ]);

        return back()->with('success', 'Profile and vehicle details successfully updated!');
    }

    /**
     * Toggle Online / Offline Working Status
     */
    public function toggleOnlineStatus(Request $request)
    {
        $rider = $this->getOrCreateRider();
        $newStatus = !$rider->is_active;

        $rider->update(['is_active' => $newStatus]);

        $msg = $newStatus
            ? 'You are now ONLINE. Ready to receive seller pickups and delivery assignments!'
            : 'You are now OFFLINE. New pickup alerts are paused until you return.';

        return back()->with('success', $msg);
    }
}
