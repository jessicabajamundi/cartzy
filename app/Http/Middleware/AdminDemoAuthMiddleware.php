<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminDemoAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) return redirect()->route('login');
        abort_unless($request->user()->isAdmin() && $request->user()->status === 'active' && !$request->user()->is_suspended, 403);
        return $next($request);
    }
}
