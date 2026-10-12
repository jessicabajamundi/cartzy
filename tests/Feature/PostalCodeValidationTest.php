<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostalCodeValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_and_seller_addresses_require_exactly_four_digits(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => 'active']);
        Seller::create(['user_id' => $seller->id, 'name' => 'Postal Test Shop', 'slug' => 'postal-test-shop', 'status' => 'approved']);
        $data = [
            'label' => 'Home', 'recipient' => 'Test Recipient', 'phone' => '09171234567',
            'line1' => '10 Main Street', 'street_address' => '10 Main Street',
            'barangay' => 'Central', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'region' => 'NCR',
        ];

        foreach ([
            [$buyer, route('account.addresses.save'), 'addressBook'],
            [$buyer, route('account.address.update'), 'default'],
            [$seller, '/seller/account/address', 'default'],
        ] as [$user, $url, $bag]) {
            $this->actingAs($user);
            foreach (['', '1', '123', '12345', '12a4', '1e03', '-123'] as $postalCode) {
                $this->post($url, $data + ['postal_code' => $postalCode])
                    ->assertSessionHasErrorsIn($bag, ['postal_code']);
            }
            $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
            foreach (['1000', '0123'] as $postalCode) {
                $this->post($url, $data + ['postal_code' => $postalCode, 'is_default' => 1])->assertSessionHasNoErrors();
                $this->assertSame($postalCode, $user->fresh()->postal_code);
            }
            $user->addresses()->delete();
        }
    }
}
