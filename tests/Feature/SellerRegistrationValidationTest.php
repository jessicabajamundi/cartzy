<?php

namespace Tests\Feature;

use Tests\TestCase;

class SellerRegistrationValidationTest extends TestCase
{
    public function test_all_registration_roles_reject_special_characters_and_digits_in_names(): void
    {
        foreach (['buyer', 'seller', 'logistics', 'courier'] as $role) {
            foreach (['Maria@', 'Cruz123', 'Ana-Maria', "O'Neil", 'Name!', 'Name🙂'] as $name) {
                $this->postJson('/register', [
                    'role' => $role, 'first_name' => $name, 'last_name' => $name,
                ])->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name']);
            }
            $this->postJson('/register', [
                'role' => $role, 'first_name' => 'María Clara', 'last_name' => 'Peña',
            ])->assertUnprocessable()->assertJsonMissingValidationErrors(['first_name', 'last_name', 'name']);
            $this->postJson('/register', ['role' => $role, 'name' => 'Name@123'])
                ->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name']);
        }
    }

    public function test_seller_names_with_digits_and_symbols_are_rejected_individually(): void
    {
        $this->postJson('/register', [
            'role' => 'seller',
            'first_name' => 'Maria Clara99',
            'last_name' => 'Dela Cruz@#',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'first_name' => 'Only letters are allowed.',
            'last_name' => 'Only letters are allowed.',
        ]);
    }

    public function test_seller_names_allow_spaces_and_unicode_letters(): void
    {
        foreach ([['Maria Clara', 'Dela Cruz'], ['José', 'Peña'], ["Jose\u{0301}", 'Peña']] as [$first, $last]) {
            // Omit the other required fields to test validation without creating an account.
            $this->postJson('/register', [
                'role' => 'seller',
                'first_name' => $first,
                'last_name' => $last,
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['email'])
                ->assertJsonMissingValidationErrors(['first_name', 'last_name', 'name']);
        }
    }

    public function test_seller_cannot_bypass_name_validation_with_only_a_combined_name(): void
    {
        $this->postJson('/register', [
            'role' => 'seller',
            'name' => 'Maria Clara Dela Cruz',
        ])->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name']);
    }
}
