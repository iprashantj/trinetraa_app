<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Branch-to-branch transfers: draft → in_transit (source stock drops) → received (destination stock rises).
 * Units keep their unique codes, so nothing is rescanned.
 */
class StockTransferController extends Controller
{
    private function hydrate($rows)
    {
        $branches = Branch::pluck('name', 'id');
        $rows->each(function ($t) use ($branches) {
            $t->setAttribute('from_name', $branches[$t->from_branch_id] ?? '—');
            $t->setAttribute('to_name', $branches[$t->to_branch_id] ?? '—');
            $items = StockTransferItem::where('stock_transfer_id', $t->id)->get();
            $names = StockItem::whereIn('id', $items->pluck('stock_item_id'))->pluck('name', 'id');
            $items->each(fn ($i) => $i->setAttribute('item_name', $names[$i->stock_item_id] ?? '—'));
            $t->setAttribute('items', $items);
        });

        return $rows;
    }

    public function index(Request $request)
    {
        $admin = Access::admin($request);
        $q = StockTransfer::query()->orderByDesc('id')->limit(200);
        // Branch staff see transfers that leave or arrive at their branch.
        $pinned = ! Access::isOwner($admin) && ! empty($admin['branch_id']) ? (int) $admin['branch_id'] : null;
        if ($pinned) {
            $q->where(fn ($w) => $w->where('from_branch_id', $pinned)->orWhere('to_branch_id', $pinned));
        }
        if ($request->query('status')) {
            $q->where('status', $request->query('status'));
        }

        return $this->data($this->hydrate($q->get()));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_branch_id' => 'required|integer|exists:branches,id|different:to_branch_id',
            'to_branch_id' => 'required|integer|exists:branches,id',
            'notes' => 'nullable|string|max:2000',
            'force' => 'sometimes|boolean',
            'dispatch' => 'sometimes|boolean',
            'items' => 'required|array|min:1',
            'items.*.stock_item_id' => 'required|integer|exists:stock_items,id|distinct',
            'items.*.quantity' => 'required|integer|min:1|max:100000',
        ]);
        $admin = Access::admin($request);
        if (! Access::canUseBranch($admin, (int) $data['from_branch_id'])) {
            return $this->message('You can only transfer stock out of your own branch.', 403);
        }

        // Safeguard 1: cannot move more than is available at the source.
        foreach ($data['items'] as $i => $line) {
            $have = StockService::level((int) $line['stock_item_id'], (int) $data['from_branch_id']);
            $reserved = $this->reservedInDrafts((int) $line['stock_item_id'], (int) $data['from_branch_id']);
            if ($line['quantity'] > $have) {
                throw ValidationException::withMessages(["items.$i.quantity" => "Only {$have} available at the source branch."]);
            }
            if ($line['quantity'] + $reserved > $have) {
                throw ValidationException::withMessages(["items.$i.quantity" => "{$reserved} of the {$have} available are already in other open transfers."]);
            }
        }
        // Safeguard 2: block an identical transfer created in the last 10 minutes (double-click / resubmit).
        if (empty($data['force']) && $this->isDuplicate($data)) {
            return response()->json(['message' => 'An identical transfer was created a moment ago. Open it from the list, or resubmit with confirmation if this is intentional.', 'duplicate' => true], 409);
        }

        $transfer = DB::transaction(function () use ($data, $admin) {
            $t = StockTransfer::create([
                'transfer_number' => 'TR-' . date('ymd') . '-' . random_int(1000, 9999),
                'from_branch_id' => $data['from_branch_id'], 'to_branch_id' => $data['to_branch_id'],
                'status' => 'draft', 'notes' => $data['notes'] ?? null, 'created_by' => $admin['id'] ?? null,
            ]);
            foreach ($data['items'] as $line) {
                StockTransferItem::create(['stock_transfer_id' => $t->id, 'stock_item_id' => $line['stock_item_id'], 'quantity' => $line['quantity']]);
            }

            return $t;
        });
        if (! empty($data['dispatch'])) {
            $this->doDispatch($transfer, $admin);
        }

        return $this->data($this->hydrate(collect([$transfer->fresh()]))->first(), null, 201);
    }

    public function dispatch(Request $request, $id)
    {
        $t = StockTransfer::find($id);
        if (! $t) {
            return $this->notFound();
        }
        if ($t->status !== 'draft') {
            return $this->message("Only draft transfers can be dispatched (this one is {$t->status}).", 422);
        }
        $admin = Access::admin($request);
        if (! Access::canUseBranch($admin, (int) $t->from_branch_id)) {
            return $this->message('Only the sending branch can dispatch.', 403);
        }
        $this->doDispatch($t, $admin);

        return $this->data($this->hydrate(collect([$t->fresh()]))->first());
    }

    public function receive(Request $request, $id)
    {
        $t = StockTransfer::find($id);
        if (! $t) {
            return $this->notFound();
        }
        if ($t->status !== 'in_transit') {
            return $this->message("Only transfers in transit can be received (this one is {$t->status}).", 422);
        }
        $admin = Access::admin($request);
        if (! Access::canUseBranch($admin, (int) $t->to_branch_id)) {
            return $this->message('Only the receiving branch can confirm receipt.', 403);
        }
        DB::transaction(function () use ($t, $admin) {
            foreach (StockTransferItem::where('stock_transfer_id', $t->id)->get() as $line) {
                StockService::change($line->stock_item_id, $t->to_branch_id, $line->quantity, 'transfer_in', "From transfer {$t->transfer_number}", 'stock_transfer', $t->id, $admin['id'] ?? null);
                // Units keep their codes; they were parked "in_transit" at the source branch id.
                StockService::moveUnits($line->stock_item_id, $t->from_branch_id, $line->quantity, 'in_stock', $t->to_branch_id, 'in_transit');
            }
            $t->update(['status' => 'received', 'received_by' => $admin['id'] ?? null, 'received_at' => now()]);
        });

        return $this->data($this->hydrate(collect([$t->fresh()]))->first());
    }

    /** Cancel a draft freely; cancel an in-transit transfer to return the stock to the source. */
    public function cancel(Request $request, $id)
    {
        $t = StockTransfer::find($id);
        if (! $t) {
            return $this->notFound();
        }
        if (! in_array($t->status, ['draft', 'in_transit'], true)) {
            return $this->message("A {$t->status} transfer cannot be cancelled.", 422);
        }
        $admin = Access::admin($request);
        DB::transaction(function () use ($t, $admin) {
            if ($t->status === 'in_transit') {
                foreach (StockTransferItem::where('stock_transfer_id', $t->id)->get() as $line) {
                    StockService::change($line->stock_item_id, $t->from_branch_id, $line->quantity, 'transfer_in', "Cancelled transfer {$t->transfer_number}", 'stock_transfer', $t->id, $admin['id'] ?? null);
                    StockService::moveUnits($line->stock_item_id, $t->from_branch_id, $line->quantity, 'in_stock', $t->from_branch_id, 'in_transit');
                }
            }
            $t->update(['status' => 'cancelled']);
        });

        return $this->data($this->hydrate(collect([$t->fresh()]))->first());
    }

    private function doDispatch(StockTransfer $t, array $admin): void
    {
        DB::transaction(function () use ($t, $admin) {
            foreach (StockTransferItem::where('stock_transfer_id', $t->id)->get() as $line) {
                StockService::change($line->stock_item_id, $t->from_branch_id, -$line->quantity, 'transfer_out', "To transfer {$t->transfer_number}", 'stock_transfer', $t->id, $admin['id'] ?? null);
                StockService::moveUnits($line->stock_item_id, $t->from_branch_id, $line->quantity, 'in_transit');
            }
            $t->update(['status' => 'in_transit', 'dispatched_by' => $admin['id'] ?? null, 'dispatched_at' => now()]);
        });
    }

    private function reservedInDrafts(int $itemId, int $branchId): int
    {
        return (int) StockTransferItem::query()
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_items.stock_transfer_id')
            ->where('stock_transfers.status', 'draft')->where('stock_transfers.from_branch_id', $branchId)
            ->where('stock_transfer_items.stock_item_id', $itemId)->sum('stock_transfer_items.quantity');
    }

    private function isDuplicate(array $data): bool
    {
        $sig = collect($data['items'])->sortBy('stock_item_id')->map(fn ($l) => $l['stock_item_id'] . 'x' . $l['quantity'])->implode(',');
        $recent = StockTransfer::where('from_branch_id', $data['from_branch_id'])->where('to_branch_id', $data['to_branch_id'])
            ->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->subMinutes(10))->get();
        foreach ($recent as $r) {
            $rs = StockTransferItem::where('stock_transfer_id', $r->id)->orderBy('stock_item_id')->get()
                ->map(fn ($l) => $l->stock_item_id . 'x' . $l->quantity)->implode(',');
            if ($rs === $sig) {
                return true;
            }
        }

        return false;
    }
}
