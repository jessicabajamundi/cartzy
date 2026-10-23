<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shop vouchers: created and paid for by one store, usable only on that store's items.
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('id')->constrained('sellers')->cascadeOnDelete();
        });

        Schema::table('seller_orders', function (Blueprint $table) {
            // Comes out of the seller's earnings; commission stays on the full subtotal (Shopee/Lazada rule).
            $table->unsignedBigInteger('seller_voucher_discount_minor')->default(0)->after('voucher_discount_minor');
        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', fn (Blueprint $table) => $table->dropColumn('seller_voucher_discount_minor'));
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });
    }
};
