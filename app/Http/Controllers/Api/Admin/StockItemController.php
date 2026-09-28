<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use Illuminate\Http\Request;

class StockItemController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'model_number' => 'nullable|string|max:100',
        'sku' => 'nullable|string|max:100',
        'category_id' => 'nullable|integer|min:1',
        'current_stock' => 'sometimes|integer|min:0',
        'sale_price' => 'sometimes|numeric|min:0',
        'cost_price' => 'nullable|numeric|min:0',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:500',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = StockItem::query()
            ->orderByDesc('created_at')
            ->with(['category:id,name'])
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = StockItem::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = StockItem::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = StockItem::find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = StockItem::find($id);
        if (! $row) {
            return $this->notFound();
        }
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

    public function lookup()
    {
        $rows = StockItem::query()
            ->orderBy('name')
            ->get(['id', 'name', 'model_number', 'sale_price', 'current_stock']);

        return $this->data($rows);
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'model_number' => $request->input('model_number'),
            'sku' => $request->input('sku'),
            'category_id' => $request->input('category_id'),
            'current_stock' => (int) $request->input('current_stock', 0),
            'sale_price' => (float) $request->input('sale_price', 0),
            'cost_price' => $request->input('cost_price'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
        ];
    }
}
