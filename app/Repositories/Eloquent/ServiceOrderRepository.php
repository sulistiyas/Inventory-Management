<?php

namespace App\Repositories\Eloquent;

use App\Models\ServiceOrder;

class ServiceOrderRepository
{
    public function create(array $data): ServiceOrder
    {
        return ServiceOrder::create($data);
    }

    public function findById(int $id): ServiceOrder
    {
        return ServiceOrder::with([
            'customer:id,name,phone,vehicle_plate',
            'handler:id,name',
            'items.product:id,name,sku,price',
            'sale:id,invoice_no,grand_total,status',
        ])->findOrFail($id);
    }

    public function getAllWithRelation(
        int     $perPage  = 15,
        int     $page     = 1,
        string  $search   = '',
        ?string $status   = null,
        ?string $dateFrom = null,
        ?string $dateTo   = null,
    ): array {
        $query = ServiceOrder::with([
                'customer:id,name,phone,vehicle_plate',
                'handler:id,name',
                'items:id,service_order_id,product_id,qty,price,subtotal',
            ])
            ->when($search, fn ($q) =>
                $q->where('order_no', 'ILIKE', "%$search%")
                  ->orWhere('vehicle_plate', 'ILIKE', "%$search%")
                  ->orWhereHas('customer', fn ($c) =>
                      $c->where('name', 'ILIKE', "%$search%")
                        ->orWhere('phone', 'ILIKE', "%$search%")
                  )
            )
            ->when($status,   fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
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
}
