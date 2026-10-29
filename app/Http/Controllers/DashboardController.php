<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStatsService $stats): View
    {
        $period = ReportPeriod::fromRequest($request);

        return view('dashboard', array_merge($period->viewData(), $stats->counts(period: $period), [
            'orderFilterQuery' => $period->queryParams(),
        ]));
    }
}
