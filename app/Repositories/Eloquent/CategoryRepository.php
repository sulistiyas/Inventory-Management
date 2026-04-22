<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'asc')
            ->paginate($perPage);
    }
}
