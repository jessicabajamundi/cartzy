<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Company details collected by the 7-step logistics partner sign-up.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_providers', function (Blueprint $table) {
            $table->string('sec_registration_no', 30)->nullable()->after('contact_phone');
            $table->string('contact_position', 100)->nullable()->after('sec_registration_no');
            $table->json('service_regions')->nullable()->after('contact_position');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_providers', function (Blueprint $table) {
            $table->dropColumn(['sec_registration_no', 'contact_position', 'service_regions']);
        });
    }
};
