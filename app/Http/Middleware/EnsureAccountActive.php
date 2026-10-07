<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ($user->is_suspended || in_array($user->status, ['suspended', 'deactivated', 'rejected'], true))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->expectsJson()) return response()->json(['message' => 'Your account is not active.'], 403);
            return redirect()->route('login')->withErrors(['email' => 'Your account is not active. Please contact support.']);
        }
        return $next($request);
    }
}
