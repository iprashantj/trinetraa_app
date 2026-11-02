<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Eyewear;
use Illuminate\Http\Request;

class EyewearController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(500, (int) ($request->query('per_page') ?: 12)));
        $skip = ($page - 1) * $perPage;
        $categorySlug = $request->query('category_slug');
        $brand = $request->query('brand');
        $minPrice = $request->query('min_price') !== null ? (float) $request->query('min_price') : null;
        $maxPrice = $request->query('max_price') !== null ? (float) $request->query('max_price') : null;
        $sortParam = $request->query('sort');

        $query = Eyewear::query()->where('is_active', true);

        if ($categorySlug) {
            $cat = Category::where('slug', $categorySlug)->first();
            if (! $cat) {
                return $this->notFound('Category not found.');
            }
            $query->where('category_id', $cat->id);
        }

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $countQuery = clone $query;

        if ($sortParam === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sortParam === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sortParam === 'name_asc') {
            $query->orderBy('name');
        } else {
            $query->orderBy('sort_order')->orderByDesc('created_at');
        }

        $rows = $query
            ->with(['category:id,name,slug'])
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = $countQuery->count();
        $this->withStock($rows);

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function show($slug)
    {
        $row = Eyewear::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['category:id,name,slug'])
            ->first();

        if (! $row) {
            return $this->notFound('Product not found.');
        }

        $this->withStock(collect([$row]));

        return $this->data($row);
    }

    /**
     * Products linked to an ERP stock item show live availability. In-store quantity per branch is
     * summarised as text ("In stock · Nashik · Mumbai"); exact counts are never exposed publicly.
     */
    private function withStock($rows): void
    {
        $linked = $rows->filter(fn ($r) => $r->stock_item_id);
        if ($linked->isEmpty()) {
            return;
        }
        $levels = \App\Models\StockLevel::whereIn('stock_item_id', $linked->pluck('stock_item_id'))->where('quantity', '>', 0)->get()->groupBy('stock_item_id');
        $branches = \App\Models\Branch::where('is_active', true)->pluck('name', 'id');
        $held = \App\Models\Reservation::active()->whereIn('stock_item_id', $linked->pluck('stock_item_id'))
            ->selectRaw('stock_item_id, branch_id, SUM(quantity) q')->groupBy('stock_item_id', 'branch_id')->get()
            ->mapWithKeys(fn ($h) => [$h->stock_item_id . ':' . $h->branch_id => (int) $h->q]);
        foreach ($linked as $r) {
            // Branches where at least one unit is free to reserve for pickup (names only, never counts).
            $r->setAttribute('reservable_branches', ($levels[$r->stock_item_id] ?? collect())
                ->filter(fn ($l) => isset($branches[$l->branch_id]) && $l->quantity - ($held[$r->stock_item_id . ':' . $l->branch_id] ?? 0) > 0)
                ->map(fn ($l) => ['id' => (int) $l->branch_id, 'name' => $branches[$l->branch_id]])->values());
            $where = ($levels[$r->stock_item_id] ?? collect())->map(fn ($l) => $branches[$l->branch_id] ?? null)->filter()->values();
            $r->setAttribute('stock_text', $where->isEmpty() ? 'Out of stock' : ($where->count() === $branches->count() && $branches->count() > 1 ? 'In stock · All branches' : 'In stock · ' . $where->implode(' · ')));
            if ($where->isEmpty() && $r->availability === 'in_store') {
                $r->setAttribute('availability', 'out_of_stock');
            } elseif ($where->isNotEmpty() && $r->availability === 'out_of_stock') {
                $r->setAttribute('availability', 'in_store');
            }
        }
    }
}
