<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Category metadata dictionary mapping slug to curated assets, department, and items.
     */
    protected static array $categoryMeta = [
        'women-clothing' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '5.8k+',
            'tag' => 'Popular',
            'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&h=400&fit=crop&q=80',
            'description' => 'Dresses, chic tops, sets & season trends',
            'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
        ],
        'beachwear' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '1.4k+',
            'tag' => 'Trending',
            'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=400&fit=crop&q=80',
            'description' => 'Swimsuits, cover-ups, beach towels & sun hats',
            'icon' => 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
        ],
        'kids' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '2.1k+',
            'tag' => 'Family',
            'image' => 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=400&h=400&fit=crop&q=80',
            'description' => 'Playful clothing, sets & footwear for boys & girls',
            'icon' => 'M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm5.25 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Z',
        ],
        'curve' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '1.6k+',
            'tag' => 'Exclusive',
            'image' => 'https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=400&h=400&fit=crop&q=80',
            'description' => 'Plus-size trendy dresses, denim & everyday curve',
            'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z',
        ],
        'men-clothing' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '3.9k+',
            'tag' => 'Essential',
            'image' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=400&h=400&fit=crop&q=80',
            'description' => 'Classic shirts, casual streetwear, polos & outerwear',
            'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 0a1.125 1.125 0 0 1 1.125-1.125h2.25a1.125 1.125 0 0 1 1.125 1.125v3.75m-4.5 0h4.5',
        ],
        'shoes' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '4.5k+',
            'tag' => 'Best Seller',
            'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop&q=80',
            'description' => 'Sneakers, running shoes, heels, loafers & sandals',
            'icon' => 'M3.75 18h16.5v2.25H3.75zm16.5-4.5c-.75-2.25-2.25-4.5-5.25-5.25l-4.5-1-2.25 2.25-3.75 1.5c-1.5.75-2.25 2.25-2.25 3.75v1.5h18v-2.75z',
        ],
        'jewelry-accessories' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '3.2k+',
            'tag' => 'Sparkle',
            'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=400&fit=crop&q=80',
            'description' => '18K gold chains, earrings, rings, watches & shades',
            'icon' => 'M6 3h12l4 6-10 12L2 9l4-6zm0 0l4 6m4-6l-4 6m4-6l6 6M2 9h20M6 9l6 12 6-12',
        ],
        'underwear-sleepwear' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '2.3k+',
            'tag' => 'Comfort',
            'image' => 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=400&h=400&fit=crop&q=80',
            'description' => 'Mulberry silk pajamas, cozy sets, bralettes & robes',
            'icon' => 'M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z',
        ],
        'baby-maternity' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Kids',
            'items_count' => '1.7k+',
            'tag' => 'Gentle',
            'image' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400&h=400&fit=crop&q=80',
            'description' => 'Organic baby swaddles, carriers, maternity wear & care',
            'icon' => 'M5.5 19a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zm13 0a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM8 16.5h8M3 4h3l2.5 8h8.5l2-6H6.5M9 4a4.5 4.5 0 0 1 9 0',
        ],
        'bags-luggage' => [
            'department' => 'fashion',
            'department_label' => 'Fashion & Apparel',
            'items_count' => '2.8k+',
            'tag' => 'Premium',
            'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop&q=80',
            'description' => 'Crossbody slings, leather totes, backpacks & suitcases',
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        ],
        'home-living' => [
            'department' => 'home',
            'department_label' => 'Home & Living',
            'items_count' => '4.1k+',
            'tag' => 'Cozy',
            'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=400&h=400&fit=crop&q=80',
            'description' => 'Nordic decor, accent lighting, cookware & organizers',
            'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        ],
        'beauty-health' => [
            'department' => 'beauty',
            'department_label' => 'Beauty & Care',
            'items_count' => '5.2k+',
            'tag' => 'Top Rated',
            'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80',
            'description' => 'Korean skincare, glow serums, fragrances & body care',
            'icon' => 'M9 3.75h6M12 3.75v3m-4.5 3h9a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 16.5 21.75h-9a2.25 2.25 0 0 1-2.25-2.25v-7.5A2.25 2.25 0 0 1 7.5 9.75zm4.5 4.5v4.5m-2.25-2.25h4.5',
        ],
        'sports-outdoors' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Sports',
            'items_count' => '2.9k+',
            'tag' => 'Active',
            'image' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=400&h=400&fit=crop&q=80',
            'description' => 'Yoga mats, workout gear, cycling & camping equipment',
            'icon' => 'M6 6.75h12M6 17.25h12M6 6.75v10.5M18 6.75v10.5M3 8.25h3v7.5H3zm15 0h3v7.5h-3zM6 12h12',
        ],
        'home-textiles' => [
            'department' => 'home',
            'department_label' => 'Home & Living',
            'items_count' => '1.5k+',
            'tag' => 'Luxury',
            'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=400&h=400&fit=crop&q=80',
            'description' => 'Egyptian cotton sheets, plush towels & fleece throws',
            'icon' => 'M3 7.5v11.25m0-7.5h18m0-3.75v11.25M3 15h18M6 7.5a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 7.5v3.75H6V7.5z',
        ],
        'cell-phones-accessories' => [
            'department' => 'tech',
            'department_label' => 'Electronics & Tech',
            'items_count' => '6.1k+',
            'tag' => 'Hot Deal',
            'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=400&fit=crop&q=80',
            'description' => 'MagSafe chargers, slim cases, cables & power banks',
            'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
        ],
        'electronics' => [
            'department' => 'tech',
            'department_label' => 'Electronics & Tech',
            'items_count' => '4.8k+',
            'tag' => 'Tech Pick',
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&q=80',
            'description' => 'Wireless ANC audio, gaming gear, smart cameras & laptops',
            'icon' => 'M3 18v-6a9 9 0 0 1 18 0v6M3 16.5a2.25 2.25 0 0 0 2.25 2.25h.75a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H3.75a.75.75 0 0 0-.75.75zm18 0a2.25 2.25 0 0 1-2.25 2.25h-.75a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1h1.5a.75.75 0 0 1 .75.75z',
        ],
        'tools-home-improvement' => [
            'department' => 'home',
            'department_label' => 'Home & Living',
            'items_count' => '1.3k+',
            'tag' => 'DIY Ready',
            'image' => 'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=400&h=400&fit=crop&q=80',
            'description' => 'Cordless electric screwdrivers, drills & hardware kits',
            'icon' => 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
        ],
        'toys-games' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Kids',
            'items_count' => '2.4k+',
            'tag' => 'Fun',
            'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=400&h=400&fit=crop&q=80',
            'description' => 'STEM blocks, RC stunt vehicles, puzzles & board games',
            'icon' => 'M6 11h4m-2-2v4m7-2h.01m2.99 0h.01M17.5 6h-11a5.5 5.5 0 0 0-5.5 5.5c0 2.5 1.5 4.5 3.5 5.2l2.5 3.3a1 1 0 0 0 1.6-.2l2.1-4.8h2.6l2.1 4.8a1 1 0 0 0 1.6.2l2.5-3.3c2-.7 3.5-2.7 3.5-5.2A5.5 5.5 0 0 0 17.5 6z',
        ],
        'pet-supplies' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Pets',
            'items_count' => '1.9k+',
            'tag' => 'Pet Love',
            'image' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400&h=400&fit=crop&q=80',
            'description' => 'Slicker brushes, orthopedic beds, feeding bowls & toys',
            'icon' => 'M12 12c-2.2 0-4 1.8-4 4 0 1.7 1.3 3 3 3 .8 0 1.5-.3 2-1 .5.7 1.2 1 2 1 1.7 0 3-1.3 3-3 0-2.2-1.8-4-4-4zm-5-3a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm4-3a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm4 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm4 3a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
        ],
        'appliances' => [
            'department' => 'home',
            'department_label' => 'Home & Living',
            'items_count' => '1.8k+',
            'tag' => 'Smart Home',
            'image' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=400&h=400&fit=crop&q=80',
            'description' => 'Digital air fryers, handheld garment steamers & blenders',
            'icon' => 'M8 3h8l1 9H7L8 3zm-2 13h12a2 2 0 0 1 2 2v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2a2 2 0 0 1 2-2zm6-4v4m-3 0h6M7 6H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2',
        ],
        'office-school-supplies' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Office',
            'items_count' => '2.2k+',
            'tag' => 'Work & Study',
            'image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400&h=400&fit=crop&q=80',
            'description' => 'Aesthetic gel pens, organizers, fine leather journals',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        ],
        'automotive' => [
            'department' => 'lifestyle',
            'department_label' => 'Lifestyle & Auto',
            'items_count' => '1.1k+',
            'tag' => 'Auto Care',
            'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=400&h=400&fit=crop&q=80',
            'description' => 'Cordless car vacuums, ceramic coatings & phone mounts',
            'icon' => 'M5.5 16.5h13m-11.5 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM4 12.5l2-6h12l2 6M2.5 12.5h19v3.5h-19v-3.5z',
        ],
    ];

    /**
     * Get the category metadata dictionary.
     */
    public function getCategoryMeta(): array
    {
        return self::$categoryMeta;
    }


    /**
     * Show the public homepage with full dynamic database categories.
     */
    public function index(Request $request)
    {
        $categories = $this->getCategoriesWithMeta();
        $activeCategory = $request->query('category') ?? $request->query('cat');
        $searchQuery = $request->query('q');

        // Department counts
        $departmentCounts = [
            'all' => count($categories),
            'fashion' => 0,
            'tech' => 0,
            'home' => 0,
            'beauty' => 0,
            'lifestyle' => 0,
        ];

        foreach ($categories as $cat) {
            $dept = $cat->meta['department'] ?? 'lifestyle';
            if (isset($departmentCounts[$dept])) {
                $departmentCounts[$dept]++;
            }
        }

        return view('home', compact(
            'categories',
            'activeCategory',
            'searchQuery',
            'departmentCounts'
        ));
    }

    /**
     * Dedicated Category landing / filter route: /category/{slug}
     */
    public function category(string $slug, Request $request)
    {
        $request->merge(['category' => $slug]);
        return $this->index($request);
    }

    /**
     * Fetch active categories from database and enrich with UI metadata.
     */
    public function getCategoriesWithMeta()
    {
        try {
            $dbCategories = Category::where('is_active', true)
                ->orderBy('position')
                ->get();

            if ($dbCategories->isEmpty()) {
                $dbCategories = $this->getDefaultCategories();
            }
        } catch (\Throwable $e) {
            $dbCategories = $this->getDefaultCategories();
        }

        return $dbCategories->map(function ($category) {
            $slug = $category->slug ?: Str::slug($category->name);
            $meta = self::$categoryMeta[$slug] ?? [
                'department' => 'lifestyle',
                'department_label' => 'Lifestyle & More',
                'items_count' => '1.5k+',
                'tag' => 'Featured',
                'image' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=400&h=400&fit=crop&q=80',
                'description' => 'Explore authentic verified items in this collection',
                'icon' => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z',
            ];

            $category->meta = $meta;
            return $category;
        });
    }

    /**
     * Fallback collection of 22 categories in case the database is offline.
     */
    protected function getDefaultCategories()
    {
        $raw = [
            ['id' => 1,  'name' => 'Women Clothing',           'position' => 1],
            ['id' => 2,  'name' => 'Beachwear',                'position' => 2],
            ['id' => 3,  'name' => 'Kids',                     'position' => 3],
            ['id' => 4,  'name' => 'Curve',                    'position' => 4],
            ['id' => 5,  'name' => 'Men Clothing',            'position' => 5],
            ['id' => 6,  'name' => 'Shoes',                    'position' => 6],
            ['id' => 7,  'name' => 'Jewelry & Accessories',   'position' => 7],
            ['id' => 8,  'name' => 'Underwear & Sleepwear',   'position' => 8],
            ['id' => 9,  'name' => 'Baby & Maternity',        'position' => 9],
            ['id' => 10, 'name' => 'Bags & Luggage',          'position' => 10],
            ['id' => 11, 'name' => 'Home & Living',           'position' => 11],
            ['id' => 12, 'name' => 'Beauty & Health',         'position' => 12],
            ['id' => 13, 'name' => 'Sports & Outdoors',       'position' => 13],
            ['id' => 14, 'name' => 'Home Textiles',           'position' => 14],
            ['id' => 15, 'name' => 'Cell Phones & Accessories','position' => 15],
            ['id' => 16, 'name' => 'Electronics',             'position' => 16],
            ['id' => 17, 'name' => 'Tools & Home Improvement','position' => 17],
            ['id' => 18, 'name' => 'Toys & Games',            'position' => 18],
            ['id' => 19, 'name' => 'Pet Supplies',            'position' => 19],
            ['id' => 20, 'name' => 'Appliances',              'position' => 20],
            ['id' => 21, 'name' => 'Office & School Supplies','position' => 21],
            ['id' => 22, 'name' => 'Automotive',              'position' => 22],
        ];

        return collect($raw)->map(function ($item) {
            $cat = new Category();
            $cat->id = $item['id'];
            $cat->name = $item['name'];
            $cat->slug = Str::slug($item['name']);
            $cat->position = $item['position'];
            $cat->is_active = true;
            return $cat;
        });
    }
}
