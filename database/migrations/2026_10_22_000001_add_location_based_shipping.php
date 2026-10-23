<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-editable fee per shipping zone (see config/shipping.php for the zones).
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->string('zone', 40)->primary();
            $table->unsignedBigInteger('fee_minor');
            $table->timestamps();
        });
        foreach (config('shipping.zones') as $zone => $settings) {
            DB::table('shipping_rates')->insert(['zone' => $zone, 'fee_minor' => $settings['fee_minor'], 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('seller_orders', function (Blueprint $table) {
            // Part of this parcel's shipping waived by the store's own free-shipping voucher (paid by the seller).
            $table->unsignedBigInteger('seller_shipping_discount_minor')->default(0)->after('shipping_discount_minor');
        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', fn (Blueprint $table) => $table->dropColumn('seller_shipping_discount_minor'));
        Schema::dropIfExists('shipping_rates');
    }
};
