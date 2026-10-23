<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // All photos of an option (color/style), in order. image_path keeps the first one for carts, waybills, etc.
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', fn (Blueprint $table) => $table->dropColumn('gallery'));
    }
};
