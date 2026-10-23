<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('title');
            $table->string('accent');
            $table->text('description');
            $table->string('cta');
            $table->string('href');
            $table->string('alt');
            $table->string('image_path');
            $table->unsignedInteger('position')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $heroSlides = [
            [
                'key' => 'halloween',
                'label' => 'Halloween picks',
                'title' => 'Spooky season.',
                'accent' => 'Fang-tastic finds.',
                'description' => 'A little spooky, a lot to love. Bring home the Halloween spirit with festive touches and everyday favorites from Cartzy.',
                'cta' => 'Explore the marketplace',
                'href' => '#daily-discover-section',
                'alt' => 'Glowing Halloween pumpkins with a witch hat, a smiling ceramic ghost, candies, and autumn leaves',
            ],
            [
                'key' => 'home',
                'label' => 'Home & living',
                'title' => 'Good finds.',
                'accent' => 'Great everyday living.',
                'description' => 'Make room for the things you love. Discover home essentials, fresh styles, and everyday favorites, all on Cartzy.',
                'cta' => 'Explore the marketplace',
                'href' => '#daily-discover-section',
                'alt' => 'An oak lounge chair with a soft ivory cushion beside a walnut table and ceramic vase',
            ],
            [
                'key' => 'tech',
                'label' => 'Tech & audio',
                'title' => 'Tune into more.',
                'accent' => 'Make it your moment.',
                'description' => 'From your morning playlist to your evening wind-down, find tech that fits the way you live.',
                'cta' => 'Discover tech & audio',
                'href' => '/category/electronics',
                'alt' => 'Charcoal wireless headphones and lavender earbuds on a pale stone display',
            ],
            [
                'key' => 'style',
                'label' => 'Everyday style',
                'title' => 'Go your own way.',
                'accent' => 'Find your everyday fit.',
                'description' => 'Easy layers, fresh kicks, and finishing touches. Meet the pieces that make every day feel a little more you.',
                'cta' => 'Find your next pair',
                'href' => '/category/shoes',
                'alt' => 'Cream sneakers, a folded beige shirt, and a muted lavender crossbody bag',
            ],
            [
                'key' => 'beauty',
                'label' => 'Beauty & self-care',
                'title' => 'A little me-time.',
                'accent' => 'A softer kind of glow.',
                'description' => 'Slow down and make space for yourself. Discover beauty essentials for your everyday rituals.',
                'cta' => 'Explore beauty',
                'alt' => 'Blush skincare bottles with rose-gold details and a lavender cosmetic pouch',
                'href' => '/category/beauty-health',
            ],
            [
                'key' => 'jewelry',
                'label' => 'Jewelry & accessories',
                'title' => 'Little details.',
                'accent' => 'Your kind of sparkle.',
                'description' => 'The finishing touches that feel like you. Find jewelry and accessories for everyday moments.',
                'cta' => 'Discover accessories',
                'alt' => 'Rose-gold hoops, a delicate necklace, and a watch on an ivory and blush display',
                'href' => '/category/jewelry-accessories',
            ],
            [
                'key' => 'coffee',
                'label' => 'Coffee & home',
                'title' => 'Stay a little longer.',
                'accent' => 'Savor the everyday.',
                'description' => 'From your first cup to your favorite corner, discover thoughtful upgrades for a home you love.',
                'cta' => 'Shop home appliances',
                'alt' => 'An ivory espresso machine with rose-gold details beside a ceramic cappuccino cup',
                'href' => '/category/appliances',
            ],
            [
                'key' => 'travel',
                'label' => 'Bags & travel',
                'title' => 'Pack a little joy.',
                'accent' => 'Go somewhere new.',
                'description' => 'Weekend escapes or everyday adventures. Find bags and travel essentials ready to go with you.',
                'cta' => 'Explore bags & travel',
                'alt' => 'A blush cabin suitcase, taupe weekender bag, and ivory travel organizer',
                'href' => '/category/bags-luggage',
            ],
            [
                'key' => 'fitness',
                'label' => 'Movement & wellness',
                'title' => 'Move at your pace.',
                'accent' => 'Feel a little better.',
                'description' => 'A morning stretch, a fresh start, a moment for you. Find essentials for the way you like to move.',
                'cta' => 'Find your fitness essentials',
                'alt' => 'A lavender yoga mat, blush dumbbells, and an ivory water bottle with a rose-gold cap',
                'href' => '/category/sports-outdoors',
            ],
        ];

        foreach ($heroSlides as $position => $slide) {
            DB::table('hero_slides')->insert(array_merge($slide, [
                'image_path' => 'images/banners/cartzy-'.$slide['key'].'.jpg',
                'position' => $position,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
