<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockOutRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        protected StockMovementService $stockService
    ) {}

    // ── Web Views ─────────────────────────────────────────────────────────────

    /**
     * GET /stock — main stock overview page (datatable + summary cards).
     */
    public function index(Request $request)
    {
        $summary  = $this->stockService->getSummary();
        $products = $this->stockService->getProductOptions();

        return view('stock.index', compact('summary', 'products'));
    }

    /**
     * GET /stock/in — dedicated stock-in page (for complex bulk entries).
     */
    public function createIn()
    {
        $products = $this->stockService->getProductOptions();
        return view('stock.in', compact('products'));
    }

    /**
     * GET /stock/out — dedicated stock-out page.
     */
    public function createOut()
    {
        $products = $this->stockService->getProductOptions();
        return view('stock.out', compact('products'));
    }

    /**
     * GET /stock/{movement} — detail page for a single movement.
     */
    public function show(StockMovement $movement)
    {
        $movement->load(['product.category', 'product.supplier', 'user']);
        return view('stock.show', compact('movement'));
    }

    /**
     * GET /stock/product/{product} — full history for one product.
     */
    public function productHistory(Product $product, Request $request)
    {
        $movements = $this->stockService->getProductHistory(
            $product->id,
            (int) $request->get('per_page', 20)
        );
        $product->load(['category', 'supplier']);

        return view('stock.product_history', compact('product', 'movements'));
    }

    // ── API Endpoints ─────────────────────────────────────────────────────────

    /**
     * GET /api/stock — datatable data with filters.
     */
    public function apiList(Request $request): JsonResponse
    {
        $result = $this->stockService->getPaginatedApi($request->all());

        // Transform for the frontend
        $result['data'] = collect($result['data'])->map(fn ($m) => [
            'id'           => $m->id,
            'type'         => $m->type,
            'type_label'   => $m->typeLabelAttribute,
            'type_class'   => $m->typeBadgeClassAttribute,
            'quantity'     => $m->quantity,
            'stock_before' => $m->stock_before,
            'stock_after'  => $m->stock_after,
            'notes'        => $m->notes,
            'created_at'   => $m->created_at->toISOString(),
            'created_at_human' => $m->created_at->diffForHumans(),
            'created_at_formatted' => $m->created_at->format('d M Y, H:i'),
            'product' => $m->product ? [
                'id'       => $m->product->id,
                'name'     => $m->product->name,
                'sku'      => $m->product->sku,
                'stock'    => $m->product->stock,
                'category' => $m->product->category?->name,
            ] : null,
            'user' => [
                'id'   => $m->user?->id,
                'name' => $m->user?->name ?? 'Deleted User',
            ],
        ])->values()->all();

        return response()->json($result);
    }

    /**
     * GET /api/stock/{movement} — single movement detail (for modal).
     */
    public function apiShow(StockMovement $movement): JsonResponse
    {
        $movement->load(['product.category', 'user']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                   => $movement->id,
                'type'                 => $movement->type,
                'type_label'           => $movement->typeLabelAttribute,
                'type_class'           => $movement->typeBadgeClassAttribute,
                'quantity'             => $movement->quantity,
                'stock_before'         => $movement->stock_before,
                'stock_after'          => $movement->stock_after,
                'notes'                => $movement->notes,
                'created_at_formatted' => $movement->created_at->format('d M Y, H:i:s'),
                'product'              => [
                    'id'       => $movement->product->id,
                    'name'     => $movement->product->name,
                    'sku'      => $movement->product->sku,
                    'category' => $movement->product->category?->name,
                ],
                'user' => [
                    'name'  => $movement->user?->name ?? 'Deleted User',
                    'email' => $movement->user?->email ?? '—',
                ],
            ],
        ]);
    }

    /**
     * POST /api/stock/in — process stock in via API (modal form).
     */
    public function storeIn(StockInRequest $request): JsonResponse
    {
        try {
            $movement = $this->stockService->processStockIn($request->validated());

            return response()->json([
                'success'  => true,
                'message'  => "Stok masuk berhasil dicatat. Stok sekarang: {$movement->stock_after}.",
                'data'     => [
                    'movement_id' => $movement->id,
                    'stock_after' => $movement->stock_after,
                    'product'     => $movement->product->name,
                ],
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => ['quantity' => [$e->getMessage()]],
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    /**
     * POST /api/stock/out — process stock out via API (modal form).
     */
    public function storeOut(StockOutRequest $request): JsonResponse
    {
        try {
            $movement = $this->stockService->processStockOut($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Stok keluar berhasil dicatat. Stok tersisa: {$movement->stock_after}.",
                'data'    => [
                    'movement_id' => $movement->id,
                    'stock_after' => $movement->stock_after,
                    'product'     => $movement->product->name,
                ],
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => ['quantity' => [$e->getMessage()]],
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    /**
     * GET /api/stock/products — product list with current stock for select dropdown.
     */
    public function apiProducts(): JsonResponse
    {
        $products = $this->stockService->getProductOptions()
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'sku'       => $p->sku,
                'stock'     => $p->stock,
                'min_stock' => $p->min_stock,
                'category'  => $p->category?->name,
                'is_low'    => $p->stock <= $p->min_stock,
                'label'     => "[{$p->sku}] {$p->name} — Stok: {$p->stock}",
            ]);

        return response()->json(['success' => true, 'data' => $products]);
    }
}