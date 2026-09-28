<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\BillNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private array $storeRules = [
        'type' => 'required|string',
        'customer_id' => 'nullable|integer|min:1',
        'order_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'tax' => 'sometimes|numeric|min:0',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.stock_item_id' => 'required|integer|min:1',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];

    private array $updateRules = [
        'type' => 'required|string',
        'customer_id' => 'nullable|integer|min:1',
        'order_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'tax' => 'sometimes|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = Order::query()
            ->orderByDesc('created_at')
            ->with([
                'customer:id,name',
                'items.stockItem:id,name',
            ])
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = Order::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->storeRules);

        $orderItems = array_map(function ($i) {
            $unitPrice = (float) $i['unit_price'];
            $quantity = (int) $i['quantity'];

            return [
                'stock_item_id' => (int) $i['stock_item_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
            ];
        }, $request->input('items'));

        $subtotal = array_sum(array_column($orderItems, 'subtotal'));
        $tax = (float) $request->input('tax', 0);
        $total = $subtotal + $tax;

        $row = DB::transaction(function () use ($request, $orderItems, $subtotal, $tax, $total) {
            $order = Order::create([
                'order_number' => BillNumber::generate('ORD'),
                'type' => $request->input('type'),
                'customer_id' => $request->input('customer_id'),
                'order_date' => $request->input('order_date'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $request->input('notes'),
            ]);
            $order->items()->createMany($orderItems);

            return $order->load('items');
        });

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = Order::with(['customer:id,name', 'items.stockItem:id,name'])->find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->updateRules);
        $row = Order::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update([
            'type' => $request->input('type'),
            'customer_id' => $request->input('customer_id'),
            'order_date' => $request->input('order_date'),
            'tax' => (float) $request->input('tax', 0),
            'notes' => $request->input('notes'),
        ]);

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Order::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }
}
