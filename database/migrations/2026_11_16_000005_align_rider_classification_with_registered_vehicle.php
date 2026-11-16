<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Correct historical assignments without changing vehicles or delivery records.
        $vehicle = "LOWER(REPLACE(REPLACE(REPLACE(vehicle_type, '-', ''), ' ', ''), '_', ''))";
        DB::table('riders')->whereRaw("{$vehicle} IN ('motorcycle', 'tricycle', 'ebike')")
            ->update(['courier_type' => 'last_mile']);
        DB::table('riders')->whereRaw("{$vehicle} IN ('car', 'van', 'truck', 'cargo')")
            ->where(fn ($q) => $q->whereNull('courier_type')->orWhere('courier_type', '!=', 'linehaul'))
            ->update(['courier_type' => 'first_mile']);
    }

    public function down(): void
    {
        // Previous incorrect classifications cannot be reconstructed safely.
    }
};
