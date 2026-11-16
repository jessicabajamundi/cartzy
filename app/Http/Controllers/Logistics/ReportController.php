<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Services\LogisticsReports;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function index(Request $request, LogisticsReports $reports)
    {
        $hub = $request->user();
        $provider = $hub->logisticsProvider;
        abort_unless($provider && $provider->status === 'approved', 403);
        $data = $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d']]);
        $dateFrom = $data['from'] ?? now('Asia/Manila')->startOfMonth()->toDateString();
        $dateTo = $data['to'] ?? now('Asia/Manila')->toDateString();
        if ($dateTo < $dateFrom || Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) > 366) {
            throw ValidationException::withMessages(['to' => 'Choose an end date on or after the start date, within one year.']);
        }
        $reportData = $reports->data($provider->id, $dateFrom, $dateTo);
        return view('logistics.reports', compact('hub', 'reportData', 'dateFrom', 'dateTo'));
    }
}
