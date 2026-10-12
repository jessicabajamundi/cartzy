<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAccountAgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_birthday_requires_age_eighteen_and_accepts_the_exact_birthday(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 12:00:00'));
        $user = User::factory()->create([
            'role' => User::ROLE_SELLER, 'status' => 'active', 'birthday' => '1995-01-01', 'age' => 31,
        ]);
        Seller::create(['user_id' => $user->id, 'name' => 'Age Test Shop', 'slug' => 'age-test-shop', 'status' => 'approved']);
        $this->actingAs($user);

        foreach (['2008-10-13', '2010-01-01', '2027-01-01', '2008-02-30'] as $birthday) {
            $this->post('/seller/account/profile', ['store_name' => 'Age Test Shop', 'birthday' => $birthday])
                ->assertSessionHasErrors('birthday');
            $this->assertDatabaseHas('users', ['id' => $user->id, 'birthday' => '1995-01-01', 'age' => 31]);
        }

        $this->post('/seller/account/profile', ['store_name' => 'Age Test Shop', 'birthday' => '2008-10-12'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'birthday' => '2008-10-12', 'age' => 18]);
        $this->get('/seller/account')->assertOk()->assertSee('max="2008-10-12"', false);
    }
}
