<?php

namespace App\Services;

use App\Repositories\Interfaces\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboard
    ) {}

    /**
     * All data needed to render the dashboard page.
     */
    public function getDashboardData(int $chartDays = 7): array
    {
        return [
            // Stat cards
            'totalProducts'        => $this->dashboard->getTotalProducts(),
            'totalStock'           => $this->dashboard->getTotalStock(),
            'totalSuppliers'       => $this->dashboard->getTotalSuppliers(),
            'todayMovementsCount'  => $this->dashboard->getTodayMovementsCount(),
            'lowStockCount'        => $this->dashboard->getLowStockCount(),
 
            // Table + activity
            'recentMovements'      => $this->dashboard->getRecentMovements(5),
            'lowStockProducts'     => $this->dashboard->getLowStockProducts(6),
 
            // Chart
            'chartData'            => $this->dashboard->getStockChartData($chartDays),
            'chartDays'            => $chartDays,
        ];
    }
}