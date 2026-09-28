<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PushSubscribeController extends Controller
{
    public function store(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string|url|max:500',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string|min:1|max:200',
            'keys.auth' => 'required|string|min:1|max:100',
        ], [
            'endpoint.url' => 'Invalid endpoint URL',
        ]);

        if ($validator->fails()) {
            return $this->message($validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        $endpoint = trim($data['endpoint']);

        $existing = PushSubscription::where('endpoint', $endpoint)->first();
        if ($existing) {
            $existing->update([
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
            ]);
        } else {
            PushSubscription::create([
                'customer_id' => $customer['id'],
                'endpoint' => $endpoint,
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $endpoint = trim((string) $request->input('endpoint', ''));
        if (! $endpoint) {
            return $this->message('Endpoint required', 400);
        }

        PushSubscription::where('endpoint', $endpoint)
            ->where('customer_id', $customer['id'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
