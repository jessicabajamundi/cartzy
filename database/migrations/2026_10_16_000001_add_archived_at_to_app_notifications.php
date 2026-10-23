<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_notifications', 'archived_at')) {
            Schema::table('app_notifications', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('is_read');
            });
        }
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
