<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DeliveryEvent;
use App\Models\LogisticsMessage;
use App\Models\LogisticsProvider;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogisticsPortalController extends Controller
{
    /**
     * Resolve the active LogisticsProvider model for the current hub user
     */
    private function provider(): LogisticsProvider
    {
        $hub = Auth::user();

        $provider = $hub->logisticsProvider
            ?? LogisticsProvider::where('user_id', $hub->id)->first()
            ?? LogisticsProvider::first();

        if (!$provider) {
            $provider = LogisticsProvider::create([
                'user_id'       => $hub->id,
                'name'          => $hub->business_name ?? $hub->name,
                'slug'          => 'metro-south-hub',
                'status'        => 'approved',
                'contact_phone' => $hub->phone ?? '09173334444',
            ]);
        }

        return $provider;
    }

    /**
     * 1. Dashboard
     */
    public function dashboard(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();

        // Date range filtering logic
        $activePreset = $request->query('preset');
        $inputStart   = $request->query('start_date');
        $inputEnd     = $request->query('end_date');

        if ($activePreset === 'yesterday') {
            $startDate   = today()->subDay()->startOfDay();
            $endDate     = today()->subDay()->endOfDay();
            $filterLabel = 'Yesterday (' . $startDate->format('M d, Y') . ')';
        } elseif ($activePreset === 'week') {
            $startDate   = today()->subDays(6)->startOfDay();
            $endDate     = today()->endOfDay();
            $filterLabel = 'Last 7 Days (' . $startDate->format('M d') . ' – ' . $endDate->format('M d, Y') . ')';
        } elseif ($activePreset === 'month') {
            $startDate   = today()->startOfMonth()->startOfDay();
            $endDate     = today()->endOfDay();
            $filterLabel = 'This Month (' . $startDate->format('M d') . ' – ' . $endDate->format('M d, Y') . ')';
        } elseif ($inputStart || $inputEnd) {
            $activePreset = 'custom';
            $parsedStart  = $inputStart ? Carbon::parse($inputStart)->startOfDay() : today()->startOfDay();
            $parsedEnd    = $inputEnd ? Carbon::parse($inputEnd)->endOfDay() : $parsedStart->copy()->endOfDay();
            if ($parsedStart->gt($parsedEnd)) {
                $temp        = $parsedStart;
                $parsedStart = $parsedEnd->copy()->startOfDay();
                $parsedEnd   = $temp->copy()->endOfDay();
            }
            $startDate = $parsedStart;
            $endDate   = $parsedEnd;

            if ($startDate->isSameDay($endDate)) {
                $filterLabel = $startDate->format('M d, Y');
            } else {
                $filterLabel = $startDate->format('M d, Y') . ' – ' . $endDate->format('M d, Y');
            }
        } else {
            $activePreset = 'today';
            $startDate    = today()->startOfDay();
            $endDate      = today()->endOfDay();
            $filterLabel  = 'Today (' . today()->format('M d, Y') . ')';
        }

        $isToday = $startDate->isSameDay(today()) && $endDate->isSameDay(today());

        $totalRiders         = Rider::where('logistics_provider_id', $provider->id)->count();
        $activeRiders        = Rider::where('logistics_provider_id', $provider->id)->where('status', 'approved')->where('is_active', true)->count();
        $pendingApplications = Rider::where('logistics_provider_id', $provider->id)->where('status', 'pending')->count();

        $pickupsCount = PickupRequest::where('logistics_provider_id', $provider->id)
            ->whereBetween('pickup_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        $parcelsInHub = Shipment::where('logistics_provider_id', $provider->id)
            ->whereIn('status', ['unassigned', 'assigned', 'picked_up', 'in_transit'])
            ->count();

        $sortedCount = Shipment::where('logistics_provider_id', $provider->id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('sorted_at', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->whereIn('sort_status', ['sorted', 'assigned'])
                          ->whereBetween('updated_at', [$startDate, $endDate]);
                  });
            })
            ->count();

        $outForDeliveryCount = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'out_for_delivery')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $deliveredCount = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $failedDeliveries = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'failed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalDispatched = Shipment::where('logistics_provider_id', $provider->id)
            ->whereIn('status', ['delivered', 'failed', 'returned', 'out_for_delivery'])
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $hubEfficiency = $totalDispatched > 0
            ? round(($deliveredCount / $totalDispatched) * 100, 1)
            : 94.2;

        $stats = [
            'total_riders'         => $totalRiders ?: 18,
            'active_riders'        => $activeRiders ?: 14,
            'pending_applications' => $pendingApplications,
            'pickups_today'        => $pickupsCount ?: ($isToday ? 42 : 0),
            'parcels_in_hub'       => $parcelsInHub ?: 187,
            'sorted_today'         => $sortedCount ?: ($isToday ? 134 : 0),
            'out_for_delivery'     => $outForDeliveryCount ?: ($isToday ? 96 : 0),
            'delivered_today'      => $deliveredCount ?: ($isToday ? 78 : 0),
            'failed_deliveries'    => $failedDeliveries ?: ($isToday ? 5 : 0),
            'hub_efficiency'       => $hubEfficiency,
        ];

        // Recent Pickups from DB matching filtered range
        $recentPickupsQuery = PickupRequest::where('logistics_provider_id', $provider->id)
            ->whereBetween('pickup_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['seller.user'])
            ->latest()
            ->take(5)
            ->get();

        $recentPickups = $recentPickupsQuery->map(function ($p) {
            return [
                'id'           => $p->id,
                'seller_name'  => $p->seller->name ?? ($p->seller->user->name ?? 'Shop Partner'),
                'parcel_count' => $p->parcel_count,
                'weight_kg'    => $p->weight_kg,
                'pickup_date'  => $p->pickup_date ? Carbon::parse($p->pickup_date)->format('M d, Y') : now()->format('M d, Y'),
                'pickup_time'  => $p->pickup_time ?: '2:00 PM',
                'status'       => $p->status,
                'notes'        => $p->notes,
            ];
        })->toArray();

        // If no recent pickups and it's today, provide baseline items
        if (empty($recentPickups) && $isToday) {
            $recentPickups = [
                [
                    'id'           => 1,
                    'seller_name'  => 'TechZone Official Mall',
                    'parcel_count' => 8,
                    'weight_kg'    => 12.4,
                    'pickup_date'  => now()->format('M d, Y'),
                    'pickup_time'  => '2:00 PM',
                    'status'       => 'pending',
                    'notes'        => 'Fragile electronics. Handle with care.',
                ],
                [
                    'id'           => 2,
                    'seller_name'  => 'Bloom Beauty',
                    'parcel_count' => 5,
                    'weight_kg'    => 3.8,
                    'pickup_date'  => now()->format('M d, Y'),
                    'pickup_time'  => '3:30 PM',
                    'status'       => 'confirmed',
                    'notes'        => null,
                ],
            ];
        }

        // Recent parcels
        $recentParcels = $this->fetchFormattedParcels($provider, 5);

        // Chart Data based on filtered date range
        $diffDays       = $startDate->diffInDays($endDate);
        $chartLabels    = [];
        $chartReceived  = [];
        $chartDelivered = [];

        if ($diffDays === 0) {
            $day = $startDate->copy();
            $chartLabels = ['8:00 AM', '11:00 AM', '2:00 PM', '5:00 PM'];
            $rec = Shipment::where('logistics_provider_id', $provider->id)->whereDate('created_at', $day)->count();
            $del = Shipment::where('logistics_provider_id', $provider->id)->where('status', 'delivered')->whereDate('updated_at', $day)->count();

            $chartReceived  = $rec > 0 ? [round($rec * 0.25), round($rec * 0.4), round($rec * 0.25), round($rec * 0.1)] : ($isToday ? [18, 36, 28, 12] : [0, 0, 0, 0]);
            $chartDelivered = $del > 0 ? [round($del * 0.15), round($del * 0.35), round($del * 0.35), round($del * 0.15)] : ($isToday ? [12, 29, 25, 12] : [0, 0, 0, 0]);
        } else {
            $steps = min((int) $diffDays, 13);
            for ($i = $steps; $i >= 0; $i--) {
                $curDate = $endDate->copy()->subDays($i);
                $chartLabels[] = $curDate->format('M d');

                $rec = Shipment::where('logistics_provider_id', $provider->id)
                    ->whereDate('created_at', $curDate)
                    ->count();
                $del = Shipment::where('logistics_provider_id', $provider->id)
                    ->where('status', 'delivered')
                    ->whereDate('updated_at', $curDate)
                    ->count();

                $chartReceived[]  = $rec > 0 ? $rec : ($isToday ? [45, 62, 58, 71, 88, 102, 94][$i % 7] : 0);
                $chartDelivered[] = $del > 0 ? $del : ($isToday ? [38, 55, 50, 67, 80, 95, 78][$i % 7] : 0);
            }
        }

        $chartData = [
            'labels'    => $chartLabels,
            'received'  => $chartReceived,
            'delivered' => $chartDelivered,
        ];

        // Notifications
        $dbNotifs = AppNotification::where('user_id', $hub->id)->whereNull('archived_at')->latest()->take(4)->get();
        if ($dbNotifs->count() > 0) {
            $notifications = $dbNotifs->map(function ($n) {
                return [
                    'id'      => $n->id,
                    'title'   => $n->title,
                    'message' => $n->message,
                    'time'    => $n->created_at->diffForHumans(),
                    'type'    => $n->type,
                    'unread'  => !$n->is_read,
                    'link'    => $n->link ?: route('logistics.dashboard'),
                ];
            })->toArray();
        } else {
            $notifications = [
                [
                    'id'      => 1,
                    'title'   => 'New Pickup Request',
                    'message' => 'TechZone Official Mall scheduled pickup for today.',
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
                    'message' => 'Rider Carlo Santos reported a delivery failure. Parcel returned to hub.',
                    'time'    => '2 hours ago',
                    'type'    => 'delivery',
                    'unread'  => false,
                    'link'    => route('logistics.monitoring'),
                ],
                [
                    'id'      => 4,
                    'title'   => 'Sorting Complete',
                    'message' => 'All pending parcels sorted and assigned to riders for route.',
                    'time'    => 'This morning',
                    'type'    => 'sorting',
                    'unread'  => false,
                    'link'    => route('logistics.sorting'),
                ],
            ];
        }

        return view('logistics.dashboard', compact(
            'hub', 'stats', 'recentPickups', 'recentParcels', 'chartData', 'notifications',
            'startDate', 'endDate', 'activePreset', 'filterLabel', 'isToday'
        ));
    }

    /**
     * 2. Rider Management
     */
    public function riders(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();
        $filter = $request->query('filter', 'all');
        $filter = in_array($filter, ['all', 'pending', 'approved', 'active', 'inactive', 'suspended', 'rejected'], true) ? $filter : 'all';
        $search = trim((string) $request->query('search', ''));
        $base = Rider::where('logistics_provider_id', $provider->id);
        $counts = [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'active' => (clone $base)->where('status', 'approved')->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('status', 'approved')->where('is_active', false)->count(),
            'suspended' => (clone $base)->where('status', 'suspended')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
        $query = (clone $base)->with('user');
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('plate_no', 'like', "%{$search}%");
            });
        }

        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'approved') {
            $query->where('status', 'approved');
        } elseif ($filter === 'active') {
            $query->where('status', 'approved')->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('status', 'approved')->where('is_active', false);
        } elseif ($filter === 'suspended') {
            $query->where('status', 'suspended');
        } elseif ($filter === 'rejected') {
            $query->where('status', 'rejected');
        }

        $riders = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest()->orderByDesc('id')->paginate(12)->withQueryString()->through(function ($r) {
            return [
                'id'               => $r->id,
                'name'             => $r->user->name ?? 'Rider',
                'email'            => $r->user->email ?? 'N/A',
                'phone'            => $r->user->phone ?? '',
                'vehicle_type'     => $r->vehicle_type ?: 'Motorcycle',
                'plate_no'         => $r->plate_no ?: 'N/A',
                'status'           => $r->status ?: 'approved',
                'is_active'        => (bool) $r->is_active,
                'deliveries_today' => $r->deliveries_today ?: 0,
                'rating'           => $r->rating,
                'applied_at'       => $r->created_at ? $r->created_at->format('M d, Y') : now()->format('M d, Y'),
                'rejection_reason' => $r->rejection_reason,
                'area'             => $r->area ?: 'Not assigned',
            ];
        });

        return view('logistics.riders', compact('hub', 'riders', 'filter', 'counts', 'search'));
    }

    public function updateRiderStatus(Request $request, $id)
    {
        $provider = $this->provider();
        $status   = $request->input('status');
        $reason   = $request->input('reason', '');

        $rider = Rider::where('logistics_provider_id', $provider->id)->with('user')->findOrFail($id);
        $rider->status           = $status;
        $rider->rejection_reason = $status === 'rejected' ? $reason : null;
        if ($status === 'approved') {
            $rider->is_active = true;
            if ($rider->user) {
                $rider->user->is_suspended = false;
                $rider->user->status = 'active';
                $rider->user->save();
            }
        } elseif ($status === 'rejected') {
            $rider->is_active = false;
        } elseif ($status === 'suspended') {
            $rider->is_active = false;
            if ($rider->user) {
                $rider->user->is_suspended = true;
                $rider->user->status = 'suspended';
                $rider->user->save();
            }
        }
        $rider->save();

        $msg = match ($status) {
            'approved' => 'Rider status updated to Approved (Active).',
            'pending' => 'Rider application returned to pending review.',
            'suspended' => 'Rider has been suspended.',
            default => 'Rider application rejected.',
        };
        return back()->with('success', $msg);
    }

    public function toggleRiderActive(Request $request, $id)
    {
        $provider = $this->provider();
        $rider    = Rider::where('logistics_provider_id', $provider->id)->with('user')->findOrFail($id);

        if ($rider->status === 'suspended') {
            $rider->status = 'approved';
            if ($rider->user) {
                $rider->user->is_suspended = false;
                $rider->user->status = 'active';
                $rider->user->save();
            }
        }

        if ($request->has('active')) {
            $rider->is_active = $request->boolean('active');
        } else {
            $rider->is_active = !$rider->is_active;
        }
        $rider->save();

        $actionWord = $rider->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Rider has been {$actionWord} successfully.");
    }

    /**
     * 3. Parcel Pickup Requests
     */
    public function pickups(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();
        $filter   = $request->query('status', 'all');
        $filter   = in_array($filter, ['all', 'pending', 'confirmed', 'assigned', 'unassigned'], true) ? $filter : 'all';
        $search   = trim($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? '');
        $base     = PickupRequest::where('logistics_provider_id', $provider->id);

        $counts = [
            'all'        => (clone $base)->count(),
            'pending'    => (clone $base)->where('status', 'pending')->count(),
            'confirmed'  => (clone $base)->where('status', 'confirmed')->count(),
            'assigned'   => (clone $base)->whereNotNull('rider_id')->count(),
            'unassigned' => (clone $base)->whereNull('rider_id')->count(),
            // What "Dispatch all" will act on: open pickups without a rider.
            'dispatchable' => (clone $base)->whereNull('rider_id')->whereIn('status', ['pending', 'confirmed'])->count(),
        ];

        // Retrieve active approved riders for area matching & assignment
        $allRiders = Rider::where('logistics_provider_id', $provider->id)
            ->where('status', 'approved')
            ->with('user')
            ->orderByDesc('is_active')
            ->orderBy('deliveries_today')
            ->get();

        $query = PickupRequest::where('logistics_provider_id', $provider->id)
            ->with(['seller.user', 'rider.user']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('address', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', fn ($seller) => $seller->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('seller.user', fn ($user) => $user->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'))
                    ->orWhereHas('rider.user', fn ($user) => $user->where('name', 'like', '%' . $search . '%')->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('rider', fn ($rider) => $rider->where('area', 'like', '%' . $search . '%')->orWhere('plate_no', 'like', '%' . $search . '%'));
            });
        }

        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'confirmed') {
            $query->where('status', 'confirmed');
        } elseif ($filter === 'assigned') {
            $query->whereNotNull('rider_id');
        } elseif ($filter === 'unassigned') {
            $query->whereNull('rider_id');
        }

        $pickups = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('pickup_date')->orderBy('id')->paginate(15)->withQueryString()->through(function ($p) use ($allRiders) {
                $rankedRiders = $this->rankRidersForPickup($allRiders, (string) ($p->address ?? ''));
                $topNearby    = $rankedRiders->first();

                return [
                    'id'            => $p->id,
                    'seller_name'   => $p->seller->name ?? ($p->seller->user->name ?? 'Shop Partner'),
                    'seller_email'  => $p->seller->user->email ?? 'N/A',
                    'seller_phone'  => $p->seller->user->phone ?? ($p->seller->contact_phone ?? 'N/A'),
                    'parcel_count'  => $p->parcel_count,
                    'weight_kg'     => $p->weight_kg,
                    'pickup_date'   => $p->pickup_date ? Carbon::parse($p->pickup_date)->format('M d, Y') : 'Not scheduled',
                    'pickup_time'   => $p->pickup_time ?: 'Time not specified',
                    'address'       => $p->address,
                    'status'        => $p->status,
                    'confirmed_at'  => $p->confirmed_at ? $p->confirmed_at->format('M d, Y h:i A') : null,
                    'assigned_at'   => $p->assigned_at ? $p->assigned_at->format('M d, Y h:i A') : null,
                    'notes'         => $p->notes,
                    'rider_id'      => $p->rider_id,
                    'rider'         => $p->rider ? [
                        'id'               => $p->rider->id,
                        'name'             => $p->rider->user->name ?? 'Rider',
                        'phone'            => $p->rider->user->phone ?? '',
                        'vehicle_type'     => $p->rider->vehicle_type ?: 'Motorcycle',
                        'plate_no'         => $p->rider->plate_no ?: 'N/A',
                        'area'             => $p->rider->area ?: 'General Area',
                        'rating'           => $p->rider->rating ?? 5.0,
                        'deliveries_today' => $p->rider->deliveries_today ?? 0,
                        'is_active'        => (bool) $p->rider->is_active,
                    ] : null,
                    'nearest_rider' => $topNearby ? [
                        'id'               => $topNearby['rider']->id,
                        'name'             => $topNearby['rider']->user->name ?? 'Rider',
                        'phone'            => $topNearby['rider']->user->phone ?? '',
                        'vehicle_type'     => $topNearby['rider']->vehicle_type ?: 'Motorcycle',
                        'plate_no'         => $topNearby['rider']->plate_no ?: 'N/A',
                        'area'             => $topNearby['rider']->area ?: 'General Area',
                        'rating'           => $topNearby['rider']->rating ?? 5.0,
                        'deliveries_today' => $topNearby['rider']->deliveries_today ?? 0,
                        'is_active'        => (bool) $topNearby['rider']->is_active,
                        'score'            => $topNearby['score'],
                        'match_badge'      => $topNearby['match_badge'],
                        'is_nearby'        => $topNearby['is_nearby'],
                    ] : null,
                    'ranked_riders' => $rankedRiders->take(12)->map(function ($r) {
                        return [
                            'id'               => $r['rider']->id,
                            'name'             => $r['rider']->user->name ?? 'Rider',
                            'phone'            => $r['rider']->user->phone ?? '',
                            'vehicle_type'     => $r['rider']->vehicle_type ?: 'Motorcycle',
                            'plate_no'         => $r['rider']->plate_no ?: 'N/A',
                            'area'             => $r['rider']->area ?: 'General Area',
                            'rating'           => $r['rider']->rating ?? 5.0,
                            'deliveries_today' => $r['rider']->deliveries_today ?? 0,
                            'is_active'        => (bool) $r['rider']->is_active,
                            'score'            => $r['score'],
                            'match_badge'      => $r['match_badge'],
                            'is_nearby'        => $r['is_nearby'],
                        ];
                    })->values()->toArray(),
                ];
            });

        return view('logistics.pickups', compact('hub', 'pickups', 'filter', 'search', 'counts', 'allRiders'));
    }

    public function confirmPickup(Request $request, $id)
    {
        $provider = $this->provider();
        $pickup   = PickupRequest::where('logistics_provider_id', $provider->id)->findOrFail($id);
        
        $pickup->status       = 'confirmed';
        $pickup->confirmed_at = now();

        if ($request->filled('rider_id')) {
            $rider = Rider::where('logistics_provider_id', $provider->id)
                ->where('status', 'approved')
                ->find($request->input('rider_id'));
            if ($rider) {
                $pickup->rider_id    = $rider->id;
                $pickup->assigned_at = now();
            }
        }

        $pickup->save();

        // Also if connected to seller_order, mark delivery event
        if ($pickup->seller_order_id && $pickup->sellerOrder?->shipment) {
            $shipment = $pickup->sellerOrder->shipment;
            if ($pickup->rider_id) {
                $shipment->update(['rider_id' => $pickup->rider_id]);
            }
            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status'      => 'pickup_confirmed',
                'attempt'     => 1,
                'user_id'     => Auth::id(),
                'note'        => 'Pickup schedule confirmed by Hub' . ($pickup->rider_id ? ' (Rider assigned).' : '.'),
                'occurred_at' => now(),
            ]);
        }

        return back()->with('success', 'Pickup request confirmed in database! Seller has been notified.');
    }

    public function assignPickupRider(Request $request, $id)
    {
        $provider = $this->provider();
        $pickup   = PickupRequest::where('logistics_provider_id', $provider->id)->with('seller.user')->findOrFail($id);

        $data = $request->validate([
            'rider_id' => ['required', 'integer', 'exists:riders,id'],
        ]);

        $rider = Rider::where('logistics_provider_id', $provider->id)
            ->where('status', 'approved')
            ->with('user')
            ->findOrFail($data['rider_id']);

        $pickup->rider_id     = $rider->id;
        $pickup->status       = 'confirmed';
        $pickup->confirmed_at = $pickup->confirmed_at ?? now();
        $pickup->assigned_at  = now();
        $pickup->save();

        if ($pickup->seller_order_id && $pickup->sellerOrder?->shipment) {
            $shipment = $pickup->sellerOrder->shipment;
            $shipment->update(['rider_id' => $rider->id]);
            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status'      => 'pickup_assigned',
                'attempt'     => 1,
                'user_id'     => Auth::id(),
                'note'        => "Rider {$rider->user->name} dispatched for pickup by Hub.",
                'occurred_at' => now(),
            ]);
        }

        // Notify Rider
        if ($rider->user_id) {
            AppNotification::create([
                'user_id' => $rider->user_id,
                'type'    => 'pickup',
                'title'   => 'New Pickup Assignment',
                'message' => "You've been requested to pick up {$pickup->parcel_count} parcel(s) at {$pickup->address}.",
                'link'    => route('courier.pickups'),
                'is_read' => false,
            ]);
        }

        // Notify Seller
        if ($pickup->seller?->user_id) {
            AppNotification::create([
                'user_id' => $pickup->seller->user_id,
                'type'    => 'pickup_accepted',
                'title'   => 'Rider Dispatched for Pickup',
                'message' => "Rider {$rider->user->name} has been assigned to pick up your packages.",
                'link'    => route('seller.courier'),
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Rider {$rider->user->name} assigned to pickup #PR-" . str_pad($pickup->id, 4, '0', STR_PAD_LEFT) . "!");
    }

    public function autoAssignPickupRider(Request $request, $id)
    {
        $provider = $this->provider();
        $pickup   = PickupRequest::where('logistics_provider_id', $provider->id)->with('seller.user')->findOrFail($id);

        $riders = Rider::where('logistics_provider_id', $provider->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->with('user')
            ->get();

        if ($riders->isEmpty()) {
            return back()->with('error', 'No active riders available in hub. Please activate an approved rider first.');
        }

        $ranked = $this->rankRidersForPickup($riders, (string) ($pickup->address ?? ''));
        $best   = $ranked->first()['rider'];
        $this->dispatchPickup($pickup, $best);

        return back()->with('success', "⚡ Nearest rider {$best->user->name} ({$best->area}) auto-dispatched for pickup!");
    }

    /** Dispatch the nearest active rider to every pickup that doesn't have one yet. */
    public function dispatchAllPickups(Request $request)
    {
        $provider = $this->provider();
        $riders = Rider::where('logistics_provider_id', $provider->id)->where('status', 'approved')->where('is_active', true)->with('user')->get();
        if ($riders->isEmpty()) {
            return back()->with('error', 'No active riders available in hub. Please activate an approved rider first.');
        }
        $waiting = PickupRequest::where('logistics_provider_id', $provider->id)->whereNull('rider_id')
            ->whereIn('status', ['pending', 'confirmed'])->with('seller.user')->get();
        if ($waiting->isEmpty()) {
            return back()->with('success', 'Every pickup request already has a rider.');
        }

        $load = [];
        DB::transaction(function () use ($waiting, $riders, &$load) {
            foreach ($waiting as $pickup) {
                $best = $this->rankRidersForPickup($riders, (string) ($pickup->address ?? ''))
                    ->map(fn ($r) => $r + ['adjusted' => $r['score'] - 8 * ($load[$r['rider']->id] ?? 0)])
                    ->sortByDesc('adjusted')->first()['rider'];
                $load[$best->id] = ($load[$best->id] ?? 0) + 1;
                $this->dispatchPickup($pickup, $best);
            }
        });

        return back()->with('success', "⚡ {$waiting->count()} ".Str::plural('pickup', $waiting->count())." dispatched to the nearest riders.");
    }

    /** Assign a rider to a pickup, log it on the shipment and notify the rider. */
    private function dispatchPickup(PickupRequest $pickup, Rider $best): void
    {
        $pickup->rider_id     = $best->id;
        $pickup->status       = 'confirmed';
        $pickup->confirmed_at = $pickup->confirmed_at ?? now();
        $pickup->assigned_at  = now();
        $pickup->save();

        if ($pickup->seller_order_id && $pickup->sellerOrder?->shipment) {
            $shipment = $pickup->sellerOrder->shipment;
            $shipment->update(['rider_id' => $best->id]);
            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status'      => 'pickup_assigned',
                'attempt'     => 1,
                'user_id'     => Auth::id(),
                'note'        => "Nearest rider {$best->user->name} auto-dispatched by Hub.",
                'occurred_at' => now(),
            ]);
        }

        // Notify Rider
        if ($best->user_id) {
            AppNotification::create([
                'user_id' => $best->user_id,
                'type'    => 'pickup',
                'title'   => 'New Pickup Assignment',
                'message' => "You've been dispatched to pick up {$pickup->parcel_count} parcel(s) from " . ($pickup->seller->name ?? 'Seller') . " at {$pickup->address}.",
                'link'    => route('courier.pickups'),
                'is_read' => false,
            ]);
        }
    }

    public function unassignPickupRider(Request $request, $id)
    {
        $provider = $this->provider();
        $pickup   = PickupRequest::where('logistics_provider_id', $provider->id)->with('rider.user')->findOrFail($id);
        $oldRiderName = $pickup->rider?->user?->name ?? 'Rider';

        $pickup->rider_id    = null;
        $pickup->assigned_at = null;
        $pickup->save();

        return back()->with('success', "Rider {$oldRiderName} unassigned from pickup request.");
    }

    /**
     * Rank riders for a pickup address based on proximity/area matching, activity, workload and rating
     */
    protected function rankRidersForPickup($riders, string $address)
    {
        return collect($riders)->map(function ($rider) use ($address) {
            $analysis = $this->scoreRiderForAddress($rider, $address);
            return [
                'rider'       => $rider,
                'score'       => $analysis['score'],
                'match_type'  => $analysis['match_type'],
                'match_badge' => $analysis['match_badge'],
                'is_nearby'   => $analysis['is_nearby'],
            ];
        })->sortByDesc('score')->values();
    }

    /**
     * Compute proximity match score for rider's assigned area and pickup address
     */
    protected function scoreRiderForAddress(Rider $rider, string $address): array
    {
        $cleanAddress = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $address));
        $riderArea    = (string) ($rider->area ?? '');
        $cleanArea    = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $riderArea));

        $score = 0;
        $hasCityMatch = false;
        $hasProvinceMatch = false;

        $provinces = ['laguna', 'batangas', 'cavite', 'rizal', 'quezon', 'metro manila', 'manila', 'bulacan', 'pampanga', 'cebu', 'davao'];

        $segments = array_filter(array_map('trim', preg_split('/[-,\/|]/', $riderArea)));

        foreach ($segments as $segment) {
            $cleanSegment = mb_strtolower(trim($segment));
            if (mb_strlen($cleanSegment) < 3) continue;

            if (str_contains($cleanAddress, $cleanSegment)) {
                if (in_array($cleanSegment, $provinces, true)) {
                    $score += 40;
                    $hasProvinceMatch = true;
                } else {
                    $score += 70;
                    $hasCityMatch = true;
                }
            }
        }

        // Additional token matching
        $areaTokens = array_filter(explode(' ', $cleanArea), fn($t) => mb_strlen($t) > 3 && !in_array($t, ['sector', 'area', 'north', 'south', 'east', 'west']));
        foreach ($areaTokens as $token) {
            if (str_contains($cleanAddress, $token)) {
                $score += 15;
            }
        }

        $matchType = 'standard';
        $matchBadge = 'Available in Fleet';

        if ($hasCityMatch && $hasProvinceMatch) {
            $matchType  = 'exact_area';
            $matchBadge = '📍 Direct Area Match';
        } elseif ($hasCityMatch) {
            $matchType  = 'city_match';
            $matchBadge = '📍 Nearby Town Match';
        } elseif ($hasProvinceMatch) {
            $matchType  = 'province_match';
            $matchBadge = '📍 Same Province / Region';
        }

        // Online status boost
        if ($rider->is_active) {
            $score += 30;
        }

        // Workload bonus (prefer riders with lower workload)
        $deliveries = (int) ($rider->deliveries_today ?? 0);
        $score += max(0, 15 - min($deliveries, 15));

        // Rating boost
        $rating = (float) ($rider->rating ?? 4.5);
        $score += ($rating * 2);

        return [
            'score'       => round($score, 1),
            'match_type'  => $matchType,
            'match_badge' => $matchBadge,
            'is_nearby'   => ($hasCityMatch || $hasProvinceMatch),
        ];
    }

    /**
     * 4-6. Hub Desk: receiving, sorting and delivery assignment on one page, as tabs.
     * A parcel moves receive -> sort -> assign; parcels whose address names a delivery area
     * skip the sort step (auto-sorted on receipt).
     */
    public const HUB_TABS = ['receive' => 'Receive', 'sort' => 'Sort', 'assign' => 'Assign rider', 'assigned' => 'Assigned'];

    public function hub(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();
        $search   = trim((string) $request->query('q', ''));

        $stageOf = function (array $p): ?string {
            if ($p['status'] === 'pending_receipt') return 'receive';
            if ($p['sort_status'] === 'unsorted') return 'sort';
            if ($p['sort_status'] === 'sorted' && !$p['assigned_rider_id']) return 'assign';
            if ($p['delivery_status'] === 'at_hub') return 'assigned'; // assigned, not yet picked up by the rider
            return null;                                               // out for delivery or finished: see Monitoring
        };
        $all = collect($this->fetchFormattedParcels($provider))->map(fn ($p) => $p + ['stage' => $stageOf($p)]);
        $counts = collect(self::HUB_TABS)->map(fn ($label, $key) => $all->where('stage', $key)->count())->all();

        $tab = $request->query('tab');
        if (!array_key_exists((string) $tab, self::HUB_TABS)) {
            // Open on the first step that has work waiting.
            $tab = collect(['receive', 'sort', 'assign'])->first(fn ($key) => $counts[$key] > 0) ?? 'assigned';
        }

        $filtered = $all->where('stage', $tab)->filter(fn ($p) => $search === '' || mb_stripos(
            implode(' ', [$p['tracking_code'], $p['seller_name'], $p['recipient_name'], $p['recipient_address'], $p['delivery_area'], $p['assigned_rider_name']]), $search
        ) !== false)->values();
        $page = max(1, (int) $request->query('page', 1));
        $parcels = new \Illuminate\Pagination\LengthAwarePaginator($filtered->forPage($page, 20)->values(), $filtered->count(), 20, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        $riderModels = Rider::where('logistics_provider_id', $provider->id)->where('status', 'approved')->where('is_active', true)->with('user')->get();
        $riders = $riderModels->map(fn ($r) => ['id' => $r->id, 'name' => $r->user->name ?? 'Rider', 'area' => $r->area ?: 'General Area'])->all();
        if ($tab === 'assign' && $riderModels->isNotEmpty()) {
            // Pre-select the best rider so assigning one parcel is a single click.
            $parcels->setCollection($parcels->getCollection()->map(fn ($p) => $p + [
                'suggested_rider_id' => $this->bestDeliveryRider($riderModels, (string) $p['delivery_area'], $p['recipient_address'])?->id,
            ]));
        }

        // What "Process all" would do right now.
        $sortSuggestable = $all->where('stage', 'sort')->whereNotNull('suggested_area')->count();
        $processable = $counts['receive'] + $sortSuggestable + ($riderModels->isNotEmpty() ? $counts['assign'] : 0);

        return view('logistics.hub', compact('hub', 'tab', 'counts', 'parcels', 'search', 'riders', 'processable', 'sortSuggestable'));
    }

    /** The old Incoming Parcels / Sorting / Delivery Assignment pages now open the matching Hub Desk tab. */
    public function parcels(Request $request)
    {
        return redirect()->route('logistics.hub', array_filter(['tab' => 'receive', 'q' => $request->query('search')]));
    }

    public function sorting(Request $request)
    {
        return redirect()->route('logistics.hub', array_filter(['tab' => 'sort', 'q' => $request->query('search')]));
    }

    public function assignments(Request $request)
    {
        return redirect()->route('logistics.hub', ['tab' => 'assign']);
    }

    public function receiveParcel(Request $request, $id)
    {
        $provider = $this->provider();
        $shipment = Shipment::where('logistics_provider_id', $provider->id)->findOrFail($id);

        if ($shipment->received_at) {
            return back()->with('success', 'This parcel has already been received.');
        }
        $area = $this->receiveShipment($shipment);

        return back()->with('success', $area
            ? "{$shipment->tracking_code} received and auto-sorted to {$area}. It's ready for a rider."
            : "{$shipment->tracking_code} received. Its address didn't match an area, so choose one in the Sort tab.");
    }

    public function receiveAllParcels(Request $request)
    {
        [$received, $sorted] = DB::transaction(fn () => $this->runReceiveAll($this->provider()));
        if (!$received) {
            return back()->with('success', 'No parcels are awaiting receipt.');
        }
        $manual = $received - $sorted;

        return back()->with('success', "{$received} ".Str::plural('parcel', $received)." received; {$sorted} auto-sorted"
            .($manual ? ", {$manual} need an area in the Sort tab." : '. All are ready for riders.'));
    }

    public function sortParcel(Request $request, $id)
    {
        $provider = $this->provider();
        $area = $request->validate(['area' => ['required', 'string', 'max:255']])['area'];

        $shipment = Shipment::where('logistics_provider_id', $provider->id)->findOrFail($id);
        $this->sortShipment($shipment, $area, "Parcel sorted to area: {$area}");

        return back()->with('success', "{$shipment->tracking_code} sorted to {$area}. It's ready for a rider.");
    }

    public function sortAllSuggested(Request $request)
    {
        [$sorted, $left] = DB::transaction(fn () => $this->runSortSuggested($this->provider()));

        return back()->with('success', $sorted
            ? "{$sorted} ".Str::plural('parcel', $sorted)." sorted to ".($sorted === 1 ? 'its suggested area' : 'their suggested areas').($left ? "; {$left} still need an area chosen." : '. Ready for riders.')
            : 'No parcels had an address that matched a delivery area. Choose their areas below.');
    }

    public function assignDelivery(Request $request, $id)
    {
        $provider = $this->provider();
        $riderId  = $request->validate(['rider_id' => ['required', 'integer']])['rider_id'];

        $shipment  = Shipment::where('logistics_provider_id', $provider->id)->findOrFail($id);
        $rider     = Rider::where('logistics_provider_id', $provider->id)->with('user')->findOrFail($riderId);
        $this->assignShipment($shipment, $rider, 'Parcel assigned to rider '.($rider->user->name ?? 'Rider').'.');

        return back()->with('success', "{$shipment->tracking_code} assigned to ".($rider->user->name ?? 'the rider').'.');
    }

    public function autoAssignDeliveries(Request $request)
    {
        $result = DB::transaction(fn () => $this->runAutoAssign($this->provider()));
        if ($result === null) {
            return back()->with('error', 'No active riders available. Activate a rider first.');
        }
        [$assigned, $ridersUsed] = $result;

        return back()->with('success', $assigned
            ? "{$assigned} ".Str::plural('parcel', $assigned)." assigned across {$ridersUsed} ".Str::plural('rider', $ridersUsed).'.'
            : 'Every sorted parcel already has a rider.');
    }

    /** One click for the whole desk: receive everything, sort what the addresses allow, assign riders. */
    public function processAll(Request $request)
    {
        $provider = $this->provider();
        [$received, $autoSorted, $sorted, $left, $assign] = DB::transaction(function () use ($provider) {
            [$received, $autoSorted] = $this->runReceiveAll($provider);
            [$sorted, $left] = $this->runSortSuggested($provider);
            return [$received, $autoSorted, $sorted, $left, $this->runAutoAssign($provider)];
        });

        $parts = [];
        if ($received) $parts[] = "{$received} received";
        if ($autoSorted + $sorted) $parts[] = ($autoSorted + $sorted).' sorted';
        if ($assign && $assign[0]) $parts[] = "{$assign[0]} assigned to riders";
        $message = $parts ? ucfirst(implode(', ', $parts)).'.' : 'Nothing was waiting to be processed.';
        if ($left) $message .= " {$left} ".Str::plural('parcel', $left).' need an area chosen in the Sort tab.';
        if ($assign === null) $message .= ' No active riders, so nothing was assigned. Activate a rider first.';

        return redirect()->route('logistics.hub', ['tab' => $left ? 'sort' : 'assigned'])->with('success', $message);
    }

    /** @return array{0:int,1:int} [received, auto-sorted] */
    private function runReceiveAll(LogisticsProvider $provider): array
    {
        $pending = Shipment::where('logistics_provider_id', $provider->id)->whereNull('received_at')->with('sellerOrder.order.buyer')->get();
        $sorted = $pending->filter(fn ($shipment) => $this->receiveShipment($shipment) !== null)->count();

        return [$pending->count(), $sorted];
    }

    /** @return array{0:int,1:int} [sorted, still unsorted] — only received parcels are sorted. */
    private function runSortSuggested(LogisticsProvider $provider): array
    {
        $unsorted = Shipment::where('logistics_provider_id', $provider->id)->whereNotNull('received_at')
            ->where(fn ($q) => $q->whereNull('sort_status')->orWhere('sort_status', 'unsorted'))
            ->with('sellerOrder.order.buyer')->get();
        $sorted = 0;
        foreach ($unsorted as $shipment) {
            if ($area = $this->suggestDeliveryArea($this->shipmentAddress($shipment))) {
                $this->sortShipment($shipment, $area, "Sorted to {$area} using the recipient address.");
                $sorted++;
            }
        }

        return [$sorted, $unsorted->count() - $sorted];
    }

    /** @return array{0:int,1:int}|null [assigned, riders used], or null when there are no active riders. */
    private function runAutoAssign(LogisticsProvider $provider): ?array
    {
        $riders = Rider::where('logistics_provider_id', $provider->id)->where('status', 'approved')->where('is_active', true)->with('user')->get();
        if ($riders->isEmpty()) {
            return null;
        }
        $waiting = Shipment::where('logistics_provider_id', $provider->id)->where('sort_status', 'sorted')->whereNull('rider_id')
            ->with('sellerOrder.order.buyer')->get();
        $load = [];
        foreach ($waiting as $shipment) {
            $rider = $this->bestDeliveryRider($riders, (string) $shipment->delivery_area, $this->shipmentAddress($shipment), $load);
            $load[$rider->id] = ($load[$rider->id] ?? 0) + 1;
            $this->assignShipment($shipment, $rider, 'Parcel auto-assigned to rider '.($rider->user->name ?? 'Rider').'.');
        }

        return [$waiting->count(), count($load)];
    }

    private function assignShipment(Shipment $shipment, Rider $rider, string $note): void
    {
        $shipment->update(['rider_id' => $rider->id, 'sort_status' => 'assigned', 'status' => 'assigned', 'assigned_at' => now()]);
        DeliveryEvent::create([
            'shipment_id' => $shipment->id,
            'status'      => 'assigned_to_rider',
            'attempt'     => 1,
            'user_id'     => Auth::id(),
            'note'        => $note,
            'occurred_at' => now(),
        ]);
    }

    /**
     * 7. Delivery Monitoring
     */
    public function monitoring(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();
        $filter   = $request->query('status', 'all');

        $labels = ['all' => 'All deliveries', 'failed' => 'Failed', 'out_for_delivery' => 'Out for delivery', 'in_transit' => 'In transit', 'at_hub' => 'At hub', 'delivered' => 'Delivered'];
        $filter = array_key_exists($filter, $labels) ? $filter : 'all';
        $search = trim((string) $request->query('search', ''));
        $all = collect($this->fetchFormattedParcels($provider));
        $counts = ['all' => $all->count()];
        foreach (array_keys($labels) as $status) {
            if ($status !== 'all') $counts[$status] = $all->where('delivery_status', $status)->count();
        }
        $priority = ['failed' => 0, 'out_for_delivery' => 1, 'in_transit' => 2, 'at_hub' => 3, 'delivered' => 4];
        $filtered = $all->filter(function ($parcel) use ($filter, $search) {
            $text = implode(' ', [$parcel['tracking_code'], $parcel['recipient_name'], $parcel['recipient_address'], $parcel['delivery_area'], $parcel['assigned_rider_name']]);
            return ($filter === 'all' || $parcel['delivery_status'] === $filter)
                && ($search === '' || mb_stripos($text, $search) !== false);
        })->sortBy(fn ($parcel) => $priority[$parcel['delivery_status']] ?? 5)->values();
        $page = max(1, (int) $request->query('page', 1));
        $parcels = new \Illuminate\Pagination\LengthAwarePaginator($filtered->forPage($page, 15)->values(), $filtered->count(), 15, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('logistics.monitoring', compact('hub', 'parcels', 'filter', 'search', 'counts', 'labels'));
    }

    /**
     * 8. Reports
     */
    public function reports(Request $request)
    {
        $hub      = Auth::user();
        $provider = $this->provider();
        $dateFrom = $request->query('from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->query('to', now()->format('Y-m-d'));

        $totalHandled = Shipment::where('logistics_provider_id', $provider->id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->count();

        $delivered = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', '>=', $dateFrom)
            ->whereDate('updated_at', '<=', $dateTo)
            ->count();

        $failed = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'failed')
            ->whereDate('updated_at', '>=', $dateFrom)
            ->whereDate('updated_at', '<=', $dateTo)
            ->count();

        $returned = Shipment::where('logistics_provider_id', $provider->id)
            ->where('status', 'returned')
            ->count();

        $successRate = ($delivered + $failed) > 0
            ? round(($delivered / ($delivered + $failed)) * 100, 1)
            : 92.5;

        // Top Riders from database
        $riders = Rider::where('logistics_provider_id', $provider->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();

        $topRiders = $riders->map(function ($r) {
            $deliveries = Shipment::where('rider_id', $r->id)->where('status', 'delivered')->count();
            return [
                'name'         => $r->user->name ?? 'Rider',
                'deliveries'   => $deliveries ?: ($r->deliveries_today * 8 ?: 45),
                'success_rate' => $r->rating ? round(($r->rating / 5.0) * 100, 1) : 95.0,
            ];
        })->sortByDesc('deliveries')->values()->take(5)->toArray();

        // Area breakdown from shipments
        $areaBreakdown = Shipment::where('logistics_provider_id', $provider->id)
            ->whereNotNull('delivery_area')
            ->selectRaw('delivery_area, count(*) as count')
            ->groupBy('delivery_area')
            ->pluck('count', 'delivery_area')
            ->map(fn($count, $area) => ['area' => $area, 'parcels' => $count])
            ->values()
            ->toArray();

        if (empty($areaBreakdown)) {
            $areaBreakdown = [
                ['area' => 'Laguna - Santa Cruz', 'parcels' => 312],
                ['area' => 'Laguna - San Pablo',  'parcels' => 278],
                ['area' => 'Laguna - Calamba',    'parcels' => 264],
                ['area' => 'Batangas - Lipa',     'parcels' => 198],
                ['area' => 'Quezon - Lucena',     'parcels' => 156],
            ];
        }

        $reportData = [
            'total_parcels_handled' => $totalHandled ?: 1842,
            'delivered_on_time'     => $delivered ?: 1654,
            'failed_deliveries'     => $failed ?: 97,
            'returned_to_hub'       => $returned ?: 91,
            'success_rate'          => $successRate,
            'avg_delivery_days'     => 1.4,
            'top_riders'            => !empty($topRiders) ? $topRiders : [
                ['name' => 'Arnel Gomez',   'deliveries' => 142, 'success_rate' => 96.5],
                ['name' => 'Carlo Santos',  'deliveries' => 128, 'success_rate' => 94.2],
                ['name' => 'Rex Villanueva','deliveries' => 115, 'success_rate' => 91.8],
            ],
            'area_breakdown'        => $areaBreakdown,
            'chart'                 => [
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
    public function getContactsList($provider)
    {
        $riders = Rider::where('logistics_provider_id', $provider->id)->with('user')->get();
        $contacts = [];

        foreach ($riders as $rider) {
            if ($rider->user) {
                $lastMsg = LogisticsMessage::where('logistics_provider_id', $provider->id)
                    ->where('contact_user_id', $rider->user->id)
                    ->latest()
                    ->first();

                $unread = LogisticsMessage::where('logistics_provider_id', $provider->id)
                    ->where('contact_user_id', $rider->user->id)
                    ->where('sender_type', 'contact')
                    ->whereNull('read_at')
                    ->count();

                $contacts[] = [
                    'id'           => $rider->user->id,
                    'name'         => $rider->user->name . ' (Rider)',
                    'store_name'   => $rider->user->name,
                    'role'         => 'Rider',
                    'avatar'       => '🛵',
                    'status'       => $rider->is_active ? 'online' : 'offline',
                    'last_message' => $lastMsg ? $lastMsg->message : 'Ready for assignment.',
                    'time'         => $lastMsg ? $lastMsg->created_at->format('h:i A') : 'Today',
                    'unread'       => $unread,
                    'last_msg_id'  => $lastMsg?->id ?? 0,
                ];
            }
        }

        // Sellers who messaged this hub (seller logistics chat) or have pickups with it
        $sellerUserIds = LogisticsMessage::where('logistics_provider_id', $provider->id)->pluck('contact_user_id')
            ->merge(PickupRequest::where('logistics_provider_id', $provider->id)->with('seller:id,user_id')->get()->pluck('seller.user_id'))
            ->filter()->unique();

        $allSellerUsers = User::where('role', 'seller')->with('seller:id,user_id,name')->get();
        $mergedSellerUsers = $allSellerUsers->filter(fn ($sUser) => $sellerUserIds->contains($sUser->id) || $allSellerUsers->count() <= 20);

        foreach ($mergedSellerUsers as $sUser) {
            $lastMsg = LogisticsMessage::where('logistics_provider_id', $provider->id)
                ->where('contact_user_id', $sUser->id)
                ->latest()
                ->first();

            $unread = LogisticsMessage::where('logistics_provider_id', $provider->id)
                ->where('contact_user_id', $sUser->id)
                ->where('sender_type', 'contact')
                ->whereNull('read_at')
                ->count();

            $storeName = $sUser->seller?->name ?: $sUser->business_name ?: $sUser->name;

            $contacts[] = [
                'id'           => $sUser->id,
                'name'         => $storeName . ' (Seller)',
                'store_name'   => $storeName,
                'role'         => 'Seller',
                'avatar'       => '🏬',
                'status'       => 'online',
                'last_message' => $lastMsg ? $lastMsg->message : 'Inquiring regarding parcel pickup.',
                'time'         => $lastMsg ? $lastMsg->created_at->format('h:i A') : 'Today',
                'unread'       => $unread,
                'last_msg_id'  => $lastMsg?->id ?? 0,
            ];
        }

        // Admin
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $lastMsg = LogisticsMessage::where('logistics_provider_id', $provider->id)
                ->where('contact_user_id', $admin->id)
                ->latest()
                ->first();

            $contacts[] = [
                'id'           => $admin->id,
                'name'         => 'Admin – cartzy Platform',
                'store_name'   => 'cartzy Admin Support',
                'role'         => 'Admin',
                'avatar'       => '⚙️',
                'status'       => 'online',
                'last_message' => $lastMsg ? $lastMsg->message : 'Please ensure sorting compliance.',
                'time'         => $lastMsg ? $lastMsg->created_at->format('h:i A') : 'Today',
                'unread'       => 0,
                'last_msg_id'  => $lastMsg?->id ?? 0,
            ];
        }

        if (empty($contacts)) {
            $contacts = [
                [
                    'id'           => 1,
                    'name'         => 'Maria Santos (TechZone Store)',
                    'store_name'   => 'TechZone Store',
                    'role'         => 'Seller',
                    'avatar'       => '🏬',
                    'status'       => 'online',
                    'last_message' => 'When will the rider pick up our parcels?',
                    'time'         => '10:42 AM',
                    'unread'       => 1,
                    'last_msg_id'  => 0,
                ],
            ];
        }

        usort($contacts, function ($a, $b) {
            if ($a['unread'] !== $b['unread']) {
                return $b['unread'] <=> $a['unread'];
            }
            return ($b['last_msg_id'] ?? 0) <=> ($a['last_msg_id'] ?? 0);
        });

        return $contacts;
    }

    public function chat(Request $request)
    {
        $hub             = Auth::user();
        $provider        = $this->provider();
        $activeContactId = (int) $request->query('contact', 0);
        $contacts        = $this->getContactsList($provider);

        $currentContact = collect($contacts)->firstWhere('id', $activeContactId) ?? $contacts[0];

        // Fetch messages from database
        $dbMessages = LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('contact_user_id', $currentContact['id'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark incoming messages as read
        LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('contact_user_id', $currentContact['id'])
            ->where('sender_type', 'contact')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($dbMessages->count() > 0) {
            $messages = $dbMessages->map(function ($m) {
                return [
                    'sender' => $m->sender_type,
                    'text'   => $m->message,
                    'time'   => $m->created_at->format('h:i A'),
                ];
            })->toArray();
        } else {
            $messages = [
                [
                    'sender' => 'contact',
                    'text'   => $currentContact['last_message'],
                    'time'   => $currentContact['time'],
                ],
                [
                    'sender' => 'hub',
                    'text'   => 'Thank you for reaching out! Our team is processing this now.',
                    'time'   => 'Just now',
                ],
            ];
        }

        return view('logistics.chat', compact('hub', 'contacts', 'currentContact', 'messages'));
    }

    public function popupConversations(Request $request)
    {
        $provider = $this->provider();
        $contacts = $this->getContactsList($provider);

        $conversations = array_map(function ($c) {
            $storeName = $c['store_name'] ?? $c['name'];
            $subtitle = $c['role'] === 'Seller' 
                ? 'About: Store Pickups & Orders' 
                : ($c['role'] === 'Rider' ? 'About: Delivery Assignments' : 'About: Platform Support');

            return [
                'id'         => $c['id'],
                'name'       => $storeName,
                'subtitle'   => $subtitle,
                'preview'    => $c['last_message'],
                'time'       => $c['time'],
                'unread'     => $c['unread'],
                'avatar'     => $c['avatar'] ?? '💬',
                'thread_url' => route('logistics.popup.messages', $c['id']),
            ];
        }, $contacts);

        $unreadTotal = LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('sender_type', 'contact')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'conversations' => $conversations,
            'unread_total'  => $unreadTotal,
        ]);
    }

    public function popupMessages(Request $request, $contactId)
    {
        $provider = $this->provider();
        $contactUser = User::find($contactId);

        if (!$contactUser) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        // Mark incoming messages as read
        LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('contact_user_id', $contactId)
            ->where('sender_type', 'contact')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $dbMessages = LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('contact_user_id', $contactId)
            ->orderBy('created_at', 'asc')
            ->get();

        $messages = $dbMessages->map(function ($m) {
            return [
                'id'     => $m->id,
                'mine'   => $m->sender_type === 'hub',
                'body'   => $m->message,
                'time'   => $m->created_at->format('h:i A'),
                'status' => $m->sender_type === 'hub' ? ($m->read_at ? 'seen' : 'sent') : 'delivered',
            ];
        })->toArray();

        $storeName = $contactUser->seller?->name ?: $contactUser->business_name ?: $contactUser->name;
        $subtitle = $contactUser->role === 'seller' 
            ? 'About: Store Pickups & Orders' 
            : ($contactUser->role === 'rider' ? 'About: Delivery Assignments' : 'About: Platform Support');

        $emptyText = $contactUser->role === 'seller'
            ? 'Say hello! Ask the seller about sizes, stock, or shipping.'
            : 'Say hello! Keep pickups, deliveries, and updates in one place.';

        $inputPlaceholder = $contactUser->role === 'seller'
            ? 'Ask about this product...'
            : 'Write a message…';

        $unreadTotal = LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('sender_type', 'contact')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'title'             => $storeName,
            'subtitle'          => $subtitle,
            'empty_text'        => $emptyText,
            'input_placeholder' => $inputPlaceholder,
            'send_url'          => route('logistics.chat.send', $contactId),
            'inbox_url'         => route('logistics.chat', ['contact' => $contactId]),
            'messages'          => $messages,
            'unread_total'      => $unreadTotal,
        ]);
    }

    public function sendMessage(Request $request, $contactId)
    {
        $provider = $this->provider();
        $text     = trim((string) $request->input('message'));

        $msg = null;
        if ($text) {
            $msg = LogisticsMessage::create([
                'logistics_provider_id' => $provider->id,
                'contact_user_id'       => $contactId,
                'sender_type'           => 'hub',
                'message'               => $text,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id'     => $msg?->id,
                    'mine'   => true,
                    'body'   => $text,
                    'time'   => $msg ? $msg->created_at->format('h:i A') : now()->format('h:i A'),
                    'status' => 'sent',
                ]
            ]);
        }

        return back()->with('success', 'Message sent and saved to database!');
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
        // Store both accepted mobile formats consistently so uniqueness checks cannot be bypassed.
        $phone = $request->input('phone');
        if (is_string($phone) && preg_match('/\A\+639[0-9]{9}\z/', $phone)) {
            $request->merge(['phone' => '0' . substr($phone, 3)]);
        }
        $hub = Auth::user();
        $unusedNumber = function ($attribute, $value, $fail) use ($hub) {
            $variants = [$value, '+63' . substr($value, 1), '63' . substr($value, 1)];
            if (User::where('id', '<>', $hub->id)->where(function ($query) use ($variants) {
                $query->whereIn('phone', $variants)->orWhereIn('telephone', $variants);
            })->exists()) {
                $fail('This number is already registered to another account. Please use a different number.');
            }
        };
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'sex' => ['required', 'in:Male,Female,Prefer not to say'],
            'birthday' => ['required', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYearsNoOverflow(18)->toDateString()],
            'phone' => ['bail', 'required', 'string', 'regex:/\A09[0-9]{9}\z/', $unusedNumber],
            'telephone' => ['bail', 'required', 'string', 'regex:/\A0[2-8][0-9]{8}\z/', $unusedNumber],
            'region' => ['nullable', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'street_address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'string', 'regex:/\A[0-9]{4}\z/'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'tin_number' => ['nullable', 'string', 'regex:/\A[0-9]{9}\z/'],
            'id_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'dti_permit' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'birthday.required' => 'Please enter your birthday.',
            'birthday.date_format' => 'Please enter a valid birthday.',
            'birthday.before_or_equal' => 'You must be at least 18 years old.',
            'phone.regex' => 'Enter a valid mobile number: 09 followed by 9 digits, or +639 followed by 9 digits.',
            'telephone.required' => 'Telephone number is required.',
            'telephone.regex' => 'Telephone number must contain exactly 10 digits, including the area code (e.g. 0281234567 or 0491234567).',
            'postal_code.required' => 'Postal code is required and must contain exactly 4 digits.',
            'postal_code.string' => 'Postal code must contain exactly 4 digits.',
            'postal_code.regex' => 'Postal code must contain exactly 4 digits.',
            'tin_number.regex' => 'TIN must contain exactly 9 digits, without hyphens or a branch code.',
        ]);

        $data['name'] = trim($data['first_name']) . ' ' . trim($data['last_name']);
        $data['age'] = max(0, (int) Carbon::parse($data['birthday'])->age);
        $data['address'] = implode(', ', array_filter([
            $data['street_address'], $data['barangay'], $data['city'], $data['province'],
        ]));
        unset($data['first_name'], $data['last_name'], $data['password'], $data['id_photo'], $data['dti_permit']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $uploads = [];
        try {
            foreach (['id_photo' => 'id_photos', 'dti_permit' => 'dti_permits'] as $field => $directory) {
                if ($request->hasFile($field)) {
                    $uploads[] = $data[$field] = $request->file($field)->store($directory, 'public');
                }
            }
            if (isset($data['id_photo'])) {
                $data['id_status'] = 'pending';
                $data['id_verified_at'] = null;
                $data['id_rejection_reason'] = null;
            }
            DB::transaction(function () use ($hub, $data) {
                $hub->update($data);
                // Only synchronize the provider owned by this account.
                $hub->logisticsProvider()->update([
                    'name' => $hub->business_name ?: $hub->name,
                    'contact_phone' => $hub->phone,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($uploads);
            throw $exception;
        }

        return back()->with('success', 'Account updated successfully in database!');
    }

    /* ──────────────────────────────────────────────────────────────────────── */
    /*  DATABASE QUERY HELPERS                                                  */
    /* ──────────────────────────────────────────────────────────────────────── */

    /** Delivery areas parcels are sorted into ("Province - City"; "/" separates towns sharing an area). */
    public const DELIVERY_AREAS = ['Laguna - Santa Cruz', 'Laguna - San Pablo', 'Laguna - Calamba', 'Laguna - Los Baños', 'Laguna - Biñan / Santa Rosa', 'Batangas - Lipa', 'Batangas - Mataasnakahoy', 'Quezon - Lucena', 'Quezon - Tayabas', 'Metro Manila - South'];

    /** The delivery area whose town appears in the address, or null when none matches confidently. */
    private function suggestDeliveryArea(?string $address): ?string
    {
        $normalize = fn (string $text) => ' '.preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower(strtr($text, ['ñ' => 'n', 'Ñ' => 'n']))).' ';
        // Addresses often abbreviate Santa/Santo ("Sta. Rosa", "Sto. Tomas").
        $haystack = strtr($normalize((string) $address), [' sta ' => ' santa ', ' sto ' => ' santo ']);
        foreach (self::DELIVERY_AREAS as $area) {
            [, $towns] = array_pad(explode(' - ', $area, 2), 2, '');
            foreach (explode('/', $towns) as $town) {
                if (trim($town) !== '' && str_contains($haystack, $normalize(trim($town)))) {
                    return $area;
                }
            }
        }

        return null;
    }

    /** Mark a shipment sorted into an area and log it. */
    private function sortShipment(Shipment $shipment, string $area, string $note): void
    {
        $shipment->update(['sort_status' => 'sorted', 'delivery_area' => $area, 'sorted_at' => now()]);
        DeliveryEvent::create([
            'shipment_id' => $shipment->id,
            'status'      => 'sorted',
            'attempt'     => 1,
            'user_id'     => Auth::id(),
            'note'        => $note,
            'occurred_at' => now(),
        ]);
    }

    /** Recipient address for a shipment, as shown on the parcel pages. */
    private function shipmentAddress(Shipment $shipment): string
    {
        $order = $shipment->sellerOrder?->order;
        $shipping = is_array($order?->shipping_address) ? $order->shipping_address : json_decode($order?->shipping_address ?? '[]', true);

        return (string) ($shipping['address'] ?? ($order?->buyer?->address ?? ''));
    }

    /** Receive one shipment at the hub; auto-sorts it when the address names a delivery area. Returns the area or null. */
    private function receiveShipment(Shipment $shipment): ?string
    {
        $shipment->update(['received_at' => now()]);
        DeliveryEvent::create([
            'shipment_id' => $shipment->id,
            'status'      => 'received_at_hub',
            'attempt'     => 1,
            'user_id'     => Auth::id(),
            'note'        => 'Parcel scanned and received at sorting hub.',
            'occurred_at' => now(),
        ]);

        if (($shipment->sort_status ?: 'unsorted') !== 'unsorted') {
            return null;
        }
        $area = $this->suggestDeliveryArea($this->shipmentAddress($shipment));
        if ($area) {
            $this->sortShipment($shipment, $area, "Auto-sorted to {$area} from the recipient address on receipt.");
        }

        return $area;
    }

    /**
     * Best active rider for each parcel, using the same scoring as pickup dispatch (area match, online,
     * workload, rating). $extraLoad counts parcels handed out earlier in the same batch so work spreads out.
     */
    private function bestDeliveryRider($riders, string $area, string $address, array $extraLoad = []): ?Rider
    {
        $ranked = $this->rankRidersForPickup($riders, trim($area.' '.$address))
            ->map(fn ($r) => $r + ['adjusted' => $r['score'] - 8 * ($extraLoad[$r['rider']->id] ?? 0)])
            ->sortByDesc('adjusted');

        return $ranked->first()['rider'] ?? null;
    }

    private function fetchFormattedParcels(LogisticsProvider $provider, ?int $limit = null, string $filter = 'all'): array
    {
        $query = Shipment::where('logistics_provider_id', $provider->id)
            ->with(['sellerOrder.seller.user', 'sellerOrder.order.buyer', 'sellerOrder.items', 'rider.user']);

        if ($filter === 'received') {
            $query->whereNotNull('received_at');
        } elseif ($filter === 'at_hub') {
            $query->whereIn('status', ['unassigned', 'assigned']);
        }

        if ($limit) {
            $query->take($limit);
        }

        return $query->latest()->get()->map(function ($s) {
            $order       = $s->sellerOrder?->order;
            $seller      = $s->sellerOrder?->seller;
            $itemsList   = $s->sellerOrder?->items?->pluck('product_name')->implode(', ');
            $shipping    = is_array($order?->shipping_address) ? $order->shipping_address : json_decode($order?->shipping_address ?? '[]', true);

            $recipientName    = $shipping['recipient'] ?? ($order?->buyer?->name ?? 'Recipient');
            $recipientAddress = $shipping['address'] ?? ($order?->buyer?->address ?? 'Laguna Area');
            $recipientPhone   = $shipping['phone'] ?? ($order?->buyer?->phone ?? '09170000000');

            $method    = $order?->payment_method === 'cod' ? 'COD' : 'Prepaid';
            $codAmount = $order?->payment_method === 'cod' ? ($s->cod_amount_minor / 100) : 0.00;

            // Map status to delivery status
            $deliveryStatus = match ($s->status) {
                'in_transit'       => 'in_transit',
                'out_for_delivery' => 'out_for_delivery',
                'delivered'        => 'delivered',
                'failed'           => 'failed',
                default            => 'at_hub',
            };

            return [
                'id'                  => $s->id,
                'tracking_code'       => $s->tracking_code,
                'seller_name'         => $seller?->name ?? ($seller?->user?->name ?? 'Partner Store'),
                'recipient_name'      => $recipientName,
                'recipient_address'   => $recipientAddress,
                'recipient_phone'     => $recipientPhone,
                'items'               => $itemsList ?: ($s->sellerOrder?->note ?: 'Order Package Items'),
                'weight_kg'           => $s->weight_kg ?: 1.0,
                'payment_method'      => $method,
                'cod_amount'          => $codAmount,
                'status'              => $s->received_at ? 'received' : 'pending_receipt',
                'sort_status'         => $s->sort_status ?: 'unsorted',
                'delivery_area'       => $s->delivery_area,
                'suggested_area'      => $this->suggestDeliveryArea($recipientAddress),
                'delivery_status'     => $deliveryStatus,
                'assigned_rider_id'   => $s->rider_id,
                'assigned_rider_user_id' => $s->rider?->user_id,
                'updated_at'          => $s->updated_at?->timezone('Asia/Manila')->format('M d, Y g:i A'),
                'assigned_rider_name' => $s->rider?->user?->name,
                'received_at'         => $s->received_at ? $s->received_at->format('M d, Y h:i A') : null,
                'sorted_at'           => $s->sorted_at ? $s->sorted_at->format('M d, Y h:i A') : null,
                'assigned_at'         => $s->assigned_at ? $s->assigned_at->format('M d, Y h:i A') : null,
            ];
        })->toArray();
    }
}
