<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    public function index()
    {
        $rows = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['eyewears as eyewears_count' => function (Builder $q) {
                $q->where('is_active', true);
            }])
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'description' => $r->description,
                'image' => $r->image,
                'eyewears_count' => $r->eyewears_count,
            ];
        });

        return $this->data($data);
    }
}
