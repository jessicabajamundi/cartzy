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

            $categories = once(function () {
                try {
                    $cats = \App\Models\Category::where('is_active', true)->orderBy('position')->get();
                    $homeController = new \App\Http\Controllers\HomeController();
                    $metaMap = $homeController->getCategoryMeta();
                    foreach ($cats as $cat) {
                        $cat->meta = $metaMap[$cat->slug] ?? [
                            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
                        ];
                    }
                    return $cats;
                } catch (\Throwable $e) {
                    return collect();
                }
            });
            $view->with('allCategories', $categories);

            $notificationsData = once(function () {
                if (!\Illuminate\Support\Facades\Auth::check()) {
                    return ['items' => collect(), 'unread' => 0];
                }
                try {
                    $items = \App\Services\NotificationService::getUserNotifications(\Illuminate\Support\Facades\Auth::id());
                    return ['items' => $items, 'unread' => $items->where('is_read', false)->count()];
                } catch (\Throwable $e) {
                    return ['items' => collect(), 'unread' => 0];
                }
            });

            $view->with('headerNotifications', $notificationsData['items']);
            $view->with('headerUnreadCount', $notificationsData['unread']);

            $adminPendingProductsCount = once(function () {
                if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                    return 0;
                }
                try {
                    return \App\Models\Product::where('approval_status', \App\Models\Product::APPROVAL_PENDING)->count();
                } catch (\Throwable $e) {
                    return 0;
                }
            });
            $view->with('adminPendingProductsCount', $adminPendingProductsCount);

            $adminPendingRegistrationsCount = once(function () {
                if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                    return 0;
                }
                try {
                    return \App\Models\User::whereIn('role', ['seller', 'logistics', 'courier'])
                        ->where('status', 'pending')
                        ->count();
                } catch (\Throwable $e) {
                    return 0;
                }
            });
            $view->with('adminPendingRegistrationsCount', $adminPendingRegistrationsCount);

            $adminPendingIdChecksCount = once(function () {
                if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                    return 0;
                }
                try {
                    return \App\Models\User::where('role', \App\Models\User::ROLE_BUYER)
                        ->where('id_status', 'pending')
                        ->count();
                } catch (\Throwable $e) {
                    return 0;
                }
            });
            $view->with('adminPendingIdChecksCount', $adminPendingIdChecksCount);

            $adminPendingLogisticsCount = once(function () {
                if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
                    return 0;
                }
                try {
                    return \App\Models\LogisticsProvider::where('status', 'pending')->count();
                } catch (\Throwable $e) {
                    return 0;
                }
            });
            $view->with('adminPendingLogisticsCount', $adminPendingLogisticsCount);
        });
    }
}
