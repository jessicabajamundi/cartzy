<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerSignupDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function details(string $type = 'individual'): array
    {
        Storage::fake('public');
        Storage::fake('local');
        $category = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);
        Cache::put('otp_seller@example.com', ['otp' => '123456', 'email' => 'seller@example.com'], 600);
        $data = [
            'role' => 'seller', 'first_name' => 'Maria', 'last_name' => 'Cruz',
            'email' => 'seller@example.com', 'phone' => '+639171234567',
            'password' => 'SecurePass!123', 'password_confirmation' => 'SecurePass!123',
            'email_verification_otp' => '123456', 'shop_name' => 'Maria Store',
            'shop_category' => $category->id, 'seller_type' => $type, 'tin_number' => '123-456-789-000',
            'region' => 'NCR', 'province' => 'Metro Manila', 'city' => 'Manila',
            'barangay' => 'Barangay 1', 'street_address' => '12 Main Street', 'postal_code' => '1000',
            'id_photo' => UploadedFile::fake()->create('id.pdf', 20, 'application/pdf'),
        ];
        if ($type === 'sole_proprietorship') $data['dti_permit'] = UploadedFile::fake()->create('dti.pdf', 20, 'application/pdf');
        if ($type === 'corporation') $data['sec_certificate'] = UploadedFile::fake()->create('sec.pdf', 20, 'application/pdf');
        if ($type !== 'individual') $data['bir_certificate'] = UploadedFile::fake()->create('bir.pdf', 20, 'application/pdf');
        return $data;
    }

    public function test_details_page_renders_with_system_fields(): void
    {
        $this->details();
        $this->get('/register?role=seller')->assertOk()->assertSee('Your shop')->assertSee('Pickup address')->assertSee('Electronics')->assertSee('BIR Form 2303');
    }

    public function test_seller_can_register_without_terms_while_section_is_removed(): void
    {
        $data = $this->details();
        $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_signin_temporarily_omits_terms_section(): void
    {
        \Illuminate\Support\Facades\DB::table('platform_settings')->updateOrInsert(['key' => 'terms_of_service'], ['value' => 'Cartzy original terms for sellers.', 'updated_at' => now()]);
        $this->get('/register?role=seller')->assertOk()->assertDontSee('Cartzy original terms for sellers.')
            ->assertSee('Send verification code')->assertDontSee('name="terms_accepted"', false)
            ->assertDontSee('Read Cartzy Terms and Conditions');
    }

    public function test_individual_registration_saves_shop_and_pickup_without_birthday(): void
    {
        $this->post('/register', $this->details())->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $user = User::where('email', 'seller@example.com')->firstOrFail();
        $this->assertSame('09171234567', $user->phone);
        $this->assertSame('123456789000', $user->tin_number);
        $this->assertSame('Electronics', $user->line_of_business);
        $this->assertSame('pending', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $seller = \App\Models\Seller::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Maria Store', $seller->name);
        $this->assertSame('pending', $seller->status);
        $this->assertSame('12 Main Street', $seller->pickupAddress->line1);
        Storage::disk('public')->assertExists($user->id_photo);
        $this->assertGuest();
    }

    public function test_corporation_documents_are_saved_and_protected(): void
    {
        $this->post('/register', $this->details('corporation'))->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $user = User::where('email', 'seller@example.com')->firstOrFail();
        Storage::disk('local')->assertExists($user->sec_certificate);
        Storage::disk('local')->assertExists($user->bir_certificate);
        $url = route('registration.document', [$user->id, 'sec_certificate']);
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get($url)->assertOk();
    }

    public function test_sole_proprietorship_requires_and_saves_dti_and_bir(): void
    {
        $data = $this->details('sole_proprietorship');
        $missing = $data;
        unset($missing['dti_permit'], $missing['bir_certificate']);
        $this->postJson('/register', $missing)->assertUnprocessable()->assertJsonValidationErrors(['dti_permit', 'bir_certificate']);
        $this->assertDatabaseCount('users', 0);
        $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $user = User::where('email', 'seller@example.com')->firstOrFail();
        Storage::disk('public')->assertExists($user->dti_permit);
        Storage::disk('local')->assertExists($user->bir_certificate);
    }

    public function test_invalid_details_and_documents_do_not_create_accounts(): void
    {
        $data = $this->details('corporation');
        $data['shop_name'] = 'ab';
        $data['tin_number'] = '123456789';
        $data['postal_code'] = 'abcd';
        $data['phone'] = '';
        $data['id_photo'] = UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf');
        $data['bir_certificate'] = UploadedFile::fake()->create('bad.txt', 20, 'text/plain');
        unset($data['sec_certificate']);
        $this->postJson('/register', $data)->assertUnprocessable()->assertJsonValidationErrors(['shop_name', 'tin_number', 'postal_code', 'phone', 'id_photo', 'bir_certificate', 'sec_certificate']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_duplicate_shop_name_and_invalid_otp_are_rejected(): void
    {
        $data = $this->details();
        \App\Models\Seller::create(['user_id' => User::factory()->create()->id, 'name' => 'MARIA STORE', 'slug' => 'maria-store', 'status' => 'pending']);
        $this->postJson('/register', $data)->assertUnprocessable()->assertJsonValidationErrors('shop_name');
        $data['shop_name'] = 'Another Store';
        $data['email_verification_otp'] = '654321';
        $this->post('/register', $data)->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'seller@example.com']);
    }
}
