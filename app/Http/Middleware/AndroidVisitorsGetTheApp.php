<?php

namespace App\Http\Middleware;

use App\Http\Controllers\RiderAppController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * On an Android phone's browser, the home page is the "get the app" page.
 * Computers, iPhones (which cannot install the APK) and crawlers get the store.
 */
class AndroidVisitorsGetTheApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = RiderAppController::isAndroidVisitor($request)
            ? RiderAppController::page($request, forEveryone: true)
            : $next($request);

        // One address, two pages: caches must keep them apart by browser.
        $response->headers->set('Vary', trim($response->headers->get('Vary').', User-Agent', ', '));

        return $response;
    }
}
