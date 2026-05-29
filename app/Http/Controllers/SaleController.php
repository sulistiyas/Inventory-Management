<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function index(Request $request)
    {
        $paymentMethods = PaymentMethod::active()->orderBy('name')->get(['id', 'name', 'type']);
        return view('sales.index', compact('paymentMethods'));
    }

    public function list(Request $request)
    {
        $result = $this->saleService->list($request);

        return response()->json([
            'success' => true,
            'data'    => collect($result['data'])->map(fn ($sale) => [
                'id'          => $sale->id,
                'invoice_no'  => $sale->invoice_no,
                'customer'    => $sale->customer?->name ?? 'Umum',
                'cashier'     => $sale->cashier->name,
                'grand_total' => (float) $sale->grand_total,
                'status'      => $sale->status,
                'items_count' => $sale->items->count(),
                'sold_at'     => optional($sale->sold_at)->format('d M Y, H:i'),
            ])->values(),
            'meta' => $result['meta'],
        ]);
    }

    public function show($id)
    {
        $sale = $this->saleService->detail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $sale->id,
                'invoice_no'    => $sale->invoice_no,
                'customer'      => $sale->customer,
                'cashier'       => $sale->cashier->name,
                'service_order' => $sale->serviceOrder,
                'items'         => $sale->items->map(fn ($item) => [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name ?? '-',
                    'product_sku'  => $item->product->sku  ?? '-',
                    'qty'          => $item->qty,
                    'price'        => (float) $item->price,
                    'subtotal'     => (float) $item->subtotal,
                ]),
                'payments'      => $sale->payments->map(fn ($p) => [
                    'method'        => $p->paymentMethod->name ?? '-',
                    'amount'        => (float) $p->amount,
                    'change_amount' => (float) $p->change_amount,
                ]),
                'subtotal'      => (float) $sale->subtotal,
                'discount'      => (float) $sale->discount,
                'tax'           => (float) $sale->tax,
                'grand_total'   => (float) $sale->grand_total,
                'status'        => $sale->status,
                'notes'         => $sale->notes,
                'sold_at'       => optional($sale->sold_at)->format('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * Produk aktif untuk dropdown kasir — support search live.
     */
    public function apiProducts(Request $request)
    {
        $search = $request->get('search', '');

        $products = Product::active()
            ->when($search, fn ($q) =>
                $q->where('name', 'ILIKE', "%$search%")
                  ->orWhere('sku', 'ILIKE', "%$search%")
            )
            ->with('category:id,name')
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'sku', 'price', 'stock', 'category_id']);

        return response()->json([
            'success' => true,
            'data'    => $products->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'sku'      => $p->sku,
                'price'    => (float) $p->price,
                'stock'    => $p->stock,
                'category' => $p->category?->name,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'                  => 'nullable|exists:customers,id',
            'service_order_id'             => 'nullable|exists:service_orders,id',
            'items'                        => 'required|array|min:1',
            'items.*.product_id'           => 'required|integer|exists:products,id',
            'items.*.qty'                  => 'required|integer|min:1',
            'payments'                     => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|integer|exists:payment_methods,id',
            'payments.*.amount'            => 'required|numeric|min:0.01',
            'discount'                     => 'nullable|numeric|min:0',
            'tax'                          => 'nullable|numeric|min:0',
            'notes'                        => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->saleService->processSale($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'data'    => [
                    'id'          => $sale->id,
                    'invoice_no'  => $sale->invoice_no,
                    'grand_total' => (float) $sale->grand_total,
                    'status'      => $sale->status,
                ],
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel($id)
    {
        try {
            $sale = $this->saleService->cancelSale($id);

            return response()->json([
                'success' => true,
                'message' => "Invoice {$sale->invoice_no} berhasil dibatalkan.",
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function dailySummary(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        return response()->json([
            'success' => true,
            'data'    => $this->saleService->getDailySummary($date),
        ]);
    }
}
