<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrameBill;
use App\Models\Branch;
use App\Support\Access;
use App\Support\BillNumber;
use App\Support\Pricing;
use App\Support\StockService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FrameBillController extends Controller
{
    private array $storeRules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string|max:255',
        'customer_contact' => 'nullable|string|max:100',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'discount_amount' => 'sometimes|numeric|min:0|max:10000000',
        'gst_rate' => 'sometimes|numeric|min:0|max:100',
        'notes' => 'nullable|string|max:2000',
        'coupon_code' => 'nullable|string|max:40',
        'loyalty_points' => 'nullable|integer|min:0|max:1000000',
        'customer_gstin' => ['nullable', 'string', 'max:20', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'],
        'branch_id' => 'nullable|integer|exists:branches,id',
        'payment_method' => 'nullable|in:cash,upi,card,online',
        'payment_status' => 'nullable|in:paid,pending,partial',
        'paid_amount' => 'nullable|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.stock_item_id' => 'nullable|integer|exists:stock_items,id',
        'items.*.brand_name' => 'required|string|max:255',
        'items.*.model_number' => 'nullable|string|max:100',
        'items.*.hsn_code' => 'nullable|string|max:12',
        'items.*.price' => 'required|numeric|min:0|max:10000000',
        'items.*.discount' => 'sometimes|numeric|min:0|max:10000000',
        'items.*.quantity' => 'sometimes|integer|min:1|max:10000',
    ];

    private array $updateRules = [
        'customer_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string|max:255',
        'customer_contact' => 'nullable|string|max:100',
        'bill_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        'discount_amount' => 'sometimes|numeric|min:0|max:10000000',
        'gst_rate' => 'sometimes|numeric|min:0|max:100',
        'notes' => 'nullable|string|max:2000',
        'customer_gstin' => ['nullable', 'string', 'max:20', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'],
        'payment_method' => 'nullable|in:cash,upi,card,online',
        'payment_status' => 'nullable|in:paid,pending,partial,refunded',
        'paid_amount' => 'nullable|numeric|min:0',
    ];

    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(500, (int) ($request->query('per_page') ?: 20)));
        $skip = ($page - 1) * $perPage;

        $branchId = Access::branchScope($request);
        $search = trim((string) $request->query('search', ''));
        $base = FrameBill::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('bill_number', 'like', "%$search%")
                ->orWhere('customer_name', 'like', "%$search%")->orWhere('customer_contact', 'like', "%$search%")));
        $total = (clone $base)->count();
        $rows = $base->orderByDesc('created_at')->with(['customer:id,name', 'items'])->skip($skip)->take($perPage)->get();
        $branches = Branch::pluck('name', 'id');
        $rows->each(fn ($r) => $r->setAttribute('branch_name', $branches[$r->branch_id] ?? null));

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
                'stock_item_id' => $i['stock_item_id'] ?? null,
                'hsn_code' => $i['hsn_code'] ?? null,
            ];
        }, $request->input('items'));

        foreach ($billItems as $i => $bi) {
            if ($bi['discount'] > $bi['price']) {
                throw ValidationException::withMessages(["items.$i.discount" => 'Item discount cannot exceed its price.']);
            }
        }
        $discountAmount = (float) $request->input('discount_amount', 0);
        $gstRate = (float) $request->input('gst_rate', 0);
        if ($discountAmount > array_sum(array_column($billItems, 'subtotal'))) {
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the items total.']);
        }
        $itemsNet = array_sum(array_column($billItems, 'subtotal')) - $discountAmount;
        $admin = Access::admin($request);
        $branchId = Access::writeBranch($request, $request->input('branch_id'));
        $tracked = array_filter($billItems, fn ($b) => ! empty($b['stock_item_id']));
        if ($tracked && (! $branchId || ! Branch::find($branchId))) {
            throw ValidationException::withMessages(['branch_id' => 'Choose the branch this sale is from so its stock can be deducted.']);
        }

        $row = DB::transaction(function () use ($request, $billItems, $discountAmount, $gstRate, $itemsNet, $admin, $branchId) {
            // Coupon + loyalty are re-validated server-side under a row lock; the browser's numbers are never trusted.
            $coupon = null;
            $couponDiscount = 0.0;
            if ($request->filled('coupon_code')) {
                $locked = \App\Models\Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($request->input('coupon_code')))])->lockForUpdate()->first();
                [$coupon, $couponDiscount] = Pricing::coupon($locked?->code ?? $request->input('coupon_code'), $itemsNet, array_column($billItems, 'brand_name'));
            }
            $loyaltyPoints = 0;
            $loyaltyDiscount = 0.0;
            $account = null;
            if ((int) $request->input('loyalty_points', 0) > 0) {
                $cust = $request->input('customer_id') ? \App\Models\Customer::find($request->input('customer_id')) : null;
                $account = Pricing::loyaltyFor($request->input('customer_contact') ?: $cust?->phone, $cust?->email);
                if (! $account) {
                    throw ValidationException::withMessages(['loyalty_points' => 'No loyalty account matches this customer’s phone or email.']);
                }
                [$loyaltyPoints, $loyaltyDiscount] = Pricing::redeemValue($account, (int) $request->input('loyalty_points'), $itemsNet - $couponDiscount);
            }
            $subtotal = round($itemsNet - $couponDiscount - $loyaltyDiscount, 2);
            $tax = round($subtotal * $gstRate / 100, 2);
            [$cgst, $sgst] = Pricing::splitTax($tax);
            $total = round($subtotal + $tax, 2);
            $status = $request->input('payment_status', $request->input('payment_method') ? 'paid' : 'pending');
            $paid = $status === 'paid' ? $total : ($request->input('paid_amount') !== null ? (float) $request->input('paid_amount') : 0);

            $stock = \App\Models\StockItem::whereIn('id', array_filter(array_column($billItems, 'stock_item_id')))->get()->keyBy('id');
            $lines = array_map(function ($b) use ($stock, $request) {
                $s = $stock[$b['stock_item_id'] ?? 0] ?? null;

                return $b + ['hsn_code' => $b['hsn_code'] ?? $s?->hsn_code, 'cost_price' => $s?->cost_price];
            }, $billItems);

            $bill = FrameBill::create([
                'bill_number' => BillNumber::generate('FB'),
                'invoice_no' => Pricing::invoiceNo($branchId, 'frame', $request->input('bill_date')),
                'customer_id' => $request->input('customer_id'),
                'customer_name' => $request->input('customer_name'),
                'customer_contact' => $request->input('customer_contact'),
                'customer_gstin' => $request->filled('customer_gstin') ? strtoupper($request->input('customer_gstin')) : null,
                'bill_date' => $request->input('bill_date'),
                'discount_amount' => $discountAmount,
                'coupon_code' => $coupon?->code, 'coupon_discount' => $couponDiscount,
                'loyalty_points_used' => $loyaltyPoints, 'loyalty_discount' => $loyaltyDiscount,
                'gst_rate' => $gstRate,
                'subtotal' => $subtotal,
                'tax' => $tax, 'cgst' => $cgst, 'sgst' => $sgst,
                'total' => $total,
                'notes' => $request->input('notes'),
                'branch_id' => $branchId ?: null,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $status,
                'paid_amount' => $paid,
                'share_token' => Str::random(40),
                'created_by' => $admin['id'] ?? null,
            ]);
            $bill->items()->createMany($lines);
            // Stock is deducted from the selling branch inside the same transaction.
            foreach ($billItems as $bi) {
                if (! empty($bi['stock_item_id'])) {
                    StockService::sell((int) $bi['stock_item_id'], $branchId, (int) $bi['quantity'], $bill->id, $admin['id'] ?? null);
                }
            }
            if ($coupon) {
                $coupon->increment('used_count');
            }
            if ($account && $loyaltyPoints > 0) {
                Pricing::applyLoyalty($account, $bill, $loyaltyPoints, 'redeem', 'Redeemed on ' . $bill->invoice_no);
            }
            Pricing::earnForBill($bill->load('customer'));

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
        // Recompute totals from stored line items so tax/total never drift.
        $itemsTotal = (float) $row->items()->get()->sum('subtotal');
        $discountAmount = (float) $request->input('discount_amount', 0);
        if ($discountAmount > $itemsTotal) {
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the items total.']);
        }
        $gstRate = (float) $request->input('gst_rate', 0);
        // Coupon / loyalty discounts were fixed when the bill was created and stay on it.
        $subtotal = round($itemsTotal - $discountAmount - (float) $row->coupon_discount - (float) $row->loyalty_discount, 2);
        $tax = round(max(0, $subtotal) * $gstRate / 100, 2);
        [$cgst, $sgst] = Pricing::splitTax($tax);

        $row->update([
            'customer_id' => $request->input('customer_id'),
            'customer_name' => $request->input('customer_name'),
            'customer_contact' => $request->input('customer_contact'),
            'bill_date' => $request->input('bill_date'),
            'discount_amount' => $discountAmount,
            'gst_rate' => $gstRate,
            'subtotal' => $subtotal,
            'tax' => $tax, 'cgst' => $cgst, 'sgst' => $sgst,
            'total' => round($subtotal + $tax, 2),
            'customer_gstin' => $request->input('customer_gstin') ? strtoupper($request->input('customer_gstin')) : $row->customer_gstin,
            'notes' => $request->input('notes'),
            'payment_method' => $request->input('payment_method', $row->payment_method),
            'payment_status' => $request->input('payment_status', $row->payment_status),
            'paid_amount' => $request->input('paid_amount', $row->paid_amount),
        ]);

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = FrameBill::with('items')->find($id);
        if ($row) {
            DB::transaction(function () use ($row) {
                $admin = request()->attributes->get('admin') ?? [];
                $returned = \App\Models\BillReturnItem::query()
                    ->whereIn('frame_bill_item_id', $row->items->pluck('id'))->where('restock', true)
                    ->selectRaw('frame_bill_item_id, SUM(quantity) q')->groupBy('frame_bill_item_id')->pluck('q', 'frame_bill_item_id');
                foreach ($row->items as $it) {
                    $back = (int) $it->quantity - (int) ($returned[$it->id] ?? 0);
                    if ($it->stock_item_id && $row->branch_id && $back > 0) {
                        StockService::restock($it->stock_item_id, $row->branch_id, $back, 'void', "Bill {$row->bill_number} deleted", 'frame_bill', $row->id, $admin['id'] ?? null);
                    }
                }
                Pricing::reverseForBill($row);
                $row->delete();
            });
        }

        return $this->message('Deleted.');
    }
}
