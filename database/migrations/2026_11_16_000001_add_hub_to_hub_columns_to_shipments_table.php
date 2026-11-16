<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('origin_hub_id')->nullable()->after('logistics_provider_id')
                ->constrained('logistics_providers')->nullOnDelete();
            $table->foreignId('destination_hub_id')->nullable()->after('origin_hub_id')
                ->constrained('logistics_providers')->nullOnDelete();
            $table->foreignId('current_hub_id')->nullable()->after('destination_hub_id')
                ->constrained('logistics_providers')->nullOnDelete();
            $table->foreignId('linehaul_courier_id')->nullable()->after('pickup_rider_id')
                ->constrained('riders')->nullOnDelete();
            $table->timestamp('dispatched_to_hub_at')->nullable()->after('assigned_at');
            $table->timestamp('arrived_at_destination_hub_at')->nullable()->after('dispatched_to_hub_at');
        });

        DB::table('shipments')
            ->whereNotNull('logistics_provider_id')
            ->update([
                'origin_hub_id' => DB::raw('logistics_provider_id'),
                'destination_hub_id' => DB::raw('logistics_provider_id'),
                'current_hub_id' => DB::raw('logistics_provider_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origin_hub_id');
            $table->dropConstrainedForeignId('destination_hub_id');
            $table->dropConstrainedForeignId('current_hub_id');
            $table->dropConstrainedForeignId('linehaul_courier_id');
            $table->dropColumn(['dispatched_to_hub_at', 'arrived_at_destination_hub_at']);
        });
    }
};
