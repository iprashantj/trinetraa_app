<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    private array $rules = [
        'type' => 'required|in:image,video',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:500',
        'embed_url' => 'nullable|string|max:500',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 50);
        $skip = ($page - 1) * $perPage;

        $rows = GalleryItem::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = GalleryItem::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = GalleryItem::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = GalleryItem::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = GalleryItem::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    private function payload(Request $request): array
    {
        return [
            'type' => $request->input('type'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'embed_url' => $request->input('embed_url'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }
}
