<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tin_number', 12)->nullable()->change();
            $table->string('seller_type', 30)->nullable();
            $table->string('sec_certificate')->nullable();
            $table->string('bir_certificate')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['seller_type', 'sec_certificate', 'bir_certificate']);
        });
    }
};
