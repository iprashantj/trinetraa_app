<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockInward;
use App\Models\StockItem;
use App\Models\StockLevel;
use App\Models\StockUnit;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockItemController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'model_number' => 'nullable|string|max:100',
        'brand' => 'nullable|string|max:255',
        'sku' => 'nullable|string|max:100',
        'barcode' => 'nullable|string|max:100',
        'pack_quantity' => 'nullable|integer|min:1|max:100000',
        'supplier_id' => 'nullable|integer|min:1',
        'category_id' => 'nullable|integer|min:1',
        'opening_stock' => 'sometimes|nullable|integer|min:0|max:100000',
        'branch_id' => 'nullable|integer|min:1',
        'sale_price' => 'sometimes|nullable|numeric|min:0',
        'cost_price' => 'nullable|numeric|min:0',
        'hsn_code' => 'nullable|string|max:12',
        'gst_rate' => 'nullable|numeric|min:0|max:100',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:500',
    ];

    /** Inventory table: search by code/brand/category/branch; quantity is for the chosen branch (or all). */
    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(500, (int) ($request->query('per_page') ?: 20)));
        $branchId = Access::branchScope($request);
        $search = trim((string) $request->query('search', ''));

        $q = StockItem::query()->with(['category:id,name']);
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $like = '%' . $search . '%';
                $w->where('name', 'like', $like)->orWhere('model_number', 'like', $like)
                    ->orWhere('sku', 'like', $like)->orWhere('brand', 'like', $like)->orWhere('barcode', 'like', $like);
            });
        }
        if ($request->query('brand')) {
            $q->where('brand', $request->query('brand'));
        }
        if ($request->query('category_id')) {
            $q->where('category_id', (int) $request->query('category_id'));
        }
        if ($branchId) {
            $q->whereIn('id', StockLevel::where('branch_id', $branchId)->where('quantity', '>', 0)->pluck('stock_item_id'));
        }
        $total = (clone $q)->count();
        $rows = $q->orderBy('name')->skip(($page - 1) * $perPage)->take($perPage)->get();

        $levels = StockLevel::whereIn('stock_item_id', $rows->pluck('id'))->get()->groupBy('stock_item_id');
        $low = collect(StockService::lowStock($branchId))->keyBy('id');
        $rows->each(function ($r) use ($levels, $branchId, $low) {
            $byBranch = ($levels[$r->id] ?? collect())->pluck('quantity', 'branch_id');
            $r->setAttribute('branch_stock', $byBranch);
            $r->setAttribute('quantity', $branchId ? (int) ($byBranch[$branchId] ?? 0) : (int) $byBranch->sum());
            $r->setAttribute('is_low', $low->has($r->id));
            $r->setAttribute('low_threshold', $low[$r->id]['threshold'] ?? null);
        });

        $sold = (int) StockUnit::where('status', 'sold')->when($branchId, fn ($x) => $x->where('branch_id', $branchId))->count();
        $totalQty = (int) StockLevel::when($branchId, fn ($x) => $x->where('branch_id', $branchId))->sum('quantity');

        return $this->data($rows, [
            'page' => $page, 'per_page' => $perPage, 'total' => $total,
            'total_quantity' => $totalQty, 'items_sold' => $sold, 'low_stock_count' => $low->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $this->validateUnique($request);
        $admin = Access::admin($request);
        $opening = (int) $request->input('opening_stock', 0);
        $branch = Access::writeBranch($request, $request->input('branch_id'));
        if ($opening > 0 && (! $branch || ! Branch::find($branch))) {
            return $this->message('Choose a branch for the opening stock.', 422);
        }

        $row = DB::transaction(function () use ($request, $admin, $opening, $branch) {
            $row = StockItem::create($this->payload($request) + ['current_stock' => 0]);
            if ($opening > 0) {
                $inward = StockInward::create([
                    'inward_date' => now()->toDateString(), 'branch_id' => $branch, 'supplier_id' => $row->supplier_id,
                    'stock_item_id' => $row->id, 'units_per_pack' => 1, 'packs' => $opening, 'quantity' => $opening,
                    'notes' => 'Opening stock', 'user_id' => $admin['id'] ?? null,
                ]);
                StockService::inward($row->id, $branch, $opening, $inward->id, $admin['id'] ?? null);
            }

            return $row->fresh();
        });

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = StockItem::with('category:id,name')->find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->setAttribute('branch_stock', StockLevel::where('stock_item_id', $row->id)->pluck('quantity', 'branch_id'));

        return $this->data($row);
    }

    /** Quantity is NOT editable here: it only changes through inward, transfer, sale, return or a reasoned adjustment. */
    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = StockItem::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $this->validateUnique($request, $row->id);
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = StockItem::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    public function lookup(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = StockItem::query()->orderBy('name')
            ->get(['id', 'name', 'brand', 'model_number', 'barcode', 'sale_price', 'cost_price', 'current_stock', 'pack_quantity', 'supplier_id', 'hsn_code', 'gst_rate']);
        $levels = StockLevel::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('stock_item_id, SUM(quantity) q')->groupBy('stock_item_id')->pluck('q', 'stock_item_id');
        $rows->each(fn ($r) => $r->setAttribute('branch_quantity', (int) ($levels[$r->id] ?? 0)));

        return $this->data($rows);
    }

    /** Scan / type: matches an item barcode, SKU, model code, or a unique unit code printed on a label. */
    public function scan(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return $this->message('Enter or scan a code.', 422);
        }
        $unit = StockUnit::where('unique_code', strtoupper($code))->first();
        $item = $unit
            ? StockItem::find($unit->stock_item_id)
            : StockItem::where('barcode', $code)->orWhere('sku', $code)->orWhere('model_number', $code)->first();
        if (! $item) {
            return $this->notFound('No product matches this code.');
        }
        $branchId = Access::branchScope($request);
        $item->setAttribute('branch_stock', StockLevel::where('stock_item_id', $item->id)->pluck('quantity', 'branch_id'));
        $item->setAttribute('branch_quantity', $branchId ? StockService::level($item->id, $branchId) : (int) $item->current_stock);
        $item->setAttribute('unit', $unit);

        return $this->data($item);
    }

    /** Units (with their unique codes) for printing QR labels. */
    public function units(Request $request, $id)
    {
        $branchId = Access::branchScope($request);

        return $this->data(StockUnit::where('stock_item_id', $id)->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')->limit(500)->get(['id', 'unique_code', 'branch_id', 'status']));
    }

    private function validateUnique(Request $request, ?int $ignore = null): void
    {
        $rules = [];
        if ($request->filled('sku')) {
            $rules['sku'] = 'unique:stock_items,sku' . ($ignore ? ",$ignore" : '');
        }
        if ($request->filled('barcode')) {
            $rules['barcode'] = 'unique:stock_items,barcode' . ($ignore ? ",$ignore" : '');
        }
        if ($rules) {
            $request->validate($rules);
        }
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'model_number' => $request->input('model_number'),
            'brand' => $request->input('brand'),
            'sku' => $request->input('sku') ?: null,
            'barcode' => $request->input('barcode') ?: null,
            'pack_quantity' => (int) ($request->input('pack_quantity') ?: 1),
            'supplier_id' => $request->input('supplier_id'),
            'category_id' => $request->input('category_id'),
            'sale_price' => (float) ($request->input('sale_price') ?: 0),
            'cost_price' => $request->input('cost_price'),
            'hsn_code' => $request->input('hsn_code') ?: null,
            'gst_rate' => $request->filled('gst_rate') ? (float) $request->input('gst_rate') : null,
            'description' => $request->input('description'),
            'image' => $request->input('image'),
        ];
    }
}
