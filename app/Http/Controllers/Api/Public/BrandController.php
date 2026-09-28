<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Eyewear;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($brands->count() > 0) {
            return $this->data($brands);
        }

        // Fallback: derive brands from eyewear records if no Brand model rows yet
        $rows = Eyewear::query()
            ->where('is_active', true)
            ->whereNotNull('brand')
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->get();

        $derived = $rows->pluck('brand')->filter()->values();

        return $this->data($derived);
    }

    public function show($slug)
    {
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $brand) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $eyewears = Eyewear::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(brand) = ?', [strtolower((string) $brand->name)])
            ->with(['category:name'])
            ->orderBy('sort_order')
            ->take(20)
            ->get();

        return $this->data(['brand' => $brand, 'eyewears' => $eyewears]);
    }
}
