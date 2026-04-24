<?php

namespace App\Http\Controllers;

use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request)
    {
        return view('suppliers.index');
    }

    public function list(Request $request)
    {
        $data = $this->supplierService->list($request);

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
        $data = $this->supplierService->detail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            // 'contact_person' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        $this->supplierService->store($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            // 'contact_person' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        $this->supplierService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $this->supplierService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully'
        ]);
    }

}
