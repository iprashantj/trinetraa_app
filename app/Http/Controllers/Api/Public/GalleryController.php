<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 12);
        $skip = ($page - 1) * $perPage;

        $rows = GalleryItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = GalleryItem::where('is_active', true)->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total])
            ->header('Cache-Control', 'no-store');
    }
}
