<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the categories table with Shein-style top-level categories.
     */
    public function run(): void
    {
        // Clear existing categories safely (disable FK checks)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            ['name' => 'Women Clothing',            'position' => 1],
            ['name' => 'Beachwear',                  'position' => 2],
            ['name' => 'Kids',                        'position' => 3],
            ['name' => 'Curve',                       'position' => 4],
            ['name' => 'Men Clothing',               'position' => 5],
            ['name' => 'Shoes',                       'position' => 6],
            ['name' => 'Jewelry & Accessories',      'position' => 7],
            ['name' => 'Underwear & Sleepwear',      'position' => 8],
            ['name' => 'Baby & Maternity',           'position' => 9],
            ['name' => 'Bags & Luggage',             'position' => 10],
            ['name' => 'Home & Living',              'position' => 11],
            ['name' => 'Beauty & Health',            'position' => 12],
            ['name' => 'Sports & Outdoors',          'position' => 13],
            ['name' => 'Home Textiles',              'position' => 14],
            ['name' => 'Cell Phones & Accessories',  'position' => 15],
            ['name' => 'Electronics',                'position' => 16],
            ['name' => 'Tools & Home Improvement',   'position' => 17],
            ['name' => 'Toys & Games',               'position' => 18],
            ['name' => 'Pet Supplies',               'position' => 19],
            ['name' => 'Appliances',                 'position' => 20],
            ['name' => 'Office & School Supplies',   'position' => 21],
            ['name' => 'Automotive',                 'position' => 22],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'parent_id'  => null,
                'name'       => $category['name'],
                'slug'       => Str::slug($category['name']),
                'position'   => $category['position'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ ' . count($categories) . ' categories seeded successfully!');
    }
}
