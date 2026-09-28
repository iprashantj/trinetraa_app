<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eyewear;
use App\Support\Slug;
use Illuminate\Http\Request;

class EyewearController extends Controller
{
    private array $rules = [
        'category_id' => 'required|integer|min:1',
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string',
        'description' => 'nullable|string',
        'price' => 'required|numeric|gt:0',
        'discount_percentage' => 'sometimes|integer|min:0|max:100',
        'image' => 'nullable|string|max:500',
        'brand' => 'nullable|string|max:255',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
        'amazon_url' => 'nullable|string|max:500',
        'flipkart_url' => 'nullable|string|max:500',
        'amazon_enabled' => 'sometimes|boolean',
        'flipkart_enabled' => 'sometimes|boolean',
        'availability' => 'sometimes|in:in_store,amazon,flipkart,both,out_of_stock',
        'marketplace_sku' => 'nullable|string|max:255',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 100);
        $categoryId = $request->query('category_id');
        $skip = ($page - 1) * $perPage;

        $query = Eyewear::query();
        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        $countQuery = clone $query;

        $rows = $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->with(['category:id,name,slug'])
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = $countQuery->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Eyewear::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = Eyewear::with(['category:id,name,slug'])->find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Eyewear::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Eyewear::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    private function payload(Request $request): array
    {
        $slug = trim((string) $request->input('slug', '')) ?: Slug::generate($request->input('name'));

        return [
            'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'discount_percentage' => (int) $request->input('discount_percentage', 0),
            'image' => $request->input('image'),
            'brand' => $request->input('brand'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'amazon_url' => $request->input('amazon_url'),
            'flipkart_url' => $request->input('flipkart_url'),
            'amazon_enabled' => $request->boolean('amazon_enabled'),
            'flipkart_enabled' => $request->boolean('flipkart_enabled'),
            'availability' => $request->input('availability', 'in_store') ?: 'in_store',
            'marketplace_sku' => $request->input('marketplace_sku'),
        ];
    }
}
