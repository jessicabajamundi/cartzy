<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// How the parcel reaches the hub: a First Mile courier picks it up from the
// seller ("pickup"), or the seller brings it to the hub ("dropoff").
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('handover_method', 10)->default('pickup')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('handover_method');
        });
    }
};
