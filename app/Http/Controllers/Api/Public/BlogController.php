<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 9);
        $skip = ($page - 1) * $perPage;

        $rows = BlogPost::query()
            ->where('is_published', true)
            ->select(['id', 'title', 'slug', 'excerpt', 'image', 'published_at', 'created_at'])
            ->orderByDesc('published_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = BlogPost::where('is_published', true)->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function show($slug)
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (! $post) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return $this->data($post);
    }
}
