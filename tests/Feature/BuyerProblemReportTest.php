<?php

namespace Tests\Feature;

use App\Models\{AdminDispute, LogisticsProvider, Seller, SellerOrder, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuyerProblemReportTest extends TestCase
{
    use RefreshDatabase;

    private function order(): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        Seller::create(['user_id' => $sellerUser->id, 'name' => 'Test Shop', 'slug' => 'test-shop', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $so = app(OrderPlacement::class)->place($buyer, [['id' => 1, 'name' => 'Coffee mug', 'price' => 280, 'quantity' => 2]], 'COD', 'Test address')
            ->sellerOrders()->firstOrFail();
        $so->update(['status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $this->actingAs($buyer);

        return $so;
    }

    private function report(SellerOrder $so, array $overrides = []): array
    {
        return $overrides + [
            'category' => 'damaged',
            'items' => [$so->items->first()->id],
            'requested_resolution' => 'replacement',
            'description' => 'The mug arrived cracked along the handle.',
            'photos' => [UploadedFile::fake()->create('crack.jpg', 200, 'image/jpeg')],
            'contact_phone' => '09171234567',
        ];
    }

    public function test_buyer_reports_a_problem_and_admin_sees_and_resolves_it(): void
    {
        Storage::fake('local');
        $so = $this->order();

        $this->get(route('buyer.dashboard', ['tab' => 'orders']))->assertSee('Report problem');
        $this->get(route('orders.report', $so->id))->assertOk()->assertSee('What went wrong?')->assertSee('Coffee mug');
        $this->post(route('orders.report.store', $so->id), $this->report($so))
            ->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']))->assertSessionHasNoErrors();

        $dispute = AdminDispute::firstOrFail();
        $this->assertSame($so->id, $dispute->seller_order_id);
        $this->assertSame('Item arrived damaged', $dispute->subject);
        $this->assertSame('Coffee mug', $dispute->items[0]['name']);
        Storage::disk('local')->assertExists($dispute->evidence[0]);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $so->seller->user_id, 'type' => 'dispute']);

        $this->get(route('buyer.dashboard', ['tab' => 'orders']))->assertSee('Case #'.$dispute->id)->assertDontSee('>Report problem<', false);
        $this->get(route('disputes.evidence', [$dispute->id, 0]))->assertOk();

        $buyer = $so->order->buyer;
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->get(route('disputes.evidence', [$dispute->id, 0]))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.disputes'))->assertOk()->assertSee('Reported by buyer')->assertSee('Replacement')->assertSee('09171234567');
        $this->post(route('admin.disputes.resolve', $dispute->id), ['notes' => 'Seller will send a new mug.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('app_notifications', ['user_id' => $buyer->id, 'title' => 'Your report was resolved']);

        $this->actingAs($buyer)->get(route('buyer.dashboard', ['tab' => 'orders']))->assertSee('Report resolved')->assertSee('Seller will send a new mug.');
    }

    public function test_photos_are_required_for_damage_and_only_one_open_report_is_allowed(): void
    {
        Storage::fake('local');
        $so = $this->order();

        $this->post(route('orders.report.store', $so->id), $this->report($so, ['photos' => []]))->assertSessionHasErrors('photos');
        $this->post(route('orders.report.store', $so->id), $this->report($so, ['category' => 'not_received', 'photos' => [], 'description' => 'short']))->assertSessionHasErrors('description');
        $this->assertDatabaseCount('admin_disputes', 0);

        $this->post(route('orders.report.store', $so->id), $this->report($so, ['category' => 'delivery', 'photos' => []]))->assertSessionHasNoErrors();
        $this->post(route('orders.report.store', $so->id), $this->report($so))->assertSessionHasErrors('category');
        $this->get(route('orders.report', $so->id))->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));
        $this->assertDatabaseCount('admin_disputes', 1);
    }

    public function test_only_the_owner_can_report_and_old_deliveries_are_closed(): void
    {
        $so = $this->order();
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->get(route('orders.report', $so->id))->assertNotFound();

        $this->actingAs($so->order->buyer);
        $so->update(['delivered_at' => now()->subDays(AdminDispute::REPORT_WINDOW_DAYS + 1)]);
        $this->get(route('orders.report', $so->id))->assertForbidden();
        $this->get(route('buyer.dashboard', ['tab' => 'orders']))->assertDontSee('Report problem');
    }
}
