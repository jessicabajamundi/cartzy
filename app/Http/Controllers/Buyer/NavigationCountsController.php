<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\BuyerNavigationCounts;
use Illuminate\Http\Request;

class NavigationCountsController extends Controller
{
    public function __invoke(Request $request, BuyerNavigationCounts $counts)
    {
        return response()->json(['counts' => $counts->forUser($request->user())]);
    }
}
