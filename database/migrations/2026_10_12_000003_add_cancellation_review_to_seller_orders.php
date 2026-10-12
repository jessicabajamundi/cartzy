<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->string('cancellation_status', 20)->nullable()->index();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->timestamp('cancellation_reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropIndex(['cancellation_status']);
            $table->dropColumn(['cancellation_status', 'cancellation_reason', 'cancellation_requested_at', 'cancellation_reviewed_at']);
        });
    }
};
