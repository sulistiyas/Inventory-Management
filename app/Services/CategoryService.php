<?php

namespace App\Services;

use App\Repositories\Eloquent\CategoryRepository;

class CategoryService
{
    protected $repo;

    public function __construct(CategoryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list($request)
    {
        return $this->repo->getAll([
            'search' => $request->search
        ], $request->perPage ?? 10);
    }

    public function store($data)
    {
        return $this->repo->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update($id, $data)
    {
        return $this->repo->update($id, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}