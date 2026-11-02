<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Support\Pricing;
use Illuminate\Http\Request;

/** Discount coupons managed by the shop and checked live at the POS. */
class CouponController extends Controller
{
    private function rules(?int $ignore = null): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:coupons,code' . ($ignore ? ",$ignore" : '')],
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percent,flat',
            'value' => 'required|numeric|min:0.01|max:10000000',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ];
    }

    private function payload(Request $r): array
    {
        $d = $r->validate($this->rules($r->route('id') ? (int) $r->route('id') : null));
        if (($d['type'] ?? '') === 'percent' && $d['value'] > 100) {
            abort(response()->json(['message' => 'A percentage coupon cannot exceed 100%.', 'errors' => ['value' => ['A percentage coupon cannot exceed 100%.']]], 422));
        }
        $d['code'] = strtoupper($d['code']);
        $d['min_amount'] = $d['min_amount'] ?? 0;

        return $d;
    }

    public function index()
    {
        return $this->data(Coupon::orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        return $this->data(Coupon::create($this->payload($request)), null, 201);
    }

    public function update(Request $request, $id)
    {
        $row = Coupon::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        Coupon::where('id', $id)->delete();

        return $this->message('Deleted.');
    }

    /** POS check: returns the discount this code would give on the current cart (nothing is consumed). */
    public function check(Request $request)
    {
        $d = $request->validate(['code' => 'required|string|max:40', 'items_total' => 'required|numeric|min:0', 'brands' => 'nullable|array']);
        [$c, $discount] = Pricing::coupon($d['code'], (float) $d['items_total'], $d['brands'] ?? []);

        return $this->data(['code' => $c->code, 'description' => $c->description, 'discount' => $discount]);
    }

    /** POS check: points a customer holds (matched by phone/email) and what they are worth on this bill. */
    public function loyalty(Request $request)
    {
        $d = $request->validate(['phone' => 'nullable|string|max:40', 'email' => 'nullable|string|max:255', 'amount' => 'nullable|numeric|min:0']);
        $acc = Pricing::loyaltyFor($d['phone'] ?? null, $d['email'] ?? null);
        if (! $acc) {
            return $this->data(['found' => false, 'points' => 0, 'max_points' => 0, 'max_discount' => 0]);
        }
        [$use, $value] = Pricing::redeemValue($acc, PHP_INT_MAX, (float) ($d['amount'] ?? 0));

        return $this->data(['found' => true, 'points' => (int) $acc->points, 'tier' => $acc->tier, 'max_points' => $use, 'max_discount' => $value,
            'rule' => Pricing::POINT_VALUE . ' point = ₹' . Pricing::POINT_VALUE . ', up to ' . Pricing::MAX_REDEEM_PCT . '% of the bill']);
    }
}
