<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GaaborStoreSeeder extends Seeder
{
    /**
     * Gaabor Store (Verified seller, no products)
     */
    public function run(): void
    {
        $gaaborUser = User::updateOrCreate(
            ['email' => 'gaabor@cartzy.com'],
            [
                'name'                => 'Gaabor Store',
                'password'            => Hash::make('Gaabor@2026'),
                'role'                => User::ROLE_SELLER,
                'status'              => 'active',
                'is_suspended'        => false,
                'phone'               => '09175551122',
                'street_address'      => 'Unit 12, Ortigas Avenue',
                'address'             => 'Ortigas Center, Pasig City, Metro Manila',
                'region'              => 'NCR',
                'province'            => 'Metro Manila',
                'city'                => 'Pasig City',
                'barangay'            => 'San Antonio',
                'postal_code'         => '1605',
                'business_name'       => 'Gaabor Store',
                'line_of_business'    => 'Home Appliances',
                'seller_type'         => 'Corporation',
                'tin_number'          => '321-654-987',
                'id_type'             => 'DTI Business Registration',
                'id_number'           => 'DTI-GAB-2026-5555',
                'id_status'           => 'verified',
                'id_verified_at'      => now(),
                'email_verified_at'   => now(),
                'id_rejection_reason' => null,
            ]
        );
        $gaaborUser->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => 'seller'])->id]);

        $gaaborAddress = Address::updateOrCreate(
            [
                'user_id' => $gaaborUser->id,
                'label'   => 'Gaabor Main Logistics Hub',
            ],
            [
                'recipient'   => 'Gaabor Store Warehouse',
                'phone'       => '09175551122',
                'line1'       => 'Unit 12, Ortigas Avenue',
                'barangay'    => 'San Antonio',
                'city'        => 'Pasig City',
                'province'    => 'Metro Manila',
                'postal_code' => '1605',
                'is_default'  => true,
            ]
        );

        Seller::updateOrCreate(
            ['user_id' => $gaaborUser->id],
            [
                'name'              => 'Gaabor Store',
                'slug'              => 'gaabor',
                'description'       => 'Small home and kitchen appliances for everyday living.',
                'logo_path'         => null,
                'banner_path'       => null,
                'status'            => 'approved',
                'rejection_reason'  => null,
                'commission_bps'    => 1000,
                'pickup_address_id' => $gaaborAddress->id,
            ]
        );
    }
}
