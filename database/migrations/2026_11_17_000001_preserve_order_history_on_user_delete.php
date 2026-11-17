<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->foreignId('buyer_id')->nullable()->change();
            $table->foreign('buyer_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->foreignId('seller_id')->nullable()->change();
            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('product_variant_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['logistics_provider_id']);
            $table->foreignId('logistics_provider_id')->nullable()->change();
            $table->foreign('logistics_provider_id')->references('id')->on('logistics_providers')->nullOnDelete();
        });

        Schema::table('return_cases', function (Blueprint $table) {
            $table->dropForeign(['logistics_provider_id']);
            $table->foreign('logistics_provider_id')->references('id')->on('logistics_providers')->nullOnDelete();
        });

        Schema::table('refund_transactions', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->foreignId('recorded_by')->nullable()->change();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('refund_transactions', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->foreignId('recorded_by')->nullable(false)->change();
            $table->foreign('recorded_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('return_cases', function (Blueprint $table) {
            $table->dropForeign(['logistics_provider_id']);
            $table->foreign('logistics_provider_id')->references('id')->on('logistics_providers')->restrictOnDelete();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['logistics_provider_id']);
            $table->foreignId('logistics_provider_id')->nullable(false)->change();
            $table->foreign('logistics_provider_id')->references('id')->on('logistics_providers')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->foreignId('product_id')->nullable(false)->change();
            $table->foreignId('product_variant_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });

        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->foreignId('seller_id')->nullable(false)->change();
            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->foreignId('buyer_id')->nullable(false)->change();
            $table->foreign('buyer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
