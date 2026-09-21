<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $courier = Auth::user();

        $stats = [
            'assigned_pickups' => 12,
            'out_for_delivery' => 18,
            'delivered_today' => 34,
            'cod_collected_today' => 14250.00,
            'rider_rating' => 4.95,
        ];

        return view('courier.dashboard', compact('courier', 'stats'));
    }
}
