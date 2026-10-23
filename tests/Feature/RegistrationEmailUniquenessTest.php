<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationEmailUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_cannot_be_reused_across_any_registration_roles(): void
    {
        Mail::fake();
        $roles = ['buyer', 'seller', 'courier', 'logistics'];

        foreach ($roles as $existingRole) {
            $email = $existingRole.'@example.com';
            User::factory()->create(['email' => ucfirst($existingRole).'@Example.com', 'role' => $existingRole]);

            foreach ($roles as $newRole) {
                foreach ([$email, '  '.strtoupper($email).'  '] as $submittedEmail) {
                    $data = [
                        'role' => $newRole, 'first_name' => 'Maria', 'last_name' => 'Santos',
                        'birthday' => '1995-06-15', 'email' => $submittedEmail,
                        'password' => 'Account@2026', 'password_confirmation' => 'Account@2026',
                        'email_verification_otp' => '123456',
                    ];
                    $this->postJson('/register/request-otp', $data)->assertUnprocessable()
                        ->assertJsonValidationErrors(['email' => 'use a different email address']);
                    $this->assertNull(Cache::get('otp_'.$email));

                    // A previously issued valid code must not bypass uniqueness on submission.
                    Cache::put('otp_'.$email, ['otp' => '123456']);
                    $this->postJson('/register', $data)->assertUnprocessable()
                        ->assertJsonValidationErrors(['email' => 'use a different email address']);
                    Cache::forget('otp_'.$email);
                }
            }
        }

        $this->assertDatabaseCount('users', 4);
        Mail::assertNothingSent();
    }

    public function test_unused_email_can_request_otp_for_every_role(): void
    {
        Mail::fake();

        foreach (['buyer', 'seller', 'courier', 'logistics'] as $role) {
            $this->postJson('/register/request-otp', [
                'role' => $role, 'email' => '  '.strtoupper($role).'@EXAMPLE.COM  ',
            ])->assertOk()->assertJsonPath('email', $role.'@example.com');
            $this->assertNotNull(Cache::get('otp_'.$role.'@example.com'));
        }
    }
}
