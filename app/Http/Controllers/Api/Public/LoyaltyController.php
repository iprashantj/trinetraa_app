<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $account = LoyaltyAccount::query()
            ->where('customer_id', $customer['id'])
            ->with(['transactions' => function ($q) {
                $q->orderByDesc('created_at')->take(20);
            }])
            ->first();

        return response()->json(['account' => $account]);
    }
}
