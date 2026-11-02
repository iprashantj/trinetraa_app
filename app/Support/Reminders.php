<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\LensSubscription;
use App\Models\ReminderLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    // ─── Customer reminders over WhatsApp / SMS / email (via Messenger) ────────────────────────────
    // Each method: $dry = true only counts who would be messaged. Results: total (eligible), sent, failed, manual
    // (no provider configured, so staff can send by link instead), dry_run.

    /** Already sent this type to this customer within $days days? */
    protected static function alreadySent(string $type, int $customerId, int $days): bool
    {
        return ReminderLog::where('type', $type)->where('recipient_id', $customerId)->where('status', 'sent')
            ->where('sent_at', '>=', now()->subDays($days))->exists();
    }

    protected static function tally(array &$res, array $r): void
    {
        if (! empty($r['sent'])) {
            $res['sent']++;
        } elseif (collect($r['attempts'] ?? [])->contains(fn ($a) => $a['status'] === 'failed')) {
            $res['failed']++;
        } else {
            $res['manual']++;
        }
    }

    protected static function blank(bool $dry): array
    {
        return ['total' => 0, 'sent' => 0, 'failed' => 0, 'manual' => 0, 'dry_run' => $dry];
    }

    protected static function firstName(?string $n): string
    {
        $first = trim(explode(' ', trim((string) $n))[0] ?? '');

        return $first !== '' ? $first : 'there';
    }

    /** Customers whose most recent eye test was ~6 or ~12 months ago (a 7-day window, so a daily run catches everyone once). */
    public static function sendEyeTestFollowups(bool $dry = false): array
    {
        $res = self::blank($dry);
        foreach ([12 => 'eye_followup_12m', 6 => 'eye_followup_6m'] as $months => $type) {
            $to = Carbon::today()->subMonths($months);
            $from = $to->copy()->subDays(7);
            $latest = DB::table('eye_tests')->selectRaw('customer_id, MAX(test_date) last_test')->groupBy('customer_id');
            $ids = DB::query()->fromSub($latest, 'l')->whereBetween('last_test', [$from->toDateString(), $to->toDateString()])->pluck('customer_id');
            foreach (\App\Models\Customer::whereIn('id', $ids)->get() as $c) {
                if (! $c->marketing_consent || (! $c->phone && ! $c->email) || self::alreadySent($type, $c->id, 300)) {
                    continue;
                }
                $res['total']++;
                if ($dry) {
                    continue;
                }
                $msg = 'Hi ' . self::firstName($c->name) . ', it has been ' . ($months === 12 ? 'a year' : '6 months') . ' since your last eye test at ' . self::STORE_NAME
                    . '. A regular check-up keeps your vision sharp — book your visit: ' . rtrim(self::storeUrl(), '/') . '/appointment';
                self::tally($res, Messenger::notify($msg, ['customer' => $c, 'type' => $type, 'subject' => 'Eye test follow-up (' . $months . ' months)', 'transactional' => false]));
            }
        }

        return $res;
    }

    public static function sendBirthdayWishes(bool $dry = false): array
    {
        $res = self::blank($dry);
        $today = Carbon::today();
        $customers = \App\Models\Customer::whereNotNull('dob')->whereMonth('dob', $today->month)->whereDay('dob', $today->day)->get();
        foreach ($customers as $c) {
            if (! $c->marketing_consent || (! $c->phone && ! $c->email) || self::alreadySent('birthday', $c->id, 300)) {
                continue;
            }
            $res['total']++;
            if ($dry) {
                continue;
            }
            $msg = 'Happy Birthday ' . self::firstName($c->name) . '! 🎉 Wishing you a wonderful year from all of us at ' . self::STORE_NAME . '.';
            self::tally($res, Messenger::notify($msg, ['customer' => $c, 'type' => 'birthday', 'subject' => 'Happy Birthday', 'transactional' => false]));
        }

        return $res;
    }

    /** Lens subscriptions due within 3 days (the sending itself is sendLensSubscriptionReminders). */
    public static function countLensSubscriptionsDue(): int
    {
        return LensSubscription::where('status', 'active')->where('next_due_date', '<=', Carbon::today()->addDays(3))->count();
    }

    /** Google review link for a branch, falling back to the site-wide one. Null when the owner has set neither. */
    public static function reviewUrl(?int $branchId): ?string
    {
        $url = $branchId ? DB::table('branches')->where('id', $branchId)->value('review_url') : null;

        return $url ?: (SiteConfig::all()['googleReviewUrl'] ?? env('GOOGLE_REVIEW_URL')) ?: null;
    }

    /** Ask one bill's customer for a review. Stamps review_requested_at only when a provider really sent it. */
    public static function requestReview(\App\Models\FrameBill $b): array
    {
        $url = self::reviewUrl($b->branch_id ? (int) $b->branch_id : null);
        if (! $url) {
            return ['error' => 'no_url'];
        }
        $c = $b->customer_id ? \App\Models\Customer::find($b->customer_id) : null;
        $msg = 'Hi ' . self::firstName($b->customer_name ?: $c?->name) . ', thank you for shopping at ' . self::STORE_NAME . '! If you liked your experience, a quick Google review means a lot to us: ' . $url;
        $r = Messenger::notify($msg, [
            'customer' => $c, 'phone' => $b->customer_contact ?: $c?->phone, 'email' => $c?->email,
            'type' => 'review_request', 'subject' => 'How was your visit?', 'transactional' => true,
        ]);
        if ($r['sent']) {
            DB::table('frame_bills')->where('id', $b->id)->update(['review_requested_at' => now()]);
        }

        return $r + ['message' => $msg];
    }

    /** Bills 2–7 days old never asked for a review; needs a phone and (when linked to a customer) marketing consent. */
    public static function sendReviewRequests(bool $dry = false): array
    {
        $res = self::blank($dry);
        $bills = \App\Models\FrameBill::whereNull('review_requested_at')
            ->whereBetween('created_at', [now()->subDays(7), now()->subDays(2)])->get();
        foreach ($bills as $b) {
            $c = $b->customer_id ? \App\Models\Customer::find($b->customer_id) : null;
            if ($c && ! $c->marketing_consent) {
                continue;
            }
            if (! Messenger::phone($b->customer_contact ?: $c?->phone) || ! self::reviewUrl($b->branch_id ? (int) $b->branch_id : null)) {
                continue;
            }
            $res['total']++;
            if (! $dry) {
                self::tally($res, self::requestReview($b));
            }
        }

        return $res;
    }
}
