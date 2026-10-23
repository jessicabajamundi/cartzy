<?php

namespace App\Console\Commands;

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
use App\Models\SellerMessage;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupRiderAccount extends Command
{
    protected $signature = 'app:setup-rider-account';
    protected $description = 'Set up a fully verified courier rider account connected to live database orders, pickups and deliveries';

    public function handle()
    {
        $this->info('Setting up verified rider account...');

        // 1. Ensure approved Logistics Provider
        $hubUser = User::where('role', User::ROLE_LOGISTICS)->first();
        if (!$hubUser) {
            $hubUser = User::firstOrCreate(
                ['email' => 'logistics@cartzy.ph'],
                [
                    'name'          => 'Metro South Logistics Hub',
                    'phone'         => '09173334444',
                    'password'      => Hash::make('logistics123'),
                    'role'          => User::ROLE_LOGISTICS,
                    'status'        => 'active',
                    'business_name' => 'Metro South Sorting & Fulfillment Hub',
                ]
            );
        }

        $provider = LogisticsProvider::first();
        if (!$provider) {
            $provider = LogisticsProvider::create([
                'user_id'       => $hubUser->id,
                'name'          => 'Metro South Sorting & Fulfillment Hub',
                'slug'          => 'metro-south-hub',
                'status'        => 'approved',
                'contact_phone' => '09173334444',
            ]);
        } else {
            $provider->update(['status' => 'approved']);
        }

        // 2. Ensure Rider User: Arnel Gomez
        $riderUser = User::firstOrCreate(
            ['email' => 'arnel.rider@cartzy.ph'],
            [
                'name'              => 'Arnel Gomez',
                'phone'             => '09285559876',
                'password'          => Hash::make('rider123'),
                'role'              => User::ROLE_COURIER,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $riderUser->update([
            'status'            => 'active',
            'email_verified_at' => now(),
            'password'          => Hash::make('rider123'),
        ]);

        $courierRole = Role::firstOrCreate(['name' => 'courier']);
        $riderUser->roles()->syncWithoutDetaching([$courierRole->id]);

        // 3. Ensure Rider profile record
        $rider = Rider::updateOrCreate(
            ['user_id' => $riderUser->id],
            [
                'logistics_provider_id' => $provider->id,
                'vehicle_type'          => 'Motorcycle',
                'plate_no'              => 'NQ-4812',
                'status'                => 'approved',
                'area'                  => 'Metro South (Laguna & NCR)',
                'rating'                => 4.95,
                'deliveries_today'      => 8,
                'is_active'             => true,
            ]
        );

        // 4. Ensure Sellers and Buyer for orders
        $seller = Seller::first();
        $buyer = User::where('role', User::ROLE_BUYER)->first() ?? $riderUser;
        $product = Product::first();
        $variant = ProductVariant::first();

        // 5. Seed Pickup Requests
        if ($seller) {
            PickupRequest::updateOrCreate(
                [
                    'logistics_provider_id' => $provider->id,
                    'seller_id'             => $seller->id,
                    'address'               => '123 Tech Hub Bldg, Brgy. San Antonio, Pasig City',
                ],
                [
                    'parcel_count' => 6,
                    'weight_kg'    => 8.5,
                    'pickup_date'  => now()->toDateString(),
                    'pickup_time'  => '2:00 PM - 4:00 PM',
                    'status'       => 'pending',
                    'notes'        => 'High-value electronics. Sealed in bubblewrap security bags.',
                ]
            );

            PickupRequest::updateOrCreate(
                [
                    'logistics_provider_id' => $provider->id,
                    'seller_id'             => $seller->id,
                    'address'               => '45 Commerce Center, Alabang, Muntinlupa City',
                ],
                [
                    'parcel_count' => 4,
                    'weight_kg'    => 5.2,
                    'pickup_date'  => now()->toDateString(),
                    'pickup_time'  => '4:30 PM - 5:30 PM',
                    'status'       => 'confirmed',
                    'confirmed_at' => now()->subHour(),
                    'notes'        => 'Contact warehouse manager Kuya Ben at 0917-888-9900.',
                ]
            );
        }

        // 6. Seed Active Deliveries (Out for delivery by Arnel)
        $sampleDeliveries = [
            [
                'tracking'    => 'CTZY-RD-884901',
                'ref'         => 'ORD-MTR-101',
                'recipient'   => 'Ma. Cristina Alcantara',
                'phone'       => '09179998877',
                'address'     => 'Unit 14B Emerald Tower, F. Ortigas Jr. Rd, Pasig City',
                'items'       => 'Sony Wireless Headphones (Midnight Blue) x1',
                'is_cod'      => true,
                'cod_minor'   => 450000,
                'status'      => 'out_for_delivery',
            ],
            [
                'tracking'    => 'CTZY-RD-884902',
                'ref'         => 'ORD-MTR-102',
                'recipient'   => 'Engr. Roberto Dalisay',
                'phone'       => '09287776655',
                'address'     => 'Block 8 Lot 12, Villa Olympia Subd, San Pedro, Laguna',
                'items'       => 'Mechanical Gaming Keyboard & RGB Mousepad x1',
                'is_cod'      => false,
                'cod_minor'   => 0,
                'status'      => 'out_for_delivery',
            ],
            [
                'tracking'    => 'CTZY-RD-884903',
                'ref'         => 'ORD-MTR-103',
                'recipient'   => 'Dra. Patricia Evangelista',
                'phone'       => '09183332211',
                'address'     => 'Clinica Medica, 2nd Flr, National Highway, Biñan, Laguna',
                'items'       => 'Ergonomic Mesh Office Chair Cushion x2',
                'is_cod'      => true,
                'cod_minor'   => 185000,
                'status'      => 'in_transit',
            ],
        ];

        foreach ($sampleDeliveries as $idx => $d) {
            $existing = Shipment::where('tracking_code', $d['tracking'])->first();
            if (!$existing) {
                $order = Order::create([
                    'buyer_id'         => $buyer->id,
                    'reference'        => $d['ref'],
                    'total_minor'      => $d['cod_minor'] > 0 ? $d['cod_minor'] : 250000,
                    'payment_method'   => $d['is_cod'] ? 'cod' : 'wallet',
                    'payment_status'   => $d['is_cod'] ? 'pending' : 'paid',
                    'shipping_address' => [
                        'recipient' => $d['recipient'],
                        'phone'     => $d['phone'],
                        'address'   => $d['address'],
                    ],
                ]);

                $sellerOrder = SellerOrder::create([
                    'order_id'              => $order->id,
                    'seller_id'             => $seller ? $seller->id : 1,
                    'logistics_provider_id' => $provider->id,
                    'subtotal_minor'        => $order->total_minor,
                    'shipping_fee_minor'    => 6500,
                    'commission_minor'      => 0,
                    'status'                => 'shipped',
                    'note'                  => $d['items'],
                ]);

                if ($product && $variant) {
                    OrderItem::create([
                        'seller_order_id'    => $sellerOrder->id,
                        'product_id'         => $product->id,
                        'product_variant_id' => $variant->id,
                        'product_name'       => $d['items'],
                        'variant_name'       => 'Standard',
                        'unit_price_minor'   => $sellerOrder->subtotal_minor,
                        'quantity'           => 1,
                    ]);
                }

                $shipment = Shipment::create([
                    'seller_order_id'       => $sellerOrder->id,
                    'logistics_provider_id' => $provider->id,
                    'rider_id'              => $rider->id,
                    'tracking_code'         => $d['tracking'],
                    'status'                => $d['status'],
                    'delivery_area'         => 'Metro South',
                    'sort_status'           => 'sorted',
                    'weight_kg'             => 1.8,
                    'fee_minor'             => 6500,
                    'cod_amount_minor'      => $d['cod_minor'],
                    'cod_collected'         => false,
                    'attempts'              => 0,
                    'received_at'           => now()->subHours(4),
                    'sorted_at'             => now()->subHours(3),
                    'assigned_at'           => now()->subHours(2),
                ]);

                DeliveryEvent::create([
                    'shipment_id' => $shipment->id,
                    'status'      => $d['status'],
                    'attempt'     => 1,
                    'user_id'     => $riderUser->id,
                    'note'        => 'Assigned to rider Arnel Gomez. Out for delivery to consignee.',
                    'occurred_at' => now()->subHours(2),
                ]);

                // Seed sample chat message for first active delivery
                if ($idx === 0) {
                    SellerMessage::create([
                        'seller_order_id' => $sellerOrder->id,
                        'sender_id'       => $buyer->id,
                        'body'            => 'Hi Kuya rider! Please leave the package with the building concierge if I am in a meeting.',
                        'created_at'      => now()->subMinutes(45),
                    ]);
                    SellerMessage::create([
                        'seller_order_id' => $sellerOrder->id,
                        'sender_id'       => $riderUser->id,
                        'body'            => 'Noted ma\'am! Will request an acknowledgment receipt from the lobby guard upon arrival.',
                        'created_at'      => now()->subMinutes(30),
                    ]);
                }
            }
        }

        // 7. Seed Completed Deliveries (History & Earnings)
        $pastDeliveries = [
            [
                'tracking'    => 'CTZY-RD-771021',
                'ref'         => 'ORD-MTR-091',
                'recipient'   => 'Jerome Villafuerte',
                'phone'       => '09171112233',
                'address'     => '18 Narra St, San Pedro, Laguna',
                'items'       => 'Smart LED Desk Lamp x1',
                'is_cod'      => true,
                'cod_minor'   => 125000,
                'delivered_at'=> now()->subHours(3),
            ],
            [
                'tracking'    => 'CTZY-RD-771022',
                'ref'         => 'ORD-MTR-092',
                'recipient'   => 'Liza Soberano Tan',
                'phone'       => '09228887766',
                'address'     => 'Phase 3, Southwoods City, Biñan, Laguna',
                'items'       => 'Bluetooth Portable Speaker x1',
                'is_cod'      => false,
                'cod_minor'   => 0,
                'delivered_at'=> now()->subHours(2),
            ],
            [
                'tracking'    => 'CTZY-RD-771023',
                'ref'         => 'ORD-MTR-093',
                'recipient'   => 'Capt. Ferdinand Cruz',
                'phone'       => '09335554433',
                'address'     => '77 Real St, Santa Rosa, Laguna',
                'items'       => 'Thermal Receipt Printer x1',
                'is_cod'      => true,
                'cod_minor'   => 285000,
                'delivered_at'=> now()->subHour(),
            ],
        ];

        foreach ($pastDeliveries as $pd) {
            $existing = Shipment::where('tracking_code', $pd['tracking'])->first();
            if (!$existing) {
                $order = Order::create([
                    'buyer_id'         => $buyer->id,
                    'reference'        => $pd['ref'],
                    'total_minor'      => $pd['cod_minor'] > 0 ? $pd['cod_minor'] : 150000,
                    'payment_method'   => $pd['is_cod'] ? 'cod' : 'wallet',
                    'payment_status'   => 'paid',
                    'shipping_address' => [
                        'recipient' => $pd['recipient'],
                        'phone'     => $pd['phone'],
                        'address'   => $pd['address'],
                    ],
                ]);

                $sellerOrder = SellerOrder::create([
                    'order_id'              => $order->id,
                    'seller_id'             => $seller ? $seller->id : 1,
                    'logistics_provider_id' => $provider->id,
                    'subtotal_minor'        => $order->total_minor,
                    'shipping_fee_minor'    => 6500,
                    'commission_minor'      => 0,
                    'status'                => 'delivered',
                    'delivered_at'          => $pd['delivered_at'],
                    'note'                  => $pd['items'],
                ]);

                $shipment = Shipment::create([
                    'seller_order_id'       => $sellerOrder->id,
                    'logistics_provider_id' => $provider->id,
                    'rider_id'              => $rider->id,
                    'tracking_code'         => $pd['tracking'],
                    'status'                => 'delivered',
                    'delivery_area'         => 'Metro South',
                    'sort_status'           => 'sorted',
                    'weight_kg'             => 1.2,
                    'fee_minor'             => 6500,
                    'cod_amount_minor'      => $pd['cod_minor'],
                    'cod_collected'         => $pd['is_cod'],
                    'attempts'              => 1,
                    'received_at'           => now()->subHours(6),
                    'sorted_at'             => now()->subHours(5),
                    'assigned_at'           => now()->subHours(4),
                    'updated_at'            => $pd['delivered_at'],
                ]);

                DeliveryEvent::create([
                    'shipment_id' => $shipment->id,
                    'status'      => 'delivered',
                    'attempt'     => 1,
                    'user_id'     => $riderUser->id,
                    'note'        => 'Delivered successfully to ' . $pd['recipient'] . '. Barcode verified by rider.',
                    'occurred_at' => $pd['delivered_at'],
                ]);
            }
        }

        // 8. Seed Logistics Hub Dispatch Message
        LogisticsMessage::create([
            'logistics_provider_id' => $provider->id,
            'contact_user_id'       => $riderUser->id,
            'sender_type'           => 'hub',
            'message'               => 'Good day Arnel! 2 priority merchant pickups are waiting in your Pasig and Alabang sector. Drive safely!',
            'created_at'            => now()->subHours(1),
        ]);

        $this->info('Rider account successfully setup: arnel.rider@cartzy.ph / rider123');
        return 0;
    }
}
