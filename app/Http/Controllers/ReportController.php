<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Category;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ── Halaman filter report ──────────────────────────────────────────────────
    public function index()
    {
        $categories = Category::ordered()->get();
        $suppliers  = Supplier::active()->ordered()->get();
        return view('reports.index', compact('categories', 'suppliers'));
    }

    // ── Laporan Stok Barang (PDF) ──────────────────────────────────────────────
    public function stockSummary(Request $request)
    {
        $query = Product::withRelations()->active();

        if ($request->filled('category_id')) {
            $query->inCategory($request->category_id);
        }
        if ($request->filled('supplier_id')) {
            $query->fromSupplier($request->supplier_id);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'low'  => $query->lowStock(),
                'out'  => $query->outOfStock(),
                default => null,
            };
        }

        $products      = $query->orderBy('name')->get();
        $totalValue    = $products->sum(fn($p) => $p->inventory_value);
        $totalProducts = $products->count();
        $lowStockCount = $products->filter(fn($p) => $p->is_low_stock)->count();

        $pdf = Pdf::loadView('reports.stock-summary', compact(
            'products', 'totalValue', 'totalProducts', 'lowStockCount', 'request'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-' . now()->format('Ymd') . '.pdf');
    }

    // ── Laporan Mutasi Stok (PDF) ──────────────────────────────────────────────
    public function stockMovement(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $movements = StockMovement::withRelations()
            ->dateBetween($request->date_from, $request->date_to)
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->latest('created_at')
            ->get();

        $totalIn  = $movements->where('type', StockMovement::TYPE_IN)->sum('quantity');
        $totalOut = $movements->where('type', StockMovement::TYPE_OUT)->sum('quantity');

        $pdf = Pdf::loadView('reports.stock-movement', compact(
            'movements', 'totalIn', 'totalOut', 'request'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-mutasi-' . now()->format('Ymd') . '.pdf');
    }
}