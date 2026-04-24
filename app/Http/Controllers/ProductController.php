<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        return view('products.index');
    }

    public function list(Request $request)
    {
        $data = $this->productService->list($request);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $data = $this->productService->detail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'sku' => 'required|unique:products',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_stock' => 'nullable|numeric',
        ]);

        $product = $this->productService->store($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'sku' => "required|unique:products,sku,$id",
            'category_id' => 'required',
            'supplier_id' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_stock' => 'nullable|numeric',
        ]);

        $product = $this->productService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $this->productService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted'
        ]);
    }
}