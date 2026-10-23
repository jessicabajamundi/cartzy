<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lets buyers report a problem on one store's part of an order; reports land in the admin Disputes page.
        Schema::table('admin_disputes', function (Blueprint $table) {
            $table->foreignId('seller_order_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('reporter_id')->nullable()->after('seller_order_id')->constrained('users')->nullOnDelete();
            $table->string('category', 40)->nullable()->after('reporter_id');
            $table->string('requested_resolution', 40)->nullable()->after('category');
            $table->json('items')->nullable()->after('description');
            $table->json('evidence')->nullable()->after('items');
            $table->string('contact_phone', 30)->nullable()->after('evidence');
            $table->index(['seller_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('admin_disputes', function (Blueprint $table) {
            $table->dropIndex(['seller_order_id', 'status']);
            $table->dropConstrainedForeignId('seller_order_id');
            $table->dropConstrainedForeignId('reporter_id');
            $table->dropColumn(['category', 'requested_resolution', 'items', 'evidence', 'contact_phone']);
        });
    }
};
