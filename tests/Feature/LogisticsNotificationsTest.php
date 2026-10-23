<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function notification(User $user, string $title, bool $read = false): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id, 'type' => 'system', 'title' => $title,
            'message' => 'Hub update details', 'is_read' => $read,
        ]);
    }

    public function test_feed_and_badge_show_only_the_current_accounts_notifications(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $other = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->notification($user, 'New hub update');
        $this->notification($user, 'Previous update', true);
        $this->notification($other, 'Private other hub update');
        $this->actingAs($user)->get(route('logistics.notifications'))->assertOk()
            ->assertSee('New hub update')->assertSee('Previous update')
            ->assertDontSee('Private other hub update')->assertSee('Notifications, 1 unread');
        $this->get(route('logistics.notifications', ['filter' => 'unread']))->assertOk()
            ->assertSee('New hub update')->assertDontSee('Previous update');
        $this->get(route('logistics.account'))->assertOk()
            ->assertSee(route('logistics.notifications'))->assertSee('Notifications, 1 unread');
    }

    public function test_read_actions_are_scoped_to_the_current_account(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $other = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $own = $this->notification($user, 'Own update');
        $second = $this->notification($user, 'Second update');
        $foreign = $this->notification($other, 'Other update');
        $this->actingAs($user)->post(route('logistics.notifications.read', $foreign->id))->assertNotFound();
        $this->post(route('logistics.notifications.read', $own->id))->assertRedirect();
        $this->assertTrue($own->fresh()->is_read);
        $this->assertFalse($second->fresh()->is_read);
        $this->post(route('logistics.notifications.readAll'))->assertRedirect();
        $this->assertTrue($second->fresh()->is_read);
        $this->assertFalse($foreign->fresh()->is_read);
        $this->get(route('logistics.notifications', ['filter' => 'unread']))->assertOk()
            ->assertSee('There are no unread notifications.')->assertSee('Notifications, 0 unread');
    }

    public function test_feed_handles_empty_and_paginated_results(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->actingAs($user)->get(route('logistics.notifications'))->assertOk()->assertSee('No notifications yet');
        for ($i = 0; $i < 16; $i++) {
            $this->notification($user, 'Update ' . $i);
        }
        $this->get(route('logistics.notifications'))->assertOk()
            ->assertViewHas('notifications', fn ($items) => $items->count() === 15 && $items->total() === 16);
        $this->get(route('logistics.notifications', ['page' => 2]))->assertOk()
            ->assertViewHas('notifications', fn ($items) => $items->count() === 1);
    }

    public function test_other_roles_cannot_access_logistics_notifications(): void
    {
        $user = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($user)->get(route('logistics.notifications'))->assertForbidden();
        $this->post(route('logistics.notifications.readAll'))->assertForbidden();
        $this->post(route('logistics.notifications.read', 1))->assertForbidden();
    }
}
