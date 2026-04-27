<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Eloquent\StockMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function __construct(
        protected StockMovementRepository $repo
    ) {}

    // =========================================================================
    // READ — dipanggil StockController
    // =========================================================================

    /**
     * Produk aktif untuk dropdown select di form stock.in / stock.out
     */
    public function getProductOptions(): Collection
    {
        return $this->repo->getActiveProducts();
    }

    /**
     * Summary cards untuk halaman stock.index
     */
    public function getSummary(): array
    {
        $today = today();

        return [
            'today_in'        => (int) StockMovement::stockIn()->whereDate('created_at', $today)->sum('quantity'),
            'today_out'       => (int) StockMovement::stockOut()->whereDate('created_at', $today)->sum('quantity'),
            'today_count'     => StockMovement::whereDate('created_at', $today)->count(),
            'total_in'        => (int) StockMovement::stockIn()->sum('quantity'),
            'total_out'       => (int) StockMovement::stockOut()->sum('quantity'),
            'low_stock_count' => Product::lowStock()->count(),
        ];
    }

    /**
     * Paginated list untuk datatable di stock.index
     * Pakai filter dari request (search, type, date_from, date_to, product_id)
     */
    public function getPaginatedApi(array $filters = []): array
    {
        return $this->repo->getAllWithRelation(
            perPage:   (int) ($filters['perPage'] ?? $filters['per_page'] ?? 15),
            page:      (int) ($filters['page'] ?? 1),
            search:    (string) ($filters['search'] ?? ''),
            type:      $filters['type']       ?? null,
            dateFrom:  $filters['date_from']  ?? null,
            dateTo:    $filters['date_to']    ?? null,
            productId: isset($filters['product_id']) ? (int) $filters['product_id'] : null,
        );
    }

    /**
     * Riwayat movement untuk satu produk (halaman product-history)
     */
    public function getProductHistory(int $productId, int $perPage = 20): LengthAwarePaginator
    {
        return StockMovement::with('user')
            ->where('product_id', $productId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    // =========================================================================
    // WRITE — dipanggil StockController::storeIn() & storeOut()
    // =========================================================================

    /**
     * Proses stock in — dipanggil dari storeIn() via StockInRequest
     */
    public function processStockIn(array $validated): StockMovement
    {
        return $this->stockIn(
            productId: (int) $validated['product_id'],
            qty:       (int) $validated['quantity'],
            notes:     $validated['notes'] ?? null,
        );
    }

    /**
     * Proses stock out — dipanggil dari storeOut() via StockOutRequest
     */
    public function processStockOut(array $validated): StockMovement
    {
        return $this->stockOut(
            productId: (int) $validated['product_id'],
            qty:       (int) $validated['quantity'],
            notes:     $validated['notes'] ?? null,
        );
    }

    // =========================================================================
    // INTERNAL — core business logic dengan DB transaction
    // =========================================================================

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
}