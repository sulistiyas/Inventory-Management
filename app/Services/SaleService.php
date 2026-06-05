<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Repositories\Eloquent\SaleRepository;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected SaleRepository       $repo,
        protected StockMovementService $stockMovementService,
    ) {}

    // =========================================================================
    // READ
    // =========================================================================

    public function list($request)
    {
        return $this->repo->getAllWithRelation(
            perPage:    (int)    ($request->perPage    ?? $request->per_page ?? 15),
            page:       (int)    ($request->page       ?? 1),
            search:     (string) ($request->search     ?? ''),
            status:               $request->status     ?? null,
            dateFrom:             $request->date_from  ?? null,
            dateTo:               $request->date_to    ?? null,
            customerId: $request->customer_id ? (int) $request->customer_id : null,
        );
    }

    public function detail(int $id): Sale
    {
        return $this->repo->findById($id);
    }

    public function getDailySummary(?string $date = null): array
    {
        return $this->repo->getDailySummary($date);
    }

    // =========================================================================
    // WRITE
    // =========================================================================

    /**
     * Proses transaksi penjualan dalam satu DB transaction:
     * 1. Lock & validasi stok semua produk sekaligus
     * 2. Buat Sale (draft)
     * 3. Buat SaleItems dengan snapshot harga
     * 4. Kurangi stok + catat StockMovement via StockMovementService
     * 5. Catat SalePayments
     * 6. Update status → paid
     */
    public function processSale(array $data): Sale
    {
        \Log::info('processSale start', ['items' => $data['items']]);
        return DB::transaction(function () use ($data) {
            \Log::info('inside transaction - locking products');
            // 1. Lock semua produk sekaligus (hindari deadlock N lock)
            $productIds = collect($data['items'])->pluck('product_id')->unique()->values()->toArray();
            $products   = Product::lockForUpdate()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');
            \Log::info('products locked', ['count' => $products->count()]);
            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id'])
                    ?? throw new \DomainException("Produk ID {$item['product_id']} tidak ditemukan.");

                if (! $product->is_active) {
                    throw new \DomainException("Produk \"{$product->name}\" tidak aktif.");
                }

                if ($product->stock < $item['qty']) {
                    throw new \DomainException(
                        "Stok \"{$product->name}\" tidak cukup. Tersedia: {$product->stock}, diminta: {$item['qty']}."
                    );
                }
            }

            // 2. Hitung subtotal & grand_total
            $subtotal      = 0.0;
            $itemsToInsert = [];

            foreach ($data['items'] as $item) {
                $product   = $products->get($item['product_id']);
                $lineTotal = (float) $product->price * $item['qty'];
                $subtotal += $lineTotal;

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'qty'        => $item['qty'],
                    'price'      => (float) $product->price,
                    'cost_price' => $product->cost_price ? (float) $product->cost_price : null,
                    'subtotal'   => $lineTotal,
                ];
            }

            $discount   = (float) ($data['discount'] ?? 0);
            $tax        = (float) ($data['tax'] ?? 0);
            $grandTotal = $subtotal - $discount + $tax;

            // 3. Buat Sale (status draft dulu)
            $sale = $this->repo->create([
                'invoice_no'       => Sale::generateInvoiceNo(),
                'user_id'          => Auth::id(),
                'customer_id'      => $data['customer_id'] ?? null,
                'service_order_id' => $data['service_order_id'] ?? null,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'tax'              => $tax,
                'grand_total'      => $grandTotal,
                'status'           => 'draft',
                'notes'            => $data['notes'] ?? null,
                'sold_at'          => now(),
            ]);

            // 4. Insert items + kurangi stok
            foreach ($itemsToInsert as $item) {
                $sale->items()->create($item);

                // Reuse StockMovementService agar audit log konsisten
                $this->stockMovementService->stockOut(
                    productId: $item['product_id'],
                    qty:       $item['qty'],
                    notes:     "Terjual via invoice {$sale->invoice_no}",
                );
            }

            // 5. Catat pembayaran
            foreach ($data['payments'] as $payment) {
                $sale->payments()->create([
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount'            => (float) $payment['amount'],
                    'change_amount'     => max(0, (float) $payment['amount'] - $grandTotal),
                ]);
            }

            // 6. Tandai paid
            $sale->update(['status' => 'paid']);

            return $sale->load([
                'items.product:id,name,sku',
                'payments.paymentMethod:id,name,type',
                'customer:id,name,phone',
                'cashier:id,name',
            ]);
        });
    }

    /**
     * Batalkan transaksi — kembalikan stok semua item.
     * Hanya admin yang boleh cancel.
     */
    public function cancelSale(int $id): Sale
    {
        if (! Auth::user()->isAdmin()) {
            throw new \DomainException('Hanya admin yang dapat membatalkan transaksi.');
        }

        return DB::transaction(function () use ($id) {
            $sale = $this->repo->findById($id);

            if ($sale->status === 'cancelled') {
                throw new \DomainException('Transaksi sudah dibatalkan sebelumnya.');
            }

            if ($sale->status !== 'paid') {
                throw new \DomainException('Hanya transaksi berstatus paid yang dapat dibatalkan.');
            }

            foreach ($sale->items as $item) {
                $this->stockMovementService->stockIn(
                    productId: $item->product_id,
                    qty:       $item->qty,
                    notes:     "Retur pembatalan invoice {$sale->invoice_no}",
                );
            }

            $sale->update(['status' => 'cancelled']);

            return $sale->fresh();
        });
    }
}
