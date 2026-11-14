<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('gcash_qr_path')->nullable()->after('banner_path');
            $table->string('gcash_name')->nullable()->after('gcash_qr_path');
            $table->string('gcash_number')->nullable()->after('gcash_name');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['gcash_qr_path', 'gcash_name', 'gcash_number']);
        });
    }
};

