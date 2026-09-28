<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\LensSubscription;
use App\Models\ReminderLog;
use Illuminate\Support\Carbon;

/**
 * Automated email reminders — ported from lib/reminders.js
 */
class Reminders
{
    const STORE_NAME = 'Trinetraa Optician';
    const STORE_PHONE = '+91 99999 99999';

    protected static function storeUrl(): string
    {
        return env('APP_URL', 'https://trinetraa.com');
    }

    protected static function esc($str): string
    {
        return htmlspecialchars((string) ($str ?? ''), ENT_QUOTES);
    }

    protected static function send(string $to, string $subject, string $html): void
    {
        ResendMailer::send($to, $subject, $html);
    }

    public static function sendLensSubscriptionReminders(): array
    {
        $threeDaysLater = Carbon::today()->addDays(3);

        $dueSubs = LensSubscription::with('customer')
            ->where('status', 'active')
            ->where('next_due_date', '<=', $threeDaysLater)
            ->get();

        $sent = 0;
        $failed = 0;
        foreach ($dueSubs as $sub) {
            try {
                $brandLine = $sub->brand ? '<strong>Brand:</strong> ' . self::esc($sub->brand) . '<br>' : '';
                $powerLine = $sub->power ? '<strong>Power:</strong> ' . self::esc($sub->power) . '<br>' : '';
                $due = Carbon::parse($sub->next_due_date)->format('d/m/Y');
                $url = self::esc(self::storeUrl());
                $html = '
          <div style="font-family:sans-serif;max-width:500px;margin:0 auto;padding:24px">
            <h2 style="color:#1a3a5c">Hi ' . self::esc($sub->customer->name) . ',</h2>
            <p>Your contact lenses are due for renewal!</p>
            <div style="background:#f8f9fa;border-radius:8px;padding:16px;margin:16px 0">
              <strong>Lens:</strong> ' . self::esc($sub->lens_name) . '<br>
              ' . $brandLine . '
              ' . $powerLine . '
              <strong>Due Date:</strong> ' . $due . '
            </div>
            <p>Visit us or call <strong>' . self::esc(self::STORE_PHONE) . '</strong> to reorder.</p>
            <a href="' . $url . '" style="display:inline-block;background:#1a3a5c;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none">Visit Store</a>
            <p style="margin-top:24px;color:#999;font-size:12px">' . self::esc(self::STORE_NAME) . ' | Nashik</p>
          </div>';
                self::send($sub->customer->email, 'Time to reorder your ' . self::esc($sub->lens_name) . ' — ' . self::STORE_NAME, $html);
                ReminderLog::create([
                    'type' => 'lens_subscription',
                    'recipient_id' => $sub->customer_id,
                    'email' => $sub->customer->email,
                    'subject' => 'Lens reorder: ' . $sub->lens_name,
                    'status' => 'sent',
                ]);
                $sent++;
            } catch (\Throwable $err) {
                ReminderLog::create([
                    'type' => 'lens_subscription',
                    'recipient_id' => $sub->customer_id,
                    'email' => $sub->customer->email ?? null,
                    'subject' => 'Lens reorder: ' . $sub->lens_name,
                    'status' => 'failed',
                    'error' => substr($err->getMessage(), 0, 500),
                ]);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'total' => $dueSubs->count()];
    }

    public static function sendAppointmentReminders(): array
    {
        $tomorrow = Carbon::today()->addDay();
        $dayAfter = Carbon::today()->addDays(2);

        $appts = Appointment::where('status', 'confirmed')
            ->where('appt_date', '>=', $tomorrow)
            ->where('appt_date', '<', $dayAfter)
            ->get();

        $sent = 0;
        $failed = 0;
        foreach ($appts as $appt) {
            try {
                $date = Carbon::parse($appt->appt_date)->format('d/m/Y');
                $html = '
          <div style="font-family:sans-serif;max-width:500px;margin:0 auto;padding:24px">
            <h2 style="color:#1a3a5c">Hi ' . self::esc($appt->name) . ',</h2>
            <p>This is a reminder for your appointment tomorrow:</p>
            <div style="background:#f8f9fa;border-radius:8px;padding:16px;margin:16px 0">
              <strong>Service:</strong> ' . self::esc($appt->service) . '<br>
              <strong>Date:</strong> ' . $date . '<br>
              <strong>Time:</strong> ' . self::esc($appt->time_slot) . '
            </div>
            <p>Questions? Call us at <strong>' . self::esc(self::STORE_PHONE) . '</strong></p>
            <p style="margin-top:24px;color:#999;font-size:12px">' . self::esc(self::STORE_NAME) . ' | Nashik</p>
          </div>';
                self::send($appt->email, 'Appointment Reminder — ' . self::STORE_NAME, $html);
                ReminderLog::create([
                    'type' => 'appointment',
                    'email' => $appt->email,
                    'subject' => 'Appointment: ' . $appt->service,
                    'status' => 'sent',
                ]);
                $sent++;
            } catch (\Throwable $err) {
                ReminderLog::create([
                    'type' => 'appointment',
                    'email' => $appt->email,
                    'subject' => 'Appointment: ' . $appt->service,
                    'status' => 'failed',
                    'error' => substr($err->getMessage(), 0, 500),
                ]);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'total' => $appts->count()];
    }
}
