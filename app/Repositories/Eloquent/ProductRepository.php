<?php

namespace App\Repositories\Eloquent;
use App\Models\Product;

class ProductRepository
{

    public function getAll($request){
        $query = Product::with(['category', 'supplier']);

        // Search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%$search%")
                  ->orWhere('sku', 'ILIKE', "%$search%")
                  ->orWhereHas('category', fn($c) => 
                        $c->where('name', 'ILIKE', "%$search%"))
                  ->orWhereHas('supplier', fn($s) => 
                        $s->where('name', 'ILIKE', "%$search%"));
            });
        }

        return $query->latest()->paginate($request->per_page ?? 10);
    }
    public function findById($id)
    {
        return Product::with(['category', 'supplier'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function delete($id)
    {
        return Product::destroy($id);
    }
}