<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('admin_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 200);
            $table->text('description');
            $table->string('status')->default('open')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
        Schema::create('platform_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('target');
            $table->text('content');
            $table->timestamps();
        });
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('platform_announcements');
        Schema::dropIfExists('admin_disputes');
        Schema::dropIfExists('admin_audit_logs');
    }
};
