<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get paginated categories with search.
     */
    public function getPaginatedCategories(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage, $search);
    }
}
