<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->string('type');
            $table->bigInteger('amount_minor');
            $table->string('description');
            $table->timestamps();
            $table->index(['user_id', 'id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_paid_minor')->default(0);
            $table->string('checkout_key', 64)->nullable();
            $table->unique(['buyer_id', 'checkout_key']);
        });
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_paid_minor')->default(0);
            $table->unsignedBigInteger('wallet_refunded_minor')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', fn (Blueprint $table) => $table->dropColumn(['wallet_paid_minor', 'wallet_refunded_minor']));
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['buyer_id', 'checkout_key']);
            $table->dropColumn(['wallet_paid_minor', 'checkout_key']);
        });
        Schema::dropIfExists('wallet_transactions');
    }
};
