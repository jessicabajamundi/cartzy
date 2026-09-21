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
            $table->string('role')->default('buyer')->after('password'); // admin, seller, courier, buyer
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->string('status')->default('active')->after('avatar'); // active, suspended, pending
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'avatar', 'status']);
        });
    }
};
