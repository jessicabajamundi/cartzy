<?php

namespace App\Http\Middleware;

use App\Models\LogisticsProvider;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogisticsDemoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== User::ROLE_LOGISTICS) {
            try {
                $user = User::where('role', User::ROLE_LOGISTICS)->first()
                    ?? User::where('email', 'logistics@cartzy.ph')->first();

                if (!$user) {
                    $user = User::create([
                        'name' => 'Metro South Sorting & Fulfillment Hub',
                        'email' => 'logistics@cartzy.ph',
                        'password' => bcrypt('logistics123'),
                        'role' => User::ROLE_LOGISTICS,
                        'status' => 'active',
                        'business_name' => 'Cartzy Express Logistics Hub - Metro South Facility',
                        'phone' => '09173334444',
                        'province' => 'Laguna',
                        'city' => 'Santa Cruz',
                        'barangay' => 'Poblacion',
                        'street_address' => 'KM 54 National Highway, Sort Center Complex',
                    ]);

                    LogisticsProvider::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'name' => 'Cartzy Express Logistics Hub',
                            'slug' => 'cartzy-express-logistics-hub',
                            'status' => 'approved',
                            'contact_phone' => '09173334444',
                        ]
                    );
                }
                Auth::login($user);
            } catch (\Throwable $e) {
                $user = new User();
                $user->forceFill([
                    'id' => 4,
                    'name' => 'Metro South Sorting & Fulfillment Hub',
                    'email' => 'logistics@cartzy.ph',
                    'role' => User::ROLE_LOGISTICS,
                    'status' => 'active',
                    'business_name' => 'Cartzy Express Logistics Hub - Metro South Facility',
                ]);
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
