<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->timestamp('cod_remitted_at')->nullable()->after('cod_collected');
            $table->foreignId('cod_remitted_by')->nullable()->after('cod_remitted_at')->constrained('users')->nullOnDelete();
            $table->string('cod_remittance_method', 40)->nullable()->after('cod_remitted_by');
            $table->string('cod_remittance_ref', 150)->nullable()->after('cod_remittance_method');
            $table->text('cod_remittance_notes')->nullable()->after('cod_remittance_ref');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['cod_remitted_by']);
            $table->dropColumn([
                'cod_remitted_at',
                'cod_remitted_by',
                'cod_remittance_method',
                'cod_remittance_ref',
                'cod_remittance_notes',
            ]);
        });
    }
};
