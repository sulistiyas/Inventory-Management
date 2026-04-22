<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Get paginated categories with search — API endpoint.
     */
    public function list(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search');

        $categories = $this->categoryService->getPaginatedCategories($perPage, $search);

        return response()->json([
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }
}
