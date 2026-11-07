<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rider Hub pages live in the Android app now. Opening one in a browser
 * (computer or phone) shows the download page instead.
 */
class RidersUseTheApp
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return redirect()->route('rider.app');
        }

        return $next($request);
    }
}
