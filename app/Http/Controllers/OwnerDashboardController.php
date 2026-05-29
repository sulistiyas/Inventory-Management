<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OwnerDashboardService;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function __construct(
        protected OwnerDashboardService $service,
    ) {}

    /**
     * Halaman utama dashboard owner.
     */
    public function index(Request $request)
    {
        $chartDays = (int) $request->get('days', 7);
        $chartDays = in_array($chartDays, [7, 14, 30]) ? $chartDays : 7;

        $date = $request->get('date', today()->toDateString());

        $data = $this->service->getDashboardData($chartDays, $date);

        return view('owner-dashboard.index', $data);
    }

    /**
     * API: refresh grafik saat owner ganti periode (7/14/30 hari).
     * Dipanggil via fetch dari Alpine.
     */
    public function chartApi(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $days = in_array($days, [7, 14, 30]) ? $days : 7;

        return response()->json($this->service->getChartData($days));
    }

    /**
     * Halaman laporan penjualan.
     */
    public function salesReport(Request $request)
    {
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   today()->toDateString());

        $report = $this->service->getSalesReport($dateFrom, $dateTo);

        return view('owner-dashboard.sales-report', array_merge($report, [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]));
    }
}
