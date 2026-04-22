<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * Get paginated categories with optional search.
     */
    public function paginate(int $perPage = 10, ?string $search = null): LengthAwarePaginator;

    /**
     * Create a new category.
     */
    public function create(array $data): Category;

    /**
     * Find a category by ID.
     */
    public function find(int $id): ?Category;

    /**
     * Update a category.
     */
    public function update(int $id, array $data): Category;

    /**
     * Delete a category.
     */
    public function delete(int $id): bool;
}
