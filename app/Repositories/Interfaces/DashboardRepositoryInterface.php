<?php

namespace App\Repositories\Interfaces;

interface DashboardRepositoryInterface
{
    public function getTotalProducts(): int;
    public function getTotalStock(): int;
    public function getTotalSuppliers(): int;
    public function getTodayMovementsCount(): int;
    public function getLowStockCount(): int;
    public function getRecentMovements(int $limit = 5): \Illuminate\Support\Collection;
    public function getLowStockProducts(int $limit = 6): \Illuminate\Support\Collection;
    public function getStockChartData(int $days = 7): array;
}