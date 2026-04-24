<?php 

namespace App\Services;
use App\Repositories\Eloquent\SupplierRepository;

class SupplierService{
    protected $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function list($request)
    {
        return $this->supplierRepository->getAll($request);
    }

    public function detail($id)
    {
        return $this->supplierRepository->findById($id);
    }

    public function store($data)
    {
        return $this->supplierRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->supplierRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->supplierRepository->delete($id);
    }
}