<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $chartDays = (int) $request->get('days', 7);
        $chartDays = in_array($chartDays, [7, 14, 30]) ? $chartDays : 7;

        $data = $this->dashboardService->getDashboardData($chartDays);

        return view('dashboard.index', $data);
    }
}