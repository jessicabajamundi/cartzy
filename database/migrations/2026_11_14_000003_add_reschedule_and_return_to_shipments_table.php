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
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'rescheduled_until')) {
                $table->timestamp('rescheduled_until')->nullable()->after('assigned_at');
            }
            if (!Schema::hasColumn('shipments', 'reschedule_reason')) {
                $table->string('reschedule_reason')->nullable()->after('rescheduled_until');
            }
            if (!Schema::hasColumn('shipments', 'reschedule_count')) {
                $table->unsignedInteger('reschedule_count')->default(0)->after('reschedule_reason');
            }
            if (!Schema::hasColumn('shipments', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('reschedule_count');
            }
            if (!Schema::hasColumn('shipments', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('return_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'rescheduled_until',
                'reschedule_reason',
                'reschedule_count',
                'return_reason',
                'returned_at',
            ]);
        });
    }
};
