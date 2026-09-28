<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Support\Carbon;

class OfferController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $rows = Offer::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return $this->data($rows);
    }
}
