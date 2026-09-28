<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    private array $rules = [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'excerpt' => 'nullable|string',
        'content' => 'required|string',
        'image' => 'nullable|string|max:500',
        'is_published' => 'sometimes|boolean',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:500',
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = BlogPost::query()
            ->orderByDesc('created_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = BlogPost::count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        $isPublished = $request->boolean('is_published');
        $row = BlogPost::create([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'excerpt' => $request->input('excerpt'),
            'content' => $request->input('content'),
            'image' => $request->input('image'),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
        ]);

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = BlogPost::find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = BlogPost::find($id);
        if (! $row) {
            return $this->notFound();
        }

        $isPublished = $request->boolean('is_published');
        $existingPublishedAt = $row->published_at;

        $row->update([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'excerpt' => $request->input('excerpt'),
            'content' => $request->input('content'),
            'image' => $request->input('image'),
            'is_published' => $isPublished,
            'published_at' => ($isPublished && ! $existingPublishedAt) ? now() : ($existingPublishedAt ?? null),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
        ]);

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = BlogPost::find($id);
        if ($row) {
            $row->delete();
        }

        return response()->json(['success' => true]);
    }
}
