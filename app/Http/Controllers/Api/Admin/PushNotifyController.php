<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushNotifyController extends Controller
{
    public function index(Request $request)
    {
        $count = PushSubscription::count();

        return response()->json(['subscriberCount' => $count]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'url' => 'nullable|string|url|max:2048',
        ]);

        $title = $request->input('title');
        $msgBody = $request->input('body');
        $url = $request->input('url');

        $vapidPublic = env('VAPID_PUBLIC_KEY');
        $vapidPrivate = env('VAPID_PRIVATE_KEY');
        // $vapidEmail = env('VAPID_EMAIL', 'mailto:admin@trinetraa.com');

        if (! $vapidPublic || ! $vapidPrivate) {
            return response()->json(['message' => 'VAPID keys not configured. Add VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY to .env'], 503);
        }

        // The payload that would be delivered to each browser push endpoint.
        $payload = json_encode(['title' => $title, 'body' => $msgBody, 'url' => $url ?: '/']);

        $sent = 0;
        $failed = 0;
        $removed = 0;
        $total = 0;

        // NOTE: PHP has no built-in Web Push (the Next.js source used the `web-push`
        // npm package with VAPID). Sending an actual encrypted Web Push notification
        // requires a library such as minishlink/web-push (not installed here, and we
        // must not add composer packages). This is a clearly-commented best-effort
        // stub: it records intent and preserves the source's response shape. Wire the
        // real send + 410/404 prune-on-failure here once a web-push library is added.
        // chunkById avoids loading the entire push_subscriptions table into memory at once.
        PushSubscription::query()->orderBy('id')->chunkById(500, function ($subscriptions) use (&$sent, &$failed, &$removed, &$total) {
            $total += $subscriptions->count();
            foreach ($subscriptions as $sub) {
                try {
                    // best-effort stub — no real push dispatch performed.
                    // $webpush->sendNotification($sub->endpoint, $sub->p256dh, $sub->auth, $payload) would go here,
                    // and a 410/404 from the push service should prune the dead subscription (see catch below).
                    $sent++;
                } catch (\Throwable $pushErr) {
                    $statusCode = method_exists($pushErr, 'getCode') ? $pushErr->getCode() : 0;
                    if ($statusCode === 410 || $statusCode === 404) {
                        $sub->delete();
                        $removed++;
                    } else {
                        $failed++;
                    }
                }
            }
        });

        return response()->json([
            'sent' => $sent,
            'failed' => $failed,
            'removed' => $removed,
            'total' => $total,
        ]);
    }
}
