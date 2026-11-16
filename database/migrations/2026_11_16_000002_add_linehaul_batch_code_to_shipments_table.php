<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('linehaul_batch_code', 32)->nullable()->after('linehaul_courier_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['linehaul_batch_code']);
            $table->dropColumn('linehaul_batch_code');
        });
    }
};
