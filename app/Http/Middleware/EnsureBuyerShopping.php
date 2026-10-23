<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerShopping
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isBuyer()) {
            $message = 'Purchases require a separate buyer account. Log out and sign in as a buyer, or register a new account with the Buyer role using a different email address.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message, 'requires_buyer' => true], 403);
            }
            return response()->view('auth.buyer-required', [], 403);
        }

        return $next($request);
    }
}
