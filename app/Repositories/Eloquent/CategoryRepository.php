<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;

class CategoryRepository
{
    public function getAll($request = [])
    {
        $query = Category::query();
        $query->orderBy('id','asc');

        if (!empty($request['search'])) {
            $query->where('name', 'ILIKE', '%' . $request['search'] . '%')->orderBy('id','asc');
        }

        return $query->latest()->paginate($request['per_page'] ?? 10);
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