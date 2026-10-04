<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LogisticsPortalController extends Controller
{
    /**
     * Session-based mock data store for full interactive capability
     */
    private function getMockData(string $key, array $default): array
    {
        if (!session()->has('logistics_' . $key)) {
            session(['logistics_' . $key => $default]);
        }
        return session('logistics_' . $key);
    }

    private function setMockData(string $key, array $data): void
    {
        session(['logistics_' . $key => $data]);
    }

    /**
     * 1. Dashboard
     */
    public function dashboard()
    {
        $hub = Auth::user();

        $stats = [
            'total_riders'        => 18,
            'active_riders'       => 14,
            'pending_applications' => 3,
            'pickups_today'       => 42,
            'parcels_in_hub'      => 187,
            'sorted_today'        => 134,
            'out_for_delivery'    => 96,
            'delivered_today'     => 78,
            'failed_deliveries'   => 5,
            'hub_efficiency'      => 92.4,
        ];

        $recentPickups = $this->getPickupRequests();
        $recentPickups = array_slice($recentPickups, 0, 5);

        $recentParcels = $this->getParcels();
        $recentParcels = array_slice($recentParcels, 0, 5);

        $chartData = [
            'labels'    => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'received'  => [45, 62, 58, 71, 88, 102, 94],
            'delivered' => [38, 55, 50, 67, 80, 95, 78],
        ];

        $notifications = [
            [
                'id'      => 1,
                'title'   => 'New Pickup Request',
                'message' => 'TechZone Gadgets (3 parcels) scheduled pickup for 2:00 PM today.',
                'time'    => '10 mins ago',
                'type'    => 'pickup',
                'unread'  => true,
                'link'    => route('logistics.pickups'),
            ],
            [
                'id'      => 2,
                'title'   => 'Rider Application',
                'message' => 'Jose Mabuhay applied as a new rider. Requires approval.',
                'time'    => '1 hour ago',
                'type'    => 'rider',
                'unread'  => true,
                'link'    => route('logistics.riders'),
            ],
            [
                'id'      => 3,
                'title'   => 'Failed Delivery Alert',
                'message' => 'Rider Carlo failed delivery for parcel #PKG-7821 (3rd attempt). Returned to hub.',
                'time'    => '2 hours ago',
                'type'    => 'delivery',
                'unread'  => false,
                'link'    => route('logistics.monitoring'),
            ],
            [
                'id'      => 4,
                'title'   => 'Sorting Complete',
                'message' => '134 parcels sorted and assigned to riders for today\'s route.',
                'time'    => 'This morning',
                'type'    => 'sorting',
                'unread'  => false,
                'link'    => route('logistics.sorting'),
            ],
        ];

        return view('logistics.dashboard', compact(
            'hub', 'stats', 'recentPickups', 'recentParcels', 'chartData', 'notifications'
        ));
    }

    /**
     * 2. Rider Management
     */
    public function riders(Request $request)
    {
        $hub = Auth::user();
        $filter = $request->query('filter', 'all');
        $riders = $this->getRiders();

        if ($filter === 'pending') {
            $riders = array_filter($riders, fn($r) => $r['status'] === 'pending');
        } elseif ($filter === 'approved') {
            $riders = array_filter($riders, fn($r) => $r['status'] === 'approved');
        } elseif ($filter === 'active') {
            $riders = array_filter($riders, fn($r) => $r['status'] === 'approved' && $r['is_active']);
        } elseif ($filter === 'inactive') {
            $riders = array_filter($riders, fn($r) => !$r['is_active']);
        } elseif ($filter === 'rejected') {
            $riders = array_filter($riders, fn($r) => $r['status'] === 'rejected');
        }

        return view('logistics.riders', compact('hub', 'riders', 'filter'));
    }

    public function updateRiderStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $reason = $request->input('reason', '');
        $riders = $this->getRiders();

        foreach ($riders as &$rider) {
            if ($rider['id'] == $id) {
                $rider['status'] = $status;
                $rider['rejection_reason'] = $status === 'rejected' ? $reason : null;
                $rider['updated_at'] = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('riders', $riders);

        $msg = $status === 'approved' ? 'Rider application approved successfully!' : 'Rider application rejected.';
        return back()->with('success', $msg);
    }

    public function toggleRiderActive(Request $request, $id)
    {
        $riders = $this->getRiders();

        foreach ($riders as &$rider) {
            if ($rider['id'] == $id) {
                $rider['is_active'] = !$rider['is_active'];
                break;
            }
        }
        $this->setMockData('riders', $riders);

        return back()->with('success', 'Rider status updated successfully!');
    }

    /**
     * 3. Parcel Pickup Requests
     */
    public function pickups(Request $request)
    {
        $hub    = Auth::user();
        $filter = $request->query('status', 'all');
        $pickups = $this->getPickupRequests();

        if ($filter !== 'all') {
            $pickups = array_filter($pickups, fn($p) => $p['status'] === $filter);
        }

        return view('logistics.pickups', compact('hub', 'pickups', 'filter'));
    }

    public function confirmPickup(Request $request, $id)
    {
        $pickups = $this->getPickupRequests();

        foreach ($pickups as &$pickup) {
            if ($pickup['id'] == $id) {
                $pickup['status'] = 'confirmed';
                $pickup['confirmed_at'] = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('pickup_requests', $pickups);

        return back()->with('success', 'Pickup request confirmed! Seller has been notified.');
    }

    /**
     * 4. Incoming Parcels Management
     */
    public function parcels(Request $request)
    {
        $hub    = Auth::user();
        $filter = $request->query('status', 'all');
        $parcels = $this->getParcels();

        if ($filter !== 'all') {
            $parcels = array_filter($parcels, fn($p) => $p['status'] === $filter);
        }

        return view('logistics.parcels', compact('hub', 'parcels', 'filter'));
    }

    public function receiveParcel(Request $request, $id)
    {
        $parcels = $this->getParcels();

        foreach ($parcels as &$parcel) {
            if ($parcel['id'] == $id) {
                $parcel['status'] = 'received';
                $parcel['received_at'] = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('parcels', $parcels);

        return back()->with('success', 'Parcel marked as received at the hub!');
    }

    /**
     * 5. Sorting of Parcels
     */
    public function sorting(Request $request)
    {
        $hub    = Auth::user();
        $filter = $request->query('status', 'all');
        $parcels = $this->getParcels();
        $riders  = array_filter($this->getRiders(), fn($r) => $r['status'] === 'approved' && $r['is_active']);

        if ($filter !== 'all') {
            $parcels = array_filter($parcels, fn($p) => $p['sort_status'] === $filter);
        }

        return view('logistics.sorting', compact('hub', 'parcels', 'riders', 'filter'));
    }

    public function sortParcel(Request $request, $id)
    {
        $area   = $request->input('area');
        $parcels = $this->getParcels();

        foreach ($parcels as &$parcel) {
            if ($parcel['id'] == $id) {
                $parcel['sort_status'] = 'sorted';
                $parcel['delivery_area'] = $area;
                $parcel['sorted_at'] = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('parcels', $parcels);

        return back()->with('success', "Parcel sorted to area: {$area}");
    }

    /**
     * 6. Delivery Assignment
     */
    public function assignments(Request $request)
    {
        $hub    = Auth::user();
        $filter = $request->query('area', 'all');
        $parcels = array_filter($this->getParcels(), fn($p) => $p['sort_status'] === 'sorted' || $p['sort_status'] === 'assigned');
        $riders  = array_filter($this->getRiders(), fn($r) => $r['status'] === 'approved' && $r['is_active']);

        if ($filter !== 'all') {
            $parcels = array_filter($parcels, fn($p) => $p['delivery_area'] === $filter);
        }

        $areas = array_unique(array_column($this->getParcels(), 'delivery_area'));
        $areas = array_filter($areas);

        return view('logistics.assignments', compact('hub', 'parcels', 'riders', 'filter', 'areas'));
    }

    public function assignDelivery(Request $request, $id)
    {
        $riderId   = $request->input('rider_id');
        $riderName = $request->input('rider_name', 'Assigned Rider');
        $parcels   = $this->getParcels();

        foreach ($parcels as &$parcel) {
            if ($parcel['id'] == $id) {
                $parcel['sort_status']   = 'assigned';
                $parcel['assigned_rider_id']   = $riderId;
                $parcel['assigned_rider_name'] = $riderName;
                $parcel['assigned_at']   = now()->format('M d, Y h:i A');
                break;
            }
        }
        $this->setMockData('parcels', $parcels);

        return back()->with('success', "Parcel assigned to {$riderName} for delivery!");
    }

    /**
     * 7. Delivery Monitoring
     */
    public function monitoring(Request $request)
    {
        $hub    = Auth::user();
        $filter = $request->query('status', 'all');
        $parcels = $this->getParcels();

        if ($filter !== 'all') {
            $parcels = array_filter($parcels, fn($p) => $p['delivery_status'] === $filter);
        }

        return view('logistics.monitoring', compact('hub', 'parcels', 'filter'));
    }

    /**
     * 8. Reports
     */
    public function reports(Request $request)
    {
        $hub      = Auth::user();
        $dateFrom = $request->query('from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->query('to', now()->format('Y-m-d'));

        $reportData = [
            'total_parcels_handled' => 1842,
            'delivered_on_time'     => 1654,
            'failed_deliveries'     => 97,
            'returned_to_hub'       => 91,
            'success_rate'          => 89.8,
            'avg_delivery_days'     => 1.4,
            'top_riders' => [
                ['name' => 'Arnel Gomez',   'deliveries' => 142, 'success_rate' => 96.5],
                ['name' => 'Carlo Santos',  'deliveries' => 128, 'success_rate' => 94.2],
                ['name' => 'Rex Villanueva','deliveries' => 115, 'success_rate' => 91.8],
                ['name' => 'Bong Aquino',   'deliveries' =>  98, 'success_rate' => 89.4],
                ['name' => 'Jun Pascual',   'deliveries' =>  87, 'success_rate' => 88.0],
            ],
            'area_breakdown' => [
                ['area' => 'Laguna - Santa Cruz',   'parcels' => 312],
                ['area' => 'Laguna - San Pablo',    'parcels' => 278],
                ['area' => 'Laguna - Calamba',      'parcels' => 264],
                ['area' => 'Batangas - Lipa',       'parcels' => 198],
                ['area' => 'Quezon - Lucena',       'parcels' => 156],
            ],
            'chart' => [
                'labels'    => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                'received'  => [420, 395, 480, 547],
                'delivered' => [378, 360, 430, 486],
            ],
        ];

        return view('logistics.reports', compact('hub', 'reportData', 'dateFrom', 'dateTo'));
    }

    /**
     * 9. Chat / Messaging
     */
    public function chat(Request $request)
    {
        $hub = Auth::user();
        $activeContactId = $request->query('contact', 1);

        $contacts = [
            [
                'id'           => 1,
                'name'         => 'Maria Santos (TechZone Store)',
                'role'         => 'Seller',
                'avatar'       => '🏬',
                'status'       => 'online',
                'last_message' => 'When will the rider pick up our parcels? We have 8 ready.',
                'time'         => '10:42 AM',
                'unread'       => 2,
            ],
            [
                'id'           => 2,
                'name'         => 'Arnel Gomez (Rider)',
                'role'         => 'Rider',
                'avatar'       => '🛵',
                'status'       => 'online',
                'last_message' => 'Hub boss, on my way na po. ETA 15 minutes.',
                'time'         => '09:15 AM',
                'unread'       => 1,
            ],
            [
                'id'           => 3,
                'name'         => 'Carlo Santos (Rider)',
                'role'         => 'Rider',
                'avatar'       => '🏍️',
                'status'       => 'offline',
                'last_message' => 'Completed all 12 deliveries. 1 failed, returned na po.',
                'time'         => 'Yesterday',
                'unread'       => 0,
            ],
            [
                'id'           => 4,
                'name'         => 'Admin – cartzy Platform',
                'role'         => 'Admin',
                'avatar'       => '⚙️',
                'status'       => 'online',
                'last_message' => 'Please ensure all parcels are scanned upon receipt.',
                'time'         => 'Yesterday',
                'unread'       => 0,
            ],
        ];

        $currentContact = collect($contacts)->firstWhere('id', $activeContactId) ?? $contacts[0];

        $messages = $this->getMockData('chat_messages_' . $activeContactId, [
            [
                'sender' => 'contact',
                'text'   => $currentContact['last_message'],
                'time'   => $currentContact['time'],
            ],
            [
                'sender' => 'hub',
                'text'   => 'Thank you for the update! We will process accordingly.',
                'time'   => 'Just now',
            ],
        ]);

        return view('logistics.chat', compact('hub', 'contacts', 'currentContact', 'messages'));
    }

    public function sendMessage(Request $request, $contactId)
    {
        $text = $request->input('message');
        if (trim($text)) {
            $messages   = $this->getMockData('chat_messages_' . $contactId, []);
            $messages[] = [
                'sender' => 'hub',
                'text'   => $text,
                'time'   => now()->format('h:i A'),
            ];
            $this->setMockData('chat_messages_' . $contactId, $messages);
        }
        return back()->with('success', 'Message sent!');
    }

    /**
     * 10. Account Management
     */
    public function account()
    {
        $hub = Auth::user();
        return view('logistics.account', compact('hub'));
    }

    public function updateAccount(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'business_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $user = Auth::user();
            $user->name          = $request->input('name');
            $user->phone         = $request->input('phone');
            $user->business_name = $request->input('business_name');
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->save();
        } catch (\Exception $e) {
            // demo mode – silently ignore
        }

        return back()->with('success', 'Account updated successfully!');
    }

    /* ──────────────────────────────────────────────────────────────────────── */
    /*  MOCK DATA HELPERS                                                       */
    /* ──────────────────────────────────────────────────────────────────────── */

    private function getRiders(): array
    {
        return $this->getMockData('riders', [
            [
                'id'               => 1,
                'name'             => 'Arnel Gomez',
                'email'            => 'arnel.rider@gmail.com',
                'phone'            => '09285559876',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'NQ-4812',
                'status'           => 'approved',
                'is_active'        => true,
                'deliveries_today' => 12,
                'rating'           => 4.95,
                'applied_at'       => 'Feb 23, 2026',
                'rejection_reason' => null,
                'area'             => 'Laguna - Santa Cruz / San Pablo',
            ],
            [
                'id'               => 2,
                'name'             => 'Carlo Santos',
                'email'            => 'carlo.rider@gmail.com',
                'phone'            => '09178884455',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'MN-3421',
                'status'           => 'approved',
                'is_active'        => true,
                'deliveries_today' => 9,
                'rating'           => 4.82,
                'applied_at'       => 'Feb 20, 2026',
                'rejection_reason' => null,
                'area'             => 'Laguna - Calamba / Los Baños',
            ],
            [
                'id'               => 3,
                'name'             => 'Rex Villanueva',
                'email'            => 'rex.rider@gmail.com',
                'phone'            => '09271112233',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'LK-7829',
                'status'           => 'approved',
                'is_active'        => false,
                'deliveries_today' => 0,
                'rating'           => 4.60,
                'applied_at'       => 'Mar 02, 2026',
                'rejection_reason' => null,
                'area'             => 'Batangas - Lipa / Mataasnakahoy',
            ],
            [
                'id'               => 4,
                'name'             => 'Jose Mabuhay',
                'email'            => 'jose.rider@gmail.com',
                'phone'            => '09394445566',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'QR-1234',
                'status'           => 'pending',
                'is_active'        => false,
                'deliveries_today' => 0,
                'rating'           => null,
                'applied_at'       => 'Oct 03, 2026',
                'rejection_reason' => null,
                'area'             => 'Quezon - Lucena / Tayabas',
            ],
            [
                'id'               => 5,
                'name'             => 'Bong Aquino',
                'email'            => 'bong.rider@gmail.com',
                'phone'            => '09451234567',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'PH-5678',
                'status'           => 'pending',
                'is_active'        => false,
                'deliveries_today' => 0,
                'rating'           => null,
                'applied_at'       => 'Oct 04, 2026',
                'rejection_reason' => null,
                'area'             => 'Laguna - Biñan / Santa Rosa',
            ],
            [
                'id'               => 6,
                'name'             => 'Noel Torres',
                'email'            => 'noel.rejected@gmail.com',
                'phone'            => '09119876543',
                'vehicle_type'     => 'Bicycle',
                'plate_no'         => 'N/A',
                'status'           => 'rejected',
                'is_active'        => false,
                'deliveries_today' => 0,
                'rating'           => null,
                'applied_at'       => 'Sep 28, 2026',
                'rejection_reason' => 'Incomplete documents. NBI Clearance expired.',
                'area'             => null,
            ],
        ]);
    }

    private function getPickupRequests(): array
    {
        return $this->getMockData('pickup_requests', [
            [
                'id'           => 1,
                'seller_name'  => 'TechZone Gadgets Store',
                'seller_email' => 'maria@techzone.ph',
                'parcel_count' => 8,
                'weight_kg'    => 12.4,
                'pickup_date'  => 'Oct 04, 2026',
                'pickup_time'  => '2:00 PM',
                'address'      => '123 Rizal St, Brgy. Poblacion, Santa Cruz, Laguna',
                'status'       => 'pending',
                'confirmed_at' => null,
                'notes'        => 'Fragile items. Handle with care.',
            ],
            [
                'id'           => 2,
                'seller_name'  => 'GlowBeauty Cosmetics Shop',
                'seller_email' => 'contact@glowbeauty.ph',
                'parcel_count' => 5,
                'weight_kg'    => 3.8,
                'pickup_date'  => 'Oct 04, 2026',
                'pickup_time'  => '3:30 PM',
                'address'      => '45 Mabini Ave, Brgy. San Jose, San Pablo, Laguna',
                'status'       => 'confirmed',
                'confirmed_at' => 'Oct 04, 2026 9:00 AM',
                'notes'        => null,
            ],
            [
                'id'           => 3,
                'seller_name'  => 'ShoeHaven Official',
                'seller_email' => 'sales@shoehaven.ph',
                'parcel_count' => 14,
                'weight_kg'    => 22.1,
                'pickup_date'  => 'Oct 05, 2026',
                'pickup_time'  => '10:00 AM',
                'address'      => '88 Quezon Blvd, Brgy. Halang, Calamba, Laguna',
                'status'       => 'pending',
                'confirmed_at' => null,
                'notes'        => 'Boxes are large. Bring extra packaging tape.',
            ],
            [
                'id'           => 4,
                'seller_name'  => 'Sportz Unlimited PH',
                'seller_email' => 'sportz@unlimited.ph',
                'parcel_count' => 3,
                'weight_kg'    => 8.2,
                'pickup_date'  => 'Oct 03, 2026',
                'pickup_time'  => '11:00 AM',
                'address'      => '22 Laurel St, Brgy. Marawoy, Lipa, Batangas',
                'status'       => 'confirmed',
                'confirmed_at' => 'Oct 03, 2026 8:45 AM',
                'notes'        => null,
            ],
        ]);
    }

    private function getParcels(): array
    {
        return $this->getMockData('parcels', [
            [
                'id'                  => 1,
                'tracking_code'       => 'CTZY-20261004-0001',
                'seller_name'         => 'TechZone Gadgets Store',
                'recipient_name'      => 'Juan Dela Cruz',
                'recipient_address'   => '45 Magsaysay St, Brgy. 1, Santa Cruz, Laguna',
                'recipient_phone'     => '09171234567',
                'items'               => 'Sony WH-1000XM5 Headphones x1',
                'weight_kg'           => 1.5,
                'payment_method'      => 'COD',
                'cod_amount'          => 8990.00,
                'status'              => 'received',
                'sort_status'         => 'sorted',
                'delivery_area'       => 'Laguna - Santa Cruz',
                'delivery_status'     => 'in_transit',
                'assigned_rider_id'   => 1,
                'assigned_rider_name' => 'Arnel Gomez',
                'received_at'         => 'Oct 04, 2026 8:00 AM',
                'sorted_at'           => 'Oct 04, 2026 9:00 AM',
                'assigned_at'         => 'Oct 04, 2026 9:30 AM',
            ],
            [
                'id'                  => 2,
                'tracking_code'       => 'CTZY-20261004-0002',
                'seller_name'         => 'GlowBeauty Cosmetics Shop',
                'recipient_name'      => 'Elena Reyes',
                'recipient_address'   => '12 Maharlika Hwy, Brgy. San Jose, San Pablo, Laguna',
                'recipient_phone'     => '09271234567',
                'items'               => 'SK-II Facial Essence 160ml x1',
                'weight_kg'           => 0.4,
                'payment_method'      => 'Prepaid',
                'cod_amount'          => 0.00,
                'status'              => 'received',
                'sort_status'         => 'sorted',
                'delivery_area'       => 'Laguna - San Pablo',
                'delivery_status'     => 'out_for_delivery',
                'assigned_rider_id'   => 2,
                'assigned_rider_name' => 'Carlo Santos',
                'received_at'         => 'Oct 04, 2026 8:15 AM',
                'sorted_at'           => 'Oct 04, 2026 9:05 AM',
                'assigned_at'         => 'Oct 04, 2026 9:35 AM',
            ],
            [
                'id'                  => 3,
                'tracking_code'       => 'CTZY-20261004-0003',
                'seller_name'         => 'ShoeHaven Official',
                'recipient_name'      => 'Pedro Garcia',
                'recipient_address'   => '7 Aguinaldo Ave, Brgy. Canlubang, Calamba, Laguna',
                'recipient_phone'     => '09451234567',
                'items'               => 'Nike Air Max 270 Size 42 x1',
                'weight_kg'           => 1.2,
                'payment_method'      => 'COD',
                'cod_amount'          => 5499.00,
                'status'              => 'received',
                'sort_status'         => 'unsorted',
                'delivery_area'       => null,
                'delivery_status'     => 'at_hub',
                'assigned_rider_id'   => null,
                'assigned_rider_name' => null,
                'received_at'         => 'Oct 04, 2026 10:00 AM',
                'sorted_at'           => null,
                'assigned_at'         => null,
            ],
            [
                'id'                  => 4,
                'tracking_code'       => 'CTZY-20261003-0087',
                'seller_name'         => 'Sportz Unlimited PH',
                'recipient_name'      => 'Ana Lim',
                'recipient_address'   => '33 JP Laurel Hwy, Brgy. Marawoy, Lipa, Batangas',
                'recipient_phone'     => '09181234567',
                'items'               => 'Adidas Running Shoes x1, Sports Bag x1',
                'weight_kg'           => 2.8,
                'payment_method'      => 'Prepaid',
                'cod_amount'          => 0.00,
                'status'              => 'received',
                'sort_status'         => 'assigned',
                'delivery_area'       => 'Batangas - Lipa',
                'delivery_status'     => 'delivered',
                'assigned_rider_id'   => 3,
                'assigned_rider_name' => 'Rex Villanueva',
                'received_at'         => 'Oct 03, 2026 9:00 AM',
                'sorted_at'           => 'Oct 03, 2026 10:00 AM',
                'assigned_at'         => 'Oct 03, 2026 10:30 AM',
            ],
            [
                'id'                  => 5,
                'tracking_code'       => 'CTZY-20261003-0088',
                'seller_name'         => 'TechZone Gadgets Store',
                'recipient_name'      => 'Mark Torres',
                'recipient_address'   => '88 Bonifacio St, Brgy. Centro 1, Lucena, Quezon',
                'recipient_phone'     => '09921234567',
                'items'               => 'Samsung 65" OLED TV x1',
                'weight_kg'           => 28.5,
                'payment_method'      => 'COD',
                'cod_amount'          => 89990.00,
                'status'              => 'received',
                'sort_status'         => 'unsorted',
                'delivery_area'       => null,
                'delivery_status'     => 'at_hub',
                'assigned_rider_id'   => null,
                'assigned_rider_name' => null,
                'received_at'         => 'Oct 03, 2026 11:30 AM',
                'sorted_at'           => null,
                'assigned_at'         => null,
            ],
            [
                'id'                  => 6,
                'tracking_code'       => 'CTZY-20261002-0045',
                'seller_name'         => 'GlowBeauty Cosmetics Shop',
                'recipient_name'      => 'Rhea Castillo',
                'recipient_address'   => '14 Lapu-Lapu St, Brgy. Palho, Calamba, Laguna',
                'recipient_phone'     => '09311234567',
                'items'               => 'Skincare Bundle Set x1',
                'weight_kg'           => 0.9,
                'payment_method'      => 'COD',
                'cod_amount'          => 1299.00,
                'status'              => 'received',
                'sort_status'         => 'sorted',
                'delivery_area'       => 'Laguna - Calamba',
                'delivery_status'     => 'failed',
                'assigned_rider_id'   => 2,
                'assigned_rider_name' => 'Carlo Santos',
                'received_at'         => 'Oct 02, 2026 8:30 AM',
                'sorted_at'           => 'Oct 02, 2026 9:00 AM',
                'assigned_at'         => 'Oct 02, 2026 9:30 AM',
            ],
        ]);
    }
}
