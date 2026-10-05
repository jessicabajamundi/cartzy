<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share cart items and active categories with all views
        View::composer('*', function ($view) {
            $cart = Session::get('cart', []);
            $view->with('cartItems', array_values($cart));
            $view->with('cartCount', array_sum(array_column($cart, 'quantity')));

            try {
                $categories = \App\Models\Category::where('is_active', true)->orderBy('position')->get();
                $homeController = new \App\Http\Controllers\HomeController();
                $metaMap = $homeController->getCategoryMeta();
                foreach ($categories as $cat) {
                    $cat->meta = $metaMap[$cat->slug] ?? [
                        'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
                    ];
                }
            } catch (\Throwable $e) {
                $categories = collect();
            }
            $view->with('allCategories', $categories);
        });
    }
}
