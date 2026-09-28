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
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 12);
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

        return $this->data($row);
    }
}
