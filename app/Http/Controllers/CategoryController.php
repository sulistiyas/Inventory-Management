<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('categories.index');
    }

    public function list(Request $request)
    {
        $data = $this->service->list($request);

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

    public function store(Request $request)
    {
        $request->merge([
            'name' => strtolower(trim($request->name))
        ]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name',
            'description' => 'nullable|string'
        ], [
            'name.unique' => 'Nama category sudah digunakan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        $category = $this->service->store($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category created',
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                ->where(fn ($q) => $q->whereRaw('LOWER(name) = ?', [strtolower($request->name)]))
                ->ignore($id)
            ],
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        $category = $this->service->update($id, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category updated',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted'
        ]);
    }
}