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
        // Sellers get the restock reminder once, on the first seller page after signing in.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if (method_exists($event->user, 'isSeller') && $event->user->isSeller()) {
                Session::put('seller_restock_prompt', true);
            }
        });

        // Share cart items and active categories with all views
        View::composer('*', function ($view) {
            $cart = app(\App\Services\BuyerCart::class)->all();
            $view->with('cartItems', array_values($cart));
            $view->with('cartCount', count($cart));

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
            $headerNotifications = collect();
            $headerUnreadCount = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                try {
                    $headerNotifications = \App\Services\NotificationService::getUserNotifications(\Illuminate\Support\Facades\Auth::id());
                    $headerUnreadCount = $headerNotifications->where('is_read', false)->count();
                } catch (\Throwable $e) {
                    $headerNotifications = collect();
                    $headerUnreadCount = 0;
                }
            }
            $view->with('headerNotifications', $headerNotifications);
            $view->with('headerUnreadCount', $headerUnreadCount);
        });
    }
}
