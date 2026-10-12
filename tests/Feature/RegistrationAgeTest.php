<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationAgeTest extends TestCase
{
    public function test_individual_roles_require_a_valid_adult_birthday(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 12:00:00'));

        foreach (['buyer', 'seller', 'courier'] as $role) {
            foreach ([null, '', 'invalid', '2008-02-30', '2008-10-13', '2010-01-01', '2026-10-12', '2027-01-01'] as $birthday) {
                $this->postJson('/register', ['role' => $role, 'birthday' => $birthday])
                    ->assertUnprocessable()->assertJsonValidationErrors(['birthday']);
            }

            foreach (['2008-10-12', '2008-10-11', '1990-01-01'] as $birthday) {
                // Other required fields are omitted to isolate age validation without creating accounts.
                $this->postJson('/register', ['role' => $role, 'birthday' => $birthday])
                    ->assertUnprocessable()->assertJsonValidationErrors(['email'])
                    ->assertJsonMissingValidationErrors(['birthday', 'role']);
            }
        }
    }

    public function test_leap_day_birthdays_reach_eighteen_on_march_first_in_non_leap_years(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-02-28 12:00:00'));
        $this->postJson('/register', ['role' => 'buyer', 'birthday' => '2008-02-29'])
            ->assertUnprocessable()->assertJsonValidationErrors(['birthday']);

        $this->travelTo(\Carbon\Carbon::parse('2026-03-01 12:00:00'));
        $this->postJson('/register', ['role' => 'buyer', 'birthday' => '2008-02-29'])
            ->assertUnprocessable()->assertJsonMissingValidationErrors(['birthday']);
    }

    public function test_registration_form_limits_birthdays_for_individual_roles(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 12:00:00'));
        foreach (['buyer', 'seller', 'courier'] as $role) {
            $this->get('/register?role=' . $role)->assertOk()
                ->assertSee('max="2008-10-12"', false)
                ->assertSee('You must be at least 18 years old to register.')
                ->assertSee('id="birthdayFieldWrapper" style="margin-bottom: 26px;"', false);
        }
    }
}
