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
            if (!Schema::hasColumn('users', 'status_reason')) {
                $table->text('status_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'removal_reason')) {
                $table->text('removal_reason')->nullable()->after('status_reason');
            }
            if (!Schema::hasColumn('users', 'removed_at')) {
                $table->timestamp('removed_at')->nullable()->after('removal_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status_reason')) {
                $table->dropColumn('status_reason');
            }
            if (Schema::hasColumn('users', 'removal_reason')) {
                $table->dropColumn('removal_reason');
            }
            if (Schema::hasColumn('users', 'removed_at')) {
                $table->dropColumn('removed_at');
            }
        });
    }
};

