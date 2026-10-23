<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryEvent;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceDatabaseSeeder extends Seeder
{
    /**
     * Seed roles, categories, shops, products, variants, logistics, and sample orders.
     */
    public function run(): void
    {
        // 1. Roles (buyer, seller, logistics, rider, admin)
        $roles = [
            'buyer'     => Role::firstOrCreate(['name' => 'buyer']),
            'seller'    => Role::firstOrCreate(['name' => 'seller']),
            'logistics' => Role::firstOrCreate(['name' => 'logistics']),
            'rider'     => Role::firstOrCreate(['name' => 'rider']),
            'admin'     => Role::firstOrCreate(['name' => 'admin']),
        ];

        // 2. Sync existing users into role_user & update is_suspended
        $users = User::all();
        foreach ($users as $user) {
            $user->is_suspended = ($user->status === 'suspended');
            $user->save();

            // Match role
            $roleKey = match ($user->role) {
                'admin'   => 'admin',
                'seller'  => 'seller',
                'courier' => 'rider',
                default   => 'buyer',
            };

            if (isset($roles[$roleKey])) {
                $user->roles()->syncWithoutDetaching([$roles[$roleKey]->id]);
            }

            if ($user->role === 'buyer') {
                \App\Models\Buyer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'           => $user->name,
                        'middle_initial' => $user->middle_initial,
                        'sex'            => $user->sex,
                        'birthday'       => $user->birthday,
                        'age'            => $user->age,
                        'phone'          => $user->phone,
                        'id_photo'       => $user->id_photo,
                        'id_type'        => $user->id_type,
                        'id_number'      => $user->id_number,
                        'id_status'      => $user->id_status ?? 'unverified',
                        'status'         => $user->status ?? 'active',
                    ]
                );
            }
        }

        // 3. Ensure sample addresses exist for users
        foreach ($users as $user) {
            if ($user->addresses()->count() === 0 && !empty($user->city)) {
                $user->addresses()->create([
                    'label'       => 'Default Address',
                    'recipient'   => $user->name,
                    'phone'       => $user->phone ?? '09170000000',
                    'line1'       => $user->street_address ?? ($user->address ?? 'Purok 1'),
                    'barangay'    => $user->barangay ?? 'Barangay 1',
                    'city'        => $user->city ?? 'Santa Cruz',
                    'province'    => $user->province ?? 'Laguna',
                    'postal_code' => $user->postal_code ?? '4009',
                    'is_default'  => true,
                ]);
            }
        }

        // 4. Sample Categories (Two-level tree)
        $electronics = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'position' => 1, 'is_active' => true]
        );
        $audio = Category::firstOrCreate(
            ['slug' => 'audio-headphones'],
            ['parent_id' => $electronics->id, 'name' => 'Headphones & Audio', 'position' => 1, 'is_active' => true]
        );
        $wearables = Category::firstOrCreate(
            ['slug' => 'smart-wearables'],
            ['parent_id' => $electronics->id, 'name' => 'Smart Watches & Wearables', 'position' => 2, 'is_active' => true]
        );

        $fashion = Category::firstOrCreate(
            ['slug' => 'fashion'],
            ['name' => 'Fashion & Apparel', 'position' => 2, 'is_active' => true]
        );
        $shoes = Category::firstOrCreate(
            ['slug' => 'sneakers-footwear'],
            ['parent_id' => $fashion->id, 'name' => 'Sneakers & Footwear', 'position' => 1, 'is_active' => true]
        );
        $accessories = Category::firstOrCreate(
            ['slug' => 'bags-accessories'],
            ['parent_id' => $fashion->id, 'name' => 'Bags & Accessories', 'position' => 2, 'is_active' => true]
        );

        // 5. Sample Seller Shop
        $sellerUser = User::where('email', 'seller@shopee.ph')->first() ?? $users->first();
        if ($sellerUser) {
            $sellerPickupAddress = $sellerUser->addresses()->first();

            $seller = Seller::firstOrCreate(
                ['user_id' => $sellerUser->id],
                [
                    'name'              => 'TechZone Official Mall',
                    'slug'              => 'techzone-official-mall',
                    'description'       => 'Your premier destination for authentic wireless earbuds, smartwatches, and premium accessories.',
                    'logo_path'         => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=200&h=200&fit=crop&q=80',
                    'banner_path'       => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=400&fit=crop&q=80',
                    'status'            => 'approved',
                    'rejection_reason'  => null,
                    'commission_bps'    => 1000, // 10.00%
                    'pickup_address_id' => $sellerPickupAddress?->id,
                ]
            );

            // 6. Sample Products (Removed as requested by user)
        }

        // 7. Sample Logistics Provider and Rider
        $courierUser = User::where('email', 'courier@shopee.ph')->first() ?? $users->skip(1)->first();
        if ($courierUser) {
            $logistics = LogisticsProvider::firstOrCreate(
                ['slug' => 'cartzy-express-logistics'],
                [
                    'user_id'          => $courierUser->id,
                    'name'             => 'Cartzy Express Logistics Hub',
                    'status'           => 'approved',
                    'rejection_reason' => null,
                    'contact_phone'    => '09173334444',
                ]
            );

            $rider = Rider::firstOrCreate(
                ['user_id' => $courierUser->id],
                [
                    'logistics_provider_id' => $logistics->id,
                    'vehicle_type'          => 'Motorcycle',
                    'plate_no'              => 'ND-4921',
                    'is_active'             => true,
                ]
            );

            // Connect courier to roles
            $courierUser->roles()->syncWithoutDetaching([$roles['logistics']->id, $roles['rider']->id]);
        }

        // 8. Sample Cart for Buyer
        $buyerUser = User::where('email', 'jessicapambago27@gmail.com')->first() ?? $users->first();
        if ($buyerUser && isset($v2)) {
            $cart = Cart::firstOrCreate(['user_id' => $buyerUser->id]);
            CartItem::firstOrCreate(
                ['cart_id' => $cart->id, 'product_variant_id' => $v2->id],
                ['quantity' => 1, 'selected' => true]
            );
        }

        // 9. Adidas Official Store (Verified seller, no products)
        $adidasUser = User::updateOrCreate(
            ['email' => 'adidas@cartzy.com'],
            [
                'name'                => 'Adidas Philippines',
                'password'            => \Illuminate\Support\Facades\Hash::make('Adidas@2026'),
                'role'                => User::ROLE_SELLER,
                'status'              => 'active',
                'is_suspended'        => false,
                'phone'               => '09178883344',
                'street_address'      => 'Bonifacio High Street Central, 9th Ave',
                'address'             => 'Bonifacio Global City, Taguig City, Metro Manila',
                'region'              => 'NCR',
                'province'            => 'Metro Manila',
                'city'                => 'Taguig City',
                'barangay'            => 'Fort Bonifacio',
                'postal_code'         => '1634',
                'business_name'       => 'Adidas Official Store',
                'line_of_business'    => 'Sports, Fitness & Outdoors',
                'seller_type'         => 'Corporation',
                'tin_number'          => '123-456-789',
                'id_type'             => 'DTI Business Registration',
                'id_number'           => 'DTI-ADI-2026-8888',
                'id_status'           => 'verified',
                'id_verified_at'      => now(),
                'email_verified_at'   => now(),
                'id_rejection_reason' => null,
            ]
        );
        $adidasUser->roles()->syncWithoutDetaching([$roles['seller']->id]);

        $adidasAddress = Address::updateOrCreate(
            [
                'user_id' => $adidasUser->id,
                'label'   => 'Adidas Main Logistics Hub',
            ],
            [
                'recipient'   => 'Adidas Official Store Warehouse',
                'phone'       => '09178883344',
                'line1'       => 'Bonifacio High Street Central, 9th Ave',
                'barangay'    => 'Fort Bonifacio',
                'city'        => 'Taguig City',
                'province'    => 'Metro Manila',
                'postal_code' => '1634',
                'is_default'  => true,
            ]
        );

        Seller::updateOrCreate(
            ['user_id' => $adidasUser->id],
            [
                'name'              => 'Adidas Official Store',
                'slug'              => 'adidas',
                'description'       => 'Official Adidas Store for authentic running shoes, sneakers, and athletic footwear.',
                'logo_path'         => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?w=200&h=200&fit=crop&q=80',
                'banner_path'       => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1200&h=400&fit=crop&q=80',
                'status'            => 'approved',
                'rejection_reason'  => null,
                'commission_bps'    => 1000,
                'pickup_address_id' => $adidasAddress->id,
            ]
        );

        // 10. Nike Official Store (Verified seller, no products)
        $nikeUser = User::updateOrCreate(
            ['email' => 'nike@cartzy.com'],
            [
                'name'                => 'Nike Philippines',
                'password'            => \Illuminate\Support\Facades\Hash::make('Nike@2026'),
                'role'                => User::ROLE_SELLER,
                'status'              => 'active',
                'is_suspended'        => false,
                'phone'               => '09179994455',
                'street_address'      => 'Bonifacio High Street Central, 7th Ave',
                'address'             => 'Bonifacio Global City, Taguig City, Metro Manila',
                'region'              => 'NCR',
                'province'            => 'Metro Manila',
                'city'                => 'Taguig City',
                'barangay'            => 'Fort Bonifacio',
                'postal_code'         => '1634',
                'business_name'       => 'Nike Official Store',
                'line_of_business'    => 'Sports, Fitness & Outdoors',
                'seller_type'         => 'Corporation',
                'tin_number'          => '987-654-321',
                'id_type'             => 'DTI Business Registration',
                'id_number'           => 'DTI-NKE-2026-9999',
                'id_status'           => 'verified',
                'id_verified_at'      => now(),
                'email_verified_at'   => now(),
                'id_rejection_reason' => null,
            ]
        );
        $nikeUser->roles()->syncWithoutDetaching([$roles['seller']->id]);

        $nikeAddress = Address::updateOrCreate(
            [
                'user_id' => $nikeUser->id,
                'label'   => 'Nike Main Logistics Hub',
            ],
            [
                'recipient'   => 'Nike Official Store Warehouse',
                'phone'       => '09179994455',
                'line1'       => 'Bonifacio High Street Central, 7th Ave',
                'barangay'    => 'Fort Bonifacio',
                'city'        => 'Taguig City',
                'province'    => 'Metro Manila',
                'postal_code' => '1634',
                'is_default'  => true,
            ]
        );

        Seller::updateOrCreate(
            ['user_id' => $nikeUser->id],
            [
                'name'              => 'Nike Official Store',
                'slug'              => 'nike',
                'description'       => 'Official Nike Store for authentic running shoes, sportswear, and athletic lifestyle gear.',
                'logo_path'         => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&h=200&fit=crop&q=80',
                'banner_path'       => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1200&h=400&fit=crop&q=80',
                'status'            => 'approved',
                'rejection_reason'  => null,
                'commission_bps'    => 1000,
                'pickup_address_id' => $nikeAddress->id,
            ]
        );

        // 11. Logitech Official Store (Fully verified seller)
        $logitechUser = User::updateOrCreate(
            ['email' => 'logitech@cartzy.com'],
            [
                'name'                => 'Logitech Philippines',
                'password'            => \Illuminate\Support\Facades\Hash::make('Logitech@2026'),
                'role'                => User::ROLE_SELLER,
                'status'              => 'active',
                'is_suspended'        => false,
                'phone'               => '09176662233',
                'street_address'      => 'Cyberzone, SM Megamall, EDSA',
                'address'             => 'Ortigas Center, Mandaluyong City, Metro Manila',
                'region'              => 'NCR',
                'province'            => 'Metro Manila',
                'city'                => 'Mandaluyong City',
                'barangay'            => 'Wack-Wack Greenhills',
                'postal_code'         => '1550',
                'business_name'       => 'Logitech Official Store',
                'line_of_business'    => 'Computers, Gaming & Electronics',
                'seller_type'         => 'Corporation',
                'tin_number'          => '456-789-101',
                'id_type'             => 'DTI Business Registration',
                'id_number'           => 'DTI-LOGI-2026-7777',
                'id_status'           => 'verified',
                'id_verified_at'      => now(),
                'email_verified_at'   => now(),
                'id_rejection_reason' => null,
            ]
        );
        $logitechUser->roles()->syncWithoutDetaching([$roles['seller']->id]);

        $logitechAddress = Address::updateOrCreate(
            [
                'user_id' => $logitechUser->id,
                'label'   => 'Logitech Main Logistics Hub',
            ],
            [
                'recipient'   => 'Logitech Official Store Warehouse',
                'phone'       => '09176662233',
                'line1'       => 'Cyberzone, SM Megamall, EDSA',
                'barangay'    => 'Wack-Wack Greenhills',
                'city'        => 'Mandaluyong City',
                'province'    => 'Metro Manila',
                'postal_code' => '1550',
                'is_default'  => true,
            ]
        );

        Seller::updateOrCreate(
            ['user_id' => $logitechUser->id],
            [
                'name'              => 'Logitech Official Store',
                'slug'              => 'logitech',
                'description'       => 'Official Logitech Store for high-performance gaming gear, mechanical keyboards, precision mice, webcams, and workspace essentials.',
                'logo_path'         => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=200&h=200&fit=crop&q=80',
                'banner_path'       => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&h=400&fit=crop&q=80',
                'status'            => 'approved',
                'rejection_reason'  => null,
                'commission_bps'    => 1000,
                'pickup_address_id' => $logitechAddress->id,
            ]
        );

        // 12. Gaabor Store (Verified seller, no products)
        $this->call(GaaborStoreSeeder::class);
    }
}

