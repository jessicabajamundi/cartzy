<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogisticsAccountTest extends TestCase
{
    use RefreshDatabase;

    private function account(): User
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Original Hub', 'slug' => 'original-hub', 'status' => 'approved']);
        return $user;
    }

    private function details(): array
    {
        return [
            'first_name' => 'Maria', 'last_name' => 'Santos',
            'sex' => 'Female', 'birthday' => '1995-04-18', 'phone' => '09171234567',
            'telephone' => '0281234567',
            'region' => 'IV-A', 'province' => 'Laguna', 'city' => 'Calamba',
            'barangay' => 'Bucal', 'street_address' => '12 Main Street',
            'postal_code' => '4027',
            'business_name' => 'Santos Logistics',
        ];
    }

    public function test_account_displays_all_registration_fields(): void
    {
        $response = $this->actingAs($this->account())->get(route('logistics.account'))->assertOk();
        foreach (array_keys($this->details()) as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
        $response->assertSee('Age (automatically calculated)')
            ->assertDontSee('name="middle_initial"', false)
            ->assertSee('name="id_photo"', false)->assertSee('name="dti_permit"', false)
            ->assertSee('administrator approval')->assertSee('E-mail (Login)');
    }

    public function test_logistics_profile_menu_links_to_workspace_and_account_management(): void
    {
        $this->actingAs($this->account());
        $this->view('layouts.partials.profile-menu')
            ->assertSee(route('logistics.dashboard'))
            ->assertSee('Logistics Workspace')
            ->assertSee(route('logistics.account'))
            ->assertSee('Account Management')
            ->assertDontSee('Account Settings');
    }

    public function test_old_logistics_settings_links_open_workspace_account_management(): void
    {
        $this->actingAs($this->account());
        foreach (['settings', 'profile', 'addresses'] as $tab) {
            $this->get(route('buyer.dashboard', ['tab' => $tab]))->assertRedirect(route('logistics.account'));
            $this->get(route('account.index', ['tab' => $tab]))->assertRedirect(route('logistics.account'));
        }
        foreach (['account.index', 'account.profile', 'account.addresses'] as $route) {
            $this->get(route($route))->assertRedirect(route('logistics.account'));
        }
        $this->get(route('buyer.dashboard'))->assertRedirect(route('logistics.dashboard'));
    }

    public function test_buyer_account_links_still_open_buyer_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->get(route('account.index'))->assertRedirect(route('buyer.dashboard', ['tab' => 'settings']));
        $this->view('layouts.partials.profile-menu')->assertSee('Account Settings')->assertDontSee('Logistics Workspace');
    }

    public function test_updates_registration_details_and_documents_and_syncs_owned_provider(): void
    {
        Storage::fake('public');
        $this->travelTo(now()->setDate(2026, 10, 14));
        $user = $this->account();
        $email = $user->email;
        $user->update(['middle_initial' => 'C']);
        $this->actingAs($user)->post(route('logistics.account.update'), $this->details() + [
            'age' => 99, 'status' => 'pending', 'email' => 'changed@example.com',
            'id_photo' => UploadedFile::fake()->create('id.pdf', 10, 'application/pdf'),
            'dti_permit' => UploadedFile::fake()->create('permit.pdf', 10, 'application/pdf'),
            'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $user->refresh();
        $this->assertSame('Maria Santos', $user->name);
        $this->assertSame(31, (int) $user->age);
        $this->assertSame('Female', $user->sex);
        $this->assertSame('C', $user->middle_initial);
        $this->assertSame('12 Main Street, Bucal, Calamba, Laguna', $user->address);
        $this->assertSame('4027', $user->postal_code);
        $this->assertSame($email, $user->email);
        $this->assertSame('active', $user->status);
        $this->assertSame('pending', $user->id_status);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
        Storage::disk('public')->assertExists([$user->id_photo, $user->dti_permit]);
        $this->assertSame('0281234567', $user->telephone);
        $this->assertDatabaseHas('logistics_providers', ['user_id' => $user->id, 'name' => 'Santos Logistics', 'status' => 'approved', 'contact_phone' => '09171234567']);
    }

    public function test_empty_uploads_preserve_documents_and_password(): void
    {
        $user = $this->account();
        $user->update(['id_photo' => 'id_photos/existing.pdf', 'dti_permit' => 'dti_permits/existing.pdf']);
        $password = $user->password;
        $this->actingAs($user)->post(route('logistics.account.update'), $this->details())
            ->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('id_photos/existing.pdf', $user->id_photo);
        $this->assertSame('dti_permits/existing.pdf', $user->dti_permit);
        $this->assertSame($password, $user->password);
    }

    public function test_invalid_details_are_rejected_without_saving(): void
    {
        Storage::fake('public');
        $user = $this->account();
        $name = $user->name;
        $this->actingAs($user)->post(route('logistics.account.update'), array_merge($this->details(), [
            'first_name' => '', 'sex' => 'invalid', 'birthday' => now()->addDay()->toDateString(),
            'phone' => 'abc', 'province' => '', 'password' => 'short',
            'id_photo' => UploadedFile::fake()->create('script.txt', 10, 'text/plain'),
            'dti_permit' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'),
        ]))->assertSessionHasErrors(['first_name', 'sex', 'birthday', 'phone', 'province', 'password', 'id_photo', 'dti_permit']);
        $this->assertSame($name, $user->fresh()->name);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_account_without_provider_does_not_modify_another_company(): void
    {
        $owner = $this->account();
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->actingAs($user)->post(route('logistics.account.update'), $this->details())->assertSessionHasNoErrors();
        $this->assertSame('Original Hub', $owner->logisticsProvider->fresh()->name);
    }

    public function test_postal_code_requires_exactly_four_digits_before_saving(): void
    {
        $user = $this->account();
        $user->update(['postal_code' => '4027']);
        $originalName = $user->name;
        $this->actingAs($user);

        foreach (['', '1', '123', '12345', '12a4', '12.4', '-123', '１２３４'] as $invalid) {
            $this->post(route('logistics.account.update'), array_replace($this->details(), [
                'postal_code' => $invalid,
            ]))->assertSessionHasErrors('postal_code');
            $this->assertSame('4027', $user->fresh()->postal_code);
            $this->assertSame($originalName, $user->fresh()->name);
        }

        $details = $this->details();
        unset($details['postal_code']);
        $this->post(route('logistics.account.update'), $details)->assertSessionHasErrors('postal_code');
    }

    public function test_postal_code_preserves_leading_zero_and_displays_saved_value(): void
    {
        $user = $this->account();
        $this->actingAs($user)->post(route('logistics.account.update'), array_replace($this->details(), [
            'postal_code' => '0123',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('0123', $user->fresh()->postal_code);
        $this->get(route('logistics.account'))->assertOk()->assertSee('value="0123"', false);
    }

    public function test_contact_numbers_reject_letters_and_incorrect_lengths(): void
    {
        $user = $this->account();
        $this->actingAs($user);
        foreach ([
            'phone' => ['', '0917123456', '091712345678', '0917123456a', '08171234567', '+63917123456', '+6391712345678', '+63917123456a', '639171234567', '++639171234567'],
            'telephone' => ['', '028123456', '02812345678', '028123456a', '0981234567'],
        ] as $field => $values) {
            foreach ($values as $value) {
                $this->post(route('logistics.account.update'), array_replace($this->details(), [$field => $value]))
                    ->assertSessionHasErrors($field);
            }
        }
        $this->assertNull($user->fresh()->telephone);
    }

    public function test_contact_numbers_cannot_belong_to_another_account(): void
    {
        $user = $this->account();
        $other = User::factory()->create(['phone' => '+639171234567', 'telephone' => '0281234567']);
        $this->actingAs($user)->post(route('logistics.account.update'), $this->details())
            ->assertSessionHasErrors(['phone', 'telephone']);
        $this->assertNull($user->fresh()->telephone);
        $other->update(['phone' => '09171234567']);
        $this->post(route('logistics.account.update'), $this->details())->assertSessionHasErrors('phone');
    }

    public function test_account_can_keep_its_own_contact_numbers(): void
    {
        $user = $this->account();
        $user->update(['phone' => '+639171234567', 'telephone' => '0491234567']);
        $this->actingAs($user)->post(route('logistics.account.update'), array_replace($this->details(), [
            'telephone' => '0491234567',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('09171234567', $user->fresh()->phone);
        $this->assertSame('0491234567', $user->fresh()->telephone);
        $this->get(route('logistics.account'))->assertOk()->assertSee('value="0491234567"', false);
    }

    public function test_international_mobile_format_saves_and_cannot_bypass_uniqueness(): void
    {
        $user = $this->account();
        $details = array_replace($this->details(), ['phone' => '+639171234567']);
        $this->actingAs($user)->post(route('logistics.account.update'), $details)->assertSessionHasNoErrors();
        $this->assertSame('09171234567', $user->fresh()->phone);
        $this->assertSame('09171234567', $user->logisticsProvider->fresh()->contact_phone);
        $this->post(route('logistics.account.update'), $details)->assertSessionHasNoErrors();

        $other = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->actingAs($other)->post(route('logistics.account.update'), array_replace($details, [
            'telephone' => '0287654321',
        ]))->assertSessionHasErrors('phone');
    }

    public function test_tin_saves_leading_zero_and_displays_saved_value(): void
    {
        $user = $this->account();
        $this->actingAs($user)->post(route('logistics.account.update'), $this->details() + [
            'tin_number' => '012345678',
        ])->assertSessionHasNoErrors();
        $this->assertSame('012345678', $user->fresh()->tin_number);
        $this->get(route('logistics.account'))->assertOk()->assertSee('value="012345678"', false);
        $this->post(route('logistics.account.update'), $this->details() + ['tin_number' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->tin_number);
    }

    public function test_invalid_tin_is_rejected_without_overwriting_saved_value(): void
    {
        $user = $this->account();
        $user->update(['tin_number' => '012345678']);
        $this->actingAs($user);
        foreach (['12345678', '1234567890', '12345678a', '123-456-789'] as $tin) {
            $this->post(route('logistics.account.update'), $this->details() + ['tin_number' => $tin])
                ->assertSessionHasErrors('tin_number');
            $this->assertSame('012345678', $user->fresh()->tin_number);
        }
    }

    public function test_other_roles_cannot_update_logistics_account(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->post(route('logistics.account.update'), $this->details())->assertForbidden();
    }
}
