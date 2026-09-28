<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'logo' => 'nullable|string|max:500',
        'story' => 'nullable|string',
        'description' => 'nullable|string',
        'website' => 'nullable|string|max:500',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
    ];

    public function index()
    {
        $rows = Brand::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->data($rows);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Brand::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Brand::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Brand::find($id);
        if ($row) {
            $row->delete();
        }

        return response()->json(['success' => true]);
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'logo' => $request->input('logo'),
            'story' => $request->input('story'),
            'description' => $request->input('description'),
            'website' => $request->input('website'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }
}
