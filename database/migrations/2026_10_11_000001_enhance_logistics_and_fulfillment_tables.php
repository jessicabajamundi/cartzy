<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add fields to riders table
        Schema::table('riders', function (Blueprint $table) {
            if (!Schema::hasColumn('riders', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('approved')->after('plate_no');
            }
            if (!Schema::hasColumn('riders', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('riders', 'area')) {
                $table->string('area')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('riders', 'rating')) {
                $table->decimal('rating', 3, 2)->default(5.00)->after('area');
            }
            if (!Schema::hasColumn('riders', 'deliveries_today')) {
                $table->unsignedInteger('deliveries_today')->default(0)->after('rating');
            }
        });

        // 2. Add fields to shipments table
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'delivery_area')) {
                $table->string('delivery_area')->nullable()->after('status');
            }
            if (!Schema::hasColumn('shipments', 'sort_status')) {
                $table->enum('sort_status', ['unsorted', 'sorted', 'assigned'])->default('unsorted')->after('delivery_area');
            }
            if (!Schema::hasColumn('shipments', 'weight_kg')) {
                $table->decimal('weight_kg', 8, 2)->default(1.00)->after('sort_status');
            }
            if (!Schema::hasColumn('shipments', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('shipments', 'sorted_at')) {
                $table->timestamp('sorted_at')->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('shipments', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('sorted_at');
            }
        });

        // 3. Create pickup_requests table if not exists
        if (!Schema::hasTable('pickup_requests')) {
            Schema::create('pickup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('logistics_provider_id')->constrained('logistics_providers')->cascadeOnDelete();
                $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
                $table->foreignId('seller_order_id')->nullable()->constrained('seller_orders')->nullOnDelete();
                $table->unsignedInteger('parcel_count')->default(1);
                $table->decimal('weight_kg', 8, 2)->default(1.00);
                $table->date('pickup_date');
                $table->string('pickup_time')->nullable();
                $table->text('address');
                $table->enum('status', ['pending', 'confirmed', 'picked_up', 'cancelled'])->default('pending');
                $table->timestamp('confirmed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 4. Create logistics_messages table if not exists
        if (!Schema::hasTable('logistics_messages')) {
            Schema::create('logistics_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('logistics_provider_id')->constrained('logistics_providers')->cascadeOnDelete();
                $table->foreignId('contact_user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('sender_type', ['hub', 'contact'])->default('contact');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['logistics_provider_id', 'contact_user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics_messages');
        Schema::dropIfExists('pickup_requests');

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_area',
                'sort_status',
                'weight_kg',
                'received_at',
                'sorted_at',
                'assigned_at',
            ]);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'rejection_reason',
                'area',
                'rating',
                'deliveries_today',
            ]);
        });
    }
};
