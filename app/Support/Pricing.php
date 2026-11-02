<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Coupon;
use App\Models\CustomerUser;
use App\Models\FrameBill;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** GST invoice numbering/split, coupon rules and POS loyalty earn/redeem. */
class Pricing
{
    public const POINT_VALUE = 1;          // ₹ off per redeemed point
    public const MAX_REDEEM_PCT = 25;      // at most this % of the bill can be paid with points
    public const EARN_PER_RUPEES = 100;    // 1 point per this many ₹ paid

    /** Indian financial year label (Apr–Mar) for a date, e.g. 2026-27. */
    public static function fy(?string $date = null): string
    {
        $t = $date ? strtotime($date) : time();
        $y = (int) date('Y', $t);
        $start = (int) date('n', $t) >= 4 ? $y : $y - 1;

        return $start . '-' . substr((string) ($start + 1), 2);
    }

    /** Next invoice number for a branch + FY, e.g. NSK/2026-27/0001 (numbers are never reused, so void rather than delete bills to avoid gaps). Call inside the bill's transaction. */
    public static function invoiceNo(?int $branchId, string $kind = 'frame', ?string $date = null): string
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $prefix = strtoupper($branch?->invoice_prefix ?: ($branch?->code ?: 'INV'));
        if ($kind === 'eye') {
            $prefix .= '-EC';
        }
        $fy = self::fy($date);
        $key = ['branch_id' => (int) $branchId, 'fy' => $fy, 'kind' => $kind];
        DB::table('invoice_sequences')->insertOrIgnore($key + ['last_no' => 0]);
        $row = DB::table('invoice_sequences')->where($key)->lockForUpdate()->first();
        $n = (int) $row->last_no + 1;
        DB::table('invoice_sequences')->where('id', $row->id)->update(['last_no' => $n]);

        return sprintf('%s/%s/%04d', $prefix, $fy, $n);
    }

    /** CGST/SGST halves of a tax amount; the second absorbs the rounding paisa. */
    public static function splitTax(float $tax): array
    {
        $c = round($tax / 2, 2);

        return [$c, round($tax - $c, 2)];
    }

    /** Validate a coupon against the bill and return [coupon, discount ₹]; throws a 422 with a readable reason. */
    public static function coupon(string $code, float $itemsTotal, array $brands = []): array
    {
        $c = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();
        $fail = fn (string $m) => throw ValidationException::withMessages(['coupon_code' => $m]);
        if (! $c || ! $c->is_active) {
            $fail('This coupon code is not valid.');
        }
        $today = now()->startOfDay();
        if ($c->valid_from && $today->lt($c->valid_from)) {
            $fail('This coupon is not active yet.');
        }
        if ($c->valid_to && $today->gt($c->valid_to)) {
            $fail('This coupon has expired.');
        }
        if ($c->usage_limit !== null && $c->used_count >= $c->usage_limit) {
            $fail('This coupon has been fully used.');
        }
        if ($itemsTotal < (float) $c->min_amount) {
            $fail('Minimum bill of ₹' . number_format($c->min_amount, 2) . ' needed for this coupon.');
        }
        if ($c->brand && ! in_array(strtolower($c->brand), array_map('strtolower', $brands), true)) {
            $fail('This coupon is only for ' . $c->brand . ' products.');
        }
        $d = $c->type === 'percent' ? $itemsTotal * $c->value / 100 : $c->value;
        if ($c->max_discount !== null) {
            $d = min($d, $c->max_discount);
        }

        return [$c, round(max(0, min($d, $itemsTotal)), 2)];
    }

    /** Loyalty account matching a POS customer by phone (last 10 digits) or email; null when they have no website account. */
    public static function loyaltyFor(?string $phone, ?string $email): ?LoyaltyAccount
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $phone), -10);
        if (strlen($digits) !== 10 && ! $email) {
            return null;
        }
        $u = CustomerUser::query()->where(function ($q) use ($digits, $email) {
            if (strlen($digits) === 10) {
                $q->where('phone', 'like', '%' . $digits);
            }
            if ($email) {
                $q->orWhere('email', $email);
            }
        })->first();

        return $u ? LoyaltyAccount::firstOrCreate(['customer_id' => $u->id], ['points' => 0, 'tier' => 'Bronze', 'total_earned' => 0]) : null;
    }

    /** Points → ₹ discount, capped by balance and MAX_REDEEM_PCT of the amount. Returns [points used, ₹]. */
    public static function redeemValue(LoyaltyAccount $acc, int $points, float $amount): array
    {
        $cap = (int) floor($amount * self::MAX_REDEEM_PCT / 100 / self::POINT_VALUE);
        $use = max(0, min($points, (int) $acc->points, $cap));

        return [$use, round($use * self::POINT_VALUE, 2)];
    }

    public static function applyLoyalty(LoyaltyAccount $acc, FrameBill $bill, int $points, string $type, string $desc): void
    {
        $delta = $type === 'redeem' ? -abs($points) : abs($points);
        $acc->points = max(0, $acc->points + $delta);
        if ($delta > 0) {
            $acc->total_earned += $delta;
        }
        $acc->tier = $acc->total_earned >= 5000 ? 'Gold' : ($acc->total_earned >= 2000 ? 'Silver' : 'Bronze');
        $acc->save();
        LoyaltyTransaction::create(['account_id' => $acc->id, 'type' => $type, 'points' => $delta, 'description' => $desc, 'reference_id' => 'FB' . $bill->id]);
    }

    /** Idempotent: awards purchase points once the bill is fully paid. */
    public static function earnForBill(FrameBill $bill): void
    {
        if ($bill->payment_status !== 'paid') {
            return;
        }
        $acc = self::loyaltyFor($bill->customer_contact, optional($bill->customer)->email);
        if (! $acc || LoyaltyTransaction::where('account_id', $acc->id)->where('type', 'purchase')->where('reference_id', 'FB' . $bill->id)->exists()) {
            return;
        }
        $pts = (int) floor((float) $bill->total / self::EARN_PER_RUPEES);
        if ($pts > 0) {
            self::applyLoyalty($acc, $bill, $pts, 'purchase', 'Purchase ' . ($bill->invoice_no ?: $bill->bill_number));
        }
    }

    /** Undo everything a bill did to loyalty and coupons (used when a bill is deleted). */
    public static function reverseForBill(FrameBill $bill): void
    {
        foreach (LoyaltyTransaction::where('reference_id', 'FB' . $bill->id)->get() as $tx) {
            if ($acc = LoyaltyAccount::find($tx->account_id)) {
                $acc->points = max(0, $acc->points - (int) $tx->points);
                $acc->save();
            }
            $tx->delete();
        }
        if ($bill->coupon_code) {
            Coupon::where('code', $bill->coupon_code)->where('used_count', '>', 0)->decrement('used_count');
        }
    }
}
