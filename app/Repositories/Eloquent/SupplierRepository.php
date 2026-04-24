<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;

class SupplierRepository{
    public function getAll($request){
        $query = Supplier::query();
        $query->orderBy('created_at', 'asc');

        // Search
        if ($search = $request->search) {
            $query->where('name', 'ILIKE', "%$search%");
        }

        return $query->latest()->paginate($request->per_page ?? 10);
    }

    public function findById($id)
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }

    public function update($id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return $supplier;
    }

    public function delete($id)
    {
        return Supplier::destroy($id);
    }
}