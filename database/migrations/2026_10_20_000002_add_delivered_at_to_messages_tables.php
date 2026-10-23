<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seller_messages') && !Schema::hasColumn('seller_messages', 'delivered_at')) {
            Schema::table('seller_messages', function (Blueprint $table) {
                $table->timestamp('delivered_at')->nullable()->after('body');
            });
        }

        if (Schema::hasTable('product_inquiry_messages') && !Schema::hasColumn('product_inquiry_messages', 'delivered_at')) {
            Schema::table('product_inquiry_messages', function (Blueprint $table) {
                $table->timestamp('delivered_at')->nullable()->after('body');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seller_messages') && Schema::hasColumn('seller_messages', 'delivered_at')) {
            Schema::table('seller_messages', function (Blueprint $table) {
                $table->dropColumn('delivered_at');
            });
        }

        if (Schema::hasTable('product_inquiry_messages') && Schema::hasColumn('product_inquiry_messages', 'delivered_at')) {
            Schema::table('product_inquiry_messages', function (Blueprint $table) {
                $table->dropColumn('delivered_at');
            });
        }
    }
};
