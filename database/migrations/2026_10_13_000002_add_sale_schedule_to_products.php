<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dateTime('discount_starts_at')->nullable();
            $table->dateTime('discount_ends_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['discount_starts_at', 'discount_ends_at']));
    }
};
