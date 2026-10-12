<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('middle_initial', 10)->nullable();
            $table->string('sex', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('default_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('id_photo', 500)->nullable();
            $table->string('id_type', 100)->nullable();
            $table->string('id_number', 100)->nullable();
            $table->enum('id_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->text('id_rejection_reason')->nullable();
            $table->timestamp('id_verified_at')->nullable();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->string('membership_tier')->default('Regular');
            $table->enum('status', ['active', 'suspended', 'deactivated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Backfill existing users with role 'buyer' into the buyers table
        $buyers = DB::table('users')->where('role', 'buyer')->get();
        foreach ($buyers as $user) {
            // Check default address if available
            $defaultAddress = DB::table('addresses')
                ->where('user_id', $user->id)
                ->where('is_default', 1)
                ->first() ?? DB::table('addresses')->where('user_id', $user->id)->first();

            DB::table('buyers')->insertOrIgnore([
                'user_id'             => $user->id,
                'name'                => $user->name,
                'middle_initial'      => $user->middle_initial ?? null,
                'sex'                 => $user->sex ?? null,
                'birthday'            => $user->birthday ?? null,
                'age'                 => $user->age ?? null,
                'phone'               => $user->phone ?? null,
                'default_address_id'  => $defaultAddress?->id,
                'id_photo'            => $user->id_photo ?? null,
                'id_type'             => $user->id_type ?? null,
                'id_number'           => $user->id_number ?? null,
                'id_status'           => in_array($user->id_status ?? '', ['unverified', 'pending', 'verified', 'rejected']) ? $user->id_status : 'unverified',
                'id_rejection_reason' => $user->id_rejection_reason ?? null,
                'id_verified_at'      => $user->id_verified_at ?? null,
                'loyalty_points'      => 0,
                'membership_tier'     => 'Regular',
                'status'              => (!empty($user->is_suspended)) ? 'suspended' : ($user->status ?? 'active'),
                'notes'               => null,
                'created_at'          => $user->created_at ?? now(),
                'updated_at'          => $user->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
