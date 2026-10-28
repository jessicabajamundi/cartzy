<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_requests', 'rider_id')) {
                $table->foreignId('rider_id')->nullable()->after('seller_order_id')->constrained('riders')->nullOnDelete();
            }
            if (!Schema::hasColumn('pickup_requests', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('confirmed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'rider_id')) {
                $table->dropConstrainedForeignId('rider_id');
            }
            if (Schema::hasColumn('pickup_requests', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};
