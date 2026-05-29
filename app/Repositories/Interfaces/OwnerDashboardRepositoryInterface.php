<?php

namespace App\Repositories\Interfaces;

interface OwnerDashboardRepositoryInterface
{
    // ── Harian ────────────────────────────────────────────────────────────────
    public function getDailyRevenue(?string $date = null): float;
    public function getDailyTransactionCount(?string $date = null): int;
    public function getDailyServiceOrderCount(?string $date = null): int;
    public function getDailyTopProducts(?string $date = null, int $limit = 5): \Illuminate\Support\Collection;

    // ── Grafik ────────────────────────────────────────────────────────────────
    public function getRevenueChartData(int $days = 7): array;
    public function getServiceChartData(int $days = 7): array;

    // ── Work Order ────────────────────────────────────────────────────────────
    public function getActiveServiceOrders(int $limit = 10): \Illuminate\Support\Collection;
    public function getServiceOrderSummary(?string $date = null): array;

    // ── Stok ─────────────────────────────────────────────────────────────────
    public function getLowStockProducts(int $limit = 10): \Illuminate\Support\Collection;
    public function getLowStockCount(): int;

    // ── Laporan penjualan ─────────────────────────────────────────────────────
    public function getSalesReport(string $dateFrom, string $dateTo): array;
}
