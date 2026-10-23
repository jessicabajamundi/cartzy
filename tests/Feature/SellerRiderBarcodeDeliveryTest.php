<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRiderBarcodeDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function setupOrder(): array
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active', 'name' => 'Tech Master Shop']);
        $shop = Seller::create([
            'user_id' => $sellerUser->id,
            'name'    => 'Tech Master Store',
            'slug'    => 'tech-master-store',
            'status'  => 'approved',
        ]);

        $logisticsUser = User::factory()->create(['role' => 'logistics', 'status' => 'active', 'name' => 'Cartzy Express Hub']);
        $provider = LogisticsProvider::create([
            'user_id' => $logisticsUser->id,
            'name'    => 'Cartzy Express',
            'slug'    => 'cartzy-express',
            'status'  => 'approved',
        ]);

        $courierUser = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => 'Kuya Juan Rider']);
        $rider = Rider::create([
            'user_id'               => $courierUser->id,
            'logistics_provider_id' => $provider->id,
            'vehicle_type'          => 'Motorcycle',
            'plate_no'              => 'NCR-8899',
            'status'                => 'approved',
            'area'                  => 'Metro Manila',
            'rating'                => 4.98,
            'is_active'             => true,
        ]);

        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'name' => 'Maria Consignee', 'phone' => '09181234567']);

        $category = Category::firstOrCreate(['slug' => 'tech'], ['name' => 'Tech', 'is_active' => true]);
        $product = $shop->products()->create(['name' => 'Wireless Gaming Mouse', 'slug' => 'mouse-'.$sellerUser->id, 'category_id' => $category->id, 'is_active' => true]);
        $variant = $product->variants()->create(['sku' => 'SKU-01', 'name' => 'Black RGB', 'price_minor' => 75000, 'stock' => 10, 'is_active' => true]);

        $order = Order::create([
            'buyer_id'         => $buyer->id,
            'reference'        => 'CTZ-ORD-889911',
            'total_minor'      => 81000,
            'payment_method'   => 'cod',
            'payment_status'   => 'pending',
            'shipping_address' => [
                'recipient' => 'Maria Consignee',
                'phone'     => '09181234567',
                'address'   => 'Unit 102 Sunshine Condo, Makati City',
            ],
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id'              => $order->id,
            'seller_id'             => $shop->id,
            'logistics_provider_id' => $provider->id,
            'subtotal_minor'        => 75000,
            'shipping_fee_minor'    => 6000,
            'commission_minor'      => 6000,
            'status'                => 'ready_to_ship',
        ]);

        $sellerOrder->items()->create([
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'product_name'       => 'Wireless Gaming Mouse',
            'variant_name'       => 'Black RGB',
            'unit_price_minor'   => 75000,
            'quantity'           => 1,
        ]);

        $shipment = Shipment::create([
            'seller_order_id'       => $sellerOrder->id,
            'logistics_provider_id' => $provider->id,
            'rider_id'              => $rider->id,
            'tracking_code'         => 'CTZ-TEST889911',
            'status'                => 'out_for_delivery',
            'fee_minor'             => 6000,
            'cod_amount_minor'      => 81000,
        ]);

        $shipment->deliveryEvents()->create([
            'status'      => 'packed',
            'attempt'     => 1,
            'user_id'     => $sellerUser->id,
            'note'        => 'The seller packed the parcel and marked it ready for pickup.',
            'occurred_at' => now()->subHours(2),
        ]);

        return compact('sellerUser', 'shop', 'courierUser', 'rider', 'buyer', 'order', 'sellerOrder', 'provider', 'shipment');
    }

    public function test_barcode_service_generates_valid_code128_svg(): void
    {
        $code = 'CTZ-TEST123456';
        $svg = BarcodeService::generateBarcodeSvg($code, 300, 70);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString($code, $svg);
        $this->assertStringContainsString('fill="#111827"', $svg);
    }

    public function test_seller_can_view_order_details_and_scannable_waybill(): void
    {
        $data = $this->setupOrder();
        $sellerUser = $data['sellerUser'];
        $sellerOrder = $data['sellerOrder'];

        $this->actingAs($sellerUser);

        $response = $this->get(route('seller.orders.waybill', $sellerOrder->id));
        $response->assertOk();
        $response->assertSee('Order Details', false);
        $response->assertSee('Print Airway Bill (Waybill)');
        $response->assertSee('TRACKING NO:');
        $response->assertSee('CARTZY');
        $response->assertSee('Wireless Gaming Mouse');
        $response->assertSee('CASH ON DELIVERY');
    }

    public function test_courier_rider_can_scan_barcode_and_confirm_delivery(): void
    {
        $data = $this->setupOrder();
        $courierUser = $data['courierUser'];
        $sellerOrder = $data['sellerOrder'];
        $sellerUser = $data['sellerUser'];

        $shipment = $sellerOrder->shipment;
        $trackingCode = $shipment->tracking_code;

        $this->actingAs($courierUser);

        // 1. Courier looks up barcode
        $scanResponse = $this->postJson(route('courier.scan'), [
            'tracking_code' => $trackingCode,
        ]);

        $scanResponse->assertOk()
            ->assertJson([
                'success' => true,
                'shipment' => [
                    'id'            => $shipment->id,
                    'tracking_code' => $trackingCode,
                    'is_cod'        => true,
                ],
            ]);

        // 2. Courier confirms delivery via scan tool
        $deliverResponse = $this->post(route('courier.deliver', $shipment->id), [
            'recipient_name' => 'Maria Consignee',
            'note'           => 'Handed directly to recipient at lobby.',
        ]);

        $deliverResponse->assertRedirect();

        // 3. Verify Database state
        $shipment->refresh();
        $sellerOrder->refresh();

        $this->assertSame('delivered', $shipment->status);
        $this->assertSame('delivered', $sellerOrder->status);
        $this->assertNotNull($sellerOrder->delivered_at);
        $this->assertTrue((bool) $shipment->cod_collected);

        // Verify Delivery Event was recorded
        $event = $shipment->deliveryEvents()->where('status', 'delivered')->first();
        $this->assertNotNull($event);
        $this->assertSame($courierUser->id, $event->user_id);
        $this->assertStringContainsString('Kuya Juan Rider', $event->note);
        $this->assertStringContainsString('Maria Consignee', $event->note);

        // 4. Verify Seller sees Rider Verification in Order Details
        $this->actingAs($sellerUser);
        $sellerView = $this->get(route('seller.orders.waybill', $sellerOrder->id));
        $sellerView->assertOk();
        $sellerView->assertSee('DELIVERY VERIFIED · RIDER SCANNED');
        $sellerView->assertSee('Kuya Juan Rider');
        $sellerView->assertSee('Product Successfully Delivered to Buyer');
    }
}
