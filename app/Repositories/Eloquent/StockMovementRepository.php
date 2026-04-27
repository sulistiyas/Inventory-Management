<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockMovementRepository
{
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    /**
     * Paginated list dengan filter lengkap.
     * Dipanggil dari StockMovementService::getPaginatedApi()
     */
    public function getAllWithRelation(
        int     $perPage   = 15,
        int     $page      = 1,
        string  $search    = '',
        ?string $type      = null,
        ?string $dateFrom  = null,
        ?string $dateTo    = null,
        ?int    $productId = null,
    ): array {
        $query = StockMovement::with([
                    'product:id,name,sku,category_id',
                    'product.category:id,name',
                    'user:id,name'
                ])
            ->when($search, fn ($q) =>
                $q->whereHas('product', fn ($p) =>
                    $p->where('name', 'ilike', "%{$search}%")
                      ->orWhere('sku', 'ilike', "%{$search}%")
                )
            )
            ->when($type,      fn ($q) => $q->where('type', $type))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($dateFrom,  fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,    fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }

    public function getActiveProducts(): Collection
    {
        return Product::active()
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock', 'min_stock', 'category_id']);
    }
}