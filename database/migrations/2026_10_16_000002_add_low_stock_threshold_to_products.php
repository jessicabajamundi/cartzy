<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'low_stock_threshold')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('low_stock_threshold')->default(5)->after('is_active');
            });
            // Existing products keep the old fixed limit of 10 so their alerts don't change.
            DB::table('products')->update(['low_stock_threshold' => 10]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
