<?php

namespace App\Http\Controllers;

use App\Services\BuddyChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chat endpoint for Cozy the teddy bear: the website posts here with its session, the app with a Bearer token.
 * Guests are welcome; only active buyers get order lookups.
 */
class BuddyController extends Controller
{
    private const HISTORY_LIMIT = 20;

    public function chat(Request $request, BuddyChat $buddy): JsonResponse
    {
        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:60'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
            'product_id' => ['nullable', 'integer'],
        ]);

        // Claude's history must start with the buyer, and must end with what they just said.
        $history = array_slice($data['messages'], -self::HISTORY_LIMIT);
        while ($history && $history[0]['role'] !== 'user') {
            array_shift($history);
        }
        if (!$history || end($history)['role'] !== 'user') {
            return response()->json(['message' => 'The last message must come from you.'], 422);
        }

        $user = $request->user() ?? auth('sanctum')->user();
        $buyer = $user && $user->isBuyer() && $user->status === 'active' ? $user : null;

        return response()->json($buddy->reply($history, $buyer, $data['product_id'] ?? null));
    }
}
