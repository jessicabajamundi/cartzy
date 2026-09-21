<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Orders (Buyer's transaction)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->unsignedBigInteger('total_minor');
            $table->enum('payment_method', ['cod', 'wallet'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'partially_refunded'])->default('pending');
            $table->json('shipping_address');
            $table->timestamps();
        });

        // 2. Seller Orders (One per shop per order — the unit of fulfilment)
        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('logistics_provider_id')->nullable()->constrained('logistics_providers')->nullOnDelete();
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('shipping_fee_minor')->default(0);
            $table->unsignedBigInteger('commission_minor')->default(0);
            $table->enum('status', [
                'pending',
                'accepted',
                'packed',
                'ready_to_ship',
                'shipped',
                'delivered',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'seller_id']);
            $table->index(['seller_id', 'status']);
        });

        // 3. Order Items (Snapshot of what was bought. Belongs to seller_order)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained('seller_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('product_name');
            $table->string('variant_name');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        // 4. Payments (What was paid, how, and its provider reference)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('method');
            $table->unsignedBigInteger('amount_minor');
            $table->string('status')->default('pending');
            $table->string('provider_ref')->nullable();
            $table->timestamps();
        });

        // 5. Shipments (The parcel for one seller order)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->unique()->constrained('seller_orders')->onDelete('cascade');
            $table->foreignId('logistics_provider_id')->constrained('logistics_providers')->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->string('tracking_code')->unique();
            $table->enum('status', [
                'unassigned',
                'assigned',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed',
                'returned'
            ])->default('unassigned');
            $table->unsignedBigInteger('fee_minor')->default(0);
            $table->unsignedBigInteger('cod_amount_minor')->default(0);
            $table->boolean('cod_collected')->default(false);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });

        // 6. Delivery Events (The parcel's timeline, written by riders)
        Schema::create('delivery_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('status');
            $table->unsignedInteger('attempt')->default(1);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique(['shipment_id', 'status', 'attempt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_events');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('seller_orders');
        Schema::dropIfExists('orders');
    }
};
