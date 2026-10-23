<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_comments', function (Blueprint $table) {
            // Replies are one level deep: parent_id always points at a top-level comment.
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('product_comments')->cascadeOnDelete();
        });

        Schema::create('product_comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->timestamps();
            $table->unique(['product_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_comment_reactions');
        Schema::table('product_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
