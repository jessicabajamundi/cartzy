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
        Schema::table('products', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();

            $table->index('approval_status');
            $table->index(['seller_id', 'approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['seller_id', 'approval_status']);
            $table->dropColumn(['approval_status', 'rejection_reason', 'reviewed_at', 'reviewed_by']);
        });
    }
};
