<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\ProductEnquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) ($request->query('per_page', 20));
        $skip = ($page - 1) * $perPage;

        $rows = ContactEnquiry::query()->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = ContactEnquiry::query()->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function product(Request $request)
    {
        $page = max(1, (int) ($request->query('page', 1)));
        $perPage = min(100, (int) ($request->query('per_page', 50)));
        $skip = ($page - 1) * $perPage;

        $items = ProductEnquiry::query()->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = ProductEnquiry::query()->count();

        return $this->data($items, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }
}
