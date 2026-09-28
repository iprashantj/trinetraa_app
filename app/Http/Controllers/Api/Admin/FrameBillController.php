<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrameBill;
use App\Support\BillNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrameBillController extends Controller
{
    private array $storeRules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string',
        'customer_contact' => 'nullable|string',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'discount_amount' => 'sometimes|numeric|min:0',
        'gst_rate' => 'sometimes|numeric|min:0',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.brand_name' => 'required|string',
        'items.*.model_number' => 'nullable|string',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.discount' => 'sometimes|numeric|min:0',
        'items.*.quantity' => 'sometimes|integer|min:1',
    ];

    private array $updateRules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string',
        'customer_contact' => 'nullable|string',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'discount_amount' => 'sometimes|numeric|min:0',
        'gst_rate' => 'sometimes|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = FrameBill::query()
            ->orderByDesc('created_at')
            ->with(['customer:id,name', 'items'])
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = FrameBill::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->storeRules);

        $billItems = array_map(function ($i) {
            $price = (float) $i['price'];
            $discount = (float) ($i['discount'] ?? 0);
            $quantity = (int) ($i['quantity'] ?? 1);

            return [
                'brand_name' => $i['brand_name'],
                'model_number' => $i['model_number'] ?? null,
                'price' => $price,
                'discount' => $discount,
                'quantity' => $quantity,
                'subtotal' => ($price - $discount) * $quantity,
            ];
        }, $request->input('items'));

        $discountAmount = (float) $request->input('discount_amount', 0);
        $gstRate = (float) $request->input('gst_rate', 0);
        $subtotal = array_sum(array_column($billItems, 'subtotal')) - $discountAmount;
        $tax = $subtotal * $gstRate / 100;
        $total = $subtotal + $tax;

        $row = DB::transaction(function () use ($request, $billItems, $discountAmount, $gstRate, $subtotal, $tax, $total) {
            $bill = FrameBill::create([
                'bill_number' => BillNumber::generate('FB'),
                'customer_id' => $request->input('customer_id'),
                'customer_name' => $request->input('customer_name'),
                'customer_contact' => $request->input('customer_contact'),
                'bill_date' => $request->input('bill_date'),
                'discount_amount' => $discountAmount,
                'gst_rate' => $gstRate,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $request->input('notes'),
            ]);
            $bill->items()->createMany($billItems);

            return $bill->load('items');
        });

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = FrameBill::with(['customer:id,name', 'items'])->find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->updateRules);
        $row = FrameBill::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update([
            'customer_id' => $request->input('customer_id'),
            'customer_name' => $request->input('customer_name'),
            'customer_contact' => $request->input('customer_contact'),
            'bill_date' => $request->input('bill_date'),
            'discount_amount' => (float) $request->input('discount_amount', 0),
            'gst_rate' => (float) $request->input('gst_rate', 0),
            'notes' => $request->input('notes'),
        ]);

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = FrameBill::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }
}
