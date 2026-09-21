<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        User::updateOrCreate(
            ['email' => 'administrationa570@gmail.com'],
            [
                'name' => 'admin',
                'phone' => '09170000000',
                'password' => Hash::make('caramelmacchiato'),
                'role' => User::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        // 2. Demo Seller Account
        User::updateOrCreate(
            ['email' => 'seller@shopee.ph'],
            [
                'name' => 'Official Tech Store',
                'phone' => '09171112222',
                'password' => Hash::make('seller123'),
                'role' => User::ROLE_SELLER,
                'status' => 'active',
            ]
        );

        // 3. Demo Courier Rider Account
        User::updateOrCreate(
            ['email' => 'courier@shopee.ph'],
            [
                'name' => 'SPX Rider - Juan',
                'phone' => '09173334444',
                'password' => Hash::make('courier123'),
                'role' => User::ROLE_COURIER,
                'status' => 'active',
            ]
        );

        // 4. Demo Buyer Account
        User::updateOrCreate(
            ['email' => 'buyer@shopee.ph'],
            [
                'name' => 'Maria Dela Cruz',
                'phone' => '09175556666',
                'password' => Hash::make('buyer123'),
                'role' => User::ROLE_BUYER,
                'status' => 'active',
            ]
        );

        // 5. Seed Roles, Categories, Shops, Products & Marketplace Ecosystem
        $this->call(MarketplaceDatabaseSeeder::class);
    }
}
