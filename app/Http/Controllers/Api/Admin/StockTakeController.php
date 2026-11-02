<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockItem;
use App\Models\StockLevel;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\StockUnit;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Physical stock count for one branch. Variance = counted − what the system holds now;
 * completing the take applies that difference through StockService::adjust (reason "Stock take #id").
 */
class StockTakeController extends Controller
{
    private function find($id, Request $request): ?StockTake
    {
        $t = StockTake::find($id);
        if (! $t || ! Access::canUseBranch(Access::admin($request), (int) $t->branch_id)) {
            return null;
        }

        return $t;
    }

    private function present(StockTake $t, bool $withItems = false): StockTake
    {
        $rows = StockTakeItem::where('stock_take_id', $t->id)->get();
        $t->setAttribute('branch_name', Branch::where('id', $t->branch_id)->value('name'));
        $t->setAttribute('items_total', $rows->count());
        $t->setAttribute('items_counted', $rows->whereNotNull('counted_qty')->count());
        if ($t->status === 'open') {
            $now = StockLevel::where('branch_id', $t->branch_id)->whereIn('stock_item_id', $rows->pluck('stock_item_id'))->pluck('quantity', 'stock_item_id');
        }
        $variance = 0;
        $diffItems = 0;
        $items = collect();
        {
            $names = StockItem::whereIn('id', $rows->pluck('stock_item_id'))->get(['id', 'name', 'brand', 'model_number', 'barcode'])->keyBy('id');
            foreach ($rows as $r) {
                $n = $names[$r->stock_item_id] ?? null;
                $current = isset($now) ? (int) ($now[$r->stock_item_id] ?? 0) : (int) $r->system_qty;
                $r->setAttribute('item_name', $n->name ?? '—');
                $r->setAttribute('brand', $n->brand ?? null);
                $r->setAttribute('model_number', $n->model_number ?? null);
                $r->setAttribute('system_now', $current);
                $var = $r->counted_qty === null ? null : (int) $r->counted_qty - $current;
                $r->setAttribute('variance', $var);
                if ($var) {
                    $variance += $var;
                    $diffItems++;
                }
                $items->push($r);
            }
        }
        $t->setAttribute('variance_total', $variance);
        $t->setAttribute('items_with_variance', $diffItems);
        if ($withItems) {
            $t->setAttribute('items', $items->sortBy('item_name', SORT_NATURAL | SORT_FLAG_CASE)->values());
        }

        return $t;
    }

    public function index(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = StockTake::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id')->limit(100)->get();
        $rows->each(fn ($r) => $this->present($r));

        return $this->data($rows);
    }

    public function show(Request $request, $id)
    {
        $t = $this->find($id, $request);

        return $t ? $this->data($this->present($t, true)) : $this->notFound();
    }

    /** Start a take: snapshot every item with stock at the branch, or only the chosen item ids. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'item_ids' => 'nullable|array|max:2000',
            'item_ids.*' => 'integer|exists:stock_items,id',
            'notes' => 'nullable|string|max:2000',
        ]);
        $branch = Access::writeBranch($request, $data['branch_id'] ?? null);
        if (! $branch || ! Branch::find($branch)) {
            return $this->message('Choose a branch.', 422);
        }
        $result = DB::transaction(function () use ($branch, $data, $request) {
            // serialise per branch so two people cannot both open a take
            Branch::where('id', $branch)->lockForUpdate()->first();
            if (StockTake::where('branch_id', $branch)->where('status', 'open')->exists()) {
                return null;
            }
            $levels = StockLevel::where('branch_id', $branch)
                ->when(! empty($data['item_ids']), fn ($q) => $q->whereIn('stock_item_id', $data['item_ids']), fn ($q) => $q->where('quantity', '>', 0))
                ->pluck('quantity', 'stock_item_id');
            $ids = ! empty($data['item_ids']) ? array_unique($data['item_ids']) : $levels->keys()->all();
            if (! $ids) {
                return false;
            }
            $take = StockTake::create(['branch_id' => $branch, 'status' => 'open', 'notes' => $data['notes'] ?? null, 'created_by' => Access::admin($request)['id'] ?? null]);
            foreach ($ids as $itemId) {
                StockTakeItem::create(['stock_take_id' => $take->id, 'stock_item_id' => $itemId, 'system_qty' => (int) ($levels[$itemId] ?? 0)]);
            }

            return $take;
        });
        if ($result === null) {
            return $this->message('A stock take is already open for this branch. Complete or cancel it first.', 409);
        }
        if ($result === false) {
            return $this->message('This branch has no stock to count.', 422);
        }

        return $this->data($this->present($result, true), null, 201);
    }

    /** Type in a count for one line (null clears it). */
    public function setCount(Request $request, $id, $itemId)
    {
        $data = $request->validate(['counted_qty' => 'nullable|integer|min:0|max:1000000', 'note' => 'nullable|string|max:255']);
        $t = $this->find($id, $request);
        if (! $t) {
            return $this->notFound();
        }
        if ($t->status !== 'open') {
            return $this->message('This stock take is closed.', 409);
        }
        $row = StockTakeItem::where('stock_take_id', $t->id)->where('stock_item_id', $itemId)->first();
        if (! $row) {
            return $this->notFound('Item is not part of this stock take.');
        }
        $row->update(['counted_qty' => $data['counted_qty'] ?? null] + (array_key_exists('note', $data) ? ['note' => $data['note']] : []));

        return $this->data($this->present($t, true));
    }

    /** Scan a unit QR code / barcode / model code: adds 1 to that item's count (adds the item if it was not in the snapshot). */
    public function scan(Request $request, $id)
    {
        $data = $request->validate(['code' => 'required|string|max:100', 'amount' => 'nullable|integer|min:1|max:1000']);
        $t = $this->find($id, $request);
        if (! $t) {
            return $this->notFound();
        }
        if ($t->status !== 'open') {
            return $this->message('This stock take is closed.', 409);
        }
        $code = trim($data['code']);
        $unit = StockUnit::where('unique_code', strtoupper($code))->first();
        $item = $unit ? StockItem::find($unit->stock_item_id)
            : StockItem::where('barcode', $code)->orWhere('sku', $code)->orWhere('model_number', $code)->first();
        if (! $item) {
            return $this->notFound('No product matches this code.');
        }
        DB::transaction(function () use ($t, $item, $data) {
            $row = StockTakeItem::where('stock_take_id', $t->id)->where('stock_item_id', $item->id)->lockForUpdate()->first();
            if (! $row) {
                $row = StockTakeItem::create([
                    'stock_take_id' => $t->id, 'stock_item_id' => $item->id,
                    'system_qty' => StockService::level($item->id, (int) $t->branch_id), 'note' => 'Added during count',
                ]);
            }
            $row->update(['counted_qty' => (int) $row->counted_qty + (int) ($data['amount'] ?? 1)]);
        });

        return $this->data(['item' => $item->only(['id', 'name', 'brand', 'model_number']), 'take' => $this->present($t->fresh(), true)]);
    }

    /**
     * Apply the counted differences. Uncounted lines block completion unless
     * ignore_uncounted=true, in which case they are left unchanged.
     */
    public function complete(Request $request, $id)
    {
        $data = $request->validate(['ignore_uncounted' => 'nullable|boolean']);
        $admin = Access::admin($request);
        $t = $this->find($id, $request);
        if (! $t) {
            return $this->notFound();
        }

        $result = DB::transaction(function () use ($t, $data, $admin) {
            $take = StockTake::lockForUpdate()->find($t->id);
            if ($take->status !== 'open') {
                return ['error' => 'This stock take is already closed.', 'code' => 409];
            }
            $rows = StockTakeItem::where('stock_take_id', $take->id)->lockForUpdate()->get();
            $uncounted = $rows->whereNull('counted_qty')->count();
            if ($uncounted > 0 && empty($data['ignore_uncounted'])) {
                return ['error' => "{$uncounted} item(s) have not been counted. Count them, or choose to leave uncounted items unchanged.", 'code' => 422, 'uncounted' => $uncounted];
            }
            $adjusted = 0;
            foreach ($rows->whereNotNull('counted_qty') as $r) {
                $delta = (int) $r->counted_qty - StockService::level((int) $r->stock_item_id, (int) $take->branch_id);
                if ($delta !== 0) {
                    StockService::adjust((int) $r->stock_item_id, (int) $take->branch_id, $delta, 'Stock take #' . $take->id, $admin['id'] ?? null);
                    $adjusted++;
                }
            }
            $take->update(['status' => 'completed', 'completed_at' => now()]);

            return ['take' => $take->fresh(), 'adjusted' => $adjusted, 'uncounted' => $uncounted];
        });
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'uncounted' => $result['uncounted'] ?? null], $result['code']);
        }

        return $this->data($this->present($result['take'], true), ['items_adjusted' => $result['adjusted'], 'left_uncounted' => $result['uncounted']]);
    }

    public function cancel(Request $request, $id)
    {
        $t = $this->find($id, $request);
        if (! $t) {
            return $this->notFound();
        }
        if ($t->status !== 'open') {
            return $this->message('Only an open stock take can be cancelled.', 409);
        }
        $t->update(['status' => 'cancelled']);

        return $this->data($this->present($t->fresh()));
    }
}
