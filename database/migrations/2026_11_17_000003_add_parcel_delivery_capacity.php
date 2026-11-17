<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('package_length_cm', 8, 2)->nullable();
            $table->decimal('package_width_cm', 8, 2)->nullable();
            $table->decimal('package_height_cm', 8, 2)->nullable();
        });
        Schema::table('logistics_providers', function (Blueprint $table) {
            $table->boolean('bulky_enabled')->default(false);
            $table->unsignedInteger('bulky_surcharge_minor')->default(0);
        });
        Schema::table('shipments', function (Blueprint $table) {
            $table->json('package_profile')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', fn (Blueprint $table) => $table->dropColumn('package_profile'));
        Schema::table('logistics_providers', fn (Blueprint $table) => $table->dropColumn(['bulky_enabled', 'bulky_surcharge_minor']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['package_length_cm', 'package_width_cm', 'package_height_cm']));
    }
};
