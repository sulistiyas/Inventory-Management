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

    // ⚠️ HARUS tetap nama ini karena dipanggil service
    public function getAllWithRelation(
        int $perPage = 10,
        int $page = 1,
        string $search = '',
    ): array {
        $query = StockMovement::withRelations()
            ->when($search, function ($q) use ($search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->latest('created_at');

        /** @var LengthAwarePaginator $paginator */
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
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock']);
    }
}