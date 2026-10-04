<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SellerDemoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== User::ROLE_SELLER) {
            try {
                $user = User::where('role', User::ROLE_SELLER)->first()
                    ?? User::where('email', 'seller@marketstore.ph')->first();

                if (!$user) {
                    $user = User::create([
                        'name' => 'Maria Santos (TechZone Store)',
                        'email' => 'seller@marketstore.ph',
                        'password' => bcrypt('password123'),
                        'role' => User::ROLE_SELLER,
                        'status' => 'active',
                    ]);
                }
                Auth::login($user);
            } catch (\Throwable $e) {
                $user = new User();
                $user->forceFill([
                    'id' => 2,
                    'name' => 'Maria Santos (TechZone Store)',
                    'email' => 'seller@marketstore.ph',
                    'role' => User::ROLE_SELLER,
                    'status' => 'active',
                ]);
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
