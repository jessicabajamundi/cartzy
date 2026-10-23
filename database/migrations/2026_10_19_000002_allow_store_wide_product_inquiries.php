<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A null product_id is a general chat with the store (started from the store page).
     */
    public function up(): void
    {
        Schema::table('product_inquiries', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->index(['buyer_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('product_inquiries')->whereNull('product_id')->delete();
        Schema::table('product_inquiries', function (Blueprint $table) {
            $table->dropIndex(['buyer_id', 'seller_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
