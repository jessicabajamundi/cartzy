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
        // Share cart items from session with all views (for header cart count/popover)
        View::composer('*', function ($view) {
            $cart = Session::get('cart', []);
            $view->with('cartItems', array_values($cart));
            $view->with('cartCount', array_sum(array_column($cart, 'quantity')));
        });
    }
}
