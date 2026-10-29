<?php

namespace App\Http\Controllers;

use App\Exports\DashboardStatsExport;
use App\Services\DashboardStatsService;
use App\Support\ReportPeriod;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStatsService $stats): View
    {
        $period = ReportPeriod::fromRequest($request);

        return view('dashboard', array_merge($period->viewData(), $stats->counts(period: $period), [
            'orderFilterQuery' => $period->queryParams(),
        ]));
    }

    public function exportPdf(Request $request, DashboardStatsService $stats): Response
    {
        $period = ReportPeriod::fromRequest($request);
        $html = view('dashboard.pdf', array_merge($period->viewData(), $stats->counts(period: $period)))->render();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dashboard-'.$period->fileSuffix().'.pdf"',
        ]);
    }

    public function exportExcel(Request $request, DashboardStatsService $stats): BinaryFileResponse
    {
        $period = ReportPeriod::fromRequest($request);

        return Excel::download(
            new DashboardStatsExport($period->label, $stats->counts(period: $period)),
            'dashboard-'.$period->fileSuffix().'.xlsx'
        );
    }
}
