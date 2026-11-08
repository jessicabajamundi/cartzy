<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rider time in / time out at a logistics hub. Riders scan the hub's rotating QR code in the Rider app.
 * The *_window columns hold the 25-second code window that was scanned, so one code can't be used twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('logistics_provider_id')->constrained()->cascadeOnDelete();
            $table->timestamp('time_in_at');
            $table->unsignedBigInteger('time_in_window');
            $table->timestamp('time_out_at')->nullable();
            $table->unsignedBigInteger('time_out_window')->nullable();
            $table->timestamps();

            $table->index(['logistics_provider_id', 'time_in_at']);
            $table->index(['rider_id', 'time_out_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_attendances');
    }
};
