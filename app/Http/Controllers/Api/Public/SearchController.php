<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Eyewear;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) ($request->query('q') ?? ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return $this->data(['eyewears' => [], 'brands' => [], 'blog' => []]);
        }

        $like = '%'.$q.'%';

        $eyewears = Eyewear::query()
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->select(['id', 'name', 'slug', 'image', 'price', 'discount_percentage', 'brand'])
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $brands = Brand::query()
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->select(['id', 'name', 'slug', 'logo'])
            ->take(4)
            ->get();

        $blog = BlogPost::query()
            ->where('is_published', true)
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like);
            })
            ->select(['id', 'title', 'slug', 'image', 'published_at'])
            ->orderByDesc('published_at')
            ->take(4)
            ->get();

        return $this->data(['eyewears' => $eyewears, 'brands' => $brands, 'blog' => $blog]);
    }
}
