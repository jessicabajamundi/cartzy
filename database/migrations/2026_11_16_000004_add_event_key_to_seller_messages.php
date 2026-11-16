<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_messages', function (Blueprint $table) {
            $table->string('event_key', 50)->nullable();
            $table->unique(['seller_order_id', 'event_key'], 'seller_messages_order_event_unique');
        });
    }

    public function down(): void
    {
        Schema::table('seller_messages', function (Blueprint $table) {
            $table->dropUnique('seller_messages_order_event_unique');
            $table->dropColumn('event_key');
        });
    }
};
