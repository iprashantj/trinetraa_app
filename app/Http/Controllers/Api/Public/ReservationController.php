<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Eyewear;
use App\Models\Reservation;
use App\Models\StockLevel;
use App\Support\ResendMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Public "reserve for store pickup". Never returns anyone's data except the caller's own confirmation. */
class ReservationController extends Controller
{
    private const HOLD_HOURS = 48;

    public function store(Request $request)
    {
        // Honeypot: real visitors never fill this hidden field. Pretend success so bots learn nothing.
        if (filled($request->input('website'))) {
            return $this->data(['code' => 'RSV-' . strtoupper(substr(md5((string) microtime()), 0, 6)), 'expires_at' => now()->addHours(self::HOLD_HOURS)], null, 201);
        }

        $v = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone' => ['required', 'string', 'regex:/^[+0-9][0-9 \-]{8,15}$/'],
            'email' => 'nullable|email|max:150',
            'eyewear_id' => 'nullable|integer|min:1',
            'stock_item_id' => 'nullable|integer|min:1',
            'branch_id' => 'required|integer|min:1',
            'quantity' => 'nullable|integer|min:1|max:2',
        ]);
        if (empty($v['eyewear_id']) && empty($v['stock_item_id'])) {
            return $this->message('Choose a product to reserve.', 422);
        }

        // Only products shown on the website (active eyewear linked to ERP stock) can be reserved.
        $eyewear = Eyewear::where('is_active', true)
            ->when(! empty($v['eyewear_id']), fn ($q) => $q->where('id', $v['eyewear_id']))
            ->when(empty($v['eyewear_id']), fn ($q) => $q->where('stock_item_id', $v['stock_item_id']))
            ->first();
        if (! $eyewear || ! $eyewear->stock_item_id) {
            return $this->message('This product cannot be reserved online. Please call or visit the store.', 422);
        }
        $branch = Branch::where('is_active', true)->find($v['branch_id']);
        if (! $branch) {
            return $this->message('Choose a valid store.', 422);
        }

        $qty = (int) ($v['quantity'] ?? 1);
        $phone = preg_replace('/[^\d+]/', '', $v['phone']);
        if (Reservation::active()->where('phone', $phone)->sum('quantity') + $qty > 4) {
            return $this->message('You already have active reservations. Please collect them first or call the store.', 429);
        }

        $itemId = (int) $eyewear->stock_item_id;
        $res = DB::transaction(function () use ($itemId, $branch, $qty, $v, $phone, $eyewear) {
            $level = StockLevel::where('stock_item_id', $itemId)->where('branch_id', $branch->id)->lockForUpdate()->first();
            $available = ($level->quantity ?? 0) - Reservation::heldQty($itemId, $branch->id);
            if ($available < $qty) {
                return null;
            }

            return Reservation::create([
                'code' => Reservation::newCode(), 'stock_item_id' => $itemId, 'eyewear_id' => $eyewear->id,
                'branch_id' => $branch->id, 'quantity' => $qty, 'name' => trim($v['name']), 'phone' => $phone,
                'email' => $v['email'] ?? null, 'status' => 'pending', 'expires_at' => now()->addHours(self::HOLD_HOURS),
            ]);
        });
        if (! $res) {
            return $this->message('Sorry, this item is no longer available at ' . $branch->name . '. Please choose another store.', 409);
        }

        if ($res->email) {
            try {
                ResendMailer::send($res->email, 'Your reservation ' . $res->code . ' — Trinetraa Optician',
                    '<p>Hello ' . e($res->name) . ',</p><p>We are holding <strong>' . e($eyewear->name) . '</strong> for you at our <strong>' . e($branch->name) . '</strong> store until <strong>'
                    . $res->expires_at->timezone(config('app.timezone'))->format('d M Y, h:i A') . '</strong>.</p><p>Reservation code: <strong>' . e($res->code) . '</strong>. Please show this code at the store.</p>');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->data([
            'code' => $res->code, 'expires_at' => $res->expires_at, 'branch' => $branch->name, 'product' => $eyewear->name,
        ], null, 201);
    }
}
