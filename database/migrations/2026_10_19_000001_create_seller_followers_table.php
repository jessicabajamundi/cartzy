<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buyers following a seller's public store page.
        Schema::create('seller_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['seller_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_followers');
    }
};
