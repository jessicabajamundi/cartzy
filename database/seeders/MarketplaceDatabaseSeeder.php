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
                    'commission_bps'    => 800, // 8.00%
                    'pickup_address_id' => $sellerPickupAddress?->id,
                ]
            );

            // 6. Sample Products & Variants & Images
            $p1 = Product::firstOrCreate(
                ['slug' => 'anc-pro-wireless-noise-cancelling-earphones'],
                [
                    'seller_id'   => $seller->id,
                    'category_id' => $audio->id,
                    'name'        => 'ANC Pro Wireless Noise Cancelling Earphones',
                    'description' => 'High-fidelity audio with active noise cancellation, 32-hour battery life, and ultra-low latency gaming mode.',
                    'is_active'   => true,
                ]
            );
            ProductVariant::firstOrCreate(
                ['sku' => 'ANC-BLK'],
                [
                    'product_id'   => $p1->id,
                    'name'         => 'Matte Black',
                    'options'      => ['color' => 'Black'],
                    'price_minor'  => 89000, // ₱890.00
                    'stock'        => 50,
                    'weight_grams' => 250,
                    'is_active'    => true,
                ]
            );
            ProductVariant::firstOrCreate(
                ['sku' => 'ANC-WHT'],
                [
                    'product_id'   => $p1->id,
                    'name'         => 'Glossy White',
                    'options'      => ['color' => 'White'],
                    'price_minor'  => 89000,
                    'stock'        => 40,
                    'weight_grams' => 250,
                    'is_active'    => true,
                ]
            );
            ProductImage::firstOrCreate(
                ['product_id' => $p1->id, 'path' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&h=600&fit=crop&q=80'],
                ['position' => 0]
            );

            $p2 = Product::firstOrCreate(
                ['slug' => 'smart-fitness-tracker-watch-blood-oxygen'],
                [
                    'seller_id'   => $seller->id,
                    'category_id' => $wearables->id,
                    'name'        => 'Smart Fitness Tracker Watch with Blood Oxygen & Heart Rate',
                    'description' => 'Track your vitals 24/7 with AMOLED display, IP68 water resistance, and 14-day standby time.',
                    'is_active'   => true,
                ]
            );
            $v2 = ProductVariant::firstOrCreate(
                ['sku' => 'FIT-SMT-01'],
                [
                    'product_id'   => $p2->id,
                    'name'         => 'Midnight Black / Standard Strap',
                    'options'      => ['color' => 'Black', 'size' => 'Standard'],
                    'price_minor'  => 129900, // ₱1,299.00
                    'stock'        => 35,
                    'weight_grams' => 180,
                    'is_active'    => true,
                ]
            );
            ProductImage::firstOrCreate(
                ['product_id' => $p2->id, 'path' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&h=600&fit=crop&q=80'],
                ['position' => 0]
            );
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
    }
}
