<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Sale;
use App\Models\ServiceOrder;
use App\Repositories\Interfaces\OwnerDashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OwnerDashboardRepository implements OwnerDashboardRepositoryInterface
{
    // =========================================================================
    // HARIAN
    // =========================================================================

    public function getDailyRevenue(?string $date = null): float
    {
        $date ??= today()->toDateString();
        return (float) Sale::paid()->whereDate('sold_at', $date)->sum('grand_total');
    }

    public function getDailyTransactionCount(?string $date = null): int
    {
        $date ??= today()->toDateString();
        return Sale::paid()->whereDate('sold_at', $date)->count();
    }

    public function getDailyServiceOrderCount(?string $date = null): int
    {
        return ServiceOrder::whereIn('status', ['pending', 'in_progress'])->count();
    }

    /**
     * Top produk terlaris hari ini berdasarkan qty terjual.
     */
    public function getDailyTopProducts(?string $date = null, int $limit = 5): Collection
    {
        $date ??= today()->toDateString();

        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'paid')
            ->whereDate('sales.sold_at', $date)
            ->groupBy('sale_items.product_id', 'products.name', 'products.sku')
            ->select(
                'sale_items.product_id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.qty) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            )
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    // =========================================================================
    // GRAFIK
    // =========================================================================

    /**
     * Omzet harian untuk N hari terakhir.
     * Shape: ['labels' => [...], 'revenue' => [...], 'transactions' => [...]]
     */
    public function getRevenueChartData(int $days = 7): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = Sale::paid()
            ->where('sold_at', '>=', $from)
            ->select(
                DB::raw("DATE(sold_at) AS date"),
                DB::raw("SUM(grand_total) AS total_revenue"),
                DB::raw("COUNT(*) AS total_transactions"),
            )
            ->groupBy(DB::raw("DATE(sold_at)"))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels       = [];
        $revenue      = [];
        $transactions = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            $labels[]       = now()->subDays($i)->format('d M');
            $revenue[]      = (float) ($rows[$date]->total_revenue      ?? 0);
            $transactions[] = (int)   ($rows[$date]->total_transactions ?? 0);
        }

        return compact('labels', 'revenue', 'transactions');
    }

    /**
     * Work order selesai vs total per hari untuk N hari terakhir.
     * Shape: ['labels' => [...], 'done' => [...], 'total' => [...]]
     */
    public function getServiceChartData(int $days = 7): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = ServiceOrder::where('created_at', '>=', $from)
            ->select(
                DB::raw("DATE(created_at) AS date"),
                DB::raw("COUNT(*) AS total"),
                DB::raw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done"),
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $done   = [];
        $total  = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            $labels[] = now()->subDays($i)->format('d M');
            $done[]   = (int) ($rows[$date]->done  ?? 0);
            $total[]  = (int) ($rows[$date]->total ?? 0);
        }

        return compact('labels', 'done', 'total');
    }

    // =========================================================================
    // WORK ORDER
    // =========================================================================

    public function getActiveServiceOrders(int $limit = 10): Collection
    {
        return ServiceOrder::with(['customer:id,name,phone,vehicle_plate', 'handler:id,name'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getServiceOrderSummary(?string $date = null): array
    {
        $date ??= today()->toDateString();

        $statuses = ServiceOrder::whereDate('created_at', $date)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'pending'     => (int) ($statuses['pending']     ?? 0),
            'in_progress' => (int) ($statuses['in_progress'] ?? 0),
            'done'        => (int) ($statuses['done']        ?? 0),
            'cancelled'   => (int) ($statuses['cancelled']   ?? 0),
            'total'       => (int) $statuses->sum(),
        ];
    }

    // =========================================================================
    // STOK
    // =========================================================================

    public function getLowStockProducts(int $limit = 10): Collection
    {
        return Product::with('category:id,name')
            ->lowStock()
            ->orderBy('stock')
            ->limit($limit)
            ->get(['id', 'name', 'sku', 'stock', 'min_stock', 'category_id']);
    }

    public function getLowStockCount(): int
    {
        return Product::lowStock()->count();
    }

    // =========================================================================
    // LAPORAN PENJUALAN
    // =========================================================================

    /**
     * Laporan penjualan untuk rentang tanggal tertentu.
     * Dipakai untuk halaman laporan owner.
     */
    public function getSalesReport(string $dateFrom, string $dateTo): array
    {
        $sales = Sale::paid()
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->with(['items.product:id,name,sku', 'payments.paymentMethod:id,name,type', 'customer:id,name', 'cashier:id,name'])
            ->latest('sold_at')
            ->get();

        // Summary per metode pembayaran
        $byPaymentMethod = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->join('payment_methods', 'payment_methods.id', '=', 'sale_payments.payment_method_id')
            ->where('sales.status', 'paid')
            ->whereDate('sales.sold_at', '>=', $dateFrom)
            ->whereDate('sales.sold_at', '<=', $dateTo)
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->select('payment_methods.name', DB::raw('SUM(sale_payments.amount) as total'))
            ->get();

        // Top produk periode ini
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'paid')
            ->whereDate('sales.sold_at', '>=', $dateFrom)
            ->whereDate('sales.sold_at', '<=', $dateTo)
            ->groupBy('sale_items.product_id', 'products.name', 'products.sku')
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.qty) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            )
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return [
            'sales'            => $sales,
            'total_revenue'    => (float) $sales->sum('grand_total'),
            'total_discount'   => (float) $sales->sum('discount'),
            'total_tax'        => (float) $sales->sum('tax'),
            'total_count'      => $sales->count(),
            'by_payment_method'=> $byPaymentMethod,
            'top_products'     => $topProducts,
        ];
    }
}
