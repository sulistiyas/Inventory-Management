<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Repositories\Eloquent\ServiceOrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrderService
{
    public function __construct(
        protected ServiceOrderRepository $repo,
        protected StockMovementService   $stockMovementService,
    ) {}

    // =========================================================================
    // READ
    // =========================================================================

    public function list($request)
    {
        return $this->repo->getAllWithRelation(
            perPage:  (int)    ($request->perPage   ?? $request->per_page ?? 15),
            page:     (int)    ($request->page      ?? 1),
            search:   (string) ($request->search    ?? ''),
            status:             $request->status    ?? null,
            dateFrom:           $request->date_from ?? null,
            dateTo:             $request->date_to   ?? null,
        );
    }

    public function detail(int $id): ServiceOrder
    {
        return $this->repo->findById($id);
    }

    // =========================================================================
    // WRITE
    // =========================================================================

    /**
     * Buat work order baru.
     * Stok belum dikurangi — dikurangi nanti saat status → done.
     */
    public function store(array $data): ServiceOrder
    {
        return DB::transaction(function () use ($data) {
            $order = $this->repo->create([
                'order_no'      => ServiceOrder::generateOrderNo(),
                'customer_id'   => $data['customer_id'],
                'vehicle_plate' => strtoupper(trim($data['vehicle_plate'])),
                'vehicle_type'  => $data['vehicle_type'] ?? null,
                'complaint'     => $data['complaint'],
                'diagnosis'     => $data['diagnosis'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'service_fee'   => (float) ($data['service_fee'] ?? 0),
                'discount'      => (float) ($data['discount'] ?? 0),
                'status'        => 'pending',
                'handled_by'    => $data['handled_by'] ?? Auth::id(),
            ]);

            if (! empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'price'      => (float) $item['price'],
                        'subtotal'   => $item['qty'] * (float) $item['price'],
                    ]);
                }
            }

            return $order->load(['customer', 'items.product', 'handler']);
        });
    }

    /**
     * Update data work order (tidak bisa kalau sudah done/cancelled).
     * Items di-sync ulang jika dikirim.
     */
    public function update(int $id, array $data): ServiceOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->repo->findById($id);

            if (in_array($order->status, ['done', 'cancelled'])) {
                throw new \DomainException("Work order berstatus {$order->status} tidak dapat diubah.");
            }

            $order->update([
                'vehicle_plate' => isset($data['vehicle_plate'])
                    ? strtoupper(trim($data['vehicle_plate']))
                    : $order->vehicle_plate,
                'vehicle_type'  => $data['vehicle_type'] ?? $order->vehicle_type,
                'complaint'     => $data['complaint']    ?? $order->complaint,
                'diagnosis'     => $data['diagnosis']    ?? $order->diagnosis,
                'notes'         => $data['notes']        ?? $order->notes,
                'service_fee'   => isset($data['service_fee']) ? (float) $data['service_fee'] : $order->service_fee,
                'discount'      => isset($data['discount'])    ? (float) $data['discount']    : $order->discount,
                'handled_by'    => $data['handled_by']   ?? $order->handled_by,
            ]);

            if (isset($data['items'])) {
                $order->items()->delete();
                foreach ($data['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'price'      => (float) $item['price'],
                        'subtotal'   => $item['qty'] * (float) $item['price'],
                    ]);
                }
            }

            return $order->fresh(['customer', 'items.product', 'handler']);
        });
    }

    /**
     * Update status work order dengan validasi transisi.
     * Saat → done: kurangi stok spare part via StockMovementService.
     */
    public function updateStatus(int $id, string $newStatus): ServiceOrder
    {
        return DB::transaction(function () use ($id, $newStatus) {
            $order = $this->repo->findById($id);

            $this->validateStatusTransition($order->status, $newStatus);

            if ($newStatus === 'done') {
                foreach ($order->items as $item) {
                    $this->stockMovementService->stockOut(
                        productId: $item->product_id,
                        qty:       $item->qty,
                        notes:     "Dipakai untuk work order {$order->order_no}",
                    );
                }
                $order->update(['status' => 'done', 'finished_at' => now()]);
            } else {
                $order->update(['status' => $newStatus]);
            }

            return $order->fresh(['customer', 'handler']);
        });
    }

    // =========================================================================
    // PRIVATE
    // =========================================================================

    private function validateStatusTransition(string $from, string $to): void
    {
        $transitions = [
            'pending'     => ['in_progress', 'cancelled'],
            'in_progress' => ['done', 'cancelled'],
            'done'        => [],
            'cancelled'   => [],
        ];

        if (! in_array($to, $transitions[$from] ?? [])) {
            throw new \DomainException(
                "Tidak bisa mengubah status dari '{$from}' ke '{$to}'."
            );
        }
    }
}
