<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_dispute_id')->unique()->constrained('admin_disputes')->restrictOnDelete();
            $table->string('status', 40)->default('requested')->index();
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->text('return_address')->nullable();
            $table->text('review_notes')->nullable();
            $table->string('handover_method', 20)->nullable();
            $table->date('scheduled_date')->nullable();
            $table->text('pickup_address')->nullable();
            $table->foreignId('logistics_provider_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tracking_code')->nullable()->unique();
            $table->timestamp('received_at')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->timestamps();
        });
        Schema::create('return_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 40);
            $table->text('notes');
            $table->timestamps();
        });
        Schema::create('refund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_case_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->string('method', 30);
            $table->string('reference', 150);
            $table->unique(['method', 'reference']);
            $table->string('proof_path');
            $table->timestamp('paid_at');
            $table->timestamps();
        });
        // Open requests made before this feature remain actionable.
        DB::table('admin_disputes')->where('status', 'open')->whereNotNull('seller_order_id')->whereNotNull('reporter_id')
            ->whereIn('requested_resolution', ['refund', 'return_refund'])->orderBy('id')->eachById(function ($report) {
                DB::table('return_cases')->insert(['admin_dispute_id' => $report->id, 'status' => 'requested',
                    'created_at' => $report->created_at, 'updated_at' => now()]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_transactions');
        Schema::dropIfExists('return_events');
        Schema::dropIfExists('return_cases');
    }
};
