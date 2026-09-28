<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\LensSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $subs = LensSubscription::query()
            ->where('customer_id', $customer['id'])
            ->orderBy('next_due_date')
            ->with(['orders' => function ($q) {
                $q->orderByDesc('ordered_at')->take(5);
            }])
            ->get();

        return response()->json(['subs' => $subs]);
    }

    public function store(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $validator = Validator::make($request->all(), [
            'lens_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'power' => 'nullable|string|max:50',
            'interval_days' => 'required|integer|in:30,60,90',
            'address_line' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ], [
            'lens_name.required' => 'Lens name required',
            'interval_days.in' => 'Interval must be 30, 60, or 90 days',
        ]);

        if ($validator->fails()) {
            return $this->message($validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        $intervalDays = (int) $data['interval_days'];
        $nextDueDate = Carbon::now()->addDays($intervalDays);

        $sub = LensSubscription::create([
            'customer_id' => $customer['id'],
            'lens_name' => $data['lens_name'],
            'brand' => $data['brand'] ?? null,
            'power' => $data['power'] ?? null,
            'interval_days' => $intervalDays,
            'next_due_date' => $nextDueDate,
            'address_line' => $data['address_line'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'active',
        ]);

        return response()->json(['sub' => $sub], 201);
    }
}
