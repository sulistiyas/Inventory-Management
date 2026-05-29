<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function getAll($request): LengthAwarePaginator
    {
        $query = Customer::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%$search%")
                  ->orWhere('phone', 'ILIKE', "%$search%")
                  ->orWhere('vehicle_plate', 'ILIKE', "%$search%");
            });
        }

        return $query->latest()->paginate($request->per_page ?? 10);
    }

    public function findById(int $id): Customer
    {
        return Customer::findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    public function delete(int $id): bool
    {
        return Customer::destroy($id) > 0;
    }
}
