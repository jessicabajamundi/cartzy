<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hero_slides')
            ->where('key', 'halloween')
            ->where('image_path', 'images/banners/cartzy-halloween.jpg')
            ->update([
                'image_path' => 'images/banners/cartzy-halloween-web.png',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Keep the replacement image: the old asset has been permanently removed.
    }
};
