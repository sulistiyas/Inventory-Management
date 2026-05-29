<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\CustomerRepository;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        return view('customers.index');
    }

    public function list(Request $request)
    {
        $data = $this->customerRepository->getAll($request);

        return response()->json([
            'success' => true,
            'data'    => collect($data->items())->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'phone'         => $c->phone,
                'vehicle_plate' => $c->vehicle_plate,
                'vehicle_type'  => $c->vehicle_type,
                'created_at'    => optional($c->created_at)->format('d M Y'),
            ])->values(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $customer      = $this->customerRepository->findById($id);
        $serviceOrders = $customer->serviceOrders()
            ->latest()
            ->limit(10)
            ->get(['id', 'order_no', 'vehicle_plate', 'complaint', 'status', 'service_fee', 'created_at']);
        $sales = $customer->sales()
            ->paid()
            ->latest('sold_at')
            ->limit(10)
            ->get(['id', 'invoice_no', 'grand_total', 'sold_at']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $customer->id,
                'name'           => $customer->name,
                'phone'          => $customer->phone,
                'vehicle_plate'  => $customer->vehicle_plate,
                'vehicle_type'   => $customer->vehicle_type,
                'address'        => $customer->address,
                'created_at'     => optional($customer->created_at)->format('d M Y'),
                'service_orders' => $serviceOrders,
                'sales'          => $sales,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'phone'         => 'nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_type'  => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
        ]);

        if (isset($validated['vehicle_plate'])) {
            $validated['vehicle_plate'] = strtoupper(trim($validated['vehicle_plate']));
        }

        $customer = $this->customerRepository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'data'    => $customer,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'phone'         => 'nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_type'  => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
        ]);

        if (isset($validated['vehicle_plate'])) {
            $validated['vehicle_plate'] = strtoupper(trim($validated['vehicle_plate']));
        }

        $customer = $this->customerRepository->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data'    => $customer,
        ]);
    }

    public function destroy($id)
    {
        $this->customerRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}
