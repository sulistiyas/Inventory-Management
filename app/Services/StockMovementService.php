<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Eloquent\StockMovementRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function __construct(
        protected StockMovementRepository $repo
    ) {}

    // ── Read ──────────────────────────────────────────────────────────────────

    // Returns { data, meta } — sesuai datatable.js
    public function paginate(int $perPage, int $page, string $search): array
    {
        return $this->repo->getAllWithRelation($perPage, $page, $search);
    }

    public function getActiveProducts(): \Illuminate\Support\Collection
    {
        return $this->repo->getActiveProducts();
    }

    // ── Stock In ──────────────────────────────────────────────────────────────

    public function stockIn(int $productId, int $qty, ?string $notes = null): StockMovement
    {
        if ($qty <= 0) {
            throw new \DomainException('Jumlah harus lebih dari 0.');
        }

        return DB::transaction(function () use ($productId, $qty, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $before = $product->stock;
            $after  = $before + $qty;

            $product->update(['stock' => $after]);

            return $this->repo->create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => StockMovement::TYPE_IN,
                'quantity'     => $qty,
                'stock_before' => $before,
                'stock_after'  => $after,
                'notes'        => $notes,
            ]);
        });
    }

    // ── Stock Out ─────────────────────────────────────────────────────────────

    public function stockOut(int $productId, int $qty, ?string $notes = null): StockMovement
    {
        if ($qty <= 0) {
            throw new \DomainException('Jumlah harus lebih dari 0.');
        }

        return DB::transaction(function () use ($productId, $qty, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($product->stock < $qty) {
                throw new \DomainException(
                    "Stok tidak cukup. Tersedia: {$product->stock}, diminta: {$qty}."
                );
            }

            $before = $product->stock;
            $after  = $before - $qty;

            $product->update(['stock' => $after]);

            return $this->repo->create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => StockMovement::TYPE_OUT,
                'quantity'     => $qty,
                'stock_before' => $before,
                'stock_after'  => $after,
                'notes'        => $notes,
            ]);
        });
    }

    // ── Adjustment — admin only ────────────────────────────────────────────────

    public function adjustStock(int $productId, int $newStock, ?string $notes = null): StockMovement
    {
        if (! Auth::user()->isAdmin()) {
            throw new \DomainException('Hanya admin yang dapat melakukan adjustment.');
        }

        if ($newStock < 0) {
            throw new \DomainException('Stok tidak boleh negatif.');
        }

        return DB::transaction(function () use ($productId, $newStock, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $before = $product->stock;
            $diff   = abs($newStock - $before);

            $product->update(['stock' => $newStock]);

            return $this->repo->create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => StockMovement::TYPE_ADJUSTMENT,
                'quantity'     => $diff,
                'stock_before' => $before,
                'stock_after'  => $newStock,
                'notes'        => $notes,
            ]);
        });
    }

    // ── Summary (dashboard cards) ───────────────────────────────────────────────
    public function getSummary(): array
    {
        return [
            'today_in' => StockMovement::where('type', StockMovement::TYPE_IN)
            ->whereDate('created_at', today())
            ->sum('quantity'),

            'today_out' => StockMovement::where('type', StockMovement::TYPE_OUT)
                ->whereDate('created_at', today())
                ->sum('quantity'),

            'today_count' => StockMovement::whereDate('created_at', today())
                ->count(),

            // ── Low stock ─────────────────────────────
            'low_stock_count' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'total_products' => Product::count(),
            'total_stock'    => Product::sum('stock'),
            'low_stock'      => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'movements_today' => StockMovement::whereDate('created_at', now())->count(),
        ];
    }

    // ── Product dropdown options ────────────────────────────────────────────────
    public function getProductOptions()
    {
        return Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->get();
    }

    // ── Product history ─────────────────────────────────────────────────────────
    public function getProductHistory(int $productId, int $perPage = 20)
    {
        return StockMovement::with(['user'])
            ->where('product_id', $productId)
            ->latest()
            ->paginate($perPage);
    }

    // ── API datatable ───────────────────────────────────────────────────────────
    public function getPaginatedApi(array $filters): array
    {
        $perPage = $filters['per_page'] ?? 10;
        $page    = $filters['page'] ?? 1;
        $search  = $filters['search'] ?? '';

        return $this->repo->getAllWithRelation($perPage, $page, $search);
    }

    // ── Process Stock In (wrapper biar cocok controller) ────────────────────────
    public function processStockIn(array $data): StockMovement
    {
        return $this->stockIn(
            $data['product_id'],
            $data['quantity'],
            $data['notes'] ?? null
        );
    }

    // ── Process Stock Out (wrapper) ─────────────────────────────────────────────
    public function processStockOut(array $data): StockMovement
    {
        return $this->stockOut(
            $data['product_id'],
            $data['quantity'],
            $data['notes'] ?? null
        );
    }
}