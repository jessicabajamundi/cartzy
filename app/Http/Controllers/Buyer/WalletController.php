<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\BuyerWallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __invoke(Request $request, BuyerWallet $wallet)
    {
        return response()->json($wallet->summary($request->user()))->header('Cache-Control', 'no-store, private');
    }
}
