<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Platform takes a flat 10% of merchandise on every seller order.
        Schema::table('sellers', function (Blueprint $table) {
            $table->unsignedInteger('commission_bps')->default(1000)->change();
        });

        DB::table('sellers')->update(['commission_bps' => 1000]);
        DB::table('seller_orders')->update(['commission_minor' => DB::raw('ROUND(subtotal_minor * 1000 / 10000)')]);
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->unsignedInteger('commission_bps')->default(800)->change();
        });
    }
};
