<?php

namespace App\Services;

use App\Models\Category;
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

    /**
     * Create a new category.
     */
    public function createCategory(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Get a category by ID.
     */
    public function getCategory(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * Update a category.
     */
    public function updateCategory(int $id, array $data): Category
    {
        return $this->categoryRepository->update($id, $data);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
