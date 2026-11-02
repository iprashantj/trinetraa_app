<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EyeCheckupBill;
use App\Support\BillNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EyeCheckupBillController extends Controller
{
    private array $rules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string|max:255',
        'customer_contact' => 'nullable|string|max:100',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'left_eye' => 'nullable|string|max:100',
        'right_eye' => 'nullable|string|max:100',
        'addition' => 'nullable|string|max:100',
        'frame_amount' => 'sometimes|numeric|min:0|max:10000000',
        'glass_amount' => 'sometimes|numeric|min:0|max:10000000',
        'advance_amount' => 'sometimes|numeric|min:0|max:10000000',
        'other_amount' => 'sometimes|numeric|min:0|max:10000000',
        'with_gst' => 'sometimes|boolean',
        'gst_rate' => 'sometimes|numeric|min:0|max:100',
        'notes' => 'nullable|string|max:2000',
        'branch_id' => 'nullable|integer|exists:branches,id',
        'payment_method' => 'nullable|in:cash,upi,card,online',
    ];

    public function index(Request $request)
    {
        $branchScope = \App\Support\Access::branchScope($request);
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(500, (int) ($request->query('per_page') ?: 20)));
        $skip = ($page - 1) * $perPage;

        $base = EyeCheckupBill::query()->when($branchScope, fn ($q) => $q->where('branch_id', $branchScope));
        $total = (clone $base)->count();
        $rows = $base->orderByDesc('created_at')->skip($skip)->take($perPage)->get();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        $data = $this->computed($request);
        $data['bill_number'] = BillNumber::generate('ECB');
        $admin = \App\Support\Access::admin($request);
        $data['branch_id'] = \App\Support\Access::writeBranch($request, $request->input('branch_id')) ?: null;
        $data['payment_method'] = $request->input('payment_method');
        $data['share_token'] = \Illuminate\Support\Str::random(40);
        $data['created_by'] = $admin['id'] ?? null;

        $row = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request) {
            $data['invoice_no'] = \App\Support\Pricing::invoiceNo($data['branch_id'], 'eye', $request->input('bill_date'));

            return EyeCheckupBill::create($data);
        });

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
        if ($advanceAmount > $total) {
            throw ValidationException::withMessages(['advance_amount' => 'Advance cannot exceed the bill total.']);
        }
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
