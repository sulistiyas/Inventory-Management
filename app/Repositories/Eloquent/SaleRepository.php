<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;

class SaleRepository
{
    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    public function findById(int $id): Sale
    {
        return Sale::with([
            'items.product:id,name,sku',
            'payments.paymentMethod:id,name,type',
            'customer:id,name,phone,vehicle_plate',
            'cashier:id,name',
            'serviceOrder:id,order_no,vehicle_plate',
        ])->findOrFail($id);
    }

    public function getAllWithRelation(
        int     $perPage    = 15,
        int     $page       = 1,
        string  $search     = '',
        ?string $status     = null,
        ?string $dateFrom   = null,
        ?string $dateTo     = null,
        ?int    $customerId = null,
    ): array {
        $query = Sale::with([
                'items:id,sale_id,product_id,qty,price,subtotal',
                'payments.paymentMethod:id,name,type',
                'customer:id,name,phone,vehicle_plate',
                'cashier:id,name',
            ])
            ->when($search, fn ($q) =>
                $q->where('invoice_no', 'ILIKE', "%$search%")
                  ->orWhereHas('customer', fn ($c) =>
                      $c->where('name', 'ILIKE', "%$search%")
                        ->orWhere('phone', 'ILIKE', "%$search%")
                  )
            )
            ->when($status,     fn ($q) => $q->where('status', $status))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($dateFrom,   fn ($q) => $q->whereDate('sold_at', '>=', $dateFrom))
            ->when($dateTo,     fn ($q) => $q->whereDate('sold_at', '<=', $dateTo))
            ->latest('sold_at');

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

    public function getDailySummary(?string $date = null): array
    {
        $date ??= today()->toDateString();

        $base = Sale::paid()->whereDate('sold_at', $date);

        return [
            'total_revenue'      => (float) (clone $base)->sum('grand_total'),
            'total_transactions' => (int)   (clone $base)->count(),
            'total_discount'     => (float) (clone $base)->sum('discount'),
        ];
    }
}
