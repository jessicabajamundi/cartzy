<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('provider_ref');
            $table->timestamp('verified_at')->nullable()->after('receipt_path');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
            $table->string('verification_notes')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'verified_at', 'verified_by', 'verification_notes']);
        });
    }
};

