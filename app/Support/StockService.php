<?php

namespace App\Support;

use App\Models\StockItem;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\StockUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Single entry point for every stock change. Keeps three things in step:
 *   - stock_levels      (quantity per item per branch)
 *   - stock_items.current_stock (all-branch total, used by older screens)
 *   - stock_movements   (append-only ledger: who / what / when / why)
 * Physical units (stock_units) keep their unique code when they move between
 * branches, so nothing is ever rescanned. Items that were entered before unit
 * tracking existed simply have fewer units than quantity; that is handled.
 */
class StockService
{
    public static function level(int $itemId, int $branchId): int
    {
        return (int) StockLevel::where('stock_item_id', $itemId)->where('branch_id', $branchId)->value('quantity');
    }

    /** Signed change of quantity at one branch. Refuses to go below zero. */
    public static function change(
        int $itemId, int $branchId, int $delta, string $type, ?string $reason = null,
        ?string $refType = null, ?int $refId = null, ?int $userId = null
    ): int {
        if ($delta === 0) {
            return self::level($itemId, $branchId);
        }

        return DB::transaction(function () use ($itemId, $branchId, $delta, $type, $reason, $refType, $refId, $userId) {
            $level = StockLevel::where('stock_item_id', $itemId)->where('branch_id', $branchId)->lockForUpdate()->first();
            if (! $level) {
                $level = StockLevel::create(['stock_item_id' => $itemId, 'branch_id' => $branchId, 'quantity' => 0]);
            }
            $new = $level->quantity + $delta;
            if ($new < 0) {
                $name = StockItem::where('id', $itemId)->value('name') ?? "item #$itemId";
                throw ValidationException::withMessages(['quantity' => "Not enough stock for {$name}: {$level->quantity} available, {$delta} requested."]);
            }
            $level->update(['quantity' => $new]);
            StockItem::where('id', $itemId)->update([
                'current_stock' => (int) StockLevel::where('stock_item_id', $itemId)->sum('quantity'),
            ]);
            StockMovement::create([
                'stock_item_id' => $itemId, 'branch_id' => $branchId, 'type' => $type, 'quantity' => $delta,
                'reference_type' => $refType, 'reference_id' => $refId, 'reason' => $reason, 'user_id' => $userId,
            ]);

            return $new;
        });
    }

    /** Stock inward: adds quantity and creates one unique-coded unit per piece. */
    public static function inward(int $itemId, int $branchId, int $qty, int $inwardId, ?int $userId): array
    {
        self::change($itemId, $branchId, $qty, 'inward', null, 'stock_inward', $inwardId, $userId);
        $codes = [];
        for ($i = 0; $i < $qty; $i++) {
            $codes[] = self::newUnit($itemId, $branchId, $inwardId);
        }

        return $codes;
    }

    /** Create one unit with a code that has never existed before (rows are never deleted). */
    public static function newUnit(int $itemId, int $branchId, ?int $inwardId = null): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = 'TR';
            for ($k = 0; $k < 8; $k++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            if (! StockUnit::where('unique_code', $code)->exists()) {
                StockUnit::create([
                    'unique_code' => $code, 'stock_item_id' => $itemId, 'branch_id' => $branchId,
                    'status' => 'in_stock', 'inward_id' => $inwardId,
                ]);

                return $code;
            }
        }
        throw new \RuntimeException('Could not allocate a unique item code.');
    }

    /** Move up to $qty in-stock units between states/branches (oldest first). Returns how many moved. */
    public static function moveUnits(int $itemId, int $fromBranch, int $qty, string $toStatus, ?int $toBranch = null, string $fromStatus = 'in_stock'): int
    {
        $units = StockUnit::where('stock_item_id', $itemId)->where('branch_id', $fromBranch)
            ->where('status', $fromStatus)->orderBy('id')->limit($qty)->lockForUpdate()->get();
        foreach ($units as $u) {
            $u->update(['status' => $toStatus, 'branch_id' => $toBranch ?? $fromBranch]);
        }

        return $units->count();
    }

    /** Sale: deduct quantity and retire units. */
    public static function sell(int $itemId, int $branchId, int $qty, int $billId, ?int $userId): void
    {
        self::change($itemId, $branchId, -$qty, 'sale', null, 'frame_bill', $billId, $userId);
        self::moveUnits($itemId, $branchId, $qty, 'sold');
    }

    /** Return/void: add quantity back (only when restocking) and revive sold units. */
    public static function restock(int $itemId, int $branchId, int $qty, string $type, string $reason, string $refType, int $refId, ?int $userId): void
    {
        self::change($itemId, $branchId, $qty, $type, $reason, $refType, $refId, $userId);
        self::moveUnits($itemId, $branchId, $qty, 'in_stock', $branchId, 'sold');
    }

    /** Correction with a mandatory reason; never a silent edit. */
    public static function adjust(int $itemId, int $branchId, int $delta, string $reason, ?int $userId): int
    {
        $new = self::change($itemId, $branchId, $delta, 'adjustment', $reason, 'adjustment', null, $userId);
        if ($delta > 0) {
            for ($i = 0; $i < $delta; $i++) {
                self::newUnit($itemId, $branchId);
            }
        } else {
            self::moveUnits($itemId, $branchId, -$delta, 'sold');
        }

        return $new;
    }

    /** Items at or below their alert level. A brand rule beats a category rule. */
    public static function lowStock(?int $branchId = null): array
    {
        $rules = \App\Models\StockAlertRule::all();
        if ($rules->isEmpty()) {
            return [];
        }
        $items = StockItem::query()->with('category:id,name')->get(['id', 'name', 'brand', 'category_id', 'model_number']);
        $levels = StockLevel::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('stock_item_id, SUM(quantity) as qty')->groupBy('stock_item_id')->pluck('qty', 'stock_item_id');

        $out = [];
        foreach ($items as $item) {
            $rule = $rules->first(fn ($r) => $r->scope === 'brand' && $item->brand && strcasecmp($r->brand, $item->brand) === 0)
                ?? $rules->first(fn ($r) => $r->scope === 'category' && $r->category_id && $r->category_id == $item->category_id);
            if (! $rule) {
                continue;
            }
            $qty = (int) ($levels[$item->id] ?? 0);
            if ($qty <= $rule->threshold) {
                $out[] = [
                    'id' => $item->id, 'name' => $item->name, 'brand' => $item->brand,
                    'category' => $item->category->name ?? null, 'model_number' => $item->model_number,
                    'quantity' => $qty, 'threshold' => (int) $rule->threshold,
                ];
            }
        }
        usort($out, fn ($a, $b) => $a['quantity'] <=> $b['quantity']);

        return $out;
    }
}
