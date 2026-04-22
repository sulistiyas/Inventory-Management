<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * Get paginated categories with optional search.
     */
    public function paginate(int $perPage = 10, ?string $search = null): LengthAwarePaginator;
}
