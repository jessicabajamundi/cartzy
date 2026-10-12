<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerDemoAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Ensure only authenticated active sellers can access seller portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        abort_unless(
            $request->user()->isSeller() &&
            $request->user()->status === 'active' &&
            !$request->user()->is_suspended,
            403
        );

        return $next($request);
    }
}
