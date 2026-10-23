<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The app is served behind an HTTPS proxy/tunnel (APP_URL=https://cartzyy.shop).
        // Trust its X-Forwarded-* headers so generated URLs and redirects use https://.
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(fn ($request) => route(\App\Http\Controllers\AuthController::loginRouteForPath($request->path())));
        $middleware->redirectUsersTo(fn () => auth()->user() ? route(auth()->user()->getDashboardRoute()) : '/');

        $middleware->append(\App\Http\Middleware\PreventBackHistory::class);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountActive::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.role' => \App\Http\Middleware\ApiRoleMiddleware::class,
            'admin.demo' => \App\Http\Middleware\AdminDemoAuthMiddleware::class,
            'seller.demo' => \App\Http\Middleware\SellerDemoAuthMiddleware::class,
            'logistics.demo' => \App\Http\Middleware\LogisticsDemoAuthMiddleware::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['device_password']);
    })->create();
