<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;

class CategoryRepository
{
    public function getAll($filters = [], $perPage = 10)
    {
        $query = Category::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')->orderBy('id','asc');
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->findById($id);
        $category->update($data);

        return $category;
    }

    public function delete($id)
    {
        $category = $this->findById($id);
        return $category->delete();
    }
}