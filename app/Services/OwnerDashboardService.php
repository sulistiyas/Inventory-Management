<?php

namespace App\Services;

use App\Repositories\Interfaces\OwnerDashboardRepositoryInterface;

class OwnerDashboardService
{
    public function __construct(
        protected OwnerDashboardRepositoryInterface $repo,
    ) {}

    /**
     * Semua data untuk halaman owner dashboard.
     */
    public function getDashboardData(int $chartDays = 7, ?string $date = null): array
    {
        $date ??= today()->toDateString();

        return [
            // Stat cards harian
            'daily_revenue'           => $this->repo->getDailyRevenue($date),
            'daily_transaction_count' => $this->repo->getDailyTransactionCount($date),
            'daily_service_count'     => $this->repo->getDailyServiceOrderCount($date),
            'daily_top_products'      => $this->repo->getDailyTopProducts($date, 5),

            // Work order aktif
            'active_service_orders'   => $this->repo->getActiveServiceOrders(10),
            'service_order_summary'   => $this->repo->getServiceOrderSummary($date),

            // Stok
            'low_stock_products'      => $this->repo->getLowStockProducts(8),
            'low_stock_count'         => $this->repo->getLowStockCount(),

            // Grafik
            'revenue_chart'           => $this->repo->getRevenueChartData($chartDays),
            'service_chart'           => $this->repo->getServiceChartData($chartDays),
            'chart_days'              => $chartDays,

            // Meta
            'selected_date'           => $date,
        ];
    }

    /**
     * Hanya data grafik — dipanggil via AJAX saat owner ganti periode.
     */
    public function getChartData(int $days): array
    {
        return [
            'revenue' => $this->repo->getRevenueChartData($days),
            'service' => $this->repo->getServiceChartData($days),
        ];
    }

    /**
     * Laporan penjualan untuk rentang tanggal.
     */
    public function getSalesReport(string $dateFrom, string $dateTo): array
    {
        return $this->repo->getSalesReport($dateFrom, $dateTo);
    }
}
