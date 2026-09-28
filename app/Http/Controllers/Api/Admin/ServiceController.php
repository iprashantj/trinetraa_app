<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private array $rules = [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:100',
        'price' => 'nullable|string|max:100',
        'duration' => 'nullable|string|max:100',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
    ];

    public function index()
    {
        $rows = Service::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return $this->data($rows);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Service::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Service::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Service::find($id);
        if ($row) {
            $row->delete();
        }

        return response()->json(['success' => true]);
    }

    private function payload(Request $request): array
    {
        return [
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'icon' => $request->input('icon'),
            'price' => $request->input('price'),
            'duration' => $request->input('duration'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }
}
