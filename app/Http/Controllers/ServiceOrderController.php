<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    protected $serviceOrderService;

    public function __construct(ServiceOrderService $serviceOrderService)
    {
        $this->serviceOrderService = $serviceOrderService;
    }

    public function index(Request $request)
    {
        return view('service-orders.index');
    }

    public function list(Request $request)
    {
        $result = $this->serviceOrderService->list($request);

        return response()->json([
            'success' => true,
            'data'    => collect($result['data'])->map(fn ($order) => [
                'id'            => $order->id,
                'order_no'      => $order->order_no,
                'customer_name' => $order->customer->name ?? '-',
                'vehicle_plate' => $order->vehicle_plate,
                'vehicle_type'  => $order->vehicle_type,
                'complaint'     => $order->complaint,
                'status'        => $order->status,
                'service_fee'   => (float) $order->service_fee,
                'handler'       => $order->handler?->name ?? '-',
                'parts_count'   => $order->items->count(),
                'created_at'    => optional($order->created_at)->format('d M Y, H:i'),
                'finished_at'   => optional($order->finished_at)->format('d M Y, H:i'),
            ])->values(),
            'meta' => $result['meta'],
        ]);
    }

    public function show($id)
    {
        $order = $this->serviceOrderService->detail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $order->id,
                'order_no'      => $order->order_no,
                'customer'      => $order->customer,
                'vehicle_plate' => $order->vehicle_plate,
                'vehicle_type'  => $order->vehicle_type,
                'complaint'     => $order->complaint,
                'diagnosis'     => $order->diagnosis,
                'notes'         => $order->notes,
                'service_fee'   => (float) $order->service_fee,
                'discount'      => (float) $order->discount,
                'grand_total'   => $order->grandTotal(),
                'status'        => $order->status,
                'handler'       => $order->handler,
                'items'         => $order->items->map(fn ($item) => [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name ?? '-',
                    'product_sku'  => $item->product->sku  ?? '-',
                    'qty'          => $item->qty,
                    'price'        => (float) $item->price,
                    'subtotal'     => (float) $item->subtotal,
                ]),
                'sale'          => $order->sale,
                'created_at'    => optional($order->created_at)->format('d M Y, H:i'),
                'finished_at'   => optional($order->finished_at)->format('d M Y, H:i'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'            => 'required|exists:customers,id',
            'vehicle_plate'          => 'required|string|max:20',
            'vehicle_type'           => 'nullable|string|max:100',
            'complaint'              => 'required|string|max:1000',
            'diagnosis'              => 'nullable|string|max:1000',
            'notes'                  => 'nullable|string|max:500',
            'service_fee'            => 'nullable|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'handled_by'             => 'nullable|exists:users,id',
            'items'                  => 'nullable|array',
            'items.*.product_id'     => 'required|integer|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.price'          => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->serviceOrderService->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Work order berhasil dibuat.',
                'data'    => [
                    'id'       => $order->id,
                    'order_no' => $order->order_no,
                    'status'   => $order->status,
                ],
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'vehicle_plate'      => 'nullable|string|max:20',
            'vehicle_type'       => 'nullable|string|max:100',
            'complaint'          => 'nullable|string|max:1000',
            'diagnosis'          => 'nullable|string|max:1000',
            'notes'              => 'nullable|string|max:500',
            'service_fee'        => 'nullable|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'handled_by'         => 'nullable|exists:users,id',
            'items'              => 'nullable|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->serviceOrderService->update($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Work order berhasil diperbarui.',
                'data'    => ['id' => $order->id, 'order_no' => $order->order_no],
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,done,cancelled',
        ]);

        try {
            $order = $this->serviceOrderService->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => "Status work order diubah ke '{$order->status}'.",
                'data'    => ['id' => $order->id, 'status' => $order->status],
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
