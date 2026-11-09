<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The bell in the rider app reads the rider's own notifications. */
class RiderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_rider_sees_only_their_own_notifications(): void
    {
        $rider = User::factory()->create(['role' => 'courier', 'status' => 'active']);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        NotificationService::notify($rider->id, 'parcel_scan', 'New pickup assigned', 'Pick up 2 parcels from Laguna Gadgets.');
        NotificationService::notify($buyer->id, 'delivery', 'Out for delivery', 'Your order is on the way.');

        Sanctum::actingAs($rider);

        $this->getJson('/api/notifications/unread-count')->assertOk();
        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.title', 'New pickup assigned')
            ->assertJsonPath('unread_count', 1);

        $id = $this->getJson('/api/notifications')->json('notifications.0.id');
        $this->postJson("/api/notifications/{$id}/read")->assertOk();
        $this->getJson('/api/notifications')->assertJsonPath('unread_count', 0);
    }

    public function test_buyers_still_have_notifications_and_sellers_do_not_use_this_api(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->getJson('/api/notifications')->assertOk();

        Sanctum::actingAs(User::factory()->create(['role' => 'seller', 'status' => 'active']));
        $this->getJson('/api/notifications')->assertForbidden();
    }
}
