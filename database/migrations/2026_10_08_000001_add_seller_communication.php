<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('seller_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
        });
        Schema::create('seller_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['seller_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_messages');
        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn(['seller_reply', 'replied_at']));
    }
};
