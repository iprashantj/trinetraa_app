<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EyeCheckupBill;
use App\Support\BillNumber;
use Illuminate\Http\Request;

class EyeCheckupBillController extends Controller
{
    private array $rules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string',
        'customer_contact' => 'nullable|string',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'left_eye' => 'nullable|string',
        'right_eye' => 'nullable|string',
        'addition' => 'nullable|string',
        'frame_amount' => 'sometimes|numeric|min:0',
        'glass_amount' => 'sometimes|numeric|min:0',
        'advance_amount' => 'sometimes|numeric|min:0',
        'other_amount' => 'sometimes|numeric|min:0',
        'with_gst' => 'sometimes|boolean',
        'gst_rate' => 'sometimes|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = EyeCheckupBill::query()
            ->orderByDesc('created_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = EyeCheckupBill::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        $data = $this->computed($request);
        $data['bill_number'] = BillNumber::generate('ECB');

        $row = EyeCheckupBill::create($data);

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = EyeCheckupBill::find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = EyeCheckupBill::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->computed($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = EyeCheckupBill::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    private function computed(Request $request): array
    {
        $frameAmount = (float) $request->input('frame_amount', 0);
        $glassAmount = (float) $request->input('glass_amount', 0);
        $advanceAmount = (float) $request->input('advance_amount', 0);
        $otherAmount = (float) $request->input('other_amount', 0);
        $withGst = $request->has('with_gst') ? $request->boolean('with_gst') : false;
        $gstRate = (float) $request->input('gst_rate', 0);

        $subtotal = $frameAmount + $glassAmount + $otherAmount;
        $tax = $withGst ? $subtotal * $gstRate / 100 : 0;
        $total = $subtotal + $tax;
        $balanceDue = $total - $advanceAmount;

        return [
            'customer_id' => $request->input('customer_id'),
            'customer_name' => $request->input('customer_name'),
            'customer_contact' => $request->input('customer_contact'),
            'bill_date' => $request->input('bill_date'),
            'left_eye' => $request->input('left_eye'),
            'right_eye' => $request->input('right_eye'),
            'addition' => $request->input('addition'),
            'frame_amount' => $frameAmount,
            'glass_amount' => $glassAmount,
            'advance_amount' => $advanceAmount,
            'other_amount' => $otherAmount,
            'with_gst' => $withGst,
            'gst_rate' => $gstRate,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'balance_due' => $balanceDue,
            'notes' => $request->input('notes'),
        ];
    }
}
