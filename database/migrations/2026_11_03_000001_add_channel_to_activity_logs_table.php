<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buyers see their own history too, so record whether it happened on the website or the mobile app.
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('channel', 10)->nullable()->after('method'); // web, mobile
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropColumn('channel');
        });
    }
};
