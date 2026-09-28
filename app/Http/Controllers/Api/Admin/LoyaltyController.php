<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $limit = min(100, (int) ($request->query('limit') ?: 20));
        $skip = ($page - 1) * $limit;

        $accounts = LoyaltyAccount::query()
            ->orderByDesc('points')
            ->with([
                'customer:id,name,email,phone',
                'transactions' => fn ($q) => $q->orderByDesc('created_at')->take(5),
            ])
            ->skip($skip)
            ->take($limit)
            ->get();

        $total = LoyaltyAccount::count();

        return response()->json(['accounts' => $accounts, 'total' => $total, 'page' => $page, 'limit' => $limit]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customerId' => 'required|integer|min:1',
            'points' => 'required|integer|min:1|max:100000',
            'type' => 'required|in:credit,debit,redeem,birthday,referral,purchase',
            'description' => 'nullable|string|max:255',
        ]);

        $customerId = (int) $request->input('customerId');
        $points = (int) $request->input('points');
        $type = $request->input('type');
        $description = $request->input('description');

        $delta = ($type === 'debit' || $type === 'redeem') ? -abs($points) : abs($points);

        $account = LoyaltyAccount::where('customer_id', $customerId)->first();
        if (! $account) {
            $account = LoyaltyAccount::create([
                'customer_id' => $customerId,
                'points' => 0,
                'tier' => 'Bronze',
                'total_earned' => 0,
            ]);
        }

        $newPoints = max(0, $account->points + $delta);
        $totalEarned = $delta > 0 ? $account->total_earned + $delta : $account->total_earned;
        $tier = $totalEarned >= 5000 ? 'Gold' : ($totalEarned >= 2000 ? 'Silver' : 'Bronze');

        [$updatedAccount, $tx] = DB::transaction(function () use ($account, $newPoints, $totalEarned, $tier, $type, $delta, $description) {
            $account->update(['points' => $newPoints, 'total_earned' => $totalEarned, 'tier' => $tier]);
            $tx = LoyaltyTransaction::create([
                'account_id' => $account->id,
                'type' => $type,
                'points' => $delta,
                'description' => $description ?: null,
            ]);

            return [$account, $tx];
        });

        return response()->json(['account' => $updatedAccount, 'transaction' => $tx]);
    }
}
