<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\RecentlyViewed;
use Illuminate\Http\Request;

class RecentlyViewedController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $rows = RecentlyViewed::query()
            ->where('customer_id', $customer['id'])
            ->orderByDesc('viewed_at')
            ->take(30)
            ->with(['eyewear.category:id,name,slug'])
            ->get();

        return $this->data($rows);
    }

    public function store(Request $request)
    {
        $customer = $request->attributes->get('customer');
        $eyewearId = $request->input('eyewear_id');

        RecentlyViewed::updateOrCreate(
            ['customer_id' => $customer['id'], 'eyewear_id' => $eyewearId],
            ['viewed_at' => now()],
        );

        return $this->message('Tracked.');
    }
}
