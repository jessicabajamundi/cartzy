<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 40)->nullable()->unique();
            $table->string('description', 500)->nullable();
            // fixed: amount_minor off items · percent: percent off items (capped by max_discount_minor)
            // free_shipping: amount_minor off shipping (null = shipping fully free)
            $table->enum('discount_type', ['fixed', 'percent', 'free_shipping']);
            $table->unsignedBigInteger('amount_minor')->nullable();
            $table->unsignedTinyInteger('percent')->nullable();
            $table->unsignedBigInteger('max_discount_minor')->nullable();
            $table->unsignedBigInteger('min_spend_minor')->default(0);
            // public: every buyer · new_buyers: buyers without orders · assigned: chosen buyers · code: buyers who enter the code
            $table->enum('audience', ['public', 'new_buyers', 'assigned', 'code'])->default('public');
            $table->enum('funded_by', ['platform', 'seller'])->default('platform');
            $table->unsignedInteger('total_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'audience']);
        });

        // The buyer's voucher wallet: vouchers given by an admin or added with a code.
        Schema::create('voucher_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('source', ['assigned', 'code']);
            $table->timestamps();
            $table->unique(['voucher_id', 'user_id']);
        });

        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('discount_minor');
            $table->enum('status', ['applied', 'reversed'])->default('applied');
            $table->timestamps();
            $table->index(['voucher_id', 'user_id', 'status']);
        });

        Schema::table('seller_orders', function (Blueprint $table) {
            // shipping_fee_minor stays what the buyer pays; the waived part is kept here so the courier is still paid in full.
            $table->unsignedBigInteger('shipping_discount_minor')->default(0)->after('shipping_fee_minor');
            $table->unsignedBigInteger('voucher_discount_minor')->default(0)->after('shipping_discount_minor');
        });

        // Replaces the old hard-coded "₱50 off shipping on ₱1,000" rule.
        DB::table('vouchers')->insert([
            'name' => 'Shipping Saver',
            'description' => '₱50 off shipping when your items reach ₱1,000.',
            'discount_type' => 'free_shipping',
            'amount_minor' => 5000,
            'min_spend_minor' => 100000,
            'audience' => 'public',
            'funded_by' => 'platform',
            'per_user_limit' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_discount_minor', 'voucher_discount_minor']);
        });
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('voucher_user');
        Schema::dropIfExists('vouchers');
    }
};
