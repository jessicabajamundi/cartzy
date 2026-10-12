<?php

namespace Database\Seeders;

use App\Models\DeliveryEvent;
use App\Models\LogisticsMessage;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PickupRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LogisticsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure hub user has logistics role and owns LogisticsProvider
        $hubUser = User::where('role', User::ROLE_LOGISTICS)->first()
            ?? User::where('email', 'logistics@cartzy.ph')->first();

        if (!$hubUser) {
            $hubUser = User::create([
                'name'          => 'Metro South Sorting & Fulfillment Hub',
                'email'         => 'logistics@cartzy.ph',
                'password'      => Hash::make('logistics123'),
                'role'          => User::ROLE_LOGISTICS,
                'status'        => 'active',
                'business_name' => 'Metro South Sorting & Fulfillment Hub',
                'phone'         => '09173334444',
                'province'      => 'Laguna',
                'city'          => 'Santa Cruz',
                'barangay'      => 'Poblacion',
                'street_address'=> 'KM 54 National Highway, Sort Center Complex',
            ]);
        }

        $logisticsRole = Role::firstOrCreate(['name' => 'logistics']);
        $hubUser->roles()->syncWithoutDetaching([$logisticsRole->id]);

        // Connect or update provider
        $provider = LogisticsProvider::first();
        if ($provider) {
            $provider->update([
                'user_id' => $hubUser->id,
                'name'    => 'Metro South Sorting & Fulfillment Hub',
                'status'  => 'approved',
            ]);
        } else {
            $provider = LogisticsProvider::create([
                'user_id'       => $hubUser->id,
                'name'          => 'Metro South Sorting & Fulfillment Hub',
                'slug'          => 'metro-south-hub',
                'status'        => 'approved',
                'contact_phone' => '09173334444',
            ]);
        }

        // 2. Ensure Rider role
        $riderRole = Role::firstOrCreate(['name' => 'rider']);

        // 3. Seed sample riders into users & riders tables
        $sampleRiders = [
            [
                'name'             => 'Arnel Gomez',
                'email'            => 'arnel.rider@cartzy.ph',
                'phone'            => '09285559876',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'NQ-4812',
                'status'           => 'approved',
                'is_active'        => true,
                'area'             => 'Laguna - Santa Cruz',
                'rating'           => 4.95,
                'deliveries_today' => 12,
            ],
            [
                'name'             => 'Carlo Santos',
                'email'            => 'carlo.rider@cartzy.ph',
                'phone'            => '09178884455',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'MN-3421',
                'status'           => 'approved',
                'is_active'        => true,
                'area'             => 'Laguna - Calamba',
                'rating'           => 4.82,
                'deliveries_today' => 9,
            ],
            [
                'name'             => 'Rex Villanueva',
                'email'            => 'rex.rider@cartzy.ph',
                'phone'            => '09271112233',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'LK-7829',
                'status'           => 'approved',
                'is_active'        => false,
                'area'             => 'Batangas - Lipa',
                'rating'           => 4.60,
                'deliveries_today' => 0,
            ],
            [
                'name'             => 'Jose Mabuhay',
                'email'            => 'jose.rider@cartzy.ph',
                'phone'            => '09394445566',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'QR-1234',
                'status'           => 'pending',
                'is_active'        => false,
                'area'             => 'Quezon - Lucena',
                'rating'           => 5.00,
                'deliveries_today' => 0,
            ],
            [
                'name'             => 'Bong Aquino',
                'email'            => 'bong.rider@cartzy.ph',
                'phone'            => '09451234567',
                'vehicle_type'     => 'Motorcycle',
                'plate_no'         => 'PH-5678',
                'status'           => 'pending',
                'is_active'        => false,
                'area'             => 'Laguna - San Pablo',
                'rating'           => 5.00,
                'deliveries_today' => 0,
            ],
            [
                'name'             => 'Noel Torres',
                'email'            => 'noel.rider@cartzy.ph',
                'phone'            => '09119876543',
                'vehicle_type'     => 'Bicycle',
                'plate_no'         => 'N/A',
                'status'           => 'rejected',
                'rejection_reason' => 'Incomplete documents. NBI Clearance expired.',
                'is_active'        => false,
                'area'             => null,
                'rating'           => 5.00,
                'deliveries_today' => 0,
            ],
        ];

        foreach ($sampleRiders as $rData) {
            $u = User::firstOrCreate(
                ['email' => $rData['email']],
                [
                    'name'     => $rData['name'],
                    'phone'    => $rData['phone'],
                    'password' => Hash::make('rider123'),
                    'role'     => 'courier',
                    'status'   => 'active',
                ]
            );
            $u->roles()->syncWithoutDetaching([$riderRole->id]);

            Rider::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'logistics_provider_id' => $provider->id,
                    'vehicle_type'          => $rData['vehicle_type'],
                    'plate_no'              => $rData['plate_no'],
                    'status'                => $rData['status'],
                    'rejection_reason'      => $rData['rejection_reason'] ?? null,
                    'area'                  => $rData['area'],
                    'rating'                => $rData['rating'],
                    'deliveries_today'      => $rData['deliveries_today'],
                    'is_active'             => $rData['is_active'],
                ]
            );
        }

        // 4. Seed Pickup Requests
        $seller1 = Seller::find(1) ?? Seller::first();
        $seller2 = Seller::find(2) ?? $seller1;
        $seller3 = Seller::find(3) ?? $seller1;

        if ($seller1 && PickupRequest::count() === 0) {
            PickupRequest::create([
                'logistics_provider_id' => $provider->id,
                'seller_id'             => $seller1->id,
                'parcel_count'          => 8,
                'weight_kg'             => 12.4,
                'pickup_date'           => now()->format('Y-m-d'),
                'pickup_time'           => '2:00 PM',
                'address'               => '123 Rizal St, Brgy. Poblacion, Santa Cruz, Laguna',
                'status'                => 'pending',
                'notes'                 => 'Fragile electronics. Handle with care.',
            ]);

            if ($seller2) {
                PickupRequest::create([
                    'logistics_provider_id' => $provider->id,
                    'seller_id'             => $seller2->id,
                    'parcel_count'          => 5,
                    'weight_kg'             => 3.8,
                    'pickup_date'           => now()->format('Y-m-d'),
                    'pickup_time'           => '3:30 PM',
                    'address'               => '45 Mabini Ave, Brgy. San Jose, San Pablo, Laguna',
                    'status'                => 'confirmed',
                    'confirmed_at'          => now()->subHours(2),
                    'notes'                 => null,
                ]);
            }

            if ($seller3) {
                PickupRequest::create([
                    'logistics_provider_id' => $provider->id,
                    'seller_id'             => $seller3->id,
                    'parcel_count'          => 14,
                    'weight_kg'             => 22.1,
                    'pickup_date'           => now()->addDay()->format('Y-m-d'),
                    'pickup_time'           => '10:00 AM',
                    'address'               => '88 Quezon Blvd, Brgy. Halang, Calamba, Laguna',
                    'status'                => 'pending',
                    'notes'                 => 'Boxes are large. Bring extra packaging tape.',
                ]);
            }
        }

        // 5. Seed Shipments & Seller Orders if low
        $buyer = User::where('role', 'buyer')->first() ?? $hubUser;
        $product = Product::first();
        $variant = ProductVariant::first();
        $riderArnel = Rider::whereHas('user', fn($q) => $q->where('name', 'Arnel Gomez'))->first();
        $riderCarlo = Rider::whereHas('user', fn($q) => $q->where('name', 'Carlo Santos'))->first();
        $riderRex   = Rider::whereHas('user', fn($q) => $q->where('name', 'Rex Villanueva'))->first();

        $parcelsData = [
            [
                'tracking'       => 'CTZY-20261011-0001',
                'seller_id'      => $seller1?->id ?? 1,
                'recipient'      => 'Juan Dela Cruz',
                'address'        => '45 Magsaysay St, Brgy. 1, Santa Cruz, Laguna',
                'phone'          => '09171234567',
                'items'          => 'Sony WH-1000XM5 Headphones x1',
                'weight_kg'      => 1.5,
                'method'         => 'cod',
                'cod_minor'      => 899000,
                'status'         => 'in_transit',
                'sort_status'    => 'sorted',
                'delivery_area'  => 'Laguna - Santa Cruz',
                'rider_id'       => $riderArnel?->id,
                'received_at'    => now()->subHours(5),
                'sorted_at'      => now()->subHours(4),
                'assigned_at'    => now()->subHours(3),
            ],
            [
                'tracking'       => 'CTZY-20261011-0002',
                'seller_id'      => $seller2?->id ?? 1,
                'recipient'      => 'Elena Reyes',
                'address'        => '12 Maharlika Hwy, Brgy. San Jose, San Pablo, Laguna',
                'phone'          => '09271234567',
                'items'          => 'SK-II Facial Essence 160ml x1',
                'weight_kg'      => 0.4,
                'method'         => 'wallet',
                'cod_minor'      => 0,
                'status'         => 'out_for_delivery',
                'sort_status'    => 'sorted',
                'delivery_area'  => 'Laguna - San Pablo',
                'rider_id'       => $riderCarlo?->id,
                'received_at'    => now()->subHours(6),
                'sorted_at'      => now()->subHours(5),
                'assigned_at'    => now()->subHours(4),
            ],
            [
                'tracking'       => 'CTZY-20261011-0003',
                'seller_id'      => $seller1?->id ?? 1,
                'recipient'      => 'Pedro Garcia',
                'address'        => '7 Aguinaldo Ave, Brgy. Canlubang, Calamba, Laguna',
                'phone'          => '09451234567',
                'items'          => 'Nike Air Max 270 Size 42 x1',
                'weight_kg'      => 1.2,
                'method'         => 'cod',
                'cod_minor'      => 549900,
                'status'         => 'unassigned',
                'sort_status'    => 'unsorted',
                'delivery_area'  => null,
                'rider_id'       => null,
                'received_at'    => now()->subHours(2),
                'sorted_at'      => null,
                'assigned_at'    => null,
            ],
            [
                'tracking'       => 'CTZY-20261011-0004',
                'seller_id'      => $seller3?->id ?? 1,
                'recipient'      => 'Ana Lim',
                'address'        => '33 JP Laurel Hwy, Brgy. Marawoy, Lipa, Batangas',
                'phone'          => '09181234567',
                'items'          => 'Adidas Running Shoes x1, Sports Bag x1',
                'weight_kg'      => 2.8,
                'method'         => 'wallet',
                'cod_minor'      => 0,
                'status'         => 'delivered',
                'sort_status'    => 'assigned',
                'delivery_area'  => 'Batangas - Lipa',
                'rider_id'       => $riderRex?->id,
                'received_at'    => now()->subDays(1)->subHours(8),
                'sorted_at'      => now()->subDays(1)->subHours(7),
                'assigned_at'    => now()->subDays(1)->subHours(6),
            ],
            [
                'tracking'       => 'CTZY-20261011-0005',
                'seller_id'      => $seller1?->id ?? 1,
                'recipient'      => 'Mark Torres',
                'address'        => '88 Bonifacio St, Brgy. Centro 1, Lucena, Quezon',
                'phone'          => '09921234567',
                'items'          => 'Samsung 65" OLED TV x1',
                'weight_kg'      => 28.5,
                'method'         => 'cod',
                'cod_minor'      => 8999000,
                'status'         => 'unassigned',
                'sort_status'    => 'unsorted',
                'delivery_area'  => null,
                'rider_id'       => null,
                'received_at'    => now()->subHours(1),
                'sorted_at'      => null,
                'assigned_at'    => null,
            ],
            [
                'tracking'       => 'CTZY-20261011-0006',
                'seller_id'      => $seller2?->id ?? 1,
                'recipient'      => 'Rhea Castillo',
                'address'        => '14 Lapu-Lapu St, Brgy. Palho, Calamba, Laguna',
                'phone'          => '09311234567',
                'items'          => 'Skincare Bundle Set x1',
                'weight_kg'      => 0.9,
                'method'         => 'cod',
                'cod_minor'      => 129900,
                'status'         => 'failed',
                'sort_status'    => 'sorted',
                'delivery_area'  => 'Laguna - Calamba',
                'rider_id'       => $riderCarlo?->id,
                'received_at'    => now()->subDays(1)->subHours(5),
                'sorted_at'      => now()->subDays(1)->subHours(4),
                'assigned_at'    => now()->subDays(1)->subHours(3),
            ],
        ];

        if (Shipment::count() <= 1) {
            foreach ($parcelsData as $idx => $p) {
                $order = Order::create([
                    'buyer_id'         => $buyer->id,
                    'reference'        => 'ORD-LOG-' . (1000 + $idx),
                    'total_minor'      => $p['cod_minor'] > 0 ? $p['cod_minor'] : 250000,
                    'payment_method'   => $p['method'],
                    'payment_status'   => $p['method'] === 'wallet' ? 'paid' : 'pending',
                    'shipping_address' => [
                        'recipient' => $p['recipient'],
                        'phone'     => $p['phone'],
                        'address'   => $p['address'],
                    ],
                ]);

                $sellerOrder = SellerOrder::create([
                    'order_id'              => $order->id,
                    'seller_id'             => $p['seller_id'],
                    'logistics_provider_id' => $provider->id,
                    'subtotal_minor'        => $order->total_minor,
                    'shipping_fee_minor'    => 6000,
                    'commission_minor'      => 0,
                    'status'                => match ($p['status']) {
                        'delivered' => 'delivered',
                        'failed'    => 'ready_to_ship',
                        default     => 'shipped',
                    },
                    'delivered_at'          => $p['status'] === 'delivered' ? now()->subDay() : null,
                    'note'                  => $p['items'],
                ]);

                if ($product && $variant) {
                    OrderItem::create([
                        'seller_order_id'    => $sellerOrder->id,
                        'product_id'         => $product->id,
                        'product_variant_id' => $variant->id,
                        'product_name'       => $p['items'],
                        'variant_name'       => 'Default',
                        'unit_price_minor'   => $sellerOrder->subtotal_minor,
                        'quantity'           => 1,
                    ]);
                }

                $shipment = Shipment::create([
                    'seller_order_id'       => $sellerOrder->id,
                    'logistics_provider_id' => $provider->id,
                    'rider_id'              => $p['rider_id'],
                    'tracking_code'         => $p['tracking'],
                    'status'                => $p['status'],
                    'delivery_area'         => $p['delivery_area'],
                    'sort_status'           => $p['sort_status'],
                    'weight_kg'             => $p['weight_kg'],
                    'fee_minor'             => 6000,
                    'cod_amount_minor'      => $p['cod_minor'],
                    'cod_collected'         => $p['status'] === 'delivered' && $p['cod_minor'] > 0,
                    'attempts'              => $p['status'] === 'failed' ? 1 : 0,
                    'received_at'           => $p['received_at'],
                    'sorted_at'             => $p['sorted_at'],
                    'assigned_at'           => $p['assigned_at'],
                ]);

                DeliveryEvent::create([
                    'shipment_id' => $shipment->id,
                    'status'      => $p['status'],
                    'attempt'     => 1,
                    'user_id'     => $hubUser->id,
                    'note'        => 'Status update: ' . $p['status'],
                    'occurred_at' => now(),
                ]);
            }
        }

        // 6. Seed Logistics Messages
        if (LogisticsMessage::count() === 0) {
            $contactUsers = User::whereIn('role', ['seller', 'courier', 'admin'])->get();
            foreach ($contactUsers->take(4) as $cUser) {
                LogisticsMessage::create([
                    'logistics_provider_id' => $provider->id,
                    'contact_user_id'       => $cUser->id,
                    'sender_type'           => 'contact',
                    'message'               => 'Hello Hub! When will the rider arrive for parcel collection?',
                    'created_at'            => now()->subMinutes(25),
                ]);

                LogisticsMessage::create([
                    'logistics_provider_id' => $provider->id,
                    'contact_user_id'       => $cUser->id,
                    'sender_type'           => 'hub',
                    'message'               => 'Rider has been dispatched. ETA is around 15 to 20 minutes.',
                    'created_at'            => now()->subMinutes(10),
                ]);
            }
        }
    }
}
