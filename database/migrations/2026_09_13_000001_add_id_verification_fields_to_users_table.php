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
            if (!Schema::hasColumn('users', 'id_type')) {
                $table->string('id_type', 100)->nullable()->after('id_photo');
            }
            if (!Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number', 100)->nullable()->after('id_type');
            }
            if (!Schema::hasColumn('users', 'id_status')) {
                $table->string('id_status', 50)->default('unverified')->after('id_number');
            }
            if (!Schema::hasColumn('users', 'id_rejection_reason')) {
                $table->text('id_rejection_reason')->nullable()->after('id_status');
            }
            if (!Schema::hasColumn('users', 'id_verified_at')) {
                $table->timestamp('id_verified_at')->nullable()->after('id_rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_type',
                'id_number',
                'id_status',
                'id_rejection_reason',
                'id_verified_at',
            ]);
        });
    }
};
