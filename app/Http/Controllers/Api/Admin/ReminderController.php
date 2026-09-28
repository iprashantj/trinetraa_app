<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReminderLog;
use App\Support\Reminders;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    private const VALID_TYPES = ['lens_subscription', 'appointment'];

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
            return response()->json(['message' => 'Invalid type. Must be: lens_subscription or appointment'], 400);
        }

        if ($type === 'lens_subscription') {
            $result = Reminders::sendLensSubscriptionReminders();
        } else {
            $result = Reminders::sendAppointmentReminders();
        }

        return response()->json(['result' => $result]);
    }
}
