<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReminderLog;
use App\Support\Reminders;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    private const VALID_TYPES = ['lens_subscription', 'appointment', 'eye_followup', 'birthday', 'review_request'];

    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $limit = 30;

        $logs = ReminderLog::query()
            ->orderByDesc('sent_at')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $total = ReminderLog::count();

        return response()->json(['logs' => $logs, 'total' => $total, 'page' => $page]);
    }

    public function store(Request $request)
    {
        $type = (string) ($request->input('type') ?? '');
        if (! in_array($type, self::VALID_TYPES, true)) {
            return response()->json(['message' => 'Invalid type. Must be one of: ' . implode(', ', self::VALID_TYPES)], 400);
        }

        $dry = $request->boolean('dry_run');
        $result = match ($type) {
            'lens_subscription' => $dry ? ['total' => Reminders::countLensSubscriptionsDue(), 'sent' => 0, 'failed' => 0, 'dry_run' => true] : Reminders::sendLensSubscriptionReminders(),
            'appointment' => Reminders::sendAppointmentReminders(),
            'eye_followup' => Reminders::sendEyeTestFollowups($dry),
            'birthday' => Reminders::sendBirthdayWishes($dry),
            'review_request' => Reminders::sendReviewRequests($dry),
        };

        return response()->json(['result' => $result]);
    }
}
