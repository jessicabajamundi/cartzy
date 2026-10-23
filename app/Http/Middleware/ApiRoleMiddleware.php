<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile API version of RoleMiddleware: answers with JSON instead of redirecting,
 * and refuses tokens of accounts that were suspended or deactivated after logging in.
 */
class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        if ($user->status !== 'active' || $user->is_suspended) {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }
        if ($roles && !in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'This action is not available for your account type.'], 403);
        }

        return $next($request);
    }
}
