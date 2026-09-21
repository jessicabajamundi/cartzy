<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = Auth::user();
        
        $stats = [
            'to_ship' => 8,
            'shipping' => 14,
            'completed' => 142,
            'unpaid' => 3,
            'total_sales' => 84250.00,
            'wallet_balance' => 32400.00,
            'total_products' => 26,
            'rating' => 4.9,
        ];

        return view('seller.dashboard', compact('seller', 'stats'));
    }
}
