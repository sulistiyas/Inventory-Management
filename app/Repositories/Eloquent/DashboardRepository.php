<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalProducts(): int
    {
        return Product::active()->count();
    }

    public function getTotalStock(): int
    {
        return (int) Product::active()->sum('stock');
    }

    public function getTotalSuppliers(): int
    {
        return Supplier::active()->count();
    }

    public function getTodayMovementsCount(): int
    {
        return StockMovement::whereDate('created_at', today())->count();
    }

    public function getLowStockCount(): int
    {
        return Product::lowStock()->count();
    }

    public function getRecentMovements(int $limit = 5): \Illuminate\Support\Collection
    {
        return StockMovement::withRelations()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getLowStockProducts(int $limit = 6): \Illuminate\Support\Collection
    {
        return Product::with('category')
            ->lowStock()
            ->orderBy('stock')
            ->limit($limit)
            ->get();
    }

    /**
     * Returns per-day stock in/out totals for the last N days.
     * Result shape: ['labels' => [...], 'in' => [...], 'out' => [...]]
     */
    public function getStockChartData(int $days = 7): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = StockMovement::select(
                DB::raw("DATE(created_at) AS date"),
                DB::raw("type"),
                DB::raw("SUM(quantity) AS total")
            )
            ->where('created_at', '>=', $from)
            ->groupBy(DB::raw("DATE(created_at)"), 'type')
            ->orderBy('date')
            ->get();

        // Build a date-keyed lookup
        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row->date][$row->type] = (int) $row->total;
        }

        $labels = [];
        $inData  = [];
        $outData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date  = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d M');

            $labels[]  = $label;
            $inData[]  = $byDate[$date]['in']  ?? 0;
            $outData[] = $byDate[$date]['out'] ?? 0;
        }

        return [
            'labels' => $labels,
            'in'     => $inData,
            'out'    => $outData,
        ];
    }
}