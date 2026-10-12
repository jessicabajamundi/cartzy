<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAccountNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_profile_accepts_and_saves_names_with_enye_exactly_as_typed(): void
    {
        $user = User::factory()->create([
            'role'   => User::ROLE_SELLER,
            'status' => 'active',
            'name'   => 'Juan Cruz',
            'business_name' => 'Old Store',
        ]);

        $shop = Seller::create([
            'user_id' => $user->id,
            'name'    => 'Old Store',
            'slug'    => 'old-store-' . strtolower(\Illuminate\Support\Str::random(6)),
            'status'  => 'approved',
        ]);

        // 1. Update Store Profile with ñ
        $response = $this->actingAs($user)->post('/seller/account/profile', [
            'store_name'       => 'Doña Maria & Niño Boutique',
            'line_of_business' => 'Fashion & Apparel',
            'phone'            => '09171234567',
            'description'      => 'Authentic piña fabrics and señorita accessories.',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $shop->refresh();

        $this->assertEquals('Doña Maria & Niño Boutique', $user->business_name);
        $this->assertEquals('Doña Maria & Niño Boutique', $shop->name);

        // 2. Update Personal Details with ñ in first name, last name, and middle initial
        $response = $this->actingAs($user)->post('/seller/account/profile', [
            'store_name'     => $shop->name,
            'first_name'     => 'Niño',
            'last_name'      => 'Peña',
            'middle_initial' => 'Ñ.',
            'sex'            => 'Male',
            'birthday'       => '1995-06-15',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertEquals('Niño Peña', $user->name);
        $this->assertEquals('Ñ.', $user->middle_initial);

        // 3. Update Pickup Address with ñ in recipient and address
        $response = $this->actingAs($user)->post('/seller/account/address', [
            'recipient'   => 'Niño Dela Peña',
            'phone'       => '09171234567',
            'line1'       => '124 Doña Soledad Ave, Parañaque City',
            'barangay'    => 'Don Bosco',
            'city'        => 'Parañaque',
            'province'    => 'Metro Manila',
            'postal_code' => '1711',
            'region'      => 'NCR',
        ]);

        $response->assertSessionHasNoErrors();
        $shop->refresh();
        $pickupAddress = $shop->pickupAddress;

        $this->assertNotNull($pickupAddress);
        $this->assertEquals('Niño Dela Peña', $pickupAddress->recipient);
        $this->assertEquals('124 Doña Soledad Ave, Parañaque City', $pickupAddress->line1);
        $this->assertEquals('Parañaque', $pickupAddress->city);

        // 4. Verify rendered page displays ñ characters without corruption
        $viewResponse = $this->actingAs($user)->get('/seller/account');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Doña Maria &amp; Niño Boutique', false);
        $viewResponse->assertSee('Niño');
        $viewResponse->assertSee('Peña');
        $viewResponse->assertSee('Ñ.');
        $viewResponse->assertSee('Niño Dela Peña');
    }
}
