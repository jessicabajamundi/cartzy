<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_buyers' => User::where('role', User::ROLE_BUYER)->count(),
            'total_sellers' => User::where('role', User::ROLE_SELLER)->count(),
            'total_couriers' => User::where('role', User::ROLE_COURIER)->count(),
            'total_gmv' => 1245800.00,
            'platform_revenue' => 62290.00,
            'pending_seller_kyc' => 5,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
