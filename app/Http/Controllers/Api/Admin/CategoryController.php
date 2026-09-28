<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:500',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 100);
        $skip = ($page - 1) * $perPage;

        $rows = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('eyewears')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = Category::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Category::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = Category::find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Category::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Category::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    private function payload(Request $request): array
    {
        $slug = trim((string) $request->input('slug', '')) ?: Slug::generate($request->input('name'));

        return [
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }
}
