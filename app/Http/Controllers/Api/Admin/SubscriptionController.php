<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LensSubscription;
use App\Models\SubscriptionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    private const VALID_STATUSES = ['active', 'paused', 'cancelled'];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $page = max(1, (int) ($request->query('page') ?: 1));
        $limit = 20;

        if ($status && ! in_array($status, self::VALID_STATUSES, true)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $query = LensSubscription::query();
        if ($status) {
            $query->where('status', $status);
        }

        $subs = (clone $query)
            ->orderBy('next_due_date')
            ->with([
                'customer:id,name,email,phone',
                'orders' => fn ($q) => $q->orderByDesc('ordered_at')->take(3),
            ])
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $total = $query->count();

        return response()->json(['subs' => $subs, 'total' => $total, 'page' => $page]);
    }

    public function update(Request $request, $id)
    {
        $numId = (int) $id;
        if (! $numId) {
            return response()->json(['message' => 'Invalid id'], 400);
        }

        $request->validate([
            'action' => 'sometimes|in:fulfill',
            'status' => 'sometimes|in:active,paused,cancelled',
            'intervalDays' => 'sometimes|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'addressLine' => 'nullable|string|max:500',
        ]);

        $action = $request->input('action');

        if ($action === 'fulfill') {
            $sub = LensSubscription::find($numId);
            if (! $sub) {
                return response()->json(['message' => 'Not found'], 404);
            }
            $nextDue = Carbon::parse($sub->next_due_date)->addDays((int) $sub->interval_days);

            [$updatedSub, $order] = DB::transaction(function () use ($sub, $numId, $nextDue, $request) {
                $sub->update(['next_due_date' => $nextDue]);
                $order = SubscriptionOrder::create([
                    'subscription_id' => $numId,
                    'status' => 'fulfilled',
                    'notes' => $request->input('notes') ?: null,
                ]);

                return [$sub, $order];
            });

            return response()->json(['sub' => $updatedSub, 'order' => $order]);
        }

        // Map allowed camelCase request keys -> snake_case columns.
        $map = [
            'status' => 'status',
            'intervalDays' => 'interval_days',
            'notes' => 'notes',
            'addressLine' => 'address_line',
        ];
        $data = [];
        foreach ($map as $reqKey => $column) {
            if ($request->has($reqKey)) {
                $data[$column] = $request->input($reqKey);
            }
        }

        if (count($data) === 0) {
            return response()->json(['message' => 'No valid fields to update'], 400);
        }

        $sub = LensSubscription::find($numId);
        if (! $sub) {
            return $this->notFound();
        }
        $sub->update($data);

        return response()->json(['sub' => $sub]);
    }

    public function destroy(Request $request, $id)
    {
        $numId = (int) $id;
        if (! $numId) {
            return response()->json(['message' => 'Invalid id'], 400);
        }
        $sub = LensSubscription::find($numId);
        if ($sub) {
            $sub->delete();
        }

        return response()->json(['success' => true]);
    }
}
