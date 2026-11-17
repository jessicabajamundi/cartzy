<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccountDeletionService
{
    private const TERMINAL_ORDER_STATUSES = [
        'delivered',
        'completed',
        'cancelled',
        'rejected',
        'returned',
        'refunded',
    ];

    private const TERMINAL_SHIPMENT_STATUSES = [
        'delivered',
        'cancelled',
        'returned',
        'returned_to_seller',
    ];

    /**
     * Validate password and role-specific active transactions, then permanently delete the user row.
     */
    public function deleteAccount(User $user, string $password): void
    {
        $this->validateDeletion($user, $password);
        $this->performDeletion($user);
    }

    public function validateDeletion(User $user, string $password): void
    {
        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'password' => ['Admin accounts cannot be deleted.'],
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        $this->ensureNoActiveTransactions($user);
    }

    public function performDeletion(User $user): void
    {
        DB::transaction(function () use ($user) {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            if (!empty($user->avatar_path) && !str_starts_with($user->avatar_path, 'http')) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->delete();
        });
    }

    private function ensureNoActiveTransactions(User $user): void
    {
        if ($user->isBuyer()) {
            $hasActiveOrders = SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id))
                ->whereNotIn('status', self::TERMINAL_ORDER_STATUSES)
                ->exists();

            if ($hasActiveOrders) {
                $this->fail('You still have active orders in progress. Please wait until they are completed or cancelled before deleting your account.');
            }
        }

        if ($user->isSeller() && $user->seller) {
            $hasActiveSellerOrders = SellerOrder::where('seller_id', $user->seller->id)
                ->whereNotIn('status', self::TERMINAL_ORDER_STATUSES)
                ->exists();

            if ($hasActiveSellerOrders) {
                $this->fail('Your store still has active or pending orders. Complete or resolve them before deleting your account.');
            }
        }

        if ($user->isCourier() && $user->rider) {
            $riderId = $user->rider->id;

            $hasActiveShipments = Shipment::where(function ($q) use ($riderId) {
                $q->where('rider_id', $riderId)
                    ->orWhere('pickup_rider_id', $riderId)
                    ->orWhere('linehaul_courier_id', $riderId);
            })
                ->whereNotIn('status', self::TERMINAL_SHIPMENT_STATUSES)
                ->exists();

            if ($hasActiveShipments) {
                $this->fail('You still have active deliveries or transit parcels assigned to you.');
            }

            $hasActivePickups = PickupRequest::where('rider_id', $riderId)
                ->whereIn('status', ['pending', 'confirmed', 'assigned', 'picked_up'])
                ->exists();

            if ($hasActivePickups) {
                $this->fail('You still have active pickup requests assigned to you.');
            }

            $hasUnremittedCod = Shipment::where('rider_id', $riderId)
                ->where('status', 'delivered')
                ->where('cod_amount', '>', 0)
                ->whereNull('cod_remitted_at')
                ->exists();

            if ($hasUnremittedCod) {
                $this->fail('Please remit your collected COD cash to the logistics hub before deleting your account.');
            }
        }

        if ($user->isLogistics() && $user->logisticsProvider) {
            $providerId = $user->logisticsProvider->id;

            $hasActiveHubShipments = Shipment::where(function ($q) use ($providerId) {
                $q->where('logistics_provider_id', $providerId)
                    ->orWhere('origin_hub_id', $providerId)
                    ->orWhere('destination_hub_id', $providerId);
            })
                ->whereNotIn('status', self::TERMINAL_SHIPMENT_STATUSES)
                ->exists();

            if ($hasActiveHubShipments) {
                $this->fail('Your logistics hub still has active parcels in transit or pending delivery.');
            }
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'account' => [$message],
        ]);
    }
}
