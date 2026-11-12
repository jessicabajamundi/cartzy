<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->timestamp('online_until')->nullable();
            $table->json('designated_place')->nullable();
            $table->index(['logistics_provider_id', 'courier_type', 'status', 'is_active', 'online_until'], 'riders_dispatch_available');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex('riders_dispatch_available');
            $table->dropColumn(['online_until', 'designated_place']);
        });
    }
};
