<?php

namespace App\Services;

use App\Models\AdminDispute;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Product;
use App\Models\Rider;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;

/**
 * What Cozy may read for admin and logistics accounts. Read-only totals, so no customer's
 * name, address or phone number ever reaches the chat.
 */
class StaffBuddyData
{
    public function platform(): array
    {
        $earned = SellerOrder::whereIn('status', ['delivered', 'completed'])
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'));

        return [
            'pending_account_reviews' => User::where('role', '!=', User::ROLE_ADMIN)
                ->where(fn ($query) => $query->where('id_status', 'pending')
                    ->orWhere(fn ($pending) => $pending->where('status', 'pending')->where('id_status', '!=', 'rejected')))
                ->count(),
            'pending_products' => Product::where('approval_status', Product::APPROVAL_PENDING)->count(),
            'open_disputes' => AdminDispute::where('status', 'open')->count(),
            'orders' => Order::count(),
            'products' => Product::count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'couriers' => User::where('role', 'courier')->count(),
            'earned_sales' => (clone $earned)->sum('subtotal_minor') / 100,
            'earned_commission' => (clone $earned)->sum('commission_minor') / 100,
        ];
    }

    public function hub(LogisticsProvider $provider): array
    {
        $shipments = Shipment::where('logistics_provider_id', $provider->id);
        $riders = Rider::where('logistics_provider_id', $provider->id);
        $today = [today()->startOfDay(), today()->endOfDay()];

        return [
            'hub_name' => $provider->name,
            'riders' => (clone $riders)->count(),
            'active_riders' => (clone $riders)->where('status', 'approved')->where('is_active', true)->count(),
            'rider_applications_pending' => (clone $riders)->where('status', 'pending')->count(),
            'pickups_today' => PickupRequest::where('logistics_provider_id', $provider->id)->whereDate('pickup_date', today())->count(),
            'parcels_in_hub' => (clone $shipments)->whereIn('status', ['unassigned', 'assigned', 'picked_up', 'in_transit'])->count(),
            'out_for_delivery' => (clone $shipments)->where('status', 'out_for_delivery')->count(),
            'delivered_today' => (clone $shipments)->where('status', 'delivered')->whereBetween('updated_at', $today)->count(),
            'failed_today' => (clone $shipments)->where('status', 'failed')->whereBetween('updated_at', $today)->count(),
        ];
    }
}
