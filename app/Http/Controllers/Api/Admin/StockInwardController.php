<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockAlertRule;
use App\Models\StockInward;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Stock inward entries, reasoned corrections, the stock ledger and low-stock alert rules. */
class StockInwardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = StockInward::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')->limit(200)->get();
        $items = StockItem::whereIn('id', $rows->pluck('stock_item_id'))->pluck('name', 'id');
        $branches = Branch::pluck('name', 'id');
        $suppliers = \App\Models\Supplier::pluck('name', 'id');
        $rows->each(function ($r) use ($items, $branches, $suppliers) {
            $r->setAttribute('item_name', $items[$r->stock_item_id] ?? '—');
            $r->setAttribute('branch_name', $branches[$r->branch_id] ?? '—');
            $r->setAttribute('supplier_name', $suppliers[$r->supplier_id] ?? null);
        });

        return $this->data($rows);
    }

    /**
     * Pack quantity: send units_per_pack × packs and the total is filled in automatically,
     * or send "quantity" directly to type it in manually.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'stock_item_id' => 'required|integer|exists:stock_items,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'inward_date' => ['required', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'units_per_pack' => 'nullable|integer|min:1|max:100000',
            'packs' => 'nullable|integer|min:1|max:100000',
            'quantity' => 'nullable|integer|min:1|max:100000',
            'invoice_ref' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);
        $admin = Access::admin($request);
        $branch = Access::writeBranch($request, $data['branch_id'] ?? null);
        if (! $branch || ! Branch::find($branch)) {
            return $this->message('Choose the receiving branch.', 422);
        }
        $per = (int) ($data['units_per_pack'] ?? 1);
        $packs = (int) ($data['packs'] ?? 1);
        $qty = (int) ($data['quantity'] ?? ($per * $packs));
        if ($qty < 1) {
            return $this->message('Quantity must be at least 1.', 422);
        }

        $result = DB::transaction(function () use ($data, $branch, $per, $packs, $qty, $admin) {
            $inward = StockInward::create([
                'inward_date' => $data['inward_date'], 'branch_id' => $branch, 'supplier_id' => $data['supplier_id'] ?? null,
                'stock_item_id' => $data['stock_item_id'], 'units_per_pack' => $per, 'packs' => $packs, 'quantity' => $qty,
                'invoice_ref' => $data['invoice_ref'] ?? null, 'notes' => $data['notes'] ?? null, 'user_id' => $admin['id'] ?? null,
            ]);
            $codes = StockService::inward((int) $data['stock_item_id'], $branch, $qty, $inward->id, $admin['id'] ?? null);

            return ['inward' => $inward, 'unit_codes' => $codes];
        });

        return $this->data($result, null, 201);
    }

    /** Correction: needs a reason, changes stock through the ledger, never edits silently. */
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'stock_item_id' => 'required|integer|exists:stock_items,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'delta' => 'required|integer|not_in:0|min:-100000|max:100000',
            'reason' => 'required|string|min:3|max:255',
        ]);
        $admin = Access::admin($request);
        $branch = Access::writeBranch($request, $data['branch_id'] ?? null);
        if (! $branch || ! Branch::find($branch)) {
            return $this->message('Choose a branch.', 422);
        }
        $new = DB::transaction(fn () => StockService::adjust((int) $data['stock_item_id'], $branch, (int) $data['delta'], $data['reason'], $admin['id'] ?? null));

        return $this->data(['quantity' => $new]);
    }

    public function movements(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = StockMovement::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->query('stock_item_id'), fn ($q, $v) => $q->where('stock_item_id', (int) $v))
            ->when($request->query('type'), fn ($q, $v) => $q->where('type', $v))
            ->orderByDesc('id')->limit(300)->get();
        $items = StockItem::whereIn('id', $rows->pluck('stock_item_id'))->pluck('name', 'id');
        $branches = Branch::pluck('name', 'id');
        $users = \App\Models\User::whereIn('id', $rows->pluck('user_id')->filter())->pluck('name', 'id');
        $rows->each(function ($r) use ($items, $branches, $users) {
            $r->setAttribute('item_name', $items[$r->stock_item_id] ?? '—');
            $r->setAttribute('branch_name', $branches[$r->branch_id] ?? '—');
            $r->setAttribute('user_name', $users[$r->user_id] ?? null);
        });

        return $this->data($rows);
    }

    // ── Low-stock alert rules (per brand or per category) ─────────────────────
    public function rules()
    {
        $rows = StockAlertRule::orderBy('scope')->get();
        $cats = \App\Models\Category::pluck('name', 'id');
        $rows->each(fn ($r) => $r->setAttribute('category_name', $cats[$r->category_id] ?? null));

        return $this->data($rows);
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'scope' => 'required|in:brand,category',
            'brand' => 'required_if:scope,brand|nullable|string|max:255',
            'category_id' => 'required_if:scope,category|nullable|integer|exists:categories,id',
            'threshold' => 'required|integer|min:0|max:100000',
        ]);
        $existing = StockAlertRule::where('scope', $data['scope'])
            ->when($data['scope'] === 'brand', fn ($q) => $q->where('brand', $data['brand']), fn ($q) => $q->where('category_id', $data['category_id']))
            ->first();
        if ($existing) {
            $existing->update(['threshold' => $data['threshold']]);

            return $this->data($existing);
        }

        return $this->data(StockAlertRule::create([
            'scope' => $data['scope'],
            'brand' => $data['scope'] === 'brand' ? $data['brand'] : null,
            'category_id' => $data['scope'] === 'category' ? $data['category_id'] : null,
            'threshold' => $data['threshold'],
        ]), null, 201);
    }

    public function destroyRule($id)
    {
        StockAlertRule::where('id', $id)->delete();

        return $this->message('Deleted.');
    }

    public function alerts(Request $request)
    {
        return $this->data(StockService::lowStock(Access::branchScope($request)));
    }
}
