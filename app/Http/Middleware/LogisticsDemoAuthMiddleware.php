<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogisticsDemoAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Ensure only authenticated active logistics personnel can access logistics portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route(\App\Http\Controllers\AuthController::loginRouteForPath($request->path()));
        }

        abort_unless(
            $request->user()->isLogistics() &&
            $request->user()->status === 'active' &&
            !$request->user()->is_suspended,
            403
        );

        return $next($request);
    }
}
