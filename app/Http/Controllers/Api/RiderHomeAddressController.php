<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A rider or courier sets where they live. The hub uses it to give them
 * pickups and deliveries in their own barangay first.
 */
class RiderHomeAddressController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $user->update(array_map('trim', $data));

        return response()->json([
            'message' => 'Home address saved. Your hub will match you with nearby pickups and deliveries.',
            'home_address' => AuthController::homeAddress($user->fresh()),
        ]);
    }
}
