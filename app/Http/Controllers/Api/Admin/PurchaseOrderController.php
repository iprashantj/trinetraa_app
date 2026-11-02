<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockInward;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Purchase orders: draft → ordered → partial → received (or cancelled). Receiving goes through StockService::inward. */
class PurchaseOrderController extends Controller
{
    private array $lineRules = [
        'items' => 'required|array|min:1|max:200',
        'items.*.stock_item_id' => 'required|integer|exists:stock_items,id',
        'items.*.quantity' => 'required|integer|min:1|max:100000',
        'items.*.unit_cost' => 'nullable|numeric|min:0|max:10000000',
    ];

    private function number(): string
    {
        do {
            $n = 'PO-' . date('Ymd') . '-' . random_int(1000, 9999);
        } while (PurchaseOrder::where('po_number', $n)->exists());

        return $n;
    }

    /** Load a PO the caller may see (branch-pinned staff only get their own branch). */
    private function find($id, Request $request): ?PurchaseOrder
    {
        $po = PurchaseOrder::find($id);
        if (! $po || ($po->branch_id && ! Access::canUseBranch(Access::admin($request), (int) $po->branch_id))) {
            return null;
        }

        return $po;
    }

    private function present(PurchaseOrder $po, bool $withItems = false): PurchaseOrder
    {
        $items = PurchaseOrderItem::where('purchase_order_id', $po->id)->get();
        $po->setAttribute('supplier_name', $po->supplier_id ? Supplier::where('id', $po->supplier_id)->value('name') : null);
        $po->setAttribute('branch_name', $po->branch_id ? Branch::where('id', $po->branch_id)->value('name') : null);
        $po->setAttribute('qty_ordered', (int) $items->sum('quantity_ordered'));
        $po->setAttribute('qty_received', (int) $items->sum('quantity_received'));
        if ($withItems) {
            $names = StockItem::whereIn('id', $items->pluck('stock_item_id'))->get(['id', 'name', 'brand', 'model_number'])->keyBy('id');
            $items->each(function ($i) use ($names) {
                $n = $names[$i->stock_item_id] ?? null;
                $i->setAttribute('item_name', $n->name ?? '—');
                $i->setAttribute('brand', $n->brand ?? null);
                $i->setAttribute('model_number', $n->model_number ?? null);
                $i->setAttribute('outstanding', max(0, $i->quantity_ordered - $i->quantity_received));
            });
            $po->setAttribute('items', $items);
        }

        return $po;
    }

    private function syncItems(PurchaseOrder $po, array $lines): void
    {
        PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();
        $total = 0;
        foreach ($lines as $l) {
            $cost = (float) ($l['unit_cost'] ?? 0);
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id, 'stock_item_id' => $l['stock_item_id'],
                'quantity_ordered' => (int) $l['quantity'], 'unit_cost' => $cost,
            ]);
            $total += $cost * (int) $l['quantity'];
        }
        $po->update(['total' => round($total, 2)]);
    }

    public function index(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = PurchaseOrder::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('supplier_id'), fn ($q, $v) => $q->where('supplier_id', (int) $v))
            ->orderByDesc('id')->limit(300)->get();
        $rows->each(fn ($r) => $this->present($r));

        return $this->data($rows);
    }

    public function show(Request $request, $id)
    {
        $po = $this->find($id, $request);

        return $po ? $this->data($this->present($po, true)) : $this->notFound();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->lineRules + [
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'order_date' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'expected_date' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'notes' => 'nullable|string|max:2000',
            'place' => 'nullable|boolean',
        ]);
        $branch = Access::writeBranch($request, $data['branch_id'] ?? null);
        if (! $branch || ! Branch::find($branch)) {
            return $this->message('Choose the branch the order is for.', 422);
        }
        $po = DB::transaction(function () use ($data, $branch, $request) {
            $po = PurchaseOrder::create([
                'po_number' => $this->number(), 'supplier_id' => $data['supplier_id'] ?? null, 'branch_id' => $branch,
                'status' => ! empty($data['place']) ? 'ordered' : 'draft',
                'order_date' => $data['order_date'] ?? now()->toDateString(), 'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null, 'created_by' => Access::admin($request)['id'] ?? null,
            ]);
            $this->syncItems($po, $data['items']);

            return $po->fresh();
        });

        return $this->data($this->present($po, true), null, 201);
    }

    /** Only drafts can be edited. */
    public function update(Request $request, $id)
    {
        $po = $this->find($id, $request);
        if (! $po) {
            return $this->notFound();
        }
        if ($po->status !== 'draft') {
            return $this->message('Only draft orders can be edited.', 409);
        }
        $data = $request->validate($this->lineRules + [
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'order_date' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'expected_date' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'notes' => 'nullable|string|max:2000',
        ]);
        DB::transaction(function () use ($po, $data) {
            $po->update([
                'supplier_id' => $data['supplier_id'] ?? null, 'order_date' => $data['order_date'] ?? $po->order_date,
                'expected_date' => $data['expected_date'] ?? null, 'notes' => $data['notes'] ?? null,
            ]);
            $this->syncItems($po, $data['items']);
        });

        return $this->data($this->present($po->fresh(), true));
    }

    public function destroy(Request $request, $id)
    {
        $po = $this->find($id, $request);
        if (! $po) {
            return $this->notFound();
        }
        if ($po->status !== 'draft') {
            return $this->message('Only draft orders can be deleted. Cancel it instead.', 409);
        }
        PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();
        $po->delete();

        return $this->message('Deleted.');
    }

    public function place(Request $request, $id)
    {
        $po = $this->find($id, $request);
        if (! $po) {
            return $this->notFound();
        }
        if ($po->status !== 'draft') {
            return $this->message('Only a draft order can be placed.', 409);
        }
        $po->update(['status' => 'ordered', 'order_date' => $po->order_date ?: now()->toDateString()]);

        return $this->data($this->present($po->fresh(), true));
    }

    /** Cancel an order nothing has been received against. */
    public function cancel(Request $request, $id)
    {
        $po = $this->find($id, $request);
        if (! $po) {
            return $this->notFound();
        }
        if (! in_array($po->status, ['draft', 'ordered'], true)) {
            return $this->message('This order already has stock received against it and cannot be cancelled.', 409);
        }
        $po->update(['status' => 'cancelled']);

        return $this->data($this->present($po->fresh(), true));
    }

    /**
     * Receive goods: lines = [{id: <po item id>, quantity: n}] — each ≤ what is still outstanding.
     * Creates one stock_inwards row per line (linked to the PO) and unique-coded units via StockService::inward.
     */
    public function receive(Request $request, $id)
    {
        $data = $request->validate([
            'lines' => 'required|array|min:1|max:200',
            'lines.*.id' => 'required|integer',
            'lines.*.quantity' => 'required|integer|min:0|max:100000',
            'invoice_ref' => 'nullable|string|max:255',
            'inward_date' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        ]);
        $admin = Access::admin($request);
        if (! $this->find($id, $request)) {
            return $this->notFound();
        }

        $result = DB::transaction(function () use ($id, $data, $admin) {
            $po = PurchaseOrder::lockForUpdate()->find($id);
            if (! in_array($po->status, ['ordered', 'partial'], true)) {
                return ['error' => 'Only an ordered or partly received purchase order can receive stock.', 'code' => 409];
            }
            $items = PurchaseOrderItem::where('purchase_order_id', $po->id)->lockForUpdate()->get()->keyBy('id');
            $plan = [];
            foreach ($data['lines'] as $l) {
                $qty = (int) $l['quantity'];
                if ($qty === 0) {
                    continue;
                }
                $line = $items[(int) $l['id']] ?? null;
                if (! $line) {
                    return ['error' => 'A line does not belong to this order.', 'code' => 422];
                }
                $outstanding = $line->quantity_ordered - $line->quantity_received;
                if ($qty > $outstanding) {
                    $name = StockItem::where('id', $line->stock_item_id)->value('name');

                    return ['error' => "Cannot receive {$qty} of {$name}: only {$outstanding} still outstanding.", 'code' => 422];
                }
                $plan[] = [$line, $qty];
            }
            if (! $plan) {
                return ['error' => 'Enter a quantity to receive for at least one line.', 'code' => 422];
            }
            $codes = 0;
            foreach ($plan as [$line, $qty]) {
                $inward = StockInward::create([
                    'inward_date' => $data['inward_date'] ?? now()->toDateString(), 'branch_id' => $po->branch_id,
                    'supplier_id' => $po->supplier_id, 'stock_item_id' => $line->stock_item_id,
                    'units_per_pack' => 1, 'packs' => $qty, 'quantity' => $qty,
                    'invoice_ref' => $data['invoice_ref'] ?? null, 'notes' => 'Received against ' . $po->po_number,
                    'purchase_order_id' => $po->id, 'user_id' => $admin['id'] ?? null,
                ]);
                $codes += count(StockService::inward((int) $line->stock_item_id, (int) $po->branch_id, $qty, $inward->id, $admin['id'] ?? null));
                $line->update(['quantity_received' => $line->quantity_received + $qty]);
            }
            $complete = PurchaseOrderItem::where('purchase_order_id', $po->id)->whereColumn('quantity_received', '<', 'quantity_ordered')->doesntExist();
            $po->update(['status' => $complete ? 'received' : 'partial']);

            return ['po' => $po->fresh(), 'units' => $codes];
        });

        if (isset($result['error'])) {
            return $this->message($result['error'], $result['code']);
        }

        return $this->data($this->present($result['po'], true), ['units_created' => $result['units']]);
    }

    /** CSV of the order, for sending to the supplier. */
    public function csv(Request $request, $id)
    {
        $po = $this->find($id, $request);
        if (! $po) {
            return $this->notFound();
        }
        $this->present($po, true);
        $safe = fn ($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : (string) $v; // spreadsheet formula guard
        $out = fopen('php://temp', 'w+');
        fputcsv($out, ['PO', $po->po_number, 'Supplier', $safe($po->supplier_name), 'Branch', $safe($po->branch_name), 'Status', $po->status]);
        fputcsv($out, ['Item', 'Brand', 'Model', 'Ordered', 'Received', 'Unit cost', 'Line total']);
        foreach ($po->items as $i) {
            fputcsv($out, [$safe($i->item_name), $safe($i->brand), $safe($i->model_number), $i->quantity_ordered, $i->quantity_received, $i->unit_cost, round($i->unit_cost * $i->quantity_ordered, 2)]);
        }
        fputcsv($out, ['', '', '', '', '', 'Total', $po->total]);
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="' . $po->po_number . '.csv"']);
    }
}
