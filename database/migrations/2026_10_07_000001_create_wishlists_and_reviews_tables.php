<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buyer wishlist. `ref` is the storefront catalog id (same id passed to addToCart),
        // with a snapshot of the product so it still renders if the catalog changes.
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ref');
            $table->string('name');
            $table->unsignedBigInteger('price_minor');
            $table->string('image', 500)->nullable();
            $table->string('variation')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ref']);
        });

        // Product reviews written by buyers for items they received.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('wishlists');
    }
};
