<?php

namespace App\Services;

use App\Repositories\Eloquent\ProductRepository;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function list($request)
    {
        return $this->productRepository->getAll($request);
    }

    public function detail($id)
    {
        return $this->productRepository->findById($id);
    }

    public function store($data)
    {
        return $this->productRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->productRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->productRepository->delete($id);
    }
}