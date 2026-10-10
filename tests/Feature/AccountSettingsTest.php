<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function addressData(array $overrides = []): array
    {
        return array_merge(['form_key' => 'new', 'label' => 'Home', 'recipient' => 'Delivery Recipient',
            'phone' => '09171234567', 'line1' => '10 Main Street', 'barangay' => 'San Jose',
            'city' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1100'], $overrides);
    }

    public function test_settings_render_real_profile_verification_and_address_state(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user)->get('/dashboard?tab=settings')->assertOk()
            ->assertSee('Profile information')->assertSee('Not verified')->assertSee('Other devices')
            ->assertSee('No saved addresses yet.');
        $this->get('/user/account')->assertRedirect(route('buyer.dashboard', ['tab' => 'settings']));
        $this->get('/user/account/profile')->assertRedirect(route('buyer.dashboard', ['tab' => 'settings']));
        $this->get('/user/account?tab=profile')->assertRedirect(route('buyer.dashboard', ['tab' => 'settings']));
        $this->get('/dashboard?tab=profile')->assertOk()->assertViewHas('tab', 'settings')
            ->assertSee('Identity Verification')->assertSee('name="sex"', false)->assertSee('name="birthday"', false)
            ->assertSee('name="id_photo"', false)->assertDontSee('data-panel="profile"', false)
            ->assertDontSee('data-nav="profile"', false);
    }

    public function test_addresses_can_be_added_edited_and_selected_without_changing_profile_contact(): void
    {
        $user = User::factory()->create(['name' => 'Profile Owner', 'phone' => '09991234567']);
        $this->actingAs($user)->from('/dashboard?tab=settings')
            ->post(route('account.addresses.save'), $this->addressData())->assertSessionHasNoErrors();
        $first = Address::firstOrFail();
        $this->assertTrue($first->is_default);
        $this->assertSame($first->formatted_address, $user->fresh()->address);
        $this->post(route('account.addresses.save'), $this->addressData(['label' => 'Work', 'line1' => '20 Office Street']))->assertSessionHasNoErrors();
        $second = Address::orderByDesc('id')->first();
        $this->assertFalse($second->is_default);
        $this->post(route('account.addresses.default', $second->id))->assertSessionHasNoErrors();
        $this->assertFalse($first->fresh()->is_default);
        $this->post(route('account.addresses.save'), $this->addressData(['address_id' => $second->id, 'line1' => '30 Office Street']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('addresses', 2);
        $this->assertSame(1, Address::where('is_default', true)->count());
        $this->assertSame('30 Office Street', $user->fresh()->street_address);
        $this->assertSame('Profile Owner', $user->fresh()->name);
        $this->assertSame('09991234567', $user->fresh()->phone);
        $this->get('/dashboard?tab=settings')->assertOk()->assertSee('30 Office Street');
        $this->get('/user/account/addresses')->assertRedirect(route('buyer.dashboard', ['tab' => 'addresses']));
    }

    public function test_add_address_dialog_is_outside_dashboard_panels_and_reopens_after_validation(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard?tab=settings')->assertOk();
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//dialog[@id="newAddressModal"]')->length);
        $this->assertSame(0, $xpath->query('//dialog[@id="newAddressModal"]/ancestor::section[@data-panel]')->length);
        $this->assertSame('body', $xpath->query('//dialog[@id="newAddressModal"]')->item(0)->parentNode->nodeName);
        $this->assertSame(1, $xpath->query('//dialog[@id="newAddressModal"]//select[@name="region"]')->length);
        $this->assertSame(1, $xpath->query('//dialog[@id="newAddressModal"]//select[@name="province"]')->length);
        $this->assertSame(1, $xpath->query('//dialog[@id="newAddressModal"]//select[@name="city"]')->length);
        $this->assertSame(1, $xpath->query('//dialog[@id="newAddressModal"]//select[@name="barangay"]')->length);

        $this->from('/dashboard?tab=settings')->post(route('account.addresses.save'), $this->addressData(['line1' => '', 'region' => 'NCR']))
            ->assertSessionHasErrorsIn('addressBook', ['line1']);
        $this->get('/dashboard?tab=settings')->assertOk()->assertSee('data-auto-open="true"', false)
            ->assertSee('value="Delivery Recipient"', false);

        $this->post(route('account.addresses.save'), $this->addressData(['region' => 'NCR']))->assertSessionHasNoErrors();
        $this->assertSame('NCR', $user->fresh()->region);
    }

    public function test_legacy_address_is_preserved_and_can_be_edited_without_duplication(): void
    {
        $user = User::factory()->create(['street_address' => 'Original home', 'address' => 'Original home, Manila']);
        $this->actingAs($user)->get('/dashboard?tab=settings')->assertOk()->assertSee('Original home');
        $this->post(route('account.addresses.save'), $this->addressData(['legacy_address' => 1, 'line1' => 'Updated home']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('addresses', 1);
        $this->assertSame('Updated home', Address::first()->line1);
        $this->post(route('account.addresses.save'), $this->addressData(['label' => 'Work']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('addresses', 2);
        $this->assertSame('Updated home', $user->fresh()->street_address);
    }

    public function test_address_ownership_and_validation_are_enforced(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $address = Address::create(array_merge($this->addressData(), ['user_id' => $owner->id, 'is_default' => true]));
        $this->actingAs($other)->post(route('account.addresses.save'), $this->addressData(['address_id' => $address->id]))->assertNotFound();
        $this->post(route('account.addresses.default', $address->id))->assertNotFound();
        $this->from('/dashboard?tab=settings')->post(route('account.addresses.save'), $this->addressData(['line1' => '']))->assertSessionHasErrorsIn('addressBook', ['line1']);
        $this->post(route('account.addresses.save'), $this->addressData(['phone' => 'jdadjajoa']))->assertSessionHasErrorsIn('addressBook', ['phone']);
        $this->get('/dashboard?tab=settings')->assertOk()->assertSee('Please check your address.');
        $this->assertDatabaseCount('addresses', 1);
    }

    public function test_checkout_uses_the_saved_default_recipient_and_address(): void
    {
        $user = User::factory()->create(['id_status' => 'verified', 'address' => 'Old profile address']);
        $address = Address::create(array_merge($this->addressData(), ['user_id' => $user->id, 'is_default' => true]));
        $this->actingAs($user)->withSession(['cart' => [[
            'name' => 'Test product', 'image' => '/images/favicon.png', 'variation' => 'Standard', 'quantity' => 1, 'price' => 100,
        ]]])->get('/checkout')->assertOk()->assertSee('Delivery Recipient')
            ->assertSee($address->formatted_address)->assertDontSee('Old profile address')
            ->assertSee('Cash on Delivery (COD)')
            ->assertSee('GCash')
            ->assertDontSee('Card / Debit')
            ->assertDontSee('GCash / Maya');
    }

    public function test_profile_update_preserves_details_omitted_from_settings_form(): void
    {
        $user = User::factory()->create(['middle_initial' => 'A', 'sex' => 'Female', 'birthday' => '1995-01-01', 'age' => 31]);
        $this->actingAs($user)->post(route('account.profile.update'), ['name' => 'Updated Name', 'phone' => '09171234567'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'sex' => 'Female', 'middle_initial' => 'A', 'birthday' => '1995-01-01', 'age' => 31]);
    }

    public function test_password_change_rejects_wrong_password_and_never_flashes_secrets(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original-password')]);
        $this->actingAs($user)->from('/dashboard?tab=settings')->post(route('account.password.update'), [
            'current_password' => 'wrong-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password')->assertSessionMissing('_old_input.current_password');
        $this->post(route('account.password.update'), [
            'current_password' => 'original-password', 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
        $this->post(route('account.password.update'), [
            'current_password' => 'original-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->get('/dashboard?tab=settings')->assertOk();
    }

    public function test_other_device_signout_requires_password_and_invalidates_old_session_hash(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original-password')]);
        $oldHash = $user->password;
        $this->actingAs($user)->from('/dashboard?tab=settings')->post(route('account.devices.logout'), ['device_password' => 'incorrect'])
            ->assertSessionHasErrorsIn('devices', ['device_password'])->assertSessionMissing('_old_input.device_password');
        $this->post(route('account.devices.logout'), ['device_password' => 'original-password'])->assertSessionHasNoErrors();
        $this->assertNotSame($oldHash, $user->fresh()->password);
        $this->get('/dashboard?tab=settings')->assertOk();
        $this->withSession(['password_hash_web' => $oldHash])->get('/dashboard?tab=settings')->assertRedirect(route('login'));
    }

    public function test_address_can_be_deleted_and_fallback_default_is_assigned(): void
    {
        $user = User::factory()->create();
        $first = Address::create(array_merge($this->addressData(), ['user_id' => $user->id, 'is_default' => true]));
        $second = Address::create(array_merge($this->addressData(['label' => 'Office', 'line1' => '99 Office St']), ['user_id' => $user->id, 'is_default' => false]));

        $this->actingAs($user)->delete(route('account.addresses.delete', $first->id))->assertRedirect();
        $this->assertDatabaseMissing('addresses', ['id' => $first->id]);
        $this->assertTrue($second->fresh()->is_default);
    }
}
