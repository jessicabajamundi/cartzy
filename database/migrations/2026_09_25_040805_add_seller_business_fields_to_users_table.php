<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add business_name and line_of_business to users table for seller registrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name', 255)->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'line_of_business')) {
                $table->string('line_of_business', 150)->nullable()->after('business_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'line_of_business']);
        });
    }
};
