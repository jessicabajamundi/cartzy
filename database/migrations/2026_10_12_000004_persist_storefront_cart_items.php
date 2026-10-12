<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Storefront catalog products are materialised as variants at order placement.
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
            $table->string('cart_key')->nullable();
            $table->json('snapshot')->nullable();
            $table->unique(['cart_id', 'cart_key']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'cart_key']);
            $table->dropColumn(['cart_key', 'snapshot']);
        });
    }
};
