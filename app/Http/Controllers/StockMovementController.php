<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Eloquent\StockMovementRepository;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function __construct(
        protected StockMovementRepository $repository,
        protected StockMovementService    $service,
    ) {}

    public function index(Request $request): mixed
    {
        if ($request->expectsJson()) {
            $perPage = (int) $request->get('perPage', 10);
            $search  = $request->get('search', '');

            $paginator = $this->repository->getAllWithRelation($perPage, $search);

            return response()->json([
                'data' => $paginator->map(fn (StockMovement $m) => [
                    'id'           => $m->id,
                    'product_name' => $m->product->name ?? '-',
                    'product_sku'  => $m->product->sku  ?? '-',  // ← ada di scope withRelations
                    'user_name'    => $m->user->name,             // withDefault sudah handle deleted user
                    'type'         => $m->type,
                    'type_label'   => $m->type_label,             // ← pakai accessor
                    'quantity'     => $m->quantity,
                    'stock_before' => $m->stock_before,
                    'stock_after'  => $m->stock_after,
                    'notes'        => $m->notes,
                    'created_at'   => $m->created_at->format('d M Y, H:i'),
                ]),
                'meta' => [
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'page'      => $paginator->currentPage(),
                ],
            ]);
        }
        $isAdmin = Auth::user()->role === 'admin';

        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'stock']);

        return view('stockmovement.index', compact('products', 'isAdmin'));
    }

    public function store(Request $request): JsonResponse
    {
        $user          = Auth::user();
        $allowedTypes  = $user->role === 'admin'
            ? array_keys(StockMovement::TYPES)               // semua type
            : StockMovement::STAFF_ALLOWED_TYPES;            // ← pakai constant

        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:' . implode(',', $allowedTypes)],
            'quantity'   => ['required_unless:type,adjustment', 'nullable', 'integer', 'min:1'],
            'new_stock'  => ['required_if:type,adjustment',     'nullable', 'integer', 'min:0'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        try {
            match ($request->type) {
                StockMovement::TYPE_IN         => $this->service->stockIn($request->product_id, $request->quantity, $request->notes),
                StockMovement::TYPE_OUT        => $this->service->stockOut($request->product_id, $request->quantity, $request->notes),
                StockMovement::TYPE_ADJUSTMENT => $this->service->adjustStock($request->product_id, $request->new_stock, $request->notes),
            };

            return response()->json(['message' => 'Transaksi berhasil disimpan.']);

        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}