<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two kinds of couriers:
 * - first_mile: collects parcels from the seller's warehouse and brings them to the hub.
 * - last_mile: delivers parcels from the hub to the buyer.
 *
 * A shipment now keeps both riders: pickup_rider_id (first mile) and rider_id (last mile, as before).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('courier_type', 20)->default('last_mile')->after('logistics_provider_id')->index();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('pickup_rider_id')->nullable()->after('rider_id')->constrained('riders')->nullOnDelete();
        });

        // Riders who are handling seller pickups today become first-mile couriers; everyone else stays last mile.
        $pickupRiders = DB::table('pickup_requests')->whereNotNull('rider_id')->distinct()->pluck('rider_id');
        $deliveryRiders = DB::table('shipments')->whereNotNull('rider_id')->whereNotNull('received_at')->distinct()->pluck('rider_id');
        $firstMile = $pickupRiders->diff($deliveryRiders);
        if ($firstMile->isNotEmpty()) {
            DB::table('riders')->whereIn('id', $firstMile)->update(['courier_type' => 'first_mile']);
        }

        // Parcels that haven't reached the hub yet: the rider on them is the pickup rider, not the delivery rider.
        DB::table('shipments')
            ->whereNull('received_at')
            ->whereNotNull('rider_id')
            ->whereIn('status', ['unassigned', 'assigned', 'picked_up'])
            ->update(['pickup_rider_id' => DB::raw('rider_id'), 'rider_id' => null]);
    }

    public function down(): void
    {
        DB::table('shipments')->whereNull('rider_id')->whereNotNull('pickup_rider_id')
            ->update(['rider_id' => DB::raw('pickup_rider_id')]);

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_rider_id');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['courier_type']);
            $table->dropColumn('courier_type');
        });
    }
};
