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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'street_address')) {
                $table->string('street_address', 255)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'barangay')) {
                $table->string('barangay', 100)->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['street_address', 'region', 'city', 'barangay']);
        });
    }
};
