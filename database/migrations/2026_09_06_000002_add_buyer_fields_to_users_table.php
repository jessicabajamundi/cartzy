<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'middle_initial')) {
                $table->string('middle_initial', 5)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'sex')) {
                $table->string('sex', 10)->nullable()->after('middle_initial');
            }
            if (!Schema::hasColumn('users', 'birthday')) {
                $table->date('birthday')->nullable()->after('sex');
            }
            if (!Schema::hasColumn('users', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('birthday');
            }
            if (!Schema::hasColumn('users', 'id_photo')) {
                $table->string('id_photo', 500)->nullable()->after('age');
            }
            // pending_approval status support — status column already exists
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['middle_initial', 'sex', 'birthday', 'age', 'id_photo']);
        });
    }
};
